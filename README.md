<div align="center">
  <img src="public/images/campus-tour-logo.png" alt="Campus Tour UniNorte 2026" width="520">

  <h1>Campus Tour UniNorte 2026</h1>

  <p>
    Uma experiência interativa para descobrir tecnologia, conhecer os cursos de
    <strong>Sistemas de Informação</strong> e
    <strong>Análise e Desenvolvimento de Sistemas</strong> e aprender jogando.
  </p>

  <p>
    <img alt="Laravel 13" src="https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white">
    <img alt="Vue 3" src="https://img.shields.io/badge/Vue-3-42B883?logo=vuedotjs&logoColor=white">
    <img alt="PostgreSQL 17" src="https://img.shields.io/badge/PostgreSQL-17-4169E1?logo=postgresql&logoColor=white">
    <img alt="Docker" src="https://img.shields.io/badge/Docker-ready-2496ED?logo=docker&logoColor=white">
  </p>
</div>

## Sobre o projeto

O Campus Tour transforma a apresentação dos cursos de tecnologia da UniNorte em uma jornada prática. O participante cria seu perfil, escolhe missões curtas, acumula pontos e acompanha sua colocação no ranking.

Não é preciso saber programar para participar. Cada desafio apresenta conceitos reais de maneira simples e explica o aprendizado ao final.

## Missões disponíveis

| Jogo | Tema | O que o participante aprende |
| --- | --- | --- |
| **Code Runner** | Lógica e programação | Sequências, comandos, direção e construção de algoritmos |
| **Guardião Digital** | Segurança da informação | Senhas seguras, phishing, privacidade e autenticação em dois fatores |
| **Rede em Ação** | Redes e Internet | Caminho de uma requisição, papel do servidor e latência |

Além dos jogos, a plataforma possui conquistas, pontuação, ranking geral e por missão, painel individual e relatórios administrativos.

## Cursos em destaque

- **Sistemas de Informação (SI):** une desenvolvimento, dados, gestão, produto e estratégia de negócios.
- **Análise e Desenvolvimento de Sistemas (ADS):** formação prática e concentrada em software, Web, Mobile, APIs, bancos de dados e testes.

Os dois cursos levam a carreiras em desenvolvimento, dados e IA, segurança, Cloud, DevOps, produtos digitais e muitas outras áreas.

## Tecnologias

- Laravel 13 e PHP 8.3
- Vue 3, Inertia.js e PrimeVue
- Tailwind CSS 4 e Vite
- PostgreSQL 17
- Docker Compose, Nginx, PHP-FPM e PM2
- PHPUnit para testes automatizados

## Como executar com Docker

Você precisa ter o [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado e em execução.

```bash
git clone https://github.com/Juancjc/campus-tour-uninorte.git
cd campus-tour-uninorte
cp .env.example .env
```

Edite o `.env` e defina, no mínimo:

```dotenv
APP_URL=http://localhost:8080
FORCE_HTTPS=false
DB_PASSWORD=escolha-uma-senha-local
ADMIN_NAME="Administrador"
ADMIN_EMAIL=admin@campustour.local
ADMIN_PASSWORD=escolha-uma-senha-segura
```

Depois, suba o ambiente:

```bash
docker compose up --build -d
```

A aplicação estará disponível em **http://localhost:8080**. As migrações, dados iniciais e usuário administrador são preparados automaticamente na primeira inicialização.

Para acompanhar os serviços ou desligá-los:

```bash
docker compose logs -f app
docker compose down
```

Os dados do PostgreSQL ficam preservados em um volume do Docker. Use `docker compose down -v` somente quando quiser apagar os dados locais e recomeçar do zero.

## Desenvolvimento sem Docker

Requisitos: PHP 8.3+, Composer, Node.js 22+ e PostgreSQL 17+.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
composer run dev
```

Nesse modo, ajuste a conexão PostgreSQL no `.env` para o endereço do seu banco local.

## Qualidade e testes

```bash
php artisan test
npm run lint
npm run build
vendor/bin/pint --test
```

A pontuação é validada no servidor, as rotas sensíveis têm limitação de requisições e o painel administrativo é protegido por autorização. Segredos e credenciais ficam apenas no `.env`, que não deve ser enviado ao repositório.

## Principais áreas

- `/` — apresentação pública do Campus Tour
- `/register` — cadastro do participante
- `/dashboard` — progresso, pontos e conquistas
- `/rankings` — ranking geral e por jogo
- `/admin` — indicadores e relatórios administrativos
- `/health` — verificação de saúde usada pelo Docker

## Estrutura resumida

```text
app/                 regras de negócio, HTTP, jobs e modelos
database/            migrações, factories e dados iniciais
resources/js/        páginas e componentes Vue
resources/css/       tema visual do Campus Tour
routes/               rotas Web e autenticação
tests/                testes automatizados
docker/               configurações de Nginx e PHP-FPM
```

## Contribuindo

Contribuições de alunos são bem-vindas. Crie uma branch descritiva, mantenha cada mudança pequena e objetiva e execute os testes antes de abrir um pull request.

Ideias para praticar:

- criar uma nova missão educativa;
- melhorar a acessibilidade e a experiência mobile;
- adicionar novos tipos de conquista;
- ampliar os relatórios sem expor dados pessoais;
- escrever novos testes para regras de pontuação.

---

Feito para o **Campus Tour UniNorte 2026** — tecnologia se aprende experimentando.
