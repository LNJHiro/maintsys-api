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

Manutenção Industrial 4.0
Situação Problema "Na era da Indústria 4.0, a disponibilidade das máquinas é crucial para a
produtividade. Paradas não planejadas custam milhares de reais e atrasam toda a cadeia
produtiva. Fábricas modernas buscam migrar de anotações em papel para sistemas digitais que
prevejam e registrem manutenções de forma eficiente."
"O Senai contratou sua consultoria para acabar com as 'fichas de papel' penduradas nas
máquinas. Eles precisam de um sistema centralizado onde a equipe de manutenção possa
registrar intervenções e os gerentes possam acompanhar a saúde dos equipamentos em tempo
real."
Desafio: "Sua equipe deve implementar o Back-End do 'MaintSys', uma API para controle de
manutenção de maquinário industrial. O sistema deve garantir a integridade dos dados e
permitir que técnicos registrem atividades através de futuros tablets ou terminais industriais."
Funcionalidades Essenciais:
1. Gestão de Técnicos e Acesso: Cadastro de técnicos especializados com autenticação
segura.
2. Inventário de Máquinas: Cadastro de equipamentos (Número de Série, Modelo,
Localização no Galpão, Data de Instalação).
3. Ordens de Serviço (O.S.): Criação de ordens de manutenção (Preventiva ou Corretiva),
vinculando um técnico a uma máquina específica.
4. Histórico de Manutenções: Log completo de todas as intervenções realizadas em uma
máquina, permitindo análise de reincidência de defeitos.
5. Alertas de Status: Sistema para notificar quando uma máquina muda de status (ex:
"Máquina 04 em Parada Crítica" ou "Manutenção Concluída").
Requisitos e Restrições:
● Back-End desenvolvido em Laravel (PHP).
● Banco de dados relacional MySQL.
● Utilizar o Eloquent ORM para modelar as tabelas e relações (ex: Uma Máquina tem muitas
Ordens de Serviço).
● API RESTful retornando JSON.
● Código seguindo padrões PSR e Clean Code.
● Publicação no GitHub e testes via Insomnia/Postman.

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
