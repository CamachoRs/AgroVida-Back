<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class AnimalCategoryModel extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'animalCategories';

    protected $fillable = [
        'name',
        'description',
        'status'
    ];

    public function news()
    {
        return $this->belongsToMany(NewsModel::class, 'newsAnimalCategories', 'categoryAnimalId', 'newId');
    }
}
