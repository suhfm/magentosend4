<?php

class Polvo_Send4_Helper_Data extends
    Mage_Core_Helper_Abstract
{
    const XML_EXPRESS_MAX_WEIGHT = 'carriers/polvo_send4/express_max_weight';
    const XML_CLIENT_ID = 'carriers/polvo_send4/client_id_send4';
    const XML_CLIENT_SECRET = 'carriers/polvo_send4/client_secret_send4';
    const API = 'carriers/polvo_send4/ambiente_api_send4';
    const QUANTIDADE_DOTS = 'carriers/polvo_send4/quantidade_dots_send4';
    const PRECO_FIXO = 'carriers/polvo_send4/preco_fixo_send4';

    
    public function getExpressMaxWeight()
    {
        return Mage::getStoreConfig(self::XML_EXPRESS_MAX_WEIGHT);
    }

    public function getClientIDSend4()
    {
        return Mage::getStoreConfig(self::XML_CLIENT_ID);
    }

    public function getClientSecretSend4()
    {
        return Mage::getStoreConfig(self::XML_CLIENT_SECRET);
    }

    public function getAmbienteApiSend4(){
        return Mage::getStoreConfig(self::API);
    }

    public function getQuantidadesDotsSend4(){
        return Mage::getStoreConfig(self::QUANTIDADE_DOTS);
    }

    public function getPrecoFixo()
    {
        return Mage::getStoreConfig(self::PRECO_FIXO);
    }

    public function getSend4OrderId($orderId){
        $resource = Mage::getSingleton('core/resource');  

        $writeConnection = $resource->getConnection('core_write');        
        $readConnection = $resource->getConnection('core_read');

        $query = 'SELECT * FROM sfm_send4_order where order_id='.$orderId;
       
        $results = $readConnection->fetchRow($query);

        return $results;

    }

    public function _autenticacao()
    {

        $clientId = Mage::helper('polvo_send4')->getClientIDSend4();
        $key = Mage::helper('polvo_send4')->getClientSecretSend4();
        $grant_type = "client_credentials";

        $data = array("grant_type"=>$grant_type,"client_id"=>$clientId,"client_secret"=>$key);
        $data_string = json_encode($data);

        $ch = curl_init(Mage::helper('polvo_send4')->getAmbienteApiSend4()."auth/connect");

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Contet-Lenght:".strlen($data_string)));

        $result=curl_exec($ch);
        curl_close($ch);
        $retorno = json_decode($result, true);

        return $retorno['token'];
        
    }

    public function getOrder($orderId){
        $token = $this->_autenticacao();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, Mage::helper('polvo_send4')->getAmbienteApiSend4()."orders/".$orderId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Authorization: Bearer ".$token));

        $response = curl_exec($ch);
        curl_close($ch);
        
        $array = json_decode($response, true);


        return $array;
    }

}