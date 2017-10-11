<?php 
class Polvo_Send4_Model_Observer
{   

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
    
    public function send4Validacao($observer){
        $cart = Mage::getSingleton('checkout/cart');
        $quote = $cart->getQuote();   
        $quoteId = $quote->getId();      

        $addressQuote = $quote->getShippingAddress();

        $shippingSelected = $quote->getShippingAddress()->getShippingMethod();


        $shippingSelected = substr($shippingSelected, 0, 19);

        if($shippingSelected=="polvo_send4_entrega"){
                $quoteCollection = Mage::getModel("sales/quote")->getCollection()
                    ->addFieldToFilter('entity_id',$quoteId) ->addFieldToSelect('*');                    
                foreach( $quoteCollection  as $eachquote){
                    $taxvat = $eachquote['customer_taxvat'];
                }
            
            if($taxvat==""){                
                Mage::throwException("Send4: Campo CPF é obrigatório. Contate o administrador do sistema.");
            }
                
        } 

    }

    public function send4Pedido($observer)
    {   	

        //$shippingMethod = substr($_order->getShippingMethod(), 0, 19);

       // if($shippingMethod=="polvo_send4_entrega"):

        	$order = $observer->getOrder();
        	$orderData = Mage::getModel('sales/order')->load($order->getId());
        	$shipping = $orderData->getShippingMethod();
        	$quoteId = $orderData->getQuoteId();
        	$quote = Mage::getModel('sales/quote')->load($quoteId);
        	$addressQuote = $quote->getShippingAddress();

        	$dot_id = $quote->getShippingAddress()->getDotId();

            $resource = Mage::getSingleton('core/resource');  

            $writeConnection = $resource->getConnection('core_write');        
            $readConnection = $resource->getConnection('core_read');

            $queryDot = 'SELECT * FROM sfm_send4_rates where quote_id='.$quoteId.' and selected=1';
           
            $results = $readConnection->fetchRow($queryDot);

            if(!empty($results)){

                	if($shipping=="polvo_send4_entrega_".$results['dot_id']){
                		$token = $this->_autenticacao();

                		$ch = curl_init(Mage::helper('polvo_send4')->getAmbienteApiSend4()."orders");

                		$customer = Mage::getModel('customer/customer')->load($orderData->getCustomerId());

                		$products = array();

                		$productsItens = $order->getAllVisibleItems();
            			foreach($productsItens as $product):
            			    $products[] = array(
            			    	"name" => $product->getName(),
            			    	"id_code" => $product->getProductId(),
            			    	"quantity" => $product->getData('qty_ordered'),
                                "volumetry" => array(
                                        "width" => 0,
                                        "weight" => 0,
                                        "lenght" => 0,
                                        "height" => 0

                                    )
            			    ); 
            			endforeach;

                        $customerData = array(
                            'name' => $customer->getName(),
                            'email' => $customer->getEmail(),
                            'nin' => $customer->getTaxvat(),
                            'phone' => $customer->getPhone()
                        );

                        $volumetry = array(
                            "width" => 0,
                            "weight" => 0,
                            "lenght" => 0,
                            "height" => 0
                        );

                		$data = array("order"=>array(
                			"dot" => $results['dot_id'],
                			"customer" => $customerData,
                			"invoice_number" => $orderData->getIncrementId(),
                			"shipping_company" => null,
                			"value" => $order->getGrandTotal(),
                			"insurance_value" => null,
                			"products" => $products,
                			"photo" => null,
                			"signature" => null,
                			"ordered_at" => $orderData->getCreatedAt()
                		));

                	 	$data_string = json_encode($data);
                		$token = $this->_autenticacao();

                		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");        
            	        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Authorization: Bearer ".$token, "Contet-Lenght:".strlen($data_string)));

                    	$result=curl_exec($ch);
                    	curl_close($ch);  

                        $resultado = json_decode($result, true);
                        try{

                            if(isset($resultado['invoice_number'])){
                                //$writeConnection->beginTransaction();
                                

                                $customerOrder = "insert into sfm_send4_customer(id_send4, name, email, nin, created_at, updated_at) values({$resultado['customer']['id']},'{$resultado['customer']['name']}', '{$resultado["customer"]["email"]}', '{$resultado["customer"]["nin"]}', '{$resultado["customer"]["created_at"]}','{$resultado["customer"]["updated_at"]}');";

                                $writeConnection->query($customerOrder);
                                $writeConnection->commit();                           
                                $lastInsertIdCustomer = $writeConnection->lastInsertId(); 

                                $customerstatus = "insert into sfm_send4_status(id_send4, name) values({$resultado['status']['id']}, '{$resultado['status']['name']}');";

                                $writeConnection->query($customerstatus);
                                $writeConnection->commit();
                                $lastInsertIdStatus = $writeConnection->lastInsertId(); 

                                $insertOrder = "insert into sfm_send4_order(order_id, value, code, updated_at_send4, created_at_send4, id, customer_send4_id, status_send4_id, dot_send4_id) 
                                    values('{$order->getId()}', {$resultado['value']}, '{$resultado['code']['uuid']}', '{$resultado['updated_at']}', '{$resultado['created_at']}',{$resultado['id']}, {$lastInsertIdCustomer}, {$lastInsertIdStatus}, {$resultado['dot']['id']});";

                                $writeConnection->query($insertOrder);
                                $writeConnection->commit();
                                $lastInsertId = $writeConnection->lastInsertId(); 

                            }else{
                                $order->cancel();
                                $order->getStatusHistoryCollection(true);
                                $order->save();

                                Mage::throwException("Send4: Não foi possível gerar a solicitação de envio. Tente novamente ou entre em contato com o administrador do sistema.");
                            }

                        }catch(Exception $e){
       
                        }
                            $content = json_encode($data).$result."------------------";
                            $fp = fopen("/var/www/html/magento/var/log/send4.txt","a");
                            fwrite($fp,$content);
                            fclose($fp);  
                	}
            }

        //endif;
    }
}