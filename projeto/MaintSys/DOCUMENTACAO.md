# DOCUMENTACAO — MaintSys

Referência completa do projeto: localização por arquivo+linha, arquitetura, modelo de dados e guias de debug.

> **Atalhos de caminho:**
> - `CTR/` → `app/Http/Controllers/`
> - `MDL/` → `app/Models/`
> - `VW/`  → `resources/views/`
> - `MDW/` → `app/Http/Middleware/`

---

## ARQUITETURA

```
projeto/
├── app/
│   ├── Http/
│   │   ├── Controllers/            ← Lógica de negócio (CRUD, automações)
│   │   ├── Middleware/             ← Autenticação e verificação de permissões
│   │   └── Requests/               ← Validações de formulário (Form Requests)
│   ├── Models/                     ← Acesso a dados via Eloquent ORM
│   └── Notifications/              ← Notificações por e-mail (queue)
├── resources/
│   ├── views/                      ← Templates Blade (HTML + lógica de exibição)
│   ├── css/                        ← Tailwind CSS (estilização)
│   └── js/                         ← JavaScript (máscaras, toggles, AJAX)
├── database/
│   ├── migrations/                 ← Criação e alteração de tabelas do banco
│   └── seeders/                    ← Dados iniciais (permissões, admin master)
└── routes/
    ├── web.php                     ← Rotas principais (CRUD de todas as entidades)
    └── auth.php                    ← Rotas de login, logout, reset de senha
```

### Fluxo de uma requisição

```
Browser → routes/web.php → Middleware (auth + perm) → Controller → Model → MySQL
                                                                         ↓
                                                              View Blade → HTML → Browser
```

---

## MODELO DE DADOS

### Tabelas e campos

| Tabela | Model | Campos principais |
|--------|-------|-------------------|
| `users` | `MDL/User.php` | id, name, email, password, role, permissions_overridden |
| `tecnicos` | `MDL/Tecnico.php` | id, user_id, nome, matricula, email, especialidade, telefone, ativo |
| `maquinas` | `MDL/Maquina.php` | id, numero_serie, modelo, fabricante, localizacao, status, descricao, data_cadastro |
| `ordens_servico` | `MDL/OrdemServico.php` | id, numero, tipo, status, prioridade, maquina_id, tecnico_id, data_abertura, data_prevista, data_conclusao, solucao, custo, tempo_parada_horas, pecas_utilizadas |
| `historico_manutencoes` | `MDL/HistoricoManutencao.php` | id, maquina_id, tecnico_id, ordem_id, tipo, descricao, solucao, tempo_parada_horas, custo, pecas_utilizadas, data_inicio, data_fim, observacoes |
| `permissions` | `MDL/Permission.php` | id, name, descricao, modulo |
| `role_permissions` | `MDL/RolePermission.php` | id, role, permission_id |
| `user_permissions` | `MDL/UserPermission.php` | id, user_id, permission_id |

### Enums (valores válidos por campo)

| Tabela | Campo | Valores possíveis |
|--------|-------|-------------------|
| `maquinas` | `status` | `operacional`, `em_manutencao`, `parada_critica`, `inativa` |
| `ordens_servico` | `status` | `aberta`, `em_andamento`, `concluida`, `cancelada` |
| `ordens_servico` | `tipo` | `preventiva`, `corretiva` |
| `ordens_servico` | `prioridade` | `baixa`, `media`, `alta`, `critica` |
| `historico_manutencoes` | `tipo` | `preventiva`, `corretiva` |
| `users` | `role` | `admin`, `usuario`, `admin_master` |

### Relacionamentos

```
User (1) ──── (1) Tecnico
User (1) ──── (*) UserPermission

Tecnico (1) ──── (*) OrdemServico
Tecnico (1) ──── (*) HistoricoManutencao

Maquina (1) ──── (*) OrdemServico
Maquina (1) ──── (*) HistoricoManutencao

OrdemServico (*) ──── (1) Tecnico
OrdemServico (*) ──── (1) Maquina
OrdemServico (1) ──── (1) HistoricoManutencao

Permission (1) ──── (*) RolePermission
Permission (1) ──── (*) UserPermission
```

---

## DASHBOARD

### Cards e Estatísticas

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Cards de stats (HTML) | `VW/dashboard.blade.php` | 21–61 | 8 cards: OS abertas, em andamento... |
| Dados que alimentam os cards | `CTR/DashboardController.php` | 32–62 | Array `$stats` com todos os counts |
| Alerta de máquinas críticas | `VW/dashboard.blade.php` | 63–84 | Bloco `$alertas`, badge vermelho |
| Dados dos alertas (query) | `CTR/DashboardController.php` | 64–66 | `Maquina::paradaCritica()->get()` |
| Tabela de O.S. ativas | `VW/dashboard.blade.php` | 89–165 | Linhas da tabela, badge de status |
| Últimas manutenções (histórico) | `VW/dashboard.blade.php` | 172–219 | Tabela de HistoricoManutencao |
| Ações rápidas (botões) | `VW/dashboard.blade.php` | 223–247 | Botões: Nova O.S., Nova Máquina... |
| Query O.S. recentes | `CTR/DashboardController.php` | 68–73 | `OrdemServico::with()->latest()` |
| Query históricos recentes | `CTR/DashboardController.php` | 74–78 | `HistoricoManutencao::with()->latest()` |

---

## TECNICOS

