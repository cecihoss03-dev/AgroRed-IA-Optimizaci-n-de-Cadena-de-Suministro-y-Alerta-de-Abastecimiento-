<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AgroRed-IA | Predicción de Precios Agrícolas</title>

    {{-- Tipografías: Sora para display/números, Inter para texto de lectura --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- Tailwind CSS --}}
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
                        // Paleta "electric purple" + lavanda, tokens propios de AgroRed-IA
                        eplum: {
                            50:  '#f7f5fc',
                            100: '#efe9fa',
                            200: '#e0d4f5',
                            300: '#c7b3ec',
                            400: '#a685de',
                            500: '#8657d1',
                            600: '#7133c4',  // violeta principal
                            700: '#5c23a3',
                            800: '#3e1670', // "electric purple" profundo
                            900: '#2a0f4d',
                        },
                        eelectric: '#8B2FE0', // acento eléctrico puntual (CTA, focos)
                    },
                    boxShadow: {
                        soft: '0 10px 40px -12px rgba(93, 35, 163, 0.25)',
                        glow: '0 0 0 4px rgba(139, 47, 224, 0.15), 0 8px 24px -8px rgba(139, 47, 224, 0.45)',
                    },
                    backgroundImage: {
                        'road-line': "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='40' viewBox='0 0 120 40'%3E%3Cpath d='M0 20 Q 30 0 60 20 T 120 20' stroke='%23c7b3ec' stroke-width='1.5' fill='none' stroke-dasharray='6 6'/%3E%3C/svg%3E\")",
                    }
                }
            }
        }
    </script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Sora', sans-serif; }

        /* Camino punteado decorativo que serpentea detrás del contenido: alude a la carretera */
        .route-backdrop::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 15% 20%, rgba(139,47,224,0.08), transparent 45%),
                radial-gradient(circle at 85% 75%, rgba(113,51,196,0.10), transparent 45%);
            pointer-events: none;
        }

        [x-cloak] { display: none !important; }

        @keyframes pulse-soft {
            0%, 100% { opacity: 1; }
            50% { opacity: .55; }
        }
        .animate-pulse-soft { animation: pulse-soft 1.6s ease-in-out infinite; }

        /* Barra de confianza: relleno animado */
        .confidence-fill {
            transition: width 900ms cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Aparición suave del panel de resultados */
        @keyframes rise-in {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .rise-in { animation: rise-in 500ms cubic-bezier(0.16, 1, 0.3, 1) both; }
    </style>
</head>
<body class="bg-eplum-50 text-eplum-900 min-h-screen">

<div class="relative route-backdrop min-h-screen">

    {{-- Encabezado --}}
    <header class="relative max-w-4xl mx-auto px-6 pt-14 pb-8 text-center">
        <span class="inline-flex items-center gap-2 rounded-full bg-eplum-100 text-eplum-700 text-xs font-semibold tracking-wide uppercase px-4 py-1.5 mb-5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l2.5 5.5L20 9l-4 4 1 6-5-3-5 3 1-6-4-4 5.5-1.5L12 2z"/>
            </svg>
            Predicción impulsada por IA · Chuquisaca
        </span>
        <h1 class="font-display font-extrabold text-4xl md:text-5xl text-eplum-900 leading-tight">
            AgroRed<span class="text-eelectric">-IA</span>
        </h1>
        <p class="mt-3 text-eplum-700 max-w-xl mx-auto text-sm md:text-base">
            Estima cómo un bloqueo de carretera puede mover el precio de tu producto antes de que llegue al mercado.
        </p>
    </header>

    {{-- Contenedor principal --}}
    <main class="relative max-w-2xl mx-auto px-6 pb-24" x-data="prediccionForm()">

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

    </main>
</div>

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