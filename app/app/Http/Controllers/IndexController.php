<?php

namespace App\Http\Controllers;

use App\Aggregator\IndexDirectoriesAggregator;
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

    public function toggleFavorite(Index $index)
    {
        $index['favorite'] = !$index['favorite'];
        $index->save();

        if ($index['favorite']){
            return new Response('Successfully marked as favorite');
        }else{
            return new Response('Favorite successfully unmarked');
        }
    }

    /**
     * Deletes the given file
     *
     * @param Index $index
     * @return Response
     * @throws \Exception
     */
    public function deleteImage(Index $index): Response
    {
        $filePath = $index['path'].'/'.$index['file_name'];
        if (file_exists($filePath)) {
            $index->delete();
            unlink($filePath);
            return new Response(sprintf('The file "%1$s" was successfully deleted.', $filePath));
        }else{
            return new Response(sprintf('The file "%1$s" could not be found.', $filePath),404);
        }
    }

    public function directories(Request $request) {
        return new Response(IndexDirectoriesAggregator::getIndexedDirectories());
    }

    public function statistics(): Response
    {
        return new Response(IndexStatisticsAggregator::overallStatistics());
    }

    public function years(): Response
    {
        return new Response(Index::getAllYears());
    }

    public function favorites(): Response
    {
        return new Response( IndexStatisticsAggregator::favoritesCount());
    }
}
