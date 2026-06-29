<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', ['users' => User::select('id', 'username', 'nama', 'email', 'role', 'created_at')->get()]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => 'required|string|max:100|unique:users,username',
            'password' => 'required|string|min:6',
            'nama' => 'required|string|max:200',
            'email' => 'nullable|email|max:200|unique:users,email',
            'role' => 'required|in:superadmin,admin',
        ]);

        $data['name'] = $data['nama'];
        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'Admin berhasil ditambahkan');
    }

    public function edit($id): View
    {
        return view('admin.users.form', ['user' => User::findOrFail($id)]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'username' => 'required|string|max:100|unique:users,username,' . $id,
            'nama' => 'required|string|max:200',
            'email' => 'nullable|email|max:200|unique:users,email,' . $id,
            'role' => 'required|in:superadmin,admin',
            'password' => 'nullable|string|min:6',
        ]);

        $data['name'] = $data['nama'];
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return redirect()->route('admin.users.index')->with('success', 'Admin berhasil diupdate');
    }

    public function destroy($id): RedirectResponse
    {
        User::findOrFail($id)->delete();
        return redirect()->route('admin.users.index')->with('success', 'Admin berhasil dihapus');
    }
}
