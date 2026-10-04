<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte {{ $nombreTipo }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        h2 { margin: 0 0 4px 0; }
        h3, h5 { margin: 12px 0 6px 0; }
        h6 { margin: 0 0 4px 0; color: #666; font-size: 9px; }
        .encabezado { border-bottom: 2px solid #222; margin-bottom: 10px; padding-bottom: 6px; }
        .row { margin-bottom: 8px; }
        .col-md-3, .col-md-4 { display: inline-block; width: 23%; vertical-align: top; margin-right: 1%; }
        .card { border: 1px solid #bbb; padding: 6px; text-align: center; }
        .card h3 { margin: 2px 0; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #999; padding: 3px 4px; text-align: left; }
        th { background: #e9e9e9; }
        .table-danger td { background: #f8d7da; }
        .table-warning td { background: #fff3cd; }
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
    <div class="encabezado">
        <h2>UPDS - Reporte {{ $nombreTipo }}</h2>
        <div>
            Desde {{ \Illuminate\Support\Carbon::parse($desde)->format('d/m/Y') }}
            hasta {{ \Illuminate\Support\Carbon::parse($hasta)->format('d/m/Y') }}
            — Generado el {{ now()->format('d/m/Y H:i') }} por {{ auth()->user()->persona->nombreCompleto() }}
        </div>
    </div>

    @include('admin.reportes.resultados')
</body>
</html>
