<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    @vite('resources/css/app.css')
</head>
<body class="p-6">

    <h1 class="text-2xl font-bold mb-4">Dashboard</h1>

    <p>Welcome, {{ auth()->user()->name }}</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="mt-4 bg-red-500 text-white px-4 py-2 rounded">
            Logout
        </button>
    </form>

</body>
</html>
