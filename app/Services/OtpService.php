<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OtpService
{
    protected int $otpLength = 6; // 6-digit OTP
    protected int $otpTTL = 5 * 60; // 5 minutes
    protected int $resendTTL = 30; // 30 seconds between resends

    /**
     * Generate OTP for a user and store in cache
     */
    public function generate(User $user): string
    {
        $otp = random_int(100000, 999999); // 6-digit numeric OTP
        $cacheKey = $this->otpCacheKey($user);

        Cache::put($cacheKey, $otp, $this->otpTTL);

        // Optional: store resend time to prevent spamming
        RateLimiter::hit($this->resendCacheKey($user), $this->resendTTL);

        // Send OTP to user (email)
        $this->sendOtp($user, $otp);

        return (string) $otp;
    }

    /**
     * Validate submitted OTP
     */
    public function validate(User $user, string $otp): bool
    {
        $cacheKey = $this->otpCacheKey($user);

        $storedOtp = Cache::get($cacheKey);

        if ($storedOtp && hash_equals($storedOtp, $otp)) {
            Cache::forget($cacheKey); // OTP is single-use
            return true;
        }

        return false;
    }

    /**
     * Resend OTP
     */
    public function resend(User $user): string
    {
        return $this->generate($user);
    }

    /**
     * Check if user can resend OTP
     */
    public function canResend(User $user): bool
    {
        $key = $this->resendCacheKey($user);
        return RateLimiter::remaining($key, 1) > 0;
    }

    /**
     * Send OTP to user via email
     */
    protected function sendOtp(User $user, string $otp): void
    {
        // You can customize this with a Mailable class
        Mail::raw("Your OTP code is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Your OTP Code');
        });
    }

    /**
     * Cache key for OTP
     */
    protected function otpCacheKey(User $user): string
    {
        return 'otp:' . $user->id;
    }

    /**
     * Cache key for resend rate limit
     */
    protected function resendCacheKey(User $user): string
    {
        return 'otp-resend:' . $user->id;
    }
}
