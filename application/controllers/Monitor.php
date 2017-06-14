<?php
defined('BASEPATH') OR exit('No direct script access allowed');

setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
require APPPATH.'/libraries/REST_Controller.php';
require APPPATH.'/libraries/BarcodeQR.php';

class Monitor extends REST_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->database('default');
        $this->load->model('Monitor_db');
    }

	public function index_get(){
    }

    /**
     * Autentifica al usuario
     */
    public function verifyUser_get(){
        $message = array('success' => false);
        // Consultar usuario
        $user = $this->Monitor_db->getCommerceUser($this->get('email'));
        // Validamos si existe por email
        if (count($user) > 0){
            $hash = $user[0]->password;
            $val = crypt($this->get('pass'), $hash) == $hash;
            $message = array('success' => $val, 'hash' => $hash, 'user' => $user[0]);
        }
        
        $this->response($message, 200);
    }
    
    /**
     * Consulta la lista de Sucursales
     */
    public function getBranchs_get(){
        // Consultar sucursales
        $items = $this->Monitor_db->getBranchs($this->get('idCommerce'));
        // Se envia informacion
        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }
    
    /**
     * Consulta la informacion
     */
    public function getData_get(){
        // Set date and grouptype
        $date = '';
        $range = $this->get('range');
        if ($this->get('range') == '1S'){   
            $date = date("Y-m-d", strtotime('monday this week'));
        }elseif ($this->get('range') == '1M'){    
            $date = date("Y-m-").'01';
        }elseif ($this->get('range') == '3M'){
            $date = date('Y-m-d', strtotime(date("Y-m-").'01 -2 months'));
        }else{ 
            $date = '2016-01-01';
        }
        
        // idBranch
        $idBranch = $this->get('idBranch');
        if ($idBranch == null){
            $idBranch = $this->Monitor_db->getCommerceBranchs($this->get('idCommerce'))[0]->idBranch;
        }
        
        // Afiliaciones
        $newUserD = $this->Monitor_db->getNewUserD($idBranch, $date, $range);
        $newUser = $this->Monitor_db->getNewUser($idBranch, $date, $range)[0];
        // Puntos Otorgados
        $pointsD = $this->Monitor_db->getPointsD($idBranch, $date, $range);
        $points = $this->Monitor_db->getPoints($idBranch, $date, $range)[0];
        // Redenciones
        $redemD = $this->Monitor_db->getRedemD($idBranch, $date, $range);
        $redem = $this->Monitor_db->getRedem($idBranch, $date, $range)[0];
        
        // Metas
        if ($this->get('range') == '1S' || $this->get('range') == '1M'){ 
            // Consultar metas
            $goals = $this->Monitor_db->getGoals($idBranch); 
            // Semanales
            if ($this->get('range') == '1S' && count($goals) > 0){ 
                $newUser->goal = $goals[0]->weekNewUser; 
                $points->goal = $goals[0]->weekPoints; 
                $redem->goal = $goals[0]->weekRedem; 
            } 
            // Mensuales
            if ($this->get('range') == '1M' && count($goals) > 0){ 
                $newUser->goal = $goals[0]->monthNewUser; 
                $points->goal = $goals[0]->monthPoints; 
                $redem->goal = $goals[0]->monthRedem; 
            }
        }
        
        // Dias de la semana
        if ($this->get('range') == '1S'){ 
            $dias = array('', 'Lunes', 'Martes','Miercoles','Jueves','Viernes','Sabado','Domingo');
            foreach ($newUserD as $item):
                $item->dateAction = $dias[$item->weekday];
            endforeach;
            foreach ($pointsD as $item):
                $item->dateAction = $dias[$item->weekday];
            endforeach;
            foreach ($redemD as $item):
                $item->dateAction = $dias[$item->weekday];
            endforeach;
        }
        
        // Retornamos valores
        $message = array('success' => true,
                         'newUser' => $newUser, 'newUserD' => $newUserD, 
                         'points' => $points, 'pointsD' => $pointsD, 
                         'redem' => $redem, 'redemD' => $redemD);
        $this->response($message, 200);
    }


    
}




















