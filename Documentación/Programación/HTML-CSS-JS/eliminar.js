let documentos = JSON.parse(localStorage.getItem('documentos')) || [];
const select = document.getElementById('documentoSelect');

// Cargar opciones en el select
documentos.forEach((doc, index) => {
    let option = document.createElement('option');
    option.value = index;
    option.textContent = `${doc.nombre} (${doc.categoria})`;
    select.appendChild(option);
});

// Manejar la eliminación
document.getElementById('eliminarForm').addEventListener('submit', (e) => {
    e.preventDefault();

    const indexAEliminar = select.value;
    
    if (indexAEliminar !== "" && documentos.length > 0) {
        documentos.splice(indexAEliminar, 1);
        localStorage.setItem('documentos', JSON.stringify(documentos));
        window.location.href = 'documentos.html';
    }
});