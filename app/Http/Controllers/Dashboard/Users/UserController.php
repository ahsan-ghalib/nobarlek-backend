<?php

namespace App\Http\Controllers\Dashboard\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreUserRequest;
use App\Http\Requests\Dashboard\UpdateUserRequest;
use App\Models\User;
use App\Support\DashboardPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:users.view')->only(['index', 'show']);
        $this->middleware('permission:users.create')->only(['store']);
        $this->middleware('permission:users.update')->only(['update']);
        $this->middleware('permission:users.delete')->only(['destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $users = User::query()
            ->with(['roles', 'permissions'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->latest('id')
            ->paginate(20);

        $users->getCollection()->transform(fn (User $user) => $this->transformUser($user));

        return response()->json([
            'data' => $users,
            'message' => 'Successfully retrieved users',
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'avatar' => $validated['avatar'] ?? '',
        ]);

        $this->syncAccess($user, $request->boolean('is_admin'), $validated['permissions'] ?? []);

        return response()->json([
            'data' => $this->transformUser($user->fresh(['roles', 'permissions'])),
            'message' => 'Successfully user created',
        ], Response::HTTP_CREATED);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => $this->transformUser($user->load(['roles', 'permissions'])),
            'message' => 'Successfully retrieved user',
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();
        $isAdmin = $request->exists('is_admin')
            ? $request->boolean('is_admin')
            : $user->isDashboardAdmin();

        if ($user->isDashboardAdmin() && ! $isAdmin && $this->isLastAdmin($user)) {
            throw ValidationException::withMessages([
                'is_admin' => ['The last administrator cannot be demoted.'],
            ]);
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $user->update($payload);
        $this->syncAccess($user, $isAdmin, $validated['permissions'] ?? []);

        return response()->json([
            'data' => $this->transformUser($user->fresh(['roles', 'permissions'])),
            'message' => 'Successfully user updated',
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()->is($user)) {
            throw ValidationException::withMessages([
                'user' => ['You cannot delete your own account.'],
            ]);
        }

        if ($user->isDashboardAdmin() && $this->isLastAdmin($user)) {
            throw ValidationException::withMessages([
                'user' => ['The last administrator cannot be deleted.'],
            ]);
        }

        $user->syncRoles([]);
        $user->syncPermissions([]);
        $user->delete();

        return response()->json([
            'data' => null,
            'message' => 'Successfully user deleted',
        ]);
    }

    private function syncAccess(User $user, bool $isAdmin, array $permissions): void
    {
        $adminRole = DashboardPermissions::adminRole();

        if ($isAdmin) {
            $user->syncRoles([$adminRole]);
            $user->syncPermissions([]);

            return;
        }

        $user->syncRoles([]);
        $user->syncPermissions($permissions);
    }

    private function isLastAdmin(User $user): bool
    {
        if (! $user->isDashboardAdmin()) {
            return false;
        }

        return User::role(DashboardPermissions::adminRole())->count() <= 1;
    }

    private function transformUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'is_admin' => $user->isDashboardAdmin(),
            'role' => $user->getRoleNames()->first() ?? '',
            'permissions' => $user->dashboardPermissionNames(),
            'created_at' => optional($user->created_at)->format('Y-m-d H:i'),
        ];
    }
}
