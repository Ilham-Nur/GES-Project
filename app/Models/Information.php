<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Information extends Model
{
    use HasFactory;

    protected $table = 'tbl_informations'; 
    protected $fillable = [
        'image_informations',
        'title_informations',
        'content_informations',
    ];
}