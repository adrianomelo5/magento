<?php

/**
* 
* SuperEmpreendedor para Magento
* 
* @category     SuperEmpreendedor
* @packages     Base
* @copyright    Copyright (c) 2014 SuperEmpreendedor (http://www.superempreendedor.com/pagseguro)
* @version      1.16.2
* @license      http://www.superempreendedor.com/magento/licenca (Este arquivo Ã© propriedade do SuperEmpreendedor e nÃ£o pode ser copiado ou distribuÃ­do sem autorizaÃ§Ã£o.)
*
*/

class Ideasa_Base_Model_ExpiraPedido extends Mage_Core_Model_Abstract {

    private $logger;

    /**
     * Initialize resource model
     */
    protected function _construct() {
        $this->logger = Ideasa_Base_Logger::getLogger(__CLASS__);
    }

    public function buscaPedidosParaExpirar() {
        $dataInicialBuscaPedidos = $this->getDataInicial();
        $dataCriacaoMaxima = $this->getDataExpiracao();
        $metodosPagamento = $this->buildPaymentMethods();
        $orderIds = array();
        $resource = Mage::getSingleton('core/resource');

        $sql = "SELECT increment_id FROM {$resource->getTableName('sales_flat_order')} o 
                INNER JOIN {$resource->getTableName('sales_flat_order_payment')} p ON p.parent_id=o.entity_id";
        $sql .= ' WHERE o.state=\'' . Mage_Sales_Model_Order::STATE_NEW . '\'';
        $sql .= ' and o.created_at BETWEEN \'' . $dataInicialBuscaPedidos . '\' and \'' . $dataCriacaoMaxima . '\'';
        $sql .= ' AND (p.method = \'\' OR p.method IS NULL)';
        
        $readConnection = $resource->getConnection('core_read');
        $tmp = $readConnection->fetchAll($sql);
        if (is_array($tmp)) {
            $orderIds = array_merge($orderIds, $tmp);
        }

        // módulo PagSeguroDireto
        if (Mage::helper('base/module')->isPagSeguroDiretoExistsAndActive()) {
            $tmp = Mage::getModel('pagsegurodireto/expiraPedido')->buscaPedidosParaExpirar($dataInicialBuscaPedidos, $dataCriacaoMaxima);
            if (is_array($tmp)) {
                $orderIds = array_merge($orderIds, $tmp);
            }
        }

        return $orderIds;
    }

    public function cancelaPedido(Mage_Sales_Model_Order $order) {
        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        $listPaymentMethodsIdeasa = Mage::helper('base')->listPaymentMethods();
        $this->logger->info('Processando cancelamento do pedido ' . $order->getRealOrderId() . ', do modulo: ' . $paymentMethod);

        try {
            if ($order->getState() != Mage_Sales_Model_Order::STATE_CANCELED) {
                $order->cancel();

                //força o cancelamento
                if ($order->getStatus() != Mage_Sales_Model_Order::STATE_CANCELED) {
                    $order->setState(
                            Mage_Sales_Model_Order::STATE_CANCELED, true, Mage::helper('base')->__('Pedido cancelado'), $notified = false
                    );
                } else {
                    $order->addStatusToHistory($order->getStatus(), Mage::helper('base')->__('Pedido cancelado.'));
                }

                if ($order->hasInvoices() != '') {
                    $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, Mage::helper('base')->__('O pagamento e o pedido foram cancelados, mas não foi possível retornar os produtos ao estoque pois já havia uma fatura gerada para este pedido.'), $notified = false);
                }
                $order->save();
            }

            if (Mage::helper('base')->isIdeasaPaymentMethod($paymentMethod) && Mage::helper('base/module')->isPagSeguroDiretoExists() && $paymentMethod == $listPaymentMethodsIdeasa[0]) {
                Mage::getModel('pagsegurodireto/notification')->sendEmail($order);
            } 
        } catch (Exception $e) {
            $this->logger->error("Erro ao cancelar pedido $orderId \n $e->__toString()");
        }

        $this->logger->info('Cancelamento do pedido foi concluido ' . $order->getRealOrderId() . ', do modulo: ' . $paymentMethod);

        return;
    }

    public function isExpiracaoAtiva() {
        return Mage::getStoreConfig(Ideasa_Base_ConfiguracoesSystem::EXPIRACAO_PEDIDO_ACTIVE);
    }

    private function getDataInicial() {
        return Mage::helper('base/date')->convertDateToBr(Mage::getStoreConfig(Ideasa_Base_ConfiguracoesSystem::EXPIRACAO_PEDIDO_DATA_INICIAL));
    }

    public function getDataExpiracao() {
        $dias = Mage::getStoreConfig(Ideasa_Base_ConfiguracoesSystem::EXPIRACAO_PEDIDO_PERIODO_DIAS);
        $horas = Mage::getStoreConfig(Ideasa_Base_ConfiguracoesSystem::EXPIRACAO_PEDIDO_PERIODO_HORAS);
        $minutos = Mage::getStoreConfig(Ideasa_Base_ConfiguracoesSystem::EXPIRACAO_PEDIDO_PERIODO_MINUTOS);

        $order = Mage::getModel('sales/order');
        $data = $order->getResource()->formatDate(mktime());

        $data = Mage::helper('base/date')->addMinutesToUs(-$minutos, $data);
        $data = Mage::helper('base/date')->addHoursToUs(-$horas, $data);
        $data = Mage::helper('base/date')->addDaysToUs(-$dias, $data);

        return $data;
    }

    private function buildPaymentMethods() {
        $paymentMethods = Mage::helper('base')->listPaymentMethods();
        $methods = '';
        if ($paymentMethods != null) {
            for ($i = 0; $i < sizeof($paymentMethods); $i++) {
                $methods .= '\'' . $paymentMethods[$i] . '\'';
                if (($i + 1) < sizeof($paymentMethods)) {
                    $methods .= ',';
                }
            }
        }
        return $methods;
    }

}
