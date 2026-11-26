{{--
    Diseño común de los PDF (dompdf). Uso:
    @extends('pdf.layout', ['titulo' => 'Liquidación de ganado', 'subtitulo' => '...', 'meta' => ['Guía' => 'G-1', 'Fecha' => '...'], 'horizontal' => false])
    @section('contenido') ... @endsection
--}}
@php
    $planta = \App\Support\Plantas::info('ganaderia') ?? [];
    // Logo en SVG: dompdf lo dibuja sin necesitar la extensión GD de PHP
    $logoPath = public_path('favicon-ganaderia.svg');
    $logo = is_file($logoPath) ? 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath)) : null;
    $meta = $meta ?? [];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>{{ $titulo }}</title>
<style>
    @page { margin: 110px 42px 70px 42px; }
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #1f2937; line-height: 1.45; }

    /* Encabezado y pie fijos en cada página */
    .encabezado { position: fixed; top: -88px; left: 0; right: 0; height: 74px; border-bottom: 2px solid #065f46; }
    .encabezado table { width: 100%; border-collapse: collapse; }
    .encabezado td { vertical-align: top; padding: 0; border: 0; }
    .marca-logo { width: 40px; height: 40px; border-radius: 8px; }
    .marca-nombre { font-size: 13px; font-weight: bold; color: #064e3b; letter-spacing: .3px; }
    .marca-sub { font-size: 8px; color: #6b7280; text-transform: uppercase; letter-spacing: 1.5px; }
    .doc-titulo { font-size: 15px; font-weight: bold; color: #111827; text-align: right; text-transform: uppercase; letter-spacing: .5px; }
    .doc-meta { text-align: right; font-size: 8.5px; color: #4b5563; margin-top: 3px; }
    .doc-meta strong { color: #111827; }
    .pie { position: fixed; bottom: -48px; left: 0; right: 0; height: 30px; border-top: 1px solid #d1d5db; padding-top: 6px; font-size: 7.5px; color: #9ca3af; }

    /* Bloques */
    h2 { font-size: 10px; font-weight: bold; color: #064e3b; text-transform: uppercase; letter-spacing: .8px; margin: 18px 0 6px; padding-bottom: 4px; border-bottom: 1px solid #d1fae5; }
    .subtitulo { color: #6b7280; font-size: 9px; margin: 0 0 12px; }
    .ficha { width: 100%; border-collapse: collapse; background: #f9fafb; border: 1px solid #e5e7eb; }
    .ficha td { padding: 8px 10px; vertical-align: top; border: 0; width: 25%; }
    .etiqueta { display: block; font-size: 7px; color: #6b7280; text-transform: uppercase; letter-spacing: .8px; margin-bottom: 2px; }
    .valor { font-size: 10px; font-weight: bold; color: #111827; }
    .nota { font-size: 8px; color: #6b7280; }

    /* Tablas */
    table.datos { width: 100%; border-collapse: collapse; }
    table.datos th { background: #f3f4f6; color: #374151; font-size: 7.5px; text-transform: uppercase; letter-spacing: .6px; text-align: left; padding: 6px 8px; border-bottom: 1px solid #d1d5db; }
    table.datos td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; }
    table.datos tr.promedio td { background: #f9fafb; color: #4b5563; font-size: 8.5px; border-top: 1px solid #e5e7eb; }
    table.datos tr.total td { font-weight: bold; color: #111827; border-top: 1.5px solid #374151; border-bottom: 0; }
    .r { text-align: right !important; } .c { text-align: center !important; }
    .mono { font-family: DejaVu Sans Mono, monospace; }
    .verde { color: #047857; } .rojo { color: #b91c1c; } .azul { color: #1d4ed8; } .gris { color: #6b7280; }

    /* Resumen */
    .resumen { width: 100%; border-collapse: collapse; margin-top: 16px; }
    .resumen td { vertical-align: top; border: 0; padding: 0; }
    .cuenta { width: 100%; border-collapse: collapse; border: 1px solid #e5e7eb; }
    .cuenta td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; }
    .cuenta tr.resultado td { font-weight: bold; border-top: 1.5px solid #374151; border-bottom: 0; background: #f9fafb; }
    .destacado { border: 1.5px solid #065f46; background: #ecfdf5; text-align: center; padding: 14px 10px; }
    .destacado.rojo-caja { border-color: #b91c1c; background: #fef2f2; }
    .destacado .rotulo { font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #065f46; }
    .destacado .monto { font-size: 20px; font-weight: bold; color: #064e3b; margin: 4px 0; }
    .destacado.rojo-caja .rotulo, .destacado.rojo-caja .monto { color: #991b1b; }

    /* Firmas: más abajo y sin partirse entre páginas */
    .firmas { width: 100%; border-collapse: collapse; margin-top: 95px; page-break-inside: avoid; }
    .firmas td { width: 42%; text-align: center; border: 0; padding: 0; vertical-align: top; }
    .firmas td.espacio { width: 16%; }
    .linea { border-top: 1px solid #374151; margin: 0 18px 5px; }
    .firma-nombre { font-size: 9px; font-weight: bold; color: #111827; }
    .firma-rol { font-size: 8px; color: #6b7280; }
</style>
</head>
<body>
    <div class="encabezado">
        <table><tr>
            <td style="width: 48px;">@if ($logo)<img src="{{ $logo }}" class="marca-logo" alt="">@endif</td>
            <td>
                <div class="marca-nombre">{{ $planta['marca'] ?? config('plantas.grupo') }}</div>
                <div class="marca-sub">{{ config('plantas.grupo') }} · Ganadería</div>
            </td>
            <td style="width: 55%;">
                <div class="doc-titulo">{{ $titulo }}</div>
                <div class="doc-meta">
                    @foreach ($meta as $k => $v)
                        {{ $k }}: <strong>{{ $v }}</strong>@if (! $loop->last) &nbsp;·&nbsp; @endif
                    @endforeach
                </div>
            </td>
        </tr></table>
    </div>

    <div class="pie">
        {{ $planta['marca'] ?? config('plantas.grupo') }} · Documento generado el {{ now()->format('d/m/Y H:i') }}@if (auth()->user()) por {{ auth()->user()->name }}@endif
    </div>

    @if (! empty($subtitulo))<p class="subtitulo">{{ $subtitulo }}</p>@endif

    @yield('contenido')

</body>
</html>
