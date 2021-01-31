<?php

namespace App\Models;

use App\Events\QueueDeleted;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Queue extends Model
{
    use HasFactory;
    protected $table = 'queue';


    protected $fillable = [
        'parent_id',
        'index_id',
        'state'
    ];

    const STATE_CURRENT = 'current';
    const STATE_QUEUED = 'queued';

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

    public function getNextItem(): ?Queue
    {
        return Queue::where('parent_id', '=', $this->id)->first();
    }

    public function getPreviousItem(): ?Queue
    {
        if (!is_null($this->parent_id)){
            return Queue::where('id', '=', $this->parent_id)->first();
        }
        return null;
    }

    /**
     * Updates the row_count column of the whole table
     */
    public static function recountQueue()
    {
        //update the row count with a raw query as this is is way faster than the object orientated way
        DB::statement(DB::raw('set @row:=0'));
        DB::statement(DB::raw('update queue set row_count = @row:=@row+1;'));
    }
}
