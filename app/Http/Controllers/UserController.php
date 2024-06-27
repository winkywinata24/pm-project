<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = User::query();
        $sortField = request("sort_field", 'created_at');
        $sortDirection = request("sort_direction", "desc");
        if (request("name")){
            $query->where("name","like","%". request("name") ."%");
        }
        if (request("status")){
            $query->where("status", request("status"));
        }
        $users = $query->orderBy($sortField, $sortDirection)->paginate(10);
        return inertia("User/Index",[
            "users" => UserResource::collection($users),
            'queryParams' => request()->query() ?: null,
            'success' =>session('success'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia("User/Create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        User::create($data);
        return to_route('user.index')
        ->with('success', 'User was created');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $query = $user->tasks();
        $sortField = request("sort_field", 'created_at');
        $sortDirection = request("sort_direction", "desc");
        if (request("name")){
            $query->where("name","like","%". request("name") ."%");
        }
        if (request("status")){
            $query->where("status", request("status"));
        }
        $tasks = $query->orderBy($sortField, $sortDirection)->paginate(10);
        return inertia("User/Show",[
            "user" => new UserResource($user),
            'queryParams' => request()->query() ?: null,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return inertia('User/Edit', [
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $name = $user->name;
        $data = $request->validated();
        $user->update($data);
        return to_route('user.index')
        ->with('success', "User \"$name\" was updated");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $name = $user->name;
        $user->delete();
        if ($user->image_path) {
            Storage::disk('public')->delete($user->image_path);
        }
        return to_route('user.index')
        ->with('success', "User \"$name\" was deleted");
    }
}
