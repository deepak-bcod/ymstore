<?php

defined('BASEPATH') or exit('No direct script access allowed');



###+------------------------------------------------------------------------------------------------

###| BCOD WEB SOLUTIONS PVT. LTD., MUMBAI [ www.bcod.co.in ]

###+------------------------------------------------------------------------------------------------

###| Code By - Ketan Vyas (ketanv@bcod.co.in)

###+------------------------------------------------------------------------------------------------

###| Date - Dec 2022

###+------------------------------------------------------------------------------------------------

use App\Services\Trackers\ShipmentStatusEnum;

class DashboardModel extends CI_Model {



    public function email_exists($email, $id='')

	{

		$this->db->where('email', $email);

		if($id!=''){

			$this->db->where('id !=', $id);

		}

		$query = $this->db->get('adminusers');

		if($query->num_rows() > 0) {

			return true;

		}

		else {

			return false;

		}

	}



    public function check_user_email_exists($curr_email, $id) {

        $this->db->where('email', $curr_email);

        $this->db->where('id', $id);

        $query = $this->db->get('adminusers');

        if($query->num_rows() > 0) {

			return true;

		}

		else {

			return false;

		}

    }



	public function check_user_exists($id) {

		$this->db->where('id', $id);

		$query = $this->db->get('adminusers');

		return $query->result_array();

	}



    public function update_admin_user_email($data)

    {

		$this->db->where($data['condition']);

		$query = $this->db->update('adminusers', $data['data']);

		if($query) return true;

		else return false;

    }



    public function check_user_password_exists($curr_pass, $id) {

        $this->db->where('password', $curr_pass);

        $this->db->where('id', $id);

        $query = $this->db->get('adminusers');

        if($query->num_rows() > 0) {

			return true;

		}

		else {

			return false;

		}

    }



    public function update_admin_user_password($data)

    {

		$this->db->where($data['condition']);

		$query = $this->db->update('adminusers', $data['data']);

		if($query) return true;

		else return false;

    }



    public function update_admin_user_details($conditions)

    {

		$this->db->where($conditions['condition']);

		$query = $this->db->update('adminusers', $conditions['data']);

		if($query) return true;

		else return false;

    }

	public function get_avg_ratings_by_merchant($merchant_id) {
        $is_badge_enabled = 1;
        if ($this->db->table_exists('publisher') && $this->db->field_exists('is_badge_enabled', 'publisher')) {
            $pub = $this->db->select('is_badge_enabled')->from('publisher')->where('id', (int)$merchant_id)->get()->row();
            if ($pub && isset($pub->is_badge_enabled)) {
                $is_badge_enabled = (int)$pub->is_badge_enabled;
            }
        }

        if ($this->db->table_exists('ym_merchant_reviews')) {
            $this->db->select('merchant_id, AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_merchant_reviews');
            $this->db->where('merchant_id', (int)$merchant_id);
            $this->db->where('LOWER(status)', 'published');
            $this->db->group_by('merchant_id');
            $query = $this->db->get();
            $row = $query->row();
            if ($row) {
                $row->is_badge_enabled = $is_badge_enabled;
            }
            return $row;
        } elseif ($this->db->table_exists('product_reviews')) {
            $this->db->select('merchant_id, AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('product_reviews');
            $this->db->where('merchant_id', (int)$merchant_id);
            if ($this->db->field_exists('status', 'product_reviews')) {
                $this->db->where('LOWER(status)', 'published');
            }
            $this->db->group_by('merchant_id');
            $query = $this->db->get();
            $row = $query->row();
            if ($row) {
                $row->is_badge_enabled = $is_badge_enabled;
            }
            return $row;
        }
        return null;
    }

}



?>