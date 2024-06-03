<?php
// Configurações do banco de dados
$host = 'localhost';
$dbname = 'carros';
$username = 'root';
$password = '1065';

try {
    // Cria uma nova conexão PDO com o banco de dados
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Define o modo de erro do PDO para exceções
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Exibe uma mensagem de erro se a conexão falhar
    echo 'Erro na conexão com o banco de dados: ' . $e->getMessage();
    exit;
}
?>
