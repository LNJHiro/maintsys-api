# 📋 MaintSys - Sistema de Gerenciamento de Manutenção de Máquinas

## 🎯 Visão Geral

**MaintSys** é um sistema completo de gerenciamento de manutenção de máquinas e equipamentos, desenvolvido em **Laravel 11** com **Blade Templates** e **Tailwind CSS**.

O sistema permite:
- ✅ Cadastrar e gerenciar máquinas
- ✅ Gerenciar técnicos responsáveis
- ✅ Criar e controlar ordens de serviço (preventivas e corretivas)
- ✅ Manter histórico completo de manutenções
- ✅ Sincronizar status de máquinas automaticamente
- ✅ Controlar permissões por usuário/role
- ✅ Gerar manutenções preventivas futuras automaticamente
- ✅ Exportar dados em CSV
- ✅ Notificar técnicos de atribuições

---

## 🗺️ Referência Rápida (Arquivo:Linha)

> **Atalhos:** `CTR/` = `app/Http/Controllers/` · `MDL/` = `app/Models/` · `VW/` = `resources/views/`

### Controllers — Técnicos

| Funcionalidade            | Arquivo                     | Linhas  | Descrição                              |
|---------------------------|-----------------------------|---------|----------------------------------------|
| Listar técnicos           | `CTR/TecnicoController.php` | 32–44   | Lista paginada com contagem de O.S.    |
| Criar (formulário)        | `CTR/TecnicoController.php` | 46–49   | Exibe form vazio                       |
| Criar (salvar)            | `CTR/TecnicoController.php` | 51–89   | Valida + cria User e Tecnico em transação |
| Ver detalhes              | `CTR/TecnicoController.php` | 91–95   | Carrega ordens e histórico             |
| Editar (formulário)       | `CTR/TecnicoController.php` | 97–102  | Form pré-preenchido                    |
| Editar (salvar)           | `CTR/TecnicoController.php` | 104–162 | Atualiza User e Tecnico                |
| Deletar                   | `CTR/TecnicoController.php` | 164–191 | Bloqueia se tem O.S. ou histórico      |

### Controllers — Máquinas

| Funcionalidade            | Arquivo                     | Linhas  | Descrição                              |
|---------------------------|-----------------------------|---------|----------------------------------------|
| Listar com estatísticas   | `CTR/MaquinaController.php` | 32–47   | Array `$stats` por status              |
| Criar                     | `CTR/MaquinaController.php` | 49–68   | Valida + alerta se status crítico      |
| Ver detalhes              | `CTR/MaquinaController.php` | 70–79   | Eager load: ordens + histórico         |
| Editar                    | `CTR/MaquinaController.php` | 81–105  | Detecta mudança de status              |
| Deletar                   | `CTR/MaquinaController.php` | 107–124 | Bloqueia se tem vínculos               |

### Controllers — Ordens de Serviço

| Funcionalidade                     | Arquivo                              | Linhas  | Descrição                                  |
|------------------------------------|--------------------------------------|---------|--------------------------------------------|
| Listar O.S.                        | `CTR/OrdemServicoController.php`     | 47–70   | Lista + array `$stats`                     |
| Criar (formulário)                 | `CTR/OrdemServicoController.php`     | 72–83   | Dropdowns de máquinas e técnicos           |
| Criar (salvar)                     | `CTR/OrdemServicoController.php`     | 85–131  | Número único + sincroniza + notifica       |
| Ver detalhes                       | `CTR/OrdemServicoController.php`     | 133–142 | Carrega histórico vinculado                |
| Editar (formulário)                | `CTR/OrdemServicoController.php`     | 144–157 | Form com campos de conclusão               |
| Atualizar / Concluir               | `CTR/OrdemServicoController.php`     | 159–269 | **COMPLEXO** — histórico + preventiva      |
| Criar histórico ao concluir        | `CTR/OrdemServicoController.php`     | 183–200 | `HistoricoManutencao::create()`            |
| Criar próxima preventiva (auto)    | `CTR/OrdemServicoController.php`     | 217–232 | Nova O.S. gerada automaticamente           |
| Exportar individual (CSV)          | `CTR/OrdemServicoController.php`     | 271–313 | Campos detalhados da O.S.                  |
| Exportar todas (CSV)               | `CTR/OrdemServicoController.php`     | 315–343 | CSV resumido                               |
| Deletar                            | `CTR/OrdemServicoController.php`     | 345–365 | Deleta + sincroniza status da máquina      |
| Sincronizar status máquina         | `CTR/OrdemServicoController.php`     | 367–407 | **CRÍTICO** — automação de status          |
| Notificar técnico                  | `CTR/OrdemServicoController.php`     | 409–427 | Notificação ao atribuir O.S.               |

### Controllers — Histórico e Dashboard

| Funcionalidade            | Arquivo                          | Linhas  | Descrição                              |
|---------------------------|----------------------------------|---------|----------------------------------------|
| Listar histórico          | `CTR/HistoricoController.php`    | 25–40   | Com filtros (máquina, tipo, data)      |
| Ver registro              | `CTR/HistoricoController.php`    | 42–47   | Detalhes com O.S. vinculada            |
| Histórico por máquina     | `CTR/HistoricoController.php`    | 49–66   | Lista + análise de reincidências       |
| Exportar (CSV)            | `CTR/HistoricoController.php`    | 68–92   | CSV com filtros aplicados              |
| Dashboard (stats + dados) | `CTR/DashboardController.php`    | 32–62   | Todos os dados da página inicial       |

### Views — Dashboard

| Seção                     | Arquivo                          | Linhas  | Descrição                              |
|---------------------------|----------------------------------|---------|----------------------------------------|
| Cards de estatísticas     | `VW/dashboard.blade.php`         | 21–61   | Contadores de máquinas, O.S., técnicos |
| Alertas de parada crítica | `VW/dashboard.blade.php`         | 63–84   | Máquinas em `parada_critica`           |
| Tabela de O.S. ativas     | `VW/dashboard.blade.php`         | 89–165  | Ordens ordenadas por prioridade        |
| Últimas manutenções       | `VW/dashboard.blade.php`         | 172–219 | 5 históricos mais recentes             |
| Ações rápidas (botões)    | `VW/dashboard.blade.php`         | 223–247 | Botões de criação rápida               |

### Models — Métodos e Scopes

