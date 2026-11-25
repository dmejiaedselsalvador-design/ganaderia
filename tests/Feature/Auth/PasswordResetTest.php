<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La recuperación por correo se reemplazó por solicitudes atendidas
 * por un administrador (ver Tests\Feature\SolicitudClaveTest).
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_pantalla_de_olvido_de_clave_se_muestra(): void
    {
        $this->get('/forgot-password')->assertOk()->assertSee('Enviar solicitud al administrador');
    }
}
