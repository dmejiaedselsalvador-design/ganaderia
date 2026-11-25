<?php

/*
| Mensajes de validación en español (los que faltan se toman de lang/en).
*/

return [
    'confirmed'        => 'La confirmación de :attribute no coincide.',
    'current_password' => 'La clave actual no es correcta.',
    'different'        => ':attribute y :other deben ser diferentes.',
    'email'            => ':attribute debe ser un correo electrónico válido.',
    'in'               => 'El valor seleccionado en :attribute no es válido.',
    'integer'          => ':attribute debe ser un número entero.',
    'max'              => [
        'numeric' => ':attribute no puede ser mayor que :max.',
        'string'  => ':attribute no puede tener más de :max caracteres.',
        'array'   => ':attribute no puede tener más de :max elementos.',
        'file'    => ':attribute no puede pesar más de :max kilobytes.',
    ],
    'min'              => [
        'numeric' => ':attribute debe ser al menos :min.',
        'string'  => ':attribute debe tener al menos :min caracteres.',
        'array'   => ':attribute debe tener al menos :min elementos.',
        'file'    => ':attribute debe pesar al menos :min kilobytes.',
    ],
    'not_in'           => 'El valor seleccionado en :attribute no es válido.',
    'numeric'          => ':attribute debe ser un número.',
    'password'         => [
        'letters'       => ':attribute debe contener al menos una letra.',
        'mixed'         => ':attribute debe contener al menos una mayúscula y una minúscula.',
        'numbers'       => ':attribute debe contener al menos un número.',
        'symbols'       => ':attribute debe contener al menos un símbolo.',
        'uncompromised' => ':attribute apareció en una filtración de datos. Elige otra.',
    ],
    'required'         => ':attribute es obligatorio.',
    'string'           => ':attribute debe ser texto.',
    'unique'           => 'Ese :attribute ya está registrado.',

    'attributes' => [
        'name'                  => 'El nombre',
        'email'                 => 'El correo',
        'password'              => 'La clave',
        'password_confirmation' => 'La confirmación de clave',
        'current_password'      => 'La clave actual',
        'telefono'              => 'El teléfono',
        'rol'                   => 'El rol',
        'display_name'          => 'El nombre del rol',
        'nivel'                 => 'El nivel',
    ],
];
