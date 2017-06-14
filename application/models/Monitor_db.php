<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
Class Monitor_db extends CI_MODEL
{

    public function __construct(){
        parent::__construct();
    }
    
    /**------------------------------ DATOS POR FECHA ------------------------------**/

    // obtiene informacion de los nuevos usuarios
	public function getNewUserD($idBranchs, $date, $range){
		$sql = "SELECT max(date_format(all_dates.dates, '%d %b %Y')) as dateAction, count(idUser) as total ";
        if ($range == '1S' ){
            $sql = $sql.", weekday(all_dates.dates) + 1 as weekday ";
        }
        $sql = $sql."FROM all_dates ";
        $sql = $sql."LEFT JOIN ( SELECT date(dateAction) as dateD, idUser, idBranch FROM log_user_checkin WHERE idBranch in (".$idBranchs.") GROUP BY idUser ) AS rangeD ON all_dates.dates = dateD ";
        $sql = $sql."WHERE all_dates.dates >= '".$date."' and all_dates.dates <= current_date() ";
        if ($range == '3M'){
            $sql = $sql."GROUP BY week(all_dates.dates) ";
        }elseif ($range == 'Todo'){
            $sql = $sql."GROUP BY concat(year(all_dates.dates), month(all_dates.dates)) ";
        }else{
            $sql = $sql."GROUP BY all_dates.dates ";
        }
        $sql = $sql."ORDER BY all_dates.dates";
        
        $query = $this->db->query($sql);
        return $query->result();
	}
    
    // obtiene informacion de los puntos otorgados
	public function getPointsD($idBranch, $date, $range){
		$this->db->select("ifnull(sum(points), 0) as total", false);
        if ($range == '1S' || $range == '1M'){
            if ($range == '1S' ){
                $this->db->select("weekday(all_dates.dates) + 1 as weekday", false);
            }
            $this->db->select("max(date_format(all_dates.dates, '%d %b %Y')) as dateAction", false);
            $this->db->from('all_dates');
            $this->db->join('log_user_checkin', 'all_dates.dates = date(log_user_checkin.dateAction) and `idBranch` in ('.$idBranch.') ', 'left');
            $this->db->where('all_dates.dates >=', $date);    
            $this->db->where('all_dates.dates <= date(now())'); 
        }else{
            $this->db->select("max(date_format(log_user_checkin.dateAction, '%d %b %Y')) as dateAction", false);
            $this->db->from('log_user_checkin');
            $this->db->where('idBranch in ('.$idBranch.')');
            $this->db->where('log_user_checkin.dateAction >=', $date);
        }
               
        // Group By
        if ($range == '3M'){
            $this->db->group_by('week(log_user_checkin.dateAction)'); 
            $this->db->order_by('log_user_checkin.dateAction');
        }elseif ($range == 'Todo'){
            $this->db->group_by("concat(year(log_user_checkin.dateAction), month(log_user_checkin.dateAction))", FALSE); 
            $this->db->order_by('log_user_checkin.dateAction');
        }else{
            $this->db->group_by('all_dates.dates'); 
            $this->db->order_by('all_dates.dates');
        }
        return  $this->db->get()->result();
	}
    
    // obtiene informacion de las recompensas canjeadas
	public function getRedemD($idBranch, $date, $range){
		$this->db->select("count(idUser) as total", false);
        if ($range == '1S' || $range == '1M'){
            if ($range == '1S' ){
                $this->db->select("weekday(all_dates.dates) + 1 as weekday", false);
            }
            $this->db->select("max(date_format(all_dates.dates, '%d %b %Y')) as dateAction", false);
            $this->db->from('all_dates');
            $this->db->join('redemption', 'all_dates.dates = date(dateChange) and idBranch in ('.$idBranch.') ', 'left');
            $this->db->where('all_dates.dates >=', $date);    
            $this->db->where('all_dates.dates <= date(now())');  
        }else{
            $this->db->select("max(date_format(dateChange, '%d %b %Y')) as dateAction", false);
            $this->db->from('redemption');
            $this->db->where('idBranch in ('.$idBranch.')');
            $this->db->where('dateChange >=', $date); 
        }
        $this->db->where('dateCancelation is null');        
        
        // Group By
        if ($range == '3M'){
            $this->db->group_by('week(dateChange)'); 
            $this->db->order_by('dateChange');
        }elseif ($range == 'Todo'){
            $this->db->group_by("concat(year(dateChange), month(dateChange))", FALSE); 
            $this->db->order_by('dateChange');
        }else{
            $this->db->group_by('all_dates.dates'); 
            $this->db->order_by('all_dates.dates');
        }
        
        return  $this->db->get()->result();
	}
    
    /**------------------------------ TOTALES ------------------------------**/
    
    
    // obtiene totales sobre los nuevos usuarios
	public function getNewUser($idBranch, $date, $range){
        $sql = "SELECT count(*) as total ";
        if ($range == '1S' ){
            $sql = $sql.", weekday(now()) + 1 as day, 7 as lastDay ";
        }elseif ($range == '1M'){
            $sql = $sql.", day(now()) as day, day(last_day(now())) as lastDay ";
        }
        $sql = $sql."FROM ( SELECT date(dateAction) as dateD, idUser, idBranch FROM log_user_checkin WHERE idBranch in (".$idBranch.") GROUP BY idUser ) AS rangeD ";
        $sql = $sql."WHERE dateD >= '".$date."'";
        
        $query = $this->db->query($sql);
        return $query->result();
        
        return  $this->db->get()->result();
	}
    
    // obtiene totales sobre los puntos otorgados
	public function getPoints($idBranch, $date, $range){
        if ($range == '1S' ){
            $this->db->select("ifnull(sum(points), 0) as total, weekday(ifnull(max(dateAction),now())) + 1 as day, 7 as lastDay", false);
        }elseif ($range == '1M'){
            $this->db->select("ifnull(sum(points), 0) as total, day(now()) as day, day(last_day(now())) as lastDay", false);
        }else{
            $this->db->select("ifnull(sum(points), 0) as total", false);
        }
        $this->db->from('log_user_checkin');
        $this->db->where('idBranch in ('.$idBranch.')');
        $this->db->where('log_user_checkin.dateAction >=', $date);
        return  $this->db->get()->result();
	}
    
    // obtiene totales sobre las recompensas canjeadas
	public function getRedem($idBranch, $date, $range){
        if ($range == '1S' ){
            $this->db->select("count(*) as total, weekday(ifnull(max(dateChange),now())) + 1 as day, 7 as lastDay", false);
        }elseif ($range == '1M'){
            $this->db->select("count(*) as total, day(now()) as day, day(last_day(now())) as lastDay", false);
        }else{
            $this->db->select("count(*) as total", false);
        }
        $this->db->from('redemption');
        $this->db->where('idBranch in ('.$idBranch.')');
        $this->db->where('dateChange >=', $date);
        $this->db->where('dateCancelation is null');
        return  $this->db->get()->result();
	}
    
    
    
    /**------------------------------ GENERALES ------------------------------**/
    
    // Obtiene metas de la sucursales
	public function getGoals($idBranch){
        $this->db->select('sum(weekNewUser) as weekNewUser, sum(weekPoints) as weekPoints');
        $this->db->select('sum(weekRedem) as weekRedem, sum(monthNewUser) as monthNewUser');
        $this->db->select('sum(monthPoints) as monthPoints, sum(monthRedem) as monthRedem');
        $this->db->from('xref_goal_branch');
        $this->db->where('idBranch in ('.$idBranch.')');
        return  $this->db->get()->result();
	}

    // obtiene el usuario del comercio
	public function getCommerceUser($email){
        $this->db->select('commerce_user.id, commerce_user.nombre as name, commerce_user.password');
        $this->db->select('commerce_user.idCommerce, commerce.name as comercio');
        $this->db->select('commerce_user.idBranch, branch.name as branch');
        $this->db->from('commerce_user');
        $this->db->join('commerce', 'commerce_user.idCommerce = commerce.id');
        $this->db->join('branch', 'commerce_user.idBranch = branch.id', 'left');
        $this->db->where('commerce_user.email', $email);
        return  $this->db->get()->result();
	}
    
    // Obtiene las sucursales del comercio
	public function getBranchs($id){
        $this->db->select('id, name');
        $this->db->from('branch');
        $this->db->where('idCommerce', $id);
        return  $this->db->get()->result();
	}
    
    // Obtiene las sucursales del comercio
	public function getCommerceBranchs($id){
        $this->db->select("GROUP_CONCAT(id SEPARATOR ', ') as idBranch", false);
        $this->db->from('branch');
        $this->db->where('idCommerce', $id);        
        $this->db->where('status = true');
        return  $this->db->get()->result();
	}
    


}
//end model
