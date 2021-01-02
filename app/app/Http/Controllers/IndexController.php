<?php

namespace App\Http\Controllers;

use App\Aggregator\IndexStatisticsAggregator;
use App\Http\Resources\IndexState as IndexStateResource;
use App\Models\Index;
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
        /** @var IndexState $indexState */
        $indexState = IndexState::query()->findOrFail(1)->first();

        switch($request->input('state')){
            case IndexState::STATE_TRIGGERED:
                $indexState->setTriggered();
                break;
            case IndexState::STATE_ABORT:
                $indexState->setAbort();
                break;
            default:
                throw new \InvalidArgumentException('state not allowed');
        }
    }

    public function statistics(): Response
    {
        return new Response(IndexStatisticsAggregator::overallStatistics());
    }

    public function years(): Response
    {
        return new Response(Index::getAllYears());
    }
}
