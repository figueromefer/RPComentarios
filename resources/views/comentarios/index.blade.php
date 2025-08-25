<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Comentarios
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                {{ session('status') }}
            </div>
        @endif

        <form method="get" class="mb-4 flex gap-2">
            <input name="q" value="{{ $q }}" class="border rounded px-3 py-2 w-full" placeholder="Buscar por texto...">
            <button class="px-4 py-2 bg-indigo-600 text-white rounded">Buscar</button>
        </form>

        <div class="bg-white shadow rounded">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-2">ID</th>
                        <th class="text-left px-4 py-2">Comentario</th>
                        <th class="text-left px-4 py-2">Estado</th>
                        <th class="text-left px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($comentarios as $c)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $c->TCO_PK_COMENTARIO }}</td>
                        <td class="px-4 py-2">{{ \Illuminate\Support\Str::limit($c->TCO_COMENTARIO, 80) }}</td>
                        <td class="px-4 py-2">{{ $c->TCO_FK_ESTADO_PUBLICACIONES }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('comentarios.edit', $c) }}" class="text-indigo-600 hover:underline">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin resultados</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $comentarios->links() }}
        </div>
    </div>
</x-app-layout>