### Listagem (tecnicos/index)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Página inteira de listagem | `VW/tecnicos/index.blade.php` | 1–116 | Toda a view |
| Botão "+ Novo Técnico" | `VW/tecnicos/index.blade.php` | 30–32 | Visível só com `tecnicos.criar` |
| Cabeçalho da página | `VW/tecnicos/index.blade.php` | 25–33 | Título + botão de criação |
| Colunas do cabeçalho da tabela | `VW/tecnicos/index.blade.php` | 38–49 | #, Matrícula, Nome, Especialidade... |
| Loop de linhas (cada técnico) | `VW/tecnicos/index.blade.php` | 52–110 | `@forelse($tecnicos as $t)` |
| Coluna Matrícula | `VW/tecnicos/index.blade.php` | 55 | `$t->matricula` |
| Coluna Nome | `VW/tecnicos/index.blade.php` | 56 | `$t->nome` |
| Coluna Especialidade | `VW/tecnicos/index.blade.php` | 57 | `$t->especialidade ?? '—'` |
| Coluna Telefone (formatação PHP) | `VW/tecnicos/index.blade.php` | 61–75 | Máscara `(99) 99999-9999` inline |
| Coluna O.S. (contagem) | `VW/tecnicos/index.blade.php` | 78 | `$t->ordens_count` (withCount) |
| Badge Ativo / Inativo | `VW/tecnicos/index.blade.php` | 81–85 | `badge-green` ou `badge-gray` |
| Botão Editar | `VW/tecnicos/index.blade.php` | 91–93 | Permissão: `tecnicos.editar` |
| Botão Deletar | `VW/tecnicos/index.blade.php` | 94–100 | Permissão: `tecnicos.deletar` |
| Paginação | `VW/tecnicos/index.blade.php` | 113 | `$tecnicos->links()` |
| Controller que monta a listagem | `CTR/TecnicoController.php` | 32–44 | Paginação 15 itens, withCount |

### Criar Técnico (tecnicos/create)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Formulário inteiro de criar | `VW/tecnicos/create.blade.php` | 38–105 | `POST /tecnicos` |
| Campo Nome Completo | `VW/tecnicos/create.blade.php` | 41–47 | `name="nome"`, required |
| Campo Matrícula | `VW/tecnicos/create.blade.php` | 48–53 | `name="matricula"`, required |
| Campo E-mail | `VW/tecnicos/create.blade.php` | 57–62 | `name="email"`, type email |
| Campo Especialidade | `VW/tecnicos/create.blade.php` | 63–67 | `name="especialidade"`, opcional |
| Campo Senha | `VW/tecnicos/create.blade.php` | 71–76 | `name="password"`, min 8 |
| Campo Confirmar Senha | `VW/tecnicos/create.blade.php` | 77–81 | `name="password_confirmation"` |
| Campo Telefone | `VW/tecnicos/create.blade.php` | 85–89 | `name="telefone"`, máscara JS |
| Checkbox Técnico Ativo | `VW/tecnicos/create.blade.php` | 90–98 | `name="ativo"`, padrão marcado |
| Botões Cadastrar / Cancelar | `VW/tecnicos/create.blade.php` | 101–104 | Submit + link de volta |
| Script JS máscara de telefone | `VW/tecnicos/create.blade.php` | 108–126 | Formata `(99) 99999-9999` |
| Validações na criação | `CTR/TecnicoController.php` | 63–75 | `$request->validate([...])` |
| Criar User + Tecnico atomicamente | `CTR/TecnicoController.php` | 77–88 | `DB::transaction()` |

### Editar Técnico (tecnicos/edit)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Formulário de edição | `VW/tecnicos/edit.blade.php` | 1–116 | `PUT /tecnicos/{id}` |
| Seção de redefinição de senha | `VW/tecnicos/edit.blade.php` | 54–69 | Campos Nova Senha + Confirmação |
| Script JS máscara de telefone | `VW/tecnicos/edit.blade.php` | 96–113 | Mesmo script do create |
| Lógica de atualização | `CTR/TecnicoController.php` | 104–162 | Atualiza User e Tecnico |

### Ver Técnico (tecnicos/show)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Card de informações do técnico | `VW/tecnicos/show.blade.php` | 26–43 | Nome, matrícula, e-mail, telefone |
| Tabela de O.S. do técnico | `VW/tecnicos/show.blade.php` | 45–80 | Lista de ordens com status |
| Deletar técnico | `CTR/TecnicoController.php` | 164–191 | Valida se tem O.S. ativa antes |

### Model Técnico

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Campos `$fillable` | `MDL/Tecnico.php` | 14–23 | Todos os campos aceitos |
| Relacionamento com User | `MDL/Tecnico.php` | 53–56 | `belongsTo(User::class, 'user_id')` |
| Relacionamento com OrdemServico | `MDL/Tecnico.php` | 58–61 | `hasMany(OrdemServico::class)` |
| Relacionamento com HistoricoManutencao | `MDL/Tecnico.php` | 63–66 | `hasMany(HistoricoManutencao::class)` |

---

## MAQUINAS

### Listagem (maquinas/index)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Botão "+ Nova Máquina" | `VW/maquinas/index.blade.php` | 16–18 | Permissão: `maquinas.criar` |
| Stats grid (cards no topo) | `VW/maquinas/index.blade.php` | 21–26 | Total, operacionais, em manutenção... |
| Tabela de listagem | `VW/maquinas/index.blade.php` | 28–83 | Com badge de status |
| Controller que monta stats | `CTR/MaquinaController.php` | 32–47 | Array `$stats` |

### Criar Máquina (maquinas/create)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Formulário de criação | `VW/maquinas/create.blade.php` | 1–83 | `POST /maquinas` |
| Dropdown de Status | `VW/maquinas/create.blade.php` | 60–67 | Valores: operacional, em_manutencao.. |
| Lógica de criação | `CTR/MaquinaController.php` | 49–68 | Validações + alerta se status crítico |

