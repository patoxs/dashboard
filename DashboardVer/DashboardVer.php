<?php 
/**
 * Descripcion DashboardVer
 * @package Reporteria
 * @filesource DashboardVer.php
 */

namespace Reporteria;

/**
 * Descripcion DashboardVer
 * @author  
 * @version 0.0.0
 * @license GPL
 * @package Reporteria
 */
class DashboardVer extends \Core\BaseModuloHoja
{
    /**
     * Constructor
     * @see \Core\BaseModuloHoja::__construct()
     */
    function __construct()
    {
        $lang = new \Core\TextConfig();
        if (is_file( __DIR__ ."/".$_SESSION['config']['base']['lang'] . ".txt")) {
            $lang->setFile( __DIR__ ."/".$_SESSION['config']['base']['lang'] . ".txt");
            $this->lang = $lang->getList();
        } elseif (is_file( __DIR__ ."/es.txt")) {
            $lang->getFile( __DIR__ ."/es.txt");
            $this->lang = $lang->getList();
        } else {
            \Core\SysLogger::emergencia(__CLASS__,'No existe archivo de idioma en <strong>' .__CLASS__. '->' .__FUNCTION__. '()</strong> ubicado en : ' .__DIR__);
        }
        $this->path = str_replace($_SESSION['config']['base']['path'].'/','',__DIR__);
        parent::__construct();
        
        $this->addFoot(\Core\Html::scriptjs($this->path.'/templates/js/js.js'));
    }

    /**
     * metodo de procesamiento principal del modulo
     * @param array $Pdata datos llegados por POST limpiados
     * @param array $Gdata datos llegados por GET limpiados
     * @param \Core\DataBase $conexion_sql objeto de conexion a la base de datos
     * @param \Core\Usuario $user objeto del tipo usuario
     * @return string codigo resultante de procesar
     */
    public function process($Pdata, $Gdata, $conexion_sql, $user){
        

        $this->user = $user;
        $this->perfil = $user->getPerfilActual();
        $this->zona_activa = $user->getZonaActiva();
        $this->area_activa = $user->getAreaActiva();

        if (!isset($Pdata['accion'])) {
            $funcion = $this->getDefault($user);
        } else {
            $funcion = $this->pasarACamelCase(explode('_', $Pdata['accion']));
        }

        $this->code = $funcion;

        return $this->code;
    }

    private function getDefault($user)
    {
        if ($user->getAmbitoActivo() == 1) {
            $respuesta = $this->perfilNacional();
        } else if ($user->getAmbitoActivo() == 2) {
            $respuesta = $this->perfilRegional();
        } else if ($user->getAmbitoActivo() == 4) {
            $respuesta = $this->perfilArea();
        } else {
            $respuesta = "<h1>No tiene acceso al modulo de dashboard</h1>";
        }
        return $respuesta;
    }

    /**
     * metodo que pasa un array a una cadena en formato camel case
     * @param  array $arreglo
     * @return string
     */
    private function pasarACamelCase($arreglo)
    {
        $cadena = '';
        foreach ($arreglo as $key => $value) {
            if ($key == 0) {
                $cadena .= strtolower($value);
            } else {
                $cadena .= ucfirst(strtolower($value));
            }
        }
        return $cadena;
    }

    /**
     * metodo que retorna si es publico el modulo o no
     * @return bool
     */
    public function esPublica()
    {
        return false;
    }

    /**
    * Parsea un archivo, reemplazando las etiquetas segun su marca
    * @param string $ruta ruta del template a parsear
    * @param array $data deuda_directa del par '###MARCA###' => 'valor' a replazar
    * @return string codigo generado
    */
    private function parseHTML($ruta, $data = array())
    {
        $obtpl = new \Core\Template();
        $obtpl->setTemplate(__DIR__.$ruta);
        $obtpl->llena($data);
        $obtpl->llena($this->lang);        
        return $obtpl->getCode();
    }

