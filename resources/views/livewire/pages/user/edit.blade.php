{{-- resources/views/users/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Editar usuário
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto">
                @if (session('success'))
                    <div class="mb-4 rounded-md bg-green-50 p-3 text-green-800 ring-1 ring-green-200">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-100 p-6 sm:p-8">
                    <form method="POST" action="{{ route('users.update', $user->id) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        {{-- Dados básicos --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            {{-- Nome --}}
                            <div>
                                <x-input-label for="name" :value="__('Nome')" />
                                <x-text-input
                                    id="name"
                                    name="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    value="{{ old('name', $user->name) }}"
                                    required
                                    autofocus
                                    autocomplete="name"
                                />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            {{-- Email --}}
                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input
                                    id="email"
                                    name="email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    value="{{ old('email', $user->email) }}"
                                    required
                                    autocomplete="username"
                                />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Senha (opcional) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <x-input-label for="password" :value="__('Nova senha (opcional)')" />
                                <x-text-input
                                    id="password"
                                    name="password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    autocomplete="new-password"
                                />
                                <p class="mt-1 text-xs text-gray-500">Deixe em branco para não alterar.</p>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="password_confirmation" :value="__('Confirmar nova senha')" />
                                <x-text-input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full"
                                    autocomplete="new-password"
                                />
                            </div>
                        </div>

                        {{-- Permissões --}}
                        @php
                            $selectedPerms = old('selectedPermissions', $user->permissions->pluck('id')->toArray());
                        @endphp

                        <div
                            x-data="{
                                q: '',
                                selectAll() {
                                    this.$root.querySelectorAll('input[name=\'selectedPermissions[]\']').forEach(cb => cb.checked = true);
                                },
                                unselectAll() {
                                    this.$root.querySelectorAll('input[name=\'selectedPermissions[]\']').forEach(cb => cb.checked = false);
                                }
                            }"
                            class="border rounded-xl p-4 sm:p-5"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-800">Permissões</h3>
                                    <p class="text-sm text-gray-500">Marque as permissões que este usuário deve possuir.</p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <x-secondary-button type="button" x-on:click="selectAll()">Selecionar todas</x-secondary-button>
                                    <x-secondary-button type="button" x-on:click="unselectAll()">Limpar</x-secondary-button>
                                </div>
                            </div>

                            {{-- Busca local (Alpine) --}}
                            <div class="mt-4">
                                <x-text-input
                                    x-model="q"
                                    type="text"
                                    placeholder="Filtrar permissões…"
                                    class="w-full"
                                />
                            </div>

                            <div class="mt-4 max-h-80 overflow-y-auto divide-y rounded-md border">
                                @forelse ($permissions as $perm)
                                    <label
                                        class="flex items-center gap-3 px-4 py-3"
                                        x-show="$el.dataset.name.toLowerCase().includes(q.toLowerCase())"
                                        x-cloak
                                        data-name="{{ $perm->permission_name }}"
                                    >
                                        <input
                                            type="checkbox"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            name="selectedPermissions[]"
                                            value="{{ $perm->id }}"
                                            @checked(in_array($perm->id, $selectedPerms))
                                        >
                                        <span class="text-gray-800">{{ $perm->permission_name }}</span>
                                    </label>
                                @empty
                                    <div class="px-4 py-3 text-sm text-gray-500">Nenhuma permissão cadastrada.</div>
                                @endforelse
                            </div>

                            <x-input-error :messages="$errors->get('selectedPermissions')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ url()->previous() }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                Voltar
                            </a>
                            <x-primary-button>
                                Salvar alterações
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
