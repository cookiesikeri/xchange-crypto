<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GiftcardController extends Controller
{
    public function upload(Request $request){
        $request->validate([
            'csv' => 'required|file'
        ]);

        $file = new \App\File();
        $file['original_name'] = $request['csv']->getClientOriginalName();
        $file['user_id'] = Auth::user()['id'];
        $file['path'] = 'files';
        $file['name'] = Str::random(32).'.'.\File::extension($file['original_name']);
        $request['csv']->storeAs($file['path'],$file['name']);
        $file->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Upload Success. File processing started.',
            'location' => '/wizard'
        ]);
    }
}
