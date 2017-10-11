<?php 
class Polvo_Send4_Model_Order extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct(){
		$this->_init('polvo_send4/send4order', 'entity_id');
	}
}