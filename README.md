# 2526-P0.0-ASIXc2BC-G05

## Índex

1. [Planificació del projecte](#planificació-del-projecte)
2. [Esquema de xarxa](#esquema-de-xarxa)
    - [Descàrrega de l'esquema](#descàrrega-de-lesquema)
    - [Visualització de l'esquema](#visualització-de-lesquema)
3. [Infraestructura desplegada](#infraestructura-desplegada)
4. [Configuració de serveis](#configuració-de-serveis)
5. [Proves realitzades](#proves-realitzades)
6. [Conclusions i millores](#conclusions-i-millores)

---

## 1. Planificació del projecte

La planificació s'ha realitzat utilitzant la plataforma **Proofhub**, on s'ha estructurat el treball en 3 sprints quinzenals de 10 hores cadascun. El backlog i la divisió de tasques han estat definits segons la nomenclatura i els requeriments especificats.

- **Sprints:** 3 (2 setmanes cadascun, 5 h/setmana)
- **Durada total:** 6 setmanes (fins al 18/11)
- **Gestió de documentació i configuració:** tot versionat al repositori git amb el nom **P0.0-ASIXc2BC-G05**
- **Enllaç a Proofhub:** [Tauler del projecte a Proofhub](https://itecbcn.proofhub.com/bapplite/#app/todos/project-9335566085/list-269936034851)

A la plataforma Proofhub es poden consultar les tasques, els sprints, el backlog i l'estat actual del projecte de manera col·laborativa.
---

## 2. Esquema de xarxa

L’arquitectura desplegada es representa en el següent diagrama:

### Descàrrega de l'esquema

Podeu descarregar el fitxer de l’esquema de xarxa aquí:  
[Descarregar esquema de xarxa (Packet Tracer)](https://drive.google.com/file/d/1atEO0mJYaNl4XfbM8BtlbaUDIN4p2D8S/view?usp=sharing)

### Visualització de l'esquema

A continuació es mostra una imatge representativa de l’esquema de xarxa:

![Esquema de Xarxa](https://github.com/user-attachments/assets/12fdae6a-c0b8-4ae6-8dcf-2a22dcaad1b4)

---
## 3. Infraestructura desplegada

### Router R-NCC
---
### DHCP Server
<img width="845" height="622" alt="image" src="https://github.com/user-attachments/assets/5f981a11-8565-4214-b966-b415ec1f0aa3" />

---
### Database Server B-NCC
<img width="845" height="653" alt="Captura de pantalla de 2025-10-20 15-09-37" src="https://github.com/user-attachments/assets/91df8564-9cf4-4e05-aa68-cafbcc95e472" />

---
### Web Server W-NCC
<img width="1038" height="416" alt="Captura de pantalla de 2025-10-20 15-51-49" src="https://github.com/user-attachments/assets/af5c4b39-2aa8-4296-9ccc-3d070ed0ceb5" />
<img width="809" height="279" alt="Captura de pantalla de 2025-10-13 16-32-50" src="https://github.com/user-attachments/assets/65501edd-9235-4c96-b090-8cf65ce86956" />


---
### FIle Server F-NCC
<img width="715" height="482" alt="Captura de pantalla de 2025-10-13 17-22-09" src="https://github.com/user-attachments/assets/211a4257-f1f7-44bf-867b-4d86b34adac8" />
<img width="746" height="359" alt="Captura de pantalla de 2025-10-13 17-33-34" src="https://github.com/user-attachments/assets/b4742b48-9bf8-4428-8813-ab1be867ebdc" />
<img width="361" height="53" alt="Captura de pantalla de 2025-10-13 17-35-11" src="https://github.com/user-attachments/assets/8a0b340f-1614-438c-8c9f-f7cfbe74cb8c" />


---
### Clients CLIWIN & CLILIN
<img width="914" height="304" alt="image" src="https://github.com/user-attachments/assets/caf48c54-ff97-4b35-a357-fea9f64cbae5" />
<img width="984" height="511" alt="Captura de pantalla de 2025-10-20 15-41-23" src="https://github.com/user-attachments/assets/a0ccc30e-6beb-4853-978c-330ec4e448c9" />
<img width="745" height="512" alt="Captura de pantalla de 2025-10-20 15-41-54" src="https://github.com/user-attachments/assets/6e622666-f19e-4617-b766-fc59e595a6a2" />





---
## 4.Configuració de serveis

### DHCP Fitxer de configuració (`/etc/dhcp/dhcpd.conf`):
<img width="771" height="517" alt="Captura de pantalla de 2025-10-20 15-21-49" src="https://github.com/user-attachments/assets/5088b146-d48d-46fe-9dce-777df3449ca7" />



| Aplicar IP CLIWIN | Aplicar IP CLILIN |
|:-----------------:|:----------------:|
| <img width="394" height="609" alt="image" src="https://github.com/user-attachments/assets/416d0ece-f402-453e-94bf-5b63f4b9742f" /> | <img width="394" height="609" alt="image" src="" /> |


### MySQL Creacio de la base de dades
<img width="707" height="527" alt="Captura de pantalla de 2025-10-14 16-32-05" src="https://github.com/user-attachments/assets/9bc4d813-f384-4563-afff-7484127ffb04" />
