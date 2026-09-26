<?php

namespace App\Models;

use App\Support\Concerns\HasUuid;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, HasUuid, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            // No 'encrypted' cast here: Fortify's TwoFactorAuthenticatable
            // trait already encrypts/decrypts these two columns itself via
            // Fortify::currentEncrypter() (Laravel's APP_KEY-based encrypter
            // by default), satisfying spec §11. Adding an Eloquent cast on
            // top would double-encrypt on every write.
            'password' => 'hashed',
        ];
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class)->latest();
    }

    public function member(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_secret !== null && $this->two_factor_confirmed_at !== null;
    }

    /**
     * WhatsApp target for the Fonnte notification channel (spec §19), normalized
     * to Fonnte's expected local-country-code format (e.g. 08123... -> 628123...).
     */
    public function routeNotificationForFonnte(): ?string
    {
        $phone = $this->member?->phone;

        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        return str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;
    }

    /**
     * Avatar URL: uploaded file (storage/app/public) or a generic placeholder.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            if (str_starts_with($this->avatar, 'http')) {
                return $this->avatar;
            }

            return Storage::disk('public')->url($this->avatar);
        }

        return asset('assets/img/team-1.jpg');
    }
}
