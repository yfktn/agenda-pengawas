<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'sekolah_id',
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
            'role' => 'string',
        ];
    }

    public function sekolah()
    {
        return $this->belongsTo(MasterSekolah::class, 'sekolah_id');
    }

    public function penugasanSekolah()
    {
        return $this->belongsToMany(MasterSekolah::class, 'penugasan_pengawas', 'user_id', 'master_sekolah_id');
    }

    public function agendas()
    {
        return $this->hasMany(Agenda::class, 'created_by');
    }

    public function agendaPeserta()
    {
        return $this->belongsToMany(Agenda::class, 'agenda_pesertas', 'user_id', 'agenda_id');
    }

    public function pertanyaans()
    {
        return $this->hasMany(Pertanyaan::class, 'user_id');
    }

    public function jawabans()
    {
        return $this->hasMany(Jawaban::class, 'user_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    public function isPengawas(): bool
    {
        return $this->role === 'Pengawas';
    }

    public function isOperatorSekolah(): bool
    {
        return $this->role === 'OperatorSekolah';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->isAdmin(),
            'supervisory' => $this->isPengawas(),
            'school' => $this->isOperatorSekolah(),
            default => false,
        };
    }
}