    /**
    * Carga mensaje y bot$oacute;n para vincular  unidad operativa a entidad ejecutora
    * @return string codigo generado
    */
    public function sinDatos()
    {
        return  '<div data-alert class="alert-box warning radius">
                    No existe información
                </div>
                ';
    }

    /**
     * Ejecuta las query creadas en el formulario del dashboard
     * @param  sql tipo string Query
     * @return array devuelve un arreglo con la respuesta desde la bd
     */
    protected function ejecutarSelect($sql){ 
        $conf = $_SESSION['config']['base'];
        $pass = \Core\Encriptador::desencriptar($conf['dbclaveAll']);
        $obd = new \Core\DataBase($conf['dbhost'], $conf['dbport'], $conf['dbuserAll'], $pass, $conf['dbdatabase']);
        $datos = $obd->select($sql);
        if(!empty($datos)){
            return $datos;
        }
        return 0;
    }

    /**
     * metodo que gestiona los informes de un perfil nacional
     * @return bool
     */
    private function perfilNacional()
    {
        $respuesta = $this->queryDashboardPorPerfil();
        $data['###dashboards###'] = $this->generaMenu($respuesta);
        $ruta = '/templates/html/menu.html';
        return $this->parseHTML($ruta, $data);
    }

    /**
     * metodo que gestiona los informes de un perfil regional
     * @return bool
     */
    private function perfilRegional()
    {
        print_r('perfil regional');
        $respuesta = $this->queryDashboardPorPerfil();        
        return false;
    }

    /**
     * metodo que gestiona los informes de un perfil area
     * @return bool
     */
    private function perfilArea()
    {
        print_r('perfil area');
        $respuesta = $this->queryDashboardPorPerfil();       
        return false;
    }

    /**
     * Obtiene via bd los elementos desplegar en el menu para un perfil y ambito definido
     * @param  
     * @return string html generado
     */
    private function queryDashboardPorPerfil(){

        $ambito = $this->user->getAmbitoActivo();

        $perfil = '';
        foreach ($this->perfil as $key => $value) {
            $perfil .= $value;
            if (next($this->perfil)==true) $perfil .= ',';
        }

        $sql = 'SELECT 
                    dashboard.id_dashboard, 
                    dashboard.nombre, 
                    dashboard.menu, 
                    dashboard.id_perfil, 
                    dashboard.id_tipo_ambito, 
                    dashboard.activo,
                    ambito.tipo as ambito,
                    perfil.nombre as perfil
                FROM dashboard.dashboard as dashboard
                INNER JOIN core.tipo_ambito as ambito 
                    on (ambito.id_tipo_ambito = dashboard.id_tipo_ambito)
                INNER JOIN core.perfil as perfil
                    on (perfil.id_perfil = dashboard.id_perfil)
                WHERE dashboard.activo = 1
                AND dashboard.id_tipo_ambito = '.$ambito.' 
                AND dashboard.id_perfil in ('.$perfil.');';
        return $this->ejecutarSelect($sql);

    }


    /**
     * Obtiene via bd los elementos desplegar en el menu para un perfil y ambito definido
     * @param  
     * @return string html generado
     */
    private function generaMenu($datos){

        $elemento = '';
        $cuenta = 0;
        foreach ($datos as $temp) {

            $elemento .= '<li><a href="#" class="ver_dashboard" data-id="'.$temp['id_dashboard'].'">'.$temp['menu'].'</a></li>';

        }

        return $elemento;

    }

