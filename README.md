# CardVault

Portal administrativo para cadastro e gerenciamento de cartas de Magic: The Gathering,
Pokémon e Yu-Gi-Oh!. O projeto usa PHP e MySQL no back-end e HTML, CSS e JavaScript
puros no front-end.

## Progresso

- [x] Etapa 1 - ambiente, conexão com o banco, schema e seed
- [x] Etapa 2 - autenticação e sessão do usuário
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

Se a porta `8080` já estiver ocupada, defina outro valor para `APP_PORT` e ajuste
`APP_URL` no arquivo `.env`. Por exemplo, use `APP_PORT=8081` e acesse
`http://localhost:8081`.

Quando o ambiente estiver correto, a página inicial exibirá o banco como conectado.
Ao abrir `http://localhost:8080`, o sistema direcionará o visitante para a tela de
login ou para o painel, caso já exista uma sessão válida.

Para encerrar os contêineres, pressione `Ctrl + C` no terminal ou execute
`docker compose down` em outro terminal.

> Atenção: `docker compose down -v` também remove o volume do MySQL e apaga todos os
> dados cadastrados. Use esse comando apenas quando quiser recriar o banco desde o início.

## Credenciais de teste

- E-mail: `admin@cardvault.local`
- Senha: `password`

Essas credenciais são exclusivas para o ambiente local do desafio.

## Autenticação

O login consulta o usuário pelo e-mail e compara a senha com o hash armazenado no
banco. Depois da autenticação, o identificador da sessão é renovado e apenas os dados
necessários do usuário ficam na sessão.

O painel exige uma sessão válida. O logout aceita somente requisições `POST` e, assim
como o login, utiliza um token CSRF. As mensagens de erro não informam se foi o e-mail
ou a senha que estava incorreto.

Rotas disponíveis nesta etapa:

- `/login.php` - entrada do usuário;
- `/dashboard.php` - área protegida;
- `/logout.php` - encerramento da sessão por `POST`;
- `/health.php` - verificação simples da aplicação e do banco.

## Estrutura inicial

```text
database/        scripts de schema e seed do MySQL
docker/php/      imagem PHP usada no ambiente local
public/          raiz pública servida pelo Apache
src/Auth/        autenticação e dados da sessão do usuário
src/Config/      configuração da conexão com o banco
src/Security/    proteção dos formulários com token CSRF
uploads/cards/   imagens enviadas no cadastro de cartas
```

## Decisões de UX e produto

As decisões serão registradas nesta seção conforme os fluxos forem implementados.
As primeiras escolhas a validar são deixar claro o carregamento das edições e pedir
confirmação antes de excluir uma carta. A documentação final explicará como cada
decisão funciona na interface e qual problema ela resolve.