| Método / Atributo       | Arquivo                   | Linhas  | Tipo    | Descrição                          |
|-------------------------|---------------------------|---------|---------|------------------------------------|
| `isAdmin()`             | `MDL/User.php`            | 57–60   | Função  | Verifica se role é admin           |
| `isMaster()`            | `MDL/User.php`            | 62–65   | Função  | Verifica se é admin_master         |
| `hasPermission($perm)`  | `MDL/User.php`            | 71–79   | Função  | Verifica permissão específica      |
| `permissionNames()`     | `MDL/User.php`            | 81–113  | Função  | Array de permissões (com cache)    |
| `scopeOperacional()`    | `MDL/Maquina.php`         | 58–61   | Scope   | Filtra status = operacional        |
| `scopeEmManutencao()`   | `MDL/Maquina.php`         | 63–66   | Scope   | Filtra status = em_manutencao      |
| `scopeParadaCritica()`  | `MDL/Maquina.php`         | 68–71   | Scope   | Filtra status = parada_critica     |
| `status_label`          | `MDL/Maquina.php`         | 73–84   | Acessor | Texto legível do status            |
| `status_color`          | `MDL/Maquina.php`         | 86–97   | Acessor | Cor visual do status               |
| `gerarNumero()`         | `MDL/OrdemServico.php`    | 111–130 | Estática| Número único com lockForUpdate     |
| `tipo_label`            | `MDL/OrdemServico.php`    | 56–61   | Acessor | Preventiva / Corretiva             |
| `status_label`          | `MDL/OrdemServico.php`    | 63–73   | Acessor | Aberta / Em Andamento / Concluída  |
| `prioridade_label`      | `MDL/OrdemServico.php`    | 75–84   | Acessor | Baixa / Média / Alta / Crítica     |

---

## 🏗️ Arquitetura Geral

```
┌─────────────────────────────────────────┐
│         CAMADA DE APRESENTAÇÃO           │
│  (Views Blade / Tailwind CSS)           │
└────────────────┬────────────────────────┘
                 │
┌─────────────────▼────────────────────────┐
│         CAMADA DE CONTROLE                │
│  (Controllers / Lógica de Negócio)       │
└────────────────┬────────────────────────┘
                 │
┌─────────────────▼────────────────────────┐
│         CAMADA DE DADOS                   │
│  (Models / Eloquent ORM)                 │
└────────────────┬────────────────────────┘
                 │
┌─────────────────▼────────────────────────┐
│      BANCO DE DADOS (MySQL)               │
│  (Tabelas: users, maquinas, tecnicos...) │
└──────────────────────────────────────────┘
```

### Fluxo de Dados

```
Cliente HTTP (Browser)
       ↓
Router (routes/web.php)
       ↓
Middleware (Autenticação, Permissões)
       ↓
Controller (Lógica de negócio)
       ↓
Model (Acesso a dados via Eloquent)
       ↓
Banco de Dados (MySQL)
       ↓
Retorno para View (Blade Template)
       ↓
HTML/CSS/JS para Browser
```

---

## 📁 Estrutura de Pastas e Arquivos

```
projeto/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── TecnicoController.php      # CRUD de técnicos
│   │   │   ├── MaquinaController.php      # CRUD de máquinas
│   │   │   ├── OrdemServicoController.php # CRUD de O.S.
│   │   │   ├── HistoricoController.php    # Histórico e análise
│   │   │   ├── DashboardController.php    # Dashboard principal
│   │   │   ├── AccessController.php       # Gerenciamento de permissões
│   │   │   ├── UserManagementController.php # Gerenciamento de usuários
│   │   │   ├── ProfileController.php      # Perfil de usuário
│   │   │   └── Auth/                      # Controllers de autenticação
│   │   ├── Middleware/
│   │   │   ├── CheckPermission.php        # Verifica permissões
│   │   │   ├── CheckAdmin.php             # Verifica se é admin
│   │   │   ├── CheckMaster.php            # Verifica se é master
│   │   │   └── CheckAdminAccess.php       # Acesso admin
│   │   └── Requests/                      # Form Requests (validação)
│   ├── Models/
│   │   ├── User.php                       # Usuário do sistema
│   │   ├── Tecnico.php                    # Técnico
│   │   ├── Maquina.php                    # Máquina
│   │   ├── OrdemServico.php               # Ordem de Serviço
│   │   ├── HistoricoManutencao.php        # Histórico
│   │   ├── Permission.php                 # Permissão
│   │   ├── RolePermission.php             # Role-Permission
│   │   └── UserPermission.php             # User-Permission
│   ├── Notifications/
│   │   └── OrdemServicoAtribuida.php      # Notificação de O.S.
│   ├── Providers/
│   │   └── AppServiceProvider.php         # Configurações gerais
│   └── View/
│       └── Components/                    # Componentes Blade
├── resources/
│   ├── views/
│   │   ├── dashboard.blade.php            # Dashboard principal
│   │   ├── tecnicos/                      # Views de técnicos
│   │   ├── maquinas/                      # Views de máquinas
│   │   ├── ordens/                        # Views de O.S.
│   │   ├── historico/                     # Views de histórico
│   │   ├── usuarios/                      # Views de usuários
│   │   ├── acesso/                        # Views de permissões
│   │   ├── auth/                          # Views de autenticação
│   │   ├── layouts/                       # Layouts base
│   │   └── components/                    # Componentes reutilizáveis
│   ├── css/                               # Tailwind CSS
│   └── js/                                # JavaScript
├── database/
│   ├── migrations/                        # Migrações de schema
│   └── seeders/                           # Seeders (dados iniciais)
├── routes/
│   ├── web.php                            # Rotas web (POST/GET/PUT/DELETE)
│   └── auth.php                           # Rotas de autenticação
├── config/                                # Configurações da app
├── storage/                               # Armazenamento (logs, cache)
├── tests/                                 # Testes (PHPUnit)
└── README.md                              # Este arquivo
```

---

## 🛠️ Tecnologias Utilizadas

| Tecnologia | Versão | Uso |
|-----------|--------|-----|
| **Laravel** | 11 | Framework PHP |
| **PHP** | 8.2+ | Linguagem servidor |
| **MySQL/MariaDB** | 5.7+ | Banco de dados |
| **Blade** | Laravel | Template engine |
| **Tailwind CSS** | 3 | Estilização |
| **Eloquent ORM** | Laravel | Acesso a dados |
| **Vite** | 5 | Build tool |

---

## 📊 Modelo de Dados

### Tabelas Principais

