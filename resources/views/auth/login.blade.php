

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <style>
        :root {
            --color-deep: #3e1670;
            --color-mid: #7133c4;
            --color-bright: #8B2FE0;
        }
 
        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at 15% 20%, rgba(139, 47, 224, 0.35), transparent 45%),
                        radial-gradient(circle at 85% 80%, rgba(113, 51, 196, 0.35), transparent 45%),
                        linear-gradient(160deg, var(--color-deep) 0%, var(--color-mid) 55%, var(--color-bright) 100%);
            position: relative;
            overflow: hidden;
        }
 
        body::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
            background-size: 26px 26px;
            pointer-events: none;
        }
 
        h1, .brand-font {
            font-family: 'Poppins', sans-serif;
        }
 
        .auth-card {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(6px);
            box-shadow: 0 25px 50px -12px rgba(62, 22, 112, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
 
        .role-badge {
            background: linear-gradient(135deg, var(--color-mid), var(--color-bright));
        }
 
        .input-icon-wrap input {
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
 
        .input-icon-wrap input:focus {
            outline: none;
            border-color: var(--color-bright);
            box-shadow: 0 0 0 3px rgba(139, 47, 224, 0.18);
        }
 
        .btn-primary {
            background: linear-gradient(135deg, var(--color-mid), var(--color-bright));
            transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
        }
 
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px -6px rgba(139, 47, 224, 0.55);
            filter: brightness(1.05);
        }
 
        .btn-primary:active {
            transform: translateY(0);
        }
 
        input[type="checkbox"]:checked {
            accent-color: var(--color-bright);
        }
 
        .link-brand {
            color: var(--color-mid);
        }
 
        .link-brand:hover {
            color: var(--color-bright);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen px-4">
 
    <div class="w-full max-w-md auth-card relative z-10 p-8 rounded-2xl">
 
        <div class="flex flex-col items-center mb-6 text-center">
            <div class="w-14 h-14 rounded-full role-badge flex items-center justify-center mb-4 shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">
                Iniciar sesión
                @if(!empty($role))
                    <span class="block text-sm font-medium mt-1" style="color: var(--color-mid);">
                        Acceso {{ ucfirst($role) }}
                    </span>
                @endif
            </h1>
        </div>
 
        @if($errors->any())
            <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg p-3">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
 
        @php
            $action = isset($role) && $role === 'productor'
                ? route('login.productor.post')
                : (isset($role) && $role === 'comerciante'
                    ? route('login.comerciante.post')
                    : route('login.post'));
        @endphp
 
        <form action="{{ $action }}" method="POST" class="space-y-5">
            @csrf
 
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700">Correo</label>
                <div class="input-icon-wrap relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="tucorreo@ejemplo.com"
                           class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2.5 text-gray-800">
                </div>
            </div>
 
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700">Contraseña</label>
                <div class="input-icon-wrap relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </span>
                    <input type="password" name="password" required
                           placeholder="••••••••"
                           class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2.5 text-gray-800">
                </div>
            </div>
 
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="mr-2 rounded">
                    <label for="remember" class="text-sm text-gray-600">Recordarme</label>
                </div>
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm link-brand font-medium">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>
 
            <div>
                <button type="submit" class="btn-primary w-full text-white font-semibold py-2.5 rounded-lg">
                    Entrar
                </button>
            </div>
        </form>
    </div>
 
</body>
</html>