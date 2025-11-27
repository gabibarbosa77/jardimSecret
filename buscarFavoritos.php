<?php
session_start();
require 'conexao/conecta.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit();
}

$idUsuario = $_SESSION['id'];

try {
    $sql = "SELECT 
                f.idFavorito,
                f.idProduto,
                f.dataAdicionado,
                p.nomeProduto,
                p.marcaProduto,
                p.valorProduto,
                p.descricaoProduto,
                p.imagemProduto,
                p.estoque,
                pr.valorPromocional,
                pr.percentualPromocao
            FROM tb_favoritos f
            INNER JOIN tb_produto p ON f.idProduto = p.idProduto
            LEFT JOIN tb_promocao pr ON p.idProduto = pr.idProduto
            WHERE f.idUsuario = ? AND p.ativo = 1
            ORDER BY f.dataAdicionado DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $favoritos = [];
    while ($row = $result->fetch_assoc()) {
        // Calcular preço final (considerando promoção)
        $precoFinal = $row['valorProduto'];
        $temPromocao = false;
        
        if ($row['valorPromocional'] > 0) {
            $precoFinal = $row['valorPromocional'];
            $temPromocao = true;
        } elseif ($row['percentualPromocao'] > 0) {
            $precoFinal = $row['valorProduto'] * (1 - ($row['percentualPromocao'] / 100));
            $temPromocao = true;
        }
        
        $favoritos[] = [
            'idFavorito' => $row['idFavorito'],
            'idProduto' => $row['idProduto'],
            'nomeProduto' => $row['nomeProduto'],
            'marcaProduto' => $row['marcaProduto'],
            'valorOriginal' => floatval($row['valorProduto']),
            'precoFinal' => floatval($precoFinal),
            'temPromocao' => $temPromocao,
            'descricao' => $row['descricaoProduto'],
            'imagem' => base64_encode($row['imagemProduto']),
            'estoque' => $row['estoque'],
            'dataAdicionado' => $row['dataAdicionado']
        ];
    }
    
    echo json_encode(['success' => true, 'favoritos' => $favoritos]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar favoritos: ' . $e->getMessage()]);
}
?>
