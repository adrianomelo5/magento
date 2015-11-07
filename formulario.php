<?php 
require_once('cadastro.php');
header("Content-type: text/html; charset=utf-8"); ?>

<form action="cadastro.php" method="post">
 <p>Nome : <input type="text" name="nome" /></p>
 <p>Cargo : 
 <select name="cargo"> 
    <option value="presidente">Presidente</option> 
    <option value="faxineiro">Faxineiro</option> 
    <option value="vendedor">Vendedor</option>
 </select>    
 </p>
 <p>Salário : <input type="text" name="salario" /></p>
 <p>Telefone : <input type="text" name="telefone" /></p>
 <p>Endereço : <input type="text" name="endereco" /></p>
 <p>CEP : <input type="text" name="cep" /></p>
 <p>Estado :
 <select name="estado"> 
<?php    
    $estados = estados();
    
    foreach ($estados as $key => $desc){
     
        
        
        echo "<option value='$key'>$desc</option>"; 
    } 
    
 ?>
</select>
         
 <p>Data Nascimento : <input type="text" name="data_nascimento" /></p>
 <p><input type="submit" value="Enviar"/></p>
</form>

<br/>
<br/>


<form action="cadastro.php" method="post">
 <p>Produto : <input type="text" name="produto" /></p>
 <p>Tipo : 
 <select name="tipo"> 
    <option value="livros">Livro</option> 
    <option value="caderno">Caderno</option> 
    <option value="lapis">Lápis</option>
 </select>    
 </p>
 <p>Quantidade : <input type="text" name="quantidade" /></p>
 <p>Preço : <input type="text" name="preco" /></p>
 <p>Matrícula Funcionário : <input type="text" name="matricula" /></p>
 <p><input type="submit" value="Enviar"/></p>
</form>

<br/>
<br/>

<div sytle="border:1px solid #000;">    
 <?php
// echo mediaSalario('vendedor');
// 
// mediaPreco();
// echo "<br/>";
// somaProdutos();
 echo "<br/>";
 idade();
 

 
?>
</div>
