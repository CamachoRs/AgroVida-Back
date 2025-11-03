<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class ProductCategoryModel extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'productCategories';

    protected $fillable = [
        'name',
        'description',
        'status'
    ];

    public function inventory()
    {
        return $this->hasMany(InventoryModel::class, 'categoryId');
    }
}
