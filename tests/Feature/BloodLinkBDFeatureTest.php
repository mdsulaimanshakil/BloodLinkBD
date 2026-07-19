<?php

namespace Tests\Feature;

use App\Models\BloodRequest;
use App\Models\DonorProfile;
use App\Models\DonorResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Prompt 23: Feature tests for BloodLinkBD core functionality.
 *
 * Covers:
 *  1. Donor eligibility cooldown logic
 *  2. Request creation with rate limiting (max 3 active requests per phone)
 *  3. Donor notification dispatch on request creation
 *  4. Request auto-expiry command
 *  5. Masked phone number reveal logic
 *  6. Admin donor verification flow
 */
class BloodLinkBDFeatureTest extends TestCase
{
    use RefreshDatabase;

    // ═══════════════════════════════════════════════════════════════
    // 1. Donor Eligibility Cooldown Logic
    // ═══════════════════════════════════════════════════════════════

    /**
     * A donor who donated within the last 90 days is NOT eligible.
     */
    public function test_donor_is_ineligible_within_90_day_cooldown(): void
    {
        $user = User::factory()->create(['role' => 'donor']);

        DonorProfile::factory()->create([
            'user_id'            => $user->id,
            'is_verified'        => true,
            'is_available'       => true,
            'last_donation_date' => Carbon::now()->subDays(50)->toDateString(),
        ]);

        $eligible = DonorProfile::eligible()->where('user_id', $user->id)->exists();

        $this->assertFalse($eligible, 'Donor donated 50 days ago — should NOT be eligible.');
    }

    /**
     * A donor who donated more than 90 days ago IS eligible.
     */
    public function test_donor_is_eligible_after_90_day_cooldown(): void
    {
        $user = User::factory()->create(['role' => 'donor']);

        DonorProfile::factory()->create([
            'user_id'            => $user->id,
            'is_verified'        => true,
            'is_available'       => true,
            'last_donation_date' => Carbon::now()->subDays(91)->toDateString(),
        ]);

        $eligible = DonorProfile::eligible()->where('user_id', $user->id)->exists();

        $this->assertTrue($eligible, 'Donor donated 91 days ago — should be eligible.');
    }

    /**
     * A donor who has never donated is always eligible (if verified + available).
     */
    public function test_first_time_donor_is_eligible(): void
    {
        $user = User::factory()->create(['role' => 'donor']);

        DonorProfile::factory()->create([
            'user_id'            => $user->id,
            'is_verified'        => true,
            'is_available'       => true,
            'last_donation_date' => null,
        ]);

        $eligible = DonorProfile::eligible()->where('user_id', $user->id)->exists();

        $this->assertTrue($eligible, 'First-time donor — should be eligible.');
    }

    /**
     * An unverified donor is NEVER eligible, regardless of donation date.
     */
    public function test_unverified_donor_is_not_eligible(): void
    {
        $user = User::factory()->create(['role' => 'donor']);

        DonorProfile::factory()->create([
            'user_id'      => $user->id,
            'is_verified'  => false,
            'is_available' => true,
        ]);

        $eligible = DonorProfile::eligible()->where('user_id', $user->id)->exists();

        $this->assertFalse($eligible, 'Unverified donor — should NOT be eligible.');
    }

