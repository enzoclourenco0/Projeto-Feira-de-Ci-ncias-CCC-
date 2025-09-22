create database projetoFeira;
use projetoFeira;

-- TABELAS -----------------------------------------------------------------------------------------------------------------------

create table materia(
    id_materia int primary key not null,
    nome varchar(50) not null
)ENGINE = InnoDB;

create table usuario(
    id_usuario int primary key not null AUTO_INCREMENT,
    nome varchar(50) not null,
    telefone varchar(50) not null,
    senha varchar(255) not null
)ENGINE = InnoDB;
ALTER TABLE usuario ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0;
UPDATE usuario SET is_admin = 1 WHERE id_usuario = 5;

create table participanteSorteio(
    id_participante int primary key not null,
    fk_id_usuario int not null,
    foi_sorteado boolean not null,
    foreign key(fk_id_usuario) references usuario (id_usuario)
) ENGINE = InnoDB;

create table perguntaResposta(
    id_pergunta int primary key not null,
    fk_id_materia int not null,
    fk_id_usuario int not null,
    enunciado varchar(150) not null,
    alternativa_A varchar (50) not null,
    alternativa_B varchar (50) not null,
    alternativa_C varchar (50) not null,
    alternativa_D varchar (50) not null,
    alternativa_correta char(1) not null,
    foreign key (fk_id_materia) references materia (id_materia),
    foreign key (fk_id_usuario) references usuario (id_usuario)
)ENGINE = InnoDB;

create table tentativa(
    id_tentativa int primary key not null AUTO_INCREMENT,
    fk_id_usuario int not null,
    acertos_totais int not null,
    total_perguntas int not null,
    respostas_totais int not null,
    finalizada boolean not null,
    validada_para_sorteio boolean not null,
    foreign key (fk_id_usuario) references usuario (id_usuario)
)ENGINE = InnoDB;
ALTER TABLE tentativa ADD COLUMN fk_id_materia INT NOT NULL AFTER fk_id_usuario;


create table resposta(
    id_resp int PRIMARY key not null AUTO_INCREMENT,
    fk_id_usuario int not null,
    fk_id_tentativa int not null,
    fk_id_pergunta int not null,
    resposta_dada char(1) not null,
    acertou boolean not null,
	FOREIGN KEY (fk_id_tentativa) REFERENCES tentativa (id_tentativa),
	FOREIGN key (fk_id_pergunta) references perguntaResposta (id_pergunta),
    FOREIGN key (fk_id_usuario) references usuario (id_usuario)
)ENGINE = InnoDB;

create table estrelas(
    id_estrela int primary key not null,
    quantidade real not null,
    fk_id_usuario int not null,
    FOREIGN KEY (fk_id_usuario) references usuario (id_usuario)
)ENGINE = InnoDB;

create table pontuacao(
    id_pontuacao int primary key not null,
    fk_id_materia int not null,
    fk_id_estrela int not null,
    pontos real not null,
    FOREIGN KEY (fk_id_estrela)references estrelas (id_estrela)
)ENGINE = InnoDB;

-- CONSULTAS -----------------------------------------------------------------------------------------------------------------------

select * from materia;
select * from pergunta;
select * from usuario;
select * from participanteSorteio;
select * from tentativa;
select id_resp, fk_id_usuario, resposta_dada, acertou from resposta;	
select * from estrelas;
select * from pontuacao;

SELECT 
    u.nome, 
    u.id_usuario, 
    u.telefone, 
    u.is_admin,
    COUNT(t.id_tentativa) AS numero_de_tentativas,
    t.validada_para_sorteio
FROM 
    usuario AS u
LEFT JOIN 
    tentativa AS t ON u.id_usuario = t.fk_id_usuario
GROUP BY
    u.id_usuario
ORDER BY
    u.id_usuario DESC;
    
-- INSERTS PARA TESTE -----------------------------------------------------------------------------------------------------------------------

INSERT INTO materia (id_materia, nome) VALUES
(1, 'Estande 1'),
(2, 'Estande 2'),
(3, 'Estande 3'),
(4, 'Estande 4'),
(5, 'Estande 5'),
(6, 'Estande 6'),
(7, 'Estande 7'),
(8, 'Estande 8'),
(9, 'Estande 9'),
(10, 'Estande 10');

INSERT INTO usuario (nome, telefone, senha) VALUES
('João da Silva', '99999-8888', 'simples123');

INSERT INTO perguntaResposta (id_pergunta, fk_id_materia, fk_id_usuario, enunciado, alternativa_A, alternativa_B, alternativa_C, alternativa_D, alternativa_correta) VALUES
(1, 1, 1, 'Qual a função da clorofila na fotossíntese?', 'Absorver luz verde', 'Produzir oxigênio', 'Absorver luz para gerar energia', 'Liberar dióxido de carbono', 'C'),
(2, 2, 1, 'O que é um buraco negro?', 'Uma estrela em explosão', 'Uma área do espaço com gravidade extrema', 'Um planeta gasoso sem luz', 'Um cometa congelado', 'B'),
(3, 3, 1, 'Qual o símbolo químico do ouro?', 'Au', 'Ag', 'Fe', 'Cu', 'A'),
(4, 4, 1, 'O que é energia eólica?', 'Energia gerada por painéis solares.', 'Energia gerada por usinas nucleares.', 'Energia gerada pelo vento.', 'Energia gerada pela queima de carvão.', 'C'),
(5, 5, 1, 'Qual o principal gás responsável pelo efeito estufa?', 'Oxigênio (O2).', 'Hidrogênio (H2).', 'Nitrogênio (N2).', 'Dióxido de carbono (CO2).', 'D'),
(6, 6, 1, 'Qual a fórmula química da água?', 'H2O.', 'CO2.', 'NaCl.', 'O2.', 'A'),
(7, 7, 1, 'Qual é o planeta mais próximo do Sol?', 'Terra.', 'Marte.', 'Mercúrio.', 'Vênus.', 'C'),
(8, 8, 1, 'O que significa a sigla "IA"?', 'Internet Avançada.', 'Informação Atômica.', 'Inteligência Artificial.', 'Interação Analógica.', 'C'),
(9, 9, 1, 'Qual vitamina o corpo humano produz em resposta à luz solar?', 'Vitamina A.', 'Vitamina C.', 'Vitamina D.', 'Vitamina K.', 'C'),
(10, 10, 1, 'O que é o processo de reciclagem?', 'A extração de petróleo do solo.', 'O processo de queimar lixo.', 'O reaproveitamento de materiais descartados.', 'A conversão de água salgada em potável.', 'C');

-- DROPS -----------------------------------------------------------------------------------------------------------------------

drop database projetoFeira;