### Editar Máquina (maquinas/edit)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Formulário de edição | `VW/maquinas/edit.blade.php` | 1–83 | `PUT /maquinas/{id}` |
| Dropdown de Status | `VW/maquinas/edit.blade.php` | 62–67 | Pré-selecionado com valor atual |
| Detecta mudança de status | `CTR/MaquinaController.php` | 81–105 | Alerta contextual por tipo de status |

### Ver Máquina (maquinas/show)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Botão "Histórico" | `VW/maquinas/show.blade.php` | 18–20 | Leva para `historico.por-maquina` |
| Botão "+ Nova O.S." | `VW/maquinas/show.blade.php` | 21–23 | Leva para `ordens.create` |
| Botão "Editar" | `VW/maquinas/show.blade.php` | 24–26 | Permissão: `maquinas.editar` |
| Deletar máquina | `CTR/MaquinaController.php` | 107–124 | Bloqueia se tem O.S. vinculada |

### Model Máquina

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Scope `operacional` | `MDL/Maquina.php` | 58–61 | `where('status', 'operacional')` |
| Scope `emManutencao` | `MDL/Maquina.php` | 63–66 | `where('status', 'em_manutencao')` |
| Scope `paradaCritica` | `MDL/Maquina.php` | 68–71 | `where('status', 'parada_critica')` |
| Acessor `status_label` | `MDL/Maquina.php` | 73–84 | Texto legível: "Operacional"... |
| Acessor `status_color` | `MDL/Maquina.php` | 86–97 | Cor: 'green', 'yellow', 'red'... |
| Status automático (sync) | `CTR/OrdemServicoController.php` | 367–407 | `sincronizarStatusMaquina()` |

---

## ORDENS DE SERVICO

### Listagem (ordens/index)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Botão "Imprimir" | `VW/ordens/index.blade.php` | 16–18 | `window.print()` |
| Botão "Exportar CSV" (todas) | `VW/ordens/index.blade.php` | 19–22 | Link para `ordens.exportar` |
| Botão "+ Nova O.S." | `VW/ordens/index.blade.php` | 23–25 | Permissão: `ordens.criar` |
| Stats (cards): totais por status | `VW/ordens/index.blade.php` | 29–46 | Abertas, Em Andamento, Concluídas... |
| Filtros (máquina, tipo, status...) | `VW/ordens/index.blade.php` | 48–73 | Form GET com selects |
| Tabela de O.S. | `VW/ordens/index.blade.php` | 75–122 | Loop com badges de prioridade/status |
| Controller listagem + filtros | `CTR/OrdemServicoController.php` | 47–70 | Array `$stats`, filtros dinâmicos |

### Criar O.S. (ordens/create)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Formulário de criação | `VW/ordens/create.blade.php` | 1–94 | `POST /ordens` |
| Dropdown Máquina | `VW/ordens/create.blade.php` | 26–36 | `name="maquina_id"` |
| Dropdown Técnico | `VW/ordens/create.blade.php` | 38–49 | `name="tecnico_id"` |
| Dropdown Tipo (Preventiva/Corretiva) | `VW/ordens/create.blade.php` | 52–60 | `name="tipo"` |
| Dropdown Prioridade | `VW/ordens/create.blade.php` | 62–70 | `name="prioridade"` (baixa→crítica) |
| Campo Data Prevista | `VW/ordens/create.blade.php` | 81–85 | `name="data_prevista"`, type date |
| Lógica de criação (gera número) | `CTR/OrdemServicoController.php` | 85–131 | Gera número, sync máquina, notifica |
| Geração do número único | `MDL/OrdemServico.php` | 111–130 | `gerarNumero()` com `lockForUpdate` |

### Editar/Concluir O.S. (ordens/edit)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Formulário de edição | `VW/ordens/edit.blade.php` | 21–138 | `PUT /ordens/{id}` |
| Dropdown Máquina (editável) | `VW/ordens/edit.blade.php` | 25–35 | Pré-selecionado com `$ordem->maquina_id` |
| Dropdown Técnico (editável) | `VW/ordens/edit.blade.php` | 36–45 | Pré-selecionado com `$ordem->tecnico_id` |
| Dropdown Status | `VW/ordens/edit.blade.php` | 69–76 | Aberta / Em Andamento / Concluída... |
| Campo Solução Aplicada | `VW/ordens/edit.blade.php` | 89–93 | Textarea `name="solucao"` |
| Campos de Conclusão (aparecem ao selecionar "Concluída") | `VW/ordens/edit.blade.php` | 95–119 | Tempo parada, Custo, Peças |
| Campo Tempo de Parada (horas) | `VW/ordens/edit.blade.php` | 101–106 | `name="tempo_parada_horas"`, step 0.5 |
| Campo Custo Total (R$) | `VW/ordens/edit.blade.php` | 107–112 | `name="custo"`, step 0.01 |
| Campo Próxima Preventiva (automático) | `VW/ordens/edit.blade.php` | 121–132 | `name="proxima_preventiva"`, date |
| JS: mostra/oculta campos de conclusão | `VW/ordens/edit.blade.php` | 141–160 | `toggleCampos()` no `change` do status |
| Lógica de atualização (CHAVE) | `CTR/OrdemServicoController.php` | 159–269 | Tudo que acontece ao salvar edição |
| Detecção de conclusão | `CTR/OrdemServicoController.php` | 175–178 | `$concluindoAgora` = mudou para concluida |
| Criação automática do Histórico | `CTR/OrdemServicoController.php` | 183–200 | `HistoricoManutencao::create()` |
| Criação automática próxima preventiva | `CTR/OrdemServicoController.php` | 217–232 | Nova O.S. gerada automaticamente |
| Sync status máquina (update) | `CTR/OrdemServicoController.php` | 207–215 | Chama `sincronizarStatusMaquina()` |

