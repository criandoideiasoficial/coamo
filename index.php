<?php
//primeiro vamos conectar ao banco de dados
$conn = mysqli_connect("localohst", "UsCoamo", "Sncoamo*", "DbCoamo");

//vamos verificar se não a erro de conexão
if (!$conn) {
    echo "Error: Falha ao conectar-se com o banco de dados MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

//levar consideração que Cooperativa já fez login, por app ou site em uma tela
//e retonou o id de login já conecta pois não foi informado qual tipo de login cooperativa teria que fazer.
$IdCooperativa = 1; //pode ser armazenada de várias formas mais vou tentar simplificar o sistema.

//levando isso consideração vou criar exibição do produto conforme ele seleciona página

//1 criando sql
// home ele pega produtos Status 1 de ativo e At diferente de 0 ou maior que 0 estoque
$sql = "SELECT * from Insumos where Insumos_Status = 1 and Insumo_Qt >= 0"; 
$result = $conn->query($sql); // consulta do sql

//primeiro vajo se resultado maior de 0 aí passo para exibir
if ($result->num_rows > 0) {
    // vaimos exbir o produto
    while($row = $result->fetch_assoc()) {
      echo "id: " . $row["idInsumos"]. " - Nome: " . $row["Insumo_Nome"]. " - Quantidade Disponivel" . $row["Insumo_QT"]. 
      " - Valor R$: " . number_format($row["Insumo_Valor"],2,",","."); // number convert para valor real
    }
  } else {
    echo "Nem um produto foi encontrado.";
  }

  //agora posso fazer mesmo por categorias
  //também posso juntar tabelas qualquer momento usando join  
  $sqlIns = 'SELECT * FROM Insumos
  INNER JOIN Categorias_Insumos
  ON Insumos.idInsumos = Categorias_Insumos.IdCategoria_Insumos 
  and Insumos.Insumos_Status = 1 
  and Insumos.Insumo_Qt = 0
  and Categorias_Insumos.IdCategoria_Insumos = 1'; //verifica se estado produto ativo, se tem estoque
  //LIKE 'a%' para achar por nome algo do tipo caso for por busca.
  $result = $conn->query( $sqlIns); //consultanto
//resultado
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      echo "id: " . $row["idInsumos"]. " - Nome: " . $row["Insumo_Nome"]. " - Quantidade Disponivel" . $row["Insumo_QT"]. 
      " - Valor R$: " . number_format($row["Insumo_Valor"],2,",","."); // number convert para valor real
    }
  } else {
    echo "Nem um produtos foi encontado.";
  }

//considerando que ele está vendo os produtos click em um botao de adicionar carrinho
//vou criar um carrinho simples para uso da lógica, mas pode ser criado de varios maneiras até com banco de dados para uso 
//caso ele saia da loja e volte mais tarde.
//vou levar em conta que cliente viu produto na loja com dados e valor, e ele vai só adicionar os produtos que quer no carrinho
//com isso temos as ações abaixo para adicionar, remover e alterar, quando estiver no carrinho ou nos produtos.

session_start();
    if(!isset($_SESSION['carrinho'])){
        $_SESSION['carrinho'] = array();
    } //cria um carrinho 

    if(isset($_GET['acao'])){
        //ADICIONAR CARRINHO
        if($_GET['acao'] == 'add'){
            $id = intval($_GET['idInsumo']);
            if(!isset($_SESSION['carrinho'][$id])){
                $_SESSION['carrinho'][$id] = 1;
            } else {
                $_SESSION['carrinho'][$id] += 1;
            }
        } //REMOVER CARRINHO 

        if($_GET['acao'] == 'del'){
            $id = intval($_GET['idInsumo']);
            if(isset($_SESSION['carrinho'][$id])){
                unset($_SESSION['carrinho'][$id]);
            }
        } //ALTERAR QUANTIDADE
        if($_GET['acao'] == 'up'){
            if(is_array($_POST['prod'])){
                foreach($_POST['prod'] as $id => $qtd){
                        $id  = intval($id);
                        $qtd = intval($qtd);
                        if(!empty($qtd) || $qtd <> 0){
                            $_SESSION['carrinho'][$id] = $qtd;
                        }else{
                            unset($_SESSION['carrinho'][$id]);
                        }
                }
            }
        }
    }
