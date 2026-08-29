<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PenggunaController extends Controller
{
    /**
     * Display a listing of internal vendor team members.
     */
    public function index(Request $request): Response
    {
        $currentUser = $request->user();
        if (! $currentUser || ! $currentUser->isSuperAdmin()) {
            abort(403, 'Akses terbatas hanya untuk Super Administrator Vendor.');
        }

        $query = User::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $stats = [
            'total' => User::count(),
            'super_admin' => User::where('role', 'super_admin')->count(),
            'sales' => User::where('role', 'sales')->count(),
            'support' => User::where('role', 'support')->count(),
        ];

        return Inertia::render('admin/pengguna/Index', [
            'users' => $users,
            'stats' => $stats,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    /**
     * Store a newly created vendor team member.
     */
    public function store(Request $request): RedirectResponse
    {
        $currentUser = $request->user();
        if (! $currentUser || ! $currentUser->isSuperAdmin()) {
            abort(403, 'Akses terbatas hanya untuk Super Administrator Vendor.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'role' => ['required', 'string', 'in:super_admin,sales,support'],
            'phone' => ['nullable', 'string', 'max:25'],
            'password' => ['required', 'string', Password::min(8)],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->back()->with('success', "Akun tim '{$validated['name']}' berhasil ditambahkan.");
    }

    /**
     * Update the specified vendor team member.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $currentUser = $request->user();
        if (! $currentUser || ! $currentUser->isSuperAdmin()) {
            abort(403, 'Akses terbatas hanya untuk Super Administrator Vendor.');
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'string', 'in:super_admin,sales,support'],
            'phone' => ['nullable', 'string', 'max:25'],
            'password' => ['nullable', 'string', Password::min(8)],
        ]);

        // Pencegahan: Super admin tidak boleh menurunkan role dirinya sendiri jika satu-satunya super_admin
        if ($user->id === $currentUser->id && $validated['role'] !== 'super_admin') {
            $superAdminCount = User::where('role', 'super_admin')->count();
            if ($superAdminCount <= 1) {
                return redirect()->back()->with('error', 'Tidak dapat mengubah role akun ini karena merupakan satu-satunya Super Admin aktif.');
            }
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->phone = $validated['phone'] ?? null;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->back()->with('success', "Data akun tim '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified vendor team member.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $currentUser = $request->user();
        if (! $currentUser || ! $currentUser->isSuperAdmin()) {
            abort(403, 'Akses terbatas hanya untuk Super Administrator Vendor.');
        }

        if ($currentUser->id === $id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.');
        }

        $user = User::findOrFail($id);
        $name = $user->name;
        $user->delete();

        return redirect()->back()->with('success', "Akun tim '{$name}' telah dihapus dari sistem.");
    }
}
