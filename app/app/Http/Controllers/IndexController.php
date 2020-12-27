<?php

namespace App\Http\Controllers;

use App\Http\Resources\IndexState as IndexStateResource;
use App\Models\IndexState;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IndexController extends Controller
{
    /**
     * @return Response
     */
    public function state() {
        return new Response(
            new IndexStateResource(
                IndexState::query()->findOrFail(1)->get()->first()
            )
        );
    }

    public function update(Request $request ) {
        switch($request->input('state')){
            case IndexState::STATE_TRIGGERED:
            case IndexState::STATE_ABORT:
                $indexState = IndexState::query()->findOrFail(1)->first();
                if ($indexState['state'] === IndexState::STATE_WAITING || $indexState['state'] === IndexState::STATE_FAILED) {
                    $indexState['state'] = $request->input('state');
                    $indexState->save();
                }
                break;
            default:
                throw new \InvalidArgumentException('state not allowed');
        }
    }
}
