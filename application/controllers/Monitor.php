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
        if ($this->get('range') == '1S' || $this->get('range') == '1M'){ 
            $newUser->goal = 1250; 
        }
        
        // Puntos Otorgados
        $pointsD = $this->Monitor_db->getPoints($this->get('idCommerce'), $date, $range);
        $points = $this->Monitor_db->getPointsT_1M($this->get('idCommerce'), $date, $range)[0];
        if ($this->get('range') == '1S' || $this->get('range') == '1M'){ 
            $points->goal = 12500; 
        }
        
        // Redenciones
        $redemD = $this->Monitor_db->getRedem($this->get('idCommerce'), $date, $range);
        $redem = $this->Monitor_db->getRedemT_1M($this->get('idCommerce'), $date, $range)[0];
        if ($this->get('range') == '1S' || $this->get('range') == '1M'){ 
            $redem->goal = 1250; 
        }
        
        // Retornamos valores
        $message = array('success' => true, 
                         'newUser' => $newUser, 'newUserD' => $newUserD, 
                         'points' => $points, 'pointsD' => $pointsD, 
                         'redem' => $redem, 'redemD' => $redemD);
        $this->response($message, 200);
    }


    
}




















