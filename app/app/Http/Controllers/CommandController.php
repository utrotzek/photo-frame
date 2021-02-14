<?php

namespace App\Http\Controllers;

use App\Models\Command;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if (!empty($request->input('view'))) {
            return new Response(
                Command::query()->where('view', $request->input('view'))->get()
            );
        }else {
            return new Response(Command::all()->toArray());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $newCommand = new Command();
        $data = $request->only($newCommand->getFillable());
        $newCommand->fill($data);
        $newCommand->save();
        return new Response($newCommand->fresh());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $command = Command::query()->find($id);

        if ($command) {
            $command->delete();
            return new Response(sprintf('Entry with ID %1$s successfully deleted', $id));
        }else{
            return new Response(sprintf('Command with ID %1$s could not be found', $id));
        }
    }

    public function clearView($view)
    {
        $deletedRows = Command::query()->where('view', $view)->delete();
        if ($deletedRows > 0) {
            return new Response(sprintf('%1$u entries successfully deleted', $deletedRows));
        } else {
            return new Response('no entries deleted');
        }
    }
}
