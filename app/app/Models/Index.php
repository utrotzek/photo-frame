<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Index extends Model
{
    use HasFactory;

    protected $table = 'index';

    protected $fillable = [
        'full_path',
        'file_name',
        'year' ,
        'month' ,
        'base_name',
        'last_indexed'
    ];
}