#### `users` (Usuários do Sistema)
```sql
- id: INT PRIMARY KEY
- name: VARCHAR(255)
- email: VARCHAR(255) UNIQUE
- password: VARCHAR(255) (hash)
- role: VARCHAR(50) -- admin_master, admin, supervisor, usuario
- permissions_overridden: BOOLEAN (default: false)
- email_verified_at: TIMESTAMP (nullable)
- created_at, updated_at: TIMESTAMP
```

#### `tecnicos` (Técnicos)
```sql
- id: INT PRIMARY KEY
- user_id: INT (FK users)
- nome: VARCHAR(255)
- matricula: VARCHAR(50) UNIQUE
- email: VARCHAR(255)
- password: VARCHAR(255) (hash)
- especialidade: VARCHAR(255) -- elétrica, mecânica, etc
- telefone: VARCHAR(20)
- ativo: BOOLEAN (default: true)
- created_at, updated_at: TIMESTAMP
```

#### `maquinas` (Máquinas)
```sql
- id: INT PRIMARY KEY
- numero_serie: VARCHAR(100) UNIQUE
- modelo: VARCHAR(255)
- fabricante: VARCHAR(255)
- localizacao: VARCHAR(255)
- data_cadastro: DATE
- status: ENUM -- operacional, em_manutencao, parada_critica, inativa
- descricao: TEXT
- created_at, updated_at: TIMESTAMP
```

#### `ordens_servico` (Ordens de Serviço)
```sql
- id: INT PRIMARY KEY
- numero: VARCHAR(50) UNIQUE -- OS-20260608-0001
- tipo: ENUM -- preventiva, corretiva
- status: ENUM -- aberta, em_andamento, concluida, cancelada
- prioridade: ENUM -- baixa, media, alta, critica
- descricao: TEXT
- solucao: TEXT (nullable)
- maquina_id: INT (FK maquinas)
- tecnico_id: INT (FK tecnicos, nullable)
- data_abertura: DATETIME
- data_prevista: DATE (nullable)
- data_conclusao: DATETIME (nullable)
- created_at, updated_at: TIMESTAMP
```

#### `historico_manutencoes` (Histórico de Manutenções)
```sql
- id: INT PRIMARY KEY
- maquina_id: INT (FK maquinas)
- tecnico_id: INT (FK tecnicos)
- ordem_id: INT (FK ordens_servico, nullable)
- tipo: ENUM -- preventiva, corretiva
- descricao: TEXT
- solucao: TEXT
- pecas_utilizadas: TEXT
- tempo_parada_horas: DECIMAL(8,2) -- Horas que máquina ficou parada
- custo: DECIMAL(10,2) -- Custo em R$
- data_inicio: DATETIME
- data_fim: DATETIME (nullable)
- observacoes: TEXT (nullable)
- created_at, updated_at: TIMESTAMP
```

#### `permissions` (Permissões do Sistema)
```sql
- id: INT PRIMARY KEY
- name: VARCHAR(255) UNIQUE -- usuarios.visualizar, maquinas.editar, etc
- descricao: VARCHAR(255)
- modulo: VARCHAR(50) -- usuarios, maquinas, tecnicos, ordens, historico, acesso
- created_at, updated_at: TIMESTAMP
```

#### `role_permissions` (Associação Role-Permission)
```sql
- id: INT PRIMARY KEY
- role: VARCHAR(50) -- admin_master, admin, supervisor, usuario
- permission_id: INT (FK permissions)
- created_at, updated_at: TIMESTAMP
```

#### `user_permissions` (Permissões Customizadas de Usuário)
```sql
- id: INT PRIMARY KEY
- user_id: INT (FK users)
- permission_id: INT (FK permissions)
- created_at, updated_at: TIMESTAMP
```

### Relacionamentos

```
User (1) ──────── (1) Tecnico
User (1) ──────── (*) UserPermission
User (1) ──────── (*) Notificação

Tecnico (1) ──────── (*) OrdemServico
Tecnico (1) ──────── (*) HistoricoManutencao

Maquina (1) ──────── (*) OrdemServico
Maquina (1) ──────── (*) HistoricoManutencao

OrdemServico (1) ──────── (1) HistoricoManutencao
OrdemServico (*) ──────── (1) Tecnico
OrdemServico (*) ──────── (1) Maquina

Permission (1) ──────── (*) RolePermission
Permission (1) ──────── (*) UserPermission
```

---

## 🔍 Como Encontrar Coisas Rapidamente

### "Quero mudar a cor dos cards do Dashboard"

```
1. VW/dashboard.blade.php:21        → Bloco dos cards (linha 21–61)
2. Procure: <div class="stat-card"> → HTML de cada card
3. Cores CSS:  resources/views/layouts/app.blade.php
4. Dados:      CTR/DashboardController.php:32  → array $stats
```

### "Quero adicionar um campo novo no formulário de técnico"

```
1. VW/tecnicos/create.blade.php     → Adiciona o input no HTML
2. CTR/TecnicoController.php:63     → Adiciona o campo em validate([...])
3. MDL/Tecnico.php:14               → Adiciona o campo em $fillable
4. php artisan make:migration ...   → Cria coluna no banco
5. php artisan migrate              → Aplica a migration
```

### "Quero entender como a O.S. sincroniza status de máquina"

```
1. CTR/OrdemServicoController.php:367  → sincronizarStatusMaquina()
   É chamada em:
     - store()   linha 82
     - update()  linha 207
     - destroy() linha 356
2. Lógica completa: linhas 367–407
3. Ver também: seção "Sincronização Automática de Status" neste README
```

### "Quero ver como o histórico é criado automaticamente"

```
1. CTR/OrdemServicoController.php:183  → HistoricoManutencao::create()
   Contexto: dentro de update(), quando status muda para 'concluida'
2. Model do histórico:  MDL/HistoricoManutencao.php
3. Tabela no banco:     historico_manutencoes
4. Listar históricos:   CTR/HistoricoController.php:25
```

---

## 🚀 Guia de Instalação e Configuração

### Pré-requisitos
- PHP 8.2 ou superior
- Composer
- MySQL 5.7+ ou MariaDB
- Node.js 18+ (para build de assets)

### Passo 1: Clonar ou Preparar Projeto
```bash
# Se está em um projeto existente, certifique-se de estar no diretório correto
cd /caminho/para/projeto/MaintSys
```

### Passo 2: Instalar Dependências PHP
```bash
composer install
```por exemplo

### Passo 3: Configurar Arquivo `.env`
```bash
# Copiar arquivo de exemplo
cp .env.example .env

# Gerar chave de aplicação
php artisan key:generate
```

