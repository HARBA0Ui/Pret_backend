<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'nom',
        'email',
        'password',
        'role',                 // 'admin' or 'employee'
        'matricule',            // Employee ID
        'salaire',              // Salary
        'date_joined',          // DateTime
        'direction',            // Department (optional)
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'date_joined' => 'datetime',
    ];

    // Relationships
    public function demandesAsEmployee()
    {
        return $this->hasMany(AideSociale::class, 'employeeId')
                    ->union($this->hasMany(DonsScolaire::class, 'employeeId'))
                    ->union($this->hasMany(SalaireCollectives::class, 'employeeId'))
                    ->union($this->hasMany(RemboursementAnticipe::class, 'employeeId'));
    }

    public function demandesAsReviewer()
    {
        return $this->hasMany(AideSociale::class, 'reviewByAdminId')
                    ->union($this->hasMany(DonsScolaire::class, 'reviewByAdminId'))
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
