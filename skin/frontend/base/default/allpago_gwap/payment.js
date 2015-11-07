function initScriptCard(method,grandTotal){
    if(method == 'gwap_cc'){
        gwapCc();
    }else if(method == 'gwap_2cc'){
        gwap2Cc(grandTotal);
    } 
}

function gwapCc(){

    jQuery(document).ready(function(){
        
        //jQuery('#gwap_cc_cc_number').mask("9999-9999-9999-9999");
        //jQuery('#gwap_cc_cc_cid').mask("999");
        jQuery('#gwap_cc_cc_number').mask("9999-9999-9999-99?99");
        jQuery('#gwap_cc_cc_cid').mask("999?9");
        
        jQuery('#gwap_cc_cc_number').keyup(function(){
            if (['6363-68','4389-35','5041-75','4514-16','6362-97','5090-48','5090-67','5090-49','5090-69','5090-50','5090-74','5090-68','5090-40','5090-45','5090-51','5090-46','5090-66','5090-47','5090-42','5090-52','5090-43','5090-64','5090-40'].indexOf(jQuery('#gwap_cc_cc_number').val()[0]+jQuery('#gwap_cc_cc_number').val()[1]+jQuery('#gwap_cc_cc_number').val()[2]+jQuery('#gwap_cc_cc_number').val()[3]+jQuery('#gwap_cc_cc_number').val()[4]+jQuery('#gwap_cc_cc_number').val()[5]+jQuery('#gwap_cc_cc_number').val()[6]) >= 0){
                jQuery('#gwap_cc_cc_type_ELO').click();                                                    
            } else if (['5067','4576','4011'].indexOf(jQuery('#gwap_cc_cc_number').val()[0]+jQuery('#gwap_cc_cc_number').val()[1]+jQuery('#gwap_cc_cc_number').val()[2]+jQuery('#gwap_cc_cc_number').val()[3]) >= 0){
                jQuery('#gwap_cc_cc_type_ELO').click();                                    
            } else if( ['51','52','53','54','55'].indexOf(jQuery('#gwap_cc_cc_number').val()[0]+jQuery('#gwap_cc_cc_number').val()[1]) >= 0) {
                jQuery('#gwap_cc_cc_type_MASTER').click();
            } else if( ['30', '36', '38'].indexOf(jQuery('#gwap_cc_cc_number').val()[0]+jQuery('#gwap_cc_cc_number').val()[1]) >= 0) {
                jQuery('#gwap_cc_cc_type_DINERS').click();
            } else if( ['34', '37'].indexOf(jQuery('#gwap_cc_cc_number').val()[0]+jQuery('#gwap_cc_cc_number').val()[1]) >= 0) {
                jQuery('#gwap_cc_cc_type_AMEX').click();
            } else if(jQuery('#gwap_cc_cc_number').val()[0] == 4) {
                jQuery('#gwap_cc_cc_type_VISA').click();
            } else if(['60', '38'].indexOf(jQuery('#gwap_cc_cc_number').val()[0]+jQuery('#gwap_cc_cc_number').val()[1]) >= 0) {            
                jQuery('#gwap_cc_cc_type_HIPERCARD').click();                  
            } else if(['21', '18'].indexOf(jQuery('#gwap_cc_cc_number').val()[0]+jQuery('#gwap_cc_cc_number').val()[1]) >= 0) {
                jQuery('#gwap_cc_cc_type_JCB').click();       
            } else if(['6042'].indexOf(jQuery('#gwap_cc_cc_number').val()[0]+jQuery('#gwap_cc_cc_number').val()[1]+jQuery('#gwap_cc_cc_number').val()[2]+jQuery('#gwap_cc_cc_number').val()[3]) >= 0) {
                jQuery('#gwap_cc_cc_type_CABAL').click();                   
            } else if(['60', '65'].indexOf(jQuery('#gwap_cc_cc_number').val()[0]+jQuery('#gwap_cc_cc_number').val()[1]) >= 0) {
                jQuery('#gwap_cc_cc_type_DISCOVER').click();                                
            } else {
                jQuery('.payment-option-gwap_cc').removeClass('selected');
            }
        })
    })
}


function numberFormat(n) {
    var parts=n.toString().split(".");
    return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".") + (parts[1] ? "," + parts[1] : "");
}        

