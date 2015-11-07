<?php

class iMasters_ProdutoLogs_Model_Observer {

    /**
     * O Magento irá passar a Varien_Event_Observer object como
     * o primeiro parâmetro de eventos despachados.
     */
    public function logUpdate(Varien_Event_Observer $observer) {
        // Recuperar o produto que está sendo atualizado a partir do observador evento
        $product = $observer->getEvent()->getProduct();

        // Escrevendo uma nova linha no var/log/product-updates.log
        $name = $product->getName();
        $sku = $product->getSku();
        Mage::log("{$name} ({$sku}) updated", null, 'atualizacoes-produtos.log');
    }

}