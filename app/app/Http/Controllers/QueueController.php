<?php

namespace App\Http\Controllers;

use App\Http\Resources\QueueResource;
use App\Models\Queue;
use App\Processor\QueueProcessor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QueueController extends Controller
{
    protected QueueProcessor $queueProcessor;

    public function __construct(QueueProcessor $queueProcessor)
    {
        $this->queueProcessor = $queueProcessor;
    }

    public function current()
    {
        return new Response(
            new QueueResource(
                Queue::findCurrent()
            )
        );
    }

    public function nextBatch(Request $request)
    {
        $limit = $request->input('limit') ?? 10;
        return new Response(
            QueueResource::collection(
                Queue::findNextBatch($limit)
            )
        );
    }

    public function create(Request $request): Response{
        if (is_null($request->input('type'))){
            return new Response('Required parameter \'type\' is given');
        }

        switch ($request->input('type')){
            case 'year':
                $this->queueProcessor->generateQueueByYear(
                    (int)$request->input('fromYear'),
                    (int)$request->input('toYear'),
                    $request->input('excludes')
                );
                break;
            case 'playlist':
                throw new \Exception("Not implemented");
            case 'folder':
                throw new \Exception("Not implemented");
            default:
                return new Response(
                    sprintf(
                        'The given type \'%1$s\' is not defined',
                        $request->input('type')
                    ),
                    501
                );
        }

        return new Response('Successfully created');
    }
}
