<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\URL;

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
                "message" => "Error al codificar la imagen",
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

    //Send Email Verification Notification
    public function sendEmailVerificationNotification(Request $request){
        $user = $request->user();
        if($user->hasVerifiedEmail()){
            return response()->json([
                'message' => 'El email ya está verificado.',
            ])->setStatusCode(200);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            "message" => "Email de verificación enviado correctamente.",
        ])->setStatusCode(200);

    }

    //Verify user email
    public function verifyEmail(Request $request, $id, $hash){
        if(!URL::hasValidSignature($request)){
            dd("PRIMER IF");
            return redirect(config('app.frontend_url').'/email-verified/invalid');
        }

        $user = User::findOrFail($id);

        if(!hash_equals(sha1($user->getEmailForVerification()), $hash)){
            dd("SEGUNDO IF");
            return redirect(config('app.frontend_url').'/email-verified/invalid');
        }

        if(!$user->hasVerifiedEmail()){
            $user->markEmailAsVerified();
        }
       
        return redirect(config('app.frontend_url').'/email-verified');
    }
}
