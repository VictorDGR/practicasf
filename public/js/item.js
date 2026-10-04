document.addEventListener('DOMContentLoaded', function () {
    var listaAdicionales = document.getElementById('listaAdicionales');
    var plantillaAdicional = document.getElementById('plantillaAdicional');
    var btnAgregarAdicional = document.getElementById('btnAgregarAdicional');
    var contador = Date.now();

    btnAgregarAdicional.addEventListener('click', function () {
        var html = plantillaAdicional.innerHTML.replace(/__i__/g, contador);
        contador++;
        listaAdicionales.insertAdjacentHTML('beforeend', html);
    });

    listaAdicionales.addEventListener('click', function (evento) {
        var boton = evento.target.closest('.btn-quitar-adicional');

        if (boton) {
            boton.closest('.row').remove();
        }
    });
});
