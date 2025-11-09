<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class TaskModel extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'tasks';

    protected $fillable = [
        'establishmentId',
        'name',
        'urgency',
        'deadline',
        'description',
        'userId',
        'inventoryId',
        'itemQuantity',
        'descriptionR',
        'FileR',
        'resolvedAt'
    ];

    public function establishment()
    {
        return $this->belongsTo(EstablishmentModel::class, 'establishmentId');
    }

    public function animals()
    {
        return $this->belongsToMany(AnimalModel::class, 'animalTask', 'taskId', 'animalId');
    }

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'userId', 'id');
    }

    public function inventory()
    {
        return $this->belongsTo(InventoryModel::class, 'inventoryId');
    }
}
