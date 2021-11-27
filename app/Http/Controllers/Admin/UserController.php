<?php

namespace App\Http\Controllers\Admin;

use App\Events\AdminAddedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateInfoRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserRole;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view', 'users');

        $query = User::query();

        if (!empty($q = $request->get('search'))) {
            $columns = Schema::getColumnListing('users');
            $query->where(function (Builder $query) use ($q, $columns) {
                foreach ($columns as $column) {
                    $search = $q;
                    if (in_array(DB::getSchemaBuilder()->getColumnType('users', $column), ['date', 'datetime'])) {
                        try {
                            $search = Carbon::parse($q)->format('Y-m-d');
                        } catch (InvalidFormatException $e) {
                        }
                    }
                    $query->orWhere($column, 'LIKE', '%' . trim($search) . '%');
                }
            });
        }
        if (!empty($roleId = $request->get('role'))) {
            $query->where('role_id', $roleId);
        }

        return UserResource::collection($query->orderBy('created_at', 'desc')->paginate());
    }

    public function show($id)
    {
        Gate::authorize('view', 'users');

        $user = User::find($id);

        return new UserResource($user);
    }

    public function store(UserCreateRequest $request)
    {
        Gate::authorize('create', 'users');

        $user = User::create($request->only('first_name', 'last_name', 'email')
            + ['password' => Hash::make($request->input('password', 1234))]
        );

        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $request->input('role_id')
        ]);

        event(new AdminAddedEvent($user));

        return response(new UserResource($user), Response::HTTP_CREATED);
    }

    public function update(UserUpdateRequest $request, $id)
    {
        Gate::authorize('edit', 'users');

        $user = User::find($id);

        $user->update($request->only(['first_name', 'last_name', 'email']));

        UserRole::where('user_id', $user->id)->delete();

        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $request->input('role_id')
        ]);

        return response(new UserResource($user), Response::HTTP_ACCEPTED);
    }

    public function destroy($id)
    {
        Gate::authorize('delete', 'users');

        User::destroy($id);

        return response(null, Response::HTTP_NO_CONTENT);
    }

}
