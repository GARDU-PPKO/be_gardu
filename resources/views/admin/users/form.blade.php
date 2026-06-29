@extends('admin.layouts.app')
@section('title', $user ? 'Edit Admin' : 'Tambah Admin')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">{{ $user ? 'Edit Admin' : 'Tambah Admin' }}</h2>

    <form method="POST" action="{{ $user ? route('admin.users.update', $user->id) : route('admin.users.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf
        @if($user) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
            <input type="text" name="username" value="{{ old('username', $user->username ?? '') }}" required
                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            @error('username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
            <input type="text" name="nama" value="{{ old('nama', $user->nama ?? '') }}" required
                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
            <select name="role" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                <option value="admin" {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="superadmin" {{ old('role', $user->role ?? '') === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password {{ $user ? '(kosongkan jika tidak diubah)' : '*' }}</label>
            <input type="password" name="password" {{ $user ? '' : 'required' }}
                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">Simpan</button>
            <a href="{{ route('admin.users.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