### Editar `.env` com Banco de Dados
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=maintsys_db  # Nome do banco
DB_USERNAME=root         # Usuário do MySQL
DB_PASSWORD=             # Senha (deixar em branco se for root sem senha)
```

### Passo 4: Executar Migrações
```bash
# Cria todas as tabelas
php artisan migrate

# Popula dados iniciais (permissões, roles)
php artisan db:seed
```

### Passo 5: Instalar Dependências JavaScript
```bash
npm install
npm run build  # Para produção: npm run build
```

### Passo 6: Iniciar Servidor
```bash
php artisan serve
# Servidor rodará em http://localhost:8000
```

---

## 🔐 Autenticação e Permissões

### Sistema de Roles (Papéis)

| Role | Descrição | Permissões |
|------|-----------|-----------|
| **admin_master** | Administrador supremo | Acesso total, sem restrições |
| **admin** | Administrador | Gerenciar usuários, máquinas, técnicos, permissões |
| **supervisor** | Supervisor | Visualizar e criar O.S., ver histórico |
| **usuario** | Usuário comum | Visualizar dados, criar alguns registros |

### Permissões Granulares

O sistema usa permissões por formato: `recurso.acao`

Exemplos:
- `usuarios.visualizar` - Ver lista de usuários
- `usuarios.criar` - Criar novo usuário
- `usuarios.editar` - Editar usuário existente
- `usuarios.deletar` - Deletar usuário
- `maquinas.visualizar`, `maquinas.criar`, `maquinas.editar`, `maquinas.deletar`
- `tecnicos.visualizar`, `tecnicos.criar`, `tecnicos.editar`, `tecnicos.deletar`
- `ordens.visualizar`, `ordens.criar`, `ordens.editar`, `ordens.deletar`
- `historico.visualizar`, `historico.criar`, `historico.deletar`
- `acesso.gerenciar` - Gerenciar permissões e acesso

### Fluxo de Verificação de Permissão

```
Requisição chega
       ↓
Middleware CheckPermission verifica
       ↓
Se usuário é admin_master → Acesso concedido
       ↓
Se user.permissions_overridden == false
    → Usa permissões do role
       ↓
Se user.permissions_overridden == true
    → Usa permissões específicas (tabela user_permissions)
       ↓
Compara com permissão esperada da rota
       ↓
Acesso concedido ou erro 403
```

---

## 📋 Documentação de Funcionalidades Principais

### 1️⃣ Como Funciona: Criação de Técnicos

**Arquivo Principal:** `app/Http/Controllers/TecnicoController.php`

#### Processo Completo

```
1. Usuário acessa GET /tecnicos/create
   └─> Exibe formulário vazio

2. Usuário preenche formulário e envia POST /tecnicos
   ├─ Nome: "João Silva"
   ├─ Matrícula: "TEC001"
   ├─ Email: "joao@empresa.com"
   ├─ Senha: "senha123"
   ├─ Especialidade: "Elétrica"
   ├─ Telefone: "(11) 99999-9999"
   └─ Ativo: true

3. Controller TecnicoController::store() executa em transação:
   ├─ Valida todos os campos
   │  └─ Matrícula única
   │  └─ Email único em tecnicos E users
   │  └─ Senha mínimo 8 caracteres com confirmação
   │
   ├─ DB::transaction() inicia (atomicidade):
   │  ├─ Hash da senha com Bcrypt
   │  ├─ Cria User (para login do técnico)
   │  │  └─ role = 'usuario'
   │  └─ Cria Tecnico vinculado ao User
   │
   └─ Se erro em qualquer passo → ROLLBACK (desfaz tudo)

4. Redirecionamento com mensagem de sucesso
   └─> GET /tecnicos (mostra lista atualizada)
```

#### Validações Aplicadas

| Campo | Validação |
|-------|-----------|
| `nome` | Obrigatório, string, máx 255 caracteres |
| `matricula` | Obrigatório, string, único, máx 50 caracteres |
| `email` | Obrigatório, email válido, único em 2 tabelas |
| `password` | Obrigatório, min 8 caracteres, confirmação obrigatória |
| `especialidade` | Opcional, string, máx 255 caracteres |
| `telefone` | Opcional, string, máx 20 caracteres |

---

### 2️⃣ Como Funciona: Edição de Técnicos

**Arquivo Principal:** `app/Http/Controllers/TecnicoController.php::update()`

#### Processo

```
1. Usuário acessa GET /tecnicos/{id}/edit
   └─> Exibe formulário pré-preenchido

2. Usuário altera dados e envia PUT /tecnicos/{id}
   ├─ Pode atualizar qualquer campo
   ├─ Opcional: deixar senha vazia (não muda)
   └─ Opcional: preencher senha (muda password)

3. Controller executa em transação:
   ├─ Valida dados (unique ignora próprio registro)
   │  └─ Matrícula: unique EXCEPT current ID
   │  └─ Email: unique EXCEPT current Tecnico AND User
   │
   ├─ Se password preenchido:
   │  └─ Valida min 8 caracteres e confirmação
   │  └─ Faz hash da nova senha
   │
   ├─ DB::transaction():
   │  ├─ Se técnico NÃO tem usuário:
   │  │  └─ Cria novo User vinculado
   │  └─ Se técnico JÁ tem usuário:
   │     ├─ Atualiza name e email do User
   │     └─ Se password novo, atualiza no User também
   │
   └─ Atualiza dados do Tecnico

4. Redirecionamento com sucesso
```

#### Casos Especiais

| Caso | Comportamento |
|------|---------------|
| Técnico sem usuário | Cria novo usuário automaticamente |
| Trocar email | Atualiza em User e Tecnico |
| Nova senha | Hash e atualiza em ambas tabelas |
| Deixar senha vazia | Mantém password atual |

---

### 3️⃣ Como Funciona: Dashboard Principal

**Arquivo Principal:** `app/Http/Controllers/DashboardController.php`

#### O que é Exibido

```
DASHBOARD PRINCIPAL (GET /dashboard)
│
├─ 📊 ESTATÍSTICAS (Cards)
│  ├─ Total de máquinas
│  ├─ Máquinas operacionais
│  ├─ Máquinas em manutenção
│  ├─ Máquinas em parada crítica
│  ├─ Técnicos ativos
│  ├─ O.S. abertas
│  ├─ O.S. em andamento
│  └─ O.S. concluídas hoje
│
├─ ⚠️ ALERTAS (Máquinas em Parada Crítica)
│  └─ Lista de máquinas críticas com O.S. ativas
│
├─ 📋 ORDENS PENDENTES (8 mais importantes)
│  ├─ Ordenadas por PRIORIDADE (críticas primeiro)
│  ├─ Status: aberta ou em_andamento
│  └─ Informações: número, máquina, técnico, prioridade
│
└─ 📈 ÚLTIMAS MANUTENÇÕES (5 mais recentes)
   ├─ Históricos concluídos
   ├─ Máquina, técnico, tipo, data
   └─ Usados para análise de tendências
