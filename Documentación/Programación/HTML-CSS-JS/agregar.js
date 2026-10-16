document.getElementById('archivoForm').addEventListener('submit', (e) => {
    e.preventDefault();

    const nombre = document.getElementById('nombre').value;
    const categoria = document.getElementById('categoria').value;

    const archivoInput = document.getElementById('archivo');

    const nombreArchivo = archivoInput.files[0]
        ? archivoInput.files[0].name
        : 'Sin archivo';

    const fechaActual = new Date().toISOString().split('T')[0];

    const nuevoDocumento = {
        nombre: nombre,
        categoria: categoria,
        archivo: nombreArchivo,
        fecha: fechaActual
    };

    let documentos =
        JSON.parse(localStorage.getItem('documentos')) || [];

    documentos.push(nuevoDocumento);

    localStorage.setItem(
        'documentos',
        JSON.stringify(documentos)
    );

    window.location.href = 'documentos.html';
});