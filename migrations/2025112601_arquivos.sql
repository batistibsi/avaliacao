CREATE TABLE avaliacao_arquivo (
    id_arquivo              serial  NOT NULL PRIMARY KEY,
    id_resposta             integer NOT NULL,
    tipo                    varchar NOT NULL,
    nome_original           varchar NOT NULL,
    caminho_arquivo         varchar NOT NULL,
    mime_type               varchar,
    tamanho_bytes           bigint
);

ALTER TABLE IF EXISTS public.avaliacao_arquivo
    ADD FOREIGN KEY (id_resposta)
    REFERENCES public.avaliacao_resposta (id_resposta) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE
    NOT VALID;