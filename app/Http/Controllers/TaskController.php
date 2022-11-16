<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    //
    public function index()
    {
        $tasks = Task::with('files')
            ->get();
        return response()->json($tasks);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'files' => 'required|array',
            'files.*' => 'required|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,jpeg,jpg,png,gif,svg |max:6000',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }


        return DB::transaction(function () use ($request) {
            $userLog = auth()->user()->id;

            $task = Task::create([
                'title' => $request->title,
                'description' => $request->description,
                'created_by' => $userLog,
                'updated_by' => $userLog,
            ]);
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('public/files');
                    $file = File::create([
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'task_id' => $task->id,
                        'created_by' => $userLog,
                        'updated_by' => $userLog,
                    ]);
                }
            }
            return response()->json($task);
        }, 5);
    }
    public function show($id)
    {
        $task = Task::with('files')
            ->find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        return response()->json($task);
    }
    public function update(Request $request, $id)
    {
        $task = Task::find($id);
        $task->update($request->all());
        return response()->json($task);
    }

    public function destroy($id)
    {
        $task = Task::find($id);
        // $file = File::where('task_id', $task->id);
        // $file->delete();
        if (!$task) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        $task->delete();
        return response()->json('Eliminado');
    }
}
