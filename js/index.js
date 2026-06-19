const container = document.querySelector('.container');
const btnSignIn = document.getElementById('btn-sign-in');
const btnSignUp = document.getElementById('btn-sign-up');
const formulario = document.getElementById('formulario');
const inputs = document.querySelectorAll('#formulario input, #formulario select, #formulario-login input');

// REGLA CORREGIDA: Ahora acepta letras, números y símbolos comunes, validando de 6 a 12 caracteres
const expresiones = {
    cedula: /^\d{7,8}$/,
    usuario: /^[A-Za-z]{4,20}$/,
    nombre: /^[A-Za-zÀ-ÿ\s]+$/,
    apellido: /^[A-Za-zÀ-ÿ\s]+$/,
    correo: /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/,
    telefono: /^\d{11}$/,
    contrasena: /^[A-Za-z0-9!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]{6,12}$/
};

btnSignIn && btnSignIn.addEventListener('click', () => container.classList.remove('toggle'));
btnSignUp && btnSignUp.addEventListener('click', () => container.classList.add('toggle'));

const groupIdMap = {
    cedula: 'grupo__cedula',
    usuario: 'grupo__usuario',
    nombre: 'grupo__nombre',
    apellido: 'grupo__apellido',
    correo: 'grupo__correo',
    telefono: 'grupo__telefono',
    cargo: 'grupo__cargo',
    contrasena: 'grupo__contraseña'
};

function validateField(fieldName, value){
    if(fieldName === 'cargo'){
        return value !== '';
    }
    if(fieldName === 'contrasena'){
        return expresiones.contrasena.test(value);
    }
    const expr = expresiones[fieldName];
    if(!expr) return true; 
    return expr.test(value);
}

function setGroupState(fieldName, valid, root){
    const groupId = groupIdMap[fieldName];
    if(!groupId) return;
    let group = null;
    if(root && root.querySelector){
        group = root.querySelector('#' + groupId);
    }
    if(!group){
        group = document.getElementById(groupId);
    }
    if(!group) return;
    const inputWrap = group.querySelector('.formulario__grupo-input');
    const icon = group.querySelector('.formulario__validacion-estado');
    const errorP = group.querySelector('.formulario__input-error');
    if(valid){
        group.classList.remove('formulario__grupo-incorrecto');
        group.classList.add('formulario__grupo-correcto');
        if(icon){ icon.classList.remove('fa-circle-xmark'); icon.classList.add('fa-circle-check'); }
        if(errorP) errorP.classList.remove('formulario__input-error-activo');
        if(inputWrap) inputWrap.classList.remove('has-error');
    } else {
        group.classList.add('formulario__grupo-incorrecto');
        group.classList.remove('formulario__grupo-correcto');
        if(icon){ icon.classList.remove('fa-circle-check'); icon.classList.add('fa-circle-xmark'); }
        if(errorP) errorP.classList.add('formulario__input-error-activo');
        if(inputWrap) inputWrap.classList.add('has-error');
    }
}

inputs.forEach((input) => {
    const name = input.name || input.id;
    input.addEventListener('keyup', (e) => {
        const val = e.target.value.trim();
        const ok = validateField(name, val);
        setGroupState(name, ok);
    });
    input.addEventListener('blur', (e) => {
        const val = e.target.value.trim();
        const ok = validateField(name, val);
        setGroupState(name, ok);
    });
    if(input.tagName.toLowerCase() === 'select'){
        input.addEventListener('change', (e) => {
            setGroupState(name, validateField(name, e.target.value));
        });
    }
});

// Cambiamos el selector para apuntar al ID real de tu formulario de registro
const formEl = document.getElementById('formulario');

