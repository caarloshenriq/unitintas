<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Pedido #{{ $order->id }}
        </h2>
    </x-slot>

    @php
        $statusMap = [
            'P' => 'Pendente',
            'D' => 'Em preparação',
            'C' => 'Completo',
            'X' => 'Cancelado',
        ];

        $badgeMap = [
            'P' => 'border-yellow-400 bg-yellow-50 text-yellow-800',
            'D' => 'border-blue-500 bg-blue-50 text-blue-800',
            'C' => 'border-green-500 bg-green-50 text-green-800',
            'X' => 'border-red-500 bg-red-50 text-red-800',
        ];

        $barMap = [
            'P' => 'bg-yellow-400',
            'D' => 'bg-blue-500',
            'C' => 'bg-green-500',
            'X' => 'bg-red-500',
        ];

        $code = $order->status;
        $label = $statusMap[$code] ?? ucfirst((string) $code);
        $badgeClass = $badgeMap[$code] ?? 'border-gray-300 bg-gray-50 text-gray-700';
        $barClass = $barMap[$code] ?? 'bg-gray-300';
    @endphp

    <div class="py-8 max-w-5xl mx-auto space-y-6">
        <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Pedido</div>
                        <div class="text-2xl font-bold text-gray-900">#{{ $order->id }}</div>
                        <div class="text-sm text-gray-500 mt-1">
                            Criado em {{ $order->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <span
                        class="inline-flex items-center px-3 py-1 rounded text-sm font-medium border {{ $badgeClass }}">
                        {{ $label }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div>
                        <div class="text-sm text-gray-500">Cliente</div>
                        <div class="font-semibold text-gray-800">
                            {{ $order->customer->name ?? '—' }}
                        </div>
                        @if(!empty($order->customer?->phone))
                            <div class="text-sm text-gray-600 mt-1">Fone: {{ $order->customer->phone }}</div>
                        @endif
                        @if(!empty($order->customer?->address))
                            <div class="text-sm text-gray-600">{{ $order->customer->address }}</div>
                        @endif
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Vendedor</div>
                        <div class="font-semibold text-gray-800">
                            {{ $order->seller->name ?? '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Total</div>
                        <div class="text-2xl font-bold text-gray-900">
                            R$ {{ number_format($order->total_amount, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="h-1.5 w-full {{ $barClass }}"></div>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
            <div class="p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Itens</h3>

                @if ($order->items->isEmpty())
                    <div class="text-sm text-gray-500">Este pedido não possui itens.</div>
                @else
                    <table class="min-w-full text-left">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-2">Produto</th>
                                <th class="px-4 py-2">Kg</th>
                                <th class="px-4 py-2">Preço (R$)</th>
                                <th class="px-4 py-2">Subtotal (R$)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $sum = 0; @endphp
                            @foreach ($order->items as $it)
                                @php
                                    $q = (float) $it->quantity;
                                    $p = (float) $it->price;
                                    $sub = $q * $p;
                                    $sum += $sub;
                                @endphp
                                <tr class="border-b">
                                    <td class="px-4 py-2">{{ $it->product->name ?? '—' }}</td>
                                    <td class="px-4 py-2">{{ number_format($q, 3, ',', '.') }}</td>
                                    <td class="px-4 py-2">{{ number_format($p, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2">{{ number_format($sub, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right font-semibold">Total calculado</td>
                                <td class="px-4 py-3 font-bold">
                                    R$ {{ number_format($sum, 2, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        @if($order->status == 'P')
            <div class="flex space-x-3 mt-6">
                <form action="{{ route('orders.updateStatus', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="D">
                    <x-primary-button>Iniciar Preparação</x-primary-button>
                </form>

                <form action="{{ route('orders.updateStatus', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="X">
                    <x-danger-button>Cancelar Pedido</x-danger-button>
                </form>
            </div>
        @elseif($order->status == 'D')
            <form action="{{ route('orders.updateStatus', $order) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="C">
                <x-primary-button>Marcar como Completo</x-primary-button>
            </form>
        @endif


        <div class="flex justify-between">
            <a href="{{ route('orders.index') }}">
                <x-secondary-button>Voltar</x-secondary-button>
            </a>
        </div>
    </div>
</x-app-layout>