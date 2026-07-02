<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-50 p-6">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Dashboard</h1>

        <p class="mb-4">Bienvenido, {{ auth()->user()->name }} ({{ auth()->user()->email }})</p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded">Cerrar sesión</button>
        </form>
    </div>
</body>
</html>