### Ver O.S. (ordens/show)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Botão Imprimir O.S. | `VW/ordens/show.blade.php` | 18–20 | `window.print()` |
| Botão Exportar CSV individual | `VW/ordens/show.blade.php` | 21–23 | Link para `ordens.exportar-single` |
| Botão Editar O.S. | `VW/ordens/show.blade.php` | 24–26 | Permissão: `ordens.editar` |
| Badges de status, prioridade, tipo | `VW/ordens/show.blade.php` | 38–42 | Cores dinâmicas via `match()` |
| Dados da O.S. (painel esquerdo) | `VW/ordens/show.blade.php` | 44–58 | Número, Máquina, Local, Técnico... |
| Seção Solução Aplicada | `VW/ordens/show.blade.php` | 71–78 | Só aparece se `$ordem->solucao` |
| Seção Registro no Histórico | `VW/ordens/show.blade.php` | 80–108 | Só aparece se `$ordem->historico` |
| Exportar O.S. individual (CSV) | `CTR/OrdemServicoController.php` | 271–313 | CSV detalhado da O.S. |
| Exportar todas as O.S. (CSV) | `CTR/OrdemServicoController.php` | 315–343 | CSV resumido com filtros |
| Deletar O.S. | `CTR/OrdemServicoController.php` | 345–365 | Deleta + sync máquina |

### Automações (lógica interna)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Sincronizar status da máquina | `CTR/OrdemServicoController.php` | 367–407 | CRITICO — chamado em store/update/destroy |
| Notificar técnico por email | `CTR/OrdemServicoController.php` | 409–427 | `notificarTecnicoAtribuido()` |

### Model OrdemServico

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Acessor `tipo_label` | `MDL/OrdemServico.php` | 56–61 | "Preventiva" / "Corretiva" |
| Acessor `status_label` | `MDL/OrdemServico.php` | 63–73 | "Aberta" / "Em Andamento"... |
| Acessor `prioridade_label` | `MDL/OrdemServico.php` | 75–84 | "Baixa" / "Média" / "Alta"... |
| Gerar número único (OS-YYYYMMDD-NNNN) | `MDL/OrdemServico.php` | 111–130 | `lockForUpdate()` evita duplicatas |

---

## HISTORICO

### Listagem com Filtros (historico/index)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Botão Imprimir | `VW/historico/index.blade.php` | 17–19 | `window.print()` |
| Botão Exportar CSV (com filtros) | `VW/historico/index.blade.php` | 20–23 | Passa query params para `historico.exportar` |
| Filtro Máquina (select) | `VW/historico/index.blade.php` | 33–38 | `name="maquina_id"` |
| Filtro Tipo (select) | `VW/historico/index.blade.php` | 42–46 | `name="tipo"` (preventiva/corretiva) |
| Filtro Técnico (select) | `VW/historico/index.blade.php` | 50–55 | `name="tecnico_id"` |
| Filtro Data Início | `VW/historico/index.blade.php` | 59 | `name="data_inicio"`, type date |
| Filtro Data Fim | `VW/historico/index.blade.php` | 63 | `name="data_fim"`, type date |
| Botões Filtrar / Limpar | `VW/historico/index.blade.php` | 65–66 | Submit + link sem params |
| Tabela de resultados | `VW/historico/index.blade.php` | 71–138 | Loop com badges tipo |
| Badge Tipo (corretiva/preventiva) | `VW/historico/index.blade.php` | 92–95 | `badge-orange` ou `badge-blue` |
| Link para O.S. vinculada | `VW/historico/index.blade.php` | 99–104 | Link se `$h->ordem` existe |
| Botão Deletar | `VW/historico/index.blade.php` | 118–124 | Permissão: `historico.deletar` |
| Paginação | `VW/historico/index.blade.php` | 137 | `$historicos->links()` |
| Controller listagem + filtros | `CTR/HistoricoController.php` | 37–57 | Filtros dinâmicos, paginate(20) |

### Ver Registro (historico/show)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Painel com dados (esquerda) | `VW/historico/show.blade.php` | 22–43 | Máquina, Técnico, O.S., datas, custo |
| Seção Descrição | `VW/historico/show.blade.php` | 47–50 | Texto livre |
| Seção Solução Aplicada | `VW/historico/show.blade.php` | 52–57 | Condicional |
| Seção Peças Utilizadas | `VW/historico/show.blade.php` | 59–64 | Condicional |
| Seção Observações | `VW/historico/show.blade.php` | 66–71 | Condicional |

### Histórico por Máquina (historico/por-maquina)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Botão Ver Máquina | `VW/historico/por-maquina.blade.php` | 21 | Link para `maquinas.show` |
| Botão + Nova O.S. | `VW/historico/por-maquina.blade.php` | 22 | Link para `ordens.create?maquina_id=` |
| Bloco de Reincidências | `VW/historico/por-maquina.blade.php` | 27–46 | Aparece se houver corretivas |
| Cards de reincidência por mês | `VW/historico/por-maquina.blade.php` | 33–44 | Vermelho se >= 3 corretivas no mês |
| Tabela de histórico da máquina | `VW/historico/por-maquina.blade.php` | 49–94 | Todas as manutenções |
| Controller: query reincidências | `CTR/HistoricoController.php` | 93–97 | GroupBy mês/ano, só corretivas |
| Exportar histórico (CSV) | `CTR/HistoricoController.php` | 112–142 | Mesmos filtros, sem paginação |

