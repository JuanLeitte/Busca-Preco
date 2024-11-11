<?php
$host = 'xxx';
$dbname = 'xxx';
$user = 'xxx';
$password = 'xxx'; 
$port = 'xxx';

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    function buscarNomeEmpresa(PDO $conn){
        try {
            $fant = $conn->prepare("SELECT nome FROM registro;");
            $fant->execute();
    
            $result = $fant->fetch(PDO::FETCH_ASSOC);
    
            if ($result) {  
                return $result['nome']; 
            } else {
                return null;
            }
    
        } catch (PDOException $e) {
            error_log("Erro na consulta: " . $e->getMessage());
            return null;
        }
    }

    function buscarProdutoPorCodBarra(PDO $conn, string $codBarra): ?array {
        try {
            $stmt = $conn->prepare("
                SELECT 
                    P.codigo,
                    P.descricao,
                    P.precovenda,
                    B.codbarra
                FROM produtos P
                LEFT OUTER JOIN produtos_codbarra B ON (P.codigo = B.produto)
                WHERE B.codbarra = :codbarra
            ");

            $stmt->bindParam(':codbarra', $codBarra);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result : null;

        } catch (PDOException $e) {
            error_log("Erro na consulta: " . $e->getMessage());
            return null;
        }
    }

    $produto = null;
    $precoFormatado = null;
    $fantasia = null;
    $fantasia = buscarNomeEmpresa($conn);


    if (isset($_GET['codigoDeBarras'])) {
        $codigoDeBarras = $_GET['codigoDeBarras'];
        $produto = buscarProdutoPorCodBarra($conn, $codigoDeBarras);

        if ($produto === null) {
            $mensagemErro = "Produto não encontrado.";
        } else {
            $precoFormatado = number_format($produto['precovenda'], 2, ",", ".");
        }
    }

} catch (PDOException $e) {
    $mensagemErroConexao = "Erro na conexão com o banco de dados: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Busca Preço</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php if (isset($mensagemErroConexao)): ?>
        <p style="color: red;"><?= $mensagemErroConexao ?></p>
    <?php endif; ?>

    <div class="container">
        <h1 class="cabecalho">BUSCA PREÇO</h1>
        <form action="" method="GET" class="form">
            <div class="header">
                <img src="logo_pdv.png" alt="Logo ELLO SISTEMAS">
                <h1><?=$fantasia?></h1>
            </div>
            <div class="dados-cont">

                <?php if(!$produto): ?>
                    <span class="descricao"><strong> Informe um código de barras valido!</strong></span>
                <?php else:?>
                <span class="descricao" ><strong><?=$produto['descricao']?>çãáà</strong></span>
                <span class="precoVenda"><strong>R$ <?=htmlspecialchars($precoFormatado)?></strong></span>
                <span>Código: <strong><?=htmlspecialchars($produto['codigo'])?></strong></span>
                <?php endif;?>
                
                <input type="text" id="IdInput" name="codigoDeBarras" class="cod-stl" placeholder="Código de barras" required autofocus>
            </div>
                        
        </form>
    </div>
</body>
</html>