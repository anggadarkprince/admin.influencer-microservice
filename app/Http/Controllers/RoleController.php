<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    public function index()
    {
        Gate::authorize('view', 'roles');

        return RoleResource::collection(Role::orderBy('id', 'desc')->get());
    }

    public function store(Request $request)
    {
        Gate::authorize('create', 'roles');

        $role = Role::create($request->only('name'));

        if ($permissions = $request->input('permissions')) {
            foreach ($permissions as $permission_id) {
                DB::table('role_permissions')->insert([
                    'role_id' => $role->id,
                    'permission_id' => $permission_id,
                ]);
            }
        }

        return response(new RoleResource($role), Response::HTTP_CREATED);
    }

    public function show($id)
    {
        Gate::authorize('view', 'roles');

        return new RoleResource(Role::find($id));
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('edit', 'roles');

        $role = Role::find($id);

        $role->update($request->only('name'));

        RolePermission::where('role_id', $role->id)->delete();

        if ($permissions = $request->input('permissions')) {
            foreach ($permissions as $permission_id) {
                DB::table('role_permissions')->insert([
                    'role_id' => $role->id,
                    'permission_id' => $permission_id,
                ]);
            }
        }

        return response(new RoleResource($role), Response::HTTP_ACCEPTED);
    }

    public function destroy($id)
    {
        Gate::authorize('delete', 'roles');

        RolePermission::where('role_id', $id)->delete();

        Role::destroy($id);

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
