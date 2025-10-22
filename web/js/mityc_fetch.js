/**
    Módulo de JavaScript para la obtención de datos de las estaciones de servicio de España.
    Autor: Néstor Santana
    Powered By: EfiData
    Versión: 1.1
    ---------------------------
    | 24 de Agosto de 2023    |
    ---------------------------

    Bibliotecas y Recursos:

    jQuery: Biblioteca JavaScript utilizada para simplificar la manipulación del DOM y realizar llamadas AJAX.
    Select2: Biblioteca jQuery que mejora la apariencia y funcionalidad de los elementos de selección en formularios.
    MITyC - Ministerio de Industria, Turismo y Comercio: Fuente oficial de los datos proporcionados, que demuestra la obtención ética y legal de la información.

    Cambios en la versión 1.1:
    · Se han añadido comentarios para mejorar la legibilidad del código.
    · Se han cambiado los nombres de las variables para mejorar la legibilidad del código.
    · Se han añadido mejoras para que el usuario pueda buscar municipios y provincias por nombre, ya que mityc tiene los pronombres de los municipios al final y en paréntesis.
    · Ahora los municipios y las provincias se guardan en la URL para que el usuario pueda compartir la búsqueda y al recargar la página se mantenga la búsqueda.
    · Ahora en vez de que los usuarios tenga que copiar y pegar los datos de la estación se envian en base64 a la cualquier pagina para que el usuario pueda gestionar los datos a su gusto.
*/

