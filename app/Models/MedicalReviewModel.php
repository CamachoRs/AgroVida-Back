<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class MedicalReviewModel extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'medicalReviews';

    protected $fillable = [
        'animalId',
        'reviewType',
        'observations',
        'reviewerName',
        'medicationName',
        'dose',
        'administrationRoute',
        'file'
    ];

    public function animal()
    {
        return $this->belongsTo(AnimalModel::class, 'animalId');
    }
}
