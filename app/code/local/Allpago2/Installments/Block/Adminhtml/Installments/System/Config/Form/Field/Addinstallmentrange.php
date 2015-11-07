<?php
class Allpago_Installments_Block_Adminhtml_Installments_System_Config_Form_Field_Addinstallmentrange
	extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
	const CONDICAO_MAIOR = 'condicao_maior';
	const CONDICAO_MENOR = 'condicao_menor';
	const NUMERO_PARCELAS = 'numeroparcelas';
	const RANGE_INICIAL = 'rangeinicial';
	const RANGE_FINAL = 'rangefinal';
	
    protected $_conditionRenderer;
	
	private function _getConditionMaiorRenderer()
	{
		if (!$this->_conditionMaiorRenderer)
		{
	        $this->_conditionMaiorRenderer = $this->getLayout()->createBlock(
				'installments/adminhtml_renderer_selectmaior', '',
				array('is_render_to_js_template' => true)
			);
			$this->_conditionMaiorRenderer->setClass(self::CONDICAO_MAIOR.'_select');
			$this->_conditionMaiorRenderer->setId(self::CONDICAO_MAIOR);
		}
		return $this->_conditionMaiorRenderer;
	}
	
	private function _getConditionMenorRenderer()
	{
		if (!$this->_conditionMenorRenderer)
		{
	        $this->_conditionMenorRenderer = $this->getLayout()->createBlock(
				'installments/adminhtml_renderer_selectmenor', '',
				array('is_render_to_js_template' => true)
			);
			$this->_conditionMenorRenderer->setClass(self::CONDICAO_MENOR.'_select');
			$this->_conditionMenorRenderer->setId(self::CONDICAO_MENOR);
		}
		return $this->_conditionMenorRenderer;
	}
	
    public function _prepareToRender()
    {
        $this->addColumn(self::NUMERO_PARCELAS, array(
            'label' => Mage::helper('installments')->__('Número de Parcelas'),
            'style' => 'width:40px',
        ));
        $this->addColumn(self::CONDICAO_MAIOR, array(
            'label' => Mage::helper('installments')->__('Condição'),
            'style' => 'width:80px',
        	'renderer' => $this->_getConditionMaiorRenderer(),
        ));
        $this->addColumn(self::RANGE_INICIAL, array(
            'label' => Mage::helper('installments')->__('Início'),
            'style' => 'width:40px',
        ));
        $this->addColumn(self::CONDICAO_MENOR, array(
            'label' => Mage::helper('installments')->__('Condição'),
            'style' => 'width:80px',
        	'renderer' => $this->_getConditionMenorRenderer(),
        ));
        $this->addColumn(self::RANGE_FINAL, array(
            'label' => Mage::helper('installments')->__('Final'),
            'style' => 'width:40px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Adicionar faixa de valores');
    }

    /**
     * Prepare existing row data object
     *
     * @param Varien_Object
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getConditionMenorRenderer()->calcOptionHash($row->getData(self::CONDICAO_MENOR)),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_' . $this->_getConditionMaiorRenderer()->calcOptionHash($row->getData(self::CONDICAO_MAIOR)),
            'selected="selected"'
        );
    }
}