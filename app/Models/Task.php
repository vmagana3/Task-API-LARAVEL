<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //Crear campos rellenables
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'attachment_path'
    ];

    //Crear la relación con el usuario
    public function user(){
        return $this->belongsTo(User::class);
    }
}
