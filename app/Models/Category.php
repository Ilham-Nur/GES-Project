<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'tbl_category';
    protected $fillable = [
        'category_name',
        'minimum_rate',
        'maximum_rate',
        'company_id',
    ];

}
