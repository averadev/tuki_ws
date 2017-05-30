<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
Class Monitor_db extends CI_MODEL
{

    public function __construct(){
        parent::__construct();
    }

    // obtiene informacion de los nuevos usuarios
	public function getNewUser($idCommerce, $date, $range){
		$this->db->select("max(date_format(dateAction, '%d %b %Y')) as dateAction, count(idUser) as total", false);
        $this->db->from('log_new_user_commerce');
        $this->db->where('idCommerce', $idCommerce);
        $this->db->where('log_new_user_commerce.dateAction >=', $date);        
        // Group By
        if ($range == '3M'){
            $this->db->group_by('week(log_new_user_commerce.dateAction)'); 
        }elseif ($range == 'Todo'){
            $this->db->group_by("concat(year(log_new_user_commerce.dateAction), month(log_new_user_commerce.dateAction))", FALSE); 
        }else{
            $this->db->group_by('day(log_new_user_commerce.dateAction)'); 
        }
        $this->db->order_by('log_new_user_commerce.dateAction');
        return  $this->db->get()->result();
	}
    
    // obtiene informacion de los puntos otorgados
	public function getPoints($idCommerce, $date, $range){
		$this->db->select("max(date_format(dateAction, '%d %b %Y')) as dateAction, sum(points) as total", false);
        $this->db->from('log_user_checkin');
        $this->db->join('branch', 'log_user_checkin.idBranch = branch.id');
        $this->db->where('branch.idCommerce', $idCommerce);
        $this->db->where('log_user_checkin.dateAction >=', $date);        
        // Group By
        if ($range == '3M'){
            $this->db->group_by('week(log_user_checkin.dateAction)'); 
        }elseif ($range == 'Todo'){
            $this->db->group_by("concat(year(log_user_checkin.dateAction), month(log_user_checkin.dateAction))", FALSE); 
        }else{
            $this->db->group_by('day(log_user_checkin.dateAction)'); 
        }
        $this->db->order_by('log_user_checkin.dateAction');
        return  $this->db->get()->result();
	}
    
    // obtiene informacion de las recompensas canjeadas
	public function getRedem($idCommerce, $date, $range){
		$this->db->select("max(date_format(dateChange, '%d %b %Y')) as dateAction, count(idUser) as total", false);
        $this->db->from('redemption');
        $this->db->join('branch', 'redemption.idBranch = branch.id');
        $this->db->where('branch.idCommerce', $idCommerce);
        $this->db->where('dateChange >=', $date);
        $this->db->where('dateCancelation is null');        
        // Group By
        if ($range == '3M'){
            $this->db->group_by('week(dateChange)'); 
        }elseif ($range == 'Todo'){
            $this->db->group_by("concat(year(dateChange), month(dateChange))", FALSE); 
        }else{
            $this->db->group_by('day(dateChange)'); 
        }
        $this->db->order_by('dateChange');
        return  $this->db->get()->result();
	}
    
    // obtiene totales sobre los nuevos usuarios
	public function getNewUserT_1M($idCommerce, $date, $range){
        if ($range == '1S' ){
            $this->db->select("count(*) as total, weekday(ifnull(max(dateAction),now())) + 1 as day, 7 as lastDay", false);
        }elseif ($range == '1M'){
            $this->db->select("count(*) as total, day(ifnull(max(dateAction),now())) as day, day(last_day(dateAction)) as lastDay", false);
        }else{
            $this->db->select("count(*) as total", false);
        }
        $this->db->from('log_new_user_commerce');
        $this->db->where('idCommerce', $idCommerce);
        $this->db->where('log_new_user_commerce.dateAction >=', $date);
        return  $this->db->get()->result();
	}
    
    // obtiene totales sobre los puntos otorgados
	public function getPointsT_1M($idCommerce, $date, $range){
        if ($range == '1S' ){
            $this->db->select("count(*) as total, weekday(ifnull(max(dateAction),now())) + 1 as day, 7 as lastDay", false);
        }elseif ($range == '1M'){
            $this->db->select("sum(points) as total, day(ifnull(max(dateAction),now())) as day, day(last_day(dateAction)) as lastDay", false);
        }else{
            $this->db->select("sum(points) as total", false);
        }
        $this->db->from('log_user_checkin');
        $this->db->join('branch', 'log_user_checkin.idBranch = branch.id');
        $this->db->where('branch.idCommerce', $idCommerce);
        $this->db->where('log_user_checkin.dateAction >=', $date);
        return  $this->db->get()->result();
	}
    
    // obtiene totales sobre las recompensas canjeadas
	public function getRedemT_1M($idCommerce, $date, $range){
        if ($range == '1S' ){
            $this->db->select("count(*) as total, weekday(ifnull(max(dateChange),now())) + 1 as day, 7 as lastDay", false);
        }elseif ($range == '1M'){
            $this->db->select("count(*) as total, day(ifnull(max(dateChange),now())) as day, day(last_day(dateChange)) as lastDay", false);
        }else{
            $this->db->select("count(*) as total", false);
        }
        $this->db->from('redemption');
        $this->db->join('branch', 'redemption.idBranch = branch.id');
        $this->db->where('branch.idCommerce', $idCommerce);
        $this->db->where('dateChange >=', $date);
        $this->db->where('dateCancelation is null');
        return  $this->db->get()->result();
	}
    


}
//end model
