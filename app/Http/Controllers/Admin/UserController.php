<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a paginated listing of the accounts.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->trim();

                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')->toString()))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => UserRole::ordered(),
            'filters' => $request->only('search', 'role'),
        ]);
    }

    /**
     * Show the form for creating a new account.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', [
            'roles' => UserRole::ordered(),
        ]);
    }

    /**
     * Store a newly created account.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
            'role' => UserRole::from($request->string('role')->toString()),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Account created for {$user->name}.");
    }

    /**
     * Show the form for editing an account.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => UserRole::ordered(),
        ]);
    }

    /**
     * Update an account.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->fill([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'role' => UserRole::from($request->string('role')->toString()),
        ]);

        // Only change the password when a new one was actually supplied.
        if ($request->filled('password')) {
            $user->password = $request->string('password')->toString();
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Account updated for {$user->name}.");
    }

    /**
     * Remove an account.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Account removed for {$name}.");
    }
}
