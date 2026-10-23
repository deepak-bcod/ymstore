<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Api extends CI_Controller
{
    public function php_version()
    {
        $response = [
            'status' => true,
            'php_version' => PHP_VERSION
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
