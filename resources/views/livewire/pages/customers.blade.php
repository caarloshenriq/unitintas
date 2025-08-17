{{-- resources/views/customers/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">Clientes</h2>
    </x-slot>

    <div x-data class="py-8 max-w-4xl mx-auto space-y-6">
        <div class="overflow-x-auto max-w-5xl mx-auto">
            <table class="min-w-full bg-white rounded shadow text-left">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-6 py-3">Nome</th>
                        <th class="px-6 py-3">CPF/CNPJ (parcial)</th>
                        <th class="px-6 py-3">Telefone</th>
                        <th class="px-6 py-3">Endereço</th>
                        <th class="px-6 py-3 text-right">
                            <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'create-customer')">
                                Novo Cliente
                            </x-primary-button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        @php
                            $docDigits   = preg_replace('/\D/', '', (string)($customer->document ?? ''));
                            $docMasked   = strlen($docDigits) > 3 ? substr($docDigits, 0, 3) . str_repeat('•', strlen($docDigits) - 3) : $docDigits;
                            $phoneDigits = preg_replace('/\D/', '', (string)($customer->phone ?? ''));
                            if (strlen($phoneDigits) === 11) {
                                $phoneMasked = sprintf('(%s) %s-%s', substr($phoneDigits,0,2), substr($phoneDigits,2,5), substr($phoneDigits,7,4));
                            } elseif (strlen($phoneDigits) === 10) {
                                $phoneMasked = sprintf('(%s) %s-%s', substr($phoneDigits,0,2), substr($phoneDigits,2,4), substr($phoneDigits,6,4));
                            } else {
                                $phoneMasked = $customer->phone;
                            }
                        @endphp

                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $customer->name }}</td>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $docMasked }}</td>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $phoneMasked }}</td>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $customer->address }}</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button class="text-yellow-500 hover:text-yellow-700"
                                    x-on:click.prevent="$dispatch('open-modal', 'edit-customer-{{ $customer->id }}')">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <button type="button" class="text-red-600 hover:text-red-800"
                                    x-on:click.prevent="$dispatch('open-modal', 'confirm-delete-{{ $customer->id }}')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Editar Cliente -->
                        <x-modal name="edit-customer-{{ $customer->id }}" maxWidth="md">
                            @php
                                $isCnpj = strlen($docDigits) === 14;
                            @endphp
                            <div class="p-6">
                                <h2 class="text-lg font-semibold mb-4">Editar {{ $customer->name }}</h2>
                                <form action="{{ route('customer.update', $customer->id) }}" method="POST" class="space-y-4" data-strip-masks>
                                    @csrf
                                    @method('PUT')

                                    <div>
                                        <x-input-label for="name_{{ $customer->id }}" value="Nome" />
                                        <x-text-input id="name_{{ $customer->id }}" name="name" value="{{ $customer->name }}" class="w-full" />
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-3 mb-2">
                                            <x-input-label value="Tipo de documento" />
                                            <label class="inline-flex items-center cursor-pointer">
                                                <span class="mr-2 text-sm">CPF</span>
                                                <input type="checkbox" class="sr-only peer" data-doc-toggle @checked($isCnpj)>
                                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 relative transition">
                                                    <span class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-5"></span>
                                                </div>
                                                <span class="ml-2 text-sm">CNPJ</span>
                                            </label>
                                        </div>

                                        <input type="hidden" name="document_type" value="{{ $isCnpj ? 'cnpj' : 'cpf' }}">

                                        <x-input-label for="document_{{ $customer->id }}" data-doc-label
                                            :value="'Documento (' . ($isCnpj ? 'CNPJ' : 'CPF') . ')'"/>
                                        <x-text-input id="document_{{ $customer->id }}" name="document" type="text"
                                            value="{{ $customer->document }}" class="w-full" data-doc-input />
                                        <p class="text-sm text-red-600 mt-1" data-doc-error></p>
                                    </div>

                                    <div>
                                        <x-input-label for="email_{{ $customer->id }}" value="E-mail" />
                                        <x-text-input id="email_{{ $customer->id }}" name="email" type="email"
                                            value="{{ $customer->email }}" class="w-full" data-email-input />
                                    </div>

                                    <div>
                                        <x-input-label for="phone_{{ $customer->id }}" value="Telefone" />
                                        <x-text-input id="phone_{{ $customer->id }}" name="phone" type="text"
                                            value="{{ $customer->phone }}" class="w-full" data-phone-input />
                                    </div>

                                    <div>
                                        <x-input-label for="address_{{ $customer->id }}" value="Endereço" />
                                        <x-text-input id="address_{{ $customer->id }}" name="address" value="{{ $customer->address }}" class="w-full" />
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="city_{{ $customer->id }}" value="Cidade" />
                                            <x-text-input id="city_{{ $customer->id }}" name="city" value="{{ $customer->city }}" class="w-full" />
                                        </div>
                                        <div>
                                            <x-input-label for="state_{{ $customer->id }}" value="Estado" />
                                            <x-text-input id="state_{{ $customer->id }}" name="state" value="{{ $customer->state }}" class="w-full" />
                                        </div>
                                        <div>
                                            <x-input-label for="zip_{{ $customer->id }}" value="CEP" />
                                            <x-text-input id="zip_{{ $customer->id }}" name="zip" value="{{ $customer->zip }}" class="w-full" />
                                        </div>
                                    </div>

                                    <div class="flex justify-end gap-2">
                                        <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                                        <x-primary-button>Salvar</x-primary-button>
                                    </div>
                                </form>
                            </div>
                        </x-modal>

                        <!-- Modal Excluir Cliente -->
                        <x-modal name="confirm-delete-{{ $customer->id }}" focusable maxWidth="md">
                            <form method="POST" action="{{ route('customer.destroy', $customer->id) }}" class="p-6">
                                @csrf
                                @method('DELETE')
                                <div class="space-y-4 p-6">
                                    <h2 class="text-lg font-semibold text-gray-900">Tem certeza que deseja excluir?</h2>
                                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">
                                        Essa ação é <span class="font-semibold text-red-600">irreversível</span>.
                                        Confirme para excluir o cliente <span class="font-medium text-gray-800">{{ $customer->name }}</span>.
                                    </p>
                                    <div class="mt-6 flex justify-end gap-3">
                                        <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                                        <x-danger-button>Excluir</x-danger-button>
                                    </div>
                                </div>
                            </form>
                        </x-modal>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center px-6 py-4 text-gray-500">Nenhum cliente encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Criar Cliente -->
    <x-modal name="create-customer" :show="false" focusable>
        <form method="POST" action="{{ route('customer.store') }}" class="p-6" data-strip-masks>
            @csrf

            <h2 class="text-lg font-medium text-gray-900">Adicionar novo cliente</h2>

            <div class="mt-4">
                <x-input-label for="name" value="Nome" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required autofocus />
            </div>

            <div class="mt-4">
                <div class="flex items-center gap-3 mb-2">
                    <x-input-label value="Tipo de documento" />
                    <label class="inline-flex items-center cursor-pointer">
                        <span class="mr-2 text-sm">CPF</span>
                        <input type="checkbox" class="sr-only peer" data-doc-toggle>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 relative transition">
                            <span class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-5"></span>
                        </div>
                        <span class="ml-2 text-sm">CNPJ</span>
                    </label>
                </div>

                <input type="hidden" name="document_type" value="cpf">

                <x-input-label for="document" data-doc-label value="Documento (CPF)" />
                <x-text-input id="document" name="document" type="text" class="mt-1 block w-full" data-doc-input required />
                <p class="text-sm text-red-600 mt-1" data-doc-error></p>
            </div>

            <div class="mt-4">
                <x-input-label for="email" value="E-mail" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" data-email-input />
            </div>
            
            <div class="mt-4">
                <x-input-label for="phone" value="Telefone" />
                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" data-phone-input />
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="address" value="Endereço" />
                    <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="city" value="Cidade" />
                    <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="state" value="Estado" />
                    <x-text-input id="state" name="state" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="zip" value="CEP" />
                    <x-text-input id="zip" name="zip" type="text" class="mt-1 block w-full" />
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-primary-button class="ms-3">Salvar</x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
