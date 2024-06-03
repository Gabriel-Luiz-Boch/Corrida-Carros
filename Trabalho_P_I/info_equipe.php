<?php
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipeId = filter_input(INPUT_POST, 'equipeId', FILTER_VALIDATE_INT);

    if ($equipeId) {
        try {
            // Obter informações da equipe
            $stmt = $conn->prepare('SELECT numero, equipe FROM carros WHERE id = :equipeId');
            $stmt->bindParam(':equipeId', $equipeId);
            $stmt->execute();
            $equipe = $stmt->fetch(PDO::FETCH_ASSOC);

            // Obter integrantes da equipe
            $stmt = $conn->prepare('SELECT nome FROM alunos WHERE equipe_id = :equipeId');
            $stmt->bindParam(':equipeId', $equipeId);
            $stmt->execute();
            $integrantes = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Montar dados para retorno
            $dados = [
                'numero' => $equipe['numero'],
                'equipe' => $equipe['equipe'],
                'integrantes' => $integrantes
            ];

            echo json_encode($dados);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Erro ao obter informações da equipe: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Dados inválidos.']);
    }
}
?>
