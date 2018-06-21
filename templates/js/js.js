var X = {
    direccion: '?mid=\\Reporteria\\Dashboard&noTemplate',
    tipo: 'post',
    sincronia: true,
    aviso: "<div id='waiting' style='background: rgba(255, 255, 255, 0.74);z-index: 1010;width: 100%;height: 100%;margin-top: -30px;position: fixed;'><div style='position:fixed;top:25%;left:50%;margin-right:63px;margin-bottom:63px;z-index:1011;'><i class='fa fa-refresh fa-spin fa-3x'></i><h3>Espere...</h3></div></div>",

    ajaxDLP: function(datos, callback) {
        $.ajax({
            data:  datos,
            url:   X.direccion,
            type:  X.tipo,
            async: X.sincronia,
            beforeSend: X.antes,
            success:  function (response) {
                callback(response);
            }
        });
    },

    antes: function(){
        $('body').prepend(X.aviso).delay( 800 );
    },

    cierraMensaje: function(){
        $('#waiting_acepta').remove();
        return false;
    },

    mensaje: function(mensaje) {
        var mensaje = "<div id='waiting_acepta' style='background: rgba(255, 255, 255, 0.6);z-index: 1010;width: 100%;height: 100%;position: fixed;'><div style='position: absolute;top: 50%;left: 50%;width: 640px;height: 120px;margin-left: -320px;margin-top: -60px;z-index:1011;'><div class='row'><div class='large-12 columns'>" + mensaje + "</div></div><div class='row'><div class='large-12 columns'><a href='' id='acepta-mensaje' class='button tiny info'>Aceptar</a></div></div></div></div>";
        $('body').prepend(mensaje);
        $("#acepta-mensaje").click(X.cierraMensaje);
    },

    isJSON: function(str) {
	    try {
	        JSON.parse(str);
	    } catch (e) {
	        return false;
	    }
	    return true;
	},

    jsonToHtmlTable: function(data) {
        var table = document.createElement("table");
        var thead = table.createTHead();
        var tbody = table.createTBody();
        
        var col = X.getJsonKey(data);
 
        var cabecera = thead.insertRow(-1);

        for (var i = 0; i < col.length; i++) {
            var th = document.createElement("th");
            th.innerHTML = col[i];
            cabecera.appendChild(th);
        }
        

        for (var i = 0; i < data.length; i++) {

            tr = tbody.insertRow(-1);

            for (var j = 0; j < col.length; j++) {
                var tabCell = tr.insertCell(-1);
                tabCell.innerHTML = data[i][col[j]];
            }
        }

        return table;
    },

    jsonToArrayKey: function(datos, keys){
        var array = typeof datos != 'object' ? JSON.parse(datos) : datos;
        var linea = [];
        if (keys != 0) {linea.push(keys);}
        var col = X.getJsonKey(datos);
        for (var i = 0; i < array.length; i++) {
            data = [];
            
            for (var j = 0; j < col.length; j++) {
                var valor = array[i][col[j]];
                if (j == 0) {
                    data.push(valor);
                } else {
                    data.push(parseFloat(valor));
                }
            }
            linea.push(data);
        }
        return linea;
    },

    getJsonKey: function(data) {
        var col = [];
        for (var i = 0; i < data.length; i++) {
            for (var key in data[i]) {
                if (col.indexOf(key) === -1) {
                    col.push(key);
                }
            }
        }
        return col;
    },
};