```

#### Dados em Tempo Real

Cada métrica é calculada em TEMPO REAL:
```php
// Exemplo de cálculo
$stats['os_abertas'] = OrdemServico::where('status', 'aberta')->count();
// = SELECT COUNT(*) FROM ordens_servico WHERE status = 'aberta'
```

---

### 4️⃣ Como Funciona: Criação e Gerenciamento de Ordens de Serviço

**Arquivo Principal:** `app/Http/Controllers/OrdemServicoController.php`

#### 4.1 - Criar Ordem de Serviço

```
POST /ordens (criar)
│
├─ Valida dados:
│  ├─ tipo: preventiva ou corretiva
│  ├─ prioridade: baixa, media, alta, critica
│  ├─ descricao: obrigatória
│  ├─ maquina_id: deve existir
│  ├─ tecnico_id: deve existir
│  └─ data_prevista: opcional
│
├─ Em transação DB:
│  ├─ Gera número único com lock:
│  │  └─ Formato: OS-YYYYMMDD-NNNN
│  │  └─ Exemplo: OS-20260608-0001
│  │
│  ├─ Define status = 'aberta'
│  ├─ Registra data_abertura = now()
│  ├─ Cria OrdemServico no banco
│  │
│  └─ Sincroniza status da máquina:
│     ├─ Se tem O.S. ativa e máquina está 'operacional'
│     │  └─ Muda para 'em_manutencao'
│     └─ Se não tem mais O.S. ativa e está 'em_manutencao'
│        └─ Volta para 'operacional'
│
├─ Notifica técnico que foi atribuído
│
└─ Retorna sucesso
```

#### 4.2 - Atualizar Ordem de Serviço

```
PUT /ordens/{id} (actualizar/concluir)
│
├─ Valida dados (pode ser complexo se concluindo)
│
├─ Validação especial:
│  └─ Se O.S. já foi concluída
│     └─ NÃO pode mudar para outro status
│     └─ Deve criar nova O.S. se preciso
│
├─ Em transação DB (MUITO IMPORTANTE):
│  │
│  ├─ Se mudando para 'concluida' PELA PRIMEIRA VEZ:
│  │  ├─ Registra data_conclusao = now()
│  │  │
│  │  ├─ CRIA HISTÓRICO AUTOMATICAMENTE:
│  │  │  └─ HistoricoManutencao com:
│  │  │     ├─ tempo_parada_horas (do form)
│  │  │     ├─ custo (do form)
│  │  │     ├─ pecas_utilizadas (do form)
│  │  │     ├─ data_inicio = data_abertura da O.S.
│  │  │     └─ data_fim = data_conclusao agora
│  │  │
│  │  └─ SE É PREVENTIVA E TEM data_proxima_preventiva:
│  │     └─ CRIA AUTOMATICAMENTE próxima O.S. preventiva:
│  │        ├─ Gera novo número
│  │        ├─ Tipo = preventiva
│  │        ├─ Status = aberta
│  │        ├─ data_prevista = data passada no form
│  │        └─ Notifica técnico da nova O.S.
│  │
│  ├─ Sincroniza status de máquinas:
│  │  └─ Máquina anterior (se mudou)
│  │  └─ Máquina nova
│  │
│  └─ Se técnico mudou:
│     └─ Adiciona para notificação
│
└─ Retorna sucesso com alertas
```

#### 4.3 - Sincronização Automática de Status de Máquina

**Função Crítica:** `sincronizarStatusMaquina($maquinaId)`

```
DECISÃO DE STATUS DA MÁQUINA:
│
├─ Verifica se EXISTE O.S. ATIVA:
│  └─ O.S. ativa = está aberta/em_andamento E (
│     ├─ status é 'em_andamento' OU
│     ├─ tipo é 'corretiva' OU
│     ├─ não tem data_prevista OU
│     └─ data_prevista <= hoje
│  )
│
├─ Se TEM O.S. ATIVA:
│  └─ Se máquina está 'operacional':
│     └─ Muda para 'em_manutencao'
│     └─ Retorna alerta
│
├─ Se NÃO TEM O.S. ATIVA:
│  └─ Se máquina está 'em_manutencao':
│     └─ Volta para 'operacional'
│     └─ Retorna alerta
│
└─ Se máquina está 'parada_critica' ou 'inativa':
   └─ NÃO muda (admin controla manualmente)
```

**Exemplo Prático:**
```
Máquina FRESA-001 está 'operacional'
      ↓
Crio O.S. corretiva para ela
      ↓
sincronizarStatusMaquina() é chamado
      ↓
Verifica: existe O.S. corretiva ativa? SIM
      ↓
Máquina está 'operacional'? SIM
      ↓
→ Muda para 'em_manutencao'
→ Retorna alerta: "FRESA-001 passou para Em Manutenção"
```

---

### 5️⃣ Como Funciona: Criação e Edição de Máquinas

**Arquivo Principal:** `app/Http/Controllers/MaquinaController.php`

#### Processo de Criação

```
GET /maquinas/create → Formulário vazio
        ↓
POST /maquinas → Store
        │
        ├─ Valida:
        │  ├─ numero_serie: unique
        │  ├─ modelo, localizacao: obrigatórios
        │  ├─ fabricante: opcional
        │  ├─ data_cadastro: opcional (default: hoje)
        │  ├─ status: obrigatório (operacional, em_manutencao, parada_critica, inativa)
        │  └─ descricao: opcional
        │
        ├─ Cria Maquina
        │
        └─ Se status = 'parada_critica':
           └─ Exibe ALERTA VISUAL

```

#### Atributos Acessores (Conversão de Status)

```
Database: 'operacional'     → View: 'Operacional' (getStatusLabelAttribute)
Database: 'em_manutencao'   → View: 'Em Manutenção'
Database: 'parada_critica'  → View: 'Parada Crítica'
Database: 'inativa'         → View: 'Inativa'

