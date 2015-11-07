<?php


class Allpago_Mc_Model_Source_Hours
{
    public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>'1h'),
            array('value'=>2, 'label'=>'2h'),
            array('value'=>4, 'label'=>'4h'),
            array('value'=>8, 'label'=>'8h'),
            array('value'=>12, 'label'=>'12h'),
            array('value'=>18, 'label'=>'18h'),
            array('value'=>24, 'label'=>'24h')
        );
    }
}