<?php
/**
 * Allpago - Gwap Payment Module
 *
 * @title      Magento -> Custom Payment Module for Gwap
 * @category   Payment Gateway
 * @package    Allpago_Gwap
 * @author     Allpago Development Team
 * @copyright  Copyright (c) 2013 Allpago
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Allpago_Clearsale_Helper_Data extends Mage_Core_Helper_Abstract
{
   /*     
    1  Diners
    2  MasterCard
    3  Visa
    4  Outros
    5  American Express
    6  HiperCard
    7  Aura      
    */
    public function getCardType($card) {
        
        $arrayCard = array('VISA'=>3,
                           'MASTER'=>2,
                           'AMEX'=>5,
                           'DINERS'=>1);
        
        return array_key_exists($card, $arrayCard) ? $arrayCard[$card] : 4;
    }     
    
    public function getOrderItems($order){
        
        //Define informação dos produtos
        $totalItems = 0;
        $items = $order->getAllItems();
        $return = array();

        $prodBundles = array();
        $prodConfigurable = array();
        foreach ($items as $arr){
            if($arr->getProductType()=="bundle"){
                  $prodBundles[] = array(
                      'price'=>$arr->getPrice(),
                      'id' => $arr->getItemId()
                  );
            }
            if($arr->getProductType()=="configurable"){
                  $prodConfigurable[] = array(
                      'price'=>$arr->getPrice(),
                      'id' => $arr->getItemId()
                  );
            }	                    
        }
        if(sizeof($prodConfigurable)>0){
            foreach ($prodConfigurable as $key => $conf){
                foreach ($items as $arr){
                    if($conf['id']==$arr->getParentItemId()){
                        $arr->setPrice(number_format($conf['price'],3,'.',''));
                    }
                }
            }   					
        }
        if(sizeof($prodBundles)>0){
            foreach ($prodBundles as $key => $bundle){
                $n_filhos = 0;
                foreach ($items as $arr){
                    if($bundle['id']==$arr->getParentItemId()){
                        $n_filhos++;                          
                    }
                }
                $prodBundles[$key]['n_filhos'] = $n_filhos;   
            }      
            foreach ($prodBundles as $key => $bundle){
                foreach ($items as $arr){
                    if($bundle['id']==$arr->getParentItemId()){
                        $arr->setPrice(number_format($bundle['price']/$bundle['n_filhos'],3,'.',''));
                    }
                }
            }
        }            

        $somaProdutos= 0;

        foreach ($items as $key =>  $item) {
            if($item->getProductType()!='configurable' 
                    && $item->getProductType()!='bundle' ){
                $return[] = array('ID'=>$item->getProductId(),
                                       'Name'=>str_replace('&','',$item->getName()),
                                       'ItemValue'=>number_format($item->getPrice(),2,'.',''),
                                       'Qty'=>(int)$item->getQtyOrdered());
                $totalItems += $item->getQtyOrdered();
                $somaProdutos += $item->getPrice()*$item['qty_ordered'];
            }
        }        

        //Tratar descontos no pagamento
        $grandTotalSemFrete = $order->getGrandTotal()-$order->getShippingAmount();
        if(($grandTotalSemFrete) < $somaProdutos){
                $discount_amount = abs($order->getDiscountAmount());
                if(($somaProdutos-$discount_amount)>($grandTotalSemFrete)){
                    $discount_amount = $discount_amount+($somaProdutos-$discount_amount)-($grandTotalSemFrete);
                }
                $descontos = number_format((($grandTotalSemFrete)/(($grandTotalSemFrete)+$discount_amount)*100),3,'.','');
                foreach($return as $key => $item){
                  $return[$key]['ItemValue'] = number_format((($descontos*$item['Qty'])*$item['ItemValue']/100)/$item['Qty'],2,'.','');
                }                        
        }        
        
        return $return;
    }



    
}