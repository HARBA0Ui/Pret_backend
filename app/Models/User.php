<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',                 // 'admin' or 'employee'
        'isEmployeeAccepted',   // Employee can access portal only when true
        'matricule',            // Employee ID
        'salaire',              // Salary
        'date_joined',          // DateTime
        'direction',            // Department (optional)
        'profilePicturePath',   // Stored on local disk (public)
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'date_joined' => 'datetime',
        'isEmployeeAccepted' => 'boolean',
    ];

    // Relationships
    public function demandesAsEmployee()
    {
        return $this->hasMany(AideSociale::class, 'employeeId')
            ->union($this->hasMany(DonsScolaire::class, 'employeeId'))
            ->union($this->hasMany(PretExceptionnel::class, 'employeeId'))
            ->union($this->hasMany(PretHajj::class, 'employeeId'))
            ->union($this->hasMany(SalaireCollectives::class, 'employeeId'))
            ->union($this->hasMany(RemboursementAnticipe::class, 'employeeId'));
    }

    public function demandesAsReviewer()
    {
        return $this->hasMany(AideSociale::class, 'reviewByAdminId')
            ->union($this->hasMany(DonsScolaire::class, 'reviewByAdminId'))
            ->union($this->hasMany(PretExceptionnel::class, 'reviewByAdminId'))
            ->union($this->hasMany(PretHajj::class, 'reviewByAdminId'))
            ->union($this->hasMany(SalaireCollectives::class, 'reviewByAdminId'))
            ->union($this->hasMany(RemboursementAnticipe::class, 'reviewByAdminId'));
    }

    public function prets()
    {
        return $this->hasMany(Pret::class, 'employeeId');
    }

    public function mensualites()
    {
        return $this->hasMany(Mensualite::class, 'employeeId');
    }

    public function attachmentsUploaded()
    {
        return $this->hasMany(Attachment::class, 'uploadedBy');
    }
}
