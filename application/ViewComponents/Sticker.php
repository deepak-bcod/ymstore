<?php
/**
 * @property CI_Controller $ci
 */
class Sticker
{
    private $ci;
    private $sticker;
   
    public function __construct(){
        $this->ci =& get_instance();
        $postArr = array('table_name' => 'sticker_text', 'database_flag' => "own", 'where' => "id = 1");
        $this->sticker = CommonRepository::get_table_data($postArr); 
    }

    public function render(){
        $this->ci->template->load('components/sticker', ['sticker'=> $this->sticker]);
    }
}
