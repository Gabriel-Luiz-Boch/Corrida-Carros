<?php
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipeId = filter_input(INPUT_POST, 'equipeId', FILTER_VALIDATE_INT);
    $nomeAluno = filter_input(INPUT_POST, 'nomeAluno', FILTER_SANITIZE_STRING);

    if ($equipeId && $nomeAluno) {
        try {
            $stmt = $conn->prepare('INSERT INTO alunos (equipe_id, nome) VALUES (:equipeId, :nomeAluno)');
            $stmt->bindParam(':equipeId', $equipeId);
            $stmt->bindParam(':nomeAluno', $nomeAluno);
            $stmt->execute();
            echo 'Aluno inserido com sucesso!';
        } catch (PDOException $e) {
            echo 'Erro ao inserir aluno: ' . $e->getMessage();
        }
    } else {
        echo 'Dados inválidos.';
    }
}
?>
