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


    public static function findCurrent(): ?Model
    {
        return self::query()->where('state', '=', 'current')->first();
    }

    /**
     * Finds the next $limit queue items depdending on the current queue items
     */
    public static function findNextBatch(int $limit = 10): array
    {
        $current = self::findCurrent();
        $batch = [];

        $lastItem = $current;
        for ($i=0;$i < $limit; $i++){
            $nextItem = self::query()->where('parent_id', '=', $lastItem->id)->first();

            if ($nextItem){
                $batch[] = $nextItem ;
                $lastItem = $nextItem;
            }
        }
        return $batch;
    }
}
