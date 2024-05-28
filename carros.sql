CREATE DATABASE carros;
USE carros;

CREATE TABLE carros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT UNIQUE NOT NULL,
    equipe VARCHAR(255) NOT NULL
);

CREATE TABLE prova_1 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    carro_id INT NOT NULL,
    distancia_percorrida FLOAT NOT NULL,
    colocacao INT,
    pontuacao FLOAT,
    nao_participou TINYINT DEFAULT 0,
    FOREIGN KEY (carro_id) REFERENCES carros(id)
);

CREATE TABLE prova_2 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    carro_id INT NOT NULL,
    tempo FLOAT NOT NULL,
    penalidade TINYINT,
    tempo_ajustado FLOAT,
    colocacao INT,
    pontuacao FLOAT,
    nao_participou TINYINT DEFAULT 0,
    FOREIGN KEY (carro_id) REFERENCES carros(id)
);

CREATE TABLE prova_3 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    carro_id INT NOT NULL,
    peso_retentor FLOAT NOT NULL,
    colocacao INT,
    pontuacao FLOAT,
    nao_participou TINYINT DEFAULT 0,
    FOREIGN KEY (carro_id) REFERENCES carros(id)
);

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

INSERT INTO usuarios (username, password) VALUES ('Admin', SHA2('Senha', 256));
