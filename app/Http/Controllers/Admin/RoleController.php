<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Models\RolePermission;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view', 'roles');

        $query = Role::query();

        if (!empty($q = $request->get('search'))) {
            $columns = Schema::getColumnListing('roles');
            $query->where(function (Builder $query) use ($q, $columns) {
                foreach ($columns as $column) {
                    $search = $q;
                    if (in_array(DB::getSchemaBuilder()->getColumnType('roles', $column), ['date', 'datetime'])) {
                        try {
                            $search = Carbon::parse($q)->format('Y-m-d');
                        } catch (InvalidFormatException $e) {
                        }
                    }
                    $query->orWhere($column, 'LIKE', '%' . trim($search) . '%');
                }
            });
        }

        return RoleResource::collection($query->orderBy('id', 'desc')->get());
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
