CREATE TABLE sete.api_liberacao_usuario (
	id_liberacao INT auto_increment NOT NULL,
	uid VARCHAR(255) NOT NULL,
	dt_liberacao DATETIME NOT NULL,
	criado_por varchar(100) NULL,
	CONSTRAINT api_liberacao_usuario_pk PRIMARY KEY (id_liberacao)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_general_ci;
CREATE UNIQUE INDEX api_liberacao_usuario_uid_IDX USING BTREE ON sete.api_liberacao_usuario (uid);
