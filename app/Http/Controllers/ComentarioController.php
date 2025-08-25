<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateComentarioRequest;

class ComentarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // protege todo con login
    }

    public function index(Request $request)
    {
        // Búsqueda simple opcional
        $q = $request->get('q');
        $comentarios = Comentario::when($q, function ($query) use ($q) {
                $query->where('TCO_COMENTARIO', 'like', "%{$q}%");
            })
            ->orderByDesc('TCO_PK_COMENTARIO')
            ->paginate(15)
            ->withQueryString();

        return view('comentarios.index', compact('comentarios', 'q'));
    }

    public function edit(Comentario $comentario)
    {
        // Si tienes una tabla de estados, cárgala aquí:
        // $estados = EstadoPublicacion::orderBy('NOMBRE')->pluck('NOMBRE','ESP_PK_ESTADO');
        // Para ejemplo, un arreglo estático:
        $estados = [
            1 => 'Pendiente',
            2 => 'Publicado',
            3 => 'Rechazado',
        ];

        return view('comentarios.edit', compact('comentario', 'estados'));
    }

    public function update(UpdateComentarioRequest $request, Comentario $comentario)
    {
        $comentario->update($request->validated());
        return redirect()->route('comentarios.index')->with('status', 'Comentario actualizado correctamente.');
    }
    // Si solo necesitas listar/editar, puedes omitir create/store/show/destroy
    public function show(Comentario $comentario) { return redirect()->route('comentarios.edit', $comentario); }
    public function create() { abort(404); }
    public function store() { abort(404); }
    public function destroy() { abort(404); }
}
