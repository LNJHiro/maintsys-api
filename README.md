<div align="center">

# ⚙️ MaintSys
**Sistema de Manutenção Industrial 4.0**

<img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white"/>
<img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white"/>
<img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white"/>
<img src="https://img.shields.io/badge/Sanctum-FF2D20?style=for-the-badge&logo=laravel&logoColor=white"/>
<img src="https://img.shields.io/badge/JSON-000000?style=for-the-badge&logo=json&logoColor=white"/>

> API RESTful que substitui fichas de papel pelo controle digital de manutenção industrial — conectando técnicos, gestores e máquinas em tempo real.

**SENAI Limeira, SP · Projeto Integrador · 2026**

</div>

---

## 🔗 Protótipo e Gestão do Projeto

| Recurso | Link |
|-------|------|
| 📋 **Link: Trello**| [Trello](https://trello.com/invite/b/699895865de97ce9e1ed0a19/ATTI092d2e5ed783761fc1dc5a293b494341499AB6EA/projeto-integrador) |
| 🎨 **Link: Figma** | [Figma](https://cleat-pickle-20252208.figma.site/login) |

---

## 📌 Sobre o Projeto

## 🏭 Manutenção Industrial 4.0

### 📌 Situação-Problema
Na era da **Indústria 4.0**, a disponibilidade das máquinas é crucial para a produtividade.  
Paradas não planejadas podem gerar prejuízos de milhares de reais e atrasar toda a cadeia produtiva.

Muitas fábricas ainda utilizam **fichas de papel penduradas nas máquinas** para registrar manutenções, o que dificulta o controle, histórico e análise de falhas.

O **SENAI** contratou uma consultoria para substituir esse método por um **sistema digital centralizado**, onde:

- Técnicos registrem manutenções em tempo real.
- Gestores acompanhem o estado das máquinas.
- O histórico de intervenções seja armazenado e analisado.

---

## 🎯 Desafio

Sua equipe deve desenvolver o **Back-End do MaintSys**, uma **API para controle de manutenção de maquinário industrial**.

O sistema deve:

- Garantir **integridade dos dados**
- Permitir **registro de intervenções por técnicos**
- Ser preparado para **integração futura com tablets e terminais industriais**

---

## 📋 Metodologia e Planejamento

### 📌 Levantamento de Requisitos
Foram identificados problemas na gestão de manutenção industrial, como falta de organização, demora nos atendimentos e ausência de histórico, definindo as principais funcionalidades do sistema.

### 🎨 Prototipagem
Interfaces desenvolvidas no **Figma** para estruturar telas como login, cadastro e dashboard, garantindo melhor visualização e experiência do usuário.

### 🔄 Metodologias Ágeis
O projeto é desenvolvido por meio de **sprints**, permitindo entregas contínuas, organização das tarefas e evolução gradual do sistema.

### 📦 Versionamento
Utilização do **Git e GitHub** para controle de versões, permitindo acompanhamento das alterações, colaboração entre a equipe e segurança no desenvolvimento.

### 📄 Documentação
Documentação planejada para descrever **endpoints da API**, padronização REST, tratamento de erros e organização das respostas em JSON.

---

# ⚙️ Funcionalidades Essenciais

### 👨‍🔧 1. Gestão de Técnicos e Acesso
Cadastro de técnicos especializados com sistema de **autenticação segura**.

### 🏭 2. Inventário de Máquinas
Cadastro de equipamentos contendo:

- Número de série
- Modelo
- Localização no galpão
- Data de instalação

### 📋 3. Ordens de Serviço (O.S.)
Criação de ordens de manutenção:

- Preventiva
- Corretiva

Cada ordem deve vincular:

- Um **técnico**
- Uma **máquina específica**

### 📊 4. Histórico de Manutenções
Registro completo de todas as intervenções realizadas em uma máquina, permitindo:

- Análise de falhas
- Identificação de reincidência de defeitos
- Rastreamento de atividades

### 🚨 5. Alertas de Status
Sistema de notificação quando uma máquina muda de status, por exemplo:

- `Máquina 04 em Parada Crítica`
- `Manutenção Concluída`

---

# 🧱 Requisitos Técnicos

- Back-End desenvolvido em **Laravel (PHP)**
- Banco de dados **MySQL**
- Utilização do **Eloquent ORM** para modelagem de dados  
  - Exemplo: *Uma Máquina possui muitas Ordens de Serviço*
- API **RESTful** retornando dados em **JSON**
- Código seguindo padrões **PSR** e **Clean Code**
- Versionamento no **GitHub**
- Testes de API utilizando **Insomnia ou Postman**

---

## 📋 Requisitos Funcionais

Os **Requisitos Funcionais** descrevem **as funcionalidades do sistema**, ou seja, as ações que o sistema deve executar para atender às necessidades dos usuários.

Eles representam **o que o sistema faz**.

**Exemplos no MaintSys:**
- Cadastro de técnicos com autenticação segura.
- Cadastro de máquinas com número de série, modelo e localização.
- Criação de Ordens de Serviço (preventiva ou corretiva).
- Registro de intervenções realizadas pelos técnicos.
- Consulta do histórico de manutenção de uma máquina.
- Atualização do status de uma Ordem de Serviço.

---

## ⚙️ Requisitos Não Funcionais

Os **Requisitos Não Funcionais** descrevem **como o sistema deve funcionar**, incluindo regras de qualidade, desempenho, segurança e padrões técnicos.

Eles não representam funcionalidades diretas, mas **características que garantem qualidade e confiabilidade do sistema**.

**Exemplos no MaintSys:**
- A API deve seguir o padrão **RESTful** com respostas em **JSON**.
- O sistema deve utilizar **Laravel 11 e PHP 8.2+**.
- A autenticação deve utilizar **Laravel Sanctum com tokens Bearer**.
- O banco de dados deve garantir **integridade com chaves estrangeiras (FK)**.
- Consultas de histórico devem responder em **menos de 2 segundos**.
- O código deve seguir o padrão **PSR-12 e boas práticas de Clean Code**.

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
