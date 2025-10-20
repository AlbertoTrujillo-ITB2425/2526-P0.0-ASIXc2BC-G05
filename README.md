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

Descripció de l'esquema:
- El diagrama mostra la topologia general del projecte: el router R-NCC com a punt d'unsió entre les subxarxes, servidors dedicats (DHCP, Web, BBDD, Fitxers) en una VLAN de serveis i clients en VLANs separades.  
- Les connexions fisico-lògiques representen rutes estàndard i regles de tallafocs mínimes per permetre els serveis necessaris (HTTP, MySQL, SMB i DHCP).
- Els IPs i subxarxes s'han planificat per facilitar el creixement (més IPs per subxarxa de serveis i VLANs CLI).

---

## 3. Infraestructura desplegada

A continuació es mostren captures i, on cal, una breu explicació textual per complementar les imatges i facilitar la lectura.

### Router R-NCC
![Router R-NCC](#)

Descripció del Router R-NCC:
- Aquest router s'encarrega del encaminament entre VLANs i de la sortida cap a xarxes externes (si s'escau).
- Configuració típica inclou interfícies amb IPs assignades per a cada VLAN, rutes estàtiques bàsiques i, si escau, configuració de NAT per a l'accés a Internet.
- En la imatge es poden veure les interfícies principals, les VLANs i les rutes configurades. També serveix com a punt per a regles de tallafoc per a segments interns.

---

### DHCP Server
<img width="845" height="622" alt="image" src="https://github.com/user-attachments/assets/5f981a11-8565-4214-b966-b415ec1f0aa3" />

Descripció del servidor DHCP:
- El servidor DHCP proporciona adreces IP dinàmiques als clients de les VLANs definides. La captura mostra la consola o la configuració del servei DHCP activa.
- Principals paràmetres: rangs d'IP per subxarxa, temps de concessió (lease), gateway per defecte i servidors DNS assignats.
- En producció hem configurat rangs que eviten solapaments amb IPs estàtiques reservades per a servidors i equips de xarxa.

---

### Database Server B-NCC
<img width="845" height="653" alt="Captura de pantalla de 2025-10-20 15-09-37" src="https://github.com/user-attachments/assets/91df8564-9cf4-4e05-aa68-cafbcc95e472" />

Descripció del servidor de base de dades:
- Aquest servidor allotja la base de dades principal (MySQL/MariaDB). La captura mostra la interfície o el resultat d'una consulta que verifica l'estat de la BBDD.
- Funcions principals: gestió d'esquemes de dades per l'aplicació web, còpies de seguretat programades i restriccions d'accés per usuaris remots.
- S'han aplicat permisos mínims als usuaris i connexions xifrades quan és possible per augmentar la seguretat.

---

### Web Server W-NCC
<img width="1038" height="416" alt="Captura Web 1" src="https://github.com/user-attachments/assets/af5c4b39-2aa8-4296-9ccc-3d070ed0ceb5" />
<img width="809" height="279" alt="Captura Web 2" src="https://github.com/user-attachments/assets/65501edd-9235-4c96-b090-8cf65ce86956" />

Descripció del servidor web:
- Les captures mostren la pàgina web servida pel W-NCC i proves d'accés HTTP/HTTPS des de clients de la xarxa.
- El servidor web serveix contingut dinàmic que pot connectar-se amb la base de dades B-NCC per obtenir dades d'aplicació.
- S'ha comprovat el correcte enrutament, permisos de fitxers i la resposta dels endpoints principals (pàgina inicial, formularis i connexió a BBDD).

---

### File Server F-NCC
<img width="715" height="482" alt="Captura Fitxer 1" src="https://github.com/user-attachments/assets/211a4257-f1f7-44bf-867b-4d86b34adac8" />
<img width="746" height="359" alt="Captura Fitxer 2" src="https://github.com/user-attachments/assets/b4742b48-9bf8-4428-8813-ab1be867ebdc" />
<img width="361" height="53" alt="Captura Fitxer 3" src="https://github.com/user-attachments/assets/8a0b340f-1614-438c-8c9f-f7cfbe74cb8c" />
<img width="1038" height="416" alt="Captura Fitxer 4" src="https://github.com/user-attachments/assets/1bbe8d27-f2f8-4d51-90b4-1d4010cf4fb3" />

Descripció del servidor de fitxers:
- El servidor F-NCC proporciona recursos compartits (per exemple SMB/CIFS) perquè els clients puguin emmagatzemar i recuperar fitxers. Les captures mostren l'estructura de directoris, permisos i un exemple d'accés des d'un client.
- S'han definit usuaris i grups, així com quotes i permisos per evitar accés indegut i mantenir l'organització dels fitxers.
- També s'han implementat còpies de seguretat i comprovacions d'integritat bàsiques.

---

