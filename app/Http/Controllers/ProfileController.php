<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ProfileResource;

class ProfileController extends Controller
{
    //Show user profile
    public function show(Request $request){
        return (new ProfileResource($request->user()))->response()->setStatusCode(200);
    }
}