---

## USUARIOS

### Listagem (usuarios/index)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Botão Gerenciar Acesso | `VW/usuarios/index.blade.php` | 17–19 | Permissão: `acesso.gerenciar` |
| Botão "+ Novo Usuário" | `VW/usuarios/index.blade.php` | 20–22 | Permissão: `usuarios.criar` |
| Stats (Total, Admins, Usuários) | `VW/usuarios/index.blade.php` | 26–39 | 3 cards de contagem |
| Badge role (Admin/Usuário) | `VW/usuarios/index.blade.php` | 58–60 | `badge-orange` (admin) / `badge-blue` |
| Badge "Individual" | `VW/usuarios/index.blade.php` | 61–63 | Aparece se `permissions_overridden` |
| Botão Editar | `VW/usuarios/index.blade.php` | 68–70 | Permissão: `usuarios.editar` |
| Botão Perms (ver permissões) | `VW/usuarios/index.blade.php` | 71–73 | Permissão: `usuarios.permissoes` |
| Botão Del | `VW/usuarios/index.blade.php` | 74–80 | Permissão: `usuarios.deletar` |
| Controller listagem | `CTR/UserManagementController.php` | 15–21 | Exclui `admin_master` da listagem |

### Criar Usuário (usuarios/create)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Campo Nome Completo | `VW/usuarios/create.blade.php` | 36–55 | `name="name"`, required |
| Campo E-mail | `VW/usuarios/create.blade.php` | 57–76 | `name="email"`, unique |
| Campo Senha | `VW/usuarios/create.blade.php` | 78–96 | `name="password"`, mín 6 |
| Dropdown Nível de Acesso | `VW/usuarios/create.blade.php` | 98–118 | `name="role"` (usuario/admin) |
| Sidebar: descrição dos níveis | `VW/usuarios/create.blade.php` | 139–168 | Info sobre Admin vs Usuário |
| Validações ao criar | `CTR/UserManagementController.php` | 33–46 | name, email unique, password, role |

### Editar Usuário (usuarios/edit)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Campo Nome | `VW/usuarios/edit.blade.php` | 34–51 | `name="name"` |
| Campo Senha (opcional) | `VW/usuarios/edit.blade.php` | 73–90 | `name="password"`, deixe em branco |
| Dropdown Nível de Acesso | `VW/usuarios/edit.blade.php` | 92–111 | `name="role"` |
| Link "Ver Permissões" (sidebar) | `VW/usuarios/edit.blade.php` | 158–163 | Botão verde para `usuarios.permissions` |
| Lógica de edição (transação) | `CTR/UserManagementController.php` | 65–98 | Limpa permissões se role mudou |

### Ver Permissões (usuarios/permissions)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Grid de permissões por módulo | `VW/usuarios/permissions.blade.php` | 36–85 | Ícone verde (✓) ou vermelho (✗) |
| Checkbox visual (ativo/bloqueado) | `VW/usuarios/permissions.blade.php` | 63–82 | Read-only, só exibe o estado |
| Box informativo "Como Funciona" | `VW/usuarios/permissions.blade.php` | 88–110 | Explica herança de role |
| Controller que carrega permissões | `CTR/UserManagementController.php` | 112–126 | `showPermissions()`, exclui master |

---

## ACESSO E PERMISSOES

### Gerenciar Permissões por Role (acesso/index)

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Grid Admin vs Usuário | `VW/acesso/index.blade.php` | 11–69 | Dois formulários lado a lado |
| Checkboxes de permissões por módulo | `VW/acesso/index.blade.php` | 40–55 | `name="permissions[]"` |
| Botão "Salvar Permissões" | `VW/acesso/index.blade.php` | 59–65 | Chama `salvarPermissoes(role)` |
| JS: salvar via fetch (AJAX) | `VW/acesso/index.blade.php` | 88–113 | `POST /acesso/role/{role}` |
| Controller: atualiza role | `CTR/AccessController.php` | 92–117 | `updateRole()` — DB::transaction |
| Controller: página principal | `CTR/AccessController.php` | 14–52 | Carrega permissões de todos os users |

### Atualizar Permissões de Usuário Individual

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Controller: atualizar permissões | `CTR/AccessController.php` | 54–90 | `updateUserPermissions()` — AJAX |
| Herdar do role vs individual | `CTR/AccessController.php` | 66–82 | `inherit=true` reseta para padrão |
| Alterar role do usuário | `CTR/AccessController.php` | 130–155 | `updateUsuario()` — limpa perms |
| Flag `permissions_overridden` | `MDL/User.php` | 20 | `true` = usa permissões individuais |
| Limpar cache de permissões | `MDL/User.php` | 113–115 | `clearPermissionCache()` |

---

## AUTENTICACAO

### Login

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Página de login (HTML) | `VW/auth/login.blade.php` | 1–59 | Formulário `POST /login` |
| Campo E-mail do login | `VW/auth/login.blade.php` | 18–27 | `name="email"`, autofocus |
| Campo Senha do login | `VW/auth/login.blade.php` | 29–37 | `name="password"` |
| Checkbox "Lembrar-me" | `VW/auth/login.blade.php` | 40–42 | `name="remember"` |
| Link "Esqueceu a senha?" | `VW/auth/login.blade.php` | 44–46 | Link para `password.request` |
| Botão "Entrar no sistema" | `VW/auth/login.blade.php` | 49 | `class="btn-submit"` |
| Rota POST login | `routes/auth.php` | 21 | `AuthenticatedSessionController@store` |

