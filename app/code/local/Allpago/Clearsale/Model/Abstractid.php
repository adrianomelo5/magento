<?php
require_once Mage::getBaseDir('lib').'/PEAR/XML/Serializer.php';

abstract class Allpago_Clearsale_Model_Abstractid extends Mage_Core_Model_Abstract {

    /**
     * set result
     * 
     * @param string $result 
     */
    public function setResult($result) {
        $this->result = $result;
    }

    /**
     * set result
     * 
     * @param string $result 
     */
    public function setResultContent($result) {
        $this->resultContent = $result;
    }
    
    /**
     * returns result content
     * 
     * @return string
     */
    public function getResult() {
        return $this->result;
    }
    
    /**
     * returns result content
     * 
     * @return string
     */
    public function getResultContent() {
        return $this->resultContent;
    }    

    public function __call($method, $args = array()) {
 
        if(!Mage::getStoreConfig('allpago/clearsale/active')){
            return false;
        }
            
        $entity_code = Mage::getStoreConfig('allpago/clearsale/ws_key');
        if(Mage::getStoreConfig('allpago/clearsale/ambiente')=='producao'){
            $url = 'http://www.clearsale.com.br/integracaov2/service.asmx';
        }else{
            $url = 'http://homologacao.clearsale.com.br/integracaov2/service.asmx';
        }
        
        $serializer_options = array (   
            'addDecl' => false,   
            'encoding' => 'ISO-8859-1', 
            'indent' => '  ',
            'rootName' => 'ClearID_Input',
            'mode' => 'simplexml'
        );        

        if ($args) {
            $args = current($args);
        }
        
        if($method == 'CheckOrderStatus'){
            $fields = array(
                'entityCode'=>urlencode($entity_code),
                'pedidoID'=>($args)
            );           
        }else{            
            // Conversão de array para xml 
            $serializer = new XML_Serializer($serializer_options);
            $status = $serializer->serialize($args);
            if (PEAR::isError($status)) {  
                Mage::throwException($status->getMessage());  
            }
            $xmlToSend = ($serializer->getSerializedData());

            //Passagem de parametros para envio de acordo com manual de integração (EntityCode e xml)            
            $fields = array(
                'entityCode'=>urlencode($entity_code),
                'xmlDados'=>($xmlToSend)
            );
        }
        
        $fields_string = '';
        foreach($fields as $key=>$value){
            $fields_string .= $key.'='.$value.'&'; 
        }     

        $time_start = microtime(true);        

        $fields_string = substr_replace(rtrim($fields_string),'',-1) ;

        if(Mage::getStoreConfig('allpago/clearsale/debug'))
            Mage::log($method.': '.print_r($fields_string,true),null,'clearsale.log');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url.'/'.$method);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

        $curlresult = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        $xml = simplexml_load_string($curlresult);
        $xml = preg_replace('/(<\?xml[^?]+?)utf-16/i', '$1utf-8', $xml); 
        $xml = simplexml_load_string($xml);

        $this->processResult($method,$xml);
            
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        $info = array();
        $info['url'] = $url;
        $info['method'] = $method;
        $info['execution_time'] = $time;
    }

    
    public function getStatusType($status) {
        
        $arrayStatus = array('APA'=> 'APA (Aprovado  Automaticamente)',
                             'PAV'=> 'PAV (Pendente  de  Auto  Validação pelo Questionário)',
                             'APQ'=> 'APQ (Aprovado  Por  Questionário)',
                             'RPQ'=> 'RPQ (Reprovado  por  Questionário)',
                             'RPP'=> 'RPP (Reprovado  Por  Política)',
                             'RPA'=> 'RPA (Reprovado  Automaticamente)');

        return array_key_exists((string)$status, $arrayStatus) ? $arrayStatus[(string)$status] : 4;
    }    
    
    private function processResult($method,$xml) {
        
        try {
            $result = array();
            switch ($method) {
                case 'CheckOrderStatus':
                    foreach($xml->Pedidos->Pedido as $line){
                        $result[] = array('ID'=>$line->ID,'Status'=>$line->Status,'Score'=>$line->Score);
                    }
                    $this->setResult($result);                   
                    break;
                case 'SubmitInfo':
                    $result['StatusCode'] = $xml->StatusCode;
                    $result['Message'] = $xml->Message;
                    $result['Status'] = $xml->Pedidos->Pedido->Status;
                    $result['URLQuestionario'] = $xml->Pedidos->Pedido->URLQuestionario;
                    $this->setResult($result);
                    break;
                /*
                case 'UpdateOrderStatusID':
                    if(isset($xml->StatusCode)){
                        $result['StatusCode'] = $xml->StatusCode;
                        $result['Message'] = $xml->Message;
                    }else{
                        $result[] = array('ID'=>$xml->Order->ID,'Status'=>$xml->Order->Status,'Score'=>$xml->Order->Score);
                    }
                    $this->setResultContent($result);                   
                    break; 
                 */
            }
            
            if(Mage::getStoreConfig('allpago/clearsale/debug'))
                Mage::log($method.'Result : '.print_r($xml,true),null,'clearsale.log');            

        } catch (Exception $e) {
            throw $e;
        }
    }  
    
    public function criaLock($model, $id) {
        $log = Mage::getModel('allpago_mc/log');
        try {
            //Obtém e bloqueia o recurso
            $locker = Mage::getModel($model);
            $locker->_id = $id;
            //Se o recurso já estiver alocado
            if ($locker->isLocked()) {
                //Cancela a execução
                echo PHP_EOL . date('Y-m-d H:i:s') . ' Robo ' . $locker->_id . ' ja em execucao' . PHP_EOL;
                $log->add(null, 'Clearsale', 'criaLock()', 'Erro', 'Robo ja em execucao', null);
                exit;
            }
            //Aloca o recurso
            $locker->lock();
            //Retorna o recurso
            return $locker;
        } catch (Exception $e) {
            //Salva log
            $log->add(null, 'Clearsale', 'criaLock()', 'Erro', 'Ocorreu um erro', $e->getMessage());
        }
    }    
    
}