<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historial - AgroRed-IA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --color-deep: #3e1670;
            --color-mid: #7133c4;
            --color-bright: #8B2FE0;
        }
        body { font-family: 'Inter', sans-serif; }
        .sidebar { background: linear-gradient(160deg, var(--color-deep) 0%, var(--color-mid) 100%); }
        .module-btn { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            padding: 12px 16px; 
            border-radius: 8px; 
            transition: all 0.2s; 
            color: white; 
            font-size: 14px; 
            font-weight: 500;
            text-decoration: none;
        }
        .module-btn:hover { background: rgba(255,255,255,0.15); transform: translateX(4px); }
        .module-btn.active { background: var(--color-bright); }
        .history-row { background: white; border-radius: 8px; padding: 14px; border-left: 4px solid var(--color-mid); box-shadow: 0 4px 12px -8px rgba(81,32,140,0.08); transition: all 0.2s; }
        .history-row:hover { box-shadow: 0 8px 20px -10px rgba(81,32,140,0.15); transform: translateY(-1px); }
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-gray-50">
    <!-- Sidebar -->
    <div class="sidebar w-64 flex flex-col p-6 space-y-8">
        <!-- Logo -->
        <div>
            <h1 class="text-2xl font-bold text-white">
                AgroRed<span style="color:var(--color-bright);">-IA</span>
            </h1>
            <p class="text-xs text-gray-300 mt-1">Predicción de precios agrícolas</p>
        </div>

        <!-- Módulos -->
        <nav class="space-y-2">
            <p class="text-xs font-semibold text-gray-300 uppercase tracking-wide px-2">Módulos</p>
            <a href="{{ route('prediccion.index') }}" class="module-btn">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Hacer Predicción
            </a>
            <a href="{{ route('prediccion.historial') }}" class="module-btn active">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Historial
            </a>
        </nav>

        <!-- Link a Dashboard -->
        <div class="pt-4 border-t border-white/20">
            <a href="{{ route('dashboard') }}" class="text-xs text-gray-300 hover:text-white transition">← Volver al Dashboard</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-auto">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 px-8 py-6">
            <h2 class="text-2xl font-bold text-gray-800">Historial de Predicciones</h2>
            <p class="text-sm text-gray-600 mt-1">Últimas 50 consultas realizadas al sistema</p>
        </div>

        <!-- Content -->
        <div class="flex-1 p-8">
            <div class="max-w-4xl">
                @if(empty($historial) || $historial->isEmpty())
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-gray-300 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-600">No hay predicciones aún</h3>
                        <p class="text-sm text-gray-500 mt-1">Comienza realizando tu primera predicción</p>
                        <a href="{{ route('prediccion.index') }}" class="inline-block mt-4 px-4 py-2 rounded-lg font-medium text-white" style="background: var(--color-mid);">
                            Hacer Predicción
                        </a>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($historial as $h)
                            <a href="{{ route('prediccion.detalle', ['id' => $h->id]) }}" class="history-row block hover:no-underline">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="text-sm text-gray-500">ID: <span class="font-mono text-gray-700">{{ $h->id }}</span></div>
                                        <div class="mt-2">
                                            <h4 class="font-semibold text-gray-800">Producto {{ $h->product_id }} → Mercado {{ $h->market_point_id }}</h4>
                                            <div class="mt-2 flex items-center gap-4 text-sm">
                                                <div>
                                                    <span class="text-gray-600">Precio estimado:</span>
                                                    <span class="font-bold ml-1" style="color: var(--color-mid);">{{ $h->predicted_price }} Bs</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-600">Confianza:</span>
                                                    <span class="font-bold ml-1" style="color: var(--color-bright);">{{ round(($h->confidence_score ?? 0) * 100) }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500 text-right whitespace-nowrap ml-4">
                                        {{ \Carbon\Carbon::parse($h->created_at)->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
