<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Category;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Illuminate\Support\Str;

new class extends Component
{
    use WithFileUploads;

    public Post $post;

    #[Validate('required|string|min:3|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:500')]
    public string $excerpt = '';

    #[Validate('required|string|min:10')]
    public string $content = '';

    #[Validate('nullable|image|max:2048')]
    public $featured_image;

    #[Validate('required|in:draft,published,archived')]
    public string $status = '';

    public string $existing_image = '';

    // #[Validate('required|array|min:1')]
    // public array $selectedCategories = [];

    // #[Validate('nullable|array')]
    // public array $selectedTags = [];

    public function mount(Post $post): void
    {
        // Authorization check
        if (!auth()->user()->can('edit all posts') && 
            !(auth()->user()->can('edit own posts') && $post->user_id === auth()->id())) {
            abort(403);
        }

        $this->post = $post;
        $this->title = $post->title;
        $this->excerpt = $post->excerpt ?? '';
        $this->content = $post->content;
        $this->status = $post->status;
        $this->existing_image = $post->featured_image ?? '';

        // Load existing categories and tags
        // $this->selectedCategories = $post->categories->pluck('id')->toArray();
        // $this->selectedTags = $post->tags->pluck('id')->toArray();
    }

    // public function with(): array
    // {
    //     return [
    //         'categories' => Category::all(), 
    //         'tags' => Tag::all(), 
    //     ];
    // }

    public function update(): void
    {
        $this->validate();

        $this->post->title = $this->title;
        $this->post->slug = Str::slug($this->title);
        $this->post->excerpt = $this->excerpt;
        $this->post->content = $this->content;
        $this->post->status = $this->status;

        if ($this->featured_image) {
            // Delete old image if exists
            if ($this->existing_image) {
                Storage::disk('public')->delete($this->existing_image);
            }
            
            $path = $this->featured_image->store('posts', 'public');
            $this->post->featured_image = $path;
            $this->existing_image = $path;
        }

        if ($this->status === 'published' && !$this->post->published_at) {
            $this->post->published_at = now();
        }

        $this->post->save();

        // Sync categories and tags
        // $this->post->categories()->sync($this->selectedCategories);
        // $this->post->tags()->sync($this->selectedTags);


        Flux::toast(
            text: "Post updated successfully!",
            variant: "success"
        );
        
        $this->redirect(route('posts.index'), navigate: true);
    }

};
?>

<div>
    {{-- Do what you can, with what you have, where you are. - Theodore Roosevelt --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Post</h1>
        <p class="mt-1 text-sm text-gray-600">Update your blog post</p>
    </div>

    <form wire:submit="update" class="space-y-6">
        <!-- Title -->
        <flux:input 
            label="Title"
            type="text"
            id="title"
            wire:model.live.debounce="title"
            placeholder="Enter post title"
        />

        <!-- Excerpt -->
        <flux:input 
            label="Excerpt"
            id="excerpt"
            wire:model="excerpt"
            rows="2"
            placeholder="A short summary of your post (optional)"
        />

        <!-- Content -->
        <flux:textarea 
            label="Content"
            wire:model="content"
        />

        <!-- Featured Image -->
        <!-- Label -->
        @if($existing_image && !$featured_image)
            <p>Current image:</p>
            <flux:avatar
                size="2xl"
                src="{{ Storage::url($existing_image) }}"
                alt="Current image"
            />
        @endif

        <flux:input 
            label="Featured Image"
            type="file"
            accept="image/*"
            wire:model="featured_image"
        />

        @if($featured_image)
            <p>New image:</p>
            <flux:avatar 
                wire:transition
                src="{{ $featured_image->temporaryUrl() }}"
            />
        @endif

        <!-- Categories -->
        {{-- 
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Categories (Required)
            </label>
            <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-md p-3">
                @foreach($categories as $category)
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            wire:model="selectedCategories" 
                            value="{{ $category->id }}"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        />
                        <span class="ml-3 flex items-center">
                            <span 
                                class="inline-block w-3 h-3 rounded-full mr-2" 
                                style="background-color: {{ $category->color }}"
                            ></span>
                            <span class="text-sm font-medium text-gray-700">{{ $category->name }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('selectedCategories')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tags -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Tags (Optional)
            </label>
            <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-md p-3">
                @foreach($tags as $tag)
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            wire:model="selectedTags" 
                            value="{{ $tag->id }}"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        />
                        <span class="ml-3 text-sm font-medium text-gray-700">{{ $tag->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('selectedTags')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div> --}}

        <!-- Status -->
        <flux:radio.group
            wire:model="status"
            label="Status"
        >
            <flux:radio
                value="draft"
                label="Draft"
                description="A short description to draft posts"
            />

            @can('publish posts')
                <flux:radio
                    value="published"
                    label="Published"
                    description="A description to published posts"
                />

                <flux:radio
                    value="archived"
                    label="Archived"
                    description="Another description to archived posts"
                />
            @endcan
        </flux:radio.group>

        <!-- Actions -->
        <div class="flex gap-3">
            <flux:button
                type="submit"
            >
                Update Post
            </flux:button>
            <flux:button
                href="{{ route('posts.index') }}"
            >
                Cancel
            </flux:button>
        </div>
    </form>
</div>