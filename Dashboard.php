<?php 
/**
 * Descripcion Dashboard
 * @package Reporteria
 * @filesource Dashboard.php
 */

namespace Reporteria;

include(__DIR__.'/../Motor/Central.php');

/**
 * Descripcion Dashboard
 * @author  Patricio González Leal
 * @version 0.0.0
 * @license GPL
 * @package Reporteria
 */
class Dashboard extends \Core\BaseModuloHoja
{
    /**
     * Constructor
     * @see \Core\BaseModuloHoja::__construct()
     */
    function __construct()
    {
        $lang = new \Core\TextConfig();
        if(is_file( __DIR__ ."/".$_SESSION['config']['base']['lang'] . ".txt")){
            $lang->setFile( __DIR__ ."/".$_SESSION['config']['base']['lang'] . ".txt");
            $this->lang = $lang->getList();
        } elseif (is_file( __DIR__ ."/es.txt")) {
            $lang->getFile( __DIR__ ."/es.txt");
            $this->lang = $lang->getList();
        } else {
            die('No existe archivo de idioma en <strong>' .__CLASS__. '->' .__FUNCTION__. '()</strong> ubicado en : ' .__DIR__);
        }
        parent::__construct();
        $this->path = str_replace($_SESSION['config']['base']['path'].'/','',__DIR__);
        $this->addFoot(\Core\Html::scriptjs('templates/unificado/js/validacionesGenericas.js'));
        $this->addFoot(\Core\Html::scriptjs('templates/unificado/js/ValidaMe.js'));
        $this->addFoot(\Core\Html::scriptjs($this->path.'/templates/js/js.js'));

    }

