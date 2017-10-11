<?php 
class Polvo_Send4_Model_Mysql4_Send4rates extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct(){
		$this->_init('polvo_send4/send4rates', 'entity_id');
	}
}