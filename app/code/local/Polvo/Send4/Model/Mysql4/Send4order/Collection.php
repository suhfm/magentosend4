<?php 
class Polvo_Send4_Model_Mysql4_Send4order_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct(){
		parent::_construct();
		$this->_init('polvo_send4/send4order');
	}
}