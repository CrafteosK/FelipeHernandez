<?php
    session_start();
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
        $verificar = mysqli_query($enlace, "SELECT id FROM trabajadores WHERE email = '$correo' OR usuario = '$usuario' OR cedula = '$cedula'");

        if(mysqli_num_rows($verificar) > 0){
            echo '
                <script>
                    alert("El usuario, correo o cédula ya están registrados.");
                    window.location = "index.php";
                </script>
            ';
            exit();
        }

        $queryInsert = "INSERT INTO trabajadores (nombre, apellido, cedula, email, usuario, contrasena, telefono, cargo) 
                        VALUES ('$nombre', '$apellido', '$cedula', '$correo', '$usuario', '$contrasena', '$telefono', '$cargo')";
        
        if(mysqli_query($enlace, $queryInsert)){
            echo '<script>alert("Registro exitoso. Ahora puedes iniciar sesión.");</script>';
        }
    }

    // lógica formulario login

    if(isset($_POST['inicio'])){
        $usuario = $_POST['usuario'];
        $contrasena = md5($_POST['contrasena']);

        $validar_login = mysqli_query($enlace, "SELECT * FROM trabajadores WHERE usuario = '$usuario' AND contrasena = '$contrasena'");

        if(mysqli_num_rows($validar_login) > 0){
            $_SESSION['usuario'] = $usuario;
            header("location:inicio.php");
            exit();
        }
        else{
            echo '
                <script>
                    alert("Usuario o contraseña incorrectos, intenta de nuevo");
                    window.location = "index.php";
                </script>
            ';
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
    <link rel="stylesheet" href="fontawesome/fontawesome-free-7.1.0-web/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="container-form">
            <form class="sign-in" method="POST">
                <h2>Iniciar Sesión</h2>

                <div class="container-input">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" placeholder="Usuario" name="usuario">
                </div>

                <div class="container-input">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" placeholder="Contraseña" name="contrasena">
                </div>

                <a href="#">¿Olvidaste tu contraseña?</a>
                <button class="button" name="inicio">Iniciar Sesión</button>
            </form>
        </div>
            
        <div class="container-form">
            <form action="" method="POST" class="sign-up" style="padding: 20px 40px;">
                <h2>Registrarse</h2>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; width: 100%;">
                    <div class="container-input r">
                        <i class="fa-solid fa-id-card"></i>
                        <input type="text" placeholder="Cédula" name="cedula" required>
                    </div>
                    <div class="container-input r">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" placeholder="Usuario" name="usuario" required>
                    </div>
                    <div class="container-input r">
                        <i class="fa-solid fa-signature"></i>
                        <input type="text" placeholder="Nombre" name="nombre" required>
                    </div>
                    <div class="container-input r">
                        <i class="fa-solid fa-signature"></i>
                        <input type="text" placeholder="Apellido" name="apellido" required>
                    </div>
                    <div class="container-input r">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" placeholder="Correo" name="correo" required>
                    </div>
                    <div class="container-input r">
                        <i class="fa-solid fa-phone"></i>
                        <input type="text" placeholder="Teléfono" name="telefono" required>
                    </div>
                    <div class="container-input r">
                        <i class="fa-solid fa-briefcase"></i>
                        <select name="cargo" required style="border:none; outline:none; background:transparent; width:100%;">
                            <option value="">Cargo...</option>
                            <?php 
                                $resC = mysqli_query($enlace, "SELECT * FROM cargos");
                                while($c = mysqli_fetch_assoc($resC)) echo "<option value='{$c['id']}'>{$c['nombre']}</option>";
                            ?>
                        </select>
                    </div>
                    <div class="container-input r">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" placeholder="Contraseña" name="contrasena" required>
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
    <script src="js/index.js"></script>
</body>
</html>