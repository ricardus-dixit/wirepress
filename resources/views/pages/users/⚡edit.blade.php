<?php

use Livewire\Component;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

new class extends Component
{
    public User $user;

    #[Validate('required|string|max:255')]
    public string $name = '';

    public string $email = '';

    #[Validate('nullable|string|min:8')]
    public string $password = '';

    #[Validate('required|array|min:1')]
    public array $selectedRoles = [];

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->user->id)],
            'password' => 'nullable|string|min:8',
            'selectedRoles' => 'required|array|min:1',
        ];
    }

     public function with(): array
    {
        return [
            'roles' => Role::all(),
        ];
    }

    public function update(): void
    {
        $this->validate();

        $this->user->name = $this->name;
        $this->user->email = $this->email;
        
        if ($this->password) {
            $this->user->password = Hash::make($this->password);
        }

        $this->user->save();

        $this->user->syncRoles($this->selectedRoles);

        Flux::toast(
            text: 'User updated successfully!',
            variant: 'success',
        );

        $this->redirect(route('users.index'), navigate: true);
    }
     
};
?>

<div>
    {{-- Breathing in, I calm body and mind. Breathing out, I smile. - Thich Nhat Hanh --}}
     <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>
        <p class="mt-1 text-sm text-gray-600">Update user information</p>
    </div>

    <form wire:submit="update" class="space-y-6">
        <!-- Name -->
        <flux:input
            label="Name"
            type="text"
            id="name"
            wire:model="name"
         />

        <!-- Email -->
        <flux:input 
            label="Email"
            type="email"
            id="email"
            wire:model="email"
        />

        <!-- Password -->
        <flux:input 
            label="New Password"
            id="password" 
            wire:model="password"
            placeholder="Leave blank to keep current password"
        />

        <!-- Roles -->
        <flux:checkbox.group
            label="Roles"
            wire:model="selectedRoles"
        >
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
                Update user
            </flux:button>
            <flux:button
                href="{{ route('users.index') }}"
            >
                Cancel
            </flux:button>
        </div>
    </form>
</div>