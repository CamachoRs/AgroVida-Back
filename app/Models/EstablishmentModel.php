<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstablishmentModel extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    protected $table = 'establishment';

    protected $fillable = [
        'nameEstate',
        'sidewalk',
        'municipality',
        'userId'
    ];

    public function user()
    {
        return $this->belongsTo(UserModel::class);
    }
}
