<?php
            class Cat {
                public $isAlive = true;
                public $numLegs = 4;
                public $name;
                
                public function __construct($name) {
                    $this->name = $name;
                }
                public function meow()  {
                    return "Meow meow";
                }
            } 
            $cat1 = new Cat("CodeCat");
            echo $cat1->meow();
       
die;
?>

<!--<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />-->
<?php header("Content-type: text/html; charset=utf-8"); ?>

<?php
// Declarando os valores das variáveis
$a = 4;
$b = 2;

?>
<h2>Adição</h2>
<p>
<?php

echo $a + $b;

?>
</p>
<h2>Subtração</h2>
<p>
<?php

echo $a - $b;

?>
</p>
<h2>Multiplicação</h2>
<p>
<?php

echo $a * $b;

?>
</p>
<h2>Divisão</h2>
<p>
<?php

echo $a / $b;

?>
</p>
<h2>Módulo(resto da divisão)</h2>
<p>
<?php

echo $a % $b;

?>
</p>


