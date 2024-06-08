<?php
session_start();
require 'conexao.php';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro na conexão com o banco de dados: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carroId = filter_input(INPUT_POST, 'editCarroIdProva3', FILTER_VALIDATE_INT);
    $pesoRetentor = filter_input(INPUT_POST, 'editPesoRetentorProva3', FILTER_VALIDATE_INT);
    $naoParticipou = isset($_POST['editNaoParticipouProva3']) ? 1 : 0;

    if ($carroId === false || ($pesoRetentor === false && !$naoParticipou)) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
        exit;
    }

    try {
        if ($naoParticipou) {
            $stmt = $conn->prepare('UPDATE prova_3 SET peso_retentor = 0, colocacao = 0, pontuacao = 0, nao_participou = :nao_participou WHERE carro_id = :carro_id');
        } else {
            $stmt = $conn->prepare('UPDATE prova_3 SET peso_retentor = :peso_retentor, nao_participou = :nao_participou WHERE carro_id = :carro_id');
            $stmt->bindParam(':peso_retentor', $pesoRetentor);
        }
        $stmt->bindParam(':nao_participou', $naoParticipou);
        $stmt->bindParam(':carro_id', $carroId);
        $stmt->execute();

        if (!$naoParticipou) {
            $stmt = $conn->prepare('SELECT id, peso_retentor FROM prova_3 WHERE nao_participou = 0 ORDER BY peso_retentor DESC');
            $stmt->execute();
            $colocacao = 0;
            $ultimoPeso = null;
            $posicaoAtual = 0;
            $empateContador = 0;

            while ($row = $stmt->fetch()) {
                $posicaoAtual++;

                if ($row['peso_retentor'] != $ultimoPeso) {
                    $colocacao = $posicaoAtual;
                    $ultimoPeso = $row['peso_retentor'];
                    $empateContador = 0;
                } else {
                    $empateContador++;
                }

                $pontuacao = calcularPontuacao($colocacao);
                $stmtUpdate = $conn->prepare('UPDATE prova_3 SET colocacao = :colocacao, pontuacao = :pontuacao WHERE id = :id');
                $stmtUpdate->bindParam(':colocacao', $colocacao);
                $stmtUpdate->bindParam(':pontuacao', $pontuacao);
                $stmtUpdate->bindParam(':id', $row['id']);
                $stmtUpdate->execute();
            }
        }

        echo json_encode(['success' => true, 'message' => 'Dados atualizados com sucesso!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar dados: ' . $e->getMessage()]);
    }
}

function calcularPontuacao($colocacao) {
    $regrasPontuacao = [
        1 => 1.0,
        2 => 1.0,
        3 => 1.0,
        4 => 0.8,
        5 => 0.8,
        6 => 0.8,
        7 => 0.6,
        8 => 0.6,
        9 => 0.4,
        10 => 0.4,
        11 => 0.4,
        12 => 0.4,
        13 => 0.4,
        14 => 0.4,
        15 => 0.4,
        16 => 0.4,
        17 => 0.4,
        18 => 0.4,
        19 => 0.4,
        20 => 0.4,
        21 => 0.4,
        22 => 0.4,
        23 => 0.4,
        24 => 0.4,
        25 => 0.4,
        26 => 0.4,
        27 => 0.4,
        28 => 0.4,
        29 => 0.4,
        30 => 0.4
    ];

    return $regrasPontuacao[$colocacao] ?? 0;
}
?>