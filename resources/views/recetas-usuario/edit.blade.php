<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar receta #{{ data_get($receta, 'pkReceta', 'N/D') }}
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

            <form method="post" action="{{ route('recetas-usuario.update', data_get($receta, 'pkReceta')) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-medium">Título</label>
                        <input type="text" name="title" class="w-full border rounded px-3 py-2"
                            value="{{ old('title', data_get($receta, 'title', data_get($receta, 'titulo'))) }}">
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Estatus</label>
                        <select name="fkStatus" class="w-full border rounded px-3 py-2">
                            @foreach($estados as $id => $nombre)
                                <option value="{{ $id }}" @selected(old('fkStatus', data_get($receta, 'fkStatus', $fkStatus)) == $id)>
                                    {{ $nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block mb-1 font-medium">Descripción</label>
                    <textarea name="description" rows="4" class="w-full border rounded px-3 py-2">{{ old('description', data_get($receta, 'description', data_get($receta, 'descripcion'))) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block mb-1 font-medium">Tiempo</label>
                        <input type="text" name="time" class="w-full border rounded px-3 py-2"
                            value="{{ old('time', data_get($receta, 'time')) }}">
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Porción</label>
                        <input type="text" name="portion" class="w-full border rounded px-3 py-2"
                            value="{{ old('portion', data_get($receta, 'portion')) }}">
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Calorías</label>
                        <input type="text" name="cal" class="w-full border rounded px-3 py-2"
                            value="{{ old('cal', data_get($receta, 'cal')) }}">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block mb-1 font-medium">Ingredientes</label>
                    <textarea name="ingredients" rows="6" class="w-full border rounded px-3 py-2">{{ old('ingredients', data_get($receta, 'ingredients')) }}</textarea>
                </div>

                <div class="mt-4">
                    <label class="block mb-1 font-medium">Instrucciones</label>
                    <textarea name="instructions" rows="8" class="w-full border rounded px-3 py-2">{{ old('instructions', data_get($receta, 'instructions')) }}</textarea>
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
</x-app-layout>
