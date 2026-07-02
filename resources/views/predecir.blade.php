<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Predicción - AgroRed-IA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        display: ['Sora', 'sans-serif'],
                        body: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        eplum: {
                            50:  '#f7f5fc',
                            100: '#efe9fa',
                            200: '#e0d4f5',
                            300: '#c7b3ec',
                            400: '#a685de',
                            500: '#8657d1',
                            600: '#7133c4',
                            700: '#5c23a3',
                            800: '#3e1670',
                            900: '#2a0f4d',
                        },
                        eelectric: '#8B2FE0',
                    },
                    boxShadow: {
                        soft: '0 10px 40px -12px rgba(93, 35, 163, 0.25)',
                        glow: '0 0 0 4px rgba(139, 47, 224, 0.15), 0 8px 24px -8px rgba(139, 47, 224, 0.45)',
                    },
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Sora', sans-serif; }
        .sidebar { background: linear-gradient(160deg, #3e1670 0%, #7133c4 100%); }
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
        .module-btn.active { background: #8B2FE0; }
        [x-cloak] { display: none !important; }
        @keyframes pulse-soft {
            0%, 100% { opacity: 1; }
            50% { opacity: .55; }
        }
        .animate-pulse-soft { animation: pulse-soft 1.6s ease-in-out infinite; }
        .confidence-fill {
            transition: width 900ms cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes rise-in {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .rise-in { animation: rise-in 500ms cubic-bezier(0.16, 1, 0.3, 1) both; }
        .bg-road-line {
            background-image: repeating-linear-gradient(
                90deg,
                rgba(255,255,255,0.6) 0px,
                rgba(255,255,255,0.6) 24px,
                transparent 24px,
                transparent 48px
            );
        }
    </style>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-eplum-50 text-eplum-900 h-screen flex overflow-hidden">

<!-- Sidebar -->
<div class="sidebar w-64 flex flex-col p-6 space-y-8">
    <!-- Logo -->
    <div>
        <h1 class="text-2xl font-bold text-white font-display">
            AgroRed<span style="color:#8B2FE0;">-IA</span>
        </h1>
        <p class="text-xs text-gray-300 mt-1">Predicción de precios agrícolas</p>
    </div>

    <!-- Módulos -->
    <nav class="space-y-2">
        <p class="text-xs font-semibold text-gray-300 uppercase tracking-wide px-2">Módulos</p>
        <a href="{{ route('prediccion.index') }}" class="module-btn active">
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
<main class="flex-1 flex flex-col overflow-auto">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-8 py-6">
        <h2 class="text-2xl font-bold text-gray-800">Calculadora de Predicción</h2>
        <p class="text-sm text-gray-600 mt-1">Estima el precio futuro de tu producto según rutas y bloqueos</p>
    </div>

    <!-- Content -->
    <div class="flex-1 p-8 overflow-auto" x-data="prediccionForm()">
        <div class="max-w-2xl">

            {{-- Tarjeta del formulario --}}
            <section class="bg-white/80 backdrop-blur rounded-3xl shadow-soft border border-eplum-100 p-6 md:p-8">
                <form @submit.prevent="calcular">

                    <div class="grid gap-5 md:grid-cols-2">
                        {{-- Producto --}}
                        <div>
                            <label for="producto" class="block text-sm font-semibold text-eplum-800 mb-1.5">
                                Producto agrícola
                            </label>
                            <div class="relative">
                                <select id="producto" x-model="form.producto" required
                                    class="appearance-none w-full rounded-xl border border-eplum-200 bg-eplum-50/60 text-eplum-900 text-sm px-4 py-3 pr-10 focus:outline-none focus:ring-4 focus:ring-eelectric/15 focus:border-eelectric transition">
                                    <option value="" disabled selected>Selecciona un producto</option>
                                    <option value="papa_imilla">Papa Imilla</option>
                                    <option value="tomate_perita">Tomate Perita</option>
                                    <option value="mani_colorado">Maní Colorado</option>
                                    <option value="cebolla_cabeza">Cebolla Cabeza</option>
                                    <option value="maiz_choclo">Maíz Choclo</option>
                                </select>
                                <svg class="w-4 h-4 text-eplum-500 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                                </svg>
                            </div>
                        </div>

                        {{-- Mercado destino --}}
                        <div>
                            <label for="mercado" class="block text-sm font-semibold text-eplum-800 mb-1.5">
                                Mercado de destino (Sucre)
                            </label>
                            <div class="relative">
                                <select id="mercado" x-model="form.mercado" required
                                    class="appearance-none w-full rounded-xl border border-eplum-200 bg-eplum-50/60 text-eplum-900 text-sm px-4 py-3 pr-10 focus:outline-none focus:ring-4 focus:ring-eelectric/15 focus:border-eelectric transition">
                                    <option value="" disabled selected>Selecciona un mercado</option>
                                    <option value="mayorista_el_morro">Mercado Mayorista El Morro</option>
                                    <option value="mercado_central">Mercado Central</option>
                                    <option value="mercado_campesino">Mercado Campesino</option>
                                    <option value="mercado_san_pedro">Mercado San Pedro</option>
                                </select>
                                <svg class="w-4 h-4 text-eplum-500 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Error de validación / API --}}
                    <p x-cloak x-show="error" x-text="error" class="mt-4 text-sm text-rose-600 font-medium"></p>

                    {{-- Botón de acción --}}
                    <button type="submit" :disabled="cargando"
                        class="mt-6 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-eplum-600 to-eelectric text-white font-semibold text-sm py-3.5 shadow-glow hover:brightness-105 active:scale-[0.99] disabled:opacity-70 disabled:cursor-not-allowed transition">
                        <svg x-show="cargando" x-cloak class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="cargando ? 'Analizando rutas y bloqueos…' : 'Calcular Predicción con IA'"></span>
                    </button>
                </form>
            </section>

            {{-- Panel de resultados --}}
            <section x-cloak x-show="resultado" x-transition class="mt-6">
                <div class="rise-in bg-white rounded-3xl shadow-soft border border-eplum-100 overflow-hidden">

                    {{-- Precio destacado --}}
                    <div class="bg-gradient-to-br from-eplum-700 to-eelectric px-6 md:px-8 py-8 text-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-road-line opacity-20"></div>
                        <p class="relative text-eplum-100 text-xs font-semibold uppercase tracking-widest mb-2">
                            Precio estimado en destino
                        </p>
                        <p class="relative font-display font-extrabold text-white text-5xl md:text-6xl tracking-tight">
                            <span x-text="resultado?.predicted_price ?? '—'"></span>
                            <span class="text-2xl md:text-3xl font-semibold text-eplum-100 align-top ml-1">Bs / Arroba</span>
                        </p>
                    </div>

                    <div class="p-6 md:p-8">

                        {{-- Medidor de confianza --}}
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-semibold text-eplum-800">Confianza del modelo</span>
                                <span class="text-sm font-bold text-eelectric" x-text="confianzaPorcentaje + '%'"></span>
                            </div>
                            <div class="w-full h-2.5 rounded-full bg-eplum-100 overflow-hidden">
                                <div class="confidence-fill h-full rounded-full"
                                     :class="confianzaColor"
                                     :style="`width: ${confianzaPorcentaje}%`"></div>
                            </div>
                            <p class="mt-1.5 text-xs text-eplum-500" x-text="confianzaEtiqueta"></p>
                        </div>

                        {{-- Explicación de la IA --}}
                        <div class="relative rounded-2xl bg-eplum-50 border border-eplum-100 p-5 pl-6">
                            <span class="absolute -left-0.5 top-5 bottom-5 w-1 rounded-full bg-gradient-to-b from-eplum-600 to-eelectric"></span>
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-4 h-4 text-eelectric" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l2.5 5.5L20 9l-4 4 1 6-5-3-5 3 1-6-4-4 5.5-1.5L12 2z"/>
                                </svg>
                                <span class="text-xs font-bold uppercase tracking-wide text-eplum-700">Análisis de bloqueos y rutas</span>
                            </div>
                            <blockquote class="text-sm leading-relaxed text-eplum-800" x-text="resultado?.explanation"></blockquote>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>
</main>

<script>
    function prediccionForm() {
        return {
            form: {
                producto: '',
                mercado: '',
            },
            cargando: false,
            error: null,
            resultado: null,

            get confianzaPorcentaje() {
                if (!this.resultado) return 0;
                return Math.round((this.resultado.confidence_score ?? 0) * 100);
            },
            get confianzaColor() {
                const p = this.confianzaPorcentaje;
                if (p >= 75) return 'bg-eplum-600';
                if (p >= 45) return 'bg-eelectric';
                return 'bg-rose-400';
            },
            get confianzaEtiqueta() {
                const p = this.confianzaPorcentaje;
                if (p >= 75) return 'Alta confianza: el modelo encontró señales claras en los datos de la ruta.';
                if (p >= 45) return 'Confianza media: hay variables de bloqueo con información parcial.';
                return 'Confianza baja: datos limitados sobre el estado actual de la vía.';
            },

            async calcular() {
                this.error = null;

                if (!this.form.producto || !this.form.mercado) {
                    this.error = 'Selecciona un producto y un mercado de destino.';
                    return;
                }

                this.cargando = true;
                this.resultado = null;

                try {
                    const response = await fetch("{{ route('prediccion.calcular') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            producto: this.form.producto,
                            mercado: this.form.mercado,
                        }),
                    });

                    if (!response.ok) {
                        const errData = await response.json().catch(() => null);
                        throw new Error(errData?.message ?? 'No se pudo calcular la predicción. Intenta nuevamente.');
                    }

                    const data = await response.json();
                    this.resultado = data.resultado ?? data;
                } catch (e) {
                    this.error = e.message ?? 'Ocurrió un error al conectar con el servicio de predicción.';
                } finally {
                    this.cargando = false;
                }
            },
        }
    }
</script>

</body>
</html>