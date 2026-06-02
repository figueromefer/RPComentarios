@php
    use Illuminate\Support\Str;

    $getValue = function (array $item, array $keys, $default = '') {
        foreach ($keys as $key) {
            if (array_key_exists($key, $item) && $item[$key] !== null && $item[$key] !== '') {
                return $item[$key];
            }
        }

        return $default;
    };

    $badgeClasses = [
        1 => 'bg-green-100 text-green-800',
        2 => 'bg-yellow-100 text-yellow-800',
        3 => 'bg-red-100 text-red-800',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Recetas Usuario
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                {{ session('status') }}
            </div>
        @endif

        @if ($error)
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                {{ $error }}
            </div>
        @endif

        @if ($errors->has('api'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                {{ $errors->first('api') }}
            </div>
        @endif

        <form method="get" class="mb-4 flex flex-col sm:flex-row gap-2 sm:items-end">
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">Estatus</label>
                <select name="fkStatus" class="border rounded px-3 py-2 min-w-52">
                    @foreach($estados as $id => $nombre)
                        <option value="{{ $id }}" @selected((int) $fkStatus === (int) $id)>
                            {{ $nombre }} ({{ $id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <button class="px-4 py-2 bg-indigo-600 text-white rounded">
                Filtrar
            </button>
        </form>

        <div class="bg-white shadow rounded overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-2">ID</th>
                        <th class="text-left px-4 py-2">Título</th>
                        <th class="text-left px-4 py-2">Descripción</th>
                        <th class="text-left px-4 py-2">Tiempo</th>
                        <th class="text-left px-4 py-2">Porción</th>
                        <th class="text-left px-4 py-2">Estatus</th>
                        <th class="text-left px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($recetas as $receta)
                    @php
                        $pkReceta = $getValue($receta, ['pkReceta', 'id', 'PK_RECETA']);
                        $title = $getValue($receta, ['title', 'titulo', 'TITULO'], 'Sin título');
                        $description = $getValue($receta, ['description', 'descripcion', 'DESCRIPCION']);
                        $time = $getValue($receta, ['time', 'tiempo', 'TIEMPO']);
                        $portion = $getValue($receta, ['portion', 'porcion', 'PORCION']);
                        $rowStatus = (int) $getValue($receta, ['fkStatus', 'status', 'FK_STATUS'], $fkStatus);
                    @endphp

                    <tr class="border-t align-top">
                        <td class="px-4 py-2 whitespace-nowrap">{{ $pkReceta }}</td>
                        <td class="px-4 py-2 font-medium text-gray-900">
                            {{ Str::limit($title, 60) }}
                        </td>
                        <td class="px-4 py-2 text-gray-700">
                            {{ Str::limit($description, 90) }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $time }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $portion }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $badgeClasses[$rowStatus] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $estados[$rowStatus] ?? $rowStatus }}
                            </span>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            @if($pkReceta)
                                <a href="{{ route('recetas-usuario.edit', ['receta' => $pkReceta, 'fkStatus' => $fkStatus]) }}" class="text-indigo-600 hover:underline">
                                    Editar
                                </a>
                            @else
                                <span class="text-gray-400">Sin ID</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                            Sin recetas para este estatus.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
