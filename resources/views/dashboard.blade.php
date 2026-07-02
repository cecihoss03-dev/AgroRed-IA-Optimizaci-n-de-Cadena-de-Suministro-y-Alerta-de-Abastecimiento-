{{--
    Variables esperadas desde el controlador:
    - $liveRoute: ['status' => 'clear'|'blocked'|'unknown', 'label' => string, 'timestamp' => string]
    - $stats: ['avg_price' => float, 'predictions_count' => int, 'avg_confidence' => float, 'active_blockages' => int]
    - $recentPredictions: Collection de objetos con product_name, market_name, predicted_price,
      confidence_score, created_at
--}}
<x-layout title="Dashboard">

    {{-- Estado de rutas en tiempo real (elemento distintivo del sistema) --}}
    <x-route-status
        :status="$liveRoute['status'] ?? 'unknown'"
        :label="$liveRoute['label'] ?? null"
        :timestamp="$liveRoute['timestamp'] ?? null"
        class="mb-6"
    />

    {{-- Fila de estadísticas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <x-stat-card
            icon="price"
            label="Precio promedio hoy"
            :value="number_format($stats['avg_price'] ?? 0, 2)"
            unit="Bs"
        />
        <x-stat-card
            icon="confidence"
            label="Confianza promedio"
            :value="round(($stats['avg_confidence'] ?? 0) * 100)"
            unit="%"
        />
        <x-stat-card
            icon="count"
            label="Predicciones (7 días)"
            :value="$stats['predictions_count'] ?? 0"
        />
        <x-stat-card
            icon="route"
            label="Bloqueos activos"
            :value="$stats['active_blockages'] ?? 0"
            :trend="($stats['active_blockages'] ?? 0) > 0 ? 'up' : null"
            :trendLabel="($stats['active_blockages'] ?? 0) > 0 ? 'Afectando logística' : null"
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Predicciones recientes --}}
        <x-card class="lg:col-span-2" padding="p-0">
            <div class="flex items-center justify-between px-6 py-5 border-b border-ink-100">
                <h2 class="font-display font-bold text-ink-900 text-base">Predicciones recientes</h2>
                <a href="{{ url('/historial') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">
                    Ver historial →
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-ink-400 border-b border-ink-100">
                            <th class="px-6 py-3">Producto</th>
                            <th class="px-6 py-3">Mercado</th>
                            <th class="px-6 py-3 text-right">Precio est.</th>
                            <th class="px-6 py-3 text-right">Confianza</th>
                            <th class="px-6 py-3 text-right">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @forelse ($recentPredictions ?? [] as $pred)
                            <tr class="hover:bg-ink-50/60 transition">
                                <td class="px-6 py-3.5 font-medium text-ink-900">{{ $pred->product_name }}</td>
                                <td class="px-6 py-3.5 text-ink-600">{{ $pred->market_name }}</td>
                                <td class="px-6 py-3.5 text-right font-mono-data font-semibold text-ink-900">
                                    {{ number_format($pred->predicted_price, 2) }} Bs
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    @php $conf = round($pred->confidence_score * 100); @endphp
                                    <x-badge :tone="$conf >= 70 ? 'brand' : ($conf >= 40 ? 'harvest' : 'alert')">
                                        {{ $conf }}%
                                    </x-badge>
                                </td>
                                <td class="px-6 py-3.5 text-right font-mono-data text-xs text-ink-400">
                                    {{ \Illuminate\Support\Carbon::parse($pred->created_at)->format('d/m H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-ink-400">
                                    Todavía no hay predicciones registradas.
                                    <a href="{{ url('/predecir') }}" class="text-brand-600 font-semibold hover:text-brand-700">
                                        Calcula la primera →
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- Acceso rápido --}}
        <x-card class="flex flex-col">
            <h2 class="font-display font-bold text-ink-900 text-base mb-1">Nueva predicción</h2>
            <p class="text-sm text-ink-600 leading-relaxed mb-5">
                Estima el precio de un producto en su mercado de destino, considerando el estado
                actual de las rutas de acceso a Sucre.
            </p>

            <a href="{{ url('/predecir') }}"
               class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 hover:bg-brand-600
                      text-white font-semibold text-sm py-3 shadow-card transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Calcular predicción
            </a>
        </x-card>
    </div>

</x-layout>