<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queue extends Model
{
    use HasFactory;
    protected $table = 'queue';

    public function index(): BelongsTo
    {
        return $this->belongsTo(Index::class, 'index_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Queue::class, 'parent_id');
    }
}
