CREATE TABLE sete.sys_log_firebase (
	colecao varchar(100) NOT NULL,
	dt_leitura DATETIME NOT NULL,
        document varchar(255) NULL,
	codigo_cidade varchar(100) NULL,
        id_log bigint(20) auto_increment NOT NULL,
	CONSTRAINT sys_log_firebase_pk PRIMARY KEY (id_log)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_general_ci;


ALTER TABLE sete.sys_log_firebase ADD document varchar(255) NULL;
ALTER TABLE sete.sys_log_firebase CHANGE document document varchar(255) NULL AFTER dt_leitura;

ALTER TABLE sete.sys_log_firebase DROP PRIMARY KEY;
ALTER TABLE sete.sys_log_firebase ADD CONSTRAINT sys_log_firebase_pk PRIMARY KEY (colecao,dt_leitura,codigo_cidade);

ALTER TABLE sete.sys_log_firebase DROP PRIMARY KEY;
ALTER TABLE sete.sys_log_firebase ADD id_log BIGINT auto_increment NOT NULL;
ALTER TABLE sete.sys_log_firebase MODIFY COLUMN codigo_cidade varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
ALTER TABLE sete.sys_log_firebase ADD CONSTRAINT sys_log_firebase_pk PRIMARY KEY (id_log);


ALTER TABLE sete.sys_log_firebase ADD id_log BIGINT NOT NULL;
ALTER TABLE sete.sys_log_firebase MODIFY COLUMN codigo_cidade varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
ALTER TABLE sete.sys_log_firebase ADD CONSTRAINT sys_log_firebase_pk PRIMARY KEY (id_log);

ALTER TABLE sete.sys_log_firebase MODIFY COLUMN id_log bigint(20) auto_increment NOT NULL;
