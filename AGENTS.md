# Estrutura do Projeto - AGENTS.md

Este documento descreve a estrutura geral do projeto e o papel de cada diretório ou arquivo principal. O objetivo é servir como um guia rápido para novos desenvolvedores que estão implementando funcionalidades relacionadas à lógica de agentes (se aplicável).

## 📁 Diretórios Principais

*   **`app/`**: Contém a maior parte da lógica de negócio do aplicativo, incluindo modelos, controladores e serviços. É o coração da aplicação.
*   **`database/`**: Arquivos de migrações de banco de dados (Schema Builder).
*   **`config/`**: Arquivos de configuração globais para diferentes partes da aplicação (ex: serviços, credenciais, etc.).
*   **`routes/`**: Define as rotas HTTP que mapeiam URLs para a lógica de negócio.
    *   *(Ex: web.php, api.php)*
*   **`public/`**: Diretório de arquivos acessíveis publicamente, como ativos estáticos (imagens, CSS compilados).
*   **`resources/`**: Contém os recursos que precisam ser compilados ou processados antes de serem servidos (views Blade, assets originais).
    *   *(Ex: views/, js/, css/)
*   **`storage/`**: Armazenamento para arquivos gerados em tempo de execução, como logs e cache.
*   **`tests/`**: Contém todos os testes unitários e funcionais do projeto (PHPUnit).

## 🧩 Configuração e Build Tools

*   **`composer.json`/`composer.lock`**: Gerenciamento de dependências PHP via Composer.
*   **`package.json`/`yarn.lock`**: Gerenciamento de dependências JavaScript/NPM.
*   **`vite.config.ts` / `eslint.*`**: Configurações para o *frontend build system* (Vite) e linting do código JavaScript/TypeScript.

## 🚀 Arquivos de Deploy e Scripts

*   **`deploy.sh`**: Script utilizado para automatizar ou guiar processos de deploy do ambiente local/staging para a produção.
*   **`.env.*` / `compose.*.yaml`**: Definições de ambiente e orquestração Docker.

## 🧠 Área de Agentes (A ser preenchida)

Esta seção deve ser atualizada à medida que novos módulos ou lógicas específicas de agentes forem implementados. Por exemplo, se a lógica principal dos agentes estiver em um namespace específico dentro de `app/`, ele deve ser detalhado aqui.
