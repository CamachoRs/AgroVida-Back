<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class NewsModel extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'news';

    protected $fillable = [
        'establishmentId',
        'userId',
        'title',
        'description',
        'image',
        'status'
    ];

    public function animalCategory()
    {
        return $this->belongsToMany(AnimalCategoryModel::class, 'newsAnimalCategories', 'newId', 'categoryAnimalId');
    }

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'userId');
    }
}
