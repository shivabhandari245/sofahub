<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Mail\OtpMail;

class OtpService
{
    public function generate(User $user)
    {
        $otp = random_int(100000, 999999);

        $user->update([
            'otp_code' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(5),
            'otp_verified' => false,
        ]);

        try {
            Mail::to($user->email)->send(new OtpMail($otp));
        } catch (\Exception $e) {
            Log::error("Failed to send OTP to {$user->email}: " . $e->getMessage());
        }

        // Log OTP in dev for debugging
        if (app()->environment('local')) {
            Log::info("OTP for {$user->email}: $otp");
        }
    }

    public function validate(User $user, string $otp): bool
    {
        if (!$user->otp_code || !$user->otp_expires_at) {
            return false;
        }

        return Hash::check($otp, $user->otp_code) && now()->lt($user->otp_expires_at);
    }
}
