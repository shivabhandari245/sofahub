<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class OtpService
{
    protected int $otpLength = 6;
    protected int $expiryMinutes = 5;
    protected int $resendCooldown = 30;
    protected int $maxAttempts = 5;

    /**
     * Generate OTP
     */
    public function generate(User $user): void
    {
        $otp = $this->generateRandomOtp();

        Cache::put(
            $this->cacheKey($user),
            [
                'hash' => Hash::make($otp),
                'attempts' => 0,
            ],
            now()->addMinutes($this->expiryMinutes)
        );

        Cache::put(
            $this->resendKey($user),
            now()->addSeconds($this->resendCooldown),
            $this->resendCooldown
        );

        $this->sendOtpEmail($user, $otp);
    }

    /**
     * Validate OTP
     */
    public function validate(User $user, string $otp): bool
    {
        $data = Cache::get($this->cacheKey($user));

        if (!$data) {
            return false;
        }

        // Too many attempts
        if ($data['attempts'] >= $this->maxAttempts) {
            Cache::forget($this->cacheKey($user));
            return false;
        }

        if (!Hash::check($otp, $data['hash'])) {
            $data['attempts']++;
            Cache::put($this->cacheKey($user), $data, now()->addMinutes($this->expiryMinutes));
            return false;
        }

        // OTP correct → remove
        Cache::forget($this->cacheKey($user));
        Cache::forget($this->resendKey($user));

        return true;
    }

    /**
     * Resend OTP
     */
    public function resend(User $user): void
    {
        if (!$this->canResend($user)) {
            $availableAt = Cache::get($this->resendKey($user));
            $seconds = $availableAt
                ? Carbon::parse($availableAt)->diffInSeconds(now())
                : $this->resendCooldown;

            throw new \Exception("Please wait {$seconds} seconds before resending OTP.");
        }

        $this->generate($user);
    }

    /**
     * Check resend availability
     */
    public function canResend(User $user): bool
    {
        $nextAvailable = Cache::get($this->resendKey($user));
        return !$nextAvailable || now()->greaterThanOrEqualTo(Carbon::parse($nextAvailable));
    }

    /**
     * Generate OTP
     */
    protected function generateRandomOtp(): string
    {
        return str_pad((string) random_int(0, 999999), $this->otpLength, '0', STR_PAD_LEFT);
    }

    protected function cacheKey(User $user): string
    {
        return "otp:user:{$user->id}";
    }

    protected function resendKey(User $user): string
    {
        return "otp:resend:user:{$user->id}";
    }

    /**
     * Send OTP email
     */
    protected function sendOtpEmail(User $user, string $otp): void
    {
        Mail::raw(
            "Your OTP code is {$otp}. It expires in {$this->expiryMinutes} minutes.",
            function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Your OTP Code');
            }
        );
    }
}
