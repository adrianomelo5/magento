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

class Ideasa_Base_Model_Observer {

    private $logger;

    const ACTION_MARCAR = 'marcar';
    const ACTION_CAPTURAR = 'capturar';
    const ACTION_CANCELAR = 'cancelar';

    /**
     * Initialize resource model
     */
    public function __construct() {
        $this->logger = Ideasa_Base_Logger::getLogger(__CLASS__);
    }

    public function expirapedido() {
        $this->logger->info('Executando expiração automática de pedidos - início');

        if (Mage::getModel('base/expiraPedido')->isExpiracaoAtiva()) {
            $orderIds = Mage::getModel('base/expiraPedido')->buscaPedidosParaExpirar();
            foreach ($orderIds as $key => $value) {
                $order = Mage::getModel('sales/order');
                $order = $order->loadByAttribute('increment_id', $value['increment_id']);
                // verifica se pedido não está cancelado
                if ($order->getState() != Mage_Sales_Model_Order::STATE_CANCELED) {
                    $this->logger->info('Vai expirar pedido ' . $value['increment_id']);
                    Mage::getModel('base/expiraPedido')->cancelaPedido($order);
                }
            }
        } else {
            $this->logger->info('Expiração automática não está ativa, não irá expirar nenhum pedido.');
        }

        $this->logger->info('Executando expiração automática de pedidos - fim');
    }

    public function expiraBoletos() {
        $this->logger->info('Executando expiração automática de boletos - início');

       

        $this->logger->info('Executando expiração automática de boletos - fim');
    }

    public function expirarOrder($orderId) {
        $this->logger->info('Vai expirar boleto ' . $orderId);
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        if ($order) {
            Mage::getModel('base/expiraPedido')->cancelaPedido($order);
        }
    }

    public function enviaEmailBoletosAvencer() {
       
    }

    public function logUserAction($observer) {
        $orderId = $observer->getEvent()->getOrderId();
        $action = $observer->getEvent()->getAction();
        $comment = '';
        if (Mage::app()->getStore()->isAdmin() && Mage::getSingleton('admin/session')->getUser() != null) {
            $user = Mage::getSingleton('admin/session')->getUser()->getName();
            if ($action == self::ACTION_MARCAR) {
                $comment = "Usuário $user marcou boleto do pedido $orderId como pago";
            } elseif ($action == self::ACTION_CAPTURAR) {
                $comment = "Usuário $user capturou pagamento do pedido $orderId";
            } elseif ($action == self::ACTION_CANCELAR) {
                $comment = "Usuário $user cancelou pagamento do pedido $orderId";
            }
        } else {
            $comment = 'Pedido atualizado automaticamente';
        }

        $this->logger->info($comment);
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        Mage::helper('base')->addStatusHistoryComment($order, $comment);
    }

    public function licenseExpCheck() {
        $this->logger->info('Executando verificação da licença PagSeguro - início');
        if (extension_loaded('ionCube Loader')) {
            $ioncubeInfo = ioncube_file_info();
            if (isset($ioncubeInfo['FILE_EXPIRY'])) {
                $licenseExpDay = $ioncubeInfo['FILE_EXPIRY'];
                $remainingDays = abs(floor((time() - $licenseExpDay) / 86400));
                if ($remainingDays == 14 || $remainingDays == 7 || $remainingDays == 1) {
                    if ($remainingDays == 14) {
                        $title = 'ATENÇÃO: A licença PagSeguro vai expirar em duas semanas! Contate nosso suporte para renovar.';
                        $description = 'A licença PagSeguro da sua loja vai expirar em duas semanas. Contate nosso <a href="https://superempreendedor.zendesk.com/" target="_blank">suporte</a> para renovar.';
                        $severity = Mage_AdminNotification_Model_Inbox::SEVERITY_MINOR;
                        $this->logger->info('Faltam duas semanas para a expiração da licença PagSeguro');
                    }
                    if ($remainingDays == 7) {
                        $title = 'ATENÇÃO: A licença PagSeguro vai expirar em uma semana! Contate nosso suporte para renovar.';
                        $description = 'A licença PagSeguro da sua loja vai expirar em uma semana. Contate nosso <a href="https://superempreendedor.zendesk.com/" target="_blank">suporte</a> para renovar.';
                        $severity = Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR;
                        $this->logger->info('Falta uma semana para a expiração da licença PagSeguro');
                    }
                    if ($remainingDays == 1) {
                        $title = 'ATENÇÃO: A licença PagSeguro vai expirar amanhã! Contate nosso suporte para renovar.';
                        $description = 'A licença PagSeguro da sua loja vai expirar amanhã. Contate nosso <a href="https://superempreendedor.zendesk.com/" target="_blank">suporte</a> para renovar.';
                        $severity = Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL;
                        $this->logger->info('Falta 1 dia para a expiração da licença PagSeguro');
                    }
                    $inbox = Mage::getModel('adminnotification/inbox');
                    $url = 'https://superempreendedor.zendesk.com/';
                    $isInternal = false;
                    $inbox->add($severity, $title, $description, $url, $isInternal);
                }
            }
        }
        $this->logger->info('Executando verificação da licença PagSeguro - fim');
    }

}