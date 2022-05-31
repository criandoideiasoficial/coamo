<?php

$valor = 10;
$diasAtraso = 30;
$jurosAd = 0.05;

//juros simples
$Juros = $valor *(1+($diasAtraso*($jurosAd/100)));
echo $Juros;
echo'<br>';

//Juros Compostos 
$Juros = $valor *((1+($jurosAd/100))**$diasAtraso);
echo $Juros;
echo'<br>';


//Juros Mistos 
//Este tipo de juro é a combinação do simples com o composto:

//Até 30 dias, é calculado o juro simples:
$Juros = $valor *(1+($diasAtraso*($jurosAd/100)));
echo $Juros;
echo'<br>';

//Acima de 30 dias, calcula-se o juro compostos:
$JurosSimples = $valor *(1+(30*($jurosAd/100)));
$Juros = $JurosSimples*((1+($jurosAd/100))**-30);
echo $Juros;
echo'<br>';

?>