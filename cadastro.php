<?php

function estados(){
         
    $arrayEstados = array(
		'' => 'Selecionar estado',
                'AC' => 'ACRE',
		'AL' => 'ALAGOAS',
		'AM' => 'AMAZONAS',
		'AP' => 'AMAPA',
		'AC' => 'ACRE',
		'BA' => 'BAHIA',
		'CE' => 'CEARA',
		'DF' => 'DISTRITO FEDERAL',
		'ES' => 'ESPIRITO SANTO',
		'GO' => 'GOIAS',
		'MA' => 'MARANHAO',
		'MT' => 'MATO GROSSO',
		'MS' => 'MATO GROSSO DO SUL',
		'MG' => 'MINAS GERAIS',
		'PA' => 'PARA',
		'PB' => 'PARAIBA',
		'PR' => 'PARANA',
		'PE' => 'PERNAMBUCO',
		'PI' => 'PIAUI',
		'RJ' => 'RIO DE JANEIRO',
		'RN' => 'RIO GRANDE DO NORTE',
		'RO' => 'RONDONIA',
		'RS' => 'RIO GRANDE DO SUL',
		'RR' => 'RORAIMA',
		'SC' => 'SANTA CATARINA',
		'SE' => 'SERGIPE',
		'SP' => 'SAO PAULO',
		'TO' => 'TOCANTINS' 
    );
      return $arrayEstados;
}



function conectar(){
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "magento";
    
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $conn;
    
}
function idade() {
         
      $conn = conectar();
      $statement = $conn->prepare("SELECT * FROM funcionarios" );
      $statement->execute();
      $rows = $statement->fetchAll();
      
      $soma=0;
      
foreach ($rows as $row){

    $date = new DateTime( $row ["data_nascimento"]); 
    $interval = $date->diff( new DateTime( date("Y-m-d") ) );  
    if ($interval->format( '%Y') >=40) {
    $soma ++;
    }
      
    }
echo $soma;
    
    }

function mediaSalario($cargo){
         
      $conn = conectar();
      $statement = $conn->prepare("SELECT * FROM funcionarios WHERE cargo = :cargo");
      $statement->execute(array(':cargo' => $cargo));
      $rows = $statement->fetchAll();
      
      $size = sizeof($rows);
      $soma = 0;
      
   foreach ($rows as $row){ 
       
       $soma += $row['salario'];
    }
    
    echo "R$".number_format($soma / $size,"2",",",".") . "<br/>" ;
  //var_dump($row);
      
      
}
function mediaPreco() {
         
      $conn = conectar();
      $statement = $conn->prepare("SELECT * FROM vendas" );
      $statement->execute();
      $rows = $statement->fetchAll();
      
      $size = sizeof($rows);
      $soma = 0;
      
   foreach ($rows as $row){ 
       
       $soma += $row['preco'];
    }
    
    echo "R$".number_format($soma / $size,"2",",",".") ;  
}
function somaProdutos() {
         
      $conn = conectar();
      $statement = $conn->prepare("SELECT * FROM vendas" );
      $statement->execute();
      $rows = $statement->fetchAll();
      
      $soma_livros = 0;
      $soma_lapis = 0;
      $soma_cadernos = 0;
      
    foreach ($rows as $row){ 
       var_dump($row);
        if ($row['tipo'] == "livro" ){
            $soma_livros += $row['qtd'];
        } elseif ($row['tipo'] == "caderno" ){
            $soma_cadernos += $row['qtd'];
        }elseif ($row['tipo'] === utf8_decode('Lápis')){
            $soma_lapis += $row['qtd'];
        }
       
    }
        echo "Livros: " . $soma_livros . "<br/>";  
        echo "Cadernos: ". $soma_cadernos . "<br/>";
        echo "Lápis: ". $soma_lapis;
       
        }

if ( isset($_POST['nome']) && ($_POST['nome']) ) {
    
            
    //var_dump ($_POST);
    //die;
    

    try {
        
        $conn = conectar();
        // prepare sql and bind parameters
        $write = $conn->prepare("INSERT INTO funcionarios (nome, cargo, salario, telefone, endereco, cep, estado, data_nascimento) 
        VALUES (:nome, :cargo, :salario, :telefone, :endereco, :cep, :estado, :data_nascimento)");
        $write->bindParam(':nome', $nome);
        $write->bindParam(':cargo', $cargo);
        $write->bindParam(':salario', $salario);
        $write->bindParam(':telefone', $telefone);
        $write->bindParam(':endereco', $endereco);
        $write->bindParam(':cep', $cep);
        $write->bindParam(':estado', $estado);
        $write->bindParam(':data_nascimento', $data_nascimento);


        // insert a row
        $nome = utf8_decode($_POST['nome']);
        $cargo = $_POST['cargo'];
        $salario = str_replace(",",".", str_replace(".","",$_POST['salario']) );
        $telefone = $_POST['telefone'];
        $endereco = utf8_decode($_POST['endereco']);
        $cep = $_POST['cep'];
        $estado = $_POST['estado'];
        
        $data_nascimento = str_replace("/","-",$_POST['data_nascimento']);
        $data_nascimento = date('Y/m/d',strtotime($data_nascimento));        
        
        $write->execute();

        
        echo "Dados Enviados com Sucesso !";
        }
    catch(PDOException $e)
        {
        echo "Error: " . $e->getMessage();
        }
    $conn = null;
} elseif ( isset($_POST['produto']) && ($_POST['produto']) ) {
  
   

    try {
        $conn = conectar();

        // prepare sql and bind parameters
        $write = $conn->prepare("INSERT INTO vendas (produto, tipo, qtd, preco, matricula) 
        VALUES (:produto, :tipo, :quantidade, :preco, :matricula)");
        $write->bindParam(':produto', $produto);
        $write->bindParam(':tipo', $tipo);
        $write->bindParam(':quantidade', $quantidade);
        $write->bindParam(':preco', $preco);
        $write->bindParam(':matricula', $matricula);


        // insert a row
        $produto = utf8_decode($_POST['produto']);
        $tipo = utf8_decode($_POST['tipo']);
        $quantidade = $_POST['quantidade'];
        $preco = str_replace(",",".", str_replace(".","",$_POST['preco']) );
        $matricula = $_POST['matricula'];
        $write->execute();

        
        echo "Dados Enviados com Sucesso !";
        }
    catch(PDOException $e)
        {
        echo "Error: " . $e->getMessage();
        }
    $conn = null;
    
}
    
//$date = new DateTime( '1901-10-11' ); // data de nascimento
//$interval = $date->diff( new DateTime( '2011-12-14' ) ); // data definida
//
//echo $interval->format( '%Y Anos, %m Meses e %d Dias' ); // 110 Anos, 2 Meses e 2 Dias
