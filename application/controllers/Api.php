<?php
defined('BASEPATH') OR exit('No direct script access allowed');

setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
require APPPATH.'/libraries/REST_Controller.php';

class Api extends REST_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->database('default');
        $this->load->model('Api_db');
    }

	public function index_get(){
    }

    /**------------------------------ HOME ------------------------------**/

    /**
     * Obtiene el comercio por id
     */
    public function getHomeRewards_get(){
        $items = $this->Api_db->getHomeRewards($this->get('idUser'));
        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }

    /**
     * Obtiene los puntos del usuario
     */
    public function getPointsBar_get(){
        $items = $this->Api_db->getPointsBar($this->get('idUser'));

        $total = 0;
        foreach ($items as $item):
            $total = $total + $item->points;
        endforeach;

        $message = array('success' => true, 'total' => $total, 'items' => $this->sliceArray($items, 3));
        $this->response($message, 200);
    }


    /**------------------------------ REWARDS ------------------------------**/

    /**
     * Obtiene las recompensas
     */
    public function getRewards_get(){
        $data = array('idUser' => $this->get('idUser'));
        $items = $this->Api_db->getRewards($data);
        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }

    /**
     * Obtiene las recompensas
     */
    public function getRewardFavs_get(){
        $data = array('idUser' => $this->get('idUser'));
        $items = $this->Api_db->getRewardFavs($data);
        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }

    /**
     * Marca como favorito un reward
     */
    public function setRewardFav_get(){
        $data = array('idUser' => $this->get('idUser'), 'idReward' => $this->get('idReward'));
        if ($this->get('isFav') == "1"){
            $items = $this->Api_db->insertRewardFav($data);
        }else{
            $items = $this->Api_db->deleteRewardFav($data);
        }

        $message = array('success' => true);
        $this->response($message, 200);
    }

    /**------------------------------ REWARD ------------------------------**/

    /**
     * Obtiene la recompensa
     */
    public function getReward_get(){
        $items = $this->Api_db->getReward($this->get('idUser'), $this->get('idReward'));

        // Fecha Vigencia
        $months = array('', 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
        $items[0]->vigencia = date('d', strtotime($items[0]->vigency)) . ' de ' . $months[date('n', strtotime($items[0]->vigency))] . ' del ' . date('Y', strtotime($items[0]->vigency));

        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }

    /**------------------------------ COMMERCE ------------------------------**/

    /**
     * Obtiene la recompensa
     */
    public function getCommerceFlow_get(){
        $items = $this->Api_db->getCommerceFlow();
        shuffle($items);
        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }

    /**
     * Obtiene la recompensa
     */
    public function getCommerces_get(){
        $items = $this->Api_db->getCommerces($this->get('idUser'));
        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }

    /**
     * Obtiene la recompensa
     */
    public function getCommerce_get(){
        $items = $this->Api_db->getCommerce($this->get('idUser'), $this->get('idCommerce'));
        $rewards = $this->Api_db->getRewardsByCommerce($this->get('idUser'), $this->get('idCommerce'));
        $photos = $this->Api_db->getCommercePhotos($this->get('idCommerce'));
        $message = array('success' => true, 'items' => $items, 'rewards' => $rewards, 'photos' => $photos);
        $this->response($message, 200);
    }

    /**
     * Marca como favorito un comercio
     */
    public function setCommerceFav_get(){
        $data = array('idUser' => $this->get('idUser'), 'idCommerce' => $this->get('idCommerce'));
        if ($this->get('isFav') == "1"){
            $items = $this->Api_db->insertCommerceFav($data);
        }else{
            $items = $this->Api_db->deleteCommerceFav($data);
        }

        $message = array('success' => true);
        $this->response($message, 200);
    }

    /**------------------------------ WALLET ------------------------------**/

    /**
     * Obtiene las recompensas
     */
    public function getWallet_get(){
        $items = $this->Api_db->getWallet($this->get('idUser'));

        // Fecha Vigencia
        $months = array('', 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
        foreach ($items as $item):
            $item->fecha = date('d', strtotime($item->dateReden)) . ' de ' . $months[date('n', strtotime($item->dateReden))] . ' del ' . date('Y', strtotime($item->dateReden));
        endforeach;

        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }

    /**------------------------------ MESSAGES ------------------------------**/

    /**
     * Obtiene las recompensas
     */
    public function getMessages_get(){
        $items = $this->Api_db->getMessages($this->get('idUser'));

        // Fecha Vigencia
        $months = array('', 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
        foreach ($items as $item):
            $item->fecha = date('d', strtotime($item->dateIncome)) . ' de ' . $months[date('n', strtotime($item->dateIncome))] . ' del ' . date('Y', strtotime($item->dateIncome));
            $item->from = "Equipo de Unify";
            if ($item->user){ $item->from = $item->user; }
            if ($item->commerce){ $item->from = $item->commerce; }
        endforeach;

        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }

    /**
     * Obtiene las recompensas
     */
    public function getMessage_get(){
        $items = $this->Api_db->getMessage($this->get('idMessage'));

        // Fecha Vigencia
        $months = array('', 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
        foreach ($items as $item):
            $item->fecha = date('d', strtotime($item->dateIncome)) . ' de ' . $months[date('n', strtotime($item->dateIncome))] . ' del ' . date('Y', strtotime($item->dateIncome));
            $item->from = "Equipo de Unify";
            if ($item->user){ $item->from = $item->user; }
            if ($item->commerce){ $item->from = $item->commerce; }
        endforeach;

        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }

	/**------------------------------ UNIFY COMMERCE ------------------------------**/
    
	/**
     * Obtiene el usuario
     */
    public function getUser_get(){
        $success = true;
        // Get User
        $user = $this->Api_db->getUser($this->get('idUser'));
        // Insert new user
        if (count($user) == 0){
            $this->Api_db->insertUser(array( 'id' => $this->get('idUser'), 'hash' => md5($this->get('key')), 'noCheckin' => 0, 'lastCheckin' => date('y-m-d h:i:s'), 'status' => 1 ));
            $user = $this->Api_db->getUser($this->get('idUser'));
        }
        
        if (count($user) > 0){
            // Get User Commerce
            $userCommerce = $this->Api_db->getUserCommerce($this->get('idUser'), $this->get('idCommerce'));
            if (count($userCommerce) == 0){
                $this->Api_db->insertUserCommerce(array( 'idUser' => $this->get('idUser'),  'idCommerce' => $this->get('idCommerce'), 'points' => '0' ));
                $userCommerce = $this->Api_db->getUserCommerce($this->get('idUser'), $this->get('idCommerce'));
            }
        }else{
            $success = false;
        }
        
        // Result data
        if ($success){
            $message = array('success' => $success, 'userCommerce' => $userCommerce[0]);
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
        
        $this->Api_db->updateUser($this->get('idUser'), array( 'name' => $name, 'email' => $email));
        
        // Result data
        $message = array('success' => true);
        $this->response($message, 200);
    }

	/**
     * Obtiene las recompensas
     */
    public function doCheckIn_get(){
        $isPoints = true;
        // Get User
        $isUser = $this->Api_db->getUser($this->get('idUser'));
        // Insert new user
        if (count($isUser) == 0){
            $this->Api_db->insertUser(array( 'id' => $this->get('idUser'), 'hash' => md5($this->get('key')), 'noCheckin' => 0, 'lastCheckin' => date('y-m-d h:i:s'), 'status' => 1 ));
        }else{
            $isUser = $isUser[0];
            if ($isUser->numhours > 6){
                $this->Api_db->updateUserLastCheckin($this->get('idUser'), array( 'lastCheckin' => date('y-m-d h:i:s') ));
            }else{
                $isPoints = false;
            }
        }
        
        // Get data
        $user = $this->Api_db->getUserPoints($this->get('idUser'), $this->get('idCommerce'));
        $rewards = $this->Api_db->getComRewards($this->get('idCommerce'));
        
        if ($user){
            $user = $user[0];
            $user->newPoints = true;
            if ($user->idCommerce){
                if ($isPoints){
                    $user->points = $user->points + 10;
                }
            }else{
                $user->points = 10;
                $this->Api_db->insertUserPoints(array( 'idUser' => $this->get('idUser'), 'idCommerce' => $this->get('idCommerce'), 'points' => 10));
            }
        }
        
        // Reward available
        foreach ($rewards as $item): 
            $item->available = false;
			if( intval($item->points) <= intval($user->points)){
				$item->available = true;
			}
        endforeach; 

        // Update points
        if ($isPoints){
            $this->Api_db->setUserPoints($this->get('idUser'), $this->get('idCommerce'), array('points' => $user->points));
        }
        
        // Result data
        $message = array('success' => true, 'isPoints' => $isPoints, 'user' => $user, 'rewards' => $rewards);
        $this->response($message, 200);
    }
    
    /**
     * Obtiene el cashier
     */
    public function getCashier_get(){
        // Get User
        $user = $this->Api_db->getCashier($this->get('idCashier'), $this->get('idCommerce'));
                                   
        // Result data
        if (count($user) == 0){
            $user = $this->Api_db->getUserPoints($this->get('idCashier'), $this->get('idCommerce'));
            if (count($user) == 0){
                $this->response(array('success' => false), 200);
            }else{
                $user = $user[0];
                $this->response(array('success' => true, 'isCashier' => false, 'user' => $user), 200);
            }
        }else{
            // Obtenemos los ultimos rewards canjeados
            $user = $user[0];
            $redemptions = $this->Api_db->getRedemRewards($this->get('idCommerce'));
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
            $user = $this->Api_db->getUserPoints($this->get('idUser'), $this->get('idCommerce'));
            $points = $user[0]->points - intval($this->get('points'));
            // Update points
            $this->Api_db->setUserPoints($this->get('idUser'), $this->get('idCommerce'), array('points' => $points));
            
            // Insert data
            $user = $this->Api_db->insertRedemption(array(
                'idUser' => $this->get('idUser'),
                'idReward' => $this->get('idReward'),
                'idCommerce' => $this->get('idCommerce'),
                'dateChange' => date('y-m-d h:i:s'),
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
            $this->Api_db->updateRedemption($this->get('idRedemption'), array( 'status' => 2, 'dateRedemption' => date('y-m-d h:i:s')));
        }else{
            $this->Api_db->updateRedemption($this->get('idRedemption'), array( 'status' => 3, 'dateCancelation' => date('y-m-d h:i:s')));
            // Get points
            $reden = $this->Api_db->getRedemption($this->get('idRedemption'));
            $user = $this->Api_db->getUserPoints($reden[0]->idUser, $reden[0]->idCommerce);
            $points = $user[0]->points + intval($this->get('points'));
            // Update points
            $this->Api_db->setUserPoints($reden[0]->idUser, $reden[0]->idCommerce, array('points' => $points));
        }
        
        $this->response(array('success' => true), 200);
    }


    /**------------------------------ COMUNES ------------------------------**/

    /**
     * Fragmenta un array
     */
    public function sliceArray($array, $count){
        if (count($array) > $count){
            $array = array_slice($array, 0, $count);
        }
        return $array;
    }
}