### Redefinição de Senha

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Página "Esqueceu a senha" | `VW/auth/forgot-password.blade.php` | 1–25 | Envia email com link |
| Rota GET forgot-password | `routes/auth.php` | 23 | `PasswordResetLinkController@create` |
| Rota POST (envia email) | `routes/auth.php` | 26–27 | `password.email` |
| Rota GET reset (token) | `routes/auth.php` | 29–31 | `password.reset` |
| Rota POST reset (salva nova senha) | `routes/auth.php` | 33 | `password.store` |
| Auto-registro desabilitado | `routes/auth.php` | 14–16 | Comentado intencionalmente |
| Logout | `routes/auth.php` | 55–57 | `POST /logout` |

---

## ROTAS COMPLETAS

| Método | URL | Controller@Método | Permissão |
|--------|-----|-------------------|-----------|
| GET | `/` | Redireciona | — |
| GET | `/dashboard` | `DashboardController@index` | auth+verified |
| GET | `/maquinas` | `MaquinaController@index` | maquinas.visualizar |
| GET | `/maquinas/create` | `MaquinaController@create` | maquinas.criar |
| POST | `/maquinas` | `MaquinaController@store` | maquinas.criar |
| GET | `/maquinas/{id}` | `MaquinaController@show` | maquinas.visualizar |
| GET | `/maquinas/{id}/edit` | `MaquinaController@edit` | maquinas.editar |
| PUT | `/maquinas/{id}` | `MaquinaController@update` | maquinas.editar |
| DELETE | `/maquinas/{id}` | `MaquinaController@destroy` | maquinas.deletar |
| GET | `/tecnicos` | `TecnicoController@index` | tecnicos.visualizar |
| GET | `/tecnicos/create` | `TecnicoController@create` | tecnicos.criar |
| POST | `/tecnicos` | `TecnicoController@store` | tecnicos.criar |
| GET | `/tecnicos/{id}` | `TecnicoController@show` | tecnicos.visualizar |
| GET | `/tecnicos/{id}/edit` | `TecnicoController@edit` | tecnicos.editar |
| PUT | `/tecnicos/{id}` | `TecnicoController@update` | tecnicos.editar |
| DELETE | `/tecnicos/{id}` | `TecnicoController@destroy` | tecnicos.deletar |
| GET | `/ordens` | `OrdemServicoController@index` | ordens.visualizar |
| GET | `/ordens/create` | `OrdemServicoController@create` | ordens.criar |
| POST | `/ordens` | `OrdemServicoController@store` | ordens.criar |
| GET | `/ordens/exportar` | `OrdemServicoController@exportar` | ordens.visualizar |
| GET | `/ordens/{id}` | `OrdemServicoController@show` | ordens.visualizar |
| GET | `/ordens/{id}/exportar` | `OrdemServicoController@exportarSingle` | ordens.visualizar |
| GET | `/ordens/{id}/edit` | `OrdemServicoController@edit` | ordens.editar |
| PUT | `/ordens/{id}` | `OrdemServicoController@update` | ordens.editar |
| DELETE | `/ordens/{id}` | `OrdemServicoController@destroy` | ordens.deletar |
| GET | `/historico` | `HistoricoController@index` | historico.visualizar |
| GET | `/historico/exportar` | `HistoricoController@exportar` | historico.visualizar |
| GET | `/historico/maquina/{id}` | `HistoricoController@porMaquina` | historico.visualizar |
| GET | `/historico/{id}` | `HistoricoController@show` | historico.visualizar |
| POST | `/historico` | `HistoricoController@store` | historico.criar |
| DELETE | `/historico/{id}` | `HistoricoController@destroy` | historico.deletar |
| GET/PATCH | `/profile` | `ProfileController@edit/update` | auth |
| DELETE | `/profile` | `ProfileController@destroy` | auth |
| GET | `/acesso` | `AccessController@index` | acesso.gerenciar |
| POST | `/acesso/role/{role}` | `AccessController@updateRole` | acesso.gerenciar |
| POST | `/acesso/usuario/{user}/permissoes` | `AccessController@updateUserPermissions` | acesso.gerenciar |
| GET | `/acesso/usuarios` | `AccessController@usuarios` | acesso.gerenciar |
| PATCH | `/acesso/usuario/{user}` | `AccessController@updateUsuario` | acesso.gerenciar |
| GET | `/usuarios` | `UserManagementController@index` | usuarios.visualizar |
| GET | `/usuarios/criar` | `UserManagementController@create` | usuarios.criar |
| POST | `/usuarios` | `UserManagementController@store` | usuarios.criar |
| GET | `/usuarios/{user}/editar` | `UserManagementController@edit` | usuarios.editar |
| PUT | `/usuarios/{user}` | `UserManagementController@update` | usuarios.editar |
| DELETE | `/usuarios/{user}` | `UserManagementController@destroy` | usuarios.deletar |
| GET | `/usuarios/{user}/permissoes` | `UserManagementController@showPermissions` | usuarios.permissoes |
| GET | `/login` | `AuthenticatedSessionController@create` | guest |
| POST | `/login` | `AuthenticatedSessionController@store` | guest |
| POST | `/logout` | `AuthenticatedSessionController@destroy` | auth |
| GET | `/forgot-password` | `PasswordResetLinkController@create` | guest |
| POST | `/forgot-password` | `PasswordResetLinkController@store` | guest |
| GET | `/reset-password/{token}` | `NewPasswordController@create` | guest |
| POST | `/reset-password` | `NewPasswordController@store` | guest |

---

## SISTEMA DE PERMISSOES

