<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'username',
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
        ];
    }

    /**
     * Relación muchos a muchos con roles
     * Debe coincidir con la tabla pivot 'roles_user'
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'roles_user', 'user_id', 'role_id')->withTimestamps();
    }

    /**
     * Relación muchos a muchos con colegios
     */
    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'user_school', 'user_id', 'school_id')->withTimestamps();
    }

    /**
     * Helper para verificar si el usuario tiene un rol específico
     */
    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Helper para verificar si el usuario es admin
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Helper para verificar si el usuario es coordinador
     */
    public function isCoordinator()
    {
        return $this->hasRole('coordinador');
    }

    /**
     * Helper para verificar si el usuario es profesor
     */
    public function isTeacher()
    {
        return $this->hasRole('profesor');
    }

    /**
     * Helper para verificar si el usuario es estudiante
     */
    public function isStudent()
    {
        return $this->hasRole('estudiante');
    }
}