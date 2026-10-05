<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte {{ $nombreTipo }}</title>
    <style>
        @page { margin: 22px 28px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8.5px; color: #222; }
        .encabezado { width: 100%; border-collapse: collapse; border-bottom: 2px solid #203b82; margin-bottom: 12px; }
        .encabezado td { border: none; padding: 0 0 8px 0; vertical-align: middle; background: none; }
        .encabezado .logo img { height: 62px; }
        .encabezado .titulo { text-align: center; }
        .encabezado .titulo h1 { margin: 0 0 4px 0; font-size: 20px; font-weight: normal; color: #203b82; }
        .encabezado .titulo div { font-size: 10px; color: #444; }
        .encabezado .datos { text-align: right; font-size: 8.5px; color: #444; line-height: 1.6; }
        h3, h5 { margin: 12px 0 4px 0; font-size: 10px; color: #203b82; }
        h6 { margin: 0 0 2px 0; color: #666; font-size: 8px; font-weight: normal; }
        .row { margin-bottom: 6px; }
        .col-md-3, .col-md-4 { display: inline-block; width: 23%; vertical-align: top; margin-right: 1%; }
        .card { border: 1px solid #ccd3e6; border-left: 3px solid #203b82; padding: 5px 8px; }
        .card h3 { margin: 0; font-size: 12px; color: #222; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { padding: 3px 4px; text-align: left; border: none; }
        th { border-bottom: 1px solid #222; border-top: 1px solid #222; font-weight: bold; background: #eef1f8; }
        tbody tr td { border-bottom: 1px solid #e5e5e5; }
        .table-danger td { background: #fbe9eb; }
        .table-warning td { background: #fff6dc; }
        .text-danger { color: #b02a37; }
        .text-success { color: #146c43; }
        .text-warning { color: #997404; }
        .text-muted { color: #666; }
        .text-center { text-align: center; }
        .badge { font-weight: bold; }
        .alert { border: 1px solid #ccc; padding: 4px; }
    </style>
</head>
<body>
    <table class="encabezado">
        <tr>
            <td class="logo" style="width: 22%;">
                <img src="{{ public_path('images/descarga.png') }}">
            </td>
            <td class="titulo" style="width: 56%;">
                <h1>Reporte {{ $nombreTipo }}</h1>
                <div>
                    Fecha desde: {{ \Illuminate\Support\Carbon::parse($desde)->format('d/m/Y') }},
                    Fecha hasta: {{ \Illuminate\Support\Carbon::parse($hasta)->format('d/m/Y') }}
                </div>
                <div>UPDS Cobros</div>
            </td>
            <td class="datos" style="width: 22%;">
                Fecha: {{ now()->format('d/m/Y') }}<br>
                Hora: {{ now()->format('H:i:s') }}<br>
                Usuario: {{ auth()->user()->persona->nombreCompleto() }}
            </td>
        </tr>
    </table>

    @include('admin.reportes.resultados')
</body>
</html>
