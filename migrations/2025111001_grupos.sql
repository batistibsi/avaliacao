create table avaliacao_grupo(
	id_grupo serial not null primary key,
	id_usuario integer not null,
	nome varchar not null
);

ALTER TABLE IF EXISTS public.avaliacao_grupo
    ADD FOREIGN KEY (id_usuario)
    REFERENCES public.avaliacao_usuario (id_usuario) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION
    NOT VALID;

create table avaliacao_grupo_usuario(
	id_grupo integer not null,
	id_usuario integer not null
);

ALTER TABLE IF EXISTS public.avaliacao_grupo_usuario
    ADD FOREIGN KEY (id_grupo)
    REFERENCES public.avaliacao_grupo (id_grupo) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION
    NOT VALID;

ALTER TABLE IF EXISTS public.avaliacao_grupo_usuario
    ADD FOREIGN KEY (id_usuario)
    REFERENCES public.avaliacao_usuario (id_usuario) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION
    NOT VALID;


ALTER TABLE IF EXISTS public.avaliacao_grupo_usuario
    ADD PRIMARY KEY (id_grupo, id_usuario);