Colors:
'operacional'   → green   (OK)
'em_manutencao' → yellow  (AVISO)
'parada_critica'→ red     (PERIGO)
'inativa'       → gray    (DESATIVADA)
```

#### Estatísticas em Tempo Real

No index de máquinas, exibe:
```
Total de máquinas: 15
Operacionais: 12
Em manutenção: 2
Parada crítica: 1
```

---

### 6️⃣ Como Funciona: Histórico de Manutenções

**Arquivo Principal:** `app/Http/Controllers/HistoricoController.php`

#### Criação do Histórico

O histórico é criado **automaticamente** quando uma O.S. é **concluída**:

```
PUT /ordens/{id} (atualizar status para 'concluida')
        ↓
No update(), se status mudou para 'concluida':
        ├─ Cria HistoricoManutencao com:
        │  ├─ maquina_id
        │  ├─ tecnico_id
        │  ├─ ordem_id (referência da O.S.)
        │  ├─ tipo (preventiva/corretiva)
        │  ├─ descricao, solucao (da O.S.)
        │  ├─ tempo_parada_horas (do form)
        │  ├─ custo (do form)
        │  ├─ pecas_utilizadas (do form)
        │  ├─ data_inicio = data_abertura da O.S.
        │  └─ data_fim = data_conclusao agora
        │
        └─ Não pode deletar O.S. se tem histórico
```

#### Visualização e Filtros

```
GET /historico
├─ Exibe lista paginada (20 por página)
├─ Filtros disponíveis:
│  ├─ maquina_id
│  ├─ tipo (preventiva/corretiva)
│  ├─ tecnico_id
│  ├─ período (data_inicio entre X e Y)
│  └─ Todos os filtros são OPCIONAIS
├─ Ordenação: mais recentes primeiro
└─ Cada linha mostra: máquina, tipo, técnico, datas, parada (h), custo (R$)
```

#### Análise de Reincidências

```
GET /historico/maquina/{maquinaId}
├─ Exibe histórico completo da máquina
└─ REINCIDÊNCIAS (problemas recorrentes):
   ├─ Conta manutenções corretivas por MÊS
   ├─ Agrupa por MONTH(data_inicio) e YEAR(data_inicio)
   ├─ Ordena por ano DESC, mês DESC (mais recentes primeiro)
   └─ Exibe: "3 correções em dezembro/2025, 2 em novembro/2025"
      └─ Ajuda a identificar máquinas com problemas crônicos
```

#### Exportação em CSV

```
GET /historico/exportar?filtros
├─ Aplica mesmos filtros do index
├─ Busca TODOS os registros (sem paginação)
├─ Cria CSV em memória
├─ Colunas: ID, Máquina, Tipo, Técnico, O.S., Início, Fim, Parada, Custo
├─ Codificação: UTF-8 com BOM (compatível com Excel)
└─ Download: historico-manutencoes-YYYY-MM-DD.csv
```

---

## 🔧 Fluxo Detalhado de Dados

### Exemplo Completo: Da Criação de O.S. até Conclusão

```
┌─────────────────────────────────────────────────────────────┐
│ PASSO 1: CRIAR ORDEM DE SERVIÇO                             │
└─────────────────────────────────────────────────────────────┘

Supervisor acessa: GET /ordens/create
        ↓
Exibe formulário com:
├─ Dropdowns de maquinas (ordenadas por modelo)
├─ Dropdowns de tecnicos (apenas ativos)
└─ Campos: tipo, prioridade, descricao, data_prevista

Supervisor preenche e clica "Criar":
├─ Tipo: corretiva
├─ Prioridade: alta
├─ Descrição: "Compressor não está ligando"
├─ Máquina: COMPRESSOR-01
├─ Técnico: João Silva
└─ Data prevista: 2026-06-10

POST /ordens
        ↓
TecnicoController::store() executa:
        │
        ├─ Valida campos
        │  └─ Todos obrigatórios, máquina e técnico existem
        │
        ├─ DB::transaction() INICIA
        │  │
        │  ├─ Gera número com LOCK:
        │  │  └─ Busca último do dia: OS-20260608-0002
        │  │  └─ Incrementa: OS-20260608-0003
        │  │
        │  ├─ Define valores iniciais:
        │  │  ├─ status = 'aberta'
        │  │  ├─ data_abertura = 2026-06-08 14:30:45
        │  │  └─ Sem data_conclusao (ainda aberta)
        │  │
        │  ├─ OrdemServico::create() no banco
        │  │
        │  ├─ sincronizarStatusMaquina(COMPRESSOR-01):
        │  │  ├─ Verifica se tem O.S. ativa
        │  │  │  └─ Sim: tipo='corretiva' (ativa)
        │  │  ├─ Máquina está 'operacional'?
        │  │  │  └─ Sim
        │  │  └─ → Muda para 'em_manutencao'
        │  │  └─ → Retorna alerta
        │  │
        │  └─ DB::transaction() COMMIT (todas as mudanças salvas)
        │
        ├─ notificarTecnicoAtribuido():
        │  ├─ Carrega Tecnico->User
        │  ├─ Se usuário existe
        │  └─ Envia notificação OrdemServicoAtribuida
        │     └─ Técnico recebe: "Você foi atribuído à O.S. OS-20260608-0003"
        │
        └─ Redirect com mensagens:
           ├─ Sucesso: "O.S. OS-20260608-0003 criada com sucesso!"
           └─ Alerta: "Compressor COMP-01 passou para Em Manutenção."

GET /ordens
        ↓
Dashboard mostra: 1 O.S. aberta, COMP-01 em 'em_manutencao'


┌─────────────────────────────────────────────────────────────┐
│ PASSO 2: TÉCNICO COMEÇA A EXECUTAR                          │
└─────────────────────────────────────────────────────────────┘

João Silva (técnico) acessa: GET /ordens/{id}/edit
        ↓
Vê formulário com dados da O.S.

Clica em "Começar Execução" ou edita e muda status para 'em_andamento':
├─ Status: em_andamento
├─ Descrição: (igual)
└─ Solução: (ainda vazio)

PUT /ordens/{id}
        ↓
Status muda de 'aberta' para 'em_andamento'
        ├─ sincronizarStatusMaquina() valida se máquina deve manter 'em_manutencao'
        │  └─ Sim, mantém (tem O.S. em_andamento)
        └─ Sem alteração de alerta


┌─────────────────────────────────────────────────────────────┐
│ PASSO 3: TÉCNICO CONCLUI A ORDEM                            │
└─────────────────────────────────────────────────────────────┘

