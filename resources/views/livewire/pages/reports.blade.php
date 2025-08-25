{{-- resources/views/reports/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">Relatórios</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto space-y-6">

        {{-- Filtros --}}
        <form method="GET" class="bg-white rounded-xl shadow p-4 border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div>
                    <label class="text-sm text-gray-600">De</label>
                    <input type="date" name="from" value="{{ $from->toDateString() }}" class="w-full border-gray-300 rounded" />
                </div>
                <div>
                    <label class="text-sm text-gray-600">Até</label>
                    <input type="date" name="to" value="{{ $to->toDateString() }}" class="w-full border-gray-300 rounded" />
                </div>
                <div>
                    <label class="text-sm text-gray-600">Top N</label>
                    <input type="number" name="top" value="{{ $topN }}" min="3" max="50" class="w-full border-gray-300 rounded" />
                </div>
                <div class="flex items-end gap-2">
                    <x-primary-button class="self-end">Aplicar</x-primary-button>
                    <a class="text-sm text-gray-600 hover:underline self-end"
                       href="{{ route('reports.index') }}">Limpar</a>
                </div>
                <div class="md:col-span-2 flex items-end gap-2 justify-end">
                    <a class="text-sm text-gray-700 underline" href="{{ route('reports.export.transactions', request()->only(['from','to'])) }}">Exportar Transações (CSV)</a>
                    <a class="text-sm text-gray-700 underline" href="{{ route('reports.export.orders', request()->only(['from','to'])) }}">Exportar Pedidos (CSV)</a>
                </div>
            </div>
        </form>

        {{-- Entradas x Saídas --}}
        <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
            <h3 class="font-semibold text-gray-800 mb-3">Entradas x Saídas</h3>
            <canvas id="chartInOut" height="80"></canvas>
        </div>

        {{-- Pedidos (quantidade vs faturamento) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                <h3 class="font-semibold text-gray-800 mb-3">Pedidos por dia (quantidade)</h3>
                <canvas id="chartOrdersCount" height="90"></canvas>
            </div>
            <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                <h3 class="font-semibold text-gray-800 mb-3">Pedidos por dia (faturamento)</h3>
                <canvas id="chartOrdersTotal" height="90"></canvas>
            </div>
        </div>

        {{-- TOPs --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                <h3 class="font-semibold text-gray-800 mb-3">Pedidos por cliente (TOP {{ $topN }})</h3>
                <canvas id="chartByCustomer" height="140"></canvas>
            </div>

            <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                <h3 class="font-semibold text-gray-800 mb-3">Produtos mais vendidos (kg)</h3>
                <canvas id="chartBestProducts" height="140"></canvas>
            </div>

            <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                <h3 class="font-semibold text-gray-800 mb-3">Vendas por usuário</h3>
                <canvas id="chartByUser" height="140"></canvas>
            </div>
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const data = @json($chart);

        // helpers
        const fmtBR = v => Number(v||0).toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});

        // 1) Entradas x Saídas (linha)
        new Chart(document.getElementById('chartInOut').getContext('2d'), {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    { label: 'Entradas', data: data.in, borderWidth: 2, tension: .2 },
                    { label: 'Saídas',   data: data.out, borderWidth: 2, tension: .2 },
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    tooltip: {
                        callbacks: { label: (ctx) => `${ctx.dataset.label}: R$ ${fmtBR(ctx.parsed.y)}` }
                    }
                },
                scales: { y: { ticks: { callback: v => `R$ ${fmtBR(v)}` } } }
            }
        });

        // 2a) Pedidos por dia (quantidade)
        new Chart(document.getElementById('chartOrdersCount').getContext('2d'), {
            type: 'bar',
            data: { labels: data.labels, datasets: [{ label: 'Qtd', data: data.orders_count }] },
            options: { responsive: true }
        });

        // 2b) Pedidos por dia (faturamento)
        new Chart(document.getElementById('chartOrdersTotal').getContext('2d'), {
            type: 'bar',
            data: { labels: data.labels, datasets: [{ label: 'R$', data: data.orders_total }] },
            options: {
                responsive: true,
                plugins: { tooltip: { callbacks: { label: (ctx) => `R$ ${fmtBR(ctx.parsed.y)}` } } },
                scales: { y: { ticks: { callback: v => `R$ ${fmtBR(v)}` } } }
            }
        });

        // 3) Pedidos por cliente (TOP N) — barras horizontais
        new Chart(document.getElementById('chartByCustomer').getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.by_customer.labels,
                datasets: [{ label: 'Total (R$)', data: data.by_customer.total }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { tooltip: { callbacks: { label: (ctx) => `R$ ${fmtBR(ctx.parsed.x)}` } } },
                scales: { x: { ticks: { callback: v => `R$ ${fmtBR(v)}` } } }
            }
        });

        // 4) Produtos mais vendidos (kg)
        new Chart(document.getElementById('chartBestProducts').getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.best_products.labels,
                datasets: [{ label: 'Kg', data: data.best_products.qty }]
            },
            options: { responsive: true }
        });

        // 5) Vendas por usuário
        new Chart(document.getElementById('chartByUser').getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.by_user.labels,
                datasets: [{ label: 'Total (R$)', data: data.by_user.total }]
            },
            options: {
                responsive: true,
                plugins: { tooltip: { callbacks: { label: (ctx) => `R$ ${fmtBR(ctx.parsed.y)}` } } },
                scales: { y: { ticks: { callback: v => `R$ ${fmtBR(v)}` } } }
            }
        });
    </script>
</x-app-layout>
