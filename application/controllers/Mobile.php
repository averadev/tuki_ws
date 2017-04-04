<?php
defined('BASEPATH') OR exit('No direct script access allowed');

setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
require APPPATH.'/libraries/REST_Controller.php';
require APPPATH.'/libraries/BarcodeQR.php';

class Mobile extends REST_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->database('default');
        $this->load->model('Api_db');
    }

	public function index_get(){
    }
    
    /**
     * Verificamos si existe la imagen
     */
    public function getQR_get($key){
        $image = $key.".png";
        if (!(file_exists("./assets/img/api/qr/".$image))){
            $qr = new BarcodeQR(); 
            $qr->text($key); 
            $qr->draw(330, "./assets/img/api/qr/".$image);
        }
    }

    /**------------------------------ HOME ------------------------------**/

    /**
     * Obtiene el comercio por id
     */
    public function getHomeRewards_get(){
        $idCity = $this->Api_db->getCity($this->get('idUser'))[0]->idCity;
        $items = $this->Api_db->getComHome($this->get('idUser'), $idCity);
        $wallet = $this->Api_db->countWallet($this->get('idUser'))[0]->total;
        $message = $this->Api_db->countMessage($this->get('idUser'))[0]->total;
        
        // Determina si el usuario accedio hoy al app
        $user = $this->Api_db->getUserApp($this->get('idUser'), date("Y-m-d"));
        if (count($user) == 0){
            $this->Api_db->insertUserApp(array('idUser' =>$this->get('idUser'), 'dateAction' => date("Y-m-d")));
        }
        
        // Rewards
        foreach ($items as $item):
            $item->rewards  = $this->Api_db->getRewardsH($this->get('idUser'), $item->id);;
        endforeach;
        
        $message = array('success' => true, 'wallet' => $wallet, 'message' => $message, 'items' => $items);
        $this->response($message, 200);
    }
    
    /**
     * Obtiene el comercio por id
     */
    public function getHomeRewardsGPS_get(){
        $idCity = $this->Api_db->getCity($this->get('idUser'))[0]->idCity;
        $items = $this->Api_db->getBranchGPS($this->get('idUser'), $idCity);
        $wallet = $this->Api_db->countWallet($this->get('idUser'))[0]->total;
        $message = $this->Api_db->countMessage($this->get('idUser'))[0]->total;
        
        // Calculate distance
        foreach ($items as $item):
            $item->distance = $this->haversineGreatCircleDistance($this->get('lat'), $this->get('long'), $item->lat, $item->long);
            $item->km  = number_format($item->distance/1000, 1, '.', '').'km';
        endforeach;
        
        // Order by distance
        usort($items, function($a, $b){
            return strcmp($a->distance, $b->distance);
        });
        
        // Agrupa por id
        $items = $this->DeDupeArrayOfObjectsByProps($items, ['id'] );
        
        // Determina si el usuario accedio hoy al app
        $user = $this->Api_db->getUserApp($this->get('idUser'), date("Y-m-d"));
        if (count($user) == 0){
            $this->Api_db->insertUserApp(array('idUser' =>$this->get('idUser'), 'dateAction' => date("Y-m-d")));
        }
        
        // Rewards
        foreach ($items as $item):
            $item->rewards  = $this->Api_db->getRewardsH($this->get('idUser'), $item->id);;
        endforeach;
        
        $message = array('success' => true, 'wallet' => $wallet, 'message' => $message, 'items' => $items);
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
    
    /**------------------------------ USER ------------------------------**/
    
    /**
     * Crea usuario por APP
     */
    public function createUser_get(){
        // Get Device ID
        $deviceID = $this->Api_db->getDeviceID($this->get('deviceID'));
        // Get User
        $message = array('success' => false);
        $user = $this->Api_db->getUserEmail($this->get('email'));
        if (count($user) == 0){
            $months = $this->Api_db->getMonths()[0]->months;
            $newUser = array(
                'id' => '', 
                'fbid' => '', 
                'name' => '', 
                'email' => $this->get('email'), 
                'password' => md5($this->get('password')), 
                'idCity' => 1,
                'status' => 1
            );
            if (count($deviceID) == 0){ 
                $device = array('deviceID' => $this->get('deviceID'));
                $newUser = array_merge($newUser, $device);
            }
            
            $user = $this->Api_db->createUser($this->getRandomCode(), $months, $newUser);
            $user = $this->Api_db->getUserEmail($this->get('email'));
            $message = array('success' => true, 'user' => $user[0]);
        }
        // Retrive message
        
        $this->response($message, 200);
    }
    
    /**
     * Crea usuario por APP FB
     */
    public function createUserFB_get(){
        // Get Device ID
        $deviceID = $this->Api_db->getDeviceID($this->get('deviceID'));
        // Get User
        $user = $this->Api_db->getUserFbid($this->get('fbid'));
        if (count($user) == 0){
            $months = $this->Api_db->getMonths()[0]->months;
            
            $name = $this->get('name');
            $firstName = $this->get('firstName');
            $lastName = $this->get('lastName');
            $ageMin = $this->get('ageMin'); 
            $ageMax = $this->get('ageMax'); 
            $gender = $this->get('gender'); 
            $locale = $this->get('locale'); 
            $timezone = $this->get('timezone'); 
            $email = $this->get('email');
             
            if ($name == '0') {$name = null;}
            if ($firstName == '0') {$firstName = null;}
            if ($lastName == '0') {$lastName = null;}
            if ($ageMin == '0') {$ageMin = null;}
            if ($ageMax == '0') {$ageMax = null;}
            if ($gender == '0') {$gender = null;}
            if ($locale == '0') {$locale = null;}
            if ($timezone == '0') {$timezone = null;}
            if ($email == '0') {$email = null;}
            
            $newUser = array(
                'id' => '', 
                'fbid' => $this->get('fbid'), 
                'name' => $name, 
                'firstName' => $firstName, 
                'lastName' => $lastName, 
                'ageMin' => $ageMin, 
                'ageMax' => $ageMax, 
                'gender' => $gender, 
                'locale' => $locale, 
                'timezone' => $timezone, 
                'email' => $email, 
                'idCity' => 1,
                'status' => 1
            );
            if (count($deviceID) == 0){ 
                $device = array('deviceID' => $this->get('deviceID'));
                $newUser = array_merge($newUser, $device);
            }
            $user = $this->Api_db->createUser($this->getRandomCode(), $months, $newUser);
            $user = $this->Api_db->getUserFbid($this->get('fbid'));
        }
        
        // Retrive message
        $message = array('success' => true, 'user' => $user[0] );
        $this->response($message, 200);
    }
    
    /**
     * Actualiza onesignalid
     */
    public function updateOneSignalId_get(){
        // Update User
        $user = $this->Api_db->updateOneSignalId($this->get('idUser'), $this->get('oneSignalId'));
        $this->response(array('success' => true), 200);
    }
    
    /**
     * Valida usuario
     */
    public function validateUser_get(){
        // Get User
        $message = array('success' => false);
        $user = $this->Api_db->getUserEmailPass($this->get('email'), md5($this->get('password')));
        if (count($user) > 0){
            $message = array('success' => true, 'user' => $user[0]);
        }
        // Retrive message
        
        $this->response($message, 200);
    }
    
    /**
     * Obtiene la informacion del usuario
     */
    public function getAccount_get(){
        $user = $this->Api_db->getAccount($this->get('idUser'))[0];
        $user->joined = $this->Api_db->getAccountCommerces($this->get('idUser'));
        
        // Formatos fecha
        $months = array('', 'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic');
        $user->signin = date('d', strtotime($user->signin)) . '/' . $months[date('n', strtotime($user->signin))] . '/' . date('Y', strtotime($user->signin));
        foreach ($user->joined as $item):
            if (isset($item->lastVisit)) {
                $item->lastVisit = date('d', strtotime($item->lastVisit)) . '/' . $months[date('n', strtotime($item->lastVisit))] . '/' . date('Y', strtotime($item->lastVisit));
            }
        endforeach;
        
        $message = array('success' => true, 'user' => $user);
        $this->response($message, 200);
    }
    
    /**
     * Obtiene la informacion del usuario
     */
    public function getProfile_get(){
        $user = $this->Api_db->getProfile($this->get('idUser'))[0];
        $cities = $this->Api_db->getCities($this->get('idUser'), '');
        
        // Split birthdate
        if (isset($user->birthDate)) {
            $user->birday = date('d', strtotime($user->birthDate));
            $user->birmonth = date('n', strtotime($user->birthDate));
            $user->biryear = date('Y', strtotime($user->birthDate));
        }
        
        // Formatos fecha
        $months = array('', 'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic');
        $user->signin = date('d', strtotime($user->signin)) . '/' . $months[date('n', strtotime($user->signin))] . '/' . date('Y', strtotime($user->signin));
        
        $message = array('success' => true, 'user' => $user, 'cities' => $cities);
        $this->response($message, 200);
    }
    
    /**
     * Update Profile
     */
    public function updateProfile_get(){
        // Update Profile
        $toUpdate = array('email' => $this->get('email'), 'phone' => $this->get('phone'));
        if ($this->get('gender')){
            $toUpdate['gender'] = $this->get('gender');
        }
        if ($this->get('birthDate')){
            $toUpdate['birthDate'] = $this->get('birthDate');
        }
        if ($this->get('idCity')){
            $toUpdate['idCity'] = $this->get('idCity');
        }
        
        $user = $this->Api_db->updateProfile($this->get('idUser'), $toUpdate);
        $this->response(array('success' => true), 200);
    }
    
    /**
     * Afiliamos un usuario a un comercio
     */
    public function setCommerceJoin_get(){
        // Join user to commerce and log it
        $isNew = false;
        $isGift = 0;
        $userCommerce = $this->Api_db->getUserCommerce($this->get('idUser'), $this->get('idCommerce'));
        if (count($userCommerce) == 0){
            $isNew = true;
            $this->Api_db->insertUserCommerce(array( 'idUser' => $this->get('idUser'),  'idCommerce' => $this->get('idCommerce'), 'points' => '0' ));
            $this->Api_db->logNewUserCom(array( 'idUser' => $this->get('idUser'), 'idCommerce' => $this->get('idCommerce') ));
            
            // Add Gift if exists
            $gifts  = $this->Api_db->isCommerceGift($this->get('idCommerce'));
            if (count($gifts) > 0){
                foreach ($gifts as $gift):
                    $isWallet = $this->Api_db->isWallet($this->get('idUser'), $this->get('deviceID'), $gift->id);
                    if (count($isWallet) == 0){
                        $isGift = $isGift + 1;
                        $this->Api_db->insertWallet(array( 'idUser' => $this->get('idUser'), 'deviceID' => $this->get('deviceID'), 'idReward' => $gift->id, 'status' => 1 ));
                    }
                endforeach;
            }
        }
        // Retrive message
        $message = array('success' => $isNew, 'gift' => $isGift);
        $this->response($message, 200);
    }
    
    /**
     * Afiliamos un usuario a un comercio
     */
    public function multipleJoin_get(){
        // Join user to commerce and log it
        $idComms = explode("-", $this->get('idComms'));
        foreach ($idComms as $idComm):
            $userCommerce = $this->Api_db->getUserCommerce($this->get('idUser'), $idComm);
            if (count($userCommerce) == 0){
                $isNew = true;
                $this->Api_db->insertUserCommerce(array( 'idUser' => $this->get('idUser'),  'idCommerce' => $idComm, 'points' => '0' ));
                $this->Api_db->logNewUserCom(array( 'idUser' => $this->get('idUser'), 'idCommerce' => $idComm ));
                
                // Add Gift if exists
                $gifts  = $this->Api_db->isCommerceGift($idComm);
                if (count($gifts) > 0){
                    foreach ($gifts as $gift):
                        $isWallet = $this->Api_db->isWallet($this->get('idUser'), $this->get('deviceID'), $gift->id);
                        if (count($isWallet) == 0){
                            $this->Api_db->insertWallet(array( 'idUser' => $this->get('idUser'), 'deviceID' => $this->get('deviceID'), 'idReward' => $gift->id, 'status' => 1 ));
                        }
                    endforeach;
                }
            }   
        endforeach;
        
        
        // Retrive message
        $message = array('success' => 1);
        $this->response($message, 200);
    }

    /**------------------------------ REWARDS ------------------------------**/

    /**
     * Obtiene las recompensas
     */
    public function getRewards_get(){
        // Comercios
        $filters = str_replace('-', ',', $this->get('filters'));
        $items = $this->Api_db->getJoinedLite($this->get('idUser'), $filters);
        // Rewards
        foreach ($items as $item):
            $item->rewards  = $this->Api_db->getRewardsByCommerce($this->get('idUser'), $item->id);;
        endforeach;
        
        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }

    /**
     * Obtiene las recompensas
     */
    public function getRewardFavs_get(){
        $filters = str_replace('-', ',', $this->get('filters'));
        $data = array('idUser' => $this->get('idUser'));
        $items = $this->Api_db->getRewardFavs($data, $filters);
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
    
    /**
     * Obtiene la recompensa
     */
    public function isGiftRedem_get(){
        $message = array('success' => false);
        $gift = $this->Api_db->isGiftRedem($this->get('idUser'), $this->get('idReward'));
        if (count($gift) > 0){
            if ($gift[0]->status >= 3){
                $message = array('success' => true);
            }
        }
        $this->response($message, 200);
    }

    /**------------------------------ COMMERCE ------------------------------**/

    /**
     * Obtiene la recompensa
     */
    public function getCommerceFlow_get(){
        $idCity = 0;
        if ($this->get('idUser') != '1'){
            $idCity = $this->Api_db->getCity($this->get('idUser'))[0]->idCity;
        }
        
        $items = $this->Api_db->getCommerceFlow($idCity);
        shuffle($items);
        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }

    /**
     * Obtiene los comercios
     */
    public function getCommerces_get(){
        $filters = str_replace('-', ',', $this->get('filters'));
        $idCity = $this->Api_db->getCity($this->get('idUser'))[0]->idCity;
        $items = $this->Api_db->getCommerces($this->get('idUser'), $filters, $idCity);
        
        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }
    
    /**
     * Obtiene los comercios por GPS
     */
    public function getCommercesByGPS_get(){
        // Cuadrante de busqueda
        $lat1 = str_replace(',', '.', $this->get('latitude') - .1);
        $lat2 = str_replace(',', '.', $this->get('latitude') + .1);
        $lon1 = str_replace(',', '.', $this->get('longitude') - .1);
        $lon2 = str_replace(',', '.', $this->get('longitude') + .1);
        
        $items = $this->Api_db->getCommercesByGPS($lat1, $lat2, $lon1, $lon2);
        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }
    
    /**
     * Obtiene los comercios por GPS
     */
    public function getCommercesByGPSLite_get(){
        // Cuadrante de busqueda
        $lat1 = str_replace(',', '.', $this->get('latitude') - .1);
        $lat2 = str_replace(',', '.', $this->get('latitude') + .1);
        $lon1 = str_replace(',', '.', $this->get('longitude') - .1);
        $lon2 = str_replace(',', '.', $this->get('longitude') + .1);
        
        $items = $this->Api_db->getCommercesByGPSLite($lat1, $lat2, $lon1, $lon2);
        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }

    /**
     * Obtiene los comercios
     */
    public function getCommercesWList_get(){
        $items = $this->Api_db->getCommercesWList($this->get('idCity'));
        
        // Calculate distance
        foreach ($items as $item):
            $item->distance = $this->haversineGreatCircleDistance($this->get('lat'), $this->get('long'), $item->lat, $item->long);
            $item->km  = number_format($item->distance/1000, 1, '.', '').'km';
        endforeach;
        
        // Order by distance
        usort($items, function($a, $b){
            return strcmp($a->distance, $b->distance);
        });
        
        // Agrupa por id
        $items = $this->DeDupeArrayOfObjectsByProps($items, ['id'] );
        
        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }

    /**
     * Obtiene los comercios
     */
    public function getCommercesWCat_get(){
        $filters = str_replace('-', ',', $this->get('filters'));
        $items = $this->Api_db->getCommercesWCat($filters);
        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }

    /**
     * Obtiene los comercios afiliados
     */
    public function getJoined_get(){
        $filters = str_replace('-', ',', $this->get('filters'));
        $items = $this->Api_db->getJoined($this->get('idUser'), $filters);
        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }

    /**
     * Obtiene la recompensa
     */
    public function getCommerce_get(){
        $items = $this->Api_db->getCommerce($this->get('idUser'), $this->get('idCommerce'));
        array_push($items, array('image' => $items[0]->banner));
        $rewards = $this->Api_db->getRewardsByCommerce($this->get('idUser'), $this->get('idCommerce'));
        $photos = $this->Api_db->getCommercePhotos($this->get('idCommerce')); 
        // Branch
        $idCity = $this->Api_db->getCity($this->get('idUser'))[0]->idCity;
        $branchs = $this->Api_db->getCommerceBranchCity($this->get('idCommerce'), $idCity);
        
        // Formatos fecha
        $months = array('', 'ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE');
        foreach ($items as $item):
            if (isset($item->lastVisit)) {
                $item->lastVisit = date('d', strtotime($item->lastVisit)) . ' DE ' . $months[date('n', strtotime($item->lastVisit))] . ' ' . date('Y', strtotime($item->lastVisit));
            }
        endforeach;
        
        $message = array('success' => true, 'items' => $items, 'rewards' => $rewards, 'photos' => $photos, 'branchs' => $branchs);
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
     * Actualiza estatus gift
     */
    public function setReadGift_get(){
        $this->Api_db->setStatusGift($this->get('idUser'), $this->get('idReward'), array('status' => 2));
        $message = array('success' => true);
        $this->response($message, 200);
    }
    
    /**
     * Obtiene las recompensas
     */
    public function getWallet_get(){
        $items = $this->Api_db->getWallet($this->get('idUser'));

        // Fecha Vigencia
        $months = array('', 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
        foreach ($items as $item):
            $item->vigency = date('d', strtotime($item->vigency)) . ' de ' . $months[date('n', strtotime($item->vigency))] . ' del ' . date('Y', strtotime($item->vigency));
            $item->fecha = date('d', strtotime($item->dateReden)) . ' de ' . $months[date('n', strtotime($item->dateReden))] . ' del ' . date('Y', strtotime($item->dateReden));
        endforeach;

        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }
    
    /**------------------------------ CITIES ------------------------------**/

    /**
     * Actualiza estatus gift
     */
    public function setCity_get(){
        $this->Api_db->setCity($this->get('idUser'), $this->get('idCity'));
        $message = array('success' => true);
        $this->response($message, 200);
    }
    
    /**
     * Obtiene las recompensas
     */
    public function getCities_get(){
        $data = $this->get('data');
        if ($data == '-') $data = '';
        $items = $this->Api_db->getCities($this->get('idUser'), $data);
        
        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }
    
    /**
     * Obtiene las recompensas
     */
    public function getIdCity_get(){
        $message = array('idCity' => 0);
        
        if(stristr($this->get('address'), 'Cancún')){
            $message = array('idCity' => 1);
        }elseif(stristr($this->get('address'), 'Merida')){
            $message = array('idCity' => 2);
        }elseif(stristr($this->get('address'), 'Mexico City')){
            $message = array('idCity' => 3);
        }
        $this->response($message, 200);
    }

    /**------------------------------ MESSAGES SEG ------------------------------**/
    
    /**
     * Actualiza estatus message
     */
    public function setReadMessage_get(){
        $this->Api_db->setStatusMessage($this->get('idMessage'), array('status' => 2));
        $message = array('success' => true);
        $this->response($message, 200);
    }
    
    /**
     * Obtiene las recompensas
     */
    public function getMessagesSeg_get(){
        $items = $this->Api_db->getMessagesSeg($this->get('idUser'));

        // Fecha Vigencia
        $months = array('', 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
        foreach ($items as $item):
            $item->fecha = date('d', strtotime($item->dateIncome)) . ' de ' . $months[date('n', strtotime($item->dateIncome))] . ' del ' . date('Y', strtotime($item->dateIncome));
        endforeach;

        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }

    /**
     * Obtiene las recompensas
     */
    public function getMessageSeg_get(){
        $items = $this->Api_db->getMessageSeg($this->get('idMessage'));

        // Fecha Vigencia
        $months = array('', 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
        foreach ($items as $item):
            $item->fecha = date('d', strtotime($item->dateIncome)) . ' de ' . $months[date('n', strtotime($item->dateIncome))] . ' del ' . date('Y', strtotime($item->dateIncome));
        endforeach;

        $message = array('success' => true, 'items' => $items);
        $this->response($message, 200);
    }

    /**------------------------------ MESSAGES ------------------------------**/

    /**
     * Obtiene las recompensas
     */
    public function getMessages_get(){
        $message = array('success' => true, 'items' => array());
        $this->response($message, 200);
    }

    /**
     * Obtiene las recompensas
     */
    public function getMessage_get(){
        $message = array('success' => true, 'items' => array());
        $this->response($message, 200);
    }
    
    /**------------------------------ USER ------------------------------**/
    /**
     * Obtiene el usuario
     */
    public function insertUser_get(){
        // Get User
        $message = array('success' => false);
        $userFbid = array();
        $email = $this->get('email');
        $name = $this->get('name');
        $fbid = $this->get('fbid');
        if ($fbid == '-') $fbid = '';
        if ($email == '-') $email = '';
        if ($name == '-') $name = '';
        $isNew = true;
        
        if ($fbid != ''){
            $userFbid = $this->Api_db->getUserFbid($fbid);
            if (count($userFbid) > 0){
                // Regresamos el usuario de BD
                $message = array('success' => true, 'user' => $userFbid[0]);
            }else{
                $emailUser;
                if ($email != ''){
                    $emailUser = $this->Api_db->getUserEmail($email);
                    if (count($emailUser) > 0){
                        // Regresamos el usuario de BD
                        $isNew = false;
                        $emailUser[0]->fbid = $fbid;
                        $emailUser[0]->name = $name;
                        $this->Api_db->updateUser($emailUser[0]->id, $emailUser[0]);
                        $message = array('success' => true, 'user' => $emailUser[0]);
                    }
                }
                
                if ($isNew){
                    // Creamos nuevo usuario
                    $newUser = $this->Api_db->newUserApp($fbid, $email, $name);
                    $message = array('success' => true, 'user' => $newUser);
                }
            }
        }elseif ($email != ''){
            $emailUser = $this->Api_db->getUserEmail($email);
            if (count($emailUser) > 0){
                // Regresamos el usuario de BD
                $isNew = false;
                $emailUser[0]->name = $name;
                $this->Api_db->updateUser($emailUser[0]->id, $emailUser[0]);
                $message = array('success' => true, 'user' => $emailUser[0]);
            }
            
            if ($isNew){
                // Creamos nuevo usuario
                $newUser = $this->Api_db->newUserApp($fbid, $email, $name);
                $message = array('success' => true, 'user' => $newUser);
            }
        }
        
        // Result data
        $this->response($message, 200);
        
    }
    
    /**
     * Obtiene el usuario
     */
    public function cardLink_get(){
        $message = array('success' => false);
        
        // Validar Id Tarjeta
        if (((double) $this->get('idCard') < 1000000000000000) or ((double) $this->get('idCard') > 4000000000000000)){
            $message = array('success' => false, 'message' => "BadIdCard");
        }else{
            // Consultar Id's
            $data = $this->Api_db->getUserCard($this->get('idUser'), $this->get('idCard'));
            if (count($data) == 0){
                $data = $this->Api_db->getAccount($this->get('idCard'));
                if (count($data) == 0){
                    // La tarjeta es nueva
                    $message = array('success' => true, 'message' => "NewCard", 'idCard' => $this->get('idCard'));
                }else{
                    // Sumamos puntos
                    $isGift = 0;
                    $points = $this->Api_db->getCardPoints($this->get('idCard'));
                    $this->Api_db->assignCardPoints($this->get('idUser'), $points);
                    
                    // Add Gift if exists
                    foreach ($points as $idComm):
                       // Add Gift if exists
                        $gifts  = $this->Api_db->isCommerceGift($idComm->idCommerce);
                        if (count($gifts) > 0){
                            foreach ($gifts as $gift):
                                $isWallet = $this->Api_db->isWallet($this->get('idUser'), $this->get('deviceID'), $gift->id);
                                if (count($isWallet) == 0){
                                    $isGift = $isGift + 1;
                                    $this->Api_db->insertWallet(array( 'idUser' => $this->get('idUser'), 'deviceID' => $this->get('deviceID'), 'idReward' => $gift->id, 'status' => 1 ));
                                }
                            endforeach;
                        }
                    endforeach;
                    
                    // Se agregaron multiples comercios
                    $message = array('success' => true, 'points' => $points, 'gift' => $isGift, 'idCard' => $this->get('idCard'));
                }
                $this->Api_db->insertUserCard(array( 'idUser' => $this->get('idUser'), 'idCard' => $this->get('idCard'), 'status' => 1));

            }else{
                
                // Se ha realizado una vinculacion previa
                if ($data[0]->idUser == $this->get('idUser')){
                    $message = array('success' => false, 'message' => "UserExist");
                }else{
                    $message = array('success' => false, 'message' => "CardExist");
                }
            }
        }
        
        // Result data
        $this->response($message, 200);
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
    
    /**
	 * Generamos codigo aleatorios
	 */
	public function getRandomCode(){
        $an = "0123456789";
        $su = strlen($an) - 1;
        return substr($an, rand(0, $su), 1) .
                substr($an, rand(0, $su), 1) .
                substr($an, rand(0, $su), 1);
    }
    
    /**
	 * Generamos numero de meses entre meses
	 */
    public function noMonths($date1, $date2)
    {
        $begin = new DateTime( $date1 );
        $end = new DateTime( $date2 );
        $end = $end->modify( '+1 month' );

        $interval = DateInterval::createFromDateString('1 month');

        $period = new DatePeriod($begin, $interval, $end);
        $counter = 0;
        foreach($period as $dt) {
            $counter++;
        }

        return $counter;
    }
    
    /**
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     */
    function haversineGreatCircleDistance(
      $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
      // convert from degrees to radians
      $latFrom = deg2rad($latitudeFrom);
      $lonFrom = deg2rad($longitudeFrom);
      $latTo = deg2rad($latitudeTo);
      $lonTo = deg2rad($longitudeTo);

      $latDelta = $latTo - $latFrom;
      $lonDelta = $lonTo - $lonFrom;

      $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
      return intval(($angle * $earthRadius));
    }
    
    /**
     * Function to sort an array by a specific key. Maintains index association.
     */
    function array_sort($array, $on, $order=SORT_ASC){
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                break;
                case SORT_DESC:
                    arsort($sortable_array);
                break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }
        return $new_array;
    }
    
    /**
     * Iterates over the array of objects and looks for matching property values.
     * If a match is found the later object is removed from the array, which is returned
     * @param array $objects    The objects to iterate over
     * @param array $props      Array of the properties to dedupe on.
     *   If more than one property is specified then all properties must match for it to be deduped.
     * @return array
     */
    public function DeDupeArrayOfObjectsByProps($objects, $props) {
        if (empty($objects) || empty($props))
            return $objects;
        $results = array();
        foreach ($objects as $object) {
            $matched = false;
            foreach ($results as $result) {
                $matchs = 0;
                foreach ($props as $prop) {
                    if ($object->$prop == $result->$prop)
                        $matchs++;
                }
                if ($matchs == count($props)) {
                    $matched = true;
                    break;
                }

            }
            if (!$matched)
                $results[] = $object;
        }
        return $results;
    }


    
}




















