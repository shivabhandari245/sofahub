<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    protected int $otpLength = 6; // 6-digit OTP
    protected int $otpTTL = 5 * 60; // 5 minutes
    protected int $resendTTL = 30; // 30 seconds

    /**
     * Generate OTP for a user and store in cache
     */
    public function generate(User $user): string
    {
        $otp = (string) random_int(100000, 999999); // always store as string
        $cacheKey = $this->otpCacheKey($user);

        Cache::put($cacheKey, $otp, $this->otpTTL);
        RateLimiter::hit($this->resendCacheKey($user), $this->resendTTL);

        $this->sendOtp($user, $otp);

        return $otp;
    }

    /**
     * Validate OTP safely
     */
    public function validate(User $user, string $otp): bool
    {
        $cacheKey = $this->otpCacheKey($user);
        $storedOtp = Cache::get($cacheKey);

        // Ensure both are strings for hash_equals
        if ($storedOtp !== null && hash_equals((string) $storedOtp, $otp)) {
            Cache::forget($cacheKey); // single-use
            return true;
        }

        return false;
    }

    /**
     * Check if user can resend OTP
     */
    public function canResend(User $user): bool
    {
        $key = $this->resendCacheKey($user);
        return RateLimiter::remaining($key, 1) > 0;
    }
    

      public function resend(User $user): string
    {
        // simply call generate() to create a new OTP and send it
        return $this->generate($user);
    }


    protected function sendOtp(User $user, string $otp): void
    {
        Mail::raw("Your OTP code is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Your OTP Code');
        });
    }

    protected function otpCacheKey(User $user): string
    {
        return 'otp:' . $user->id;
    }

    protected function resendCacheKey(User $user): string
    {
        return 'otp-resend:' . $user->id;
    }
}
