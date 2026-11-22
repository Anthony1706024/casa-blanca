<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Pedido que registra el personal (mozo o caja). La mesa es opcional: en caja puede ser para llevar.
class PedidoPersonalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dni' => 'required|digits:8',
            'cliente_nombre' => 'nullable|string|max:150',
            'mesa' => 'nullable|string|max:20',
            'observacion' => 'nullable|string|max:255',
            'items' => 'required|array',
            'items.*' => 'integer|min:0|max:99',
        ];
    }

    public function messages(): array
    {
        return [
            'dni.required' => 'El DNI del cliente es obligatorio.',
            'dni.digits' => 'El DNI debe tener 8 dígitos.',
            'items.required' => 'Selecciona al menos un producto.',
        ];
    }
}
