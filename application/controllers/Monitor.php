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
        // Set date
        $date = date("Y-m-").'01';
        if ($this->get('range') == '1M'){    
            $date = date("Y-m-").'01';
        
        }elseif ($this->get('range') == '3M'){    
            $date = date('Y-m-d', strtotime(date("Y-m-").'01 -2 months'));
            echo $date;
        }
        
        // Afiliaciones
        $newUserD = $this->Monitor_db->getNewUser($this->get('idCommerce'), $date);
        $newUser = $this->Monitor_db->getNewUserT_1M($this->get('idCommerce'), $date)[0];
        $newUser->goal = 1250; 
        
        // Puntos Otorgados
        $pointsD = $this->Monitor_db->getPoints($this->get('idCommerce'), $date);
        $points = $this->Monitor_db->getPointsT_1M($this->get('idCommerce'), $date)[0];
        $points->goal = 12000; 
        
        // Redenciones
        $redemD = $this->Monitor_db->getRedem($this->get('idCommerce'), $date);
        $redem = $this->Monitor_db->getRedemT_1M($this->get('idCommerce'), $date)[0];
        $redem->goal = 1000; 
        
        // Retornamos valores
        $message = array('success' => true, 
                         'newUser' => $newUser, 'newUserD' => $newUserD, 
                         'points' => $points, 'pointsD' => $pointsD, 
                         'redem' => $redem, 'redemD' => $redemD);
        //$this->response($message, 200);
    }


    
}




















