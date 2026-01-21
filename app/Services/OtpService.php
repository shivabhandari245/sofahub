<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class OtpService
{
    // Generate OTP and send
    public function generate(User $user): string
    {
        $otp = rand(100000, 999999); // 6-digit OTP
        $user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        // Send OTP email
        Mail::to($user->email)->send(new OtpMail($otp));

        return $otp;
    }

    // Validate OTP
    public function validate(User $user, string $otp): bool
    {
        if ($user->otp_code === $otp && $user->otp_expires_at > now()) {
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->otp_verified = true;
            $user->save();
            return true;
        }

        return false;
    }

    // Resend OTP
    public function resend(User $user)
    {
        if ($user->otp_expires_at && $user->otp_expires_at->gt(now())) {
            // Optional: prevent frequent resends
        }
        return $this->generate($user);
    }
}
