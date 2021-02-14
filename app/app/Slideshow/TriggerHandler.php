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

    /**
     * Triggers a new duration for the given slideshow
     *
     * @param Slideshow $slideshow
     * @param int $nextDuration
     * @throws InvalidNextActionException
     */
    public function triggerUpdateDuration(Slideshow $slideshow, int $nextDuration): void
    {
        $action = Slideshow::ACTION_UPDATE_SETTINGS_DURATION;

        $slideshow['next_action'] = $action;
        $slideshow['next_duration'] = $nextDuration;
        $slideshow->save();
    }

    /**
     * Triggers the given action to the given slideshow
     *
     * @param string $action
     * @param Slideshow $slideshow
     * @throws InvalidNextActionException
     */
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
    }
}
