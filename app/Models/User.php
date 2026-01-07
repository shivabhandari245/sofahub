<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
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

    // Relation to admin who approved
    public function approvedByAdmin()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // OTP validation helper
    public function otpIsValid(string $otp): bool
    {
        return $this->otp_code &&
               $this->otp_expires_at &&
               Hash::check($otp, $this->otp_code) &&
               now()->lt($this->otp_expires_at);
    }

    // Can user login (approved & OTP verified)
    public function canLogin(): bool
    {
        return $this->approved && $this->otp_verified;
    }

    // Invalidate OTP after use
    public function markOtpVerified()
    {
        $this->update([
            'otp_verified' => true,
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);
    }

    // Audit logging for sensitive changes
    protected static function booted()
    {
        static::updated(function ($user) {
            if ($user->isDirty('approved')) {
                Log::info("User {$user->id} approved status changed by admin.");
            }
            if ($user->isDirty('otp_verified') && $user->otp_verified) {
                Log::info("OTP verified for user {$user->id}");
            }
        });
    }
}
