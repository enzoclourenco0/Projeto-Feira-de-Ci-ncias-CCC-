<?php

class Conexao {
    private $host = 'localhost'; // Geralmente 'localhost'
    private $dbname = 'projetoFeira';
    private $user = 'root';      // Usuário padrão do MySQL
    private $pass = '';          // Senha padrão do MySQL (geralmente vazia no XAMPP)
    private $conn;

    public function getConexao() {
        $this->conn = null;

        try {
            // Cria a conexão usando a classe mysqli (abordagem orientada a objetos)
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);

            // Define o charset para utf8 para evitar problemas com acentuação
            $this->conn->set_charset("utf8");

        } catch (Exception $e) {
            // Se houver um erro na conexão, exibe a mensagem de erro
            die("Erro na conexão: " . $e->getMessage());
        }

        return $this->conn;
    }
}

?>