### Como funciona

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Middleware que bloqueia rotas | `MDW/CheckPermission.php` | 1–20 | `abort(403)` se sem permissão |
| Verificar permissão do usuário | `MDL/User.php` | 71–79 | `hasPermission($perm)` |
| Listar todas as permissões do user | `MDL/User.php` | 81–113 | `permissionNames()` com cache |
| Cache de permissões em memória | `MDL/User.php` | 81–113 | `$permissionNamesCache` — evita N+1 |
| Verificar se é admin | `MDL/User.php` | 57–60 | `isAdmin()` — role === 'admin' |
| Verificar se é master | `MDL/User.php` | 62–65 | `isMaster()` — acesso total |
| Tabela de permissões disponíveis | `MDL/Permission.php` | 1–30 | `permissions` — name + descricao |
| Permissões por role | `MDL/RolePermission.php` | 1–25 | `role_permissions` — role + perm_id |
| Permissões individuais | `MDL/UserPermission.php` | 1–25 | `user_permissions` — user_id + perm_id |

### Lista de permissões (`permissions.name`)

| Permission | O que habilita |
|------------|----------------|
| `maquinas.visualizar` | Ver lista e detalhes de máquinas |
| `maquinas.criar` | Criar nova máquina |
| `maquinas.editar` | Editar máquina existente |
| `maquinas.deletar` | Deletar máquina |
| `tecnicos.visualizar` | Ver lista e detalhes de técnicos |
| `tecnicos.criar` | Criar novo técnico |
| `tecnicos.editar` | Editar técnico existente |
| `tecnicos.deletar` | Deletar técnico |
| `ordens.visualizar` | Ver lista e detalhes de O.S. |
| `ordens.criar` | Criar nova O.S. |
| `ordens.editar` | Editar/concluir O.S. |
| `ordens.deletar` | Deletar O.S. |
| `historico.visualizar` | Ver histórico de manutenções |
| `historico.criar` | Criar histórico manual |
| `historico.deletar` | Deletar registro de histórico |
| `acesso.gerenciar` | Gerenciar permissões de roles e usuários |
| `usuarios.visualizar` | Ver lista de usuários |
| `usuarios.criar` | Criar novo usuário |
| `usuarios.editar` | Editar usuário |
| `usuarios.deletar` | Deletar usuário |
| `usuarios.permissoes` | Ver permissões de usuário |

---

## MODELS — METODOS IMPORTANTES

| Método / Atributo | Arquivo | Linhas | Tipo | O que faz |
|-------------------|---------|--------|------|-----------|
| `isAdmin()` | `MDL/User.php` | 57–60 | Método | Verifica se role é admin |
| `isMaster()` | `MDL/User.php` | 62–65 | Método | Verifica se role é admin_master |
| `hasPermission($perm)` | `MDL/User.php` | 71–79 | Método | Verifica permissão específica |
| `permissionNames()` | `MDL/User.php` | 81–113 | Método | Array de permissões com cache |
| `clearPermissionCache()` | `MDL/User.php` | 113–115 | Método | Limpa cache `$permissionNamesCache` |
| `scopeOperacional()` | `MDL/Maquina.php` | 58–61 | Scope | Filtra máquinas operacionais |
| `scopeEmManutencao()` | `MDL/Maquina.php` | 63–66 | Scope | Filtra máquinas em manutenção |
| `scopeParadaCritica()` | `MDL/Maquina.php` | 68–71 | Scope | Filtra máquinas em parada crítica |
| `status_label` | `MDL/Maquina.php` | 73–84 | Acessor | Texto legível do status |
| `status_color` | `MDL/Maquina.php` | 86–97 | Acessor | Cor visual do status |
| `gerarNumero()` | `MDL/OrdemServico.php` | 111–130 | Estática | Número único com lock DB |
| `tipo_label` | `MDL/OrdemServico.php` | 56–61 | Acessor | Texto do tipo (Preventiva/Corretiva) |
| `status_label` | `MDL/OrdemServico.php` | 63–73 | Acessor | Texto do status da O.S. |
| `prioridade_label` | `MDL/OrdemServico.php` | 75–84 | Acessor | Texto da prioridade |
| `sincronizarStatusMaquina` | `CTR/OrdemServicoController.php` | 367–407 | Privado | Atualiza status automático |
| `notificarTecnicoAtribuido` | `CTR/OrdemServicoController.php` | 409–427 | Privado | Envia notificação por email |

---

## TAREFAS COMUNS

### Adicionar campo no formulário de técnico

```
1. VW/tecnicos/create.blade.php     → Adiciona o input no HTML
2. CTR/TecnicoController.php:63     → Adiciona em validate([...])
3. MDL/Tecnico.php:14               → Adiciona em $fillable
4. php artisan make:migration ...   → Cria migration
5. php artisan migrate              → Aplica no banco
```

### Mudar cor de status de máquina

```
1. MDL/Maquina.php:86               → getStatusColorAttribute()
2. Muda o mapeamento de cor
   Ex: 'parada_critica' => 'purple'
3. Confirma que o CSS da cor existe em resources/css/
```

### Adicionar nova estatística no Dashboard

```
1. CTR/DashboardController.php:32   → Adiciona ao array $stats
   Ex: 'tecnicos_inativos' => Tecnico::where('ativo', false)->count()

2. VW/dashboard.blade.php:21        → Adiciona novo stat-card
   Ex: <div class="stat-card">
           <div class="stat-label">Técnicos Inativos</div>
           <div class="stat-value">{{ $stats['tecnicos_inativos'] }}</div>
       </div>
```

### Adicionar filtro novo no Histórico

```
1. CTR/HistoricoController.php:38   → Adiciona if($request->filled(...))
   Ex: if ($request->filled('custo_minimo'))
           $query->where('custo', '>=', $request->custo_minimo);

2. VW/historico/index.blade.php     → Adiciona input no form de filtro
   (entre linha 29 e 68)
```

