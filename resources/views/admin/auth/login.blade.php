<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin - Desa Getas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-emerald-900 to-emerald-700 min-h-screen flex items-center justify-center font-sans">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-8 mx-4">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-emerald-900">Desa Getas</h1>
            <p class="text-gray-500 text-sm mt-1">Admin Panel</p>
        </div>

        <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" value="{{ old('username') }}" required autofocus
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                @error('username')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                <label for="remember" class="ml-2 text-sm text-gray-600">Ingat saya</label>
            </div>
            <button type="submit" class="w-full py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-lg transition">
                Masuk
            </button>
        </form>

        @if($errors->any())
            <div class="mt-4 bg-red-50 border border-red-200 text-red-600 text-sm p-3 rounded-lg">
                {{ $errors->first('username') }}
            </div>
        @endif
    </div>
</body>
</html>
