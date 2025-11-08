INSERT INTO public.avaliacao_perfil(
	id_perfil, descricao)
	VALUES (1, 'Administrador'),(2, 'Gerente');

INSERT INTO public.avaliacao_usuario(
	nome, email, senha, ativo, id_perfil)
	VALUES ('Administrador', 'batisti_bsi@hotmail.com', md5('tel335428'), true, 1);