CREATE TABLE sete.firebase_fila_del_user (
	id INT auto_increment NOT NULL,
	uid varchar(255) NOT NULL,
	email varchar(100) NULL,
	is_processado char(1) DEFAULT 'N' NULL,
	dt_criacao DATETIME NOT NULL,
	criado_por varchar(100) NULL,
	dt_processado DATETIME NULL,
	CONSTRAINT firebase_fila_del_user_pk PRIMARY KEY (id)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_general_ci;
