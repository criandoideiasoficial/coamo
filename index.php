<?php
//primeiro vamos conectar ao banco de dados
$conn = mysqli_connect("localohst", "coamo", "dbcoamo*", "db");

//vamos verificar se não a erro de conexão
if (!$conn) {
    echo "Error: Falha ao conectar-se com o banco de dados MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

//apos isso preciso verfiicar o usuario vou levar consideração que ele fez login, por app ou site
//e retonou o id de login já conecta pois não foi informado qual tipo de login cooperativa teria que fazer.

$IdCooperativa = '1' //pode ser armazenada de varias formas mais vou tentar simplificar o sistema.

//agora ele vai listar produtos, home vc pode add produtos novos promoção ou meno por categoria
//levando isso consideração vou criar exibição do produto conforme ele seleciona página

//1 criando sql
// home ele pega produtos Status 1 de ativo e At diferente de 0 ou maior que 0 estoque
$sql = 'select * from Insumos where Insumos_Status = 1 and Insumo_Qt >= 0'; 
$result = $conn->query($sql); // consulta do sql

//primeiro vajo se resultado maior de 0 ai passo para exibir
if ($result->num_rows > 0) {
    // vaimos exbir o produto
    while($row = $result->fetch_assoc()) {
      echo "id: " . $row["idInsumos"]. " - Nome: " . $row["Insumo_Nome"]. " - Quantidade Disponivel" . $row["Insumo_QT"]. 
      " - Valor R$: " . number_format($row["Insumo_Valor"],2,",","."). ; // number convert para valor real
    }
  } else {
    echo "Nem um produtos foi encontado.";
  }


  //agora posso fazer mesmo por categorias
  //tambem posso juntar tabelas qualquer momento usando join  
  $sql = 'SELECT * FROM Insumos
  INNER JOIN Categorias_Insumos
  ON Insumos.idInsumos = Categorias_Insumos.IdCategoria_Insumos 
  and Insumos_Status.idInsumos = 1 
  and Insumo_Qt.idInsumos >= 0
  and Categorias_Insumos.IdCategoria_Insumos = 1';

$result = $conn->query($sql); //consultanto
//resultado
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      echo "id: " . $row["idInsumos"]. " - Nome: " . $row["Insumo_Nome"]. " - Quantidade Disponivel" . $row["Insumo_QT"]. 
      " - Valor R$: " . number_format($row["Insumo_Valor"],2,",","."). ; // number convert para valor real
    }
  } else {
    echo "Nem um produtos foi encontado.";
  }

//considerando que ele está vendo os produtos click em um botao de adicionar carrinho
//vou criar um carrinho simples para uso da logica, mais pode ser criado de varios manerias até com banco de dados para uso 
//caso ele saia da loja e volte mais tarde.
//vou levar em conta que cliente viu produto na loja com dados e valor, e ele vai so adiconar os produtos que quer no carrios
//com isso temos as ações abaixo para adicionar, remover e alterar, quando estiver no carrinho ou no produtos.

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

    //isso pode ser usado em uma classe ou função vai da necessidade do uso.

    //agora cliente vai fechar o pedido referente ao total de produtos e valor no carrinho
    //lembrando que não foi falado nada sobre calculo de frete de entrega.

    


?>