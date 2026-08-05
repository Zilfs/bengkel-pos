<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'default_price', 'default_commission_percentage', 'is_active'])]
class ServiceType extends Model
{
    //
}
