<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    protected $guarded = [
        'id',
        'approved',
        'approved_by',
        'approved_at',
        'otp_code',
        'otp_expires_at',
        'otp_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'approved_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'otp_verified' => 'boolean',
        'approved' => 'boolean',
    ];

    public function approvedByAdmin()
    {
        return $this->belongsTo(User::class, 'approved_by')
            ->whereHas('roles', fn ($q) => $q->where('name', 'admin'));
    }

    public function otpIsValid(string $otp): bool
    {
        return $this->otp_code &&
               $this->otp_expires_at &&
               Hash::check($otp, $this->otp_code) &&
               now()->lt($this->otp_expires_at);
    }

    public function canLogin(): bool
    {
        return $this->approved && $this->otp_verified;
    }

    public function markOtpVerified(): void
    {
        $this->update([
            'otp_verified' => true,
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);
    }

    protected static function booted()
    {
        static::updated(function ($user) {
            if ($user->isDirty('approved')) {
                Log::info('User approval changed', [
                    'user_id' => $user->id,
                    'approved' => $user->approved,
                    'changed_by' => Auth::id(),
                ]);
            }

            if ($user->isDirty('otp_verified') && $user->otp_verified) {
                Log::info('OTP verified', [
                    'user_id' => $user->id,
                ]);
            }
        });
    }
}
