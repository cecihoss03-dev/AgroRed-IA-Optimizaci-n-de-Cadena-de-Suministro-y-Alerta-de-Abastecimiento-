<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Predicción #{{ $pred->id }} - AgroRed-IA</title>
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
        .detail-card { background: white; border-radius: 10px; padding: 16px; box-shadow: 0 8px 20px -10px rgba(81,32,140,0.1); }
        .price-badge { display: inline-block; padding: 8px 16px; border-radius: 8px; font-weight: bold; color: white; }
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
            <a href="{{ route('prediccion.historial') }}" class="module-btn">
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
            <h2 class="text-2xl font-bold text-gray-800">Detalle de Predicción #{{ $pred->id }}</h2>
            <p class="text-sm text-gray-600 mt-1">Análisis completo de la predicción realizada</p>
        </div>

        <!-- Content -->
        <div class="flex-1 p-8 overflow-auto">
            <div class="max-w-4xl">
                <!-- Precio Principal -->
                <div class="detail-card mb-4" style="border-left: 4px solid var(--color-bright);">
                    <div class="text-sm text-gray-600 mb-2">Precio Estimado (Bs/Arroba)</div>
                    <div class="text-5xl font-bold" style="color: var(--color-mid);">{{ $pred->predicted_price }}</div>
                    <div class="mt-4 flex gap-4">
                        <div>
                            <span class="text-xs text-gray-600">Confianza del modelo:</span>
                            <div class="mt-1">
                                <span class="price-badge" style="background: var(--color-mid);">{{ round(($pred->confidence_score ?? 0) * 100) }}%</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs text-gray-600">Registrado:</span>
                            <div class="mt-1 text-sm font-medium text-gray-800">
                                {{ \Carbon\Carbon::parse($pred->created_at)->format('d/m/Y H:i:s') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Datos de Entrada -->
                <div class="detail-card mb-4" style="border-left: 4px solid var(--color-mid);">
                    <h3 class="font-semibold text-gray-800 mb-3">Datos de Entrada</h3>
                    <div class="bg-gray-50 rounded-lg p-4 overflow-auto">
                        <pre class="text-xs text-gray-700">{{ json_encode(json_decode($pred->scenario_input), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>

                <!-- Explicación del Modelo -->
                <div class="detail-card mb-4" style="border-left: 4px solid var(--color-bright);">
                    <h3 class="font-semibold text-gray-800 mb-3">Análisis de la IA</h3>
                    <div class="bg-gradient-to-r from-purple-50 to-transparent rounded-lg p-4">
                        <pre class="text-sm text-gray-800 whitespace-pre-wrap">{{ json_encode(json_decode($pred->model_explanation), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>

                <!-- Fecha de Validez -->
                <div class="detail-card" style="border-left: 4px solid var(--color-mid);">
                    <h3 class="font-semibold text-gray-800 mb-2">Información Adicional</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Válido para:</span>
                            <div class="font-medium text-gray-800">{{ $pred->valid_for_date ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <span class="text-gray-600">Producto ID:</span>
                            <div class="font-medium text-gray-800">{{ $pred->product_id }}</div>
                        </div>
                    </div>
                </div>

                <!-- Botón Atrás -->
                <div class="mt-6">
                    <a href="{{ route('prediccion.historial') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium text-white" style="background: var(--color-mid);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Volver al Historial
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
