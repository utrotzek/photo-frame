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

        $queryBuilder->orderBy('path');
        $queryBuilder->orderBy('file_name');
        //TODO: order by date (date is missing currently)
        $indexResult = $queryBuilder->get();
        if (!$indexResult) {
            throw new QueueGenerationException('Error while generating the queue (no index entries found)');
        }
        $this->createQueue($indexResult, $shuffle);
    }

    public function restart()
    {
        $current = Queue::findCurrent();
        if ($current != null) {
            $current['state'] = Queue::STATE_QUEUED;
            $current->save();
        }

        $first = Queue::query()->first();
        $first['state'] = Queue::STATE_CURRENT;
        $first->save();
    }

    public function moveForward()
    {
        $current = Queue::findCurrent();

        if ($current === null) {
            $this->restart();
            return;
        }

        $next = $current->getNextItem();

        if ($next){
            $current['state'] = QUEUE::STATE_DONE;
            $current->save();
            $next['state'] = QUEUE::STATE_CURRENT;
            $next->save();
        }else {
            $current['state'] = QUEUE::STATE_DONE;
            $current->save();

            $first = Queue::findFirst();
            $first['state'] = QUEUE::STATE_CURRENT;
            $first->save();
        }
    }

    public function moveBackward()
    {
        $current = Queue::findCurrent();
        if ($current === null) {
            $this->restart();
            return;
        }
        $previous = $current->getPreviousItem();

        if ($previous){
            $current['state'] = QUEUE::STATE_QUEUED;
            $current->save();
            $previous['state'] = QUEUE::STATE_CURRENT;
            $previous->save();
        }
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
        $queueId = 0;
        foreach ($indexArray as $index) {
            $isFirst = ($queueId === 0);
            Queue::create([
                'parent_id' => ($isFirst) ? null : $queueId,
                'state' => ($isFirst) ? 'current': 'queued',
                'index_id' => $index['id']
            ]);
            $queueId++;
        }
    }

}