function gwap2Cc(grandTotal){

    jQuery(document).ready(function(){
        
        //jQuery('#gwap_2cc_number').mask("9999-9999-9999-9999");
        //jQuery('#gwap_2cc_cid').mask("999");
        jQuery('#gwap_2cc_number').mask("9999-9999-9999-99?99");
        jQuery('#gwap_2cc_cid').mask("999?9");
        jQuery('#gwap_2cc_amount').priceFormat({
            prefix: 'R$ ',
            centsSeparator: ',',
            thousandsSeparator: '.'
        });
        jQuery('#gwap_2cc_amount').keyup(function(){
            var customValue = jQuery(this).val();
            var originalValue = parseFloat(grandTotal)
            customValue = parseFloat(((customValue.replace('R$','')).replace('.','')).replace(',','.'));
            if(customValue > originalValue){
                alert('O valor informado é maior que o total do pedido');
                jQuery('#gwap_2cc_amount2').val('');
                return false;
            }
            customValue = originalValue-customValue;
            jQuery('#gwap_2cc_amount2').val('R$ '+numberFormat(customValue.toFixed(2)))
        }) 
        
        
        jQuery('#gwap_2cc_number').keyup(function(){
            
            if (['6363-68','4389-35','5041-75','4514-16','6362-97','5090-48','5090-67','5090-49','5090-69','5090-50','5090-74','5090-68','5090-40','5090-45','5090-51','5090-46','5090-66','5090-47','5090-42','5090-52','5090-43','5090-64','5090-40'].indexOf(jQuery('#gwap_2cc_number').val()[0]+jQuery('#gwap_2cc_number').val()[1]+jQuery('#gwap_2cc_number').val()[2]+jQuery('#gwap_2cc_number').val()[3]+jQuery('#gwap_2cc_number').val()[4]+jQuery('#gwap_2cc_number').val()[5]+jQuery('#gwap_2cc_number').val()[6]) >= 0){
                jQuery('#gwap_2cc_type_ELO').click();                                                    
            } else if (['5067','4576','4011'].indexOf(jQuery('#gwap_2cc_number').val()[0]+jQuery('#gwap_2cc_number').val()[1]+jQuery('#gwap_2cc_number').val()[2]+jQuery('#gwap_2cc_number').val()[3]) >= 0){
                jQuery('#gwap_2cc_type_ELO').click();                                    
            } else if( ['51','52','53','54','55'].indexOf(jQuery('#gwap_2cc_number').val()[0]+jQuery('#gwap_2cc_number').val()[1]) >= 0) {
                jQuery('#gwap_2cc_type_MASTER').click();
            } else if( ['30', '36', '38'].indexOf(jQuery('#gwap_2cc_number').val()[0]+jQuery('#gwap_2cc_number').val()[1]) >= 0) {
                jQuery('#gwap_2cc_type_DINERS').click();
            } else if( ['34', '37'].indexOf(jQuery('#gwap_2cc_number').val()[0]+jQuery('#gwap_2cc_number').val()[1]) >= 0) {
                jQuery('#gwap_2cc_type_AMEX').click();
            } else if (jQuery('#gwap_2cc_number').val()[0] == 4){
                jQuery('#gwap_2cc_type_VISA').click();     
            } else if(['60', '38'].indexOf(jQuery('#gwap_2cc_number').val()[0]+jQuery('#gwap_2cc_number').val()[1]) >= 0) {            
                jQuery('#gwap_2cc_type_HIPERCARD').click();                  
            } else if(['21', '18'].indexOf(jQuery('#gwap_2cc_number').val()[0]+jQuery('#gwap_2cc_number').val()[1]) >= 0) {
                jQuery('#gwap_2cc_type_JCB').click();   
            } else if(['6042'].indexOf(jQuery('#gwap_cc_cc_number').val()[0]+jQuery('#gwap_cc_cc_number').val()[1]+jQuery('#gwap_cc_cc_number').val()[2]+jQuery('#gwap_cc_cc_number').val()[3]) >= 0) {
                jQuery('#gwap_cc_cc_type_CABAL').click();                      
            } else if(['60', '65'].indexOf(jQuery('#gwap_2cc_number').val()[0]+jQuery('#gwap_2cc_number').val()[1]) >= 0) {
                jQuery('#gwap_2cc_type_DISCOVER').click();                                
                
            } else {
                jQuery('.payment-option-gwap_2cc').removeClass('selected');
            }
        })
        
        //jQuery('#gwap_2cc_number2').mask("9999-9999-9999-9999");
        //jQuery('#gwap_2cc_cid2').mask("999");
        jQuery('#gwap_2cc_number2').mask("9999-9999-9999-99?99");
        jQuery('#gwap_2cc_cid2').mask("999?9");
        
        
        jQuery('#gwap_2cc_number2').keyup(function(){
            
            if (['6363-68','4389-35','5041-75','4514-16','6362-97','5090-48','5090-67','5090-49','5090-69','5090-50','5090-74','5090-68','5090-40','5090-45','5090-51','5090-46','5090-66','5090-47','5090-42','5090-52','5090-43','5090-64','5090-40'].indexOf(jQuery('#gwap_2cc_number2').val()[0]+jQuery('#gwap_2cc_number2').val()[1]+jQuery('#gwap_2cc_number2').val()[2]+jQuery('#gwap_2cc_number2').val()[3]+jQuery('#gwap_2cc_number2').val()[4]+jQuery('#gwap_2cc_number2').val()[5]+jQuery('#gwap_2cc_number2').val()[6]) >= 0){
                jQuery('#gwap_2cc_type_ELO2').click();                                                    
            } else if (['5067','4576','4011'].indexOf(jQuery('#gwap_2cc_number2').val()[0]+jQuery('#gwap_2cc_number2').val()[1]+jQuery('#gwap_2cc_number2').val()[2]+jQuery('#gwap_2cc_number2').val()[3]) >= 0){
                jQuery('#gwap_2cc_type_ELO2').click();                                    
            } else if( ['51','52','53','54','55'].indexOf(jQuery('#gwap_2cc_number2').val()[0]+jQuery('#gwap_2cc_number2').val()[1]) >= 0) {
                jQuery('#gwap_2cc_type_MASTER2').click();
            } else if( ['30', '36', '38'].indexOf(jQuery('#gwap_2cc_number2').val()[0]+jQuery('#gwap_2cc_number2').val()[1]) >= 0) {
                jQuery('#gwap_2cc_type_DINERS2').click();
            } else if( ['34', '37'].indexOf(jQuery('#gwap_2cc_number2').val()[0]+jQuery('#gwap_2cc_number2').val()[1]) >= 0) {
                jQuery('#gwap_2cc_type_AMEX2').click();
            } else if (jQuery('#gwap_2cc_number2').val()[0] == 4){
                jQuery('#gwap_2cc_type_VISA2').click();              
            } else if(['60', '38'].indexOf(jQuery('#gwap_2cc_number2').val()[0]+jQuery('#gwap_2cc_number2').val()[1]) >= 0) {            
                jQuery('#gwap_2cc_type_HIPERCARD2').click();  
            } else if(['21', '18'].indexOf(jQuery('#gwap_2cc_number2').val()[0]+jQuery('#gwap_2cc_number2').val()[1]) >= 0) {
                jQuery('#gwap_2cc_type_JCB2').click();                  
            } else if(['6042'].indexOf(jQuery('#gwap_cc_cc_number2').val()[0]+jQuery('#gwap_cc_cc_number2').val()[1]+jQuery('#gwap_cc_cc_number2').val()[2]+jQuery('#gwap_cc_cc_number2').val()[3]) >= 0) {
                jQuery('#gwap_cc_cc_type_CABAL2').click();                      
            } else if(['60', '65'].indexOf(jQuery('#gwap_2cc_number2').val()[0]+jQuery('#gwap_2cc_number2').val()[1]) >= 0) {
                jQuery('#gwap_2cc_type_DISCOVER2').click();                                
                
            } else {
                jQuery('.payment-option-gwap_2cc2').removeClass('selected');
            }
        }) 
        
    })
}

function paymentSelected( element, parent ){
    payment_elements = $$(parent);
    
    for(i=0; i<payment_elements.length; i++){
        payment_elements[i].removeClassName('selected');
    }
    element.addClassName('selected');
}
