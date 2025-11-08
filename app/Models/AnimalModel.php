<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AnimalModel extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'animals';

    protected $fillable = [
        'establishmentId',
        'categoryId',
        'name',
        'sex',
        'healthStatus',
        'ageRange',
        'weight',
        'observations',
        'image',
        'status'
    ];

    public function animalCategory()
    {
        return $this->belongsTo(AnimalCategoryModel::class, 'categoryId');
    }

    public function establishment()
    {
        return $this->belongsTo(EstablishmentModel::class, 'establishmentId');
    }

    public function medicalReviews()
    {
        return $this->hasMany(MedicalReview::class, 'animalId');
    }
}
