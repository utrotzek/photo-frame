<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidNextActionException;
use App\Http\Resources\SlideshowResource;
use App\Models\Slideshow;
use App\Slideshow\ActionHandler;
use App\Slideshow\TriggerHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SlideshowController extends Controller
{
    /**
     * Returns the slideshow object for the given device
     *
     * @param string $device
     * @return Response
     */
    public function slideshow(string $device): Response
    {
        return new Response(new SlideshowResource(Slideshow::findByDevice($device)));
    }

    /**
     * Triggers a next_action in the database
     *
     * @param Request $request
     * @param string $device
     * @param TriggerHandler $triggerHandler
     * @return Response
     */
    public function triggerNextAction (Request $request, string $device, TriggerHandler $triggerHandler): Response
    {
        $action = $request->input('action');
        $slideshow = Slideshow::findByDevice($device);

        try {
            if ($action === Slideshow::ACTION_START_QUEUE) {
                if (!$request->has('queue_title')) {
                    return new Response('When a new queue start will be triggered the parameter queueTitle is mandatory');
                }
                $triggerHandler->triggerStartQueue($request->input('queue_title'), $slideshow);
            }

            $triggerHandler->triggerNextAction($action, $slideshow);
        } catch (InvalidNextActionException $exception) {
            return new Response($exception->getMessage(), 500);
        }

        return new Response(sprintf('next action %1$s triggered', $action));
    }

    /**
     * An existing next_action in the database will be marked as "done"
     *
     * @param string $device
     * @param ActionHandler $actionHandler
     * @return Response
     */
    public function nextActionDone (string $device, ActionHandler $actionHandler): Response
    {
        $slideshow = Slideshow::findByDevice($device);

        if (is_null($slideshow['next_action'])){
            return new Response('No pending next action');
        }
        $actionHandler->handle($slideshow);
        return new Response('next action successfully marked as done');
    }
}
