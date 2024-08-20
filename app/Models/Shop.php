<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    const OPEN_STATUS = 'O';

    const CLOSED_STATUS = 'C';

    protected $fillable = [
        'name',
        'geo_coordinates',
        'status',
        'store_type',
        'max_delivery_distance',
    ];
}
