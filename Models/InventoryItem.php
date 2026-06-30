<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'material',
        'stock',
        'reposicion',
        'costo',
        'proveedor',
    ];

    protected $casts = [
        'stock' => 'integer',
        'reposicion' => 'integer',
        'costo' => 'decimal:2',
    ];

    public function needsRestock(): bool
    {
        return $this->stock <= $this->reposicion;
    }

    public function inventoryValue(): float
    {
        return $this->stock * (float) $this->costo;
    }
}
