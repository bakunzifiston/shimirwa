<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $users = User::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $total     = User::count();
        $thisMonth = User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $delta     = $thisMonth > 0 ? '+'.($thisMonth).' this month' : 'none this month';
        $pageStats = [
            ['label' => 'Total users',    'value' => $total,     'icon' => 'users', 'color' => 'blue',   'delta' => null],
            ['label' => 'Added this month','value' => $thisMonth, 'icon' => 'calendar', 'color' => 'green',  'delta' => null],
        ];

        return view('admin.users.index', compact('users', 'search', 'pageStats'));
    }

    public function create(): View
    {
        return view('admin.users.create', ['user' => new User]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function show(User $user): View
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->only(['name', 'email']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.show', $user)->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }
}
