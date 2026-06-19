<div id="contenedor-toast" class="contenedor-toast"></div>

<?php
if (isset($_SESSION['notificacion'])) {
    $n = $_SESSION['notificacion'];
    $tipo = $n['tipo'];
    $titulo = $n['titulo'];
    $msg = $n['msg'];
    
    // Convertimos a JSON seguro para evitar cualquier ruptura de comillas en JS
    echo "<script>
        window.addEventListener('load', () => {
            if (typeof agregarToast === 'function') {
                agregarToast(" . json_encode($tipo) . ", " . json_encode($titulo) . ", " . json_encode($msg) . ");
            } else {
                console.error('La función agregarToast no está definida.');
            }
        });
    </script>";
    unset($_SESSION['notificacion']);
}
?>