<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar comentario #{{ $comentario->TCO_PK_COMENTARIO }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded p-6">
            <form method="post" action="{{ route('comentarios.update', $comentario) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block mb-1 font-medium">Comentario</label>
                    <textarea name="TCO_COMENTARIO" rows="5" class="w-full border rounded px-3 py-2">{{ old('TCO_COMENTARIO', $comentario->TCO_COMENTARIO) }}</textarea>
                    @error('TCO_COMENTARIO')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block mb-1 font-medium">Estado publicación</label>
                    <select name="TCO_FK_ESTADO_PUBLICACIONES" class="w-full border rounded px-3 py-2">
                        @foreach($estados as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('TCO_FK_ESTADO_PUBLICACIONES', $comentario->TCO_FK_ESTADO_PUBLICACIONES)==$id)>
                                {{ $nombre }} ({{ $id }})
                            </option>
                        @endforeach
                    </select>
                    @error('TCO_FK_ESTADO_PUBLICACIONES')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('comentarios.index') }}" class="px-4 py-2 border rounded">Cancelar</a>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
