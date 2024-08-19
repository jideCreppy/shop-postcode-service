<?php

namespace App\Enums;

enum StoreTypes: string
{
    case TAKE_AWAY = 'takeaway';
    case SHOP = 'shop';
    case RESTAURANT = 'restaurant';
}
