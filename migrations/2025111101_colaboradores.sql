insert into avaliacao_perfil values(3,'Colaborador');

alter table avaliacao_grupo add column id_formulario integer not null;

ALTER TABLE IF EXISTS public.avaliacao_grupo
    ADD FOREIGN KEY (id_formulario)
    REFERENCES public.avaliacao_formulario (id_formulario) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION
    NOT VALID;