<?php
namespace App\Slideshow;

use App\Models\Slideshow;

class ActionHandler
{
    /**
     * Handles an existing "next_action" of a slideshow in the database
     *
     * @param Slideshow $slideshow
     */
    public function handle(Slideshow $slideshow)
    {
        switch ($slideshow['next_action']) {
            case Slideshow::ACTION_START_QUEUE:
                $this->handleStartQueue($slideshow);
                break;
            case Slideshow::ACTION_RESTART:
            case Slideshow::ACTION_NEXT:
            case Slideshow::ACTION_PREV:
            case Slideshow::SLIDESHOW_ACTION_RELOAD_CURRENT:
            case Slideshow::SLIDESHOW_ACTION_ADD_FAVORITE:
            case Slideshow::SLIDESHOW_ACTION_REMOVE_FAVORITE:
                $this->handleSimpleAction($slideshow);
                break;
            case Slideshow::ACTION_UPDATE_SETTINGS_DURATION:
                $this->handleUpdateSettingsDuration($slideshow);
                break;
            default:
                $this->handleAction($slideshow);
                break;
        }
    }

    /**
     * Handles the settings duration update task
     *
     * @param Slideshow $slideshow
     */
    protected function handleUpdateSettingsDuration(Slideshow $slideshow)
    {
        $slideshow['duration'] = $slideshow['next_duration'];
        $slideshow['next_action'] = null;
        $slideshow['next_duration'] = null;
        $slideshow->save();
    }

    /**
     * Handles the start queue task
     *
     * @param Slideshow $slideshow
     */
    protected function handleStartQueue(Slideshow $slideshow) {
        $slideshow['queue_title'] = $slideshow['next_queue_title'];
        $slideshow['action'] = Slideshow::ACTION_PLAY;
        $slideshow['next_action'] = null;
        $slideshow['next_queue_title'] = null;
        $slideshow->save();
    }

    /**
     * Sets the current action to the next action and deletes the "next_action"
     * @param Slideshow $slideshow
     */
    protected function handleAction(Slideshow $slideshow): void
    {
        $slideshow['action'] = $slideshow['next_action'];
        $slideshow['next_action'] = null;
        $slideshow->save();
    }

    /**
     * Simple actions are action where the "current action doen't need to be updated.
     * For example: When the next action is triggered, the current action should stay on "play" and should not become
     * next.
     * @param Slideshow $slideshow
     */
    protected function handleSimpleAction(Slideshow $slideshow): void
    {
        $slideshow['next_action'] = null;
        $slideshow->save();
    }

}
