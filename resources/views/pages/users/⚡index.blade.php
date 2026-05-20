<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Permission\Models\Role;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = 'all';

    public function with(): array
    {
        $query = User::with('roles')->latest();

        // Filter by search
        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
        }

        // Filter by role
        if ($this->roleFilter !== 'all') {
            $query->whereHas('roles', function ($q) {
                $q->where('name', $this->roleFilter);
            });
        }

        return [
            'users' => $query->paginate(10),
            'roles' => Role::all(),
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function deleteUser(User $user): void
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account!');
            return;
        }

        $user->delete();
        session()->flash('success', 'User deleted successfully!');
    }
};
?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Users</h1>
        <p class="mt-1 text-sm text-gray-600">Manage user accounts and roles</p>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <flux:input 
                type="text"
                icon="magnifying-glass"
                wire:model.live.debounce.300ms="search" 
                placeholder="Search users..." 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            />
        </div>

        <div class="sm:w-48">
            <flux:select
                wire:model.live="roleFilter"
                placeholder="Select roles"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <flux:select.option value="all">All roles</flux:select.option>
                @foreach($roles as $role)
                    <flux:select.option value="{{ $role->name }}">{{ ucfirst($role->name) }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div>
            <flux:button
                href="{{ route('users.create') }}"
                icon:trailing="plus"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-md uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                New User
            </flux:button>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session('success'))
        <flux:toast variant="success" position="top center"/>
    @endif

    @if (session('error'))
        <flux:toast variant="error" position="top center" />
    @endif

    <!-- Users Table -->
    <div class="overflow-x-auto">
        <flux:table :paginate="$users">
            <flux:table.columns>
                <flux:table.column>User</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Roles</flux:table.column>
                <flux:table.column>Joined</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($users as $user)
                    <flux:table.row wire:key="user-{{ $user->id }}">
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:avatar size="xs" name="{{ $user->name }}" />

                            {{ $user->name}}
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">{{ $user->email }}</flux:table.cell>

                        <flux:table.cell>
                            @forelse($user->roles as $role)
                                <flux:badge size="sm" inset="top bottom">{{ ucfirst($role->name) }}</flux:badge>
                            @empty
                                <flux:badge size="sm" inset="top bottom">No Role</flux:badge>
                            @endforelse
                        </flux:table.cell>

                        <flux:table.cell>{{ $user->created_at->format('M d, Y') }}</flux:table.cell>

                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>

                                <flux:menu>
                                    <flux:menu.item href="{{ route('users.edit', $user) }}" icon="pencil-square" kbd="⌘E">Edit</flux:menu.item>

                                    @if($user->id !== auth()->id())
                                        <flux:menu.item wire:click="deleteUser({{ $user->id }})" wire:confirm="Are you sure you want to delete this user?" icon="trash" kbd="⌘D">Delete</flux:menu.item>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row class="text-center">
                        No users found
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>