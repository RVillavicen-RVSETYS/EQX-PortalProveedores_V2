$(document).ready(function() {

    var dt = $('#tableBajaProductos').DataTable({
        rowCallback: function(row, data) {
            if (data.stock <= 0) {
                $(row).addClass('table-danger');
            }
        },
        "dom": 'T<"clear">lfrtip',
        "data": datsJson.data,
        "columns": [{
                "class": 'details-control',
                "orderable": false,
                "data": null,
                "defaultContent": '<div class="text-center"><i class="fas fa-plus-circle text-' + pyme + '"></i> </div>',
            },
            {
                "data": null,
                render: function(data, type, row) {
                    let prioridad;
                    switch (data.prioridad) {
                        case '1':
                            prioridad = '<i class="fa fa-circle m-r-5 text-success"></i>';
                            break;
                        case '2':
                            prioridad = '<i class="fa fa-circle m-r-5" style="color:#EAC03E;"></i>';
                            break;
                        case '3':
                            prioridad = '<i class="fa fa-circle m-r-5" style="color:#CCC;"></i>';
                            break;
                        default:
                            prioridad = '';
                            break;
                    }
                    return '<p>' + prioridad + ' ' + data.descripcion + '</p>';
                }
            },
            {
                "data": "marca"
            },
            {
                "data": "dpto"
            },
            {
                "data": null,
                render: function(data, type, row) {
                    var formatter = new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD',
                    });

                    costoBase = formatter.format(data.costoBase);
                    return '<p>' + costoBase + '</p>';
                }
            },
            {
                "data": null,
                render: function(data, type, row) {
                    stock = new Intl.NumberFormat("en-IN").format(data.stock);
                    return '<p class="text-center">' + stock + '</p>';
                }
            },
            {
                "data": null,
                render: function(data, type, row) {
                    const iconEstatus = (data.estatus == 1) ?
                        `<div id="estatusProducto-${data.id}"><button class="btn btn-circle-xs btn-circle-tablita bg-white muestraSombra" onclick="cambiaEstatusProd('${data.id}', '${data.estatus}');" title="Activo, selecciona para desactivar"><i class="text-success fas fa-check"></i></button></div>` :
                        ` <div id="estatusProducto-${data.id}"><button onclick ="cambiaEstatusProd('${data.id}', '${data.estatus}');" class ="btn btn-circle-xs bg-white btn-circle-tablita muestraSombra"  title = "Desactivado, selecciona para activar" > <i class = "text-danger fas fa-times" > </i></button></div>`;
                    return iconEstatus;
                }
            }

        ],

        "buttons": [
            'excel',
            'print'
        ],
        "order": [
            [5, 'asc']
        ],


    });


    //Add event listener for opening and closing details
    var o = this;
    $('#tableBajaProductos tbody').on('click', 'td.details-control', function() {
        var tr = $(this).closest('tr');
        var row = dt.row(tr);

        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        } else {
            // Open this row
            row.child(lanzaLoading(row.data())).show();
            tr.addClass('shown');
            row.child(cargaContenido(row.data()));
            //alert("hola");
        }
    });


    var lanzaLoading = function(d) {
        //alert(d.idPyme + " La varGlob ");
        return '<div id="DIV' + d.id + '" class="col-lg-12 text-center">' +
            '<div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div>' +
            '</div>';
    };

    var cargaContenido = function(d) {
        // alert(d.id + " Probado ");
        ejecutandoCarga(d.id);
    };
}); // Cierre de document ready