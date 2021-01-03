<?php

namespace App\Http\Controllers;

use App\Http\Resources\SlideshowResource;
use App\Models\Slideshow;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SlideshowController extends Controller
{
    public function slideshow(string $device): Response
    {
        return new Response(new SlideshowResource(Slideshow::findByDevice($device)));
    }
    public function triggerNextAction (Request $request, string $device): Response
    {
        $action = $request->input('action');

        if ($action === Slideshow::ACTION_START_QUEUE) {
            if (!$request->has('queue_title')) {
                return new Response('When a new queue start will be triggered the parameter queueTitle is mandatory');
            }
        }

        $slideshow = Slideshow::findByDevice($device);

        if ($slideshow['action'] === $action) {
            return new Response(sprintf('The action is already %1$s. Triggering an action would have no effect.', $action));
        }

        if ($slideshow['next_action'] === $action) {
            return new Response(sprintf('Next action %1$s already triggered.', $action));
        }

        $slideshow['next_action'] = $action;
        if ($action === Slideshow::ACTION_START_QUEUE) {
            $slideshow['next_queue_title'] = $request->input('queue_title');
        }
        $slideshow->save();
        return new Response(sprintf('next action %1$s triggered', $action));
    }

    public function nextActionDone (string $device): Response
    {
        $slideshow = Slideshow::findByDevice($device);

        if (is_null($slideshow['next_action'])){
            return new Response('No pending next action');
        }

        if ($slideshow['next_action'] === Slideshow::ACTION_START_QUEUE) {
            $slideshow['queue_title'] = $slideshow['next_queue_title'];
            $slideshow['action'] = Slideshow::ACTION_PLAY;
        } elseif (
            $slideshow['next_action'] === Slideshow::ACTION_NEXT or
            $slideshow['next_action'] === Slideshow::ACTION_PREV
        ) {
            //do not change currenct action
        } else {
            $slideshow['action'] = $slideshow['next_action'];
        }

        $slideshow['next_action'] = null;
        $slideshow['next_queue_title'] = null;
        $slideshow->save();
        return new Response('next action successfully marked as done');
    }
}
