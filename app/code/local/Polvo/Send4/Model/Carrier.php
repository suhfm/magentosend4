<?php

class Polvo_Send4_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * Carrier's code, as defined in parent class
     *
     * @var string
     */
    protected $_code = 'polvo_send4';

    /**
     * Retorna métodos disponíveis para 
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        /** @var Mage_Shipping_Model_Rate_Result $result */
        $result = Mage::getModel('shipping/rate_result');

        $token = $this->_autenticacao();
        $getSend4Dots = $this->_getSend4Dots($token);
        if(!empty($getSend4Dots['dots'])){
            for($x=0;$x<=1;$x++){
                $idDot = $this->_saveSend4Rates($getSend4Dots['dots'][$x]);
                $result->append($this->_getSend4Rates($getSend4Dots['dots'][$x],$idDot));                
            }

        }

        return $result;
    }

    protected function _autenticacao()
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

    protected function _getSend4Dots($token)
    {

        $cart = Mage::getSingleton('checkout/cart');
        $quote = $cart->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        $zip = $shippingAddress->getPostcode();
        $state = $shippingAddress->getState();

        $address = $this->_getFullAddress($zip);
        $add = json_decode($address);
        
        $data = array("items_to_return"=>Mage::helper('polvo_send4')->getQuantidadesDotsSend4(),
            "location"=>array(
            "address"=>$add->logradouro,
            "number"=>"",
            "complement"=>"",
            "neighbor"=>$add->bairro,
            "city"=>$add->cidade,
            "state"=>$add->uf,
            "country"=>"Brasil",
            "postal_code"=>$zip,
            "lat"=>null,
            "lng"=>null
            ));
        $data_string = json_encode($data);
        //print_r($data_string);die();
        $ch = curl_init(Mage::helper('polvo_send4')->getAmbienteApiSend4()."dots/closests");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Authorization: Bearer ".$token, "Contet-Lenght:".strlen($data_string)));

        $result=curl_exec($ch);
        curl_close($ch);

        //if()

        //print_r($result);die();
        $dots = json_decode($result, true);
        return $dots;

    }

    protected function _getFullAddress($cep){
        $webservice = 'http://cep.republicavirtual.com.br/web_cep.php';
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $webservice . '?cep='. urlencode($cep) . '&formato=JSON');
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $resultado = curl_exec($ch);
        curl_close($ch);

        return $resultado;
    }


    /**
     * Retorna Métodos de envio disponíveis pelo send4
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            'entrega'    =>  'Send4'
        );
    }

    protected function _getSend4Rates($dot, $idDot){
        $rate = Mage::getModel('shipping/rate_result_method');
        $priceOriginal = str_replace(",", ".", Mage::helper('polvo_send4')->getPrecoFixo());
        $price = number_format($priceOriginal, 2, '.', ',');

        if($price!=0){
            $finalprice = $price;
        }else{
            $finalprice = 0;
        }

        $address = $dot['address'].", ".$dot['complement'].", ".$dot['neighbor'].", ".$dot['zip_code'];

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('entrega_'.$dot['id']);
        $rate->setMethodTitle($dot['trade_name']." - ".$dot['complement']);
        $rate->setMethodDescription($address);
        $rate->setDotId($idDot);        
        $rate->setPrice($finalprice);
        $rate->setCost(0);

        return $rate;
    }   

    protected function _saveSend4Rates($dot){        



        $cart = Mage::getSingleton('checkout/cart');
        $quote = $cart->getQuote();        

        $dot_id = $quote->getShippingAddress()->getShippingMethod();

        //echo $dot_id;die();

        $shippingSelected = substr($dot_id, 0, 19);

        if($shippingSelected=="polvo_send4_entrega"){

                $shippingAddress = $quote->getShippingAddress();
                $addressId = $shippingAddress->getId();

                $rate = Mage::getModel('shipping/rate_result_method');

                $resource = Mage::getSingleton('core/resource');    

                $writeConnection = $resource->getConnection('core_write');        

                $readConnection = $resource->getConnection('core_read');

                $cart = Mage::getSingleton('checkout/cart');
                $quote = $cart->getQuote();
                $quoteId = $quote->getId();    
                $dotId = $dot['id'];
                $companyName = $dot['company_name'];
                $tradeName = $dot['trade_name'];
                $displayName = $dot['display_name'];
                $cnpj = $dot['cnpj'];
                $email = $dot['email'];
                $address = $dot['address'];
                $complement = $dot['complement'];
                $neighbor = $dot['neighbor'];
                $zipCode = $dot['zip_code'];
                $city = $dot['city']['name'];
                $country = $dot['city']['district']['country']['name'];
                $selected = 0;

                $steps = Mage::getSingleton('checkout/session')->getSteps();

                if($steps['shipping']['complete']==1 && empty($steps['shipping_method']['complete'])){

                    if(!empty($dotId)){
                        $queryClearRate = "DELETE from sfm_send4_rates where quote_id={$quoteId} and dot_id={$dotId}";
                        $writeConnection->query($queryClearRate);
                    }else{
                        $queryClearRate = "DELETE from sfm_send4_rates where quote_id={$quoteId}";
                        $writeConnection->query($queryClearRate);
                    }


                    //if(empty($results)){
                    $queryInsertRate = "INSERT into sfm_send4_rates(quote_id, dot_id, company_name, trade_name, display_name, cnpj, email, address, complement, neighbor, zip_code, city, country, selected, orderflag) values($quoteId, $dotId, '$companyName','$tradeName','$displayName','$cnpj','$email','$address','$complement','$neighbor','$zipCode', '$city','$country',$selected,0);";

                    $writeConnection->query($queryInsertRate);
                    $lastInsertId = $writeConnection->lastInsertId(); 
                    //}

                }

                $content = json_encode($data).$result."------------------";
                $fp = fopen("/var/www/html/magento/var/log/send4.txt","a");
                fwrite($fp,$content);
                fclose($fp);  
                
                return $lastInsertId;

        }else{
            return;
        }
    }

}