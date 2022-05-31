
<?php

require_once 'feriado.php';


function somar_dias_uteis( $str_data, $int_qtd_dias_somar, $feriados )
{
    // Caso seja informado uma data do MySQL do tipo DATETIME - aaaa-mm-dd 00:00:00
    // Transforma para DATE - aaaa-mm-dd
    $str_data = substr( $str_data, 0, 10 );
    // Se a data estiver no formato brasileiro: dd/mm/aaaa
    // Converte-a para o padrão americano: aaaa-mm-dd
    if ( preg_match( "@/@", $str_data ) == 1 ) {
        $str_data = implode( "-", array_reverse( explode( "/", $str_data ) ) );
    }
    // chama a funcao que calcula a pascoa
    $pascoa_dt = dataPascoa( date( 'Y' ) );
    $aux_p = explode( "/", $pascoa_dt );
    $aux_dia_pas = $aux_p[0];
    $aux_mes_pas = $aux_p[1];
    $pascoa = "$aux_mes_pas" . "-" . "$aux_dia_pas"; // crio uma data somente como mes e dia
    // chama a funcao que calcula o carnaval
    $carnaval_dt = dataCarnaval( date( 'Y' ) );
    $aux_carna = explode( "/", $carnaval_dt );
    $aux_dia_carna = $aux_carna[0];
    $aux_mes_carna = $aux_carna[1];
    $carnaval = "$aux_mes_carna" . "-" . "$aux_dia_carna";
    // chama a funcao que calcula corpus christi
    $CorpusChristi_dt = dataCorpusChristi( date( 'Y' ) );
    $aux_cc = explode( "/", $CorpusChristi_dt );
    $aux_cc_dia = $aux_cc[0];
    $aux_cc_mes = $aux_cc[1];
    $Corpus_Christi = "$aux_cc_mes" . "-" . "$aux_cc_dia";
    // chama a funcao que calcula a sexta feira santa
    $sexta_santa_dt = dataSextaSanta( date( 'Y' ) );
    $aux = explode( "/", $sexta_santa_dt );
    $aux_dia = $aux[0];
    $aux_mes = $aux[1];
    $sexta_santa = "$aux_mes" . "-" . "$aux_dia";
    $feriados = array( "01-01", $carnaval, $sexta_santa, $pascoa, $Corpus_Christi, "04-21", "05-01", "06-12", "07-09", "07-16", "09-07", "10-12", "11-02", "11-15", "12-25" );
    $array_data = explode( '-', $str_data );
    $count_days = 0;
    $int_qtd_dias_uteis = 0;
    while ( $int_qtd_dias_uteis < $int_qtd_dias_somar ) {
        $count_days++;
        $day = date( 'm-d', strtotime( '+' . $count_days . 'day', strtotime( $str_data ) ) );
        if ( ($dias_da_semana = gmdate( 'w', strtotime( '+' . $count_days . ' day', gmmktime( 0, 0, 0, $array_data[1], $array_data[2], $array_data[0] ) ) ) ) != '0' && $dias_da_semana != '6' && !in_array( $day, $feriados ) ) {
            $int_qtd_dias_uteis++;
        }
    }
    return gmdate( 'd/m/Y', strtotime( '+' . $count_days . ' day', strtotime( $str_data ) ) );
}

?>

<!-- feriado.php -->

<?php


function dataPascoa( $ano = false, $form = "d/m/Y" ) {
    $ano = $ano ? $ano : date( "Y" );
    if ( $ano < 1583 ) {
        $A = ($ano % 4);
        $B = ($ano % 7);
        $C = ($ano % 19);
        $D = ((19 * $C + 15) % 30);
        $E = ((2 * $A + 4 * $B - $D + 34) % 7);
        $F = ( int ) (($D + $E + 114) / 31);
        $G = (($D + $E + 114) % 31) + 1;
        return date( $form, mktime( 0, 0, 0, $F, $G, $ano ) );
    } else {
        $A = ($ano % 19);
        $B = ( int ) ($ano / 100);
        $C = ($ano % 100);
        $D = ( int ) ($B / 4);
        $E = ($B % 4);
        $F = ( int ) (($B + 8) / 25);
        $G = ( int ) (($B - $F + 1) / 3);
        $H = ((19 * $A + $B - $D - $G + 15) % 30);
        $I = ( int ) ($C / 4);
        $K = ($C % 4);
        $L = ((32 + 2 * $E + 2 * $I - $H - $K) % 7);
        $M = ( int ) (($A + 11 * $H + 22 * $L) / 451);
        $P = ( int ) (($H + $L - 7 * $M + 114) / 31);
        $Q = (($H + $L - 7 * $M + 114) % 31) + 1;
        return date( $form, mktime( 0, 0, 0, $P, $Q, $ano ) );
    }
}


function dataCarnaval( $ano = false, $form = "d/m/Y" ) {
    $ano = $ano ? $ano : date( "Y" );
    $a = explode( "/", dataPascoa( $ano ) );
    return date( $form, mktime( 0, 0, 0, $a[1], $a[0] - 47, $a[2] ) );
}


// formato = formatacao da funcao date() http://br.php.net/date
//	 Padrao: d/m/Y

function dataCorpusChristi( $ano = false, $form = "d/m/Y" ) {
    $ano = $ano ? $ano : date( "Y" );
    $a = explode( "/", dataPascoa( $ano ) );
    return date( $form, mktime( 0, 0, 0, $a[1], $a[0] + 60, $a[2] ) );
}



function dataSextaSanta( $ano = false, $form = "d/m/Y" ) {
    $ano = $ano ? $ano : date( "Y" );
    $a = explode( "/", dataPascoa( $ano ) );
    return date( $form, mktime( 0, 0, 0, $a[1], $a[0] - 2, $a[2] ) );
}

?>