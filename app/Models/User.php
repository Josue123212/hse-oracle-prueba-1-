<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Role;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
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
        //auemntamos
         'role_id',
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

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->role && $this->role->name === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isEncargado(): bool
    {
        $encargadoRoles = [
            'encargado',
            'JEFE QHSE',
            'Médico Ocupacional',
            'Supervisor QHSE',
            'Asistente QHSE',
            'Monitor de Seguridad'
        ];
        return $this->role && in_array($this->role->name, $encargadoRoles);
    }

    public function isLector(): bool
    {
        $lectorRoles = ['lector', 'Asistente General'];
        return $this->role && in_array($this->role->name, $lectorRoles);
    }

    public function isAlumno(): bool
    {
        return $this->hasRole('alumno');
    }
}
