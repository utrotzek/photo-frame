<?php
declare(strict_types = 1);

namespace App\Processor;

use App\Exceptions\QueueGenerationException;
use App\Models\Index;
use App\Models\Queue;
use Illuminate\Database\Eloquent\Collection;

class QueueProcessor
{

    public function generateQueueByYear(?int $fromYear, ?int $toYear, ?array $excludes, bool $shuffle = true)
    {
        $queryBuilder =Index::query();
        if (!empty($fromYear)) {
            $queryBuilder->where('year', '>=', $fromYear);
        }
        if (!empty($toYear)) {
            $queryBuilder->where('year', '<=', $toYear);
        }
        //TODO: order by date
        $indexResult = $queryBuilder->get();
        if (!$indexResult) {
            throw new QueueGenerationException('Error while generating the queue (no index entries found)');
        }
        $this->createQueue($indexResult, $shuffle);
    }

    /**
     * Generates a new queue and writes them to the database
     */
    protected function createQueue(Collection $indexResult, bool $shuffle = true)
    {
        $indexArray = [];
        if ($indexResult){
            $indexArray = $indexResult->getIterator()->getArrayCopy();
        }
        if ($shuffle) {
            shuffle($indexArray);
        }

        Queue::query()->truncate();
        $lastItem = null;
        foreach ($indexArray as $index) {
            $queue = new Queue();
            $queue->index()->associate($index);
            $queue['state'] = (is_null($lastItem)) ? 'current': 'queued';
            $queue->save();
            $queue->fresh();

            if ($lastItem) {
                $queue->parent()->associate($lastItem);
                $queue->save();
            }
            $lastItem = $queue;
        }
    }
}