João Silva volta ao formulário de edição:
├─ Status: concluida (MUDANÇA IMPORTANTE)
├─ Solução: "Compressor: revisão completa, ajuste de válvulas"
├─ Campos adicionais aparecem:
│  ├─ Próxima preventiva: 2026-09-08 (em 3 meses)
│  ├─ Tempo de parada: 2.5 horas
│  ├─ Custo: R$ 450,00
│  └─ Peças: "Junta de borracha, óleo hidráulico"
└─ Clica: Concluir O.S.

PUT /ordens/{id}
        ↓
OrdemServicoController::update() executa:
        │
        ├─ Verifica: estava 'aberta', vai para 'concluida' ✓
        │
        ├─ DB::transaction() INICIA
        │  │
        │  ├─ Data de conclusão é registrada:
        │  │  └─ data_conclusao = 2026-06-08 16:45:30
        │  │
        │  ├─ CRIA HISTÓRICO AUTOMATICAMENTE:
        │  │  └─ HistoricoManutencao::create():
        │  │     ├─ maquina_id = COMPRESSOR-01
        │  │     ├─ tecnico_id = João Silva
        │  │     ├─ ordem_id = OS-20260608-0003
        │  │     ├─ tipo = corretiva
        │  │     ├─ descricao = "Compressor não estava ligando"
        │  │     ├─ solucao = "Compressor: revisão completa..."
        │  │     ├─ tempo_parada_horas = 2.5
        │  │     ├─ custo = 450.00
        │  │     ├─ pecas_utilizadas = "Junta de borracha, óleo..."
        │  │     ├─ data_inicio = 2026-06-08 14:30:45 (da O.S.)
        │  │     └─ data_fim = 2026-06-08 16:45:30 (agora)
        │  │
        │  ├─ CRIA PRÓXIMA PREVENTIVA (automático):
        │  │  └─ Como tipo='corretiva' e tem data_proxima:
        │  │     └─ OrdemServico::create():
        │  │        ├─ numero = OS-20260608-0004
        │  │        ├─ tipo = preventiva
        │  │        ├─ status = aberta
        │  │        ├─ prioridade = alta (herdada)
        │  │        ├─ descricao = "Manutenção preventiva..."
        │  │        ├─ maquina_id = COMPRESSOR-01
        │  │        ├─ tecnico_id = João Silva (mesmo)
        │  │        ├─ data_prevista = 2026-09-08
        │  │        └─ data_abertura = 2026-06-08 16:45:30
        │  │
        │  ├─ sincronizarStatusMaquina(COMPRESSOR-01):
        │  │  ├─ Verifica O.S. ativas:
        │  │  │  ├─ Antiga concluída (não conta)
        │  │  │  └─ Nova preventiva é aberta (conta como ativa)
        │  │  ├─ Tem O.S. ativa? SIM (a nova preventiva)
        │  │  └─ Mantém status 'em_manutencao'
        │  │
        │  └─ DB::transaction() COMMIT
        │
        ├─ notificarTecnicoAtribuido():
        │  └─ Notifica que foi atribuído à nova O.S. (preventiva)
        │
        └─ Redirect com MÚLTIPLOS alertas:
           ├─ Sucesso: "O.S. OS-20260608-0003 atualizada!"
           ├─ Alerta 1: "Próxima manutenção preventiva agendada para 08/09/2026"
           └─ Alerta 2: (nenhum de mudança de status pois continua em manutenção)

GET /historico
        ↓
Novo registro aparece:
├─ Máquina: COMPRESSOR-01
├─ Tipo: Corretiva
├─ Técnico: João Silva
├─ Início: 08/06/2026 14:30
├─ Fim: 08/06/2026 16:45
├─ Parada: 2,5h
├─ Custo: R$ 450,00
└─ O.S. Vinculada: OS-20260608-0003


┌─────────────────────────────────────────────────────────────┐
│ PASSO 4: SISTEMA SINCRONIZA MÁQUINA                         │
└─────────────────────────────────────────────────────────────┘

Estado atual:
├─ Máquina COMPRESSOR-01: em_manutencao
├─ O.S. corretiva: CONCLUÍDA
└─ O.S. preventiva: ABERTA (gerada automaticamente)

sincronizarStatusMaquina(COMPRESSOR-01) é chamado:
        │
        ├─ Busca O.S. ativa para COMPRESSOR-01
        │  └─ Encontra: preventiva, aberta, data_prevista=2026-09-08
        │  └─ É ativa? SIM (está aberta e data_prevista no futuro)
        │
        ├─ Máquina está 'em_manutencao'? SIM
        │
        └─ Mantém 'em_manutencao' (pois tem O.S. preventiva ativa)


┌─────────────────────────────────────────────────────────────┐
│ PASSO 5: PREVENTIVA É EXECUTADA E CONCLUÍDA                 │
└─────────────────────────────────────────────────────────────┘

(3 meses depois em 08/09/2026)

João Silva conclui a preventiva:
├─ Status: concluida
├─ Solução: "Limpeza de filtros, revisão de pernos"
├─ Tempo de parada: 1 hora
├─ Custo: R$ 150,00
├─ Próxima preventiva: 2026-12-08 (próximos 3 meses)
└─ Submete

PUT /ordens/{id para preventiva}
        ↓
CREATE HistoricoManutencao (segunda vez para esta máquina)
        ├─ tipo = preventiva
        ├─ tempo_parada_horas = 1.0
        └─ custo = 150.00
        ↓
CREATE OrdemServico (terceira O.S. para COMPRESSOR-01)
        ├─ numero = OS-20260908-XXXX
        ├─ tipo = preventiva
        ├─ data_prevista = 2026-12-08
        └─ status = aberta
        ↓
sincronizarStatusMaquina():
        ├─ Encontra O.S. preventiva nova
        └─ Mantém 'em_manutencao'
        
(Se não houvesse próxima preventiva ou deletássemos essa O.S.)
sincronizarStatusMaquina():
        ├─ Não encontra O.S. ativa
        └─ Muda de 'em_manutencao' → 'operacional'
        └─ Alerta: "COMPRESSOR-01 voltou a ser Operacional!"
```

---

## 📊 Análise de Dados com Histórico

### Relatório de Reincidência

```
GET /historico/maquina/{id}
        ↓
Mostra análise por máquina:

MÁQUINA: Compressor Pneumático (COMP-001)
├─ 📋 Histórico Completo (20 registros paginados)
│  └─ Cada linha: Tipo | Data | Técnico | O.S. | Parada | Custo
│
└─ 📈 REINCIDÊNCIAS (Análises de Problemas)
   └─ Problemas agrupados por MÊS:
      ├─ SETEMBRO/2025: 2 correções
      ├─ AGOSTO/2025:   1 correção
      ├─ JULHO/2025:    3 correções ← CRÍTICO! 3 por mês
      ├─ JUNHO/2025:    2 correções
      └─ MAIO/2025:     1 correção

