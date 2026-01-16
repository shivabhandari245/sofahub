<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class OtpService
{
    protected int $otpLength = 6;
    protected int $expiryMinutes = 5;   // OTP expires in 5 minutes
    protected int $resendCooldown = 30; // seconds

    /**
     * Generate OTP for user
     */
    public function generate(User $user): void
    {
        $otp = $this->generateRandomOtp();

        // Store OTP in cache
        Cache::put($this->cacheKey($user), $otp, now()->addMinutes($this->expiryMinutes));

        // Store resend cooldown
        Cache::put($this->resendKey($user), now()->addSeconds($this->resendCooldown), $this->resendCooldown);

        // Send OTP email
        $this->sendOtpEmail($user, $otp);
    }

    /**
     * Resend OTP
     */
    public function resend(User $user): void
    {
        if (!$this->canResend($user)) {
            $availableAt = Cache::get($this->resendKey($user));
            $seconds = $availableAt ? Carbon::parse($availableAt)->diffInSeconds(now()) : $this->resendCooldown;
            throw new \Exception("Please wait {$seconds} seconds before resending OTP.");
        }

        $this->generate($user);
    }

    /**
     * Validate OTP
     */
    public function validate(User $user, string $otp): bool
    {
        $cachedOtp = Cache::get($this->cacheKey($user));

        if ($cachedOtp && $cachedOtp === $otp) {
            // OTP valid → remove it from cache
            Cache::forget($this->cacheKey($user));
            return true;
        }

        return false;
    }

    /**
     * Check if user can resend OTP
     */
    public function canResend(User $user): bool
    {
        $nextAvailable = Cache::get($this->resendKey($user));
        if (!$nextAvailable) return true;

        return now()->greaterThanOrEqualTo(Carbon::parse($nextAvailable));
    }

    /**
     * Generate random numeric OTP
     */
    protected function generateRandomOtp(): string
    {
        return str_pad((string)random_int(0, 999999), $this->otpLength, '0', STR_PAD_LEFT);
    }

    /**
     * OTP cache key
     */
    protected function cacheKey(User $user): string
    {
        return "user:otp:{$user->id}";
    }

    /**
     * Resend cooldown cache key
     */
    protected function resendKey(User $user): string
    {
        return "user:otp:resend:{$user->id}";
    }

    /**
     * Send OTP email
     */
    protected function sendOtpEmail(User $user, string $otp): void
    {
        // Replace with your Mailable class
        Mail::raw("Your OTP code is: {$otp}. It expires in {$this->expiryMinutes} minutes.", function($message) use ($user) {
            $message->to($user->email)
                    ->subject('Your OTP Code');
        });
    }
}
