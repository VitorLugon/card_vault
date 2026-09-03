# CardVault

Portal administrativo para cadastro e gerenciamento de cartas de Magic: The Gathering,
Pokémon e Yu-Gi-Oh!. O projeto usa PHP e MySQL no back-end e HTML, CSS e JavaScript
puros no front-end.

## Progresso

- [x] Etapa 1 - ambiente, conexão com o banco, schema e seed
- [ ] Etapa 2 - autenticação e sessão do usuário
- [ ] Etapa 3 - API e regras do CRUD de cartas
- [ ] Etapa 4 - interface administrativa responsiva
- [ ] Etapa 5 - testes manuais, revisão e documentação final

## Como executar a etapa atual

### Pré-requisitos

- Docker Desktop instalado e em execução.
- Portas `8080` e `3307` disponíveis.

### Inicialização

1. Na raiz do projeto, copie `.env.example` para `.env` caso queira alterar as
   credenciais locais do banco. Os valores padrão já permitem iniciar a aplicação.
2. Execute `docker compose up --build`.
3. Acesse `http://localhost:8080`.

Quando o ambiente estiver correto, a página inicial exibirá o banco como conectado.

Para encerrar os contêineres, pressione `Ctrl + C` no terminal ou execute
`docker compose down` em outro terminal.

> Atenção: `docker compose down -v` também remove o volume do MySQL e apaga todos os
> dados cadastrados. Use esse comando apenas quando quiser recriar o banco desde o início.

## Credenciais de teste

- E-mail: `admin@cardvault.local`
- Senha: `password`

Essas credenciais são exclusivas para o ambiente local do desafio.

## Estrutura inicial

```text
database/        scripts de schema e seed do MySQL
docker/php/      imagem PHP usada no ambiente local
public/          raiz pública servida pelo Apache
src/             código PHP que não deve ficar exposto diretamente
uploads/cards/   imagens enviadas no cadastro de cartas
```

## Decisões de UX e produto

As decisões serão registradas nesta seção conforme os fluxos forem implementados.
As primeiras escolhas a validar são deixar claro o carregamento das edições e pedir
confirmação antes de excluir uma carta. A documentação final explicará como cada
decisão funciona na interface e qual problema ela resolve.
