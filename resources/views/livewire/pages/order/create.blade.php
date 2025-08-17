<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">Novo Pedido</h2>
    </x-slot>

    <div x-data="orderForm()" class="py-8 max-w-5xl mx-auto space-y-6">
        <form method="POST" action="{{ route('orders.store') }}" class="space-y-6">
            @csrf

            @if ($errors->any())
                <div class="rounded bg-red-50 text-red-700 p-3 text-sm">
                    <ul class="list-disc ml-5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white p-6 rounded shadow space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="customer_id" value="Cliente" />
                        <select id="customer_id" name="customer_id" class="w-full border-gray-300 rounded" required>
                            <option value="">Selecione...</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label value="Total do pedido" />
                        <div class="h-10 flex items-center px-3 bg-gray-50 rounded border border-gray-200">
                            <span>R$ </span>
                            <span class="ml-1 font-semibold" x-text="formatMoney(grandTotal())">0,00</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded shadow space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Itens</h3>
                    <x-primary-button type="button" x-on:click="addItem()">Adicionar item</x-primary-button>
                </div>

                <template x-if="items.length === 0">
                    <p class="text-sm text-gray-500">Nenhum item adicionado.</p>
                </template>

                <div class="space-y-3">
                    <template x-for="(it, idx) in items" :key="it.key">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end border rounded p-3">

                            <div class="md:col-span-6">
                                <x-input-label for="product_0" x-bind:for="`product_${idx}`" value="Produto" />
                                <select class="w-full border-gray-300 rounded"
                                        x-bind:id="`product_${idx}`"
                                        x-bind:name="`items[${idx}][product_id]`"
                                        x-model="it.product_id"
                                        x-on:change="syncPrice(idx)"
                                        required>
                                    <option value="">Selecione...</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-price="{{ $p->price }}">
                                            {{ $p->name }} — R$ {{ number_format($p->price, 2, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="qty_0" value="Kg" />
                                <x-text-input id="qty_0"
                                              x-bind:id="`qty_${idx}`"
                                              x-bind:name="`items[${idx}][quantity]`"
                                              type="number" step="0.001" min="0.001"
                                              class="w-full"
                                              x-model.number="it.quantity"
                                              required />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="price_0" value="Preço (R$)" />
                                <x-text-input id="price_0"
                                              x-bind:id="`price_${idx}`"
                                              type="number" step="0.01" min="0"
                                              class="w-full bg-gray-50"
                                              x-model.number="it.price"
                                              readonly />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label value="Subtotal (R$)" />
                                <div class="h-10 flex items-center px-3 bg-gray-50 rounded border border-gray-200">
                                    <span x-text="formatMoney(it.quantity * it.price)">0,00</span>
                                </div>
                            </div>

                            <button type="button"
                                    class="text-red-600 hover:text-red-800 md:col-span-12 justify-self-end"
                                    x-on:click="removeItem(idx)">
                                Remover
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('orders.index') }}">
                    <x-secondary-button>Cancelar</x-secondary-button>
                </a>
                <x-primary-button type="submit" class="ml-3">Salvar Pedido</x-primary-button>
            </div>
        </form>
    </div>

    <script>
        function orderForm() {
            return {
                items: [],

                addItem() {
                    this.items.push({
                        key: Date.now() + Math.random(),
                        product_id: '',
                        quantity: 1.000,
                        price: 0.00
                    });
                },
                removeItem(i) {
                    this.items.splice(i, 1);
                },
                syncPrice(i) {
                    const select = document.getElementById(`product_${i}`);
                    const opt = select?.selectedOptions?.[0];
                    const price = parseFloat(opt?.dataset?.price || '0');
                    this.items[i].price = isNaN(price) ? 0 : price;
                },
                grandTotal() {
                    return this.items.reduce((acc, it) =>
                        acc + (parseFloat(it.quantity || 0) * parseFloat(it.price || 0)), 0);
                },
                formatMoney(v) {
                    const n = Number(v || 0);
                    return n.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            }
        }
    </script>
</x-app-layout>
