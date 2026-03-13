<div align="center">

<img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white"/>
<img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white"/>
<img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white"/>
<img src="https://img.shields.io/badge/REST_API-005571?style=for-the-badge&logo=fastapi&logoColor=white"/>
<img src="https://img.shields.io/badge/Scrum-6DB33F?style=for-the-badge&logo=scrumalliance&logoColor=white"/>

# ⚙️ MaintSys

### Sistema de Manutenção Industrial 4.0

> API REST para controle digital de manutenção de maquinário industrial — desenvolvida como projeto integrador no **SENAI Limeira/SP**.

[![Figma Prototype](https://img.shields.io/badge/Protótipo-Figma-F24E1E?style=flat-square&logo=figma&logoColor=white)](https://cleat-pickle-20252208.figma.site/login)
[![Trello Board](https://img.shields.io/badge/Quadro-Trello-0052CC?style=flat-square&logo=trello&logoColor=white)](https://trello.com/invite/b/699895865de97ce9e1ed0a19/ATTI092d2e5ed783761fc1dc5a293b494341499AB6EA/projeto-integrador)

</div>

---

## 📋 Índice

- [Sobre o Projeto](#-sobre-o-projeto)
- [Funcionalidades](#-funcionalidades)
- [Tecnologias](#-tecnologias)
- [Requisitos](#-requisitos)
- [Instalação](#-instalação)
- [Endpoints da API](#-endpoints-da-api)
- [Autenticação](#-autenticação)
- [Estrutura do Banco de Dados](#-estrutura-do-banco-de-dados)
- [Padrão de Commits](#-padrão-de-commits)
- [Sprints](#-sprints)
- [Equipe](#-equipe)

---

## 🏭 Sobre o Projeto

Na era da **Indústria 4.0**, a disponibilidade das máquinas é fator crítico para a produtividade. Paradas não planejadas custam milhares de reais e atrasam toda a cadeia produtiva.

O **MaintSys** substitui as fichas de papel penduradas nas máquinas por um sistema digital centralizado, permitindo que técnicos registrem intervenções via tablets industriais e que gestores acompanhem a saúde dos equipamentos em tempo real.

```
Fábrica Moderna  →  MaintSys API  →  Técnicos & Gestores
  (máquinas)         (back-end)         (qualquer device)
```

---

## ✅ Funcionalidades

| # | Funcionalidade | Status |
|---|---------------|--------|
| 🔐 | Autenticação segura de técnicos e gestores com token Bearer | 🟡 Em desenvolvimento |
| 🏗️ | Inventário de máquinas com número de série, modelo e localização | 🟡 Em desenvolvimento |
| 📋 | Criação de Ordens de Serviço preventivas e corretivas | 🟡 Em desenvolvimento |
| 📜 | Histórico completo de manutenções por máquina | 🟡 Em desenvolvimento |
| 🔔 | Alertas automáticos de mudança de status | 🟡 Em desenvolvimento |
| 🔍 | Consulta e filtragem de O.S. por técnico, máquina ou período | 🟡 Em desenvolvimento |

---

## 🛠️ Tecnologias

- **[Laravel 11](https://laravel.com/)** — Framework PHP (back-end)
- **[MySQL](https://www.mysql.com/)** — Banco de dados relacional
- **[Eloquent ORM](https://laravel.com/docs/eloquent)** — Modelagem de dados e relacionamentos
- **[Laravel Sanctum](https://laravel.com/docs/sanctum)** — Autenticação via token Bearer
- **[Composer](https://getcomposer.org/)** — Gerenciador de dependências PHP

---

## 📦 Requisitos

Antes de instalar, certifique-se de ter:

- PHP >= 8.2
- Composer >= 2.x
- MySQL >= 8.0
- Node.js >= 18 (para assets, se necessário)

---

## 🚀 Instalação

**1. Clone o repositório**
```bash
git clone https://github.com/seu-usuario/maintsys.git
cd maintsys
```

**2. Instale as dependências**
```bash
composer install
```

**3. Configure o ambiente**
```bash
cp .env.example .env
php artisan key:generate
```

**4. Configure o banco de dados no `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=maintsys
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

**5. Execute as migrations e seeders**
```bash
php artisan migrate --seed
```

**6. Inicie o servidor**
```bash
php artisan serve
```

A API estará disponível em: `http://localhost:8000`

---

## 🌐 Endpoints da API

Todos os endpoints (exceto `/api/login`) exigem autenticação via **Bearer Token** no header:
```
Authorization: Bearer {seu_token}
```

### 🔐 Autenticação

| Método | Endpoint | Descrição | Acesso |
|--------|----------|-----------|--------|
| `POST` | `/api/login` | Autentica o usuário. Retorna token Bearer. | Público |
| `POST` | `/api/logout` | Encerra a sessão do usuário. | Autenticado |

### 🏗️ Máquinas

| Método | Endpoint | Descrição | Acesso |
|--------|----------|-----------|--------|
| `GET` | `/api/maquinas` | Lista todas as máquinas cadastradas. | Autenticado |
| `POST` | `/api/maquinas` | Cadastra uma nova máquina. | Gestor |
| `GET` | `/api/maquinas/{id}` | Retorna detalhes e histórico de uma máquina. | Autenticado |
| `PUT` | `/api/maquinas/{id}` | Atualiza dados de uma máquina. | Gestor |
| `DELETE` | `/api/maquinas/{id}` | Remove uma máquina (sem O.S. ativa). | Gestor |

### 📋 Ordens de Serviço

| Método | Endpoint | Descrição | Acesso |
|--------|----------|-----------|--------|
| `GET` | `/api/ordens` | Lista todas as Ordens de Serviço. | Autenticado |
| `POST` | `/api/ordens` | Cria uma nova O.S. (Preventiva ou Corretiva). | Autenticado |
| `GET` | `/api/ordens/{id}` | Retorna detalhes de uma O.S. específica. | Autenticado |
| `PATCH` | `/api/ordens/{id}/status` | Atualiza o status de uma O.S. | Autenticado |

### 👷 Técnicos

| Método | Endpoint | Descrição | Acesso |
|--------|----------|-----------|--------|
| `GET` | `/api/tecnicos` | Lista todos os técnicos cadastrados. | Gestor |
| `POST` | `/api/tecnicos` | Cadastra um novo técnico especializado. | Gestor |

### 📜 Histórico & Alertas

| Método | Endpoint | Descrição | Acesso |
|--------|----------|-----------|--------|
| `GET` | `/api/historico/{maquina}` | Histórico completo de intervenções de uma máquina. | Autenticado |
| `GET` | `/api/alertas` | Lista os alertas ativos de mudança de status. | Autenticado |

---

## 🔑 Autenticação

**Login — `POST /api/login`**

```json
// Request
{
  "email": "tecnico@senai.com.br",
  "password": "senha123"
}

// Response 200 OK
{
  "token": "1|xYzAbC...",
  "user": {
    "id": 1,
    "name": "Carlos Silva",
    "role": "tecnico"
  }
}
```

**Criação de O.S. — `POST /api/ordens`**

```json
// Request (com Authorization: Bearer {token})
{
  "tipo": "corretiva",
  "descricao": "Rolamento com ruído anormal",
  "maquina_id": 7,
  "prioridade": "alta"
}

// Response 201 Created
{
  "id": 42,
  "tipo": "corretiva",
  "status": "aberta",
  "maquina_id": 7,
  "tecnico_id": 1,
  "created_at": "2026-03-13T14:00:00.000000Z"
}
```

> 💡 Importe a **collection do Postman/Insomnia** disponível em `/docs/postman/` para testar todos os endpoints rapidamente.

---

## 🗄️ Estrutura do Banco de Dados

```
users              maquinas
├── id (PK)        ├── id (PK)
├── name           ├── numero_serie
├── email          ├── modelo
├── password       ├── localizacao_galpao
├── role           ├── data_instalacao
└── especialidade  └── status

ordens_servico             intervencoes
├── id (PK)                ├── id (PK)
├── tipo                   ├── ordem_servico_id (FK)
├── descricao              ├── tecnico_id (FK)
├── prioridade             ├── descricao_servico
├── status                 ├── duracao_minutos
├── maquina_id (FK)  ───▶  ├── pecas_utilizadas
└── tecnico_id (FK)        └── realizado_em

alertas
├── id (PK)
├── maquina_id (FK)
├── tipo_alerta
├── mensagem
└── lido
```

**Relacionamentos:**
- `User` **1 → N** `OrdemServico`
- `Maquina` **1 → N** `OrdemServico`
- `OrdemServico` **1 → N** `Intervencao`
- `Maquina` **1 → N** `Alerta`

---

## 📝 Padrão de Commits

Este projeto segue o padrão **[Conventional Commits](https://www.conventionalcommits.org)**:

| Prefixo | Uso |
|---------|-----|
| `feat:` | Nova funcionalidade |
| `fix:` | Correção de bug |
| `docs:` | Atualização de documentação |
| `test:` | Adição ou correção de testes |
| `refactor:` | Refatoração sem mudança de comportamento |
| `chore:` | Configuração e manutenção |

**Exemplos:**
```bash
git commit -m "feat: implementa endpoint POST /api/ordens"
git commit -m "fix: corrige validação de token expirado"
git commit -m "docs: adiciona collection Postman no README"
```

**Branches:**
```
main        → código estável, pronto para produção
develop     → integração de funcionalidades concluídas
feature/*   → desenvolvimento de novas funcionalidades
fix/*       → correção de bugs
docs/*      → atualizações de documentação
```

---

## 📅 Sprints

| Sprint | Período | Foco | Atividades |
|--------|---------|------|-----------|
| Sprint 1 | 20/02 – 25/02/2026 | Documentação inicial | Levantamento de Requisitos, Prototipagem, Metodologias Ágeis, Versionamento e Documentação. |
| Sprint 2 | 13/03 – 18/03/2026 | Diagramas UML | Revisão geral. Diagrama de Classes e Diagrama de Sequência. |
| Sprint 3 | 01/04 – 10/04/2026 | Banco de Dados | Revisão geral. Diagramação de Banco de Dados (DER e modelo lógico). |
| Sprint 4 | 29/04 – 08/05/2026 | Testes | Revisão geral. Testes de Acessibilidade, Funcionalidades e Recursos. |
| Sprint 5 | 27/05 – 29/05/2026 | Qualidade & Deploy I | Revisão geral. Qualidade de Software. Início da Implantação. |
| Sprint 6 | 12/06 – 17/06/2026 | Deploy II & Entrega | Implantação final. Entrega e Apresentação dos Projetos. |

> 📌 Acompanhe o progresso detalhado no **[Quadro Trello](https://trello.com/invite/b/699895865de97ce9e1ed0a19/ATTI092d2e5ed783761fc1dc5a293b494341499AB6EA/projeto-integrador)**

---

## 👥 Equipe

<div align="center">

| [Douglas Nogueira](https://github.com/) | [Hayron de Oliveira](https://github.com/) | [Gabriel Marques](https://github.com/) |
|:---:|:---:|:---:|
| Desenvolvedor | Desenvolvedor | Desenvolvedor |

**Instituição:** SENAI — Limeira, SP  
**Projeto:** Integrador — Indústria 4.0 · 2026

</div>

---

<div align="center">

**🔗 Links rápidos**

[![Protótipo Figma](https://img.shields.io/badge/Protótipo_Figma-F24E1E?style=for-the-badge&logo=figma&logoColor=white)](https://cleat-pickle-20252208.figma.site/login)
[![Quadro Trello](https://img.shields.io/badge/Quadro_Trello-0052CC?style=for-the-badge&logo=trello&logoColor=white)](https://trello.com/invite/b/699895865de97ce9e1ed0a19/ATTI092d2e5ed783761fc1dc5a293b494341499AB6EA/projeto-integrador)

<sub>Desenvolvido com ❤️ por Douglas Nogueira, Hayron de Oliveira & Gabriel Marques · SENAI Limeira 2026</sub>

</div>
