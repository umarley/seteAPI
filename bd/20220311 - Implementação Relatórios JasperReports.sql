CREATE TABLE sistema.relatorios (
	id_relatorio int8 NOT NULL GENERATED ALWAYS AS IDENTITY,
	nome varchar(255) NOT NULL,
	descricao text NULL,
	path_jasper varchar(255) NOT NULL,
	dt_criacao timestamp NULL,
	criado_por varchar(255) NULL,
	dt_alteracao timestamp NULL,
	alterado_por varchar(255) NULL
);




CREATE TABLE sistema.relatorios_parametros (
	id_relatorio int8 NOT NULL,
	parametro varchar(255) NOT NULL,
	tipo varchar(45) NOT NULL,
	"label" varchar(100) NOT NULL,
	is_obrigatorio varchar(1) NOT NULL DEFAULT 'N'
);

ALTER TABLE sistema.relatorios ADD CONSTRAINT relatorios_pk PRIMARY KEY (id_relatorio);
ALTER TABLE sistema.relatorios_parametros ADD CONSTRAINT relatorios_parametros_fk FOREIGN KEY (id_relatorio) REFERENCES sistema.relatorios(id_relatorio) ON DELETE CASCADE ON UPDATE CASCADE;
