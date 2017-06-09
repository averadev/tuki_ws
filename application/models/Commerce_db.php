<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
Class Commerce_db extends CI_MODEL
{

    public function __construct(){
        parent::__construct();
    }

    // obtiene el usuario
	public function getUser($idUser){
        $this->db->from('user');
        $this->db->where('user.id = '.$idUser);
        return  $this->db->get()->result();
	}
    
    // registra el usuario
	public function insertUser($data){
        $this->db->insert('user', $data);
        return  1;
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
    
    // registra el usuario
	public function logNewUserCom($data){
        $this->db->insert('log_new_user_commerce', $data);
        return  1;
	}
    
    // obtiene el usuario
	public function getNewUserCom($idUser, $idCommerce){
        $this->db->select('idBranch');
        $this->db->from('log_new_user_commerce');
        $this->db->where('idUser', $idUser);
        $this->db->where('idCommerce', $idCommerce);
        return  $this->db->get()->result();
	}
    
    // actuliza la sucursal
	public function updateNewUserBranch($idUser, $idCommerce, $data){
        $this->db->where('idUser', $idUser);
        $this->db->where('idCommerce', $idCommerce);
        $this->db->update('log_new_user_commerce', $data);
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
    
    // registra el usuario
	public function logCheckin($data){
        $this->db->insert('log_user_checkin', $data);
        return  1;
	}
    
    // obtiene el usuario
	public function getUserPoints($idUser, $idCommerce){
        $this->db->select('user.id, user.name, xref_user_commerce.idCommerce, xref_user_commerce.points');
        $this->db->select("TIMESTAMPDIFF(hour, (select max(log_user_checkin.dateAction) from  log_user_checkin join branch on log_user_checkin.idBranch = branch.id where log_user_checkin.idUser = xref_user_commerce.idUser and branch.idCommerce = xref_user_commerce.idCommerce ), now()) as numhours", false);
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
    
    // actualiza los puntos
	public function insertUserPoints($data){
        $this->db->insert('xref_user_commerce', $data);
    }
    
    // actualiza los puntos
	public function setUserPoints($idUser, $idCommerce, $data){
        $this->db->where('idUser', $idUser);
        $this->db->where('idCommerce', $idCommerce);
        $this->db->update('xref_user_commerce', $data);
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
	public function getRedemRewards($idBranch){
        $this->db->select('redemption.id, redemption.dateChange, redemption.dateRedemption');
        $this->db->select('redemption.dateCancelation, redemption.status');
        $this->db->select('reward.name as reward, reward.points, reward.description, user.name as user, user.fbid');
        $this->db->from('redemption');
        $this->db->join('reward', 'redemption.idReward = reward.id', 'left');
        $this->db->join('user', 'redemption.idUser = user.id', 'left');
        $this->db->where('redemption.idBranch', $idBranch);
        $this->db->where('redemption.status = 1');
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
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**------------------------------ APP COMMERCE ------------------------------**/
    
    // obtiene el reward
	public function verifyPassword($password){
        $this->db->select('branch.id as idBranch, branch.idCommerce, commerce.image');
        $this->db->from('branch');
        $this->db->join('commerce', 'branch.idCommerce = commerce.id');
        $this->db->where('password', $password);
        return  $this->db->get()->result();
	}
    
    // obtiene el reward
	public function validateExit($idBranch, $password){
        $this->db->select('id');
        $this->db->from('branch');
        $this->db->where('id', $idBranch);
        $this->db->where('password', $password);
        return  $this->db->get()->result();
	}
    
    // obtiene el logo
	public function getlogo($idCommerce){
        $this->db->select('image');
        $this->db->from('commerce');
        $this->db->where('id', $idCommerce);
        return  $this->db->get()->result();
	}
    
    // obtiene el reward
	public function getReward($idReward){
        $this->db->select('reward.id, reward.name, reward.description, reward.image, reward.points');
        $this->db->from('reward');
        $this->db->where('reward.id', $idReward);
        return  $this->db->get()->result();
	}
    
    // obtiene los rewards
	public function getRewards($idCommerce){
        $this->db->select('reward.id, reward.name, reward.description, reward.image, reward.points');
        $this->db->from('reward');
        $this->db->where('reward.idCommerce = '.$idCommerce);
        $this->db->where('reward.status = 1');
        $this->db->order_by('reward.points',"ASC");
        return  $this->db->get()->result();
	}
    
    // obtiene el usuario
	public function isCashier($idUser){
        $this->db->select('cashier.id');
        $this->db->from('cashier');
        $this->db->where('cashier.idCard', $idUser);
        $this->db->where('status = 1');
        return  $this->db->get()->result();
	}
    
    // obtiene el usuario
	public function isCashierBranch($idUser, $idBranch){
        $this->db->select('commerce_user.id, commerce_user.nombre');
        $this->db->from('cashier');
        $this->db->join('commerce_user', 'cashier.idComUser = commerce_user.id');
        $this->db->where('cashier.idCard', $idUser);
        $this->db->where('commerce_user.idBranch', $idBranch);
        $this->db->where('cashier.status = 1');
        return  $this->db->get()->result();
	}
    
    // obtiene el usuario
	public function isUser($idUser){
        $this->db->select('id, name');
        $this->db->from('user');
        $this->db->where('user.id = '.$idUser);
        return  $this->db->get()->result();
	}
    
    // obtiene puntos del usuario
	public function checkPoints($idUser, $idCommerce){
        $this->db->select('xref_user_commerce.idUser, xref_user_commerce.points');
        $this->db->from('xref_user_commerce');
        $this->db->where('idUser', $idUser);
        $this->db->where('idCommerce', $idCommerce);
        return  $this->db->get()->result();
	}
    
    // obtiene el usuario
	public function isUserCommerce($idUser, $idCommerce){
        $this->db->select('xref_user_commerce.idUser, xref_user_commerce.points');
        $this->db->select("TIMESTAMPDIFF(hour, (select max(log_user_checkin.dateAction) from  log_user_checkin join branch on log_user_checkin.idBranch = branch.id where log_user_checkin.idUser = xref_user_commerce.idUser and branch.idCommerce = xref_user_commerce.idCommerce ), now()) as numhours", false);
        $this->db->from('xref_user_commerce');
        $this->db->where('idUser', $idUser);
        $this->db->where('idCommerce', $idCommerce);
        return  $this->db->get()->result();
	}

    // obtiene el usuario por usario
	public function userPoints($idUser, $idCommerce){
        $this->db->select('user.id, user.name, xref_user_commerce.points');
        $this->db->from('user');
        $this->db->join('xref_user_commerce', 'xref_user_commerce.idUser = user.id and xref_user_commerce.idCommerce = '.$idCommerce);
        $this->db->where('user.id = '.$idUser);
        return  $this->db->get()->result();
	}
    
    // obtiene el usuario
	public function isLinkCard($idCard){
        $this->db->select('idUser');
        $this->db->from('xref_user_card');
        $this->db->where('idCard', $idCard);
        return  $this->db->get()->result();
	}
    
    // obtiene el esquema de la sucursal
	public function getSchema($idBranch){
        $this->db->select('commerce.squema');
        $this->db->from('branch');
        $this->db->join('commerce', 'branch.idCommerce = commerce.id');
        $this->db->where('branch.id', $idBranch);
        return  $this->db->get()->result();
	}
    
    // obtiene la ciudad de la sucursal
	public function getBranchCity($idBranch){
        $this->db->select('branch.idCity');
        $this->db->from('branch');
        $this->db->where('branch.id', $idBranch);
        return  $this->db->get()->result();
	}
    
    // verifica si es un regalo
	public function isGift($idReward){
        $this->db->from('reward');
        $this->db->where('id', $idReward);
        $this->db->where('status = -1');
        return  $this->db->get()->result();
	}
    
    // verifica si es un regalo
	public function isDisabledGift($idUser, $idReward){
        $this->db->from('xref_user_wallet');
        $this->db->where('idUser', $idUser);
        $this->db->where('idReward', $idReward);
        $this->db->where('status = 3');
        return  $this->db->get()->result();
	}
    
    // deshabilita regalo del usuario
	public function changeGift($idUser, $idReward, $status){
        $this->db->where('idUser', $idUser);
        $this->db->where('idReward', $idReward);
        $this->db->update('xref_user_wallet', array('status' => $status));
	}
    
    // obtiene empleado a cargo
	public function getCheckEmp($idBranch){
        $this->db->select("commerce_user.id, ifnull(commerce_user.nombre, '') as nombre", false);
        $this->db->from('branch');
        $this->db->join('commerce_user', 'branch.currentEmp = commerce_user.id', 'left');
        $this->db->where('branch.id', $idBranch);
        return  $this->db->get()->result();
	}
    
    // deshabilita regalo del usuario
	public function setCheckEmp($idBranch, $data){
        $this->db->where('id', $idBranch);
        $this->db->update('branch', $data);
	}
    
    // registra nuevo cajero a cargo
	public function logBranchComUser($data){
        $this->db->insert('log_branch_comuser', $data);
        return  1;
	}
    
    // registra nuevo cajero a cargo
	public function logBranchDevice($data){
        $this->db->insert('log_branch_device', $data);
        return  1;
	}

}
//end model
