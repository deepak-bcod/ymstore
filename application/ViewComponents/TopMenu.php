<?php

/**
 * @property CI_Controller $ci
 */


class TopMenu
{
    private $ci;
    private $top_menu_list;
    private $identifier;
    private $search_flag;
    private $feature_prod;

    use UsesRestAPI;

    public function __construct($identifier, $search_flag = '', $feature_prod = '')
    {
        $this->ci =& get_instance();

        // Load session library if not already loaded
        if (!isset($this->ci->session)) {
            $this->ci->load->library('session');
        }

        $this->identifier   = $identifier;
        $this->search_flag  = $search_flag;
        $this->feature_prod = $feature_prod;

        $langCode = $this->ci->session->userdata('lcode');

        //echo "<pre>";print_r($langCode);die;

        $customerTypeId = $this->ci->session->userdata('CustomerTypeID');

        $menuIdentifier = in_array($this->identifier, ['top-menu', 'categorymenu']) ? 'top-menu' : $this->identifier;

        $this->top_menu_list = HomeDetailsRepository::get_menus([
            'Identifier'       => $menuIdentifier,
            'lang_code'        => !empty($langCode) ? $langCode : 'en',
            'customer_type_id' => !empty($customerTypeId) ? $customerTypeId : 1
        ]);

        //echo "<pre>";print_r($this->top_menu_list);die;
    }

    public function render()
    {
        if (empty($this->top_menu_list)) {
            return;
        }

        $lang_code = '';

        $lcode = $this->ci->session->userdata('lcode');
        $defaultLanguage = $this->ci->session->userdata('lis_default_language');

        if (!empty($lcode) && $defaultLanguage == 0) {
            $lang_code = $lcode;
        }

        //echo $lang_code;die;

        if ($this->identifier === 'top-menu') {

            $this->ci->template->load(
                'components/top_menu',
                [
                    'navCatData' => $this->top_menu_list->AllMenuLevels ?? [],
                    'menuType'   => $this->top_menu_list->menu_type ?? '',
                    'lang'       => $lang_code
                ]
            );

        } else {

            $this->ci->template->load(
                'components/category_menu',
                [
                    'navCatData'   => $this->top_menu_list->AllMenuLevels ?? [],
                    'search_flag'  => $this->search_flag,
                    'feature_prod' => $this->feature_prod,
                    'lang'         => $lang_code
                ]
            );

        }
    }

    public function not_found_page()
    {
        if (empty($this->top_menu_list)) {
            return;
        }

        $this->ci->template->load(
            'components/custom_404_page',
            [
                'navCatData'   => $this->top_menu_list->AllMenuLevels ?? [],
                'search_flag'  => $this->search_flag,
                'feature_prod' => $this->feature_prod
            ]
        );
    }
}