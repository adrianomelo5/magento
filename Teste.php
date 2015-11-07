<?php 
define('M_PIiiii', 5.1234);
header("Content-type: text/html; charset=utf-8"); ?>
<html>
  <head>
    <title>Modicando Elements</title>
  </head>
  
  <body>
    <p>
   
     
    <?php
    $nomes = array("Adriano", "Adair", "Alan", "Zelia", "Vó Luiza", "Luiza", "Angela", "Bruce", "Fernando", "Zonta", "Hugo", "Alph", "Renata");
	// Ordene a lista
	sort($nomes);       
    $vencedor = rand(0, count($nomes) - 1);
    print $nomes[$vencedor];
    // Escolha um vencedor de forma aleatória!
        
    // Imprima o nome do vencedor em LETRAS MAIÚSCULAS
    
    exit;
    ?>
  </p>
  <p>
    <?php
    // Coloque seu nome em letras maiúsculas e imprima-o na tela:
    $uppercase = strtoupper($myname);
    print $uppercase;
    ?>
  </p>
  <p>
    <?php
    // Coloque seu nome em letras minúsculas e imprima-o na tela:
    $lowercase = strtolower($uppercase);
    print $lowercase;
    die;
        ?>
    </p>
  </body>
</html>


<!DOCTYPE html>
<html>
    <head>
        <!--conteudo do head-->
    </head>
    <body>
        <!--conteudo do body-->
    </body>
</html>


<!DOCTYPE html>
<html>
    <head>
       <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
       <?php
  //$mensagem = "MENSAGEM TESTANDO";
  
  $mensagem[0] = array(5.5, "Cicalno", "Beltrano","fulano","2000.777");
  $mensagem[] = array(5.5, "Cicalno", "Coisa","Eu","ugugug");
  
     echo $mensagem[0].'<br/>';
  var_dump($mensagem);
  die;
  foreach ($mensagem as $key => $valor){
      echo $key.' '.$valor.'<br/>';
  }
  
  die;
  for ($v=0;$v <  sizeof($mensagem);$v++){
      
      echo $mensagem[$v].'<br/>';
  }
  
  die;
  if ($mensagem!=='' OR $mensagem!==''
          && $mensagem > 1){
      echo 'VERDADEIRO';
  }else{
      echo 'FALSO';
  }
  
 die;
for ($var=1;$var<=100;$var++){
    
      if ($var==10){
         continue;
     }   
   //echo $mensagem ."<br/>";
    if ($var % 2 == 0 ){
        
        echo $var.' é par <br/>';
       
    }else {
        echo $var.' é impar <br/>';
    } 
    
            
            
}
        
?>
    </body>
</html>



<form id='contactus' method='post'>
<fieldset>
<legend>Contact us</legend>
 
<input type='hidden' name='submitted' id='submitted' value='1'/>
 
    <label for='name' >Your Full Name*: </label><br/>
    <input type='text' name='name' id='name'  maxlength="50" /><br/>
 
    <label for='email' >Email Address*:</label><br/>
    <input type='text' name='email' id='email' maxlength="50" /><br/>
 
    <label for='phone' >Phone Number:</label><br/>
    <input type='text' name='phone' id='phone' maxlength="15" /><br/>
 
    <label for='message' >Message:</label><br/>
    <textarea rows="10" cols="50" name='message' id='message'> </textarea>
 
    <input type='submit' name='Submit' value='Submit' />
 
    
</fieldset>
</form>