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
                $error = 'No se pudieron obtener las recetas. Código API: '.$response->status().' '.$this->apiMessage($response);
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
                $error = 'No se pudo obtener el detalle de la receta. Código API: '.$response->status().' '.$this->apiMessage($response);
            }
        } catch (ConnectionException $exception) {
            $error = 'No se pudo conectar con la API de recetas.';
        }

        if (! $data && ! $error) {
            $error = 'No se encontró la receta en el listado del estatus seleccionado.';
            $data = ['id' => $receta, 'fkStatus' => $fkStatus];
        }

        if (is_array($data)) {
            $data['ingredients'] = $this->brToTextarea($data['ingredients'] ?? '');
            $data['instructions'] = $this->brToTextarea($data['instructions'] ?? '');
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

        $debugEnabled = $request->boolean('debugApi');
        $apiDebug = [];

        $recipePayload = [
            'pkReceta' => $receta,
            'title' => $validated['title'],
            'description' => '',
            'time' => $validated['time'],
            'portion' => $validated['portion'],
            'cal' => '',
            'ingredients' => $this->textareaToBr($validated['ingredients']),
            'instructions' => $this->textareaToBr($validated['instructions']),
        ];

        try {
            $updateUrl = self::API_BASE_URL.'/api/actualizarRecetaUsuario';
            $updateResponse = Http::timeout(30)
                ->asForm()
                ->post($updateUrl, $recipePayload);

            $apiDebug[] = [
                'label' => 'Actualizar receta',
                'method' => 'POST',
                'url' => $updateUrl,
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'body' => $recipePayload,
                'response_status' => $updateResponse->status(),
                'response_body' => $updateResponse->body(),
            ];

            if (! $updateResponse->successful()) {
                return $this->backWithApiError($debugEnabled, $apiDebug, 'No se pudo actualizar la receta. Código API: '.$updateResponse->status().' '.$this->apiMessage($updateResponse));
            }

            $statusResponse = $this->sendStatusChange($receta, (int) $validated['fkStatus'], $apiDebug);

            if (! $statusResponse->successful()) {
                return $this->backWithApiError($debugEnabled, $apiDebug, 'La receta se editó, pero no se pudo cambiar el estatus. Código API: '.$statusResponse->status().' '.$this->apiMessage($statusResponse));
            }
        } catch (ConnectionException $exception) {
            return $this->backWithApiError($debugEnabled, $apiDebug, 'No se pudo conectar con la API de recetas.');
        }

        if ($debugEnabled) {
            return back()->withInput()->with('api_debug', $apiDebug)->with('status', 'Debug API generado. Copia esta información para compartirla con el programador.');
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
            $apiDebug = [];
            $response = $this->sendStatusChange($receta, (int) $validated['fkStatus'], $apiDebug);

            if (! $response->successful()) {
                return back()->withErrors([
                    'api' => 'No se pudo cambiar el estatus. Código API: '.$response->status().' '.$this->apiMessage($response),
                ])->with('api_debug', $apiDebug);
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

    private function sendStatusChange(int $receta, int $fkStatus, array &$apiDebug = [])
    {
        $url = self::API_BASE_URL.'/api/cambiarStatusRecetaUsuario';
        $headers = [
            'pkReceta' => (string) $receta,
            'fkStatus' => (string) $fkStatus,
        ];

        $response = Http::timeout(30)
            ->withHeaders($headers)
            ->post($url);

        $apiDebug[] = [
            'label' => 'Cambiar estatus',
            'method' => 'POST',
            'url' => $url,
            'headers' => $headers,
            'body' => [],
            'response_status' => $response->status(),
            'response_body' => $response->body(),
        ];

        return $response;
    }

    private function backWithApiError(bool $debugEnabled, array $apiDebug, string $message)
    {
        $response = back()->withInput()->withErrors([
            'api' => $message,
        ]);

        return $debugEnabled ? $response->with('api_debug', $apiDebug) : $response;
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

    private function textareaToBr(string $value): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", trim($value));
        return str_replace("\n", '<br>', $normalized);
    }

    private function brToTextarea(string $value): string
    {
        return preg_replace('/<br\s*\/?>/i', "\n", $value) ?? $value;
    }

    private function apiMessage($response): string
    {
        $json = $response->json();

        if (is_array($json)) {
            foreach (['mensaje', 'message', 'error', 'msg'] as $key) {
                $value = data_get($json, $key);
                if (is_string($value) && $value !== '') {
                    return '- '.$value;
                }
            }
        }

        $body = trim($response->body());
        return $body !== '' ? '- '.mb_substr($body, 0, 250) : '';
    }
}
