<?php
include 'bd/conexion.php'; // Usa tu archivo de conexión real

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$mensaje = "";
$tipo_mensaje = ""; 
$paso = 1; // 1: Pedir Usuario, 2: Responder las 3 Preguntas, 3: Nueva Contraseña, 4: Éxito

$usuario_validado = "";
$p1 = ""; $p2 = ""; $p3 = "";

// FASE 1: BUSCAR AL TRABAJADOR Y TRAER SUS PREGUNTAS
if (isset($_POST['buscar_usuario'])) {
    $usuario = mysqli_real_escape_string($enlace, trim($_POST['usuario']));

    $query = mysqli_query($enlace, "SELECT pregunta_1, pregunta_2, pregunta_3 FROM trabajadores WHERE usuario = '$usuario'");
    
    if (mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
        
        // Verificar que tenga las preguntas configuradas
        if (!empty($row['pregunta_1']) && !empty($row['pregunta_2']) && !empty($row['pregunta_3'])) {
            $paso = 2;
            $usuario_validado = $usuario;
            $_SESSION['recup_user'] = $usuario;
            $_SESSION['p1'] = $row['pregunta_1'];
            $_SESSION['p2'] = $row['pregunta_2'];
            $_SESSION['p3'] = $row['pregunta_3'];
        } else {
            $mensaje = "Este usuario no posee preguntas de seguridad configuradas. Contacte soporte.";
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "El nombre de usuario no se encuentra registrado.";
        $tipo_mensaje = "error";
    }
}

// FASE 2: VERIFICAR LAS 3 RESPUESTAS (Ignorando mayúsculas/minúsculas con LOWER)
if (isset($_POST['verificar_respuestas'])) {
    $usuario = mysqli_real_escape_string($enlace, $_POST['usuario_temporal']);
    $r1 = mysqli_real_escape_string($enlace, trim($_POST['respuesta_1']));
    $r2 = mysqli_real_escape_string($enlace, trim($_POST['respuesta_2']));
    $r3 = mysqli_real_escape_string($enlace, trim($_POST['respuesta_3']));

    $sql = "SELECT * FROM trabajadores WHERE usuario = '$usuario' 
            AND LOWER(respuesta_1) = LOWER('$r1') 
            AND LOWER(respuesta_2) = LOWER('$r2') 
            AND LOWER(respuesta_3) = LOWER('$r3')";
            
    $query = mysqli_query($enlace, $sql);

    if (mysqli_num_rows($query) > 0) {
        $paso = 3;
        $usuario_validado = $usuario;
        $mensaje = "Identidad validada con éxito. Defina su nueva contraseña.";
        $tipo_mensaje = "success";
    } else {
        $paso = 2;
        $usuario_validado = $usuario;
        $mensaje = "Una o más respuestas de seguridad son incorrectas.";
        $tipo_mensaje = "error";
    }
}

// FASE 3: CAMBIAR LA CONTRASEÑA EN LA BASE DE DATOS
if (isset($_POST['cambiar_contrasena'])) {
    $usuario = mysqli_real_escape_string($enlace, $_POST['usuario_confirmado']);
    $nueva_pass = $_POST['nueva_contrasena'];
    $confirmar_pass = $_POST['confirmar_contrasena'];

    if ($nueva_pass === $confirmar_pass) {
        // Encriptación MD5 idéntica a la de tu script de inserción en index.php
        $pass_encriptada = md5($nueva_pass); 
        
        $update = mysqli_query($enlace, "UPDATE trabajadores SET contrasena = '$pass_encriptada' WHERE usuario = '$usuario'");
        
        if ($update) {
            $mensaje = "¡Contraseña actualizada! Ya puede ingresar con sus nuevas credenciales.";
            $tipo_mensaje = "success";
            $paso = 4;
            session_destroy();
        } else {
            $mensaje = "Error interno al guardar la nueva contraseña.";
            $tipo_mensaje = "error";
            $paso = 3;
            $usuario_validado = $usuario;
        }
    } else {
        $mensaje = "Las contraseñas ingresadas no coinciden.";
        $tipo_mensaje = "error";
        $paso = 3;
        $usuario_validado = $usuario;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Felipe Hernández</title>
    <link rel="stylesheet" href="fontawesome/fontawesome-free-7.1.0-web/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Montserrat', sans-serif; }
        body { background-color: #f7f7f7; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .recovery-container { background: #ffffff; padding: 35px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 440px; }
        .recovery-header { text-align: center; margin-bottom: 25px; }
        .recovery-header h1 { font-size: 22px; color: #333; margin-bottom: 5px; }
        .recovery-header p { font-size: 13px; color: #666; }
        .input-group { position: relative; margin-bottom: 15px; }
        .input-group i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999; font-size: 15px; }
        .input-group input { width: 100%; padding: 11px 12px 11px 38px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; outline: none; transition: border-color 0.3s; }
        .input-group input:focus { border-color: #4e54c8; }
        .btn-submit { width: 100%; padding: 12px; background-color: #4e54c8; border: none; color: white; font-size: 15px; font-weight: bold; border-radius: 6px; cursor: pointer; transition: background 0.3s; }
        .btn-submit:hover { background-color: #3b3f9a; }
        .alert { padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; text-align: center; font-weight: 500; }
        .alert.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .question-label { font-size: 13px; font-weight: 600; color: #2c3e50; margin-top: 10px; margin-bottom: 4px; display: block; text-align: left;}
        .footer-links { text-align: center; margin-top: 20px; }
        .footer-links a { color: #4e54c8; text-decoration: none; font-size: 13px; font-weight: 500; }
        .footer-links a:hover { text-decoration: underline; }
        .form-scrollable { max-height: 320px; overflow-y: auto; padding-right: 5px; margin-bottom: 15px; }
        .form-scrollable::-webkit-scrollbar { width: 4px; }
        .form-scrollable::-webkit-scrollbar-thumb { background: #ccc; border-radius: 2px; }
    </style>
</head>
<body>

<div class="recovery-container">
    <div class="recovery-header">
        <i class="fas fa-shield-alt" style="font-size: 38px; color: #4e54c8; margin-bottom: 10px;"></i>
        <h1>Recuperación de Cuenta</h1>
        <p>Verifique su identidad respondiendo sus preguntas de seguridad.</p>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert <?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <?php if ($paso === 1): ?>
        <form action="" method="POST">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="usuario" placeholder="Ingrese su Nombre de Usuario" required autocomplete="off">
            </div>
            <button type="submit" name="buscar_usuario" class="btn-submit">Siguiente</button>
        </form>
    <?php endif; ?>

    <?php if ($paso === 2): ?>
        <form action="" method="POST">
            <input type="hidden" name="usuario_temporal" value="<?php echo htmlspecialchars($usuario_validado); ?>">
            
            <div class="form-scrollable">
                <label class="question-label">Pregunta 1: <?php echo htmlspecialchars($_SESSION['p1']); ?></label>
                <div class="input-group">
                    <i class="fas fa-comment-dots"></i>
                    <input type="text" name="respuesta_1" placeholder="Respuesta 1" required autocomplete="off">
                </div>

                <label class="question-label">Pregunta 2: <?php echo htmlspecialchars($_SESSION['p2']); ?></label>
                <div class="input-group">
                    <i class="fas fa-comment-dots"></i>
                    <input type="text" name="respuesta_2" placeholder="Respuesta 2" required autocomplete="off">
                </div>

                <label class="question-label">Pregunta 3: <?php echo htmlspecialchars($_SESSION['p3']); ?></label>
                <div class="input-group">
                    <i class="fas fa-comment-dots"></i>
                    <input type="text" name="respuesta_3" placeholder="Respuesta 3" required autocomplete="off">
                </div>
            </div>

            <button type="submit" name="verificar_respuestas" class="btn-submit">Verificar Respuestas</button>
        </form>
    <?php endif; ?>

    <?php if ($paso === 3): ?>
        <form action="" method="POST">
            <input type="hidden" name="usuario_confirmado" value="<?php echo htmlspecialchars($usuario_validado); ?>">
            
            <div class="input-group">
                <i class="fas fa-key"></i>
                <input type="password" name="nueva_contrasena" placeholder="Nueva Contraseña" required minlength="6">
            </div>
            
            <div class="input-group">
                <i class="fas fa-check-double"></i>
                <input type="password" name="confirmar_contrasena" placeholder="Confirme la Contraseña" required minlength="6">
            </div>

            <button type="submit" name="cambiar_contrasena" class="btn-submit">Restablecer Contraseña</button>
        </form>
    <?php endif; ?>

    <div class="footer-links">
        <a href="index.php"><i class="fas fa-arrow-left"></i> Volver al Inicio de Sesión</a>
    </div>
</div>

</body>
</html>