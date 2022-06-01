<?php
namespace App\models;
defined("APPPATH") OR die("Access denied");

use \Core\Database;
use \Core\MasterDom;
use \App\interfaces\Crud;
use \App\controllers\UtileriasLog;

class ComprobantePago{

    public static function getAll($id){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT pro.nombre, pp.id_pendiente_pago,pp.status,pp.tipo_pago,pp.url_archivo
      FROM productos pro
      INNER JOIN pendiente_pago pp ON (pro.id_producto = pp.id_producto)
      WHERE pp.user_id = $id
sql;
      return $mysqli->queryAll($query);
    }

    public static function updateComprobante($data){
        $mysqli = Database::getInstance(true);
        // var_dump($user);
        $query=<<<sql
        UPDATE pendiente_pago SET url_archivo = :url_archivo  WHERE id_pendiente_pago = :id_pendiente_pago;
sql;
        $parametros = array(
          ':url_archivo'=>$data->_url,
          ':id_pendiente_pago'=>$data->_id_pendiente_pago
        );
       
        return $mysqli->update($query, $parametros);

    }

  
}