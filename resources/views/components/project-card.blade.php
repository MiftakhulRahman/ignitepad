@props(['project'])

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden transition-transform duration-300 hover:scale-[1.02]">
    <a href="{{ route('projects.show', $project) }}">
        <div class="w-full h-48 bg-gray-200 dark:bg-gray-700">
            @if($project->thumbnail)
                <img src="{{ Storage::url($project->thumbnail) }}" alt="{{ $project->title }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l-1.586-1.586a2 2 0 00-2.828 0L6 14m6-6l.01.01"></path></svg>
                </div>
            @endif
        </div>
    </a>

    <div class="p-4">
        <div class="mb-2">
            @forelse($project->categories->take(2) as $category)
                <a href="{{ route('categories.show', $category) }}" 
                   class="inline-block bg-blue-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded 
                          dark:bg-blue-900 dark:text-blue-300 
                          hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                    {{ $category->name }}
                </a>
            @empty
                <span class="inline-block bg-gray-100 text-gray-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">
                    Tanpa Kategori
                </span>
            @endforelse
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
            <a href="{{ route('projects.show', $project) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                {{ $project->title }}
            </a>
        </h3>

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
            {{ Str::limit($project->description, 100) }}
        </p>

        <div class="flex items-center">
            <img class="h-8 w-8 rounded-full object-cover mr-2" 
                 src="{{ $project->user->photo ? Storage::url($project->user->photo) : 'https://ui-avatars.com/api/?name='.urlencode($project->user->name).'&color=7F9CF5&background=EBF4FF' }}" 
                 alt="{{ $project->user->name }}">
            <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $project->user->name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Angkatan {{ $project->user->batch_year ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>