<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - AgroRed-IA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
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
        .feature-card { background: white; border-radius: 10px; padding: 16px; box-shadow: 0 8px 20px -10px rgba(81,32,140,0.1); }
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
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-auto">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 px-8 py-6">
            <h2 class="text-2xl font-bold text-gray-800">Panel de Control</h2>
            <p class="text-sm text-gray-600 mt-1">Sistema de análisis y predicción de precios agrícolas en Sucre</p>
        </div>

        <!-- Content -->
        <div class="flex-1 p-8">
            <div class="max-w-4xl">
                <!-- Título de funcionalidades -->
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-800">Funcionalidades del Sistema</h3>
                    <p class="text-sm text-gray-600 mt-1">Descubre qué puede hacer AgroRed-IA para tu negocio agrícola</p>
                </div>

                <!-- Grid de funcionalidades -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Card 1 -->
                    <div class="feature-card border-l-4" style="border-color: var(--color-mid);">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--color-mid); color: white;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-800">Predicción de Precios</h4>
                        </div>
                        <p class="text-sm text-gray-600">Estima precios futuros según condiciones de rutas y bloqueos actuales. Utiliza IA avanzada para análisis en tiempo real.</p>
                    </div>

                    <!-- Card 2 -->
                    <div class="feature-card border-l-4" style="border-color: var(--color-bright);">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--color-bright); color: white;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-800">Historial Completo</h4>
                        </div>
                        <p class="text-sm text-gray-600">Accede a todas las predicciones realizadas. Revisa análisis previos y tendencias de precios para tomar decisiones informadas.</p>
                    </div>

                    <!-- Card 3 -->
                    <div class="feature-card border-l-4" style="border-color: var(--color-mid);">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--color-mid); color: white;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-800">Puntuación de Confianza</h4>
                        </div>
                        <p class="text-sm text-gray-600">Cada predicción incluye un indicador de confianza que refleja la calidad del análisis basado en datos disponibles.</p>
                    </div>

                    <!-- Card 4 -->
                    <div class="feature-card border-l-4" style="border-color: var(--color-bright);">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--color-bright); color: white;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-800">Análisis Detallado</h4>
                        </div>
                        <p class="text-sm text-gray-600">Obtén explicaciones de IA sobre los factores que influyeron en cada predicción de precio.</p>
                    </div>
                </div>

                <!-- CTA Section -->
                <div class="mt-8 p-6 rounded-xl" style="background: linear-gradient(135deg, var(--color-mid), var(--color-bright)); color: white;">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-lg font-semibold">¿Listo para empezar?</h4>
                            <p class="text-sm mt-1 opacity-90">Realiza tu primera predicción y descubre el poder de la IA en la agricultura.</p>
                        </div>
                        <a href="{{ route('prediccion.index') }}" class="px-6 py-2.5 bg-white rounded-lg font-semibold text-sm" style="color: var(--color-mid); text-decoration: none; display: inline-block;">
                            Iniciar Predicción →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

