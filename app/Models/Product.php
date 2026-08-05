<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'sku', 'selling_price', 'cost', 'stock_quantity', 'unit', 'is_active'])]
class Product extends Model
{
    //
}
