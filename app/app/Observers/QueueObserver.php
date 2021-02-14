<?php

namespace App\Observers;

use App\Models\Queue;

class QueueObserver
{
    /**
     * Update the queue chain if a queue item will be removed from the chain
     *
     * @param Queue $queue
     */
    public function deleting(Queue $queue)
    {
        $previous = $queue->getPreviousItem();
        $next = $queue->getNextItem();

        $isCurrent = ($queue['state'] === Queue::STATE_CURRENT);
        if ($next && $previous) {
            $next->parent()->associate($previous);
            $next->save();
        }elseif (!$previous && $next){
            $next->parent()->disassociate();
            $next->save();
        }

        if ($next){
            $next['state'] = ($isCurrent) ? Queue::STATE_CURRENT : Queue::STATE_QUEUED;
            $next->save();
        }elseif ($previous) {
            $previous['state'] = ($isCurrent) ? Queue::STATE_CURRENT : Queue::STATE_QUEUED;
            $previous->save();
        }
    }

    public function deleted(Queue $queue){
        Queue::recountQueue();
    }
}