$(document).ready(function () {
    // Inicialización de variables y obtención de la URL actual
    var url = new URL(window.location.href);

    //constantes urls
    const url_provincias = "https://energia.serviciosmin.gob.es/ServiciosRESTCarburantes/PreciosCarburantes/Listados/Provincias/";
    const url_municipios = "https://energia.serviciosmin.gob.es/ServiciosRESTCarburantes/PreciosCarburantes/Listados/MunicipiosPorProvincia/";

    //constantes urls filtros
    const url_filtroMunicipio = "https://energia.serviciosmin.gob.es/ServiciosRESTCarburantes/PreciosCarburantes/EstacionesTerrestres/FiltroMunicipio/";
    const url_filtroProvincia = "https://energia.serviciosmin.gob.es/ServiciosRESTCarburantes/PreciosCarburantes/EstacionesTerrestres/FiltroProvincia/";

    //constantes url redireccion al seleccionar estacion
    const url_redireccion = "estacion.php"; //la pagina a la que te redigirá al seleccionar una estacion

    //constantes url parametros donde irán los datos de la estacion
    const nombre_param = "bsht";

    // Deshabilitar elementos y ocultar botones
    $( "#localidad_select" ).prop( "disabled", true );
    $( "#mirar_por_pronvincias" ).hide();
    $("#search").hide();

    // Llamada AJAX para obtener la lista de provincias
    $.ajax({
        url:url_provincias,  // URL para obtener las provincias
        method:"get",
        dataType:"json",
        success: function(data, msg){
            // Iterar sobre las provincias y agregarlas al elemento de selección
            $.each(data, function(key, v){
                // get provincia url params and set selected
                var provincia = url.searchParams.get("provincia");

                if(provincia != null){
                    if(provincia == v.IDPovincia){
                        $('#provincia_select').append("<option value='"+v.IDPovincia+"' selected>"+orderName(v.Provincia)+"</option>");
                        $('#provincia_select').trigger("change");

                        //si en el header no hay municipio, se muestra el boton de mirar por provincia
                        if(url.searchParams.get("municipio") == null || url.searchParams.get("municipio") == 'Select'){
                            $("#mirar_por_pronvincias").click();
                        }

                    }else{
                        $('#provincia_select').append("<option value='"+v.IDPovincia+"'>"+orderName(v.Provincia)+"</option>");
                    }
                }else{
                    $('#provincia_select').append("<option value='"+v.IDPovincia+"'>"+orderName(v.Provincia)+"</option>");
                }

            });
        },
        error: function(msg){
            console.table(msg);
        }
    });

    // Event Listener para cambio de provincia
    $('#provincia_select').change(function(){
        $("#search").text("");
        $("#search").hide();
        $("#estaciones").hide();
        $("#mirar_por_pronvincias").show();
        $("#localidad_select").prop( "disabled", false );
        $('#localidad_select').empty();
        $('#localidad_select').append('<option value="Select">Seleccionar Municipio</option>');

        //update url
        var url = new URL(window.location.href);
        url.searchParams.set('provincia', $(this).val());
        window.history.pushState({}, '', url);

        var id = $(this).val();
        if(id != "Select"){
            $( "#mirar_por_pronvincias").show();
            $( "#mirar_por_pronvincias").on("click", function(){

                //delete municipio url params
                url.searchParams.delete("municipio");
                window.history.pushState({}, '', url);
   
                load(false);
            });
        }else{
            $( "#mirar_por_pronvincias").hide();
        }

        $.ajax({
            url:url_municipios+id,
            method:"get",
            dataType:"json",
            success: function(data, msg){
                $.each(data, function(key, v){
                    // consigue municipio de los parametros de la url y lo selecciona
                    var municipio = url.searchParams.get("municipio");

                    if(municipio != null){
                        if(municipio == v.IDMunicipio){
                            $('#localidad_select').append("<option value='"+v.IDMunicipio+"' selected>"+orderName(v.Municipio)+"</option>");
                            $('#localidad_select').trigger("change");
                        }else{
                            $('#localidad_select').append("<option value='"+v.IDMunicipio+"'>"+orderName(v.Municipio)+"</option>");
                        }
                    }else{
                        $('#localidad_select').append("<option value='"+v.IDMunicipio+"'>"+orderName(v.Municipio)+"</option>");
                    }
                });
            },
            error: function(msg){
                console.table(msg);
            }
        });
    });

    // Event Listener para cambio de municipio
    $('#localidad_select').change(function(){
        $( "#mirar_por_pronvincias").hide();
        load();

        //actualizar url en el cambio de municipio
        var url = new URL(window.location.href);
        url.searchParams.set('municipio', $(this).val());
        window.history.pushState({}, '', url);
    });

    // Función para cargar estaciones
    function load(municipio = true){
        var url = municipio ? url_filtroMunicipio : url_filtroProvincia;
        var id = municipio ? $('#localidad_select').val() : $('#provincia_select').val();
        $("#estaciones").show();
        var validate = Validate();
        $("#estaciones").html(validate);
        if (validate.length == 0 && id != "Select") {
            $.ajax({
                type: "GET",
                url: url + id,
                dataType: "json",
                success: function (result, status, xhr) {
                    if(result["ListaEESSPrecio"].length > 0){
                        $("#search").show();
                        var table = null;
                        table = $("<table class='table table-striped table-hover table-bordered sortable '>\
                            <thead class='table-dark'>\
                                <tr>\
                                    <!--<th scope='col'>ID Estación</th>--!>\
                                    <th scope='col'>Nombre Estación (Rótulo)</th>\
                                    <th scope='col'>Dirección</th>\
                                    <th scope='col'>Municipio</th>\
                                    <th scope='col'>Margen</th>\
                                    <th scope='col'>Ubicación</th>\
                                    <th scope='col'>Acciones</th>\
                                </tr>\
                            </thead>\
                            <tbody>"
                        );


                        for (var i = 0; i < result["ListaEESSPrecio"].length; i++) {
                            let lat = result["ListaEESSPrecio"][i]["Latitud"].replace(",", ".");
                            let lon = result["ListaEESSPrecio"][i]["Longitud (WGS84)"].replace(",", ".");
                            let cod_estacion = result["ListaEESSPrecio"][i]["IDEESS"];
                            let margen = result["ListaEESSPrecio"][i]["Margen"];
                            let nombre_estacion = result["ListaEESSPrecio"][i]["Rótulo"];
                            let direccion = result["ListaEESSPrecio"][i]["Dirección"];
                            let municipio = result["ListaEESSPrecio"][i]["Municipio"];

                            table.append("<tr>" +
                                //"<td><b>" + cod_estacion + "</b></td>" +
                                "<td><b>" + nombre_estacion + "</b></td>" +
                                "<td>" + direccion + "</td>" +
                                "<td>" + orderName(municipio) + "</td>" +
                                "<td style='font-weight: bold'>" + margen + "</td>" +
                                "<td><a href='https://www.google.com/maps/search/?api=1&query=" + lat + "," + lon + "' target='_blank' class='btn btn-warning'><i class='bi bi-geo-alt-fill'></i> Ver en maps</a></td>" +
                                "<td><button class='btn btn-success' onclick="+selected_station(result["ListaEESSPrecio"][i])+">Seleccionar</button></td>" +
                                "</tr>"
                            );
                        }
                    }else{
                        var table = $(
                            "<hr>"+
                            "<div class='alert alert-danger p-0 pt-3 ps-2' role='alert' style='width: 31rem;'>"+
                            "<p><b>Información:</b> No se ha encontrado estaciones en este municipio</p>"+
                            "</div>"
                        );
                        $("#search").hide();
                        $("#mirar_por_pronvincias").show();
                    }
                    $("#estaciones").append("</tbody></table>");
                    $("#estaciones").html(table);
                },
                error: function (xhr, status, error) {
                    alert("Se ha producido un error. Más Info: " + status + " " + error + " " + xhr.status + " " + xhr.statusText)
                    location.reload();
                }
            });
        }
        if(id == "Select"){
            $("#estaciones").html("");
            $("#search").hide();
            $( "#mirar_por_pronvincias").show();
        }
    }
    $("#search").on("keyup", function() {
        var towe = $(this).val().toLowerCase();
        var value =  towe;
        $("tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Función para generar enlace con datos de la estación
    function selected_station(data) {
        let b64 = btoa(unescape(encodeURIComponent(JSON.stringify(data))));
        return "window.location.href='"+url_redireccion+"?"+"m="+data["IDMunicipio"]+"&p="+data["IDProvincia"]+"&"+nombre_param+"="+b64+"'";
    }

    // Función de validación
    function Validate() {
        var errorMessage = "";
        if ($("#provincia_select").val() == "Select") {
            errorMessage += ">>>> Selecione un Municipio";
        }
        return errorMessage;
    }

    function orderName(nombre){
        let pronombres = ["A", "EL", "LA", "LOS", "LAS", "DE", "DEL", "AL", "ILLES", "El", "La", 
            "Los", "Las", "De", "Del", "Al", "Illes", "la", "l'", "L'", "els", "Els", "el", "El", 
            "Es", "Sa", "Ses", "Les", "les", "O", "As","Os"];
        let name = nombre;
        for (var i = 0; i < pronombres.length; i++) {
            var pronombre = pronombres[i];
            if (name.endsWith(" (" + pronombre + ")")) {
                // Eliminar el pronombre del final y moverlo al principio
                name = pronombre + " " + name.substring(0, name.length - pronombre.length - 3);
                break; // Terminar el bucle una vez que se encuentra el pronombre adecuado
            }
        }
        return name;
    }
});
// Event Listeners globales para manejo de carga AJAX
$(document).ajaxStart(function () {
    $("#loading").show();
    $( "#provincia_select" ).prop( "disabled", true );
});
// Event Listeners globales para manejo de carga AJAX
$(document).ajaxStop(function () {
    $("#loading").hide();
    $("#provincia_select").prop( "disabled", false );
});
let opciones = {
    width: '100%',
    placeholder: 'Elige una opción',
    language: {
        noResults: function() {
            return "No se han encontrado resultados";
        },
        searching: function() {
            return "Buscando..";
        }
    }
};
// Configuración de la libreria Select2
$("#provincia_select").select2(opciones);
$("#localidad_select").select2(opciones);