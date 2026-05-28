<?php

use Illuminate\Support\Str;
use App\Models\Post;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    #[Validate('required|string|min:3|max:255')]
    public string $title = "";

    #[Validate('nullable|string|max:500')]
    public string $excerpt = "";

    #[Validate('required|string|min:10')]
    public string $content = "";

    #[Validate('nullable|image|max:2048')]
    public $featured_image;

    #[Validate('required|in:draft,published')]
    public string $status = 'draft';

    public function save()
    {
         $this->validate();

         $post = new Post();
         $post->user_id = auth()->id();
         $post->title = $this->title;
         $post->slug = Str::slug($this->title);
         $post->excerpt = $this->excerpt;
         $post->content = $this->content;
         $post->status = $this->status;

         if ($this->featured_image) {
            $path = $this->featured_image->store('posts', 'public');
            $post->featured_image = $path;
         }
        
         if ($this->status === 'published')
         {
            $post->published_at = now();
         }

         $post->save();

         session()->flash('success', 'Post created successfully!');

         $this->redirect(route('posts.index'), true);
    }
};
?>

<div>
    {{-- The only way to do great work is to love what you do. - Steve Jobs --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create New Post</h1>
        <p class="mt-1 text-sm text-gray-600">Write and publish your blog post</p>
    </div>

    {{-- form --}}
    <form wire:submit="save" class="space-y-6">
        <!-- Title -->
        <flux:input 
            label="Title"
            type="text"
            id="title"
            wire:model.live.debounce="title"
            placeholder="Enter post title"
            autofocus
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
        <flux:input 
            label="Featured Image"
            type="file"
            wire:model="featured_image"
            accept="image/*"
        />

        <!-- Categories -->
        {{-- <div>
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
        </div> --}}

        <!-- Tags -->
        {{-- <div>
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
            <p class="mt-1 text-sm text-gray-500">Select relevant tags to help readers find your content</p>
        </div> --}}

        <!-- Status -->
        <flux:radio.group
            label="Status"
        >
            <flux:radio 
                name="draft"
                value="draft"
                label="Draft"
                description="Save as draft, not visible to readers"
                wire:model="status"
            />

            @can('publish posts')
                <flux:radio 
                    name="published"
                    value="published"
                    label="Published"
                    description="Publish immediately, visible to all readers"
                    wire:model="status"
                />
            @endcan
        </flux:radio.group>

        <!-- Actions -->
        <div class="flex gap-3">
            <flux:button
                type="submit"
            >
                Create Post
            </flux:button>
            <flux:button
                href="{{ route('posts.index') }}"
            >
                Cancel
            </flux:button>
        </div>
    </form>
</div>