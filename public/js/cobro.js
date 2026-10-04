document.addEventListener('DOMContentLoaded', function () {
    var selectItem = document.getElementById('selectItem');
    var adicionalesItem = document.getElementById('adicionalesItem');
    var btnAgregarItem = document.getElementById('btnAgregarItem');
    var cuerpoDetalle = document.getElementById('cuerpoDetalle');
    var filaVacia = document.getElementById('filaVacia');
    var totalCobro = document.getElementById('totalCobro');
    var inputMontoPagado = document.getElementById('inputMontoPagado');
    var etiquetaCambio = document.getElementById('etiquetaCambio');
    var cambioCobro = document.getElementById('cambioCobro');
    var btnRegistrar = document.getElementById('btnRegistrar');
    var formCobro = document.getElementById('formCobro');
    var inputBuscarEstudiante = document.getElementById('inputBuscarEstudiante');
    var sugerenciasEstudiante = document.getElementById('sugerenciasEstudiante');
    var datosEstudiante = document.getElementById('datosEstudiante');
    var estudianteNombre = document.getElementById('estudianteNombre');
    var estudianteApellidos = document.getElementById('estudianteApellidos');
    var estudianteCarrera = document.getElementById('estudianteCarrera');
    var inputEstudianteId = document.getElementById('inputEstudianteId');
    var camposEstudiante = document.getElementById('camposEstudiante');
    var estudiantes = JSON.parse(document.getElementById('datosEstudiantes').textContent);
    var items = JSON.parse(document.getElementById('datosItems').textContent);
    var contadorFilas = 0;

    function formatearMoneda(valor) {
        return 'Bs. ' + valor.toFixed(2);
    }

    function buscarItem(id) {
        return items.find(function (item) {
            return String(item.id) === String(id);
        });
    }

    function llenarSelectItems() {
        items.forEach(function (item) {
            var opcion = document.createElement('option');
            opcion.value = item.id;
            opcion.textContent = item.nombre + ' — ' + formatearMoneda(item.monto);
            selectItem.appendChild(opcion);
        });
    }

    function mostrarAdicionales() {
        var item = buscarItem(selectItem.value);
        adicionalesItem.innerHTML = '';
        btnAgregarItem.disabled = ! item;

        if (! item || item.adicionales.length === 0) {
            adicionalesItem.classList.add('d-none');
            return;
        }

        var titulo = document.createElement('div');
        titulo.className = 'small text-muted mb-1';
        titulo.textContent = 'Adicionales (marca los que lleva):';
        adicionalesItem.appendChild(titulo);

        item.adicionales.forEach(function (adicional) {
            var contenedor = document.createElement('div');
            contenedor.className = 'form-check';

            var check = document.createElement('input');
            check.type = 'checkbox';
            check.className = 'form-check-input';
            check.id = 'adicional' + adicional.id;
            check.value = adicional.id;

            var etiqueta = document.createElement('label');
            etiqueta.className = 'form-check-label';
            etiqueta.htmlFor = check.id;
            etiqueta.textContent = adicional.nombre + ' — ' + formatearMoneda(adicional.monto);

            contenedor.appendChild(check);
            contenedor.appendChild(etiqueta);
            adicionalesItem.appendChild(contenedor);
        });

        adicionalesItem.classList.remove('d-none');
    }

    function crearOculto(nombre, valor) {
        var oculto = document.createElement('input');
        oculto.type = 'hidden';
        oculto.name = nombre;
        oculto.value = valor;
        return oculto;
    }

    function agregarItem() {
        var item = buscarItem(selectItem.value);

        if (! item) {
            return;
        }

        var indice = contadorFilas++;
        var marcados = Array.from(adicionalesItem.querySelectorAll('input[type=checkbox]:checked'));
        var subtotal = item.monto;

        var fila = document.createElement('tr');
        fila.dataset.subtotal = '0';
        fila.dataset.itemId = item.id;

        var celdaDetalle = document.createElement('td');
        var nombreItem = document.createElement('div');
        nombreItem.textContent = item.nombre + ' — ' + formatearMoneda(item.monto);
        celdaDetalle.appendChild(nombreItem);
        celdaDetalle.appendChild(crearOculto('items[' + indice + '][item_id]', item.id));

        marcados.forEach(function (check) {
            var adicional = item.adicionales.find(function (a) {
                return String(a.id) === check.value;
            });

            subtotal += adicional.monto;

            var lineaAdicional = document.createElement('div');
            lineaAdicional.className = 'small text-muted ms-3';
            lineaAdicional.textContent = '+ ' + adicional.nombre + ' — ' + formatearMoneda(adicional.monto);
            celdaDetalle.appendChild(lineaAdicional);
            celdaDetalle.appendChild(crearOculto('items[' + indice + '][adicionales][]', adicional.id));
        });

        fila.dataset.subtotal = subtotal;

        var celdaSubtotal = document.createElement('td');
        celdaSubtotal.className = 'text-end';
        celdaSubtotal.textContent = formatearMoneda(subtotal);

        var celdaQuitar = document.createElement('td');
        celdaQuitar.className = 'text-center';
        var botonQuitar = document.createElement('button');
        botonQuitar.type = 'button';
        botonQuitar.className = 'btn btn-sm btn-outline-danger btn-quitar-item';
        botonQuitar.textContent = 'Quitar';
        celdaQuitar.appendChild(botonQuitar);

        fila.appendChild(celdaDetalle);
        fila.appendChild(celdaSubtotal);
        fila.appendChild(celdaQuitar);
        cuerpoDetalle.appendChild(fila);

        selectItem.querySelector('option[value="' + item.id + '"]').disabled = true;
        selectItem.value = '';
        mostrarAdicionales();
        recalcular();
    }

    function quitarItem(fila) {
        selectItem.querySelector('option[value="' + fila.dataset.itemId + '"]').disabled = false;
        fila.remove();
        recalcular();
    }

    function calcularTotal() {
        var total = 0;

        cuerpoDetalle.querySelectorAll('tr[data-subtotal]').forEach(function (fila) {
            total += parseFloat(fila.dataset.subtotal);
        });

        return Math.round(total * 100) / 100;
    }

    function recalcular() {
        var total = calcularTotal();
        var hayItems = cuerpoDetalle.querySelectorAll('tr[data-subtotal]').length > 0;
        var textoPagado = inputMontoPagado.value.trim();
        var montoPagado = parseFloat(textoPagado) || 0;
        var alcanza = true;

        filaVacia.classList.toggle('d-none', hayItems);
        totalCobro.textContent = formatearMoneda(total);
        cambioCobro.classList.remove('text-danger', 'text-success');

        if (textoPagado === '') {
            etiquetaCambio.textContent = 'Cambio';
            cambioCobro.textContent = formatearMoneda(0);
        } else if (montoPagado < total) {
            etiquetaCambio.textContent = 'Falta pagar';
            cambioCobro.textContent = formatearMoneda(total - montoPagado);
            cambioCobro.classList.add('text-danger');
            alcanza = false;
        } else {
            etiquetaCambio.textContent = 'Cambio a devolver';
            cambioCobro.textContent = formatearMoneda(montoPagado - total);
            cambioCobro.classList.add('text-success');
        }

        btnRegistrar.disabled = ! hayItems || ! alcanza;
    }

    function ocultarSugerencias() {
        sugerenciasEstudiante.classList.add('d-none');
        sugerenciasEstudiante.innerHTML = '';
    }

    function limpiarEstudiante() {
        datosEstudiante.classList.add('d-none');
        inputEstudianteId.value = '';
        camposEstudiante.disabled = true;
    }

    function seleccionarEstudiante(estudiante) {
        estudianteNombre.textContent = estudiante.nombre;
        estudianteApellidos.textContent = estudiante.apellidos;
        estudianteCarrera.textContent = estudiante.carrera;
        datosEstudiante.classList.remove('d-none');
        inputEstudianteId.value = estudiante.id;
        camposEstudiante.disabled = false;
        inputBuscarEstudiante.value = estudiante.etiqueta;
        ocultarSugerencias();
        recalcular();
    }

    function renderSugerencias(coincidencias) {
        sugerenciasEstudiante.innerHTML = '';

        if (coincidencias.length === 0) {
            var vacio = document.createElement('div');
            vacio.className = 'list-group-item text-muted';
            vacio.textContent = 'Sin coincidencias';
            sugerenciasEstudiante.appendChild(vacio);
        } else {
            coincidencias.forEach(function (estudiante) {
                var boton = document.createElement('button');
                boton.type = 'button';
                boton.className = 'list-group-item list-group-item-action';
                boton.textContent = estudiante.etiqueta;
                boton.dataset.id = estudiante.id;
                sugerenciasEstudiante.appendChild(boton);
            });
        }

        sugerenciasEstudiante.classList.remove('d-none');
    }

    function buscarCoincidencias(texto) {
        var busqueda = texto.toLowerCase();

        return estudiantes.filter(function (estudiante) {
            var contenido = (estudiante.nombre + ' ' + estudiante.apellidos + ' ' + estudiante.ci).toLowerCase();

            return contenido.indexOf(busqueda) !== -1;
        }).slice(0, 8);
    }

    selectItem.addEventListener('change', mostrarAdicionales);
    btnAgregarItem.addEventListener('click', agregarItem);
    inputMontoPagado.addEventListener('input', recalcular);

    cuerpoDetalle.addEventListener('click', function (evento) {
        var boton = evento.target.closest('.btn-quitar-item');

        if (boton) {
            quitarItem(boton.closest('tr'));
        }
    });

    formCobro.addEventListener('submit', function () {
        btnRegistrar.disabled = true;
        btnRegistrar.textContent = 'Registrando...';
    });

    inputBuscarEstudiante.addEventListener('input', function () {
        limpiarEstudiante();

        var texto = inputBuscarEstudiante.value.trim();

        if (! texto) {
            ocultarSugerencias();
            return;
        }

        renderSugerencias(buscarCoincidencias(texto));
    });

    inputBuscarEstudiante.addEventListener('blur', function () {
        setTimeout(ocultarSugerencias, 150);
    });

    sugerenciasEstudiante.addEventListener('mousedown', function (evento) {
        var boton = evento.target.closest('button[data-id]');

        if (! boton) {
            return;
        }

        evento.preventDefault();

        var estudiante = estudiantes.find(function (item) {
            return String(item.id) === boton.dataset.id;
        });

        if (estudiante) {
            seleccionarEstudiante(estudiante);
        }
    });

    llenarSelectItems();
    recalcular();
});