    /**
     * Genera la previsualizacion de un dashboard
     * @param  $Pdata array
     * @return string html generado
     */
    private function verUnDashboard($Pdata){
        extract($Pdata);
        $sql = '
                SELECT 
                    dashboard.id_dashboard, 
                    dashboard.nombre, 
                    dashboard.menu, 
                    dashboard.id_perfil, 
                    dashboard.id_tipo_ambito, 
                    dashboard.activo,
                    ambito.tipo as ambito,
                    perfil.nombre as perfil,
                    dashboardbox.linea,
                    dashboardbox.columna,
                    box.id_box,
                    box.titulo as titulo_box,
                    box.query,
                    box.eje_x,
                    box.eje_y,
                    box.columnas,
                    box.filtro_columna,
                    box.tipo_control_grafico,
                    box.control_grafico,
                    tipografico.codigo as tipo_grafico
                FROM dashboard.dashboard as dashboard
                INNER JOIN core.tipo_ambito as ambito 
                    on (ambito.id_tipo_ambito = dashboard.id_tipo_ambito)
                INNER JOIN core.perfil as perfil
                    on (perfil.id_perfil = dashboard.id_perfil)
                INNER JOIN dashboard.dashboard_box as dashboardbox
                    on (dashboardbox.id_dashboard = dashboard.id_dashboard)
                INNER JOIN dashboard.box as box
                    on box.id_box = dashboardbox.id_box
                INNER JOIN dashboard.tipo_grafico as tipografico
                    on tipografico.id_tipo_grafico = box.id_tipo_grafico
                WHERE dashboard.id_dashboard = '.$id_dashboard.';';

        $respuesta = $this->ejecutarSelect($sql);

        return $this->verUnDashboardHtml($respuesta);

    }


    /**
     * Genera html con el informe completo
     * @param  $datos array con data con toda la información la procesar la pagina
     * @return string html generado
     */
    private function verUnDashboardHtml($datos){
        $data = array();
        $data['###titulo###'] = $datos[0]['nombre'];
        $data['###contenedores###'] = '';
        $data['###scriptjs###'] = '';
        $data['###estilos###'] = '';
        $background_colors = array('#3cba54', '#f4c20d', '#db3236', '#4885ed', '#7D3C98');
        $total_datos = count($datos);
        $row = '';
        $celda = '';

        $i = 0;

        foreach ($datos as $temp) {
            extract($temp);
            $id_celda = uniqid();
            $query = base64_decode( $query );
            $arreglo_box = $this->ejecutarSelect(stripslashes($query));
            $columnas = stripslashes($columnas);

            $rand_background = $background_colors[array_rand($background_colors)];

            $celda .= '<div class="large-'.$columna.' columns" style="border-top: solid 4px '.$rand_background.'"><h4>'.$titulo_box.'</h4><div id="div_'.$id_celda.'"></div></div>'; 

            if ($datos[$i+1]['linea'] != $linea) {
                $row .= '<div class="row">'.$celda.'</div>';   
                $celda = ''; 
            }

            $matriz = "";
            foreach ($arreglo_box as $linea => $elementos) {
                $matriz .= "[";
                $r = 0;
                foreach ($elementos as $key => $value) {
                    if($r != 0) {
                        $matriz .= $value;
                    } else {
                        $matriz .= '"'.$value.'"';
                    }
                    $r = $r +1;
                    if (next($elementos)==true) $matriz .= ",";
                }
                $matriz .= "],";
            }

            $datos = array(
                        "id_celda" => $id_celda, 
                        "columnas" => $columnas, 
                        "matriz" => $matriz, 
                        "eje_x" => $eje_x, 
                        "eje_y" => $eje_y, 
                        "tipo_grafico" => $tipo_grafico,
                    );

            if ($control_grafico == 'Si') {
                $datos["filter_type"] = $tipo_control_grafico;
                $datos["filter_column"] = $filtro_columna;
                $data['###scriptjs###'] .= $this->creaJsDashboard($datos);
            } else {
                if ($tipo_grafico == 'Table') {
                    $datos["arreglo_box"] = $arreglo_box[0];
                    $data['###scriptjs###'] .= $this->creaJsTabla($datos);
                } else {
                    $data['###scriptjs###'] .= $this->creaJsGrafico($datos);
                }
            }
            
            $data['###estilos###'] .= '';
            $i = $i + 1;
        }

        $data['###contenedores###'] = $row;


        $ruta = '/templates/html/dashboard.html';
        return $this->parseHTML($ruta, $data);

    }

