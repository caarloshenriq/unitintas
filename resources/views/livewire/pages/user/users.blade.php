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
                        <th class="px-6 py-3">e-mail</th>
                        <th class="px-6 py-3">Criado em</th>
                        <th class="px-6 py-3 text-right">
                            <x-primary-button x-on:click="window.location='{{ route('register') }}'">
                                Novo Usuario
                            </x-primary-button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $user->name }}</td>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $user->email }}</td>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button type="button" class="text-yellow-500 hover:text-yellow-700"
                                    x-on:click="window.location='{{ route('users.edit', $user->id) }}'"
                                    title="Editar usuário">
                                    <i class="fa-solid fa-pen"></i>
                                </button>


                                <button type="button" class="text-red-600 hover:text-red-800"
                                    x-on:click.prevent="$dispatch('open-modal', 'confirm-delete-{{ $user->id }}')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <x-modal name="confirm-delete-{{ $user->id }}" focusable maxWidth="md">
                            <form method="POST" action="{{ route('users.destroy', $user->id) }}" class="p-6">
                                @csrf
                                @method('DELETE')
                                <div class="space-y-4 p-6">
                                    <h2 class="text-lg font-semibold text-gray-900">Tem certeza que deseja excluir?</h2>
                                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">
                                        Essa ação é <span class="font-semibold text-red-600">irreversível</span>.
                                        Confirme para excluir o usuario <span
                                            class="font-medium text-gray-800">{{ $user->name }}</span>.
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
                            <td colspan="5" class="text-center px-6 py-4 text-gray-500">
                                Nenhum usuario encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>