### Clients CLIWIN & CLILIN
<img width="914" height="304" alt="Clients CLI 1" src="https://github.com/user-attachments/assets/caf48c54-ff97-4b35-a357-fea9f64cbae5" />
<img width="984" height="511" alt="Clients CLI 2" src="https://github.com/user-attachments/assets/a0ccc30e-6beb-4853-978c-330ec4e448c9" />
<img width="745" height="512" alt="Clients CLI 3" src="https://github.com/user-attachments/assets/6e622666-f19e-4617-b766-fc59e595a6a2" />

Descripció dels clients:
- CLIWIN representa un client Windows i CLILIN un client Linux. Les captures mostren l'obtenció d'IP via DHCP, ping cap a servidors i comprovacions d'accés a serveis (web, fitxers).
- Es detallen exemples de com els clients reben la configuració de xarxa, com accedeixen als recursos compartits i com s'executen proves de connectivitat bàsica.
- Aquestes proves serveixen per validar que el DHCP, el tallafocs i les rutes funcionen correctament.

---

## 4.Configuració de serveis

A continuació es mostren fragments de configuració i explicacions per a cada servei implementat, junt amb captures quan és rellevant.

### DHCP Fitxer de configuració (`/etc/dhcp/dhcpd.conf`):
<img width="771" height="517" alt="Configuració DHCP" src="https://github.com/user-attachments/assets/a26df952-47a8-4a5e-9c8d-e798f4a00059" />

Explicació del fitxer dhcpd.conf:
- El fitxer conté la definició de subxarxes i els rangs d'IP assignables (pools), així com options globals com DNS i temps de concessió (lease).
- També s'inclouen reserves d'IP per a servidors i dispositius de xarxa perquè no coincideixin amb el pool dinàmic.
- Exemple d'opcions: option domain-name-servers 8.8.8.8, option routers 192.168.1.1, range 192.168.1.100 192.168.1.200.

Taula d'aplicació d'IP als clients:

| Aplicar IP CLIWIN | Aplicar IP CLILIN |
|:-----------------:|:----------------:|
| <img width="394" height="609" alt="Aplicar IP CLIWIN" src="https://github.com/user-attachments/assets/416d0ece-f402-453e-94bf-5b63f4b9742f" /> | Imatge no disponible — descripció: captura de la pantalla de CLILIN mostrant la interfície de xarxa i l'adreça IP assignada per DHCP, amb la configuració de gateway i DNS aplicats. |

Nota addicional:
- Si la imatge del CLILIN no està disponible, el que hi hauria de mostrar és la sortida de la comanda `ip addr` o `ifconfig` amb la interfície principal configurada i la IP assignada, juntament amb `ip route` per validar el gateway.

---

### MySQL Creacio de la base de dades
<img width="707" height="527" alt="Creació BBDD" src="https://github.com/user-attachments/assets/9bc4d813-f384-4563-afff-7484127ffb04" />

Descripció del procés de creació:
- La captura mostra les instruccions SQL utilitzades per crear la base de dades i l'usuari aplicatiu. Exemple d'ordres utilitzades (resum):
  - CREATE DATABASE aplicacio_db;
  - CREATE USER 'appuser'@'%' IDENTIFIED BY '*****';
  - GRANT SELECT, INSERT, UPDATE, DELETE ON aplicacio_db.* TO 'appuser'@'%';
  - FLUSH PRIVILEGES;
- També s'han aplicat exportacions de dades i scripts d'inicialització per carregar l'esquema i dades de prova.
- Recomanació: emmagatzemar scripts SQL versionats en el repositori i protegir credencials usant secrets gestionats (no en text pla).

---

## 5. Proves realitzades

Resum de proves efectuades:
- Connectivitat bàsica: pings entre clients i servidors, resolució DNS i rutes entre VLANs.
- DHCP: comprovació d'assignació d'IP i reserves per dispositius específics.
- Web: accés a pàgines principals, comprovació de connexió a la base de dades des de l'aplicació web.
- Base de dades: creació de taules, inserció i lectura de dades de prova amb l'usuari aplicatiu.
- Fitxers: muntatge de recursos compartits i proves de lectura/escriptura amb usuaris diferents.
- Tests de seguretat bàsiques: comprovació de ports oberts i permisos de fitxers mínims.

---

## 6. Conclusions i millores

Conclusions:
- La infraestructura desplegada cobreix els requisits bàsics: serveis centrals (DHCP, Web, BBDD, Fitxers) i clients en VLANs separades.
- Les proves essencials confirmen que els serveis són accessibles i que l'estructura d'adreçament IP és coherent amb el disseny.

Millores proposades:
- Afegir monitoratge i alertes (Prometheus/Grafana) per supervisar recursos i serveis.
- Implementar còpies de seguretat automatitzades i verificacions periòdiques (BBDD i fitxers).
- Reforçar la seguretat amb TLS per a connexions internes si cal, i revisar regles del tallafocs.
- Versionar scripts de desplegament (IaC) per facilitar reproducció i escalabilitat.
