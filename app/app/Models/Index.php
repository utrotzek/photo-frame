<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Index extends Model
{
    use HasFactory;

    protected $table = 'index';

    protected $fillable = [
        'path',
        'file_name',
        'year' ,
        'month' ,
        'base_name',
        'last_indexed'
    ];

    public function getFilePath(){
        return "{$this->path}/{$this->file_name}";
    }
}