@php
    $recetaId = data_get($receta, 'id', data_get($receta, 'pkReceta'));
    $image = data_get($receta, 'image');
    $video = data_get($receta, 'youtube');
    $mediaBaseUrl = 'https://storage.googleapis.com/rp-recipes-dev-images/';
    $imageUrl = $image ? $mediaBaseUrl.ltrim($image, '/') : null;
    $videoUrl = $video ? $mediaBaseUrl.ltrim($video, '/') : null;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar receta #{{ $recetaId ?? 'N/D' }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded p-6">

            @if ($error)
                <div class="mb-4 p-3 bg-yellow-100 text-yellow-800 rounded">
                    {{ $error }}
                </div>
            @endif

            @if ($errors->has('api'))
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                    {{ $errors->first('api') }}
                </div>
            @endif

            @if (session('api_debug'))
                <div class="mb-6 p-4 bg-gray-900 text-gray-100 rounded overflow-auto">
                    <div class="font-semibold mb-2">Debug API</div>
                    <pre class="text-xs whitespace-pre-wrap">{{ json_encode(session('api_debug'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block mb-1 font-medium">Imagen</label>
                    @if($imageUrl)
                        <img src="{{ $imageUrl }}" alt="Imagen de receta"
                             class="block rounded border bg-gray-50"
                             style="display:block;width:auto !important;height:auto !important;max-width:400px !important;max-height:300px !important;object-fit:contain;">
                        <a href="{{ $imageUrl }}" target="_blank" class="inline-block mt-2 text-indigo-600 hover:underline">
                            Abrir imagen
                        </a>
                    @else
                        <div class="border rounded p-4 text-gray-500 bg-gray-50" style="max-width:400px;">Sin imagen</div>
                    @endif
                </div>

                <div>
                    <label class="block mb-1 font-medium">Video</label>
                    @if($videoUrl)
                        <video src="{{ $videoUrl }}" controls
                               class="block rounded border bg-gray-50"
                               style="display:block;width:auto !important;height:auto !important;max-width:400px !important;max-height:300px !important;">
                        </video>
                        <a href="{{ $videoUrl }}" target="_blank" class="inline-block mt-2 text-indigo-600 hover:underline">
                            Abrir video
                        </a>
                    @else
                        <div class="border rounded p-4 text-gray-500 bg-gray-50" style="max-width:400px;">Sin video</div>
                    @endif
                </div>
            </div>

            <form method="post" action="{{ route('recetas-usuario.update', $recetaId) }}">
                @csrf
                @method('PUT')

                @if(request()->boolean('debugApi'))
                    <input type="hidden" name="debugApi" value="1">
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-medium">Título</label>
                        <input type="text" name="title" class="w-full border rounded px-3 py-2"
                            value="{{ old('title', data_get($receta, 'title', data_get($receta, 'titulo'))) }}">
                        @error('title') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block mb-2 font-medium">Estatus</label>
                        @php
                            $selectedStatus = (int) old('fkStatus', data_get($receta, 'fkStatus', $fkStatus));
                            $statusStyles = [
                                1 => 'border-green-500 bg-green-50 text-green-800 box-shadow:0 0 0 2px #bbf7d0;',
                                2 => 'border-yellow-500 bg-yellow-50 text-yellow-800 box-shadow:0 0 0 2px #fde68a;',
                                3 => 'border-red-500 bg-red-50 text-red-800 box-shadow:0 0 0 2px #fecaca;',
                            ];
                            $statusDotStyles = [
                                1 => 'background:#22c55e;',
                                2 => 'background:#eab308;',
                                3 => 'background:#ef4444;',
                            ];
                        @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2" id="status-options">
                            @foreach($estados as $id => $nombre)
                                @php $isSelected = $selectedStatus === (int) $id; @endphp
                                <label data-status-option="{{ $id }}" class="cursor-pointer rounded-lg border px-3 py-3 text-sm font-semibold transition {{ $isSelected ? 'border-green-500 bg-green-50 text-green-800' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}" style="{{ $isSelected ? ($statusStyles[$id] ?? '') : '' }}">
                                    <input type="radio" name="fkStatus" value="{{ $id }}" class="sr-only" @checked($isSelected) onchange="window.updateStatusCards && window.updateStatusCards(this.value)">
                                    <span class="flex items-center gap-2">
                                        <span data-status-dot="{{ $id }}" class="h-2.5 w-2.5 rounded-full" style="{{ $statusDotStyles[$id] ?? 'background:#9ca3af;' }}"></span>
                                        {{ $nombre }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('fkStatus') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block mb-1 font-medium">Tiempo</label>
                        <input type="text" name="time" class="w-full border rounded px-3 py-2"
                            value="{{ old('time', data_get($receta, 'time')) }}">
                        @error('time') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Porciones</label>
                        <input type="text" name="portion" class="w-full border rounded px-3 py-2"
                            value="{{ old('portion', data_get($receta, 'portions', data_get($receta, 'portion'))) }}">
                        @error('portion') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block mb-1 font-medium">Ingredientes</label>
                    <textarea name="ingredients" rows="6" class="w-full border rounded px-3 py-2">{{ old('ingredients', data_get($receta, 'ingredients')) }}</textarea>
                    @error('ingredients') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mt-4">
                    <label class="block mb-1 font-medium">Instrucciones</label>
                    <textarea name="instructions" rows="8" class="w-full border rounded px-3 py-2">{{ old('instructions', data_get($receta, 'instructions')) }}</textarea>
                    @error('instructions') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="flex gap-2 mt-6">
                    <a href="{{ route('recetas-usuario.index', ['fkStatus' => $fkStatus]) }}" class="px-4 py-2 border rounded">
                        Cancelar
                    </a>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.updateStatusCards = function (selectedValue) {
            const styles = {
                '1': { cls: 'border-green-500 bg-green-50 text-green-800', shadow: '0 0 0 2px #bbf7d0' },
                '2': { cls: 'border-yellow-500 bg-yellow-50 text-yellow-800', shadow: '0 0 0 2px #fde68a' },
                '3': { cls: 'border-red-500 bg-red-50 text-red-800', shadow: '0 0 0 2px #fecaca' }
            };

            document.querySelectorAll('[data-status-option]').forEach(function (label) {
                const status = label.getAttribute('data-status-option');
                label.className = 'cursor-pointer rounded-lg border px-3 py-3 text-sm font-semibold transition ' +
                    (status === selectedValue ? styles[status].cls : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50');
                label.style.boxShadow = status === selectedValue ? styles[status].shadow : '';
            });
        };
    </script>
</x-app-layout>
