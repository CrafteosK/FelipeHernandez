<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    include "bd/conexion.php";

    // lógica formulario register
    if(isset($_POST['registro'])){
        $usuario = $_POST['usuario'];
        $correo = $_POST['correo'];
        $contrasena = md5($_POST['contrasena']);
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $cedula = $_POST['cedula'];
        $telefono = $_POST['telefono'];
        $cargo = $_POST['cargo'];

        // Evitar repetición de datos críticos
        $verificar_sql = "SELECT id FROM trabajadores WHERE email = '$correo' OR usuario = '$usuario' OR cedula = '$cedula'";
        $verificar = mysqli_query($enlace, $verificar_sql);
        if($verificar === false){
            $_SESSION['notificacion'] = ['tipo' => 'error', 'titulo' => 'Error de Registro', 'msg' => 'Error en consulta de verificación: ' . mysqli_error($enlace)];
            session_write_close();
            header("location:index.php");
            exit();
        }

        if(mysqli_num_rows($verificar) > 0){
            $_SESSION['notificacion'] = ['tipo' => 'error', 'titulo' => 'Error de Registro', 'msg' => 'El usuario, correo o cédula ya están registrados.'];
            session_write_close(); // Asegura el guardado de la sesión
            header("location:index.php");
            exit();
        }

        $queryInsert = "INSERT INTO trabajadores (nombre, apellido, cedula, email, usuario, contrasena, telefono, cargo) " .
                   "VALUES ('$nombre', '$apellido', '$cedula', '$correo', '$usuario', '$contrasena', '$telefono', '$cargo')";

        $resInsert = mysqli_query($enlace, $queryInsert);
        if($resInsert){
            $_SESSION['notificacion'] = ['tipo' => 'exito', 'titulo' => 'Éxito', 'msg' => 'Registro exitoso. Ahora puedes iniciar sesión.'];
            session_write_close(); // Asegura el guardado de la sesión
            header("location:index.php");
            exit();
        } else {
            // Registrar error detallado para depuración
            $sqlErr = mysqli_error($enlace);
            $_SESSION['notificacion'] = ['tipo' => 'error', 'titulo' => 'Error de Registro', 'msg' => 'No se pudo registrar: ' . $sqlErr];
            session_write_close();
            header("location:index.php");
            exit();
        }
    }

    // lógica formulario login
    if(isset($_POST['inicio'])){
        $usuario = $_POST['usuario'];
        $contrasena = md5($_POST['contrasena']);

        $validar_login = mysqli_query($enlace, "SELECT * FROM trabajadores WHERE usuario = '$usuario' AND contrasena = '$contrasena'");

        if(mysqli_num_rows($validar_login) > 0){
            $_SESSION['usuario'] = $usuario;
            session_write_close();
            header("location:inicio.php");
            exit();
        }
        else{
            $_SESSION['notificacion'] = ['tipo' => 'error', 'titulo' => 'Acceso Denegado', 'msg' => 'Usuario o contraseña incorrectos.'];
            session_write_close(); // Asegura el guardado de la sesión
            header("location:index.php");
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Felipe Hernandez</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/notificaciones.css">
    <link rel="stylesheet" href="fontawesome/fontawesome-free-7.1.0-web/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="container-form">
            <form class="sign-in" id="formulario-login" method="POST">
                <img src="imagenes/felipeHernandez.jpg" alt="Felipe Hernandez" class="logo-image">
                <h2>Iniciar Sesión</h2>
                <div class="formulario__grupo" id="grupo__usuario">
                    <div class="container-input formulario__grupo-input">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" placeholder="Usuario" name="usuario"  class="formulario__input">
                        <i class="formulario__validacion-estado fa-solid fa-circle-xmark"></i>
                    </div>
                    <p class="formulario__input-error">El usuario solo puede contener letras, un minimo de 4 y un maximo de 20 caracteres</p>
                </div>
                <div class="formulario__grupo" id="grupo__contraseña">
                    <div class="container-input formulario__grupo-input">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" placeholder="Contraseña" name="contrasena" class="formulario__input">
                        <i class="formulario__validacion-estado fa-solid fa-circle-xmark"></i>
                    </div>
                    <p class="formulario__input-error">La contraseña debe tener un minimo de 6 caracteres y contener mayúsculas, minúsculas, números y caracteres especiales</p>
                </div>
                <a href="recovery.php">¿Olvidaste tu contraseña?</a>
                <button class="button" name="inicio">Iniciar Sesión</button>
            </form>
        </div>
            
        <div class="container-form">
            <form action="" method="POST" class="sign-up" style="padding: 20px 40px;">
                <h2>Registrarse</h2>
                <div class="formulario" id="formulario">
                    <div class="formulario__grupo" id="grupo__cedula">
                        <div class="container-input r formulario__grupo-input">
                            <i class="fa-solid fa-id-card"></i>
                            <input type="text" placeholder="Cédula" name="cedula" class="formulario__input" id="cedula">
                            <i class="formulario__validacion-estado fa-solid fa-circle-xmark"></i>
                        </div>
                        <p class="formulario__input-error">La cedula solo puede contener números y con un minimo de 7 a 8 dígitos</p>
                    </div>
                    <div class="formulario__grupo" id="grupo__usuario">
                        <div class="container-input r formulario__grupo-input">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" placeholder="Usuario" name="usuario" class="formulario__input" id="usuario">
                            <i class="formulario__validacion-estado fa-solid fa-circle-xmark"></i>
                        </div>
                        <p class="formulario__input-error">El usuario solo puede contener letras, un minimo de 4 y un maximo de 20 caracteres</p>
                    </div>
                    <div class="formulario__grupo" id="grupo__nombre">
                        <div class="container-input r formulario__grupo-input">
                            <i class="fa-solid fa-signature"></i>
                            <input type="text" placeholder="Nombre" name="nombre" class="formulario__input" id="nombre">
                            <i class="formulario__validacion-estado fa-solid fa-circle-xmark"></i>
                        </div>
                        <p class="formulario__input-error">El nombre solo puede contener letras</p>
                    </div>
                    <div class="formulario__grupo" id="grupo__apellido">
                        <div class="container-input r formulario__grupo-input">
                            <i class="fa-solid fa-signature"></i>
                            <input type="text" placeholder="Apellido" name="apellido" class="formulario__input" id="apellido">
                            <i class="formulario__validacion-estado fa-solid fa-circle-xmark"></i>
                        </div>
                        <p class="formulario__input-error">El apellido solo puede contener letras</p>

                    </div> 
                    <div class="formulario__grupo" id="grupo__correo">
                        <div class="container-input r formulario__grupo-input">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" placeholder="Correo" name="correo" class="formulario__input" id="correo">
                            <i class="formulario__validacion-estado fa-solid fa-circle-xmark"></i>
                        </div>
                        <p class="formulario__input-error">El correo no es válido</p>

                    </div>
                    <div class="formulario__grupo" id="grupo__telefono">
                        <div class="container-input r formulario__grupo-input">
                            <i class="fa-solid fa-phone"></i>
                            <input type="text" placeholder="Teléfono" name="telefono" class="formulario__input" id="telefono">
                            <i class="formulario__validacion-estado fa-solid fa-circle-xmark"></i>
                        </div>
                        <p class="formulario__input-error">El teléfono solo puede contener números y un maximo de 11 digitos</p>

                    </div>
                    <div class="formulario__grupo" id="grupo__cargo">
                        <div class="container-input r formulario__grupo-input">
                            <i class="fa-solid fa-briefcase"></i>
                            <select name="cargo"  style="border:none; outline:none; background:transparent; width:100%;" class="formulario__input" id="cargo">
                                <option value="">Cargo...</option>
                                <?php 
                                    $resC = mysqli_query($enlace, "SELECT * FROM cargos");
                                    while($c = mysqli_fetch_assoc($resC)) echo "<option value='{$c['id']}'>{$c['nombre']}</option>";
                                ?>
                            </select>
                        </div>
                        <p class="formulario__input-error">Por favor, seleccione un cargo</p>

                    </div>
                    <div class="formulario__grupo" id="grupo__contraseña">
                        <div class="container-input r formulario__grupo-input">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" placeholder="Contraseña" name="contrasena" class="formulario__input" id="contrasena">
                            <i class="formulario__validacion-estado fa-solid fa-circle-xmark"></i>
                        </div>
                        <p class="formulario__input-error">La contraseña debe tener de 6 a 12 caracteres</p>

                    </div>
                </div>
                <button class="button" name="registro" style="margin-top: 20px;">Registrarse</button>
            </form>
        </div>
        <div class="container-welcome">
            <div class="welcome-sign-up welcome">
                <h3>Bienvenido</h3>
                <p>Ingrse su usuario y contraseña</p>
                <p>¿No tienes una cuenta?</p>
                <button class="button" id="btn-sign-up">Registrate</button>
            </div>
            <div class="welcome-sign-in welcome">
                <h3>¡Hola!</h3>
                <p>Ingrese sus datos para Registrarse</p>
                <p>¿Ya tienes una cuenta?</p>
                <button class="button" id="btn-sign-in">Iniciar Sesión</button>
            </div>
        </div>
    </div>
    <script src="js/notificaciones.js"></script>
    <script src="js/index.js"></script>
    <?php include "includes/notificaciones.php"; ?>
</body>
</html>