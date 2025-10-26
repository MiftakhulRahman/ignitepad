<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Proyek: ') }} {{ $project->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    
                    <form method="POST" action="{{ route('student.projects.update', $project) }}" enctype="multipart/form-data">
                        @method('PUT')
                        
                        @include('student.projects._form', [
                            'project' => $project, // Kirim model berisi data
                            'buttonText' => 'Update Proyek'
                        ])
                    </form>
                </div>
            </div>

            {{-- Form-form untuk hapus gambar diletakkan di sini, di luar form utama --}}
            @if(isset($project) && $project->images->isNotEmpty())
                @foreach($project->images as $image)
                    <form id="delete-image-form-{{ $image->id }}" 
                          action="{{ route('student.projects.image.destroy', $image) }}" 
                          method="POST" 
                          class="hidden"
                          onsubmit="return confirm('Anda yakin ingin menghapus screenshot ini?');">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            @endif
        </div>
    </div>
</x-app-layout>