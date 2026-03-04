<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    //Listar todas las tareas
    public function index(Request $request){
        $tasksList = Task::where('user_id', $request->user()->id)->paginate(5);
        return TaskResource::collection($tasksList)->response()->setStatusCode(200);
    }

    //Crear una tarea
    public function store(Request $request){
        $validatedPayload = $request->validate([
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'completed'])],
            'due_date' => ['nullable', 'date', Rule::date()->afterOrEqual(today())],
            'attachment_path' => ['nullable', 'string'],
        ]);

         //Usar la relación task para crear la tarea(asigna automaticamente el user_id)
        $newTask = $request->user()->tasks()->create([
            ...$validatedPayload,
            'status' => 'pending',
        ]);
        return (new TaskResource($newTask))->response()->setStatusCode(201);

        //Agregar el user_id al crear la task
        /* $newTask = Task::create([
            ...$validatedPayload,
            'user_id' => $request->user()->id,
        ]); */
       
    }

    //Mostrar una tarea
    public function show(Request $request, $id){
        $task = Task::where('user_id', $request->user()->id)
                    ->where('id', $id)
                    ->firstOrFail();

        return (new TaskResource($task))->response()->setStatusCode(200);
    }

    //Actualizar una tarea
    public function update(Request $request, $id){
        $validatedPayload = $request->validate([
            'title' => ['sometimes', 'string'],
            'description' => ['sometimes','nullable', 'string'],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high'])],
            'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'completed'])],
            'due_date' => ['sometimes','nullable', 'date', Rule::date()->afterOrEqual(today())],
            'attachment_path' => ['sometimes','nullable', 'string'],
        ]);

        $taskToUpdate = Task::where('user_id', $request->user()->id)
                            ->where('id', $id)
                            ->firstOrFail();

        $taskToUpdate->update($validatedPayload);

        return (new TaskResource($taskToUpdate))->response()->setStatusCode(200);
    }

    //Eliminar una tarea
    public function destroy(Request $request, $id){
        Task::destroy($id);
        return response([
            "message" => "Tarea eliminada correctamente",
        ]);
    }
}
