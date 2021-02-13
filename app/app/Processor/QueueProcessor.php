<?php
declare(strict_types = 1);

namespace App\Processor;

use App\Exceptions\QueueGenerationException;
use App\Models\Index;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use App\Models\Queue;
use Illuminate\Database\Eloquent\Builder;
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

        $this->createQueueByQueryBuilder($queryBuilder, $shuffle);
    }

    public function generateQueueByAlbumList(array $albumList, bool $shuffle)
    {
        $queryBuilder = Index::query();

        foreach ($albumList as $album) {
            $internalPath = str_replace('/images', config('slideshow.imagePath'), $album);
            $queryBuilder = $queryBuilder->orWhere('path', 'LIKE', '\\' . $internalPath . '%');
        }
        $this->createQueueByQueryBuilder($queryBuilder, $shuffle);
    }

    public function generateQueueByPlaylist(Playlist $playlist, bool $shuffle): void
    {
        $queryBuilder = Index::query();

        /** @var PlaylistItem $playlistItems */
        foreach ($playlist->items()->get() as $playlistItems) {
            $path = $playlistItems['path'];
            $queryBuilder = $queryBuilder->orWhere('path', 'LIKE', $path.'%');
        }
        $this->createQueueByQueryBuilder($queryBuilder, $shuffle);
    }

    public function restart()
    {
        Queue::query()->update(['state' => Queue::STATE_QUEUED]);
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
        Queue::query()->update(['state' => Queue::STATE_QUEUED]);
        $next = $current->getNextItem();
        if ($next){
            $next['state'] = QUEUE::STATE_CURRENT;
            $next->save();
        }else {
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
            Queue::query()->update(['state' => Queue::STATE_QUEUED]);
            $previous['state'] = QUEUE::STATE_CURRENT;
            $previous->save();
        }
    }

    protected function createQueueByQueryBuilder(Builder $queryBuilder, bool $shuffle){
        $queryBuilder->orderBy('path');
        $queryBuilder->orderBy('file_name');
        $indexResult = $queryBuilder->get();
        if (!$indexResult) {
            throw new QueueGenerationException('Error while generating the queue (no index entries found)');
        }
        $this->createQueueByCollection($indexResult, $shuffle);
    }

    /**
     * Generates a new queue and writes them to the database
     */
    protected function createQueueByCollection(Collection $indexResult, bool $shuffle = true)
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

        $data = [];
        foreach ($indexArray as $index) {
            $isFirst = ($queueId === 0);
            $data[] = [
                'parent_id' => ($isFirst) ? null : $queueId,
                'state' => ($isFirst) ? 'current': 'queued',
                'index_id' => $index['id'],
                'row_count' => $queueId + 1
            ];
            $queueId++;
        }
        Queue::query()->insert($data);

    }
}
