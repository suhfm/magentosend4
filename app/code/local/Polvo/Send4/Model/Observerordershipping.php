<?php 
class Polvo_Send4_Model_Observerordershipping
{   

    public function send4Setdot($observer)
    {   	
        $cart = Mage::getSingleton('checkout/cart');
        $quote = $cart->getQuote();   
        $quoteId = $quote->getId();      

    	$addressQuote = $quote->getShippingAddress();

    	$dot_id = $quote->getShippingAddress()->getShippingMethod();




        $shippingSelected = substr($dot_id, 0, 19);

        if($shippingSelected=="polvo_send4_entrega"){

            $dotId = str_replace("polvo_send4_entrega_", "", $dot_id);

            $resource = Mage::getSingleton('core/resource');    
            $writeConnection = $resource->getConnection('core_write');        
            $readConnection = $resource->getConnection('core_read');
            
            //$queryDot = 'SELECT * FROM sfm_send4_rates where quote_id='.$quoteId;       
            //$results = $readConnection->fetchAll($queryDot);

            if(!empty($quoteId)){
                $sqlupdate = "update sfm_send4_rates set selected=0, orderflag=1 where quote_id={$quoteId}";
                $writeConnection->query($sqlupdate);
                $writeConnection->commit();
            }

            if(!empty($dot_id) && !empty($quoteId)){
                $sqlupdateS = "update sfm_send4_rates set selected=1, orderflag=1 where dot_id={$dotId} and quote_id={$quoteId}";
                $writeConnection->query($sqlupdateS);
                $writeConnection->commit();
            }
        }
        
    }

}