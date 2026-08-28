<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * GET /api/users
     * Hanya bisa diakses oleh role: administrator
     */
    public function index(Request $request)
    {
        // Otoritas: Pastikan hanya administrator yang bisa mengakses
        if ($request->user()->role !== 'administrator') {
            return response()->json(['message' => 'Akses ditolak. Hanya administrator yang dapat mengelola pengguna.'], 403);
        }

        $query = User::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        $perPage = min((int) $request->query('per_page', 10), 100);
        $data = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => collect($data->items())->map(fn($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'role'       => $u->role ?? 'operator',
                'created_at' => $u->created_at,
            ]),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
                'total_admin'  => User::where('role', 'administrator')->count(),
                'total_operator' => User::where('role', 'operator')->count(),
            ],
        ]);
    }

    /**
     * POST /api/users
     */
    public function store(Request $request)
    {
        if ($request->user()->role !== 'administrator') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => ['required', Rule::in(['administrator', 'operator'])],
        ], [
            'name.required'     => 'Nama pengguna wajib diisi.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.unique'      => 'Email sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min'      => 'Kata sandi minimal 8 karakter.',
            'role.required'     => 'Peran pengguna wajib dipilih.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        return response()->json([
            'message' => 'Pengguna baru berhasil ditambahkan.',
            'data'    => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->role,
                'created_at' => $user->created_at,
            ],
        ], 201);
    }

    /**
     * PUT /api/users/{user}
     */
    public function update(Request $request, User $user)
    {
        if ($request->user()->role !== 'administrator') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'     => ['required', Rule::in(['administrator', 'operator'])],
            'password' => 'nullable|string|min:8',
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Email sudah digunakan pengguna lain.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
        ]);

        // Pencegahan keamanan: jika mengubah role diri sendiri menjadi operator padahal satu-satunya admin
        if ($user->id === $request->user()->id && $validated['role'] !== 'administrator') {
            $adminCount = User::where('role', 'administrator')->count();
            if ($adminCount <= 1) {
                return response()->json(['message' => 'Tidak dapat mengubah peran. Sistem harus memiliki minimal satu administrator.'], 422);
            }
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Data pengguna berhasil diperbarui.',
            'data'    => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->role,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    /**
     * DELETE /api/users/{user}
     */
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->role !== 'administrator') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Keamanan: Tidak boleh menghapus akun diri sendiri yang sedang aktif
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Anda tidak dapat menghapus akun Anda sendiri saat sedang masuk.'], 422);
        }

        // Keamanan: Sistem harus memiliki setidaknya 1 admin
        if ($user->role === 'administrator') {
            $adminCount = User::where('role', 'administrator')->count();
            if ($adminCount <= 1) {
                return response()->json(['message' => 'Tidak dapat menghapus satu-satunya administrator di sistem.'], 422);
            }
        }

        $user->tokens()->delete(); // Revoke token
        $user->delete();

        return response()->json(['message' => "Pengguna {$user->name} berhasil dihapus."]);
    }
}
