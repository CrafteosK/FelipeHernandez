const agregarToast = (tipo, titulo, descripcion) => {
    const contenedorToast = document.getElementById('contenedor-toast');
    if (!contenedorToast) return;

    const nuevoToast = document.createElement('div');
    nuevoToast.classList.add('toast', tipo, 'autoCierre');
    
    // El icono cambia según el tipo (exito o error)
    const iconoClase = tipo === 'exito' ? 'fa-circle-check' : 'fa-circle-xmark';

    nuevoToast.innerHTML = `
        <div class="contenido">
            <div class="icono">
                <i class="fa-solid ${iconoClase} i"></i>
            </div>
            <div class="texto">
                <p class="titulo">${titulo}</p>
                <p class="descripcion">${descripcion}</p>
            </div>
        </div>
        <button class="btn-cerrar">
            <i class="fa-solid fa-xmark"></i>
        </button>
    `;

    contenedorToast.appendChild(nuevoToast);

    const cerrar = () => {
        nuevoToast.classList.add('cerrado');
        setTimeout(() => nuevoToast.remove(), 300);
    };

    // Auto cierre tras 5 segundos
    const timeout = setTimeout(cerrar, 5000);

    nuevoToast.querySelector('.btn-cerrar').addEventListener('click', () => {
        clearTimeout(timeout);
        cerrar();
    });
};