### Adicionar nova permissão ao sistema

```
1. Insere registro em tabela `permissions`
   INSERT INTO permissions (name, descricao, modulo)
   VALUES ('modulo.acao', 'Descrição legível', 'modulo');

2. Adiciona aos roles padrão em tabela `role_permissions`

3. Usa no middleware: middleware('perm:modulo.acao')

4. Usa na view: @if(auth()->user()->hasPermission('modulo.acao'))
```

---

## IMPRESSAO

| O que quero | Arquivo | Linhas | Detalhe |
|-------------|---------|--------|---------|
| Botão Imprimir Ordens | `VW/ordens/index.blade.php` | 17–19 | `onclick="window.print()"` |
| Botão Imprimir Detalhes O.S. | `VW/ordens/show.blade.php` | 18–20 | `onclick="window.print()"` |
| Botão Imprimir Histórico | `VW/historico/index.blade.php` | 17–19 | `onclick="window.print()"` |

Todos usam `window.print()` nativo do browser — sem CSS especial de impressão ou lógica server-side.

---

## DEBUGAR PROBLEMAS

### Máquina não muda de status automaticamente

```
1. CTR/OrdemServicoController.php:367  → sincronizarStatusMaquina()
   É chamada em: store() linha 82, update() linha 207, destroy() linha 356

2. Lógica (linhas 367–407):
   - Tem O.S. ativa (aberta/em_andamento)?  → muda para em_manutencao
   - Não tem mais O.S. ativa?               → volta para operacional
   - Status parada_critica ou inativa?      → NÃO muda (manual)
```

### Histórico não é criado ao concluir O.S.

```
1. CTR/OrdemServicoController.php:175  → $concluindoAgora
   Condição: status anterior != 'concluida' E novo == 'concluida'

2. CTR/OrdemServicoController.php:183  → HistoricoManutencao::create()
   Verifica se o bloco if($concluindoAgora) está sendo atingido

3. Check banco: SELECT * FROM historico_manutencoes ORDER BY id DESC LIMIT 5;
```

### Técnico não recebe notificação ao ser atribuído

```
1. CTR/OrdemServicoController.php:409  → notificarTecnicoAtribuido()
   Verifica:
   - Tecnico tem user_id?   → MDL/Tecnico.php relacionamento user()
   - User tem email?        → $tecnico->user->email
   - Queue worker rodando?  → php artisan queue:work
   - Logs: storage/logs/laravel.log
```

### Erro 403 ao acessar uma página

```
1. MDW/CheckPermission.php:14  → hasPermission() retornou false

2. Verifica se o usuário tem a permissão:
   - Se role = admin_master → tem tudo, checar outra coisa
   - Se permissions_overridden = true → usa user_permissions
   - Se permissions_overridden = false → usa role_permissions

3. MDL/User.php:81  → permissionNames() — lista as permissões do user
```

### Próxima preventiva não é criada automaticamente

```
1. CTR/OrdemServicoController.php:217  → if ($proxima_preventiva)
   Condição: campo proxima_preventiva preenchido na edição
   E a O.S. sendo concluída é do tipo 'preventiva'

2. VW/ordens/edit.blade.php:121–132    → Campo proxima_preventiva
   Só aparece quando tipo=preventiva E status=concluida
```

---

## QUICK REFERENCE — NUMEROS DE LINHA

```
CONTROLLERS (app/Http/Controllers/)
┌──────────────────────────────────────────────────────────────┐
│ TecnicoController       32 index  │  51 create │  63 store   │
│                        104 update │ 164 destroy               │
│ MaquinaController       32 index  │  49 store  │  81 update  │
│                        107 destroy                            │
│ OrdemServicoController  47 index  │  85 store  │ 159 update  │
│                        345 destroy │ 367 sincronizar          │
│                        409 notificar                          │
│ DashboardController     32 index (stats + alertas)           │
│ HistoricoController     37 index  │  66 show   │  83 porMaquina│
│                        112 exportar                           │
│ AccessController        14 index  │  54 updateUserPerms      │
│                         92 updateRole │ 130 updateUsuario     │
│ UserManagementController 15 index │  24 create │  31 store   │
│                          48 edit  │  65 update │ 100 destroy  │
│                         112 showPermissions                   │
└──────────────────────────────────────────────────────────────┘

MODELS (app/Models/)
┌──────────────────────────────────────────────────────────────┐
│ User.php          57 isAdmin   │ 62 isMaster  │ 71 hasPerm   │
│                   81 permissionNames │ 113 clearCache        │
│ Maquina.php       58 scopes    │ 73 status_label             │
│                   86 status_color                             │
│ OrdemServico.php  56 tipo_label│ 63 status_label             │
│                   75 prioridade_label │ 111 gerarNumero       │
│ HistoricoManutencao.php  14 fillable │ 35 casts               │
│                          47 relacoes                          │
└──────────────────────────────────────────────────────────────┘

VIEWS (resources/views/)
┌──────────────────────────────────────────────────────────────┐
│ dashboard.blade.php      21 cards │ 63 alertas               │
│                          89 tabela O.S. │ 172 manutenções     │
│                         223 ações rápidas                     │
│ tecnicos/index.blade.php 30 btn_novo │ 81 badge_ativo        │
│                          88 acoes    │ 113 paginacao          │
│ ordens/edit.blade.php    95 campos_conclusao                  │
│                         121 proxima_preventiva │ 141 JS       │
└──────────────────────────────────────────────────────────────┘
```

---

**Versão:** 3.0 — **Atualizado:** 2026-06-08
