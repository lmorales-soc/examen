<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'cliente',
        'producto',
        'cantidad',
        'precio_unitario',
        'fecha',
        'total',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'precio_unitario' => 'decimal:2',
        'total' => 'decimal:2',
        'cantidad' => 'integer',
    ];
}
