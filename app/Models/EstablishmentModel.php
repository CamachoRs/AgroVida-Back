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
        return $this->hasMany(UserModel::class);
    }

    public function inventory()
    {
        return $this->hasMany(InventoryModel::class);
    }
}
