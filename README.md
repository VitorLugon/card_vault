# CardVault

Portal administrativo para cadastro e gerenciamento de cartas de Magic: The Gathering,
Pokémon e Yu-Gi-Oh!. O projeto usa PHP e MySQL no back-end e HTML, CSS e JavaScript
puros no front-end.

## Progresso

- [x] Etapa 1 - ambiente, conexão com o banco, schema e seed
- [x] Etapa 2 - autenticação e sessão do usuário
- [x] Etapa 3 - API e regras do CRUD de cartas
- [x] Etapa 4 - interface administrativa responsiva
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

## Dados opcionais de demonstração

O banco inicia sem cartas para que o estado vazio e o fluxo de cadastro possam ser
avaliados. Se quiser visualizar a listagem preenchida, execute:

```bash
docker compose exec -T app php database/demo.php install
```

O comando adiciona uma carta real de cada card game e baixa as respectivas imagens
para a pasta local de uploads. Por isso, a primeira execução precisa de conexão com a
internet. As execuções seguintes reutilizam os arquivos e não duplicam os registros.

As imagens são usadas somente para demonstração e pertencem aos respectivos titulares.
Fontes: [Scryfall](https://scryfall.com/),
[Pokémon TCG API](https://pokemontcg.io/) e
[YGOPRODeck](https://ygoprodeck.com/api-guide/).

Para remover somente esses exemplos e preservar as cartas cadastradas manualmente:

```bash
docker compose exec -T app php database/demo.php remove
```

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

## API de cartas

Todos os endpoints da API exigem uma sessão autenticada. As operações que alteram
dados também exigem o token CSRF no campo `csrf_token` ou no cabeçalho
`X-CSRF-Token`.

- `GET /api/editions.php?game=magic` - lista as edições do jogo informado;
- `GET /api/cards.php` - lista todas as cartas;
- `GET /api/cards.php?id=1` - consulta uma carta;
- `POST /api/cards.php` - cadastra uma carta;
- `POST /api/cards.php?id=1` com `_method=PUT` - atualiza uma carta;
- `POST /api/cards.php?id=1` com `_method=DELETE` - exclui uma carta;
- `GET /api/card-image.php?id=1` - entrega a imagem de uma carta.

O cadastro recebe `name_en`, `name_pt` opcional, `game`, `edition_id`, `rarity` e
`image`. A edição precisa pertencer ao jogo selecionado. Imagens são limitadas a
5 MB e aos formatos JPG, PNG e WebP. Ao atualizar uma carta, a imagem só é trocada
quando um novo arquivo é enviado.

## Interface administrativa

O painel consome a API com `fetch` e atualiza a página sem recarregamentos. É possível
buscar pelo nome da carta, filtrar por card game e abrir o cadastro, a edição ou a
exclusão diretamente na listagem. O formulário usa elementos HTML nativos e apresenta
os erros devolvidos pela API junto aos campos correspondentes.

Todo o comportamento foi escrito em JavaScript vanilla. A interface não carrega
bibliotecas, frameworks, fontes ou outros recursos externos.

## Estrutura inicial

```text
database/             scripts de schema, seed e dados opcionais do MySQL
data/                 catálogo de edições fornecido no desafio
docker/php/           imagem PHP usada no ambiente local
public/api/           endpoints JSON e entrega protegida das imagens
public/assets/        estilos e JavaScript vanilla da interface
public/               raiz pública servida pelo Apache
src/Auth/             autenticação e dados da sessão do usuário
src/Cards/            validação, persistência e arquivos das cartas
src/Config/           configuração da conexão com o banco
src/Http/             respostas JSON e proteção das requisições da API
src/Security/         proteção dos formulários com token CSRF
uploads/cards/        imagens enviadas no cadastro de cartas
```

## Decisões de UX e produto

### Seleção progressiva da edição

O campo de edição começa desabilitado. Depois que o usuário escolhe o card game, a
interface mostra o texto "Carregando edições..." enquanto realiza a requisição. Ao
trocar o jogo, a seleção anterior é descartada e a lista é carregada novamente. Esse
fluxo evita combinações inválidas e deixa claro por que o campo ainda não pode ser usado.

### Confirmação antes da exclusão

A exclusão abre uma confirmação com o nome da carta e informa que a imagem também será
removida. A ação destrutiva fica separada do botão de edição e usa uma cor de alerta.
Isso reduz exclusões acidentais, principalmente para usuários menos familiarizados
com sistemas administrativos.

### Organização visual

A paleta usa azul nas ações principais, vermelho na identidade e nos alertas, amarelo
nos destaques e superfícies claras para leitura. A combinação foi inspirada na linguagem
visual de portais de TCG, em especial a [Liga Pokémon](https://www.ligapokemon.com.br/),
sem copiar componentes ou recursos externos do site.
