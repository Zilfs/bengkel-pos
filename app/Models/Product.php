<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'sku', 'selling_price', 'cost_price', 'stock_quantity', 'unit', 'is_active'])]
class Product extends Model
{
    use SoftDeletes;
}
