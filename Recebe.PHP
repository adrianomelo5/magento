<?php
echo "valor1 " . strtotime("16/09/1986") . "<br/>";

echo "valor2 " . strtotime("16091986") . "<br/>";

echo "valor3 " . strtotime("16-09-1986");

die;
header("Content-type: text/html; charset=utf-8");
function val_subtracao($num1, $num2){
        if($num1 < $num2){
                die("Números negativos");
        }else{
                $return_result=0;
                while($num1 > $num2){
                        $num1 -= 1;
                        $return_result = $return_result + 1;
                }
        }
        return($return_result);
}

$primeiro_op=493;
$segundo_op=355;
$resultado1 = val_subtracao($primeiro_op, $segundo_op);
print ("Resultado1 é $resultado1<br>");
$resultado2 = val_subtracao($primeiro_op, $segundo_op);
print("Resultado2 é $resultado2<br>");



die;
function depositar($valor)
    {
        static $total_depositos;
        $total_depositos++;
        $saldo += $valor;
        echo "Foram feitos ".$total_depositos." depósitos.";
    }
 
    function sacar($valor)
    {
        static $total_saques;
        $total_saques++;
        $saldo -= $valor;
        echo "Foram feitos ".$total_saques." saques.";
    }

die;
function par_ou_impar($num)
    {
        $resto = $num % 2;
        if($resto == 0)
            return "PAR";
        else
            return "IMPAR";
    }
    //fora da função não é possível acessar a variável $resto
   
    echo par_ou_impar(9);
    
die;
header("Content-type: text/html; charset=utf-8");
//definição da função
//chamando a função
$nome = "Joel Rodrigues";
$nome = exibir_mensagem($nome);
echo "<br/>".$nome ;



function exibir_mensagem($nome)
{
    echo " Olá <br/> ";
    $nome = "gluglu";
    echo $nome ;
     return $nome;
}




die;
if ( isset($_POST['name']) && ($_POST['name']) ) {
    
    if ( isset($_POST['age']) && ($_POST['age']) ) {
        
        echo "Hi {$_POST['name']}, you are " . (int)$_POST['age'] . " years old.";
        
    }   else  {
    
        echo "Falta IDADE !";
    
    }  

}  else  {
    
    echo "Falta o nome !";
    
}
