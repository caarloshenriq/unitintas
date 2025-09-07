<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">Pedidos</h2>
    </x-slot>

    {{-- Abre o modal automaticamente se a sessão pedir --}}
    <div x-data x-init="$nextTick(() => { 
    @if(session('force_password_reset')) 
        $dispatch('open-modal', 'force-reset-password') 
    @endif 
})"></div>

    <x-modal name="force-reset-password" :show="false" focusable maxWidth="md">
        <form method="POST" action="{{ route('users.force-reset-password') }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <h2 class="text-lg font-semibold">Trocar senha</h2>
            <p class="text-sm text-gray-600">
                Por segurança, informe sua senha atual e defina uma nova senha.
            </p>

            {{-- Senha atual --}}
            <div>
                <x-input-label for="current_password" :value="__('Senha atual')" />
                <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full"
                    required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
            </div>

            {{-- Nova senha --}}
            <div>
                <x-input-label for="password" :value="__('Nova senha')" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required
                    autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            {{-- Confirmar nova senha --}}
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirmar nova senha')" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                    class="mt-1 block w-full" required autocomplete="new-password" />
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>
                <x-primary-button>
                    Salvar nova senha
                </x-primary-button>
            </div>
        </form>
    </x-modal>


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
    @endphp

    <div class="py-8 max-w-6xl mx-auto space-y-6">
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('orders.index') }}" class="flex items-center gap-3">
                <div>
                    <label for="status" class="text-sm text-gray-700">Status</label>
                    <select id="status" name="status" class="ml-2 border-gray-300 rounded">
                        @php $current = request('status'); @endphp
                        <option value="" {{ $current === null || $current === '' ? 'selected' : '' }}>Todos</option>
                        <option value="P" {{ $current === 'P' ? 'selected' : '' }}>Pendente</option>
                        <option value="C" {{ $current === 'C' ? 'selected' : '' }}>Completo</option>
                        <option value="D" {{ $current === 'D' ? 'selected' : '' }}>Em Preparação</option>
                        <option value="X" {{ $current === 'X' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                <x-primary-button type="submit">Filtrar</x-primary-button>
                @if($current)
                    <a href="{{ route('orders.index') }}" class="text-sm text-gray-600 hover:underline">Limpar filtro</a>
                @endif
            </form>
            <div class="ml-auto">
                <a href="{{ route('orders.create') }}">
                    <x-primary-button>Novo Pedido</x-primary-button>
                </a>
            </div>
        </div>

        @if ($orders->isEmpty())
            <div class="text-center text-gray-500 bg-white p-10 rounded shadow">
                Nenhum pedido encontrado.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($orders as $order)
                    @php
                        $code = $order->status;
                        $badgeClass = $colorMap[$code] ?? 'border-gray-300 bg-gray-50 text-gray-700';
                        $barClass = $barMap[$code] ?? 'bg-gray-300';
                        $label = $statusMap[$code] ?? ucfirst((string) $code);
                    @endphp

                    <div class="bg-white rounded-xl shadow border border-gray-100 flex flex-col overflow-hidden hover:cursor-pointer hover:shadow-lg transition-shadow duration-200"
                        onclick="window.location='{{ route('orders.show', $order) }}'">
                        <div class="p-5 flex-1">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500">#{{ $order->id }}</div>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border {{ $badgeClass }}">
                                    {{ $label }}
                                </span>
                            </div>

                            <div class="mt-3 space-y-1.5">
                                <div class="text-sm text-gray-500">Cliente</div>
                                <div class="font-semibold text-gray-800">
                                    {{ $order->customer->name ?? '—' }}
                                </div>

                                <div class="text-sm text-gray-500 mt-2">Vendedor</div>
                                <div class="text-gray-800">
                                    {{ $order->seller->name ?? '—' }}
                                </div>

                                <div class="flex items-center justify-between mt-3">
                                    <div>
                                        <div class="text-sm text-gray-500">Total</div>
                                        <div class="text-lg font-bold text-gray-900">
                                            R$ {{ number_format($order->total_amount, 2, ',', '.') }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm text-gray-500">Criado em</div>
                                        <div class="text-gray-800">
                                            {{ $order->created_at->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="h-1.5 w-full {{ $barClass }}"></div>
                    </div>
                @endforeach
            </div>

            {{-- paginação --}}
            <div class="mt-6">
                {{ $orders->appends(['status' => request('status')])->links() }}
            </div>
        @endif
    </div>
</x-app-layout>