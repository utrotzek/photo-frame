<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queue extends Model
{
    use HasFactory;
    protected $table = 'queue';

    const STATE_CURRENT = 'current';
    const STATE_QUEUED = 'queued';
    const STATE_DONE = 'done';

    public function index(): BelongsTo
    {
        return $this->belongsTo(Index::class, 'index_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Queue::class, 'parent_id');
    }

    public static function findFirst(): ?Queue
    {
        /** @var Queue $queue */
        $queue = self::query()->whereNull('parent_id')->first();
        return $queue;
    }

    public static function findCurrent(): ?Queue
    {
        /** @var Queue $queue */
        $queue = self::query()->where('state', '=', 'current')->first();
        return $queue;
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
            $nextItem = $lastItem->getNextItem();

            if (!$nextItem) {
                //abort if no nextItem was found
                continue;
            }
            $batch[] = $nextItem ;
            $lastItem = $nextItem;
        }
        return $batch;
    }

    /**
     * Finds the previos $limit queue items depending on the current queue items
     */
    public static function findPreviousBatch(int $limit = 10): array
    {
        $current = self::findCurrent();
        $batch = [];

        $lastItem = $current;
        for ($i=0;$i < $limit; $i++){
            $nextItem = $lastItem->getPreviousItem();

            if (!$nextItem) {
                //abort if no previous item was found
                continue;
            }
            $batch[] = $nextItem ;
            $lastItem = $nextItem;
        }
        return $batch;
    }

    public function getNextItem(): ?Model
    {
        return $this->newQuery()->where('parent_id', '=', $this->id)->first();
    }

    public function getPreviousItem(): ?Model
    {
        if (!is_null($this->parent_id)){
            return $this->newQuery()->where('id', '=', $this->parent_id)->first();
        }
        return null;
    }
}
