let documentos = JSON.parse(localStorage.getItem('documentos')) || [];
let tabla = document.querySelector('.tabla-documentos tbody');

function renderizarTabla(listaDocs = documentos) {
    tabla.innerHTML = '';

    listaDocs.forEach(function(documento) {
        let fila = document.createElement('tr');

        fila.innerHTML = `
            <td>${documento.nombre}</td>
            <td>${documento.categoria}</td>
            <td>${documento.fecha}</td>
        `;

        tabla.appendChild(fila);
    });
}

function filtrarOpciones(criterio) {
    let documentosOrdenados = [...documentos];

    if (criterio === 'nombre') {
        documentosOrdenados.sort((a, b) => a.nombre.localeCompare(b.nombre));
    } else if (criterio === 'categoria') {
        documentosOrdenados.sort((a, b) => a.categoria.localeCompare(b.categoria));
    } else if (criterio === 'fecha') {
        documentosOrdenados.sort((a, b) => new Date(b.fecha) - new Date(a.fecha));
    }

    renderizarTabla(documentosOrdenados);
}

// Carga inicial
renderizarTabla();