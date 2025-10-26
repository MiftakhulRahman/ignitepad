<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Proyek Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    
                    <form method="POST" action="{{ route('student.projects.store') }}" enctype="multipart/form-data">
                        @include('student.projects._form', [
                            'project' => new \App\Models\Project(), // Kirim model kosong
                            'buttonText' => 'Simpan & Tambah Proyek'
                        ])
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>