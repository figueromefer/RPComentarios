<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComentarioRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'TCO_COMENTARIO' => ['required','string','max:2000'],
            'TCO_FK_ESTADO_PUBLICACIONES' => ['required','integer'],
        ];
    }
}