//no carrinho vai exibir dados do produto na session
//que vai ser bem parecido com pedido para onde vamos agora

//obs não tem nada sobre frete

//agora vou pegar todos dados da Cooperativa ou Cliente para criar pedido final, envio por email, sms, gravação de dados e criação de notas fiscais.
$sqlCoop = "SELECT *  FROM Cooperativas WHERE idCooperativa = ".$IdCooperativa; //lembrando que id ja tinha no início do codigo quando cliente entrou no sistema.
$qrCoop    = mysqli_query($conn, $sqlCoop) or die( 'Estamos com Problema no Momento tente mais tarde');  //consulta
$lnCoop = mysqli_fetch_assoc($qrCoop); //jogando as consulta para uso

$nomeCoop = $lnCoop['Coop_Nome']; 
$foneCoop = $lnCoop['Telefone']; 
$emailCoop = $lnCoop['Email']; 
$cnpjCoop = $lnCoop['Cnpj']; 
$cpfCoop =  $lnCoop['CPF']; 
$rgCoop = $lnCoop['RG']; 
$ufCoop = $lnCoop['UF']; 
$cidadeCoop = $lnCoop['Cidade']; 
$cepCoop = $lnCoop['Cep']; 
$endCoop = $lnCoop['Endereco']; 
$endNmCoop= $lnCoop['End_Numero'];

//verificando se Cooperativa e por Cnpj ou CPF
$tipoDeCoop;
if(empty($cnpjCoop)){
    $tipoDeCoop = 2; //se for cnpj vazio então é cpf tipo 2

} else{
    $tipoDeCoop = 1; //é por cnpj
}   
    

//agora vamos fazer calculo final do pedido
//primeiro vamos verificar os produtos
$TotalICSMS = 0; 
$total = 0;
$GrupoPod = 0;
$sub = 0; //valor por produto para usar no insert do pedido
$prodQt = 0; //total de produtos para usar no insert do pedido


   
    foreach($_SESSION['carrinho'] as $id => $qtd){
          $sql   = "SELECT *  FROM Insumos Join Categoria_Insumo Join Insumo_Tipos_UMD 
          and Insumo.idCat_Insumo = Categoria_Insumo.idCategoria_Insumo
          and Insumo.idTipo_Insumo = Insumo_Tipos_UMD.idInsumo_Tipos WHERE idInsumos = '$id'";
          $qr    = mysqli_query($conn, $sql) or die( 'Estamos com Problema no Momento tente mais tarde'); //caso nao consiga conectar
          $ln    = mysqli_fetch_assoc($qr); 
          
         //aqui vou trazer valor com icms conforme os tributos      
         $sqlIcms = 'SELECT * from ICMS_Coop JOIN ICMS_Tipo JOIN ICMS_Uf 
         WHERE ICMS_Coop.id = '.$tipoDeCoop.' AND ICMS_Tipo.id = '.$ln['Insumo_Tipos_UMD.Tipo'].'  AND ICMS_Uf.ICMS_valor_uf = '.$ufCoop;
         $qrICMS    = mysqli_query($conn, $sqlIcms) or die( 'Estamos com Problema no Momento tente mais tarde'); 
         $lnIcms    = mysqli_fetch_assoc($qrICMS); 
         
          $nome  = $ln['Insumo_Nome']; //exibir nome do produto no pedido
          $preco = $ln['Insumo_Valor']; // exibir valor produto individual
          
          //agora vejo desconto por grupo
          if(!isset($_SESSION['grupo'][$ln['Categoria_Insumo.idCategoria_Insumo']])){
            //se esse grupo já existe não faça nada
         }else{
             if($GrupoPod >= 5){}else{
            $GrupoPod ++; //adiciona 1% desconto por grupo
             }
         }
         //soma do icms
         $TotalICSMS = $lnIcms['ICMS_Coop.ICMS_Coop_Valor'] + $lnIcms['ICMS_Tipo.ICMS_Tipo_Valor'] + $lnIcms['ICMS_Uf.ICMS_valor_uf']; //somando os icms supondo q seja 4 + 4 + 4 total 12% 
         $valorComIcms = $preco * (1+($TotalICSMS/100));

          $sub =  $valorComIcms * (1-($GrupoPod/100)); //removendo valor 4% exemplo desconto por grupo
          
          $novosub   = $sub * $qtd; //total por quantidade
          $prodQt = $qtd;   
          $total += $novosub; //total geral de produtos
         
      
    }

     //agora desconto por classificação do cliente
     $jurosPrazo = 0.05;// sabendo que já tenho juros ao dia se caso cliente escolheu prazo.
     $tipoClasse; //variável para receber a classificação
     $descontoFormPg; //variável recebera desconto por tipo de pagamento selecionado
     $diasAtraso = 23; //dias uteis poderia criar uma função para pegar só dias mês e ano correto (considerando 01/05/2022 a 01/06/2022)
     //deixei no git um exemplo de ferido a ser usado.

