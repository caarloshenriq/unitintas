<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">Produtos</h2>
    </x-slot>

    <div x-data class="py-8 max-w-4xl mx-auto space-y-6">
    <div class="overflow-x-auto max-w-5xl mx-auto">
            <table class="min-w-full bg-white rounded shadow text-left">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-6 py-3">Nome</th>
                        <th class="px-6 py-3">Preço</th>
                        <th class="px-6 py-3">Código Cor</th>
                        <th class="px-6 py-3 text-right">
                            <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'create-product')">
                                Novo Produto
                            </x-primary-button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $product->name }}</td>
                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ $product->price ? 'R$ ' . number_format($product->price, 2, ',', '.') : 'Grátis' }}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $product->color_code }}</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <!-- Editar -->
                                <button class="text-yellow-500 hover:text-yellow-700"
                                    x-on:click.prevent="$dispatch('open-modal', 'edit-product-{{ $product->id }}')">
                                    <i class="fas fa-pen"></i>
                                </button>

                                <!-- Excluir -->
                                <button type="button" class="text-red-600 hover:text-red-800"
                                    x-on:click.prevent="$dispatch('open-modal', 'confirm-delete-{{ $product->id }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Editar Produto -->
                        <x-modal name="edit-product-{{ $product->id }}" maxWidth="md">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold mb-4">Editar {{ $product->name }}</h2>
                                <form action="{{ route('product.update', $product->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="space-y-4">
                                        <x-input-label for="name_{{ $product->id }}" value="Nome do Produto" />
                                        <x-text-input id="name_{{ $product->id }}" name="name"
                                            value="{{ $product->name }}" class="w-full" />

                                        <x-input-label for="price_{{ $product->id }}" value="Preço" />
                                        <x-text-input id="price_{{ $product->id }}" name="price" type="number" step="0.01"
                                            value="{{ $product->price }}" class="w-full" />

                                        <x-input-label for="color_code_{{ $product->id }}" value="Código da Cor" />
                                        <x-text-input id="color_code_{{ $product->id }}" name="color_code"
                                            value="{{ $product->color_code }}" class="w-full" />

                                        <div class="flex justify-end gap-2 mt-4">
                                            <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                                            <x-primary-button>Salvar</x-primary-button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </x-modal>

                        <!-- Modal Excluir Produto -->
                        <x-modal name="confirm-delete-{{ $product->id }}" focusable maxWidth="md">
                            <form method="POST" action="{{ route('product.destroy', $product->id) }}" class="p-6">
                                @csrf
                                @method('DELETE')
                                <div class="space-y-4 p-6">
                                    <h2 class="text-lg font-semibold text-gray-900 ">
                                        Tem certeza que deseja excluir?
                                    </h2>

                                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">
                                        Essa ação é <span class="font-semibold text-red-600">irreversível</span>.
                                        Confirme para excluir o produto <span
                                            class="font-medium text-gray-800">{{ $product->name }}</span>.
                                    </p>

                                    <div class="mt-6 flex justify-end gap-3">
                                        <x-secondary-button x-on:click="$dispatch('close')">
                                            Cancelar
                                        </x-secondary-button>

                                        <x-danger-button>
                                            Excluir
                                        </x-danger-button>
                                    </div>
                                </div>
                            </form>
                        </x-modal>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center px-6 py-4 text-gray-500">
                                Nenhum produto encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Criar Produto -->
    <x-modal name="create-product" :show="false" focusable>
        <form method="POST" action="{{ route('product.store') }}" class="p-6">
            @csrf

            <h2 class="text-lg font-medium text-gray-900">
                Adicionar novo produto
            </h2>

            <div class="mt-4">
                <x-input-label for="name" value="Nome do Produto" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required autofocus />
            </div>

            <div class="mt-4">
                <x-input-label for="price" value="Preço" />
                <x-text-input id="price" name="price" type="number" step="0.01" class="mt-1 block w-full" required />
            </div>

            <div class="mt-4">
                <x-input-label for="color_code" value="Código da Cor" />
                <x-text-input id="color_code" name="color_code" type="text" class="mt-1 block w-full" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>

                <x-primary-button class="ms-3">
                    Salvar
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
