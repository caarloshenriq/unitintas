<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\User;
use Doctrine\Inflector\Rules\English\Rules;
use Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();

        return view('livewire.pages.user.users', compact('users'));
    }


    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::with('permissions:id')->findOrFail($id);

        // Se sua coluna é 'permission_name', carregue assim:
        $permissions = \App\Models\Permission::orderBy('permission_name')
            ->get(['id', 'permission_name']);

        return view('livewire.pages.user.edit', compact('user', 'permissions'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255'
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        // Pivot das permissões
        if ($request->has('selectedPermissions')) {
            $user->permissions()->sync($request->input('selectedPermissions', []));
        } else {
            $user->permissions()->detach();
        }

        $user->fill(collect($validated)->except(['password', 'selectedPermissions'])->toArray());

        if ($request->filled('password')) {
            $this->resetPass($user, $request->input('password'));
        }

        $user->save();

        $logController = new LogController();
        $logController->registerLog('Update', 'User - id: ' . $id);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    /**
     * Troca a senha do usuário e marca o flag reset_password.
     */
    private function resetPass(User $user, string $newPassword): void
    {
        $user->forceFill([
            'password' => Hash::make($newPassword),
            'reset_password' => true,
        ]);
        $logController = new LogController();
        $logController->registerLog('Reset password', 'User - id: ' . $user->id);
    }

    public function forceResetPassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        $user->forceFill([
            'password'       => Hash::make($validated['password']),
            'reset_password' => false,
        ])->save();


        return back()->with('success', 'Senha alterada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        $logController = new LogController();
        $logController->registerLog('Delete', 'User - id: ' . $id);

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
