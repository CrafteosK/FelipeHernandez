<?php
    session_start();
    include "bd/conexion.php";

    // lógica formulario register

    if(isset($_POST['registro'])){
        $usuario = $_POST['usuario'];
        $correo = $_POST['correo'];
        $contrasena = md5($_POST['contrasena']);

        $insertarDatos = "INSERT INTO usuarios VALUES ('', '$usuario', '$correo', '$contrasena')";

        //evitar repeticion de datos

        $verificar_correo = mysqli_query($enlace, "SELECT * FROM usuarios WHERE email = '$correo'");

        if(mysqli_num_rows($verificar_correo) > 0){
            echo '
                <script>
                    alert("El correo ya está registrado, intenta con otro diferente");
                    window.location = "index.php";
                </script>
            ';
            exit();
        }

        $verificar_usuario = mysqli_query($enlace, "SELECT * FROM usuarios WHERE usuario = '$usuario'");

        if(mysqli_num_rows($verificar_usuario) > 0){
            echo '
                <script>
                    alert("El usuario ya está registrado, intenta con otro diferente");
                    window.location = "index.php";
                </script>
            ';
            exit();
        }

        $ejecutarInsertar = mysqli_query($enlace, $insertarDatos);
    }

    // lógica formulario login

    if(isset($_POST['inicio'])){
        $usuario = $_POST['usuario'];
        $contrasena = md5($_POST['contrasena']);

        $validar_login = mysqli_query($enlace, "SELECT * FROM usuarios WHERE usuario = '$usuario' AND contrasena = '$contrasena'");

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
            <form action="" method="POST" class="sign-up">
                <h2>Registrarse</h2>

                <div class="container-input">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" placeholder="Usuario" name="usuario">
                </div>

                <div class="container-input">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" placeholder="Correo Electrónico" name="correo">
                </div>

                <div class="container-input">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" placeholder="Contraseña" name="contrasena">
                </div>

                <button class="button" name="registro">Registrarse</button>
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