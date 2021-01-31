<?php
namespace App\Slideshow;

use App\Exceptions\InvalidNextActionException;
use App\Models\Slideshow;

class TriggerHandler
{

    /**
     * Triggers the start queue action on the given slideshow
     *
     * @param string $queueTitle
     * @param Slideshow $slideshow
     * @throws InvalidNextActionException
     */
    public function triggerStartQueue(string $queueTitle, Slideshow $slideshow): void
    {
        $action = Slideshow::ACTION_START_QUEUE;
        $this->validateAction($slideshow, $action);

        if ($action === Slideshow::ACTION_START_QUEUE) {
            $slideshow['next_queue_title'] = $queueTitle;
        }
        $slideshow['next_action'] = $action;
        $slideshow->save();
    }

    public function triggerNextAction(string $action, Slideshow $slideshow): void
    {
        $this->validateAction($slideshow, $action);
        $slideshow['next_action'] = $action;
        $slideshow->save();
    }

    protected function validateAction(Slideshow $slideshow, string $action): void
    {
        if ($slideshow['action'] === $action) {
            throw new InvalidNextActionException(sprintf('The action is already %1$s. Triggering an action would have no effect.', $action));
        }

        if ($slideshow['next_action'] === $action) {
            throw new InvalidNextActionException(sprintf('Next action %1$s already triggered.', $action));
        }
    }

}
