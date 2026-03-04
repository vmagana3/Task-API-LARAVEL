<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* return parent::toArray($request); */
        return [
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            //TODO: Agregar estadísticas simples(cantidad total de tareas,
            //cantidad tareas finalizadas, usuario desde?)
        ];
    }
}
