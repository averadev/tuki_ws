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
    
    
    
    
    
    
    /**------------------------------ APP COMMERCE ------------------------------**/
    
	/**
     * Obtiene las recompensas
     */
    public function verifyPassword_get(){
        $response = array('success' => false);
        $branch = $this->Commerce_db->verifyPassword(md5($this->get('password')));
        if (count($branch) > 0){
            $response = array('success' => true, 'branch' => $branch );
        }
        
        $this->response($response, 200);
    }
    
	/**
     * Obtiene las recompensas
     */
    public function validateExit_get(){
        $response = array('success' => false);
        $branch = $this->Commerce_db->validateExit($this->get('idBranch'), md5($this->get('password')));
        if (count($branch) > 0){
            $response = array('success' => true );
        }
        
        $this->response($response, 200);
    }
    
	/**
     * Obtiene las recompensas
     */
    public function getRewards_get(){
        $rewards = $this->Commerce_db->getRewards($this->get('idCommerce'));
        $this->response(array('success' => true, 'items' => $rewards), 200);
    }
    
	/**
     * Obtiene las recompensas
     */
    public function getRedenciones_get(){
        $redemptions = $this->Commerce_db->getRedemRewards($this->get('idBranch'));
        // Fecha Vigencia
        $months = array('', 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
        foreach ($redemptions as $item):
            $item->dateTexto = date('d', strtotime($item->dateChange)) . ' de ' . 
                $months[date('n', strtotime($item->dateChange))].' '.date('h:i', strtotime($item->dateChange));
        endforeach;
        $this->response(array('success' => true, 'items' => $redemptions), 200);
    }
    
    /**
     * Validamos QR
     */
    public function validateQR_get(){
        $response = array();
        $newPoints = 0;
        $idQR = $this->get('qr');
        
        // Validar Cajero
        $user = $this->Commerce_db->isCashier($idQR);
        if (count($user) >0){
            $user = $this->Commerce_db->isCashierBranch($idQR, $this->get('idBranch'));
            if (count($user) >0){
                $response = $this->response(array('success' => true, 'cashier' => $user[0]), 200);
            }else{
                $response = $this->response(array('success' => false, 'cashier' => 1), 200);
            }
        }else{
            
            // Validar LinkCard
            $user = $this->Commerce_db->isLinkCard($idQR);
            if (count($user) > 0){
                $idQR = $user[0]->idUser;
            }
            
            // Validar Usuario
            $user = $this->Commerce_db->isUser($idQR, $this->get('idCommerce'));
            if (count($user) == 0){
                $this->Commerce_db->insertUser(array( 'id' => $idQR, 'idCity' => 1, 'status' => 1 ));
                $user = $this->Commerce_db->isUser($idQR, $this->get('idCommerce'));
            }
            
            // Validar Usuario-Commercio
            $user = $this->Commerce_db->isUserCommerce($idQR, $this->get('idCommerce'));
            if (count($user) == 0){
                $newPoints = 10;
                $this->Commerce_db->insertUserCommerce(array( 'idUser' => $idQR,  'idCommerce' => $this->get('idCommerce'), 'points' => '10' ));
                $this->Commerce_db->setUserPoints($idQR, $this->get('idCommerce'), array('points' => 10));
                $this->Commerce_db->logNewUserCom(array( 'idUser' => $idQR, 'idCommerce' => $this->get('idCommerce') ));
                $this->Commerce_db->logCheckin(array( 'idUser' => $idQR, 'points' => 10, 'idBranch' => $this->get('idBranch') ));
            }else{
                $user = $user[0];
                if ($user->numhours == null || $user->numhours >= 6){
                    $newPoints = 10;
                    $user->points = $user->points + 10;
                    $this->Commerce_db->setUserPoints($idQR, $this->get('idCommerce'), array('points' => $user->points));
                    $this->Commerce_db->logCheckin(array( 'idUser' => $idQR, 'points' => 10, 'idBranch' => $this->get('idBranch') ));
                }
            }
            
            // Obtener Usuario-Commercio
            $user = $this->Commerce_db->userPoints($idQR, $this->get('idCommerce'));
            $user[0]->newPoints = $newPoints;
            $response = $this->response(array('success' => true, 'user' => $user[0]), 200);
        }
        
        $this->response($response, 200);
    }
    
    
    
    /**
     * Validamos QR Reward
     */
    public function validateQrReward_get(){
        $response = array('success' => false);
        if (strrpos($this->get('qr'), '-') > 0){
            $ids = explode("-", $this->get('qr'));
            $user = $this->Commerce_db->userPoints($ids[0], $this->get('idCommerce'));
            $reward = $this->Commerce_db->getReward($ids[1]);
            if (count($user) > 0 && count($reward) > 0){
                $response = array('success' => true, 'user' => $user[0], 'reward' => $reward[0]);
            }
        }
                                          
                                          
        $this->response($response, 200);
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
                'idBranch' => $this->get('idBranch'),
                'idCashier' => 1,
                'dateChange' => date('y-m-d h:i:s'),
                'points' => intval($this->get('points')),
                'status' => 1));
        }
        $this->response(array('success' => true), 200);
    }
    
    /**
     * Inserta / Actualiza una compra
     */
    public function setRedemption_get(){
        // Actualiza info
        if ($this->get('status') == "2"){
            $this->Commerce_db->updateRedemption($this->get('idRedemption'), array( 'status' => 2, 'idCashier' => $this->get('idCashier'), 'dateRedemption' => date('y-m-d h:i:s')));
        }else{
            $this->Commerce_db->updateRedemption($this->get('idRedemption'), array( 'status' => 3, 'idCashier' => $this->get('idCashier'), 'dateCancelation' => date('y-m-d h:i:s')));
            // Get points
            $reden = $this->Commerce_db->getRedemption($this->get('idRedemption'));
            $user = $this->Commerce_db->getUserPoints($reden[0]->idUser, $this->get('idCommerce'));
            $points = $user[0]->points + intval($this->get('points'));
            // Update points
            $this->Commerce_db->setUserPoints($reden[0]->idUser, $this->get('idCommerce'), array('points' => $points));
        }
        
        $this->response(array('success' => true), 200);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    

}
