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
---
### Database Server B-NCC
---
### Web Server W-NCC
---
### FIle Server F-NCC
---
### Clients CLIWIN & CLILIN


---
## 4.Configuració de serveis

### DHCP Fitxer de configuració (`/etc/dhcp/dhcpd.conf`):
<img width="785" height="491" alt="image" src="https://github.com/user-attachments/assets/81048a62-9b62-4856-a0a9-e2c9e9579f46" />

| Aplicar IP CLIWIN | Aplicar IP CLILIN |
|:-----------------:|:----------------:|
| <img width="394" height="609" alt="image" src="https://github.com/user-attachments/assets/416d0ece-f402-453e-94bf-5b63f4b9742f" /> | <img width="394" height="609" alt="image" src="https://github.com/user-attachments/assets/173f1964-2fa2-4087-8d5f-95b1444473c9" /> |


### MySQL Creacio de la base de dades

```mysql

```
