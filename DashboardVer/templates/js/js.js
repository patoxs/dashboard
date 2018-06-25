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
        $('#datos_mapa').prepend(X.aviso).delay( 800 );
    },

    cierraMensaje: function(){
        $('#waiting_acepta').remove();
        return false;
    },

    mensaje: function(mensaje) {
        var mensaje = "<div id='waiting_acepta' style='background: rgba(255, 255, 255, 0.6);z-index: 1010;width: 100%;height: 100%;position: fixed;'><div style='position: absolute;top: 50%;left: 50%;width: 640px;height: 120px;margin-left: -320px;margin-top: -60px;z-index:1011;'><div data-alert='' class='alert-box secondary radius'><div class='row'><div class='large-8 columns'>" + mensaje + "</div><div class='large-4 columns'><a href='' id='acepta-mensaje' class='button tiny success'>Aceptar</a></div></div></div></div></div>";
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
        linea.push(keys);
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



var dashboard = {

    col: {},
    data: {},

	start: function() {
        $(".ver_dashboard").click(dashboard.desplegarDashboard);
	},

    utf8_to_b64: function(str) {
        return window.btoa(unescape(encodeURIComponent( str )));
    },

    desplegarDashboard: function(){
        var dashboard = $(this).data("id");
        var datos = {
            'accion':'ver_un_dashboard',
            'id_dashboard': dashboard
        };
        var callback = function(response){
            $("#desplega_dashboard").empty();
            $("#desplega_dashboard").append(response);
        };
        X.ajaxDLP(datos, callback);
    },

	
    
};



$(document).ready(dashboard.start);
