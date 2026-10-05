<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - UPDS Cobros</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/estilos.css') }}" rel="stylesheet">
</head>
<body>
    <div class="login">
        <div class="login-formulario">
            <div class="caja">
                <img src="{{ asset('images/descarga.png') }}" alt="UPDS" class="d-lg-none mb-4" style="height: 70px;">

                <h1>Iniciar sesión</h1>
                <p class="text-muted mb-4">Ingresa tu usuario y contraseña para continuar</p>

                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login.intentar') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="username" class="form-label">Usuario <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" placeholder="Ingresa tu usuario" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">Iniciar sesión</button>
                </form>
            </div>
        </div>

        <div class="login-panel">
            <div class="logo">
                <img src="{{ asset('images/descarga.png') }}" alt="UPDS">
            </div>
            <h2>UPDS Cobros</h2>
            <p>Sistema de cobros de la Universidad Privada Domingo Savio</p>
        </div>
    </div>
</body>
</html>
