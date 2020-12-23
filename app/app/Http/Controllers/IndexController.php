<?php

namespace App\Http\Controllers;

use App\Http\Resources\IndexState as IndexStateResource;
use App\Models\IndexState;
use Illuminate\Http\Response;

class IndexController extends Controller
{
    /**
     * @return Response
     */
    public function state() {
        try {
            return new Response(
                new IndexStateResource(
                    IndexState::query()->findOrFail(1)->get()->first()
                )
            );
        } catch (\Exception $exception) {
            $state = new IndexState(['state' => 'waiting']);
            $state->save();
            return new Response($state->fresh());
        }
    }
}