if (formEl) {
    formEl.addEventListener('submit', function(e){
        e.preventDefault(); // Detiene el envío para validar primero
        
        const toCheck = ['cedula','usuario','nombre','apellido','correo','telefono','cargo','contrasena'];
        let firstInvalid = null;
        let allValid = true;

        toCheck.forEach(function(field){
            const input = formEl.querySelector('[name="'+field+'"]') || formEl.querySelector('#'+field);
            const val = input ? (input.value || '').trim() : '';
            
            let ok = true;
            
            // Validación especial para el campo cargo (por ser un select)
            if (field === 'cargo') {
                ok = val !== "" && val !== "0"; // Válido si seleccionó una opción real
            } else {
                // Para los demás campos, usa la función de expresiones regulares existente
                ok = typeof validateField === 'function' ? validateField(field, val) : true;
            }

            if (typeof setGroupState === 'function') {
                setGroupState(field, ok);
            }
            
            if(!ok){ 
                allValid = false; 
                if(!firstInvalid && input) firstInvalid = input; 
            }
        });

        // Si algún campo no cumple con los requisitos del frontend, detiene el proceso
        if(!allValid){
            if(firstInvalid) firstInvalid.focus();
            // Opcional: Aquí podrías disparar un mensaje flotante/toast diciendo "Verifique los campos en rojo"
            return false;
        }

        // Si TODO está correcto en JS, se envía manualmente el formulario al PHP
        formEl.submit();
    });
}

const signInEl = document.getElementById('formulario-login');
if(signInEl){
    // Buscamos los inputs que estén específicamente dentro del contenedor de login
    const signInputs = signInEl.querySelectorAll('input, select');
    
    signInputs.forEach(input => {
        const name = input.name || input.id;
        if (!name) return; // Ignora inputs sin nombre o ID

        const validateAndMark = (val) => {
            const ok = validateField(name, (val || '').trim());
            setGroupState(name, ok, signInEl);
            const container = input.closest('.container-input');
            if(container){
                if(!ok) container.classList.add('has-error'); else container.classList.remove('has-error');
            } else {
                if(!ok) input.classList.add('has-error'); else input.classList.remove('has-error');
            }
            return ok;
        };
        input.addEventListener('keyup', (e) => validateAndMark(e.target.value));
        input.addEventListener('blur', (e) => validateAndMark(e.target.value));
    });

//   signInEl.addEventListener('submit', function(e){
//        e.preventDefault(); // Detiene el envío automático
//        
//        const inputsArr = Array.from(signInputs).filter(input => input.type !== 'submit' && input.tagName !== 'BUTTON');
//        const errors = [];
//        let firstInvalid = null;
//
//        inputsArr.forEach(input => {
//            const name = input.name || input.id;
//
//            // FILTRO CRÍTICO: Si el input no es 'usuario' ni 'contrasena', lo ignoramos completamente
//            if (name !== 'usuario' && name !== 'contrasena') return;
//
//            const val = (input.value || '').trim();
//            const ok = validateField(name, val);
//
//            // marcar UI
//            const container = input.closest('.container-input');
//            if(container){
//                if(!ok) container.classList.add('has-error'); else container.classList.remove('has-error');
//            } else {
//                if(!ok) input.classList.add('has-error'); else input.classList.remove('has-error');
//            }
//            setGroupState(name, ok, signInEl);
//        
//            if(!ok){
//                firstInvalid = firstInvalid || input;
//                if(name === 'usuario') errors.push('El usuario debe contener sólo letras (4-20).');
//                else if(name === 'contrasena') errors.push('La contraseña debe tener de 6 a 12 caracteres.');
//            }
//        });
//
//        if(errors.length){
//            const msg = errors.join(' ');
//            if(typeof agregarToast === 'function') agregarToast('error', 'Error de Inicio', msg);
//            else alert(msg);
//            if(firstInvalid) firstInvalid.focus();
//            return false;
//        }
//
//        // SOLUCIÓN: Usamos e.target (que es el <form> real que disparó el submit) en lugar de 'this'
//        if (e.target && typeof e.target.submit === 'function') {
//            e.target.submit();
//        } else {
//            // En caso de que se haya escuchado en el contenedor, busca el formulario interno
//            const formInside = signInEl.querySelector('form');
//            if(formInside) formInside.submit();
//        }
//    });
}