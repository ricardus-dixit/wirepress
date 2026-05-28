<?php

use Livewire\Component;
use App\Models\Post;
use Flux\Flux;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all';

    public function with(): array
    {
        $query = Post::with('user')->latest();

        // Filter by search
        if ($this->search)
        {
            $query
                ->where('title', 'like', "{%$this->search%}")
                ->orWhere('content', 'like', "{%$this->search%}");
        }

        // Filter by status
        if ($this->status !== 'all')
        {
            $query->where('status', $this->status);
        }

        // Authorization: Author only see own posts
        if (auth()->user()->hasRole('author'))
        {
            $query->where('user_id', auth()->id);
        }

        return [
            'posts' => $query->paginate(10)
        ]; 
    }

    public function updateSearch()
    {
        return $this->resetPage();
    }

    public function updateStatus()
    {
        return $this->resetPage(); 
    }

    public function delete(Post $post)
    {
        if (($post->user_id == auth()->user()->id && auth()->user()->can('delete own posts'))
            || auth()->user()->can('delete all posts'))
        {
            $post->delete();

            Flux::toast(
                variant: 'success',
                position: 'top center',
                heading: 'User deleted successfully!'
            );
        }
    }
};
?>

<div>
    {{-- Knowing is not enough; we must apply. Being willing is not enough; we must do. - Leonardo da Vinci --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Posts</h1>
        <p class="mt-1 text-sm text-gray-600">Manage your blog posts</p>
    </div>
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <flux:input
                type="text"
                icon="magnifying-glass"
                wire:model.live.debounce.300ms="search"
                placeholder="Search posts..."
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 ring:border-indigo-500"
            />
        </div>

        <div class="sm:w-48">
            <flux:select
                wire:model.live="status"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 ring:border-indigo-500"
            >
                <flux:select.option value="all">All Posts</flux:select.option>
                <flux:select.option value="draft">Draft</flux:select.option>
                <flux:select.option value="published">Published</flux:select.option>
                <flux:select.option value="archived">Archived</flux:select.option>
            </flux:select>
        </div>

        @can('create posts')
            <div>
                <flux:button 
                    href="{{ route('posts.create') }}"
                    icon:trailing="plus"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-md uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    New Post
                </flux:button>
            </div>
        @endcan
    </div>

    {{-- Success Message --}}
    @if (session('success'))
        <d<flux:toast variant="success" position="top center"/>
    @endif

    {{-- posts table --}}
    <div class="overflow-x-auto mt-6">
        <flux:table :paginate="$posts">
            <flux:table.columns>
                <flux:table.column>Title</flux:table.column>
                {{-- <flux:table.column>Categories</flux:table.column> --}}
                <flux:table.column>Author</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Created</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($posts as $post)
                    <flux:table.row wire:key="post-{{ $post->id }}">
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:heading>{{ $post->title }}</flux:heading>
                            <flux:text class="mt-2">{{ Str::limit($post->excerpt, 50) }}</flux:text>

                            @if($post->comments_count > 0)
                            <div class="mt-1">
                                <flux:badge color="blue" inset="top bottom">
                                    <flux:icon.chat-bubble-left-ellipsis class="size-3 mr-1" />

                                    {{ $post->comments_count }}
                                    {{ Str::plural('comment', $post->comments_count) }}
                                </flux:badge>
                            </div>
                            @endif
                        </flux:table.cell>
                        {{-- <flux:table.cell>
                            @forelse($post->categories as $category)
                                <flux:badge color="{{ $category->color }}">{{ $category->name }}</flux:badge>
                            @empty
                                <flux:badge color="gray">No category</flux:badge>
                            @endforelse
                        </flux:table.cell> --}}
                        <flux:table.cell>{{ $post->user->name }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge
                                :color="match($post->status) {
                                    'published' => 'green',
                                    'draft' => 'yellow',
                                    'archived' => 'zinc',
                                }"
                            >
                                {{ ucfirst($post->status) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $post->created_at->format('M d, Y') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>

                                <flux:menu>
                                    @if(auth()->user()->can('edit all posts') || 
                                        (auth()->user()->can('edit own posts') && $post->user_id === auth()->id()))
                                        <flux:menu.item href="{{ route('posts.edit', $post) }}" class="text-indigo-600 hover:text-indigo-900">
                                            Edit
                                        </flux:menu.item>
                                    @endif

                                    @if(auth()->user()->can('delete all posts') || 
                                        (auth()->user()->can('delete own posts') && $post->user_id === auth()->id()))
                                        <flux:menu.item
                                            wire:click="deletePost({{ $post->id }})"
                                            wire:confirm="Are you sure you want to delete this post?"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </flux:menu.item>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row class="text-center">
                        <flux:table.cell colspan="5">No posts found</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>