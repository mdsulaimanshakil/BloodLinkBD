<?php

namespace App\Contracts;

use App\Models\DonorProfile;

/**
 * Interface for notification channels.
 *
 * Implementing a new channel (e.g. paid SMS) is a one-file addition:
 * create a class implementing this interface and register it in the service.
 */
interface NotificationChannelInterface
{
    /**
     * Send a notification message to a donor.
     *
     * @param  DonorProfile $donor   The donor to notify.
     * @param  string       $message The notification message.
     * @return void
     */
    public function send(DonorProfile $donor, string $message): void;
}
