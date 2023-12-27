<?php

class Stock_model extends CI_Model{

	public function get_last_stocks() {
        $this->db->select('nemonico, MAX(date) as last_date');
        $this->db->from('stock');
        $this->db->group_by('nemonico');
        $query = $this->db->get();

        if ($query->num_rows() > 0) return $query->result();
        else return [];
    }

}
?>
