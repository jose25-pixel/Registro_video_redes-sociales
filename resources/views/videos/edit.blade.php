<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar video</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('videos.update', $video) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('videos._form')
                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('videos.index') }}" class="rounded-md px-4 py-2 text-sm text-gray-600">Cancelar</a>
                        <button class="rounded-md bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800">Actualizar video</button>
                    </div>
                </form>
            </div>

            <section class="rounded-lg border border-sky-100 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Listas de reproducción del video</h3>
                @if ($video->playlists->isNotEmpty())
                    <ul class="mt-3 space-y-2">
                        @foreach ($video->playlists as $playlist)
                            <li><a href="{{ $playlist->url }}" target="_blank" rel="noopener" class="break-all text-sm font-medium text-sky-700 hover:underline">{{ $playlist->name }}</a></li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-2 text-sm text-gray-500">Este video todavía no está asociado a una lista.</p>
                @endif

                <form method="POST" action="{{ route('videos.playlists.store', $video) }}" class="mt-5 grid gap-4 border-t border-gray-100 pt-5 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label for="playlist_title" class="block text-sm font-medium text-gray-700">Nombre de la nueva lista</label>
                        <input id="playlist_title" name="playlist_title" value="{{ old('playlist_title') }}" maxlength="255" class="mt-1 block w-full rounded-md border-gray-300">
                        @error('playlist_title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="playlist_url" class="block text-sm font-medium text-gray-700">Enlace de la nueva lista</label>
                        <input id="playlist_url" name="playlist_url" type="url" value="{{ old('playlist_url') }}" maxlength="255" required class="mt-1 block w-full rounded-md border-gray-300">
                        @error('playlist_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <button class="rounded-md bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800">Agregar solo esta lista</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>