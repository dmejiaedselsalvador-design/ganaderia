<?php

namespace Tests\Feature;

use App\Models\PreguntaAyuda;
use App\Models\User;
use Database\Seeders\RolesYPermisosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AyudaTest extends TestCase
{
    use RefreshDatabase;

    private User $operario;
    private User $gerente;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(RolesYPermisosSeeder::class);

        $this->operario = User::factory()->create();
        $this->operario->assignRole('operario');
        $this->operario->givePermissionTo('planta.ganaderia');

        $this->gerente = User::factory()->create();
        $this->gerente->assignRole('gerente');
        $this->gerente->givePermissionTo('planta.ganaderia');
    }

    private function png(): string
    {
        // PNG válido de 1×1
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
    }

    public function test_pantalla_muestra_su_ayuda_y_el_boton(): void
    {
        $this->actingAs($this->operario)->post(route('plantas.cambiar'), ['planta' => 'ganaderia']);
        $this->get(route('nutricion.index'))->assertOk()
            ->assertSee('Ayuda de esta pantalla')->assertSee('Raciones y comida servida')->assertSee('¿No te quedó claro?');
    }

    public function test_usuario_envia_pregunta_con_captura_y_ve_la_respuesta(): void
    {
        $this->actingAs($this->operario)->postJson(route('ayuda.store'), [
            'pregunta' => '¿Por qué me sugiere 700 kg?', 'ruta' => 'nutricion.planeacion',
            'url' => 'http://localhost/nutricion/planeacion', 'captura' => $this->png(),
        ])->assertCreated();

        $p = PreguntaAyuda::first();
        $this->assertSame('Plan de alimentación', $p->pantalla);
        $this->assertNotNull($p->captura_path);
        Storage::disk('local')->assertExists($p->captura_path);

        // Su captura solo la ven él y quien responde
        $this->get(route('ayuda.captura', $p))->assertOk();
        $otro = User::factory()->create();
        $otro->assignRole('vendedor');
        $this->actingAs($otro)->get(route('ayuda.captura', $p))->assertForbidden();

        // El operario no puede entrar a la bandeja
        $this->actingAs($this->operario)->get(route('ayuda.index'))->assertForbidden();

        // El gerente responde
        $this->actingAs($this->gerente)->get(route('ayuda.index'))->assertOk()->assertSee('¿Por qué me sugiere 700 kg?');
        $this->put(route('ayuda.responder', $p), ['respuesta' => 'Porque el corral pesa 25,000 kg al 2.8 %.'])->assertSessionHasNoErrors();
        $this->assertSame('respondida', $p->fresh()->estado);
        $this->assertSame(1, PreguntaAyuda::respuestasNuevas($this->operario->id)->count());

        // Al abrir el panel queda leída
        $this->actingAs($this->operario)->postJson(route('ayuda.vistas'))->assertOk();
        $this->assertSame(0, PreguntaAyuda::respuestasNuevas($this->operario->id)->count());
    }

    public function test_captura_invalida_se_ignora_y_pregunta_vacia_se_rechaza(): void
    {
        $this->actingAs($this->operario)->postJson(route('ayuda.store'), ['pregunta' => ''])->assertJsonValidationErrors('pregunta');

        $this->postJson(route('ayuda.store'), ['pregunta' => 'No entiendo esta parte', 'ruta' => 'ruta.que.no.existe',
            'captura' => 'data:image/png;base64,' . base64_encode('<?php echo 1; ?>')])->assertCreated();
        $p = PreguntaAyuda::first();
        $this->assertNull($p->captura_path);
        $this->assertNull($p->ruta);
        $this->assertSame('Ayuda', $p->pantalla);
    }
}
