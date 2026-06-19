<div class="left">
    <div class="menu-container">
        <div class="menu" id="menu">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
    <div class="brand">
        <img src="imagenes/felipeHernandez.jpg" alt="logo Felipe Hernandez" class="logo">
    </div>
</div>
<div class="right">
    <!--<a href="#">
        <i class="fa-solid fa-bell right-icon" alt="Notificaciones"></i>
    </a>
    <a href="#">
        <i class="fa-regular fa-circle-question right-icon" alt="Ayuda"></i>
    </a>-->
        <a href="#" id="logout-link" data-href="bd/sesion-destroy.php">
            <i class="fa-solid fa-right-from-bracket right-icon" alt="Cerrar sesión"></i>
        </a>
    </div>

    <!-- Modal de confirmación de cierre de sesión -->
    <div id="logout-modal" class="logout-modal" aria-hidden="true">
        <div class="logout-modal-backdrop"></div>
        <div class="logout-modal-content">
            <i class="fa-regular fa-circle-question logout-modal-icon" aria-hidden="true"></i>
            <p>¿Seguro que deseas cerrar sesión?</p>
            <div class="logout-modal-actions">
                <button id="logout-cancel" class="btn-cancel">Cancelar</button>
                <button id="logout-confirm" class="btn-confirm">Cerrar sesión</button>
            </div>
        </div>
    </div>

    <style>
    /* Estilos mínimos para el modal con transición */
    .logout-modal{position:fixed;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;justify-content:center;z-index:1050;opacity:0;visibility:hidden;transition:opacity .22s ease, visibility .22s ease}
    .logout-modal.show{opacity:1;visibility:visible}
    .logout-modal-backdrop{position:absolute;inset:0;background:rgba(0,0,0,0.5);opacity:0;transition:opacity .22s ease}
    .logout-modal.show .logout-modal-backdrop{opacity:1}
    .logout-modal-content{position:relative;background:#fff;padding:20px;border-radius:6px;box-shadow:0 10px 25px rgba(0,0,0,0.2);max-width:320px;width:90%;z-index:1060;text-align:center;transform:translateY(-10px) scale(.98);opacity:0;transition:transform .25s cubic-bezier(.2,.8,.2,1),opacity .25s ease}
    .logout-modal.show .logout-modal-content{transform:translateY(0) scale(1);opacity:1}
.logout-modal-icon{font-size:48px;color:#f0ad4e;margin-bottom:10px}
.logout-modal-actions{margin-top:12px;display:flex;justify-content:center;gap:10px}
    .btn-cancel{background:#e0e0e0;border:none;padding:8px 12px;border-radius:4px;cursor:pointer}
    .btn-confirm{background:#d9534f;color:#fff;border:none;padding:8px 12px;border-radius:4px;cursor:pointer}
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function(){
        var logoutLink = document.getElementById('logout-link');
        var modal = document.getElementById('logout-modal');
        var btnCancel = document.getElementById('logout-cancel');
        var btnConfirm = document.getElementById('logout-confirm');
        if(!logoutLink || !modal) return;
        logoutLink.addEventListener('click', function(e){
            e.preventDefault();
            modal.classList.add('show');
            modal.setAttribute('aria-hidden','false');
        });
        function hideModal(){
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden','true');
        }
        btnCancel.addEventListener('click', hideModal);
        modal.addEventListener('click', function(e){ if(e.target === modal || e.target.classList.contains('logout-modal-backdrop')) hideModal(); });
        btnConfirm.addEventListener('click', function(){
            var href = logoutLink.getAttribute('data-href') || 'bd/sesion-destroy.php';
            window.location.href = href;
        });
        // cerrar con ESC
        document.addEventListener('keydown', function(e){ if(e.key === 'Escape') hideModal(); });
    });
    </script>
</div>
