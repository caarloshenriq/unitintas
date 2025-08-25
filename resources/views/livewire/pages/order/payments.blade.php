<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Pagamento do Pedido #{{ $order->id }}
        </h2>
    </x-slot>

    <div x-data="payForm({{ json_encode(['total' => (float) $order->total_amount]) }})"
        class="py-8 max-w-4xl mx-auto space-y-6">

        {{-- Resumo do pedido --}}
        <div class="bg-white rounded-xl shadow border border-gray-100 p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="text-sm text-gray-500">Cliente</div>
                    <div class="font-semibold text-gray-800">{{ $order->customer->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Total do Pedido</div>
                    <div class="text-2xl font-bold text-gray-900">
                        R$ {{ number_format($order->total_amount, 2, ',', '.') }}
                    </div>
                </div>
                <div class="text-right">
                    <a href="{{ route('orders.show', $order->id) }}">
                        <x-secondary-button>Voltar ao pedido</x-secondary-button>
                    </a>
                </div>
            </div>
        </div>

        {{-- Form de pagamento --}}
        <form method="POST" action="{{ route('orders.payment.store', $order->id) }}"
            class="bg-white rounded-xl shadow border border-gray-100 p-6 space-y-5">
            @csrf

            @if (session('success'))
                <div class="rounded bg-green-50 text-green-800 p-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded bg-red-50 text-red-700 p-3 text-sm">
                    <ul class="list-disc ml-5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="payment_method" value="Forma de pagamento" />
                    <select id="payment_method" name="payment_method" class="w-full border-gray-300 rounded"
                        x-model="method" required>
                        <option value="">Selecione...</option>
                        <option value="debito">Débito</option>
                        <option value="credito">Crédito</option>
                        <option value="pix">PIX</option>
                        <option value="dinheiro">Dinheiro</option>
                    </select>
                </div>

                <div>
                    <x-input-label for="discount" value="Desconto (R$)" />
                    <x-text-input id="discount" name="discount" type="number" step="0.01" min="0" class="w-full"
                        x-model.number="discount" />
                </div>

                <div x-show="method === 'credito'">
                    <x-input-label for="installments" value="Parcelas" />
                    <x-text-input id="installments" name="installments" type="number" min="1" max="24" class="w-full"
                        x-model.number="installments" />
                </div>

                <div x-show="method === 'credito'">
                    <x-input-label for="first_due_date" value="Vencimento 1ª parcela" />
                    <x-text-input id="first_due_date" name="first_due_date" type="date" class="w-full"
                        x-model="firstDue" />
                </div>
            </div>

            <div class="border-t pt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="text-sm text-gray-500">Total original</div>
                    <div class="text-lg font-semibold">R$ <span x-text="fmt(total)"></span></div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Desconto</div>
                    <div class="text-lg font-semibold">R$ <span x-text="fmt(discount||0)"></span></div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Total a receber</div>
                    <div class="text-2xl font-bold text-gray-900">R$ <span x-text="fmt(netTotal())"></span></div>
                </div>
            </div>

            <div x-show="installmentsPreview().length > 0" class="mt-2">
                <h4 class="font-semibold text-gray-800 mb-2">Preview das parcelas</h4>
                <div class="rounded border border-gray-200 divide-y">
                    <template x-for="(p, i) in installmentsPreview()" :key="i">
                        <div class="flex items-center justify-between px-4 py-2">
                            <div>
                                <span class="text-sm text-gray-500">Parcela</span>
                                <span class="font-semibold" x-text="`${i+1}/${installments}`"></span>
                            </div>
                            <div class="text-sm text-gray-500">
                                Venc.: <span class="font-medium" x-text="p.due"></span>
                            </div>
                            <div class="font-semibold">R$ <span x-text="fmt(p.amount)"></span></div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex justify-end">
                <x-primary-button type="submit">Gerar cobrança</x-primary-button>
            </div>
        </form>
    </div>

    <script>
        function payForm(props) {
            return {
                total: Number(props.total || 0),
                method: '',
                discount: 0,
                installments: 1,
                firstDue: new Date().toISOString().slice(0, 10),

                netTotal() {
                    const v = this.total - (this.discount || 0);
                    return v > 0 ? v : 0;
                },
                fmt(v) {
                    return Number(v || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
                installmentsPreview() {
                    if (this.method !== 'credito') return [];
                    const n = Math.max(1, Number(this.installments || 1));
                    const total = this.netTotal();
                    const base = Math.floor((total / n) * 100) / 100;
                    const soma = base * n;
                    let resto = Math.round((total - soma) * 100) / 100;

                    const out = [];
                    for (let i = 0; i < n; i++) {
                        let v = base;
                        if (i === n - 1) v = Math.round((v + resto) * 100) / 100;
                        const d = new Date(this.firstDue);
                        d.setMonth(d.getMonth() + i);
                        const due = d.toISOString().slice(0, 10).split('-').reverse().join('/');
                        out.push({ amount: v, due });
                    }
                    return out;
                }
            }
        }
    </script>
</x-app-layout>