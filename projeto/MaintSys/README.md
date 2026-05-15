# MaintSys — Sistema de Gerenciamento de Manutenção

Sistema web para gerenciamento de manutenção industrial, desenvolvido com Laravel 13 e Tailwind CSS 4. Permite controlar máquinas, ordens de serviço, técnicos e histórico de manutenções com sistema granular de permissões por papel (role).

---

## Tecnologias

| Camada | Tecnologia |
|--------|------------|
| Backend | PHP 8.3+, Laravel 13 |
| Frontend | Blade, Tailwind CSS 4, Vite 8 |
| Banco de dados | MySQL (produção) / SQLite (dev) |
| Testes | PHPUnit 12.5 |
| Estilo de código | Laravel Pint |

---

## Requisitos

- PHP 8.3+
- Composer
- Node.js 18+
- MySQL 8+

---

## Instalação

```bash
# 1. Clonar o repositório
git clone <url> maintsys
cd maintsys

# 2. Setup completo (instala dependências, configura .env, roda migrations)
composer run setup

# 3. Configurar o banco no .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=maintsys
DB_USERNAME=root
DB_PASSWORD=

# 4. Rodar seeders para dados iniciais
php artisan db:seed
```

---

## Desenvolvimento

```bash
# Iniciar todos os serviços (Laravel + Queue + Logs + Vite)
composer run dev

# Ou individualmente:
php artisan serve        # Servidor PHP
npm run dev              # Vite (hot reload)
```

Acesse: `http://localhost:8000`

**Usuários iniciais criados pelo seeder:**

| Email | Senha | Role |
|-------|-------|------|
| `admin@maintsys.com` | `password` | admin_master |
| `gerente@maintsys.com` | `password` | admin |
| `tecnico@maintsys.com` | `password` | usuario |

---

## Estrutura do projeto

```
app/
├── Http/
│   ├── Controllers/        # DashboardController, MaquinaController, OrdemServicoController,
│   │                       # TecnicoController, HistoricoController, AccessController,
│   │                       # UserManagementController, ProfileController
│   ├── Middleware/         # CheckPermission, CheckAdmin, CheckAdminAccess, CheckMaster
│   └── Requests/           # Form Requests com validação
├── Models/                 # User, Maquina, Tecnico, OrdemServico, HistoricoManutencao,
│                           # Permission, RolePermission, UserPermission
└── Providers/
database/
├── migrations/             # 12 migrations
├── seeders/                # UserSeeder, PermissionSeeder, DatabaseSeeder
└── factories/              # 5 factories para testes
resources/
├── css/app.css             # Tailwind + variáveis do tema SENAI
├── js/app.js
└── views/                  # Blade templates por módulo
routes/
├── web.php                 # Rotas principais
└── auth.php                # Rotas de autenticação
```

---

## Módulos

### Dashboard
Painel com estatísticas de máquinas, técnicos e ordens. Exibe alertas de parada crítica e ações rápidas. As seções visíveis dependem das permissões do usuário logado.

### Máquinas
Cadastro de equipamentos com número de série, modelo, fabricante, localização e status.

**Status possíveis:** `operacional`, `em_manutencao`, `parada_critica`, `inativa`

O status é atualizado automaticamente ao abrir/concluir ordens de serviço.

### Ordens de Serviço
Rastreamento de manutenções com número único (`OS-YYYYMMDD-####`), tipo (preventiva/corretiva), status, prioridade e vínculo com máquina e técnico.

**Automações:**
- Abrir O.S. → máquina muda para `em_manutencao`
- Concluir O.S. → registra no histórico automaticamente
- Concluir O.S. preventiva com data fornecida → cria a próxima preventiva automaticamente
- Concluir última O.S. aberta da máquina → máquina volta a `operacional`

### Técnicos
Cadastro de técnicos com matrícula, especialidade e contato. Não é possível excluir técnicos com ordens vinculadas.

### Histórico de Manutenções
Registro de todas as manutenções realizadas com peças utilizadas, tempo de parada, custo e observações. Criado automaticamente ao concluir uma O.S., ou manualmente.

### Gestão de Usuários
Criação e gerenciamento de usuários do sistema (apenas admin/admin_master). Suporta atribuição de permissões individuais que sobrescrevem as permissões de papel.

### Gerenciamento de Acesso
Painel para configurar permissões por papel (role) e por usuário individualmente.

---

## Sistema de Permissões

O sistema usa três roles: `admin_master`, `admin`, `usuario`.

As permissões são verificadas em dois níveis:
1. Permissões atribuídas ao papel (role) do usuário
2. Permissões individuais do usuário (sobrescrevem as do papel)

**Permissões disponíveis:**

| Módulo | Permissões |
|--------|------------|
| Máquinas | `maquinas.visualizar`, `maquinas.criar`, `maquinas.editar`, `maquinas.deletar` |
| Técnicos | `tecnicos.visualizar`, `tecnicos.criar`, `tecnicos.editar`, `tecnicos.deletar` |
| Ordens | `ordens.visualizar`, `ordens.criar`, `ordens.editar`, `ordens.deletar` |
| Histórico | `historico.visualizar`, `historico.criar`, `historico.deletar` |
| Dashboard | `dashboard.maquinas`, `dashboard.tecnicos`, `dashboard.ordens`, `dashboard.alertas`, `dashboard.historico` |

**Middlewares disponíveis nas rotas:**

```php
->middleware('perm:maquinas.criar')   // Permissão específica
->middleware('admin')                  // admin ou admin_master
->middleware('admin_access')           // Acesso ao painel de administração
->middleware('master')                 // Apenas admin_master
```

---

## Banco de Dados

```
users                   → usuários do sistema
maquinas                → inventário de equipamentos
tecnicos                → técnicos de manutenção
ordens_servico          → ordens de serviço (FK: maquinas, tecnicos)
historico_manutencoes   → histórico (FK: maquinas, tecnicos, ordens_servico)
permissions             → permissões disponíveis
role_permissions        → permissões por papel
user_permissions        → permissões individuais por usuário
sessions                → sessões de usuário
password_reset_tokens   → tokens de redefinição de senha
```

---

## Testes

```bash
# Executar todos os testes
composer run test

# Ou diretamente
php artisan test
```

---

## Comandos úteis

```bash
php artisan migrate:fresh --seed   # Resetar banco e popular com dados de exemplo
php artisan tinker                 # REPL interativo
php artisan pint                   # Corrigir estilo de código
npm run build                      # Build para produção
```

---

## Tema

A interface usa o tema SENAI com:
- Cor primária: `#E3000F` (vermelho SENAI)
- Fontes: Barlow (texto), Barlow Condensed (títulos), Share Tech Mono (monospace)
- Suporte a modo escuro via `data-theme="dark"` no elemento raiz
