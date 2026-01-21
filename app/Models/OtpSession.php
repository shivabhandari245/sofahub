<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OtpSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'device',
        'otp_code',
        'attempts',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'attempts' => 'integer',
    ];

    // Relation to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Check if OTP session is expired
    public function isExpired(): bool
    {
        return $this->expires_at->lt(Carbon::now());
    }

    // Increment attempts and optionally lock if exceeds limit
    public function incrementAttempts(int $maxAttempts = 5): bool
    {
        $this->increment('attempts');

        return $this->attempts >= $maxAttempts;
    }
}
