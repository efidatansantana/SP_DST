$(document).ready(function() {

    //configuracion de los multiselect de los productos
    $('#selected_producto').multiselect({
        columns: 1,
        placeholder: 'Selecciona los productos',
        search: true,
        searchOptions: {
            'default': 'Buscar producto'
        }
    });

    //configuracion de los multiselect de las empresas
    $('#selected_empresa').multiselect({
        columns: 1,
        placeholder: 'Selecciona las empresas',
        search: true,
        searchOptions: {
            'default': 'Buscar empresa'
        }
    });


    //reloj del header
    $('#reloj').html(hora());
    setInterval(function() {
        $('#reloj').html(hora());
    }, 1000);

    function hora(){
        var date = new Date();

        var dia = date.getDate();
        var mes_letra = date.getMonth();
        var anio = date.getFullYear();
        var hora = date.getHours();
        var minutos = date.getMinutes();

        if(hora < 10){
            hora = '0' + hora;
        }

        if(minutos < 10){
            minutos = '0' + minutos;
        }

        var meses = new Array("Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic");
        var dias = new Array("Dom","Lun","Mar","Mie","Jue","Vie","Sab");
        var mes = meses[mes_letra];
        var dias_l = dias[date.getDay()];

        var horita = dias_l + ' ' +dia + ' de ' + mes + ' ' + anio + ' - <b>' + hora + ':' + minutos+'</b>';
        return horita;
    }



    //select2 bootstrap 5 
    $('.custom-select').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });
});