var DOMhtml = {

    label: function(texto, elemento) {  
        var nuevolabel = document.createElement('label');
        nuevolabel.innerHTML = texto;
        nuevolabel.appendChild(elemento);
        return nuevolabel;
    },

    selec: function(data, id, texto) {
        var sel = document.createElement('select');
        sel.setAttribute("id", id);
        var option = document.createElement("option");
        option.text = "Seleccione";
        option.value = 0;
        sel.add(option);
        for (var i = 0; i < data.length; i++) {
            var opt = document.createElement('option');
            opt.innerHTML = data[i];
            opt.value = data[i];
            sel.appendChild(opt);
        }
        return DOMhtml.label(texto, sel);
    },

    input: function(campo){
        var input = document.createElement("INPUT");
        var texto_label = "Titulo Columna " + campo;
        input.setAttribute("type", "text");
        input.setAttribute("class", "columnas_graficos");
        input.setAttribute("id", campo);
        input.setAttribute("name", campo);
        return DOMhtml.label(texto_label, input);
    },

    row: function(columna){
        var nuevoDiv = document.createElement("div");
        nuevoDiv.setAttribute("class", "row");
        nuevoDiv.appendChild(columna);
        return nuevoDiv;
    },

    columns: function(contenido, size){
        var nuevoDiv = document.createElement("div");
        nuevoDiv.setAttribute("class", "large-"+size+" columns");
        nuevoDiv.appendChild(contenido);
        return nuevoDiv;
    },
    

};


var Informe = {

    arreglo: [],

    conf: {
        titulo: 'Definir Titulo',
        hAxis: 'Leyenda eje x',
        vAxis: 'Leyenda eje y',
        piehole: '',
        tresd: false,
        tipo_grafico: 'BarChart',
        datos: [],
        titulo_columnas: [],
        filter: false,
        filter_type: 'CategoryFilter',
        filter_column: '',
    },

    init: function(x){
        for(var i in x) {
            Informe.conf[i] = x[i];
        }
    },
    
    graficar: function() {
        $('#seleccion').html('');

        if (Informe.conf.filter) {
            Informe.conf.arreglo = X.jsonToArrayKey(
                                    Informe.conf.datos,
                                    Informe.conf.titulo_columnas,
                                );
            google.charts.load('current', {'packages':['corechart', 'controls']});
            google.charts.setOnLoadCallback(Informe.dashboard);
        } else {
            if (Informe.conf.tipo_grafico == 'Table') {
                Informe.conf.arreglo = X.jsonToArrayKey(
                                        Informe.conf.datos,
                                        0,
                                    );
                google.charts.load('current', {'packages':['table']});
                google.charts.setOnLoadCallback(Informe.tabla);
            } else {
                Informe.conf.arreglo = X.jsonToArrayKey(
                                        Informe.conf.datos,
                                        Informe.conf.titulo_columnas,
                                    );
                google.charts.load('current', {'packages':['corechart']});
                google.charts.setOnLoadCallback(Informe.grafico);
            }
        }
        
            
        
    },
    
    grafico: function() {
        var tipo = Informe.conf.tipo_grafico;
        var data = google.visualization.arrayToDataTable(Informe.conf.arreglo);
        var options = {
            title: Informe.conf.titulo,
            hAxis: { title: Informe.conf.hAxis},
            vAxis: { title: Informe.conf.vAxis },
            pieHole: Informe.conf.piehole,
            is3D: Informe.conf.tresd
        };
        eval('var chart = new google.visualization.'+tipo+'(document.getElementById(\'chart_div\'));');
        chart.draw(data, options);
    },

    tabla: function() {
        var data = new google.visualization.DataTable();
        var cantidad = Informe.conf.titulo_columnas.length;
        for (var i = 0; i < cantidad; i++) {
            if (typeof Informe.conf.arreglo[1][i] == 'string') {
                var tipo = 'string';
            } else if (typeof Informe.conf.arreglo[1][i] == 'number') {
                var tipo = 'number';
            } else {
                var tipo = 'boolean';
            }
            data.addColumn(tipo, Informe.conf.titulo_columnas[i]);
        }
        data.addRows(Informe.conf.arreglo);
        var options = {showRowNumber: true, width: '100%', height: '100%'};
        var table = new google.visualization.Table(document.getElementById('chart_div'));
        table.draw(data, options);
    },

    dashboard: function() {
        var data = google.visualization.arrayToDataTable(Informe.conf.arreglo);
        var dashboard = new google.visualization.Dashboard(document.getElementById('chart_div'));
        var filtro = new google.visualization.ControlWrapper({
          'controlType': Informe.conf.filter_type, // 2 - NumberRangeFilter | 1 - CategoryFilter
          'containerId': 'filter_div',
          'options': {
            'filterColumnLabel': Informe.conf.filter_column
          }
        });
        var grafico = new google.visualization.ChartWrapper({
          'chartType': Informe.conf.tipo_grafico,
          'containerId': 'chart_div',
          'options': {
            'width': '100%',
            'height': '100%',
            'pieSliceText': 'value',
            'legend': 'right'
          }
        });
        dashboard.bind(filtro, grafico);
        dashboard.draw(data);
    },

};


