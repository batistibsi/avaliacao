delete from avaliacao_pergunta;
delete from avaliacao_bloco;

alter table avaliacao_pergunta add column peso integer not null default 1;
alter table avaliacao_resposta add column peso integer not null default 1;