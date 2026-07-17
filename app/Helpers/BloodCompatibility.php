<?php

namespace App\Helpers;

/**
 * Blood compatibility matrix encoding standard donor→recipient transfusion rules.
 *
 * Usage:
 *   BloodCompatibility::canDonateTo('O-', 'A+');    // true (O- is universal donor)
 *   BloodCompatibility::compatibleDonors('A+');      // ['A+', 'A-', 'O+', 'O-']
 *   BloodCompatibility::compatibleRecipients('O-');  // all 8 blood groups
 *   BloodCompatibility::matrix();                    // full 8×8 boolean matrix
 */
class BloodCompatibility
{
    /**
     * All recognized blood groups.
     *
     * @var list<string>
     */
    public const BLOOD_GROUPS = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

    /**
     * Donor→Recipient compatibility map.
     *
     * Key = donor blood group, Value = list of recipient groups that can receive it.
     *
     * @var array<string, list<string>>
     */
    protected const CAN_DONATE_TO = [
        'O-'  => ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'],  // Universal donor
        'O+'  => ['O+', 'A+', 'B+', 'AB+'],
        'A-'  => ['A-', 'A+', 'AB-', 'AB+'],
        'A+'  => ['A+', 'AB+'],
        'B-'  => ['B-', 'B+', 'AB-', 'AB+'],
        'B+'  => ['B+', 'AB+'],
        'AB-' => ['AB-', 'AB+'],
        'AB+' => ['AB+'],                                                // Universal recipient
    ];

    /**
     * Check if a donor blood group can donate to a recipient blood group.
     */
    public static function canDonateTo(string $donorGroup, string $recipientGroup): bool
    {
        return in_array($recipientGroup, self::CAN_DONATE_TO[$donorGroup] ?? [], true);
    }

    /**
     * Get all blood groups that can DONATE to the given recipient.
     *
     * Used to find eligible donors when a blood request is posted.
     * e.g. compatibleDonors('A+') => ['A+', 'A-', 'O+', 'O-']
     *
     * @return list<string>
     */
    public static function compatibleDonors(string $recipientGroup): array
    {
        $donors = [];

        foreach (self::CAN_DONATE_TO as $donorGroup => $recipients) {
            if (in_array($recipientGroup, $recipients, true)) {
                $donors[] = $donorGroup;
            }
        }

        return $donors;
    }

    /**
     * Get all blood groups that the given donor can donate TO.
     *
     * @return list<string>
     */
    public static function compatibleRecipients(string $donorGroup): array
    {
        return self::CAN_DONATE_TO[$donorGroup] ?? [];
    }

    /**
     * Generate the full compatibility matrix.
     *
     * Returns an associative array keyed by donor group, each containing
     * an associative array keyed by recipient group with boolean values.
     *
     * @return array<string, array<string, bool>>
     */
    public static function matrix(): array
    {
        $matrix = [];

        foreach (self::BLOOD_GROUPS as $donor) {
            foreach (self::BLOOD_GROUPS as $recipient) {
                $matrix[$donor][$recipient] = self::canDonateTo($donor, $recipient);
            }
        }

        return $matrix;
    }
}