var dashboard = {

    col: {},
    data: {},

	start: function() {

        $("#nuevo_dashboard").click(dashboard.nuevoDashboard);
        $("#nuevo_box").click(dashboard.nuevoBox);
        $("#ver_dashboard").click(dashboard.verDashboard);
        $("#ver_box").click(dashboard.verBox); 
        $(".ver_un_dashboard").click(dashboard.verUnDashboard);
	},

    utf8_to_b64: function(str) {
        return window.btoa(unescape(encodeURIComponent( str )));
    },

    nuevoDashboard: function(){
        $(".div_contenedor").hide();
        $(".item").removeClass('active');
        $("#nuevo_dashboard").addClass('active');
        $("#div_nuevo_dashboard").show();
        $('button').unbind('click');
        $("#guardar_dashboard").click(dashboard.crearDashboard);
    },

    nuevoBox: function(){
        $(".item").removeClass('active');
        $("#nuevo_box").addClass('active');
        $(".div_contenedor").hide();
        $("#div_nuevo_box").show();
        $('button').unbind('click');
        $("#run_query").click(dashboard.ejecutarQuery);
    },

    verDashboard: function(){
        $(".item").removeClass('active');
        $("#ver_dashboard").addClass('active');
        $(".div_contenedor").hide();

        var datos = {
            'accion':'desplegar_dashboards',
        };
      
        var callback = function(response){
            $("#body_tabla_dashboard").empty();
            $("#body_tabla_dashboard").append(response);
            $("#div_tabla_dashboard").show();
            $("#waiting").remove();
            $(".ver_un_dashboard").click(dashboard.verUnDashboard);
        };
        X.ajaxDLP(datos, callback);
        

    },

    verBox: function(){
        $(".item").removeClass('active');
        $("#ver_box").addClass('active');
        $(".div_contenedor").hide();

        var datos = {
            'accion':'desplegar_boxes',
        };
      
        var callback = function(response){
            $("#body_tabla_boxes").empty();
            $("#body_tabla_boxes").append(response);
            $("#div_tabla_boxes").show();
            $("#waiting").remove();
        };
        X.ajaxDLP(datos, callback);
        
    },

    verUnDashboard: function(){
        var dashboard = $(this).data("id");

        var datos = {
            'accion':'ver_un_dashboard',
            'id_dashboard': dashboard
        };
      
        var callback = function(response){
            $("#desplega_dashboard").empty();
            $("#desplega_dashboard").append(response);
            $("#waiting").remove();
        };
        X.ajaxDLP(datos, callback);
    },

	ejecutarQuery: function() {
        var query = $('#query').val();
        var querysp = $('input[name=tipo_sql]:checked').val();
        var region = $('#region_default').val();
        var area = $('#area_default').val();
        var programa = $('#programa_default').val();

        var datos = {
            'accion':'ejecutar_query',
            'query': dashboard.utf8_to_b64(query),
            'querysp': querysp,
            'region': region,
            'area': area,
            'programa': programa
        };
        
        var callback = function(response){
            
            if (X.isJSON(response)) {
                dashboard.data = JSON.parse(response);
                $('#query_exec').html(X.jsonToHtmlTable(dashboard.data));
                dashboard.col = X.getJsonKey(dashboard.data);  
                var columnas = dashboard.col;
                $('#campos').html('');
                for (var i = 0; i < columnas.length; i++) {
                    $('#campos').append(DOMhtml.input(columnas[i]));
                }
                $('#previsualizar').click(dashboard.tipoBox);
                $('.control_grafico').click(dashboard.controlGrafico);
                $("#tipo_box").change(dashboard.cambioDeGrafico);
                $("#div_columna_control_grafico").empty();
                $("#div_columna_control_grafico").append( DOMhtml.selec(columnas, 'columna_control_grafico', 'Columna de control') );
            }
            $('#waiting').remove();
        };
        X.ajaxDLP(datos, callback);
	},

    cambioDeGrafico: function() {
        $("#div_previsualizar").show();
    },

    controlGrafico: function() {
        var control_grafico = $('input[name=control_grafico]:checked').val();
        if (control_grafico == 'Si') {
            $('#div_control_grafico').show();
            $("#filter_div").empty();
            $("#chart_div").empty();
        } else {
            $('#div_control_grafico').hide();
            $("#filter_div").empty();
            $("#chart_div").empty();
        }
    },

    tipoBox: function() {
        var grafico = $('#tipo_box').val();
        if (grafico != 0) {
            var encabezado = [];
            $(".columnas_graficos").each(function(index){
                encabezado.push( $( this ).val() );
            });
            var control_grafico = $('input[name=control_grafico]:checked').val();
            var filter = false;
            var filter_type = 'CategoryFilter';
            var filter_column = '';
            if (control_grafico == 'Si') {
                filter = true;
                filter_type = $("#tipo_control_grafico").val();
                filter_column_id = $("#columna_control_grafico").val();
                filter_column = $("#"+filter_column_id).val();
            }
            Informe.init({
                titulo: $("#titulo_box").val(),
                hAxis: $("#titulo_eje_x").val(),
                vAxis: $("#titulo_eje_y").val(),
                tipo_grafico: grafico,
                datos: dashboard.data,
                titulo_columnas: encabezado,
                filter: filter,
                filter_type: filter_type,
                filter_column: filter_column,
            });
            Informe.graficar();
            $("#div_guardar_box").show();
            $("#div_limpiar_box").show();
            $("#guardar_box").unbind('click');
            $("#guardar_box").click(dashboard.guardarBox);
            $("#limpiar_box").unbind('click');
            $("#limpiar_box").click(dashboard.limpiarBox);
        } else {
            return false;
        }
    },

    guardarBox: function() {
        var b = $('#id_box').val();
        filter_column_id = $("#columna_control_grafico").val();
        filter_column = $("#"+filter_column_id).val();
        var datos = {
            'accion':'guardar_box',
            'query': dashboard.utf8_to_b64($('#query').val()),
            'id_tipo_grafico': $('#tipo_box').find(':selected').data('id'),
            'titulo': $('#titulo_box').val(),
            'eje_x': $('#titulo_eje_x').val(),
            'eje_y': $('#titulo_eje_y').val(),
            'columnas': Informe.conf.titulo_columnas,
            'activo': 1,
            'id_box': $('#id_box').val(),
            'filtro_columna': filter_column,
            'tipo_control_grafico': $("#tipo_control_grafico").val(),
            'control_grafico': $('input[name=control_grafico]:checked').val();
        };
        
        var callback = function(response){
            if (X.isJSON(response)) {
                respuesta = JSON.parse(response);
                if (respuesta.estado == true && b == 0) {
                    $("#id_box").val(respuesta.id_registro);
                    X.mensaje('<div data-alert class="alert-box success radius">El box se creo con éxito</div>');
                } else if (respuesta.estado == true) {
                    X.mensaje('<div data-alert class="alert-box success radius">Se guardaron las modificaciones del box</div>');
                } else {
                    console.log(respuesta.mensaje);
                    return false;
                }
            }
            $('#waiting').remove();
        };

        X.ajaxDLP(datos, callback);
    },


    limpiarBox: function() {
        $("#campos").empty();
        $("#tipo_box").val(0);
        $("#titulo_box").val('');
        $("#titulo_eje_y").val('');
        $("#titulo_eje_x").val('');
        $("#query").val('');
        $("#chart_div").empty();
        $("#query_exec").empty();
        $("#id_box").val(0);
        $("#div_guardar_box").hide();
        $("#div_limpiar_box").hide();
        $("#div_previsualizar").hide();
    },


    crearDashboard: function() {
        formulario_valido = false;
        ValidaMe.init({
            mark:'data-uno'
        });
        var formulario_valido = ValidaMe.validate();
        if (formulario_valido) {
            var b = $('#id_dashboard').val();
            var numero = $('#nombre_dasboard').val();
            var datos = {
                'accion':'crear_dashboard',
                'nombre': $('#nombre_dasboard').val(),
                'menu': $('#menu_dasboard').val(),
                'perfil': $('#perfil').val(),
                'ambito': $('#ambito').val(),
                'icono': $('#icono').val(),
                'id_dashboard': $('#id_dashboard').val(),
            };
            
            var callback = function(response){

                if (X.isJSON(response)) {

                    respuesta = JSON.parse(response);

                    if (respuesta.estado == true && b == 0) {
                        $("#id_dashboard").val(respuesta.id_registro);
                    }
                    if (respuesta.estado == true) {
                        $("#guardar_dashboard").hide();
                        $("#generar_grilla").show();
                        $("#numero_columnas").change(dashboard.crearColumnas);
                    }

                    $('#waiting').remove();
                    X.mensaje(respuesta.mensaje);
                    
                }
                
            };

            X.ajaxDLP(datos, callback);
        }
    },

    crearColumnas: function() {
        var numero = $('#numero_columnas').val();
        var datos = {
            'accion':'crear_columnas',
            'columnas': numero,
        };
        
        var callback = function(response){
            $("#celdas").html('');
            $("#celdas").html(response);
            $('#waiting').remove();
            $("#guardar_linea").unbind();
            $("#guardar_linea").click(dashboard.agregarLinea);
            $('#numero_columnas').val(0);
        };

        X.ajaxDLP(datos, callback);
    },

    agregarLinea: function() {

        var n = $('#previsualizacion_grilla .row.linea_grilla').length;
        $('#previsualizacion_grilla').append('<div class="row linea_grilla" id="linea_'+n+'"><div class="large-11 columns"><div class="row contenido_linea_grilla" data-linea="'+n+'"></div></div><div class="large-1 columns"><button type="button" class="botonRojo eliminar_linea_grilla" data-linea="'+n+'"><span><i class="fa fa-trash" aria-hidden="true"></i></span></button></div></div>')
        $("#celdas").unbind();
        $('#celdas').children().appendTo('#linea_'+n+' .contenido_linea_grilla');

        $(".eliminar_linea_grilla").unbind();
        $(".eliminar_linea_grilla").click(dashboard.eliminarLineaGrilla);
        $("#guardar_grilla").unbind();
        $("#guardar_grilla").click(dashboard.guardarGrilla);

    },

    eliminarLineaGrilla:function() {
        var linea = $(this).data('linea');
        $("#linea_"+linea).remove();
        var n = 0;
        $('#previsualizacion_grilla .row.linea_grilla').each(function(index){
            $(this).attr("id","linea_"+n);
            n = n + 1;
        });
    },

    guardarGrilla: function(){
        var linea = new Array();
        var celda = new Array();
        var box;
        var columna;
        $( ".contenido_linea_grilla" ).each(function( index ) {
            var li = $(this).data('linea');

            linea[li] = new Array();

            $(this).find(".celda").each(function(index){
                celda = new Array();
                columna = $(this).data("columna");
                box = $(this).find('select option:selected').val();
                celda.push( parseInt(columna), parseInt(box) );
                linea[li].push(celda);
            });

        });
        var datos = {
            'accion':'guardar_grilla',
            'dashboard': $("#id_dashboard").val(),
            'datos': JSON.stringify(linea),
        };
        
        var callback = function(response){

            $('#waiting').remove();
            X.mensaje('<div data-alert class="alert-box success radius">El dashboard se creo con éxito</div>');

            $("#previsualizacion_grilla").empty();
            $("#celdas").empty();
            $("#numero_columnas").val(0);
            $("#generar_grilla").hide();

            $("#guardar_dashboard").show();
            $("#icono").val(0);
            $("#ambito").val(0);
            $("#perfil").val(0);
            $("#menu_dasboard").val('');
            $("#nombre_dasboard").val('');
            $("#id_dashboard").val(0);

        };

        X.ajaxDLP(datos, callback);
        
    },

    
};



$(document).ready(dashboard.start);
