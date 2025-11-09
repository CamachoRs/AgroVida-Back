<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstablishmentModel extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    protected $table = 'establishments';

    protected $fillable = [
        'nameEstate',
        'sidewalk',
        'municipality'
    ];

    public function user()
    {
        return $this->hasMany(UserModel::class, 'establishmentId');
    }

    public function inventory()
    {
        return $this->hasMany(InventoryModel::class, 'establishmentId');
    }

    public function animals()
    {
        return $this->hasMany(AnimalModel::class, 'establishmentId');
    }

    public function task(): HasMany
    {
        return $this->hasMany(TaskModel::class, 'establishmentId');
    }
}
