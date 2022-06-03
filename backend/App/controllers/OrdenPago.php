<?php

namespace App\controllers;

defined("APPPATH") or die("Access denied");
require_once dirname(__DIR__) . '/../public/librerias/fpdf/fpdf.php';

use \Core\View;
use \Core\Controller;
use \App\models\Talleres as TalleresDao;
use \App\models\Transmision as TransmisionDao;
use \App\models\Register as RegisterDao;
use \App\models\Home as HomeDao;

class OrdenPago extends Controller
{

    private $_contenedor;

    function __construct()
    {
        parent::__construct();
        $this->_contenedor = new Contenedor;
        View::set('header', $this->_contenedor->header());
        View::set('footer', $this->_contenedor->footer());
    }

    public function getUsuario()
    {
        return $this->__usuario;
    }


    public function Pagar(){
        echo $_POST['costo'];
    }
    
    public function ticketAll($clave = null, $id_curso = null)
    {
        date_default_timezone_set('America/Mexico_City');

        
        $metodo_pago = $_POST['metodo_pago'];
        $user_id = $_SESSION['user_id'];
        $clave = $this->generateRandomString();
        $datos_user = RegisterDao::getUser($this->getUsuario())[0];

        $productos = TalleresDao::getCarritoByIdUser($user_id);

        // var_dump($productos);
        // exit;



        foreach($productos as $key => $value){

            if($value['es_congreso'] == 1){
                $precio = $value['amout_due'];
            }else if($value['es_servicio'] == 1){
                $precio = $value['precio_publico'];
            }else if($value['es_curso'] == 1){
                $precio = $value['precio_publico'];
            }
           
            $documento = new \stdClass();  

            $nombre_curso = $value['nombre'];
            $id_producto = $value['id_producto'];
            $user_id = $datos_user['user_id'];
            $reference = $datos_user['reference'];
            $fecha =  date("Y-m-d");
            // $monto = $value['precio_publico'];
            $monto = $precio;
            $tipo_pago = $metodo_pago;
            $status = 0;
    
            $documento->_id_producto = $id_producto;
            $documento->_user_id = $user_id;
            $documento->_reference = $reference;
            $documento->_fecha = $fecha;
            $documento->_monto = $monto;
            $documento->_tipo_pago = $tipo_pago;
            $documento->_clave = $clave;
            $documento->_status = $status;

            $id = TalleresDao::inserPendientePago($documento);
            $delete = TalleresDao::deleteItem($value['id_carrito']);

        }



        $d = $this->fechaCastellano($fecha);
        
        $nombre_completo = $datos_user['name_user'] . " " . $datos_user['middle_name'] . " " . $datos_user['surname'] . " " . $datos_user['second_surname'];


        $pdf = new \FPDF($orientation = 'P', $unit = 'mm', $format = 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 8);    //Letra Arial, negrita (Bold), tam. 20
        $pdf->setY(1);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Image('constancias/plantillas/orden.jpeg', 0, 0, 200, 300);
        // $pdf->SetFont('Arial', 'B', 25);
        // $pdf->Multicell(133, 80, $clave_ticket, 0, 'C');

        $espace = 125;
        $total = array();
        foreach($productos as $key => $value){            
            
            
            if($value['es_congreso'] == 1){
                $precio = $value['amout_due'];
            }else if($value['es_servicio'] == 1){
                $precio = $value['precio_publico'];
            }else if($value['es_curso'] == 1){
                $precio = $value['precio_publico'];
            }

            array_push($total,$precio);

            //Nombre Curso
            $pdf->SetXY(12, $espace);
            $pdf->SetFont('Arial', 'B', 8);  
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Multicell(100, 4, utf8_decode($value['nombre']), 0, 'C');

            //Costo
            $pdf->SetXY(118, $espace);
            $pdf->SetFont('Arial', 'B', 8);  
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Multicell(100, 4, '$ '.$precio.' ' .$value['tipo_moneda'], 0, 'C');

            $espace = $espace + 8;
        }

        //folio
        $pdf->SetXY(118, 42.5);
        $pdf->SetFont('Arial', 'B', 13);  
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, $reference, 0, 'C');

