MaintSys – SENAI Limeira

Sistema de Gestão de Manutenção Industrial desenvolvido como projeto acadêmico para digitalizar o controle de ordens de serviço e status de máquinas no SENAI de Limeira.

📌 Contexto

Atualmente, registros de manutenção são realizados manualmente, dificultando:

• Controle de histórico

• Monitoramento de status de máquinas

• Organização de ordens de serviço

• Análise de reincidência de falhas

O MaintSys propõe uma API RESTful para centralizar essas informações.

🎯 Objetivo do Projeto

Desenvolver uma API robusta para:

• Gerenciar máquinas industriais

• Controlar ordens de serviço (preventivas e corretivas)

• Registrar histórico de manutenções

• Permitir abertura de chamados por docentes e técnicos

• Monitorar status operacional das máquinas

🧱 Modelagem Inicial

Entidades principais:

• Docente

• Técnico

• Máquina

• Ordem de Serviço

Relacionamentos:

• Docente pode solicitar ordens de serviço

• Técnico pode solicitar e executar ordens de serviço

• Máquina possui múltiplas ordens de serviço

Diagramas UML já desenvolvidos:

• Diagrama de Casos de Uso

• Diagrama de Classes

• Diagrama de Entidade-Relacionamento (DER)

Diagrama de Casos de Uso


<img width="781" height="605" alt="image" src="https://github.com/user-attachments/assets/b252ee00-8334-4aed-809c-eab63c286d5f" />


Diagrama de Entidade-Relacionamento (DER)


<img width="971" height="593" alt="image (1)" src="https://github.com/user-attachments/assets/013c9710-cb3d-4202-9336-e9a974702051" />



🛠 Tecnologias

• PHP
• Laravel
• MySQL
• Eloquent ORM
• API RESTful
• Git
• Postman / Insomnia

🏗 Metodologia

Projeto estruturado em Sprints:

Sprint 1

• Levantamento de requisitos

• Modelagem UML

• Prototipagem inicial

• Organização do repositório

• Documentação inicial

