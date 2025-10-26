<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Notifikasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Semua Notifikasi</h3>
                        <div class="flex space-x-2">
                            <form action="{{ route('notifications.mark_all_read') }}" method="POST">
                                @csrf
                                <button type="submit" class="text-sm text-blue-600 hover:underline">Tandai semua sudah dibaca</button>
                            </form>
                            <form action="{{ route('notifications.destroy_all') }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus semua notifikasi?');">
                                @csrf
                                <button type="submit" class="text-sm text-red-600 hover:underline">Hapus semua notifikasi</button>
                            </form>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @forelse ($notifications as $notification)
                            <div class="p-4 rounded-lg flex justify-between items-start {{ $notification->read_at ? 'bg-gray-50 dark:bg-gray-700/50' : 'bg-blue-50 dark:bg-blue-900/20' }}">
                                <div>
                                    <a href="{{ route('notifications.read', $notification->id) }}" class="hover:underline">
                                        <p class="font-medium">{!! $notification->data['message'] !!}</p>
                                    </a>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="flex-shrink-0 ml-4">
                                    <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type-="submit" class="text-gray-400 hover:text-red-500" title="Hapus notifikasi">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p>Tidak ada notifikasi.</p>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