    /**
     * metodo de procesamiento principal del modulo
     * @param array $Pdata datos llegados por POST limpiados
     * @param array $Gdata datos llegados por GET limpiados
     * @param object $conexion_sql objeto de conexion a la base de datos
     * @param Usuario $user objeto del tipo usuario
     * @return codigo resultante de procesar
     */
    public function process($Pdata, $Gdata, $conexion_sql, $user){
        if (!isset($Pdata['accion'])) {
            $funcion = 'getDefault';
        } else {
            $funcion = $this->pasarACamelCase(explode('_', $Pdata['accion']));
        }
        array_push($Pdata, $_SESSION['usuario']['rut'], $funcion);
        $this->code = $this->$funcion($Pdata, $user);
        return $this->code;
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

    public function cargarBase($titulo)
    {
        $data['###titulo###'] = $titulo;
        $data['###contenido###'] = "";
        $data['###menu###'] = "";

        $data['###id_box###'] = 0;
        $data['###id_dashboard###'] = 0;

        $data['###tipo_grafico###'] = $this->creaOptionTipoGrafico();

        $data['###perfiles###'] = $this->creaOptionPerfil();
        $data['###ambitos###'] = $this->creaOptionAmbito();

        $data['###dashboards###'] = $this->desplegarDashboards();
        $data['###boxes###'] = $this->desplegarBoxes();

        $ruta = '/templates/html/base.html';
        return $this->parseHTML($ruta, $data);
    }

    /**
     * Guarda un registro
     * @param  string $tabla    tabla objetivo
     * @param  array $datos    datos a insertar
     * @param  array $mensajes array de mensaje de exito y error a disparar
     * @return array           respuesta de la operacion
     */
    public function guardarRegistro($tabla, $datos, $mensajes)
    {
        $respuesta = array(
            'estado' => true,
            );
        try {
            $id_registro = $this->insertarEnBd($tabla, $datos, $mensajes);
            $respuesta['mensaje'] = \Core\Html::exito($mensajes['exito']);
            $respuesta['id_registro'] = $id_registro;
            $respuesta['estado'] = true;
        } catch (\Exception $e) {
            $respuesta['estado'] = false;
            $respuesta['mensaje'] = \Core\Html::alerta($e->getMessage());
        }

        return $respuesta;
    }

    /**
     * Inserta el registro en base de datos
     * @param  string $tabla    tabla objetico
     * @param  array $datos    datos a insertar
     * @param  array $mensajes mensaje de exito y error
     * @return mixed           captura un error o devuelve el id del registro
     */
    private function insertarEnBd($tabla, $datos, $mensajes)
    {
        $tabla = new \Core\MiTabla($tabla);
        if (!$id_registro = $tabla->insertaRegistro($datos, false)) {
            throw new \Exception($mensajes['error'], 1);
        }

        return $id_registro;
    }

    /**
     * Actualiza un registro
     * @param  string $tabla    tabla objetivo
     * @param  array $datos    datos a actualizar
     * @param  array $mensajes mensajes de exito y error
     * @param  array $filtro   filtro de la actualizacion
     * @return array           respuesta la operacion
     */
    public function actualizarRegistro($tabla, $datos, $mensajes, $filtro)
    {
        $respuesta = array(
            'estado' => true,
            );
        try {
            $this->actualizarEnBd($tabla, $datos, $mensajes, $filtro);
            $respuesta['mensaje'] = \Core\Html::exito($mensajes['exito']);
            $respuesta['estado'] = true;
        } catch (\Exception $e) {
            $respuesta['estado'] = false;
            $respuesta['mensaje'] = \Core\Html::alerta($e->getMessage());
        }
        return $respuesta;
    }

    /**
     * Realiza el proceso de actualizacion en la base de datos
     * @param  string $tabla    tabla objetivo
     * @param  array $datos    arreglo de datos a actualizar
     * @param  array $mensajes listado de mensajes de exito y error
     * @param  array $filtro   filtro de la actualizacion
     * @return mixed           captura el error en caso de fallo
     */
    private function actualizarEnBd($tabla, $datos, $mensajes, $filtro)
    {
        $tabla = new \Core\MiTabla($tabla);
        if (!$tabla->actualizarPorCampo($filtro['campo'], $filtro['valor'], $datos)) {
            throw new \Exception($mensajes['error'], 1);
        }
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
     * Ejecuta las query de delete en el formulario del dashboard
     * @param  sql tipo string Query
     * @return array devuelve un arreglo con la respuesta desde la bd
     */
    protected function ejecutarDelete($sql){ 
        $conf = $_SESSION['config']['base'];
        $pass = \Core\Encriptador::desencriptar($conf['dbclaveAll']);
        $obd = new \Core\DataBase($conf['dbhost'], $conf['dbport'], $conf['dbuserAll'], $pass, $conf['dbdatabase']);
        $datos = $obd->delete($sql);
        if(!empty($datos)){
            return $datos;
        }
        return 0;
    }

    /**
     * Pregunta si un arreglo tiene o no elementos
     * @param  array $elemento arreglo a contar
     * @return boolean           true si existe, false en caso contrario
     */
    public function existe($elemento)
    {
        $numero_elementos = count($elemento);
        if ($numero_elementos>0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Inserta la frase top 10 en cualquier sql para previsualizar
     * @param  string $query   query que se desea consultar
     * @return mixed           query con el string insertado
     */
    private function topEnSql($query){
        $sql = mb_strtolower(trim($query));
        $substr = 'select';
        $attachment = ' top 10 ';
        return str_replace($substr, $substr.$attachment, $sql);
    }

    /**
     * Inserta la frase top 10 en cualquier sql para previsualizar
     * @param  string $query   query que se desea consultar
     * @return mixed           query con el string insertado
     */
    private function revisaFiltro($query, $region, $area, $programa){
        $sql = mb_strtolower(trim($query));
        $substr = '{region}';
        $attachment = $region;
        $sql = str_replace($substr, $attachment, $sql);
        $substr = '{area}';
        $attachment = $area;
        $sql = str_replace($substr, $attachment, $sql);
        $substr = '{programa}';
        $attachment = $programa;
        $sql = str_replace($substr, $attachment, $sql);
        return $sql;
    }

    /**
     * Genera los tr para desplegar los dashboard generados
     * @param  
     * @return string html generado
     */
    private function desplegarDashboards(){
        $sql = '
                SELECT 
                    dashboard.id_dashboard, 
                    dashboard.nombre, 
                    dashboard.menu, 
                    dashboard.id_perfil, 
                    dashboard.id_tipo_ambito,
                    dashboard.icono, 
                    dashboard.activo,
                    ambito.tipo as ambito,
                    perfil.nombre as perfil
                FROM dashboard.dashboard as dashboard
                INNER JOIN core.tipo_ambito as ambito 
                    on (ambito.id_tipo_ambito = dashboard.id_tipo_ambito)
                INNER JOIN core.perfil as perfil
                    on (perfil.id_perfil = dashboard.id_perfil)
                WHERE dashboard.activo = 1;';
        $respuesta = $this->ejecutarSelect($sql);
        $tr = '';
        foreach ($respuesta as $temp) {
            if ($temp['activo'] == 1) {
                $activo = '<i class="fa fa-check-circle-o" aria-hidden="true"></i>';
            } else {
                $activo = '<i class="fa fa-times" aria-hidden="true"></i>';
            }
            $tr .= '<tr>
                            <td>1</td>
                            <td>'.$temp['nombre'].'</td>
                            <td>'.$temp['menu'].'</td>
                            <td>'.$temp['perfil'].'</td>
                            <td>'.$temp['ambito'].'</td>
                            <td><a href="#" class="ver_un_dashboard" data-id="'.$temp['id_dashboard'].'"><i class="fa fa-file-text" aria-hidden="true"></i></a></td>
                            <td><i class="fa fa-pencil-square-o" aria-hidden="true"></i></td>
                        </tr>';
        }
        return $tr;

    }

    /**
     * Genera los tr para desplegar los boxes generados
     * @param  
     * @return string html generado
     */
    private function desplegarBoxes(){
        $sql = '
                SELECT box.id_box,
                        box.id_tipo_grafico,
                        tipo_grafico.nombre,
                        box.titulo,
                        box.eje_x,
                        box.eje_y,
                        box.activo
                FROM dashboard.box as box
                INNER JOIN dashboard.tipo_grafico as tipo_grafico
                    on tipo_grafico.id_tipo_grafico = box.id_tipo_grafico
                WHERE box.activo = 1;';
        $respuesta = $this->ejecutarSelect($sql);
        $tr = '';
        foreach ($respuesta as $temp) {
            if ($temp['activo'] == 1) {
                $activo = '<i class="fa fa-check-circle-o" aria-hidden="true"></i>';
            } else {
                $activo = '<i class="fa fa-times" aria-hidden="true"></i>';
            }
            $tr .= '<tr>
                            <td>1</td>
                            <td>'.$temp['nombre'].'</td>
                            <td>'.$temp['titulo'].'</td>
                            <td>'.$temp['eje_x'].'</td>
                            <td>'.$temp['eje_y'].'</td>
                            <td><a href="#" class="ver_un_box" data-id="'.$temp['id_box'].'"><i class="fa fa-file-text" aria-hidden="true"></i></a></td>
                            <td><i class="fa fa-pencil-square-o" aria-hidden="true"></i></td>
                        </tr>';
        }
        return $tr;

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
                    dashboard.icono,
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

        for ($i = 0; $i < $total_datos; $i++) {

            extract($datos[$i]);
            $id_celda = uniqid();
            $query = base64_decode( $query );
            $query = $this->revisaFiltro($query, 09, 00, 11);
            $arreglo_box = $this->ejecutarSelect(stripslashes($query));
            $columnas = stripslashes($columnas);
            $rand_background = $background_colors[array_rand($background_colors)];

            $celda .= '<div class="large-'.$columna.' columns" style="border-top: solid 4px '.$rand_background.'"><h4>'.$titulo_box.'</h4><div id="div_'.$id_celda.'"></div></div>'; 

            if (!isset($datos[$i+1]['linea'])) {
                $row .= '<div class="row">'.$celda.'</div>';   
                $celda = ''; 
            } else {
                if ($datos[$i+1]['linea'] != $linea) {
                    $row .= '<div class="row">'.$celda.'</div>';   
                    $celda = ''; 
                }
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

            if ($tipo_grafico == 'Table') {
                $data['###scriptjs###'] .= $this->creaJsTabla($id_celda, $columnas, $matriz, $eje_x, $eje_y, $tipo_grafico, $arreglo_box[0]);
            } else {
                $data['###scriptjs###'] .= $this->creaJsGrafico($id_celda, $columnas, $matriz, $eje_x, $eje_y, $tipo_grafico);
            }

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
    private function creaJsGrafico($id_celda, $columnas, $matriz, $eje_x, $eje_y, $tipo_grafico)
    {
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
    private function creaJsTabla($id_celda, $columnas, $matriz, $eje_x, $eje_y, $tipo_grafico, $arreglo_box)
    {
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
     * Crea los option para el select de tipo de grafico
     * @param  
     * @return string  devuelve los option con los tipos de graficos
     */
    private function creaOptionTipoGrafico(){
        $sql = "SELECT id_tipo_grafico, nombre, codigo, activo
                FROM dashboard.tipo_grafico
                WHERE activo = 1
                ORDER BY nombre;";
        $respuesta = $this->ejecutarSelect($sql);
        $option = '';
        foreach ($respuesta as $temp) {
            $option .= '<option data-id="'.$temp['id_tipo_grafico'].'" value="'.$temp['codigo'].'">'.$temp['nombre'].'</option>';
        }
        return $option;

    }

    /**
     * Crea los option para el select de tipo de grafico
     * @param  
     * @return string  devuelve los option con los tipos de graficos
     */
    private function creaOptionPerfil(){
        $sql = "SELECT id_perfil, nombre
                FROM sistema_integrado.core.perfil
                ORDER BY nombre;";
        $respuesta = $this->ejecutarSelect($sql);
        $option = '';
        foreach ($respuesta as $temp) {
            $option .= '<option value="'.$temp['id_perfil'].'">'.$temp['nombre'].'</option>';
        }
        return $option;

    }

    /**
     * Crea los option para el select de tipo de grafico
     * @param  
     * @return string  devuelve los option con los tipos de graficos
     */
    private function creaOptionAmbito(){
        $sql = "SELECT id_tipo_ambito, tipo
                FROM sistema_integrado.core.tipo_ambito
                WHERE id_tipo_ambito IN (1,2,4)
                ORDER BY tipo;";
        $respuesta = $this->ejecutarSelect($sql);
        $option = '';
        foreach ($respuesta as $temp) {
            $option .= '<option value="'.$temp['id_tipo_ambito'].'">'.$temp['tipo'].'</option>';
        }
        return $option;

    }


    /**
     * Crea una tabla a partir de un arreglo
     * @param  array $arreglo  arreglo desde consulta sql
     * @return array           string con tabla creada
     */
    protected function crearTabla($arreglo){
        $data = array();
        if (count($arreglo) > 0):
            $data['###head###'] = implode('</th><th>', array_keys(current($arreglo)));
            $data['###body###'] = '';
            foreach ($arreglo as $row): 
                array_map('htmlentities', $row);
                $data['###tbody###'] .= "<tr><td>";
                $data['###tbody###'] .= implode('</td><td>', $row);
                $data['###tbody###'] .= "</td></tr>";
            endforeach;
        endif;
        $ruta = '/templates/html/table.html';
        return $this->parseHTML($ruta, $data);
    }


    /**
    * Metodo que carga un dashboard por defecto segun lso permisos he información a la que tenga acceso
    * @return string codigo generado
    */
    private function getDefault($Pdata, $user)
    {
        return $this->cargarBase("Dashboard Builder");
    }


    /**
     * Metodo que ejecuta la query que se consulta desde el formulario
     * @param  array $user     arreglo que contiene la info del usuario
     * @param  array $Pdata    arreglo con el post
     * @return array           devuelve un json con la respuesta obtenida de la bd
     */
    private function ejecutarQuery($Pdata, $user)
    {
        extract($Pdata);
        $sql = base64_decode($query);
        $sql = $this->topEnSql($sql);
        $sql = $this->revisaFiltro($sql, $region, $area, $programa);
        $respuesta = $this->ejecutarSelect($sql);
        $arreglo = json_encode($respuesta);
        return $arreglo;
    }

    /**
     * Guarda un box
     * @param  array $Pdata
     * @return string json con respuesta
     */
    public function crearDashboard($Pdata)
    {
        extract($Pdata);
        $tabla = "dashboard.dashboard";    
     
        $datos = array(
            'nombre' => $nombre,
            'menu' => $menu,
            'id_perfil' => $perfil,
            'id_tipo_ambito' => $ambito,
            'icono' => $icono,
            'activo' => 1
        );

        if ($id_dashboard == 0) {
            $mensaje = array('exito'=>"Se ha creado un nuevo dashboard", 'error'=>"Hubo un problema al registrar el dashboard"); 
            $id_dashboard = $this->guardarRegistro($tabla, $datos, $mensaje);
        } else {
            $filtro = array('campo'=>'id_dashboard', 
                            'valor'=>trim($id_dashboard)
                        );
            $mensaje = array('error'=>"Hubo un problema al actualizar el dashboard", 'exito'=>"Se ha actualizado el dashboard");  
            $id_dashboard = $this->actualizarRegistro($tabla, $datos, $mensaje, $filtro);
        }
        
        return json_encode($id_dashboard);

    }

    /**
     * Guarda un box
     * @param  array $Pdata
     * @return string json con respuesta
     */
    public function guardarBox($Pdata)
    {
        extract($Pdata);
        $tabla = "dashboard.box";    
             
        $datos = array(
            'query' => $query,
            'id_tipo_grafico' => $id_tipo_grafico,
            'titulo' => $titulo,
            'eje_x' => $eje_x,
            'eje_y' => $eje_y,
            'columnas' => addslashes(json_encode($columnas)),
            'activo' => 1,
            'filtro_columna' => $filtro_columna,
            'tipo_control_grafico' => $tipo_control_grafico,
            'control_grafico' => $control_grafico,
        );
        if ($id_box == 0) {
            $mensaje = array('error'=>"Hubo un problema al registrar el box"); 
            $id_box = $this->guardarRegistro($tabla, $datos, $mensajes);
        } else {
            $filtro = array('campo'=>'id_box', 
                            'valor'=>trim($id_box)
                        );
            $mensaje = array('error'=>"Hubo un problema al actualizar el box"); 
            $id_box = $this->actualizarRegistro($tabla, $datos, $mensaje, $filtro);
        }
        
        return json_encode($id_box);

    }

    /**
     * Crea una linea con una cantidad de columnas determinada
     * @param  array $Pdata
     * @return string html generado
     */
    public function crearColumnas($Pdata)
    {
        extract($Pdata);
        $col = $columnas;
        if ($columnas == 21 || $columnas == 12) {
            $col = 2;
        }
        if ($columnas == 1) {
            $columns = 12;
        } else if ($columnas == 2) {
            $columns = 6;
        } else {
            $columns = 4;
        }

        $sql = "SELECT id_box, titulo
                FROM sistema_integrado.dashboard.box 
                WHERE activo = 1;";
        $boxes = $this->ejecutarSelect($sql);
        $select = '';
        foreach ($boxes as $temp) {
            $opciones .= \Core\Html::option($temp['titulo'], $temp['id_box'], '','');
        }
        
        $select = '<select name="listado_cuotas" id="listado_cuotas">'.$opciones.'</select>';

        $html = '';
        for ($i = 1; $i <= $col; $i++) {
            if ($columnas == 21){
                if ($i==1) { $columns = 8; } else { $columns = 4; }
            } 
            if ($columnas == 12){
                if ($i==1) { $columns = 4; } else { $columns = 8; }
            } 
            $elemento = '<label>Columna '.$i.$select.'</label>';
            $html .= $this->crearColumnaHtml($elemento, $columns);
        }
        return $html;

    }

    /**
     * Crea una una columna large-12
     * @param  string elemento dentro de la columa
     * @return string html generado
     */
    public function crearColumnaHtml($elemento, $i)
    {
        return '<div class="large-'.$i.' columns celda" data-columna="'.$i.'">'.$elemento.'</div>';
    }


    /**
     * Crea una una columna large-12
     * @param  string elemento dentro de la columa
     * @return string html generado
     */
    public function guardarGrilla($Pdata)
    {
        extract($Pdata);

        $tabla = "dashboard.dashboard_box";   

        $id_dashboard = $dashboard;
        $activo = 1;
        $sql_delete = 'DELETE FROM dashboard.dashboard_box WHERE id_dashboard='.$id_dashboard.';';
        $delete = $this->ejecutarDelete($sql_delete);

        $arreglo = json_decode($datos);

        $sql_insert = '';

        foreach ($arreglo as $linea => $lineas) {

            foreach ($lineas as $temp) {

                $columna = $temp[0];
                $id_box = $temp[1];  
                
                $sql_insert .= 'INSERT INTO dashboard.dashboard_box (id_box,id_dashboard,linea,columna,activo) VALUES ('.$id_box.','.$id_dashboard.','.$linea.','.$columna.','.$activo.');';
                
            }

        }

        $insercion = $this->ejecutarSelect($sql_insert);

        $mensaje = array(
            'delete'=>$delete,
            'insert'=>$insercion
        );

        return json_encode($mensaje);
    }

    

}
