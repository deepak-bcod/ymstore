<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Language extends CI_Controller {

    public function switch()
    {
        $lang = $this->input->post('site_lang');

        if ($lang) {
            $this->session->set_userdata('site_lang', $lang);
        }

        redirect($_SERVER['HTTP_REFERER']);
    }
}
