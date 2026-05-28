<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;

new class extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|email|max:255|unique:users')]
    public string $email = '';

    #[Validate('required|string|min:8')]
    public string $password = '';

    #[Validate('required|array|min:1')]
    public array $selectedRoles = [];

    public function with(): array
    {
        return [
            'roles' => Role::all(),
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $user->assignRole($this->selectedRoles);

        Flux::toast(
            variant: 'success',
            position: 'top center',
            heading: 'User created successfully!'
        );
        
        $this->redirect(route('users.index'), navigate: true);
    }
};
?>

<div>
    {{-- Simplicity is the essence of happiness. - Cedric Bledsoe --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create New User</h1>
        <p class="mt-1 text-sm text-gray-600">Add a new user to the system</p>
    </div>

    <form wire:submit="save" class="space-y-6">
        <!-- Name -->
        <flux:input label="Name" 
            type="text"
            id="name"
            wire:model="name" 
            placeholder="Enter user's full name"
            autofocus
        />

        <!-- Email -->
        <flux:input 
            label="Email"
            type="email"
            id="email"
            wire:model="email" 
            placeholder="user@example.com"
        />

        <!-- Password -->
        <flux:input 
            label="Password"
            type="password"
            id="password"
            wire:model="password" 
            placeholder="Minimum 8 characters"
        />

        <!-- Roles -->
        <flux:checkbox.group wire:model="selectedRoles" label="Roles">
            @foreach($roles as $role)
                <flux:checkbox
                    value="{{ $role->name }}"
                    label="{{ ucfirst($role->name) }}"
                    description="{{ $role->description }}"
                />
            @endforeach
        </flux:checkbox.group>

        <!-- Actions -->
        <div class="flex gap-3">
            <flux:button
                type="submit" 
            >
                Create User
            </flux:button>

            <flux:button
                href="{{ route('users.index') }}" 
            >
                Cancel
            </flux:button>
        </div>
    </form>
</div>