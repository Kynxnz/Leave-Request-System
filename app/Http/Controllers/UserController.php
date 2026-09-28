<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = User::with('manager')->latest();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $q->where(fn ($x) => $x
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        if ($request->filled('role')) {
            $q->where('role', $request->string('role')->toString());
        }

        $managers = User::query()
            ->where('role', 'Manager')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        $unassignedStaff = User::query()
            ->where('role', 'Staff')
            ->where('status', 'active')
            ->whereNull('manager_id')
            ->count();

        return view('users.index', [
            'users' => $q->paginate(15)->withQueryString(),
            'managers' => $managers,
            'unassignedStaff' => $unassignedStaff,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', 'in:Administrator,Manager,Staff'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if ($user->isAdmin() && $user->id !== auth()->id() && $data['role'] !== 'Administrator') {
            abort(422, 'Administrator accounts cannot be demoted.');
        }

        if ($user->id === auth()->id()) {
            $data['role'] = 'Administrator';
            $data['status'] = 'active';
            $data['manager_id'] = null;
        }

        if ($data['role'] === 'Staff') {
            $managerId = $data['manager_id'] ?? null;

            if ($managerId === $user->id) {
                abort(422, 'A user cannot manage themselves.');
            }

            if ($managerId !== null) {
                $manager = User::find($managerId);

                abort_if(
                    !$manager || !$manager->isManager() || $manager->status !== 'active',
                    422,
                    'Assigned Manager must be an active Manager account.'
                );
            }
        } else {
            if ($user->isManager() && $data['role'] !== 'Manager' && $user->reports()->exists()) {
                abort(422, 'Reassign this Manager’s Staff before changing the Manager role.');
            }

            $data['manager_id'] = null;
        }

        if (blank($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return back()->with('success', 'User account updated successfully.');
    }
}