    /**
     * Genera el script js para crear un grafico de google
     * @param  $datos array con data con toda la información la procesar la pagina
     * @return string html generado
     */
    private function creaJsGrafico($datos)
    {
        extract($datos);
        $graficojs = "google.charts.load('current', {'packages':['corechart']});";
        $graficojs .= "google.charts.setOnLoadCallback(draw_grafico_".$id_celda.");";
        $graficojs .= "function draw_grafico_".$id_celda."() {
                            var data = google.visualization.arrayToDataTable([".$columnas.",".$matriz."]);
                            var options = {
                                height: 250,
                                hAxis: {title:'".$eje_x."'},
                                vAxis: {title:'".$eje_y."'}  
                            };
                            var chart = new google.visualization.".$tipo_grafico."(document.getElementById('div_".$id_celda."'));
                            chart.draw(data, options);
                        }";
        return $graficojs;
    }

    /**
     * Genera el script js para crear un grafico de google
     * @param  $datos array con data con toda la información la procesar la pagina
     * @return string html generado
     */
    private function creaJsTabla($datos)
    {
        extract($datos);
        $tablajs = "google.charts.load('current', {'packages':['table']});";
        $tablajs .= "google.charts.setOnLoadCallback(draw_grafico_".$id_celda.");";
        $tablajs .= "function draw_grafico_".$id_celda."() { var data = new google.visualization.DataTable();";
        
        $columnas = str_replace("[", "", $columnas);
        $columnas = str_replace("]", "", $columnas);
        $columnas = explode(',', $columnas);
        $c = 0;

        $cantidad = count($arreglo_box);

        foreach ($arreglo_box as $key => $value) {

            if (!is_numeric($value)) {
                $tablajs .= "data.addColumn('string', '".$key."');";

            } elseif (is_numeric($value)) {
                $tablajs .= "data.addColumn('number', '".$key."');";

            } else {
                $tablajs .= "data.addColumn('boolean', '".$key."');";
            }

        }

        $tablajs .= "data.addRows([".$matriz."]);";
        $tablajs .= "var options = {showRowNumber: true, width: '100%', height: '100%'};
                    var table = new google.visualization.Table(document.getElementById('div_".$id_celda."'));
                    table.draw(data, options);}";

        return $tablajs;
    }

    /**
     * Genera el script js para crear un dashboard de google 
     * @param  $datos array con data con toda la información la procesar la pagina
     * @return string html generado
     */
    private function creaJsDashboard($datos)
    {
        extract($datos);
        $dashboardjs = "google.charts.load('current', {'packages':['corechart', 'controls']});";
        $dashboardjs .= "google.charts.setOnLoadCallback(draw_grafico_".$id_celda.");";
        $dashboardjs .= "function draw_grafico_".$id_celda."() {
                            var data = google.visualization.arrayToDataTable([".$columnas.",".$matriz."]);
                            var dashboard = new google.visualization.Dashboard(document.getElementById('div_".$id_celda."'));
                            var filtro = new google.visualization.ControlWrapper({
                                'controlType': '".$filter_type."',
                                'containerId': 'filter_div_".$id_celda."',
                                'options': {
                                'filterColumnLabel': '".$filter_column."'
                                }
                            });
                            var grafico = new google.visualization.ChartWrapper({
                                'chartType': '".$tipo_grafico."',
                                'containerId': 'div_".$id_celda."',
                                'options': {
                                    'width': '100%',
                                    'height': '100%',
                                    'pieSliceText': 'value',
                                    'legend': 'right'
                                }
                            });
                            dashboard.bind(filtro, grafico);
                            dashboard.draw(data);
                        }";
        return $dashboardjs;
    }

}
