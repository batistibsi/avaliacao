ALTER TABLE IF EXISTS public.avaliacao_envio
    RENAME id_equipe TO id_grupo;
ALTER TABLE IF EXISTS public.avaliacao_envio DROP CONSTRAINT IF EXISTS avaliacao_envio_id_equipe_fkey;

ALTER TABLE IF EXISTS public.avaliacao_envio
    ADD FOREIGN KEY (id_grupo)
    REFERENCES public.avaliacao_grupo (id_grupo) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION
    NOT VALID;

drop table avaliacao_equipe_integrantes;
drop table avaliacao_equipe;

ALTER TABLE IF EXISTS public.avaliacao_envio
    ALTER COLUMN id_grupo SET NOT NULL;

ALTER TABLE IF EXISTS public.avaliacao_envio
    ALTER COLUMN id_formulario SET NOT NULL;

ALTER TABLE IF EXISTS public.avaliacao_envio
    ALTER COLUMN data_envio SET NOT NULL;

ALTER TABLE IF EXISTS public.avaliacao_envio
    ADD COLUMN id_usuario integer NOT NULL;

ALTER TABLE IF EXISTS public.avaliacao_envio
    ADD COLUMN id_avaliador integer NOT NULL;
ALTER TABLE IF EXISTS public.avaliacao_envio
    ADD FOREIGN KEY (id_usuario)
    REFERENCES public.avaliacao_usuario (id_usuario) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION
    NOT VALID;

ALTER TABLE IF EXISTS public.avaliacao_envio
    ADD FOREIGN KEY (id_avaliador)
    REFERENCES public.avaliacao_usuario (id_usuario) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION
    NOT VALID;

alter table avaliacao_resposta drop column tipo;
alter table avaliacao_resposta drop column opcoes;

ALTER TABLE IF EXISTS public.avaliacao_resposta
    RENAME grupo TO bloco;

alter table avaliacao_resposta drop column ordem;