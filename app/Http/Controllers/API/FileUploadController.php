<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Faker\Provider\Base;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileUploadController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if ($request->hasFile('image')) {
            if ($request->file('image')->isValid()) {
                $validated = $request->validate([
                    'image' => 'mimes:jpeg,png|max:1014',
                ]);
                $extension = $request->image->extension();
                $name = $_SERVER['REQUEST_TIME'];
                $request->image->storeAs('/public', $name.".".$extension);
                $url = Storage::url($name.".".$extension);
                return $this->sendResponse(null, $url);
            }
        }
        return $this->sendError("Upload image error");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
