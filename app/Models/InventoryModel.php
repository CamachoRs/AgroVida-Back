<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class InventoryModel extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'inventories';

    protected $fillable = [
        'establishmentId',
        'categoryId',
        'nameItem',
        'quantity',
        'unitMeasurement',
        'entryDate',
        'expiryDate',
        'supplierName',
    ];

    public function establishment()
    {
        return $this->belongsTo(EstablishmentModel::class, 'establishmentId');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategoryModel::class, 'categoryId');
    }
}
