<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'firebase_uid',
        'auth_provider',
        'password',
        'is_online',
        'last_seen_at', // Ditambahkan agar bisa diupdate oleh middleware
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'password' => 'hashed',
            'is_online' => 'boolean',
            'last_seen_at' => 'datetime', // WAJIB: Agar Filament bisa menghitung selisih waktu (Online/Offline)
        ];
    }

    /**
     * Izinkan akses ke panel Filament.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * Helper untuk mengecek apakah user sedang online.
     * Menganggap user online jika aktif dalam 5 menit terakhir.
     */
    public function isOnline(): bool
    {
        return (bool) $this->is_online;
    }

    public function vouchers(): BelongsToMany
    {
        return $this->belongsToMany(Voucher::class, 'user_vouchers')
            ->withPivot(['claimed_at', 'used_at'])
            ->withTimestamps();
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }
}
