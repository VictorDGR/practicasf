<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class BackupController extends Controller
{
    public function index()
    {
        File::ensureDirectoryExists($this->carpeta());

        $backups = collect(File::files($this->carpeta()))
            ->filter(fn ($archivo) => $archivo->getExtension() === 'sql')
            ->sortByDesc(fn ($archivo) => $archivo->getMTime())
            ->map(fn ($archivo) => [
                'nombre' => $archivo->getFilename(),
                'fecha' => date('d/m/Y H:i:s', $archivo->getMTime()),
                'tamano' => number_format($archivo->getSize() / 1024, 1) . ' KB',
            ]);

        return view('superadmin.backups.index', compact('backups'));
    }

    public function crear()
    {
        File::ensureDirectoryExists($this->carpeta());

        $conexion = config('database.connections.' . config('database.default'));
        $nombre = 'backup_' . $conexion['database'] . '_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $ruta = $this->carpeta() . DIRECTORY_SEPARATOR . $nombre;

        $comando = [
            config('database.mysqldump'),
            '--host=' . $conexion['host'],
            '--port=' . $conexion['port'],
            '--user=' . $conexion['username'],
            '--routines',
            '--single-transaction',
            '--result-file=' . $ruta,
        ];

        if (! empty($conexion['password'])) {
            $comando[] = '--password=' . $conexion['password'];
        }

        $comando[] = $conexion['database'];

        $resultado = Process::env(['SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows'])->run($comando);

        if ($resultado->failed()) {
            File::delete($ruta);

            return back()->withErrors(['backup' => 'No se pudo crear el backup: ' . trim($resultado->errorOutput())]);
        }

        return back()->with('mensaje', "Backup creado correctamente: {$nombre}");
    }

    public function descargar(string $archivo)
    {
        return response()->download($this->rutaArchivo($archivo));
    }

    public function eliminar(string $archivo)
    {
        File::delete($this->rutaArchivo($archivo));

        return back()->with('mensaje', 'Backup eliminado.');
    }

    private function carpeta(): string
    {
        return base_path('backups');
    }

    private function rutaArchivo(string $archivo): string
    {
        $ruta = $this->carpeta() . DIRECTORY_SEPARATOR . basename($archivo);

        if (! str_ends_with($ruta, '.sql') || ! File::exists($ruta)) {
            abort(404);
        }

        return $ruta;
    }
}
