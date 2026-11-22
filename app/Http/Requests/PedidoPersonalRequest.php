<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Pedido que registra el personal (mozo o caja). Solo la mesa es obligatoria:
// hay clientes que no quieren dar su DNI ni su nombre.
class PedidoPersonalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dni' => 'nullable|digits:8',
            'cliente_nombre' => 'nullable|string|max:150',
            'mesa' => 'required|string|max:20',
            'observacion' => 'nullable|string|max:255',
            'items' => 'required|array',
            'items.*' => 'integer|min:0|max:99',
        ];
    }

    public function messages(): array
    {
        return [
            'dni.digits' => 'El DNI debe tener 8 dígitos (o déjalo vacío).',
            'mesa.required' => 'Indica el número de mesa.',
            'items.required' => 'Selecciona al menos un producto.',
        ];
    }
}
