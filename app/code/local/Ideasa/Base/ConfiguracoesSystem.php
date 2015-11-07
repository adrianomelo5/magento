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

class Ideasa_Base_ConfiguracoesSystem {
    
    // Página de sucesso
    const PAGINA_SUCESSO_MOSTRAR_CONTEUDO = 'base/pagina_sucesso/mostrar_conteudo';
    
    // Expiração pedidos
    const EXPIRACAO_PEDIDO_ACTIVE = 'base/expiracao_pedido/ativa';
    const EXPIRACAO_PEDIDO_DATA_INICIAL = 'base/expiracao_pedido/data_inicial';
    const EXPIRACAO_PEDIDO_PERIODO_DIAS = 'base/expiracao_pedido/periodo_dias';
    const EXPIRACAO_PEDIDO_PERIODO_HORAS = 'base/expiracao_pedido/periodo_horas';
    const EXPIRACAO_PEDIDO_PERIODO_MINUTOS = 'base/expiracao_pedido/periodo_minutos';
    
    // Página de estilo OSC
    const OSC_CSS_ACTIVE = 'base/osc/css_active';
    
    const REENVIO_BOLETO_ATIVO = 'base/reenvio_boleto/ativa';
    const REENVIO_BOLETO_NUMERO_DIAS = 'base/reenvio_boleto/numero_dias';
    
      //Controle do envio de e-mails
    const CONTROLE_ENVIO_EMAIL_NOVO = 'base/controle_envio_email/novo';
    const CONTROLE_ENVIO_EMAIL_CANCELADO = 'base/controle_envio_email/cancelado';
    const CONTROLE_ENVIO_EMAIL_FATURA_GERADA = 'base/controle_envio_email/fatura_gerada';
    
}