    // ═══════════════════════════════════════════════════════════════
    // 2. Request Creation with Rate Limiting (max 3 active per phone)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Posting a blood request with no active requests succeeds.
     */
    public function test_request_creation_succeeds_when_under_rate_limit(): void
    {
        $response = $this->post(route('blood-requests.store'), [
            'patient_name'    => 'Test Patient',
            'blood_group'     => 'A+',
            'district'        => 'Dhaka',
            'hospital'        => 'Dhaka Medical College Hospital',
            'urgency'         => 'urgent',
            'requester_phone' => '01712345678',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('blood_requests', [
            'patient_name'    => 'Test Patient',
            'requester_phone' => '01712345678',
            'status'          => 'active',
        ]);
    }

    /**
     * Posting a 4th active request from the same phone is rejected.
     */
    public function test_request_creation_blocked_when_at_rate_limit(): void
    {
        // Create 3 existing active requests for this phone number
        for ($i = 1; $i <= 3; $i++) {
            BloodRequest::factory()->create([
                'requester_phone' => '01787654321',
                'status'          => 'active',
            ]);
        }

        $response = $this->post(route('blood-requests.store'), [
            'patient_name'    => 'Extra Patient',
            'blood_group'     => 'B+',
            'district'        => 'Dhaka',
            'hospital'        => 'Dhaka Medical',
            'urgency'         => 'normal',
            'requester_phone' => '01787654321',
        ]);

        // Should be rejected with a validation error
        $response->assertSessionHasErrors('requester_phone');
        $this->assertEquals(3, BloodRequest::where('requester_phone', '01787654321')->count());
    }

    // ═══════════════════════════════════════════════════════════════
    // 3. Donor Notification Dispatch on Request Creation
    // ═══════════════════════════════════════════════════════════════

    /**
     * When a blood request is created, NotifyDonorsJob is dispatched.
     */
    public function test_notify_donors_job_is_dispatched_on_request_creation(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        $this->post(route('blood-requests.store'), [
            'patient_name'    => 'Job Test Patient',
            'blood_group'     => 'O+',
            'district'        => 'Chittagong',
            'hospital'        => 'Chittagong Medical College',
            'urgency'         => 'critical',
            'requester_phone' => '01811111111',
        ]);

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\NotifyDonorsJob::class);
    }

    // ═══════════════════════════════════════════════════════════════
    // 4. Request Auto-Expiry Command
    // ═══════════════════════════════════════════════════════════════

    /**
     * The expire command sets status = 'expired' for past-due active requests.
     */
    public function test_expire_command_marks_expired_requests(): void
    {
        // Create an active request that has already passed its expiry time
        $expired = BloodRequest::factory()->create([
            'status'     => 'active',
            'urgency'    => 'critical',
            'expires_at' => Carbon::now()->subHour(),
        ]);

        // Create an active request that has NOT yet expired
        $active = BloodRequest::factory()->create([
            'status'     => 'active',
            'urgency'    => 'urgent',
            'expires_at' => Carbon::now()->addHours(24),
        ]);

        $this->artisan('requests:expire')->assertSuccessful();

        $this->assertDatabaseHas('blood_requests', [
            'id'     => $expired->id,
            'status' => 'expired',
        ]);

        $this->assertDatabaseHas('blood_requests', [
            'id'     => $active->id,
            'status' => 'active',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // 5. Masked Phone Number Reveal Logic
    // ═══════════════════════════════════════════════════════════════

    /**
     * Donor search shows masked phone for unauthenticated visitors.
     */
    public function test_phone_is_masked_for_unauthenticated_visitors(): void
    {
        $user = User::factory()->create(['role' => 'donor']);
        DonorProfile::factory()->create([
            'user_id'    => $user->id,
            'phone'      => '01712345678',
            'is_verified'=> true,
            'is_available'=> true,
        ]);

        $response = $this->get(route('donor-search'));
        $response->assertOk();

        // Should show masked form, not full number
        $response->assertSee('***');
        $response->assertDontSee('01712345678');
    }

    /**
     * DonorProfile::masked_phone accessor returns correct masked format.
     */
    public function test_masked_phone_accessor_masks_correctly(): void
    {
        $user    = User::factory()->create(['role' => 'donor']);
        $profile = DonorProfile::factory()->create([
            'user_id' => $user->id,
            'phone'   => '01712345678',
        ]);

        // e.g. 01712***678
        $this->assertEquals('01712***678', $profile->masked_phone);
    }

    /**
     * A verified donor can see the full phone number in donor search.
     */
    public function test_verified_donor_sees_full_phone_number(): void
    {
        // The viewer: a verified, available donor
        $viewer = User::factory()->create(['role' => 'donor']);
        DonorProfile::factory()->create([
            'user_id'     => $viewer->id,
            'phone'       => '01799999999',
            'is_verified' => true,
            'is_available'=> true,
        ]);

        // The listed donor
        $listed = User::factory()->create(['role' => 'donor']);
        DonorProfile::factory()->create([
            'user_id'     => $listed->id,
            'phone'       => '01712345678',
            'is_verified' => true,
            'is_available'=> true,
        ]);

        $response = $this->actingAs($viewer)->get(route('donor-search'));
        $response->assertOk();
        $response->assertSee('01712345678');
    }

    // ═══════════════════════════════════════════════════════════════
    // 6. Admin Donor Verification Flow
    // ═══════════════════════════════════════════════════════════════

    /**
     * An admin can approve a pending donor profile.
     */
    public function test_admin_can_verify_donor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $donor = User::factory()->create(['role' => 'donor']);
        $profile = DonorProfile::factory()->create([
            'user_id'    => $donor->id,
            'is_verified'=> false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.donors.verify', $profile));

        $response->assertRedirect(route('admin.donors.pending'));
        $this->assertDatabaseHas('donor_profiles', [
            'id'          => $profile->id,
            'is_verified' => true,
        ]);

        // Audit log should be created
        $this->assertDatabaseHas('admin_audit_logs', [
            'admin_id'    => $admin->id,
            'action'      => 'verify_donor',
            'target_id'   => $profile->id,
        ]);
    }

    /**
     * An admin can reject a donor profile.
     */
    public function test_admin_can_reject_donor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $donor = User::factory()->create(['role' => 'donor']);
        $profile = DonorProfile::factory()->create([
            'user_id'    => $donor->id,
            'is_verified'=> false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.donors.reject', $profile), [
            'notes' => 'Incomplete information provided.',
        ]);

        $response->assertRedirect(route('admin.donors.pending'));
        $this->assertDatabaseHas('donor_profiles', [
            'id'          => $profile->id,
            'is_verified' => false,
            'is_available'=> false,
        ]);

        $this->assertDatabaseHas('admin_audit_logs', [
            'admin_id'  => $admin->id,
            'action'    => 'reject_donor',
            'target_id' => $profile->id,
        ]);
    }

    /**
     * A non-admin user cannot access admin routes.
     */
    public function test_non_admin_cannot_access_admin_panel(): void
    {
        $donor = User::factory()->create(['role' => 'donor']);

        $response = $this->actingAs($donor)->get(route('admin.dashboard'));
        $response->assertForbidden();
    }

    /**
     * Admin can remove a blood request (marks as removed, writes audit log).
     */
    public function test_admin_can_remove_blood_request(): void
    {
        $admin   = User::factory()->create(['role' => 'admin']);
        $request = BloodRequest::factory()->create(['status' => 'active']);

        $response = $this->actingAs($admin)->post(route('admin.requests.remove', $request), [
            'notes' => 'Suspected fake request.',
        ]);

        $response->assertRedirect(route('admin.requests.index'));
        $this->assertDatabaseHas('blood_requests', [
            'id'     => $request->id,
            'status' => 'removed',
        ]);

        $this->assertDatabaseHas('admin_audit_logs', [
            'admin_id'  => $admin->id,
            'action'    => 'remove_request',
            'target_id' => $request->id,
        ]);
    }
}
