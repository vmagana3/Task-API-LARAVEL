<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class AuthController extends Controller
{
    //Método para registro
    public function register(Request $request){
        //validar request
        $validatedPayload = $request->validate([
            'name'=>['required', 'string', 'max:100'],
            'email'=>['required', 'email', 'unique:users,email'],
            'password'=>['required', 'regex:^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{8,12}^'],
        ]);
       
        //hashear password
        Hash::make($validatedPayload['password']);

        //crear usuario(en $user se guarda la instancia del modelo User)
        $user = User::create($validatedPayload);

        //crear token para el usuario
        $newAuhtToken = $user->createToken('auth_token')->plainTextToken;
        
        //retornar user + token
        return response()->json([
            "auth_token"=>$newAuhtToken,
            "user"=>$user,
        ]);

    }

    //Método para login
    public function login(Request $request){
        //1.-Vaildar request
        $validatedPayload = $request->validate([
            'email'=>['required', 'string', 'email'],
            'password'=>['required', 'string', 'regex:^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{8,12}^'],
        ]);


        //2.-Traer el usuario del email ingresado(para sacar su campo password)
        $user = User::where('email', $validatedPayload['email'])->first();
 
        //3.-Si no coincide el Hash del password, retornamos credenciales inválidas
        if(!$user || !Hash::check($validatedPayload['password'], $user->password)){
            return response()->json([
                "message"=>"Credenciales inválidas!"
            ]);
        }


        //4.-Si si coincide, regresamos el usuario y le mandamos un nuevo token
        $user->tokens()->delete();
        $newAuhtToken = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            "auth_token"=>$newAuhtToken,
            "user"=>$user,
        ]);

    }

    //Método para logout
    public function logout(Request $request){
        //Eliminar
       $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Se cerró la sesión correctamente'
        ]);
    }
}