INSIGHT:
Se máquina teve 3+ correções em um mês,
→ Possível problema crônico
→ Recomendação: revisão profunda da máquina
→ Pode indicar: necessidade de substituição
```

---

## 🚨 Tratamento de Erros e Validações

### Validações em Forma de Serviço

| Operação | Validações |
|----------|-----------|
| **Criar O.S.** | Máquina existe, Técnico ativo, Status inicial é 'aberta' |
| **Concluir O.S.** | Campos de conclusão preenchidos (tempo, custo), Cria histórico |
| **Reabrir O.S.** | ❌ NÃO PERMITIDO - já foi concluída |
| **Deletar Técnico** | ❌ Impossível se tem O.S. ou históricos |
| **Deletar Máquina** | ❌ Impossível se tem O.S. ou históricos |
| **Deletar O.S.** | Sincroniza status de máquina depois |

### Mensagens de Erro

```
❌ Erros de Validação:
"Não é possível excluir: existem O.S. vinculadas a este técnico."
"Não é possível alterar o status de uma O.S. já concluída."
"Email já está cadastrado em outro técnico/usuário."

✅ Alertas Positivos:
"COMPRESSOR-01 passou para Em Manutenção."
"COMPRESSOR-01 voltou a ser Operacional."
"Próxima manutenção preventiva agendada para 08/09/2026."
```

---

## 🔄 Transações no Banco de Dados

O sistema usa `DB::transaction()` em operações críticas:

```php
DB::transaction(function () {
    // Todas essas operações acontem atomicamente:
    // - Se uma falhar, TODAS são desfeitas
    // - Garante consistência dos dados
    
    OrdemServico::create(...);           // 1️⃣
    HistoricoManutencao::create(...);    // 2️⃣
    $maquina->update(['status' => ...]);  // 3️⃣
    
    // Se erro em qualquer uma: ROLLBACK
    // Se todas OK: COMMIT
});
```

**Quando Usadas:**
- ✅ Criar técnico (User + Tecnico)
- ✅ Criar O.S. (OrdemServico + sincronizar status)
- ✅ Concluir O.S. (atualizar O.S. + criar Histórico + próxima preventiva)
- ✅ Deletar técnico (Tecnico + User)

---

## 🛡️ Segurança

### Proteções Implementadas

| Proteção | Como Funciona |
|----------|--------------|
| **Hash de Senha** | Bcrypt (algoritmo moderno) |
| **CSRF Protection** | Token em formulários (Laravel built-in) |
| **Mass Assignment** | Fillable/Guarded em Models |
| **SQL Injection** | Eloquent ORM previne (prepared statements) |
| **Permissões** | Middleware CheckPermission em rotas |
| **Admin Master** | Acesso irrestrito (fallback de segurança) |

### Locks para Concorrência

```php
// Evita que dois processos gerem mesmo número de O.S.
$ultimo = OrdemServico::where(...)
    ->lockForUpdate()  // ← Bloqueia linhas até transação terminar
    ->orderByDesc('numero')
    ->value('numero');
```

---

## 📈 Performance

### Otimizações Implementadas

1. **Eager Loading** (N+1 prevention)
```php
// ✅ BOM: carrega relacionamentos de uma vez
$ordens = OrdemServico::with(['maquina', 'tecnico'])->get();

// ❌ RUIM: faz N queries extras
$ordens = OrdemServico::all();
foreach ($ordens as $ordem) {
    echo $ordem->maquina->modelo;  // Query extra!
}
```

2. **Índices no Banco**
- `numero_serie` (unique)
- `numero` (unique)
- `status` (pesquisas frequentes)
- `maquina_id`, `tecnico_id` (ForeignKeys)

3. **Paginação**
- Sempre pagina 15-20 registros
- Evita carregar milhares na memória

4. **Cache de Permissões**
- User armazena permissões em memória ($permissionNamesCache)
- Evita múltiplas queries de permissão

---

## 🐛 Debugging e Troubleshooting

### Se Algo Der Errado

#### 1. Erro: "Duplicate entry for numero_serie"
```
Causa: Tentou criar máquina com número_série já existente
Solução: Use número_série único
```

#### 2. Erro: "Cannot delete or update parent row"
```
Causa: Tentou deletar técnico/máquina que tem O.S. ou históricos
Solução: Delete primeiro as O.S. relacionadas
```

#### 3. Máquina não muda de status automaticamente
```
Causa: sync

cronizarStatusMaquina() não foi chamado
Solução: Verifique se:
  - OrdemServico foi criada/atualizada/deletada
  - DB::transaction() foi usado
  - Não houve erro antes de chamar sync
```

#### 4. Permissões não funcionam
```
Causa: User.permissions_overridden = true mas user_permissions vazio
Solução: 
  1. Verifique UserPermission tem registros para este usuário
  2. Ou coloque permissions_overridden = false e use role
```

### Logs

Verificar erros em:
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# SQL queries (se debug mode ativo)
DB::listen(function($query) {
    var_dump($query->sql);
});
```

---

## 📞 Suporte e Contribuição

Para dúvidas ou problemas:
1. Verifique os comentários do código
2. Execute `php artisan migrate:fresh --seed` para resetar dados
3. Verifique as tabelas do banco com `php artisan tinker`

---

## 📝 Changelog Importante

- **v1.0** - Sistema base com CRUD completo
- **Autom. de Preventivas** - Gera próxima preventiva ao concluir
- **Sincronização de Status** - Máquina muda status automaticamente
- **Histórico Automático** - Criado ao concluir O.S.
- **Permissões Granulares** - Controle fino de acesso

---

## 📌 Dicas Finais

✅ **Faça:**
- Sempre use `with()` para eager loading
- Use `DB::transaction()` em operações múltiplas
- Valide dados NO CONTROLLER antes de salvar
- Crie índices para campos frequentemente pesquisados

❌ **Não Faça:**
- Não delete usuario/tecnico/maquina com O.S. ativas
- Não mude status de O.S. concluída (crie nova)
- Não atualize permissões sem limpar cache
- Não use queries raw sem prepared statements

---

**Última Atualização:** 2026-06-08  
**Versão do Sistema:** 1.0  
**Desenvolvido em:** Laravel 11  
**Banco de Dados:** MySQL 5.7+
