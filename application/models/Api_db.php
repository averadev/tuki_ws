<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
Class Api_db extends CI_MODEL
{

    public function __construct(){
        parent::__construct();
    }

    /**------------------------------ HOME ------------------------------**/

	// obtiene las recompensas
	public function getHomeRewards($idUser){
		$this->db->select('reward.id, reward.name, reward.description, reward.points, reward.image');
        $this->db->select('commerce.name as commerce, commerce.description as commerceDesc');
        $this->db->select('xref_user_reward_fav.idReward as fav, ifnull(xref_user_commerce.points, 0) as userPoints', false);
        $this->db->from('reward');
        $this->db->join('commerce', 'reward.idCommerce = commerce.id ');
        $this->db->join('xref_user_reward_fav', 'reward.id = xref_user_reward_fav.idReward and xref_user_reward_fav.idUser = '.$idUser, 'left');
        $this->db->join('xref_user_commerce', 'reward.idCommerce = xref_user_commerce.idCommerce and xref_user_commerce.idUser = '.$idUser, 'left');
        $this->db->where('reward.important = 1');
        $this->db->where('reward.status = 1');
        return  $this->db->get()->result();
	}

    // obtiene los puntos
	public function getPointsBar($id){
		$this->db->select('xref_user_commerce.idCommerce, commerce.name, xref_user_commerce.points');
        $this->db->from('xref_user_commerce');
        $this->db->join('commerce', 'xref_user_commerce.idCommerce = commerce.id ');
        $this->db->where('xref_user_commerce.idUser', $id);
        $this->db->order_by('xref_user_commerce.points',"DESC");
        return  $this->db->get()->result();
	}
    
    /**------------------------------ USER ------------------------------**/
    
    // registra el usuario
	public function createUser($randomC, $data){
        $this->db->select('(max(id)+1000) as maxId');
        $this->db->from('user');
        $max = $this->db->get()->result()[0]->maxId;
        $newId = substr(strval($max), 0, -3).$randomC;
        $data['id'] = $newId;
        $this->db->insert('user', $data);
        return  1;
	}
    
    // obtiene la informacion del usuario
	public function getAccount($id){
		$this->db->select('user.name, user.fbid, user.signin, city.name as ciudad');
        $this->db->from('user');
        $this->db->join('city', 'user.idCity = city.id ');
        $this->db->where('user.id', $id);
        return  $this->db->get()->result();
	}
    // obtiene la informacion del usuario
	public function getAccountCommerces($id){
        $this->db->select('commerce.id, commerce.name, commerce.description, commerce.image, xref_user_commerce.points');
        $this->db->select('(select count(*) from reward where reward.idCommerce = commerce.id and reward.points <= xref_user_commerce.points) as avaliable', false);
        $this->db->select('(select count(*) from reward where reward.idCommerce = commerce.id) as rewards', false);
        $this->db->select('(select count(*) from log_user_checkin where log_user_checkin.idUser = xref_user_commerce.idUser and log_user_checkin.idCommerce = commerce.id) as visits', false);
        $this->db->select('(select max(dateAction) from log_user_checkin where log_user_checkin.idUser = xref_user_commerce.idUser and log_user_checkin.idCommerce = commerce.id) as lastVisit', false);
        $this->db->from('xref_user_commerce');
        $this->db->join('commerce', 'xref_user_commerce.idCommerce = commerce.id');
        $this->db->where('xref_user_commerce.idUser', $id);
        $this->db->order_by('xref_user_commerce.points',"DESC");
        return  $this->db->get()->result();
	}

    /**------------------------------ REWARDS ------------------------------**/

	// obtiene los rewards
	public function getRewards($data){
		$this->db->select('reward.id, reward.name, reward.points, commerce.name as commerce');
        $this->db->select('xref_user_reward_fav.idReward as fav, ifnull(xref_user_commerce.points, 0) as userPoints', false);
        $this->db->from('reward');
        $this->db->join('commerce', 'reward.idCommerce = commerce.id ');
        $this->db->join('xref_user_commerce', 'reward.idCommerce = xref_user_commerce.idCommerce and xref_user_commerce.idUser = '.$data['idUser']);
        $this->db->join('xref_user_reward_fav', 'reward.id = xref_user_reward_fav.idReward and xref_user_reward_fav.idUser = '.$data['idUser'], 'left');
        $this->db->where('reward.status = 1');
        $this->db->where('commerce.status = 1');
        return  $this->db->get()->result();
	}

    // obtiene los rewards
	public function getRewardsByCommerce($idUser, $idCommerce){
		$this->db->select('reward.id, reward.name, reward.points, xref_user_reward_fav.idReward as fav');
        $this->db->from('reward');
        $this->db->join('xref_user_reward_fav', 'reward.id = xref_user_reward_fav.idReward and xref_user_reward_fav.idUser = '.$idUser, 'left');
        $this->db->where('reward.idCommerce = '.$idCommerce);
        $this->db->order_by('reward.points',"ASC");
        $this->db->where('reward.status = 1');
        return  $this->db->get()->result();
	}
    
    // obtiene los rewards
	public function getRewardsH($idUser, $idCommerce){
		$this->db->select('reward.id, reward.name, reward.image, reward.points, xref_user_reward_fav.idReward as fav');
        $this->db->from('reward');
        $this->db->join('xref_user_reward_fav', 'reward.id = xref_user_reward_fav.idReward and xref_user_reward_fav.idUser = '.$idUser, 'left');
        $this->db->where('reward.idCommerce = '.$idCommerce);
        $this->db->order_by('reward.points',"ASC");
        $this->db->where('reward.status = 1');
        return  $this->db->get()->result();
	}

    // obtiene los rewards con fav
	public function getRewardFavs($data, $filters){
		$this->db->select('reward.id, reward.name, reward.points, commerce.name as commerce');
        $this->db->select('xref_user_reward_fav.idReward as fav, xref_user_commerce.points as userPoints', false);
        $this->db->from('xref_user_reward_fav');
        $this->db->join('reward', 'xref_user_reward_fav.idReward = reward.id and xref_user_reward_fav.idUser = '.$data['idUser'], 'left');
        $this->db->join('commerce', 'reward.idCommerce = commerce.id ');
        $this->db->join('xref_user_commerce', 'reward.idCommerce = xref_user_commerce.idCommerce and xref_user_commerce.idUser = '.$data['idUser'], 'left');
        if ($filters != '1'){
            $this->db->join('xref_commerce_categories', 'commerce.id = xref_commerce_categories.idCommerce  and xref_commerce_categories.idCategory in ( '.$filters.' )');
        }
        $this->db->where('reward.status = 1');
        $this->db->where('commerce.status = 1');
        return  $this->db->get()->result();
	}

    // insert fav reward
	public function insertRewardFav($data){
		$this->db->insert('xref_user_reward_fav', $data);
    return  1;
	}

    // delete fav reward
	public function deleteRewardFav($data){
		$this->db->delete('xref_user_reward_fav', $data);
    return  1;
	}

    /**------------------------------ REWARD ------------------------------**/

    // obtiene el reward
	public function getReward($idUser, $idReward){
		$this->db->select('reward.id, reward.name, reward.description, reward.terms, reward.points');
        $this->db->select('reward.vigency, reward.image, reward.idCommerce');
        $this->db->select('commerce.name as commerce, commerce.description as commerceDesc, commerce.image as comImage');
        $this->db->select('palette.colorA1, palette.colorA2, palette.colorA3');
        $this->db->select('xref_user_reward_fav.idReward as fav, ifnull(xref_user_commerce.points, 0) as userPoints, ifnull(xref_user_commerce.idCommerce, 0) as isCommerce', false);
        $this->db->from('reward');
        $this->db->join('commerce', 'reward.idCommerce = commerce.id ');
        $this->db->join('xref_user_reward_fav', 'reward.id = xref_user_reward_fav.idReward and xref_user_reward_fav.idUser = '.$idUser, 'left');
        $this->db->join('xref_user_commerce', 'reward.idCommerce = xref_user_commerce.idCommerce and xref_user_commerce.idUser = '.$idUser, 'left');
        $this->db->join('palette', 'commerce.idPalette = palette.id ');
        $this->db->where('reward.id', $idReward);
        return  $this->db->get()->result();
	}

    /**------------------------------ COMMERCE ------------------------------**/

    // obtiene los comercios destacados
	public function getCommerceFlow(){
        $this->db->select('commerce.id, commerce.name, commerce.description, commerce.image');
        $this->db->select('palette.colorA1, palette.colorA2, palette.colorA3');
        $this->db->select('palette.colorB1, palette.colorB2, palette.colorB3');
        $this->db->from('commerce');
        $this->db->join('palette', 'commerce.idPalette = palette.id ');
        $this->db->where('commerce.important = 1');
        $this->db->where('commerce.status = 1');
        return  $this->db->get()->result();
	}

    // obtiene los comercios
	public function getCommerces($idUser, $filters){
        $this->db->select('commerce.id, commerce.name, commerce.description, commerce.address, commerce.lat, commerce.long, commerce.image');
        $this->db->select('palette.colorA1, palette.colorA2, palette.colorA3');
        $this->db->select('xref_user_commerce.points as afil');
        $this->db->from('commerce');
        $this->db->join('palette', 'commerce.idPalette = palette.id ');
        $this->db->join('xref_user_commerce', 'commerce.id = xref_user_commerce.idCommerce  and xref_user_commerce.idUser = '.$idUser, 'left');
        if ($filters != '1'){
            $this->db->join('xref_commerce_categories', 'commerce.id = xref_commerce_categories.idCommerce  and xref_commerce_categories.idCategory in ( '.$filters.' )');
        }
        $this->db->where('commerce.status = 1');
        $this->db->group_by('commerce.id'); 
        $this->db->order_by("commerce.name", "asc");
        return  $this->db->get()->result();
	}
    
    // obtiene los comercios
	public function getCommercesByGPS($lat1, $lat2, $lon1, $lon2){
        $this->db->select('commerce.id, commerce.name, commerce.description, commerce.image');
        $this->db->select('palette.colorA1, palette.colorA2, palette.colorA3');
        $this->db->from('commerce');
        $this->db->join('palette', 'commerce.idPalette = palette.id ');
        $this->db->where('commerce.status = 1');
        $this->db->where('(commerce.lat > '.$lat1.' AND commerce.lat < '.$lat2.')', '', false);
        $this->db->where('(commerce.long > '.$lon1.' AND commerce.long < '.$lon2.')', '', false);
        $this->db->group_by('commerce.id'); 
        $this->db->order_by("commerce.name", "asc");
        return  $this->db->get()->result();
	}
    
    // obtiene los comercios
	public function getCommercesByGPSLite($lat1, $lat2, $lon1, $lon2){
        $this->db->select('commerce.id, commerce.name, idCategory, commerce.description, commerce.address, commerce.lat, commerce.long');
        $this->db->from('commerce');
        $this->db->join('xref_commerce_categories', 'commerce.id = xref_commerce_categories.idCommerce');
        $this->db->where('commerce.status = 1');
        $this->db->where('(commerce.lat > '.$lat1.' AND commerce.lat < '.$lat2.')', '', false);
        $this->db->where('(commerce.long > '.$lon1.' AND commerce.long < '.$lon2.')', '', false);
        $this->db->group_by('commerce.id'); 
        return  $this->db->get()->result();
	}

    // obtiene los comercios
	public function getCommercesWCat($filters){
        $this->db->select('commerce.id, commerce.name, commerce.description, commerce.image');
        $this->db->select('palette.colorA1, palette.colorA2, palette.colorA3');
        $this->db->from('commerce');
        $this->db->join('palette', 'commerce.idPalette = palette.id ');
        if ($filters != '1'){
            $this->db->join('xref_commerce_categories', 'commerce.id = xref_commerce_categories.idCommerce  and xref_commerce_categories.idCategory in ( '.$filters.' )');
        }
        $this->db->where('commerce.status = 1');
        $this->db->group_by('commerce.id'); 
        $this->db->order_by("commerce.name", "asc");
        return  $this->db->get()->result();
	}
    
    // obtiene los comercios afiliados
	public function getJoined($idUser, $filters){
        $this->db->select('commerce.id, commerce.name, commerce.description, commerce.image');
        $this->db->select('palette.colorA1, palette.colorA2, palette.colorA3');
        $this->db->select('xref_user_commerce.points as points');
        $this->db->select('(select count(*) from reward where reward.idCommerce = commerce.id and reward.status = 1) as rewards', false);
        $this->db->select('(select count(*) from reward where reward.idCommerce = commerce.id and reward.points <= xref_user_commerce.points and reward.status = 1) as posible', false);
        $this->db->from('commerce');
        $this->db->join('palette', 'commerce.idPalette = palette.id ');
        $this->db->join('xref_user_commerce', 'commerce.id = xref_user_commerce.idCommerce  and xref_user_commerce.idUser = '.$idUser);
        if ($filters != '1'){
            $this->db->join('xref_commerce_categories', 'commerce.id = xref_commerce_categories.idCommerce  and xref_commerce_categories.idCategory in ( '.$filters.' )');
        }
        $this->db->where('commerce.status = 1');
        $this->db->group_by('commerce.id'); 
        $this->db->order_by("commerce.name", "asc");
        return  $this->db->get()->result();
	}
    
    // obtiene los comercios afiliados
	public function getComHome($idUser, $filters){
        $this->db->select('commerce.id, commerce.image, commerce.name, commerce.description, xref_user_commerce.points');
        $this->db->select('palette.colorA1, palette.colorA2, palette.colorA3');
        $this->db->from('commerce');
        $this->db->join('palette', 'commerce.idPalette = palette.id ');
        $this->db->join('xref_user_commerce', 'commerce.id = xref_user_commerce.idCommerce  and xref_user_commerce.idUser = '.$idUser, 'left');
        if ($filters != '1'){
            $this->db->join('xref_commerce_categories', 'commerce.id = xref_commerce_categories.idCommerce  and xref_commerce_categories.idCategory in ( '.$filters.' )');
        }
        $this->db->where('commerce.status = 1');
        $this->db->group_by('commerce.id'); 
        $this->db->order_by("commerce.name", "asc");
        return  $this->db->get()->result();
	}
    
    // obtiene los comercios afiliados
	public function getJoinedLite($idUser, $filters){
        $this->db->select('commerce.id, commerce.name, xref_user_commerce.points');
        $this->db->from('commerce');
        $this->db->join('xref_user_commerce', 'commerce.id = xref_user_commerce.idCommerce  and xref_user_commerce.idUser = '.$idUser, 'left');
        if ($filters != '1'){
            $this->db->join('xref_commerce_categories', 'commerce.id = xref_commerce_categories.idCommerce  and xref_commerce_categories.idCategory in ( '.$filters.' )');
        }
        $this->db->where('commerce.status = 1');
        $this->db->group_by('commerce.id'); 
        $this->db->order_by("commerce.name", "asc");
        return  $this->db->get()->result();
	}

    // obtiene los comercios destacados
	public function getCommerce($idUser, $idCommerce){
        $this->db->select('commerce.id, commerce.name, commerce.description, commerce.address, commerce.lat, commerce.long');
        $this->db->select('commerce.image, commerce.banner, commerce.phone, commerce.web, commerce.facebook, commerce.twitter');
        $this->db->select('palette.colorA1, palette.colorA2, palette.colorA3');
        $this->db->select('xref_user_commerce.points as points');
        $this->db->select('xref_user_commerce_fav.idCommerce as fav');
        $this->db->select('(select count(*) from reward where reward.idCommerce = commerce.id and reward.points <= xref_user_commerce.points) as avaliable', false);
        $this->db->select('(select count(*) from reward where reward.idCommerce = commerce.id) as rewards', false);
        $this->db->select('(select count(*) from log_user_checkin where log_user_checkin.idUser = xref_user_commerce.idUser and log_user_checkin.idCommerce = commerce.id) as visits', false);
        $this->db->select('(select max(dateAction) from log_user_checkin where log_user_checkin.idUser = xref_user_commerce.idUser and log_user_checkin.idCommerce = commerce.id) as lastVisit', false);
        $this->db->from('commerce');
        $this->db->join('palette', 'commerce.idPalette = palette.id ');
        $this->db->join('xref_user_commerce', 'commerce.id = xref_user_commerce.idCommerce  and xref_user_commerce.idUser = '.$idUser, 'left');
        $this->db->join('xref_user_commerce_fav', 'commerce.id = xref_user_commerce_fav.idCommerce and xref_user_commerce_fav.idUser = '.$idUser, 'left');
        $this->db->where('commerce.status = 1');
        $this->db->where('commerce.id', $idCommerce);
        $this->db->order_by("commerce.name", "asc");
        return  $this->db->get()->result();
	}

    // obtiene los comercios destacados
	public function getCommercePhotos($idCommerce){
        $this->db->select('image');
        $this->db->from('xref_commerce_photos');
        $this->db->where('xref_commerce_photos.idCommerce', $idCommerce);
        return  $this->db->get()->result();
	}

    // insert fav reward
	public function insertCommerceFav($data){
		$this->db->insert('xref_user_commerce_fav', $data);
        return  1;
	}

    // delete fav reward
	public function deleteCommerceFav($data){
		$this->db->delete('xref_user_commerce_fav', $data);
        return  1;
	}

    /**------------------------------ WALLET ------------------------------**/

    // obtiene los rewards con fav
	public function getWallet($idUser){
		$this->db->select('reward.id, reward.name, reward.image');
        $this->db->select('xref_user_wallet.dateReden, xref_user_wallet.status');
		$this->db->select('commerce.name as commerce');
        $this->db->from('xref_user_wallet');
        $this->db->join('reward', 'xref_user_wallet.idReward = reward.id and xref_user_wallet.idUser = '.$idUser, 'left');
        $this->db->join('commerce', 'reward.idCommerce = commerce.id ');
        $this->db->where('reward.status = 1');
        $this->db->where('commerce.status = 1');
        $this->db->order_by("xref_user_wallet.status, xref_user_wallet.dateReden", "asc");
        return  $this->db->get()->result();
	}

    /**------------------------------ MESSAGES ------------------------------**/

    // obtiene los rewards con fav
	public function getMessages($idUser){
		$this->db->select('message.id, message.dateIncome, message.status');
        $this->db->select("ifnull(reward.name, message.name) as name", false);
        $this->db->select('commerce.name as commerce, commerce.image, user.fbid');
        $this->db->select('user.name as user');
        $this->db->from('message');
        $this->db->join('commerce', 'message.fromCommerce = commerce.id', 'left');
        $this->db->join('user', 'message.fromUser = user.id', 'left');
        $this->db->join('reward', 'message.idReward = reward.id', 'left');
        $this->db->where('message.status > 0');
        $this->db->where('message.idUser', $idUser);
        $this->db->order_by("message.status", "asc");
        $this->db->order_by("message.dateIncome", "desc");
        return  $this->db->get()->result();
	}

    // obtiene los rewards con fav
	public function getMessage($idMessage){
        $this->db->select('message.id, message.dateIncome, message.status');
        $this->db->select("ifnull(reward.name, message.name) as name", false);
        $this->db->select("ifnull(reward.image, message.image) as image", false);
        $this->db->select("ifnull(reward.description, message.description) as description", false);
        $this->db->select('commerce.name as commerce');
        $this->db->select('user.name as user');
        $this->db->from('message');
        $this->db->join('commerce', 'message.fromCommerce = commerce.id', 'left');
        $this->db->join('user', 'message.fromUser = user.id', 'left');
        $this->db->join('reward', 'message.idReward = reward.id', 'left');
        $this->db->where('message.id', $idMessage);
        return  $this->db->get()->result();
	}

  /**------------------------------ UNIFY COMMERCE ------------------------------**/

  // registra el usuario
	public function insertUser($data){
    $this->db->insert('user', $data);
    return  1;
	}
    
    // registra el usuario
	public function insertUserLog($data){
        $this->db->insert('user_log', $data);
        return  1;
	}
    
    // registra el usuario
	public function logNewUser($data){
        $this->db->insert('log_new_user', $data);
        return  1;
	}
    // registra el usuario
	public function logNewUserCom($data){
        $this->db->insert('log_new_user_commerce', $data);
        return  1;
	}
    // registra el usuario
	public function logCheckin($data){
        $this->db->insert('log_user_checkin', $data);
        return  1;
	}
    
  // actualiza el usuario
	public function updateUser($id, $data){
    $this->db->where('id', $id);
    $this->db->update('user', $data);
    return  1;
	}

  // obtiene el usuario
	public function updateUserLastCheckin($idUser, $data){
    $this->db->where('id', $idUser);
    $this->db->update('user', $data);
	}

  // obtiene el usuario
	public function getUser($idUser){
    $this->db->select("*, TIMESTAMPDIFF(hour, dateAction, '".date('y-m-d h:i:s')."') as numhours", false);
    $this->db->from('user');
    $this->db->where('user.id = '.$idUser);
    return  $this->db->get()->result();
	}
    
    // obtiene el usuario
	public function getUserKey($idUser){
        $this->db->from('user');
        $this->db->where('id', $idUser);
        return  $this->db->get()->result();
	}
    
    // obtiene el usuario
	public function newUserApp($fbid, $email, $name){
        $this->db->select("max(id)+1 as maxId");
        $this->db->from('user');
        $newUser =  $this->db->get()->result()[0]->maxId;
        
        $user = array(
            'id' => $newUser,
            'fbid' => $fbid,
            'email' => $email,
            'name' => $name,
            'status' => 1);
        $this->db->insert('user', $user);
        return $user;
	}
    
    // obtiene el usuario
	public function getUserFbid($fbid){
        $this->db->select('*', false);
        $this->db->select('(select count(*) from xref_user_commerce where xref_user_commerce.idUser = user.id) as totalCom', false);
        $this->db->from('user');
        $this->db->where('user.fbid', $fbid);
        return  $this->db->get()->result();
	}
    
    // obtiene el usuario
	public function getUserEmail($email){
        $this->db->from('user');
        $this->db->where('email', $email);
        return  $this->db->get()->result();
	}
    
    // obtiene el usuario
	public function getUserEmailPass($email, $pass){
        $this->db->select('*', false);
        $this->db->select('(select count(*) from xref_user_commerce where xref_user_commerce.idUser = user.id) as totalCom', false);
        $this->db->from('user');
        $this->db->where('email', $email);
        $this->db->where('password', $pass);
        return  $this->db->get()->result();
	}

  // registra el usuario y comercio
	public function insertUserCommerce($data){
    $this->db->insert('xref_user_commerce', $data);
    return  1;
	}

  // obtiene el usuario por usario
	public function getUserCommerce($idUser, $idCommerce){
    $this->db->select('user.id, user.name, user.email, xref_user_commerce.points');
    $this->db->from('user');
    $this->db->join('xref_user_commerce', 'xref_user_commerce.idUser = user.id and xref_user_commerce.idCommerce = '.$idCommerce);
    $this->db->where('user.id = '.$idUser);
    return  $this->db->get()->result();
	}

  // actualiza los puntos
	public function setUserPoints($idUser, $idCommerce, $data){
    $this->db->where('idUser', $idUser);
    $this->db->where('idCommerce', $idCommerce);
    $this->db->update('xref_user_commerce', $data);
  }

  // actualiza los puntos
	public function insertUserPoints($data){
    $this->db->insert('xref_user_commerce', $data);
  }

  // obtiene el usuario
	public function getUserPoints($idUser, $idCommerce){
    $this->db->select('user.id, user.name, xref_user_commerce.idCommerce, xref_user_commerce.points');
    $this->db->from('user');
    $this->db->join('xref_user_commerce', 'xref_user_commerce.idUser = user.id and xref_user_commerce.idCommerce = '.$idCommerce, 'left');
    $this->db->where('user.id = '.$idUser);
    $this->db->where('user.status = 1');
    return  $this->db->get()->result();
	}

  // obtiene los rewards
	public function getComRewards($idCommerce){
    $this->db->select('reward.id, reward.name, reward.description, reward.points');
    $this->db->from('reward');
    $this->db->where('reward.idCommerce = '.$idCommerce);
    $this->db->where('reward.status = 1');
    $this->db->order_by('reward.points',"ASC");
    return  $this->db->get()->result();
	}
    
    // obtiene el cashier
	public function getCashier($idCashier, $idCommerce){
        $this->db->select('id, name');
        $this->db->from('cashier');
        $this->db->where('id', $idCashier);
        $this->db->where('idCommerce', $idCommerce);
        $this->db->where('status = 1');
        return  $this->db->get()->result();
	}
    
    // obtiene los rewards canjeados del comercio
	public function getRedemRewards($idCommerce){
        $this->db->select('redemption.id, redemption.dateChange, redemption.dateRedemption');
        $this->db->select('redemption.dateCancelation, redemption.status');
        $this->db->select('reward.name as reward, reward.points, reward.description, user.name as user');
        $this->db->from('redemption');
        $this->db->join('reward', 'redemption.idReward = reward.id', 'left');
        $this->db->join('user', 'redemption.idUser = user.id', 'left');
        $this->db->where('redemption.idCommerce = '.$idCommerce);
        $this->db->where('redemption.status = 1');
        $this->db->limit(10);
        return  $this->db->get()->result();
    }
    
    // obtiene los rewards canjeados del comercio
    public function insertRedemption($data){
        $this->db->insert('redemption', $data);
        return  1;
	}
    
    // actualiza el usuario
	public function updateRedemption($id, $data){
        $this->db->where('id', $id);
        $this->db->update('redemption', $data);
        return  1;
	}
    
    // obtiene los rewards canjeados del comercio
    public function getRedemption($id){
        $this->db->from('redemption');
        $this->db->where('id', $id);
        return  $this->db->get()->result();
	}


}
//end model
