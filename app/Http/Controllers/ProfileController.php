<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Nette\Utils\Json;

class ProfileController extends Controller
{
    //Show user profile
    public function show(Request $request){
        return (new ProfileResource($request->user()))->response()->setStatusCode(200);
    }

    //Save user Avatar
    public function saveAvatar(Request $request){

        $request->validate([
            'avatar' => ['required',' string']
        ]);

        $user = $request->user();
        $base64 = $request->avatar;

        //Se obtiene la parte de la data, eliminando la cabecera del base64
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            $base64 = substr($base64, strpos($base64, ',') + 1);
            $extension = strtolower($type[1]);
        } else {
            throw new \Exception('Invalid image format');
        }

        $base64 = str_replace(' ', '+', $base64);

        //Codifica la imagen base64 a binary
        $image = base64_decode($base64);
        if ($image === false) {
            return response()->json([
                "message" => "Error al guardar la imagen",
            ])->setStatusCode(500);
        }


        //Crear el nombre del archivo 
        $fileName = 'avatars/'.Str::uuid().'.'.$extension;

        //Verificar si ya hay una imagen y eliminarla
        if($user->avatar && Storage::disk('public')->exists($user->avatar)){
            Storage::disk('public')->delete($user->avatar);
        }

        //Guardar la imagen en storage
        if(!Storage::disk('public')->put($fileName, $image)){
            return response()->json([
                "message" => 'Error al guardar el avatar',
            ])->setStatusCode(500);
        }

        //Guardado exitoso, guardar ruta en campo avatar de BD
        $user->avatar = $fileName;
        $user->save();

        //retornar el perfil actualizado
        return (new ProfileResource($user))->response()->setStatusCode(200);
        
    }
}
