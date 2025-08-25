{{-- resources/views/finance/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            {{ $type === 'receivable' ? 'Contas a Receber' : 'Contas a Pagar' }}
        </h2>
    </x-slot>

    @php
        $title = $type === 'receivable' ? 'Contas a Receber' : 'Contas a Pagar';
        $accent = $type === 'receivable' ? 'emerald' : 'sky';
    @endphp

    <div x-data="{ openCreate:false }" class="py-8 max-w-6xl mx-auto space-y-6">
        {{-- Abas --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('finance.index', ['type'=>'receivable']) }}">
                <x-secondary-button class="{{ $type==='receivable' ? 'ring-2 ring-emerald-500' : '' }}">Receber</x-secondary-button>
            </a>
            <a href="{{ route('finance.index', ['type'=>'payable']) }}">
                <x-secondary-button class="{{ $type==='payable' ? 'ring-2 ring-sky-500' : '' }}">Pagar</x-secondary-button>
            </a>
            <x-primary-button class="ml-auto" x-on:click="openCreate = true">Nova Transação</x-primary-button>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl shadow p-4 border border-gray-100">
                <div class="text-sm text-gray-500">Pendentes</div>
                <div class="text-2xl font-bold text-gray-900">
                    R$ {{ number_format($sumPending, 2, ',', '.') }}
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border border-gray-100">
                <div class="text-sm text-gray-500">Pagas</div>
                <div class="text-2xl font-bold text-gray-900">
                    R$ {{ number_format($sumPaid, 2, ',', '.') }}
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border border-gray-100">
                <div class="text-sm text-gray-500">Canceladas</div>
                <div class="text-2xl font-bold text-gray-900">
                    R$ {{ number_format($sumCancel, 2, ',', '.') }}
                </div>
            </div>
        </div>

        {{-- Filtros (GET) --}}
        <form method="GET" class="bg-white rounded-xl shadow p-4 border border-gray-100">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div class="md:col-span-2">
                    <label class="text-sm text-gray-600">Buscar</label>
                    <input type="text" name="q" value="{{ $q }}" class="w-full border-gray-300 rounded" placeholder="Descrição, #pedido..." />
                </div>
                <div>
                    <label class="text-sm text-gray-600">Status</label>
                    <select name="status" class="w-full border-gray-300 rounded">
                        <option value="" {{ $status===''||$status===null?'selected':'' }}>Todos</option>
                        <option value="pending"   {{ $status==='pending'?'selected':'' }}>Pendente</option>
                        <option value="paid"      {{ $status==='paid'?'selected':'' }}>Pago</option>
                        <option value="cancelled" {{ $status==='cancelled'?'selected':'' }}>Cancelado</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm text-gray-600">De</label>
                    <input type="date" name="from" value="{{ optional($from)->toDateString() }}" class="w-full border-gray-300 rounded" />
                </div>
                <div>
                    <label class="text-sm text-gray-600">Até</label>
                    <input type="date" name="to" value="{{ optional($to)->toDateString() }}" class="w-full border-gray-300 rounded" />
                </div>
                <div class="flex items-end gap-2">
                    <x-primary-button class="self-end">Filtrar</x-primary-button>
                    <a href="{{ route('finance.index', ['type'=>$type]) }}" class="self-end text-sm text-gray-600 hover:underline">Limpar</a>
                </div>
            </div>
        </form>

        {{-- Lista em cards --}}
        @if ($transactions->isEmpty())
            <div class="text-center text-gray-500 bg-white p-10 rounded shadow border border-gray-100">
                Nenhuma transação encontrada.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($transactions as $t)
                    @php
                        $badge = [
                            'pending'   => 'border-yellow-400 bg-yellow-50 text-yellow-800',
                            'paid'      => 'border-green-500 bg-green-50 text-green-800',
                            'cancelled' => 'border-red-500 bg-red-50 text-red-800',
                        ][$t->status] ?? 'border-gray-300 bg-gray-50 text-gray-700';

                        $bar = [
                            'pending'   => 'bg-yellow-400',
                            'paid'      => 'bg-green-500',
                            'cancelled' => 'bg-red-500',
                        ][$t->status] ?? 'bg-gray-300';
                    @endphp

                    <div class="bg-white rounded-xl shadow border border-gray-100 flex flex-col overflow-hidden">
                        <div class="p-5 flex-1">
                            <div class="flex items-start justify-between">
                                <div class="text-sm text-gray-500">
                                    {{ $type === 'receivable' ? 'Receber' : 'Pagar' }}
                                    @if($t->order_id)
                                        &middot; Pedido #{{ $t->order_id }}
                                    @endif
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border {{ $badge }}">
                                    {{ ['pending'=>'Pendente','paid'=>'Pago','cancelled'=>'Cancelado'][$t->status] ?? ucfirst($t->status) }}
                                </span>
                            </div>

                            <div class="mt-3 space-y-1.5">
                                <div class="font-semibold text-gray-900">{{ $t->description }}</div>

                                <div class="text-sm text-gray-500">Vencimento</div>
                                <div class="text-gray-800">{{ \Carbon\Carbon::parse($t->due_date)->format('d/m/Y') }}</div>

                                <div class="text-sm text-gray-500 mt-2">Valor</div>
                                <div class="text-lg font-bold text-gray-900">
                                    R$ {{ number_format($t->amount, 2, ',', '.') }}
                                </div>
                            </div>

                            <div class="mt-4 flex items-center gap-2">
                                @if($t->status !== 'paid')
                                    <form method="POST" action="{{ route('transactions.updateStatus', $t) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="paid">
                                        <x-primary-button>Dar baixa</x-primary-button>
                                    </form>
                                @endif
                                @if($t->status !== 'cancelled')
                                    <form method="POST" action="{{ route('transactions.updateStatus', $t) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">
                                        <x-danger-button>Cancelar</x-danger-button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('transactions.destroy', $t) }}" onsubmit="return confirm('Excluir esta transação?')">
                                    @csrf @method('DELETE')
                                    <x-secondary-button>Excluir</x-secondary-button>
                                </form>
                            </div>
                        </div>
                        <div class="h-1.5 w-full {{ $bar }}"></div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $transactions->links() }}
            </div>
        @endif

        {{-- Modal criar (Alpine simples) --}}
        <div x-show="openCreate" x-cloak class="fixed inset-0 bg-black/40 grid place-items-center z-50">
            <div class="bg-white rounded-xl shadow p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Nova transação — {{ $title }}</h3>

                <form method="POST" action="{{ route('finance.store') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">

                    <div>
                        <label class="text-sm text-gray-600">Descrição</label>
                        <input type="text" name="description" class="w-full border-gray-300 rounded" required>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Vencimento</label>
                        <input type="date" name="due_date" class="w-full border-gray-300 rounded" required
                               value="{{ now()->toDateString() }}">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Valor (R$)</label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="w-full border-gray-300 rounded" required>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <x-secondary-button type="button" x-on:click="openCreate=false">Cancelar</x-secondary-button>
                        <x-primary-button>Salvar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
