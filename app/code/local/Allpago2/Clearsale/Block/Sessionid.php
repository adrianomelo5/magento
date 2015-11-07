<?php

class Allpago_Clearsale_Block_Sessionid extends Mage_Core_Block_Template {
    

    public function createRandomVal() {
        Mage::getModel('core/session')->setClearsaleSessionId();
        $chars = "abcdTUVWXYZefghi01234jklzABCDEFGHIJK56789LMNOPQRS";
        srand((double) microtime() * 1000000);
        $i = 0;
        $id = '';
        while ($i <= 26) {
            $num = rand() % 33;
            $tmp = substr($chars, $num, 1);
            $id = $id . $tmp;
            $i++;
        }
        Mage::getModel('core/session')->setClearsaleSessionId($id);
        return $id;
    }

}

