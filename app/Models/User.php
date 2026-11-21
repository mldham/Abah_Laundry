<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const PERAN_ADMIN = 'admin';
    public const PERAN_OPERATOR = 'operator';

    public const LABEL_PERAN = [
        self::PERAN_ADMIN => 'Admin',
        self::PERAN_OPERATOR => 'Operator',
    ];

    // peran dan aktif sengaja tidak di sini: input form tidak boleh bisa menaikkan hak akses.
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
            'aktif' => 'boolean',
        ];
    }

    public function punyaPeran(string ...$peran): bool
    {
        return in_array($this->peran, $peran, true);
    }

    public function adalahAdmin(): bool
    {
        return $this->punyaPeran(self::PERAN_ADMIN);
    }

    public function getLabelPeranAttribute(): string
    {
        return self::LABEL_PERAN[$this->peran] ?? ucfirst((string) $this->peran);
    }
}
