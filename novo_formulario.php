

<?php 

require_once('cadastro.php'); ?>

<style>

*{ margin:0; padding:0;}

body{ font:100% normal Arial, Helvetica, sans-serif; background:#838484;}

form,input,select,textarea{margin:0; padding:0; color:#ffffff;}

div.box {
    margin:0 auto;
    width:500px;
    background:#222222;
    float: left;
    margin: 57px 0px 0px 77px;
    border:1px solid #262626;
    border-radius:6px;
}
div.box h1 { 
    color:#ffffff;
    font-size:18px;
    text-transform:uppercase;
    padding:5px 0 5px 5px;
    border-bottom:1px solid #161712;
    border-top:1px solid #161712; 
}

div.box label {
    width:100%;
    display: block;
    background:#1C1C1C;
    border-top:1px solid #262626;
    border-bottom:1px solid #161712;
    padding:10px 0 10px 0;
}

div.box label span {
    display: block;
    color:#bbbbbb;
    font-size:12px;
    float:left;
    width:100px;
    text-align:right;
    padding:5px 20px 0 0;
}

div.box .input_text {
    padding:10px 10px;
    width:200px;
    background:#262626;
    border-bottom: 1px double #171717;
    border-top: 1px double #171717;
    border-left:1px double #333333;
    border-right:1px double #333333;
}

div.box .message{
    padding:7px 7px;
    width:350px;
    background:#262626;
    border-bottom: 1px double #171717;
    border-top: 1px double #171717;
    border-left:1px double #333333;
    border-right:1px double #333333;
    overflow:hidden;
    height:150px;
}

div.box .button{
    margin:0 0 10px 0;
    padding:4px 7px;
    background:#CC0000;
    border:0px;
    width:100px;
    border-bottom: 1px double #660000;
    border-top: 1px double #660000;
    border-left:1px double #FF0033;
    border-right:1px double #FF0033;
}

</style>

<html>
    <head>

        
        <meta charset="utf-8" /> 
        <title>Livraria SARAVÁ</title>
        
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js" ></script>
        <script type="text/javascript">
        jQuery(document).ready(function(){
            jQuery("input").blur(function(){               
             if(jQuery(this).val() == "")
                 {
                     jQuery(this).css({"border" : "1px solid #F00"});
                 }else{
                    jQuery(this).css({"border-bottom" : "1px double #171717",
                                      "border-top" : "1px double #171717",
                                      "border-left" : "1px double #333333",
                                      "border-right" : "1px double #333333"});
                 }
            });
            
            jQuery("#cargo").blur(function(){               
                jQuery(this).each(function(){
                    if(jQuery(this).val() == "")
                    {
                        jQuery(this).css({"border" : "1px solid #F00"});
                    }else{
                        jQuery(this).css({  "border-bottom" : "1px double #171717",
                                            "border-top" : "1px double #171717",
                                            "border-left" : "1px double #333333",
                                            "border-right" : "1px double #333333"});
                     }
                });
            });
                
            jQuery('#estado').blur(function() {
                jQuery(this).each(function()  {
                    if(jQuery(this).val() == "") 
                    {
                        jQuery(this).css({"border" : "1px solid #F00"});
                    }else{
                        jQuery(this).css({  "border-bottom" : "1px double #171717",
                                            "border-top" : "1px double #171717",
                                            "border-left" : "1px double #333333",
                                            "border-right" : "1px double #333333"});
                    }
                });
             });
             
             jQuery('#tipo').blur(function() {
                jQuery(this).each(function()  {
                    if(jQuery(this).val() == "") 
                    {
                        jQuery(this).css({"border" : "1px solid #F00"});
                    }else{
                        jQuery(this).css({  "border-bottom" : "1px double #171717",
                                            "border-top" : "1px double #171717",
                                            "border-left" : "1px double #333333",
                                            "border-right" : "1px double #333333"});
                    }
                });
             });
             
            jQuery("#button_funcionarios").click(function(){
                var cont = 0;
                jQuery("#form_funcionarios input").each(function(){
                    if(jQuery(this).val() == ""){
                        jQuery(this).css({"border" : "1px solid #F00"});
                        cont++;
                    }
                });
                
                jQuery("#form_funcionarios select").each(function(){
                    if(jQuery(this).val() == ""){
                        jQuery(this).css({"border" : "1px solid #F00"});
                        cont++;
                    }
                });
                
                if(cont == 0)
                {
                    var dados = jQuery( '#form_funcionarios' ).serialize();

			jQuery.ajax({
				type: "POST",
				url: "cadastro.php",
				data: dados,
				success: function( data )
				{
					alert( data );
				}
			});
                }
            });
        
            jQuery("#button_vendas").click(function(){
                var cont = 0;
                jQuery("#form_vendas input").each(function(){
                    if(jQuery(this).val() == ""){
                        jQuery(this).css({"border" : "1px solid #F00"});
                        cont++;
                    }
                });
                
                jQuery("#form_vendas select").each(function(){
                    if(jQuery(this).val() == ""){
                        jQuery(this).css({"border" : "1px solid #F00"});
                        cont++;
                    }
                });
                
                if(cont == 0)
                {
                    var dados = jQuery( '#form_vendas' ).serialize();

			jQuery.ajax({
				type: "POST",
				url: "cadastro.php",
				data: dados,
				success: function( data )
				{
					alert( data );
				}
			});
                }
            });
    
    
    
    
        });
        
        
        
            
        </script>

    </head> 
    <body> 
        <form action="cadastro.php" name="form_funcionarios" method="post" id="form_funcionarios">
            <div class="box"> 
                <h1>Cadastro De Funcionários :</h1>
                <label> 
                    <span>Nome</span>
                    <input type="text" class="input_text" name="nome" id="nome"/>
                </label>

                <label>
                    <span>Cargo</span>
                    <select name="cargo" class="input_text" id="cargo"> 
                        <option value="">Selecionar opção</option> 
                        <option value="presidente">Presidente</option> 
                        <option value="faxineiro">Faxineiro</option> 
                        <option value="vendedor">Vendedor</option>
                    </select>    
                </label>
                
                <label>
                    <span>Salário</span>
                    <input type="text" class="input_text" name="salario" id="salario"/>
                </label>
                
                <label>
                    <span>Telefone</span>
                    <input type="text" class="input_text" name="telefone" id="telefone"/>
                </label>
                
                <label>
                    <span>Endereço</span>
                    <input type="text" class="input_text" name="endereco" id="endereco"/>
                </label>
                
                <label>
                    <span>CEP</span>
                    <input type="text" class="input_text" name="cep" id="cep"/>
                </label>
                
                <label>
                    <span>Estado</span>
                    <select name="estado" class="input_text" id="estado" > 
                        <?php    
                            $estados = estados();
    
                            foreach ($estados as $key => $desc){
                            echo "<option value='$key'>$desc</option>"; 
                        } 

                     ?>
                    </select>
                </label>
                
                <label>
                <span>Data Nascimento</span>
                  <input type="text" class="input_text" name="data_nascimento" id="data_nascimento"/>  
                </label> 
                
                <div style="text-align: center;background:#1C1C1C;">   
                  <input type="button" class="button" id="button_funcionarios" value="Enviar" />       
                  
                </div>

                
            </div>
        </form> 
        
        
        
        <form action="cadastro.php" name="form_vendas" method="post" id="form_vendas">
            <div class="box"> 
                <h1>VENDAS :</h1>
                <label> 
                    <span>Produto</span>
                    <input type="text" class="input_text" name="produto" id="produto"/>
                </label>

                <label>
                    <span>Tipo</span>
                    <select name="tipo" class="input_text" id="tipo"> 
                        <option value="">Selecionar opção</option>
                        <option value="livros">Livro</option> 
                        <option value="caderno">Caderno</option> 
                        <option value="lapis">Lápis</option>
                     </select>   
                </label>
                
                <label>
                    <span>Quantidade</span>
                    <input type="text" class="input_text" name="quantidade" id="quantidade"/>
                </label>
                
                <label>
                    <span>Preço</span>
                    <input type="text" class="input_text" name="preco" id="preco"/>
                </label>
                
                <label>
                    <span>Matrícula Funcionário</span>
                    <input type="text" class="input_text" name="matricula" id="matricula"/>
                </label>
                
                <div style="text-align: center;background:#1C1C1C;">
                    <input type="button" class="button" id="button_vendas" value="Enviar" /> 
                </div>
            
            </div>
        </form> 
    </body>
</html>

<!DOCTYPE html>
<html>
    <head>
		<title></title>
	</head>
	<body>
    
            
            
            
            
            
            
            
            
            
            
            