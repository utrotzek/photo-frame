<?php

namespace App\Http\Controllers;

use App\Models\IndexState;
use Illuminate\Http\Response;

class IndexController extends Controller
{
    /**
     * @return Response
     */
    public function state() {
        try {
            return new Response(IndexState::query()->firstOrFail()->get());
        } catch (\Exception $exception) {
            $state = new IndexState(['state' => 'waiting']);
            $state->save();
            return new Response($state->fresh());
        }
    }
}
