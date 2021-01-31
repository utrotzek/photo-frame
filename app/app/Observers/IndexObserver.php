<?php

namespace App\Observers;

use App\Models\Index;
use App\Models\Queue;

class IndexObserver
{
    /**
     * delete all queue entries before deleting the index entry itself. Calling the delete
     * function directly makes sure that the deleting hooks are triggered as well
     *
     * @param Index $index
     * @throws \Exception
     */
    public function deleting(Index $index)
    {
        /** @var Queue $queue */
        foreach ($index->queues()->get() as $queue){
            $queue->delete();
        }
    }
}
