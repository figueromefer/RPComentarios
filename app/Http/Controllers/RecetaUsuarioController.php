<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RecetaUsuarioController extends Controller
{
    private const API_BASE_URL = 'http://149.56.15.70:3013';
    private const FK_COUNTRY = 1;

    private const ESTADOS = [
        1 => 'Aprobado',
        2 => 'Pendiente',
        3 => 'Rechazada',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $fkStatus = (int) $request->query('fkStatus', 2);

        if (! array_key_exists($fkStatus, self::ESTADOS)) {
            $fkStatus = 2;
        }

        $recetas = [];
        $error = null;

        try {
            $response = Http::timeout(30)
                ->get(self::API_BASE_URL.'/api/obtenerRecetasUsuario', [
                    'fkCountry' => self::FK_COUNTRY,
                    'fkStatus' => $fkStatus,
                ]);

            if ($response->successful()) {
                $recetas = $this->normalizeCollection($response->json());
            } else {
                $error = 'No se pudieron obtener las recetas. Código API: '.$response->status();
            }
        } catch (ConnectionException $exception) {
            $error = 'No se pudo conectar con la API de recetas.';
        }

        return view('recetas-usuario.index', [
            'recetas' => $recetas,
            'estados' => self::ESTADOS,
            'fkStatus' => $fkStatus,
            'error' => $error,
        ]);
    }

    public function edit(Request $request, int $receta)
    {
        $fkStatus = (int) $request->query('fkStatus', 2);
        $data = null;
        $error = null;

        try {
            $response = Http::timeout(30)
                ->get(self::API_BASE_URL.'/api/obtenerRecetasUsuario', [
                    'fkCountry' => self::FK_COUNTRY,
                    'fkStatus' => $fkStatus,
                ]);

            if ($response->successful()) {
                $data = collect($this->normalizeCollection($response->json()))
                    ->first(fn ($item) => (int) $this->value($item, ['pkReceta', 'id', 'PK_RECETA']) === $receta);
            } else {
                $error = 'No se pudo obtener el detalle de la receta. Código API: '.$response->status();
            }
        } catch (ConnectionException $exception) {
            $error = 'No se pudo conectar con la API de recetas.';
        }

        if (! $data && ! $error) {
            $error = 'No se encontró la receta en el listado del estatus seleccionado.';
            $data = ['id' => $receta, 'fkStatus' => $fkStatus];
        }

        return view('recetas-usuario.edit', [
            'receta' => $data,
            'estados' => self::ESTADOS,
            'fkStatus' => $fkStatus,
            'error' => $error,
        ]);
    }

    public function update(Request $request, int $receta)
    {
        $validated = $request->validate([
            'title' => ['required', 'string'],
            'time' => ['required', 'string'],
            'portion' => ['required', 'string'],
            'ingredients' => ['required', 'string'],
            'instructions' => ['required', 'string'],
            'fkStatus' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $recipePayload = [
            'pkReceta' => $receta,
            'title' => $validated['title'],
            'description' => '',
            'time' => $validated['time'],
            'portion' => $validated['portion'],
            'cal' => '',
            'ingredients' => $validated['ingredients'],
            'instructions' => $validated['instructions'],
        ];

        try {
            $updateResponse = Http::timeout(30)
                ->asForm()
                ->post(self::API_BASE_URL.'/api/actualizarRecetaUsuario', $recipePayload);

            if (! $updateResponse->successful()) {
                return back()->withInput()->withErrors([
                    'api' => 'No se pudo actualizar la receta. Código API: '.$updateResponse->status(),
                ]);
            }

            $statusResponse = Http::timeout(30)
                ->asForm()
                ->post(self::API_BASE_URL.'/api/cambiarStatusRecetaUsuario', [
                    'pkReceta' => $receta,
                    'fkStatus' => $validated['fkStatus'],
                ]);

            if (! $statusResponse->successful()) {
                return back()->withInput()->withErrors([
                    'api' => 'La receta se editó, pero no se pudo cambiar el estatus. Código API: '.$statusResponse->status(),
                ]);
            }
        } catch (ConnectionException $exception) {
            return back()->withInput()->withErrors([
                'api' => 'No se pudo conectar con la API de recetas.',
            ]);
        }

        return redirect()
            ->route('recetas-usuario.index', [
                'fkStatus' => $validated['fkStatus'],
            ])
            ->with('status', 'Receta actualizada correctamente.');
    }

    public function changeStatus(Request $request, int $receta)
    {
        $validated = $request->validate([
            'fkStatus' => ['required', 'integer', 'in:1,2,3'],
        ]);

        try {
            $response = Http::timeout(30)
                ->asForm()
                ->post(self::API_BASE_URL.'/api/cambiarStatusRecetaUsuario', [
                    'pkReceta' => $receta,
                    'fkStatus' => $validated['fkStatus'],
                ]);

            if (! $response->successful()) {
                return back()->withErrors([
                    'api' => 'No se pudo cambiar el estatus. Código API: '.$response->status(),
                ]);
            }
        } catch (ConnectionException $exception) {
            return back()->withErrors([
                'api' => 'No se pudo conectar con la API de recetas.',
            ]);
        }

        return redirect()
            ->route('recetas-usuario.index', [
                'fkStatus' => $validated['fkStatus'],
            ])
            ->with('status', 'Estatus actualizado correctamente.');
    }

    private function normalizeCollection(mixed $payload): array
    {
        if (is_array($payload) && array_is_list($payload)) {
            return $payload;
        }

        foreach (['mensaje', 'data', 'recetas', 'recipes', 'result', 'resultado'] as $key) {
            $value = data_get($payload, $key);
            if (is_array($value)) {
                return array_is_list($value) ? $value : [$value];
            }
        }

        return is_array($payload) ? [$payload] : [];
    }

    private function value(array $item, array $keys, string $default = ''): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $item) && $item[$key] !== null) {
                return $item[$key];
            }
        }

        return $default;
    }
}