        //fecha
        $pdf->SetXY(118, 51.5);
        $pdf->SetFont('Arial', 'B', 13);  
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, $fecha, 0, 'C');

        //total
        // $pdf->SetXY(118, 170);
        // $pdf->SetFont('Arial', 'B', 8);  
        // $pdf->SetTextColor(0, 0, 0);
        // $pdf->Multicell(100, 10, 'TOTAL : '.number_format(array_sum($total),2), 0, 'C');

        $pdf->Output();
        // $pdf->Output('F','constancias/'.$clave.$id_curso.'.pdf');

        // $pdf->Output('F', 'C:/pases_abordar/'. $clave.'.pdf');
    }
    

    public function ordenPago($clave = null, $id_curso = null)
    {
        date_default_timezone_set('America/Mexico_City');

        // $this->generaterQr($clave_ticket);

        $datos_user = RegisterDao::getUser($this->getUsuario())[0];
        $clave = $this->generateRandomString();

        $documento = new \stdClass();  

        $nombre_curso = $_POST['nombre_curso'];
        $id_producto = $_POST['id_producto'];
        $user_id = $datos_user['user_id'];
        $reference = $datos_user['reference'];
        $fecha =  date("Y-m-d");
        $monto = $_POST['costo'];
        $tipo_pago = $_POST['tipo_pago'];
        $status = 0;

        $documento->_id_producto = $id_producto;
        $documento->_user_id = $user_id;
        $documento->_reference = $reference;
        $documento->_clave = $clave;
        $documento->_fecha = $fecha;
        $documento->_monto = $monto;
        $documento->_tipo_pago = $tipo_pago;
        $documento->_status = $status;

        $d = $this->fechaCastellano($fecha);

        // var_dump($documento);
        // exit;

        $id = TalleresDao::inserPendientePago($documento);       


        $nombre_completo = $datos_user['name_user'] . " " . $datos_user['middle_name'] . " " . $datos_user['surname'] . " " . $datos_user['second_surname'];


        $pdf = new \FPDF($orientation = 'P', $unit = 'mm', $format = 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 8);    //Letra Arial, negrita (Bold), tam. 20
        $pdf->setY(1);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Image('constancias/plantillas/orden.jpeg', 0, 0, 200, 300);
        // $pdf->SetFont('Arial', 'B', 25);
        // $pdf->Multicell(133, 80, $clave_ticket, 0, 'C');

        //$pdf->Image('1.png', 1, 0, 190, 190);
        $pdf->SetFont('Arial', 'B', 5);    //Letra Arial, negrita (Bold), tam. 20
        //$nombre = utf8_decode("Jonathan Valdez Martinez");
        //$num_linea =utf8_decode("Línea: 39");
        //$num_linea2 =utf8_decode("Línea: 39");

        //Nombre Curso
        $pdf->SetXY(12, 125);
        $pdf->SetFont('Arial', 'B', 8);  
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, utf8_decode($nombre_curso), 0, 'C');

        //Costo
        $pdf->SetXY(118, 125);
        $pdf->SetFont('Arial', 'B', 8);  
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, '$ '.$monto, 0, 'C');

        //folio
        $pdf->SetXY(118, 42.5);
        $pdf->SetFont('Arial', 'B', 13);  
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, $reference, 0, 'C');

        //fecha
        $pdf->SetXY(118, 51.5);
        $pdf->SetFont('Arial', 'B', 13);  
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, $fecha, 0, 'C');


        $pdf->Output();
        // $pdf->Output('F','constancias/'.$clave.$id_curso.'.pdf');

        // $pdf->Output('F', 'C:/pases_abordar/'. $clave.'.pdf');
    }

    public function impticket($user_id = null, $id_producto = null)
    {
        date_default_timezone_set('America/Mexico_City');

        // $this->generaterQr($clave_ticket);

        $user_id = base64_decode($user_id);
        $id_producto = base64_decode($id_producto);

        $datos_user = RegisterDao::getUser($this->getUsuario())[0];
        $datos_pendiente_pago = TalleresDao::getProductosPendientesPagoTicket($user_id,$id_producto)[0];



        $documento = new \stdClass();  

        $nombre_curso = $datos_pendiente_pago['nombre'];
        $id_producto = $_POST['id_producto'];
        $user_id = $datos_pendiente_pago['user_id'];
        $reference = $datos_pendiente_pago['reference'];
        $fecha =  $datos_pendiente_pago['fecha'];
        $monto = $datos_pendiente_pago['monto'];
        $tipo_pago = $datos_pendiente_pago['tipo_pago'];
     


        $nombre_completo = $datos_user['name_user'] . " " . $datos_user['middle_name'] . " " . $datos_user['surname'] . " " . $datos_user['second_surname'];


        $pdf = new \FPDF($orientation = 'P', $unit = 'mm', $format = 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 8);    //Letra Arial, negrita (Bold), tam. 20
        $pdf->setY(1);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Image('constancias/plantillas/orden.jpeg', 0, 0, 200, 300);
        // $pdf->SetFont('Arial', 'B', 25);
        // $pdf->Multicell(133, 80, $clave_ticket, 0, 'C');

        //$pdf->Image('1.png', 1, 0, 190, 190);
        $pdf->SetFont('Arial', 'B', 5);    //Letra Arial, negrita (Bold), tam. 20
        //$nombre = utf8_decode("Jonathan Valdez Martinez");
        //$num_linea =utf8_decode("Línea: 39");
        //$num_linea2 =utf8_decode("Línea: 39");

        //Nombre Curso
        $pdf->SetXY(12, 125);
        $pdf->SetFont('Arial', 'B', 13);  
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, utf8_decode($nombre_curso), 0, 'C');

        //Costo
        $pdf->SetXY(118, 125);
        $pdf->SetFont('Arial', 'B', 13);  
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, '$ '.$monto, 0, 'C');

        //folio
        $pdf->SetXY(118, 42.5);
        $pdf->SetFont('Arial', 'B', 13);  
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, $reference, 0, 'C');

        //fecha
        $pdf->SetXY(118, 51.5);
        $pdf->SetFont('Arial', 'B', 13);  
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, $fecha, 0, 'C');


        $pdf->Output();
        // $pdf->Output('F','constancias/'.$clave.$id_curso.'.pdf');

        // $pdf->Output('F', 'C:/pases_abordar/'. $clave.'.pdf');
    }


    public function PagarPaypal($clave = null, $id_curso = null)
    {
        date_default_timezone_set('America/Mexico_City');

        // $this->generaterQr($clave_ticket);

        $datos_user = RegisterDao::getUser($this->getUsuario())[0];

        $documento = new \stdClass();

        $nombre_curso = $_POST['nombre_curso'];
        $id_producto = $_POST['id_producto'];
        $user_id = $datos_user['user_id'];
        $reference = $datos_user['reference'];
        $fecha =  date("Y-m-d");
        $monto = $_POST['costo'];
        $tipo_pago = $_POST['tipo_pago'];
        $status = 0;

        $documento->_id_producto = $id_producto;
        $documento->_user_id = $user_id;
        $documento->_reference = $reference;
        $documento->_fecha = $fecha;
        $documento->_monto = $monto;
        $documento->_tipo_pago = $tipo_pago;
        $documento->_status = $status;

        $d = $this->fechaCastellano($fecha);

        // var_dump($documento);
        // exit;

        // $id = TalleresDao::inserPendientePago($documento);


        $nombre_completo = $datos_user['name_user'] . " " . $datos_user['middle_name'] . " " . $datos_user['surname'] . " " . $datos_user['second_surname'];


        $pdf = new \FPDF($orientation = 'P', $unit = 'mm', $format = 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 8);    //Letra Arial, negrita (Bold), tam. 20
        $pdf->setY(1);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Image('constancias/plantillas/orden.jpeg', 0, 0, 200, 300);
        // $pdf->SetFont('Arial', 'B', 25);
        // $pdf->Multicell(133, 80, $clave_ticket, 0, 'C');

        //$pdf->Image('1.png', 1, 0, 190, 190);
        $pdf->SetFont('Arial', 'B', 5);    //Letra Arial, negrita (Bold), tam. 20
        //$nombre = utf8_decode("Jonathan Valdez Martinez");
        //$num_linea =utf8_decode("Línea: 39");
        //$num_linea2 =utf8_decode("Línea: 39");

        //Nombre Curso
        $pdf->SetXY(12, 125);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, utf8_decode($nombre_curso), 0, 'C');

        //Costo
        $pdf->SetXY(118, 125);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, '$ '.$monto, 0, 'C');

        //folio
        $pdf->SetXY(118, 42.5);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, $reference, 0, 'C');

        //fecha
        $pdf->SetXY(118, 51.5);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, $fecha, 0, 'C');


        $pdf->Output();
        // $pdf->Output('F','constancias/'.$clave.$id_curso.'.pdf');

        // $pdf->Output('F', 'C:/pases_abordar/'. $clave.'.pdf');
    }

    function fechaCastellano ($fecha) {
        $fecha = substr($fecha, 0, 10);
        $numeroDia = date('d', strtotime($fecha));
        $dia = date('l', strtotime($fecha));
        $mes = date('F', strtotime($fecha));
        $anio = date('Y', strtotime($fecha));

        $dias_ES = array("Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo");
        $dias_EN = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
        $nombredia = str_replace($dias_EN, $dias_ES, $dia);
        $meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
        $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
        $nombreMes = str_replace($meses_EN, $meses_ES, $mes);

        return $nombredia." ".$numeroDia." de ".$nombreMes." de ".$anio;
    }

    function generateRandomString($length = 10)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

}
