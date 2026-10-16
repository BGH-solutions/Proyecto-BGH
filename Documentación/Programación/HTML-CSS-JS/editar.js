let documentos = JSON.parse(localStorage.getItem('documentos')) || [];

const select = document.getElementById('documentoSelect');
const inputNombre = document.getElementById('nombre');
const selectCategoria = document.getElementById('categoria');
const btnGuardar = document.getElementById('btnGuardar');

// Cargar opciones en el select de documentos
documentos.forEach((doc, index) => {
    let option = document.createElement('option');
    option.value = index;
    option.textContent = `${doc.nombre} (${doc.categoria})`;
    select.appendChild(option);
});

// Al seleccionar un documento, cargar sus datos en los campos
select.addEventListener('change', (e) => {
    const index = e.target.value;

    if (index !== "") {
        const doc = documentos[index];
        inputNombre.value = doc.nombre;
        selectCategoria.value = doc.categoria;

        inputNombre.disabled = false;
        selectCategoria.disabled = false;
        btnGuardar.disabled = false;
    } else {
        inputNombre.value = "";
        inputNombre.disabled = true;
        selectCategoria.disabled = true;
        btnGuardar.disabled = true;
    }
});

// Guardar los cambios actualizados
document.getElementById('editarForm').addEventListener('submit', (e) => {
    e.preventDefault();

    const index = select.value;

    if (index !== "") {
        documentos[index].nombre = inputNombre.value;
        documentos[index].categoria = selectCategoria.value;

        localStorage.setItem('documentos', JSON.stringify(documentos));
        window.location.href = 'documentos.html';
    }
});