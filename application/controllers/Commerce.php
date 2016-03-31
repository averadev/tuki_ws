<?php
defined('BASEPATH') OR exit('No direct script access allowed');

setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
require APPPATH.'/libraries/REST_Controller.php';

class Commerce extends REST_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->database('default');
        $this->load->model('Commerce_db');
    }

	public function index_get(){
    }
    
	/**------------------------------ UNIFY COMMERCE ------------------------------**/
    
	/**
     * Obtiene el usuario
     */
    public function getUser_get(){
        $success = true;
        // Get User
        $user = $this->Commerce_db->getUser($this->get('idUser'));
        // Insert new user
        if (count($user) == 0){
            $this->Commerce_db->insertUser(array( 'id' => $this->get('idUser'), 'idCity' => 1, 'status' => 1 ));
            $user = $this->Commerce_db->getUser($this->get('idUser'));
        }
        
        // Result data
        if ($success){
            $message = array('success' => $success, 'userCommerce' => $user[0]);
        }else{
            $message = array('success' => $success, 'message' => 'Error data.');
        }
        $this->response($message, 200);
    }
    
    /**
     * Actualiza el usuario
     */
    public function updateUser_get(){
        // Update user
        $name = '';
        $email = '';
        if ($this->get('name') != '-'){ $name = $this->get('name'); } 
        if ($this->get('email') != '-'){ $email = $this->get('email'); } 
        
        $this->Commerce_db->updateUser($this->get('idUser'), array( 'name' => $name, 'email' => $email));
        
        // Result data
        $message = array('success' => true);
        $this->response($message, 200);
    }

	/**
     * Obtiene las recompensas
     */
    public function doCheckIn_get(){
        // Get User
        $user;
        $isUser = $this->Commerce_db->getUser($this->get('idUser'));
        // Insert new user
        if (count($isUser) == 0){
            $this->Commerce_db->insertUser(array( 'id' => $this->get('idUser'), 'idCity' => 1, 'status' => 1 ));
        }
        
        $userCommerce = $this->Commerce_db->getUserCommerce($this->get('idUser'), $this->get('idCommerce'));
        if (count($userCommerce) == 0){
            // Crear usuario-commercio
            $this->Commerce_db->insertUserCommerce(array( 'idUser' => $this->get('idUser'),  'idCommerce' => $this->get('idCommerce'), 'points' => '10' ));
            $this->Commerce_db->setUserPoints($this->get('idUser'), $this->get('idCommerce'), array('points' => 10));
            $this->Commerce_db->logNewUserCom(array( 'idUser' => $this->get('idUser'), 'idCommerce' => $this->get('idCommerce') ));
            $this->Commerce_db->logCheckin(array( 'idUser' => $this->get('idUser'), 'points' => 10, 'idCommerce' => $this->get('idCommerce') ));
            // obtener datos
            $user = $this->Commerce_db->getUserPoints($this->get('idUser'), $this->get('idCommerce'))[0];
            $user->newPoints = true;
        }else{
            // obtener datos
            $user = $this->Commerce_db->getUserPoints($this->get('idUser'), $this->get('idCommerce'))[0];
            if ($user->numhours == null || $user->numhours > 6){
                $user->newPoints = true;
                $user->points = $user->points + 10;
                $this->Commerce_db->setUserPoints($this->get('idUser'), $this->get('idCommerce'), array('points' => $user->points));
                $this->Commerce_db->logCheckin(array( 'idUser' => $this->get('idUser'), 'points' => 10, 'idCommerce' => $this->get('idCommerce') ));

            }else{
                $user->newPoints = false;
            }
        }
        
        
        // Reward available
        $rewards = $this->Commerce_db->getComRewards($this->get('idCommerce'));
        foreach ($rewards as $item): 
            $item->available = false;
			if( intval($item->points) <= intval($user->points)){
				$item->available = true;
			}
        endforeach; 
        
        // Result data
        $message = array('success' => true, 'isPoints' => $user->newPoints, 'user' => $user, 'rewards' => $rewards);
        $this->response($message, 200);
    }
    
    /**
     * Obtiene el cashier
     */
    public function getCashier_get(){
        // Get User
        $user = $this->Commerce_db->getCashier($this->get('idCashier'), $this->get('idCommerce'));
                                   
        // Result data
        if (count($user) == 0){
            $user = $this->Commerce_db->getUserPoints($this->get('idCashier'), $this->get('idCommerce'));
            if (count($user) == 0){
                $this->response(array('success' => false), 200);
            }else{
                $user = $user[0];
                $this->response(array('success' => true, 'isCashier' => false, 'user' => $user), 200);
            }
        }else{
            // Obtenemos los ultimos rewards canjeados
            $user = $user[0];
            $redemptions = $this->Commerce_db->getRedemRewards($this->get('idCommerce'));
            // Fecha Vigencia
            $months = array('', 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
            foreach ($redemptions as $item):
                $item->dateTexto = date('d', strtotime($item->dateChange)) . ' de ' . 
                    $months[date('n', strtotime($item->dateChange))];
            endforeach;
            $this->response(array('success' => true, 'isCashier' => true, 'user' => $user, 'redemptions' => $redemptions), 200);
        }
    }
    
    /**
     * Inserta / Actualiza una compra
     */
    public function insertRedemption_get(){
        
        if ($this->get('status') == '1'){
            // Get points
            $user = $this->Commerce_db->getUserPoints($this->get('idUser'), $this->get('idCommerce'));
            $points = $user[0]->points - intval($this->get('points'));
            // Update points
            $this->Commerce_db->setUserPoints($this->get('idUser'), $this->get('idCommerce'), array('points' => $points));
            
            // Insert data
            $user = $this->Commerce_db->insertRedemption(array(
                'idUser' => $this->get('idUser'),
                'idReward' => $this->get('idReward'),
                'idCommerce' => $this->get('idCommerce'),
                'idCashier' => 1,
                'dateChange' => date('y-m-d h:i:s'),
                'points' => 10,
                'status' => 1));
        }
        $this->response(array('success' => true), 200);
    }
    
    /**
     * Inserta / Actualiza una compra
     */
    public function updateRedemption_get(){
        // Actualiza info
        if ($this->get('status') == "2"){
            $this->Commerce_db->updateRedemption($this->get('idRedemption'), array( 'status' => 2, 'dateRedemption' => date('y-m-d h:i:s')));
        }else{
            $this->Commerce_db->updateRedemption($this->get('idRedemption'), array( 'status' => 3, 'dateCancelation' => date('y-m-d h:i:s')));
            // Get points
            $reden = $this->Commerce_db->getRedemption($this->get('idRedemption'));
            $user = $this->Commerce_db->getUserPoints($reden[0]->idUser, $reden[0]->idCommerce);
            $points = $user[0]->points + intval($this->get('points'));
            // Update points
            $this->Commerce_db->setUserPoints($reden[0]->idUser, $reden[0]->idCommerce, array('points' => $points));
        }
        
        $this->response(array('success' => true), 200);
    }

}