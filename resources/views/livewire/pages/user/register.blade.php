<?php

use App\Models\User;
use App\Models\Permission;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    // Campos do form
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /** Lista de permissões para exibir no modal */
    public $permissions;

    /** IDs das permissões selecionadas */
    public array $selectedPermissions = [];

    public function mount(): void
    {
        // Carrega permissões (ajuste colunas conforme seu schema)
        $this->permissions = Permission::query()
            ->orderBy('permission_name')
            ->get(['id','permission_name']);
    }

    public function register(): void
    {
        $validated = $this->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','string','lowercase','email','max:255','unique:'.User::class],
            'password' => ['required','string','confirmed', Rules\Password::defaults()],
            'selectedPermissions'   => ['array'],
            'selectedPermissions.*' => ['integer','exists:permissions,id'],
        ]);

        // Prepara senha
        $validated['password'] = Hash::make($validated['password']);

        // Cria usuário
        $user = User::create($validated);

        // Vincula permissões (pivot permissionuser: user_id, permission_id)
        $user->permissions()->sync($this->selectedPermissions);

        // Redireciona
        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">Cadastrar usuário</h2>
    </x-slot>

    <div class="py-8">
        <div class="container mx-auto px-4">
            <div class="max-w-md mx-auto">
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-100 p-6 sm:p-8">

                    <!-- FORM DE CRIAÇÃO -->
                    <form wire:submit="register" class="space-y-5">

                        <!-- Nome -->
                        <div>
                            <x-input-label for="name" :value="__('Nome')" />
                            <x-text-input
                                wire:model="name"
                                id="name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                                autocomplete="name"
                            />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- E-mail -->
                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input
                                wire:model="email"
                                id="email"
                                type="email"
                                class="mt-1 block w-full"
                                required
                                autocomplete="username"
                            />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Senha -->
                        <div>
                            <x-input-label for="password" :value="__('Senha')" />
                            <x-text-input
                                wire:model="password"
                                id="password"
                                type="password"
                                class="mt-1 block w-full"
                                required
                                autocomplete="new-password"
                            />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Confirmar Senha -->
                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirme sua senha')" />
                            <x-text-input
                                wire:model="password_confirmation"
                                id="password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                                required
                                autocomplete="new-password"
                            />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <!-- Seleção de Permissões -->
                        <div class="pt-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Permissões</span>
                                <x-secondary-button
                                    x-on:click.prevent="$dispatch('open-modal', 'pick-permissions')">
                                    Selecionar
                                </x-secondary-button>
                            </div>

                            <div class="mt-2 text-sm text-gray-600">
                                @if(count($selectedPermissions))
                                    {{ count($selectedPermissions) }} permissão(ões) selecionada(s).
                                @else
                                    Nenhuma permissão selecionada.
                                @endif
                            </div>
                            <x-input-error :messages="$errors->get('selectedPermissions')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                               href="{{ route('login') }}" wire:navigate>
                                {{ __('Já possui uma conta?') }}
                            </a>

                            <x-primary-button>
                                {{ __('Registrar') }}
                            </x-primary-button>
                        </div>
                    </form>
                    <!-- /FORM -->
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: ESCOLHER PERMISSÕES -->
    <x-modal name="pick-permissions" :show="false" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-semibold mb-4">Selecione as permissões</h2>

            <div class="max-h-80 overflow-y-auto divide-y">
                @foreach ($permissions as $perm)
                    <label class="flex items-center gap-3 py-3">
                        <input
                            type="checkbox"
                            value="{{ $perm->id }}"
                            wire:model="selectedPermissions"
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-gray-800">{{ $perm->permission_name }}</span>
                    </label>
                @endforeach
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <x-secondary-button x-on:click="$dispatch('close')">Fechar</x-secondary-button>
                <x-primary-button x-on:click="$dispatch('close')">Concluir</x-primary-button>
            </div>
        </div>
    </x-modal>

</div> <!-- /root -->
