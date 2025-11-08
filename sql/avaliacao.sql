CREATE TABLE avaliacao_perfil (
    id_perfil integer NOT NULL PRIMARY KEY,
    descricao character varying(255)
);

CREATE TABLE avaliacao_usuario (
    id_usuario serial NOT NULL PRIMARY KEY,
    nome character varying(255) NOT NULL,
    email character varying(255),
    senha character(32) NOT NULL,
    ativo boolean DEFAULT true,
    id_perfil integer NOT NULL,
    CONSTRAINT avaliacao_usuario_id_perfil_fkey FOREIGN KEY (id_perfil)
        REFERENCES avaliacao_perfil (id_perfil) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        NOT VALID
);

CREATE TABLE avaliacao_login (
    id_login serial NOT NULL PRIMARY KEY,
    id_usuario integer NOT NULL,
    data_hora timestamp without time zone DEFAULT now(),
    CONSTRAINT avaliacao_login_id_usuario_fkey FOREIGN KEY (id_usuario)
        REFERENCES avaliacao_usuario (id_usuario) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        NOT VALID
);

CREATE TABLE avaliacao_equipe(
	id_equipe serial not null PRIMARY KEY,
	nome varchar not null,
	id_usuario integer,
    CONSTRAINT avaliacao_equipe_id_usuario_fkey FOREIGN KEY (id_usuario)
        REFERENCES avaliacao_usuario (id_usuario) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        NOT VALID
);

CREATE TABLE avaliacao_equipe_integrantes(
	id_equipe integer not null,
	id_usuario integer NOT null,
	CONSTRAINT avaliacao_equipe_integrantes_pkey PRIMARY KEY (id_equipe,id_usuario),
    CONSTRAINT avaliacao_equipe_integrantes_id_equipe_fkey FOREIGN KEY (id_equipe)
        REFERENCES avaliacao_equipe (id_equipe) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        NOT VALID,
	CONSTRAINT avaliacao_equipe_integrantes_id_usuario_fkey FOREIGN KEY (id_usuario)
        REFERENCES avaliacao_usuario (id_usuario) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        NOT VALID
);

CREATE TABLE avaliacao_formulario (
    id_formulario integer NOT NULL PRIMARY KEY,
    descricao character varying,
    cor character varying
);

CREATE TABLE avaliacao_envio (
    id_envio integer NOT NULL PRIMARY KEY,
    id_equipe integer,
    id_formulario integer,
    data_envio timestamp without time zone DEFAULT now(),
	CONSTRAINT avaliacao_envio_id_equipe_fkey FOREIGN KEY (id_equipe)
        REFERENCES avaliacao_equipe (id_equipe) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        NOT VALID,
	CONSTRAINT avaliacao_envio_id_formulario_fkey FOREIGN KEY (id_formulario)
        REFERENCES avaliacao_formulario (id_formulario) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        NOT VALID
);

CREATE TABLE avaliacao_pergunta (
    id_pergunta integer NOT NULL PRIMARY KEY,
    id_formulario integer NOT NULL,
    pergunta text NOT NULL,
    tipo character varying NOT NULL,
    opcoes text,
    grupo character varying,
    ordem integer,
	CONSTRAINT avaliacao_pergunta_id_formulario_fkey FOREIGN KEY (id_formulario)
        REFERENCES avaliacao_formulario (id_formulario) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        NOT VALID
);

CREATE TABLE avaliacao_resposta (
    id_resposta integer NOT NULL PRIMARY KEY,
    id_envio integer,
    pergunta text NOT NULL,
    tipo character varying NOT NULL,
    opcoes text,
    grupo character varying,
    ordem integer,
    resposta text NOT NULL,
    observacao text,
	CONSTRAINT avaliacao_resposta_id_formulario_fkey FOREIGN KEY (id_envio)
        REFERENCES avaliacao_envio (id_envio) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        NOT VALID
);



