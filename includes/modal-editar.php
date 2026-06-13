<div class="modal" tabindex="-1"  id="staticBackdrop . $field0name ." aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Trabajador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="post" id=". $field0name .">
                <div class="modal-body">
                      <div class="mb-3">
                          <label for="nombre" class="form-label">Nombre</label>
                          <input type="text" class="form-control" id="nombre" value="' . $field1name . '" name="nombre" required>
                      </div>
                      <div class="mb-3">
                          <label for="apellido" class="form-label">Apellido</label>
                          <input type="text" class="form-control" id="apellido" value="' . $field2apellido . '" name="apellido" required>
                      </div>
                      <div class="mb-3">
                          <label for="cedula" class="form-label">Cédula</label>
                          <input type="text" class="form-control" id="cedula" value="' . $field3cedula . '" name="cedula" required>
                      </div>
                      <div class="mb-3">
                          <label for="telefono" class="form-label">Teléfono</label>
                          <input type="text" class="form-control" id="telefono" value="' . $field4telefono . '" name="telefono" required>
                      </div>
                      <div class="mb-3">
                          <label for="cargo" class="form-label">Cargo</label>
                          <select name="cargo" id="cargo" class="form-control" required placeholder="Seleccionar cargo">
                              <option value="">Seleccionar cargo</option>
                              <option value="Administrativo">Administrativo</option>
                              <option value="Docente">Docente</option>
                              <option value="Obrero">Obrero</option>
                              <option value="Cosinero">Cosinero</option>
                              <option value="Vigilante">Vigilante</option>
                          </select>
                      </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button class="btn btn-primary" name="editTrabajadorBtn">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php

if(isset($_POST['editTrabajadorBtn'])){
    $editarTrabajador = "SELECT * FROM trabajadores";
    $resultado = mysqli_query($enlace, $editarTrabajador);
    while($row=$resultado->fetch_assoc()){
        $field0name=$row['id'];
        $field1name=$row['nombre'];
        $field2apellido=$row['apellido'];
        $field3cedula=$row['cedula'];
        $field4telefono=$row['telefono'];
        $field5cargo=$row['cargo'];
    }
}?>



