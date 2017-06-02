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
        
        // Afiliaciones
        $newUserD = $this->Monitor_db->getNewUser($this->get('idCommerce'), $date, $range);
        $newUser = $this->Monitor_db->getNewUserT_1M($this->get('idCommerce'), $date, $range)[0];
        
        // Puntos Otorgados
        $pointsD = $this->Monitor_db->getPoints($this->get('idCommerce'), $date, $range);
        $points = $this->Monitor_db->getPointsT_1M($this->get('idCommerce'), $date, $range)[0];
        
        // Redenciones
        $redemD = $this->Monitor_db->getRedem($this->get('idCommerce'), $date, $range);
        $redem = $this->Monitor_db->getRedemT_1M($this->get('idCommerce'), $date, $range)[0];
        
        // Metas
        if ($this->get('range') == '1S' || $this->get('range') == '1M'){ 
            $goalCom = $this->Monitor_db->getGoalCommerce($this->get('idCommerce')); 
            // Semanales
            if ($this->get('range') == '1S' && count($goalCom) > 0){ 
                $newUser->goal = $goalCom[0]->weekNewUser; 
                $points->goal = $goalCom[0]->weekPoints; 
                $redem->goal = $goalCom[0]->weekRedem; 
            } 
            // Mensuales
            if ($this->get('range') == '1M' && count($goalCom) > 0){ 
                $newUser->goal = $goalCom[0]->monthNewUser; 
                $points->goal = $goalCom[0]->monthPoints; 
                $redem->goal = $goalCom[0]->monthRedem; 
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




















