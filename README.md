<div align="center">

# ⚙️ MaintSys
**Sistema de Manutenção Industrial 4.0**

<img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white"/>
<img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white"/>
<img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white"/>
<img src="https://img.shields.io/badge/Sanctum-FF2D20?style=for-the-badge&logo=laravel&logoColor=white"/>

> API RESTful que substitui fichas de papel pelo controle digital de manutenção industrial — conectando técnicos, gestores e máquinas em tempo real.

**SENAI Limeira, SP · Projeto Integrador · 2026**

</div>

---

## 🔗 Protótipo e Gestão do Projeto

| Recurso | Link |
|-------|------|
| 🎨 Protótipo (Figma) | https://cleat-pickle-20252208.figma.site/login |
| 📋 Quadro do Projeto (Trello) | https://trello.com/invite/b/699895865de97ce9e1ed0a19/ATTI092d2e5ed783761fc1dc5a293b494341499AB6EA/projeto-integrador |

---

## 📌 Sobre o Projeto

O **MaintSys** resolve um problema real do chão de fábrica: o controle de manutenção feito em fichas de papel é lento, suscetível a perdas e impossível de rastrear. O sistema digitaliza esse processo com uma API segura e rastreável, pensada para tablets industriais e terminais de fábrica.

**Dois perfis de usuário:**

- 👷 **Técnico** — abre O.S., registra intervenções, consulta máquinas e alertas  
- 🏭 **Gestor** — todos os privilégios do técnico + cadastro de máquinas e técnicos, histórico completo e análise de reincidências

---

## 🛠️ Tecnologias

| Camada | Tecnologia |
|--------|-----------|
| Framework | Laravel 11 (PHP 8.2+) |
| Banco de Dados | MySQL 8.0 + Eloquent ORM |
| Autenticação | Laravel Sanctum — token Bearer + bcrypt |
| Padrão de Código | PSR-12 + Clean Code |
| Versionamento | Git / GitHub — GitFlow simplificado |
| Gerenciamento | Scrum — 6 sprints |

---

## 📋 Requisitos Funcionais

| ID | Funcionalidade | Prioridade |
|----|---------------|-----------|
| RF01 | Cadastro de técnicos com autenticação segura por token | 🔴 Alta |
| RF02 | Cadastro de máquinas: N° Série, Modelo, Localização e Data de Instalação | 🔴 Alta |
| RF03 | Criação de O.S. Preventiva ou Corretiva, vinculando técnico e máquina | 🔴 Alta |
| RF04 | Log completo de intervenções por máquina (histórico de manutenções) | 🔴 Alta |
| RF05 | Notificações automáticas ao técnico/gestor por mudança de status | 🟡 Média |
| RF06 | Consulta e filtragem de O.S. por técnico, máquina ou período | 🟡 Média |
| RF07 | Análise de reincidência de defeitos por máquina | 🟡 Média |
| RF08 | Encerramento e atualização de status de O.S. pelo técnico responsável | 🔴 Alta |

---

## ⚙️ Requisitos Não Funcionais

| ID | Categoria | Descrição | Prioridade |
|----|-----------|-----------|-----------|
| RNF01 | Linguagem e Framework | Back-end em PHP com Laravel (MVC, middlewares, service providers) | 🔴 Alta |
| RNF02 | Banco de Dados | MySQL com Eloquent ORM, migrations e seeders versionados | 🔴 Alta |
| RNF03 | Padrão de API | RESTful com respostas em JSON e verbos HTTP semânticos | 🔴 Alta |
| RNF04 | Segurança | Sanctum, bcrypt, rotas protegidas por middleware, prevenção de SQL Injection | 🔴 Alta |
| RNF05 | Qualidade de Código | PSR-12 + Clean Code: responsabilidade única, sem duplicações | 🔴 Alta |
| RNF06 | Versionamento | GitHub com GitFlow, branches por funcionalidade | 🔴 Alta |
| RNF07 | Testabilidade | Endpoints testáveis via Postman/Insomnia com collection documentada | 🟡 Média |
| RNF08 | Integridade dos Dados | FK com constraints; histórico imutável — registros apenas encerrados | 🔴 Alta |
| RNF09 | Desempenho | Consultas de histórico < 2s via eager loading do Eloquent | 🟡 Média |
| RNF10 | Compatibilidade | API stateless para integração com tablets e terminais industriais | 🟢 Baixa |

---

## 📅 Sprints

| Sprint | Período | Entregas |
|--------|---------|---------|
| Sprint 1 | 20/02 – 25/02/2026 | Requisitos, Prototipagem, Metodologias, Versionamento, Documentação |
| Sprint 2 | 13/03 – 18/03/2026 | Revisão geral + Diagrama de Classes e Sequência |
| Sprint 3 | 01/04 – 10/04/2026 | Revisão geral + Diagramação de Banco de Dados |
| Sprint 4 | 29/04 – 08/05/2026 | Revisão geral + Testes de Acessibilidade e Funcionalidades |
| Sprint 5 | 27/05 – 29/05/2026 | Revisão geral + Qualidade de Software + Implantação (Parte I) |
| Sprint 6 | 12/06 – 17/06/2026 | Implantação (Parte II) + Entrega e Apresentação |

---

## 👥 Equipe

| Douglas Nogueira | Hayron de Oliveira | Gabriel Marques |
|:---:|:---:|:---:|
| Desenvolvedor | Desenvolvedor | Desenvolvedor |

<div align="center">
<sub>SENAI Limeira, SP · Projeto Integrador — Indústria 4.0 · 2026</sub>
</div>
