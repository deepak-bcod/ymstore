<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Template {
    protected $ci;

    public function __construct() {
        // Get CI instance
        $this->ci =& get_instance();
    }

    /**
     * Load a view with optional data
     *
     * @param string $body_view  The main view file to load
     * @param array|object|null $data  Data to pass to the view
     */
    public function load($body_view, $data = null) {
        // Theme folder constant, set in constants.php
        $tpl_view = defined('THEMENAME') ? THEMENAME : '';

        // Normalize view names to match Linux case-sensitivity
        $body_view = ltrim($body_view, '/'); // remove leading slash if any

        // Build possible paths
        $paths = [];
        if (!empty($tpl_view)) {
            $paths[] = APPPATH . 'views/' . $tpl_view . '/' . $body_view;
            $paths[] = APPPATH . 'views/' . $tpl_view . '/' . $body_view . '.php';
        }
        $paths[] = APPPATH . 'views/' . $body_view;
        $paths[] = APPPATH . 'views/' . $body_view . '.php';

        // Find the first existing file
        $body_view_path = null;
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $body_view_path = str_replace(APPPATH . 'views/', '', $path);
                break;
            }
        }

        // If file not found, show an error
        if (!$body_view_path) {
            show_error('Unable to load the requested file: ' . $body_view);
        }

        // Load the view with data
        $this->ci->load->view($body_view_path, $data);
    }
}