//caso ele escolha pagamento avista ou prazo 
 $formaPg = 2; //supondo que cliente escolha forma de pagamento 2 que seria a prazo, supondo que seja 30 dias boleto como não foi informado essas condições e 1 seria avista deposito
  
 
     //verificando que tipo de desconto vai receber
     switch($formaPg){
         case 1:
             $sqlClass = 'SELECT Classificasao from Cooperativas where idCooperativas = '.$IdCooperativa; //aqui verifico se cliente e classe A B ou C
             $resultClass = $conn->query($sqlClass);
             while($row = $resultClass->fetch_assoc()) {
                 $tipoClasse = $row["Classificasao"];           
               }
             //agora vou fazer switch caso ele seja a b ou c
             switch($tipoClasse){
                 case "A":
                     $descontoFormPg = 5;
                     break;
                 case "B":
                     $descontoFormPg = 3;
                     break;
                 default:
                     $descontoFormPg = 0; //caso não seja A e B desconto e 0
                     break; 
 
             }
         break;
         //se for pagamento prazo já não terá nem um desconto por classificação
         default:
             $descontoFormPg = 0;
         break;
     }
     
      
     //se desconto for igual 0 quer disser que a Cooperativa escolheu prazo
     if($descontoFormPg == 0){ 
        $TotalGeral = $total *((1+($jurosPrazo/100))**$diasAtraso);

     }else{
        // total geral com desconto por Classificação   
        $TotalGeral = $total * (1-($descontoFormPg/100));   

     }

     //agora apos fechar e pagamento e gerado o pedido final    
     $sqlIdfinal = 'SELECT MAX(idPedidosNF) as idNF FROM Pedidos';  //PRIMEIRO VOU PEGAR ULTIMO PEDIDO E CRIAR NOVO NUMERO
     $result =  mysqli_query($conn,$sqlIdfinal) or die( 'Estamos com Problema no Momento tente mais tarde');  
     $dado = mysqli_fetch_array($result);

      $idNf = $dado[0] + 1; //ultima nota adicionar mais 1 numero (considerando que nota seja só de números)

      //pego a sessão do carrinho vou adicionar um linha do pedido para cada produto
      foreach($_SESSION['carrinho'] as $id => $qtd){      
     $sqlPedido = 'INSERT INTO Pedidos ( idPedidosNF, idCooperativa, idInsumo, QtInsumo, ValorInsumo, ValorTotal, DataPedido ) 
                   VALUE   ("'.$idNf.'", "'.$IdCooperativa.'","'.$_SESSION['carrinho'][$id].'","'.$prodQt.'","'.$sub.'", "'.$TotalGeral.'", "'.date('Y-m-d').'" )';
        mysqli_query($conn,$sqlPedido) or die( 'Estamos com Problema no Momento tente mais tarde'); 
      }
      session_destroy(); //limpando carrinho

    //aqui agora posso criar sistema de envio de Email do pedido, depois ir para tela de pagamentos
    //chamando uma nova classe ou funciona
    //ai posso usar dados da Cooperativa para enviar email e gerar nota abaixo
    include_once('email.php');

    //posso fazer abrir pagina de pedido com dados do pedido salvo enviando os dados ou só id da nova nota
    header("Location: pagamento.php?idpd=".$idNf); //só exemplo existe varios métodos.


?>
