<?php
 class Polvo_Send4_Model_Quote extends Mage_Core_Model_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('polvo_send4/quote');
    }
}