<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class CategoryModel extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'categories';

    protected $fillable = [
        'nameCategory',
        'description',
        'status'
    ];

    public function inventory()
    {
        return $this->hasMany(InventoryModel::class);
    }
}
