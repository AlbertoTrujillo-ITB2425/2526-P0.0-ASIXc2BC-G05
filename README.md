# 2526-P0.0-ASIXc2BC-G05

## Índex

1. [Planificació del projecte](#planificació-del-projecte)
2. [Esquema de xarxa](#esquema-de-xarxa)
    - [Descàrrega de l'esquema](#descàrrega-de-lesquema)
    - [Visualització de l'esquema](#visualització-de-lesquema)
3. [Infraestructura desplegada](#infraestructura-desplegada)
4. [Configuració de serveis](#configuració-de-serveis)

---

## 1. Planificació del projecte

La planificació s'ha fet a **Proofhub** en **tres sprints** de **dues setmanes** i **cinc hores per setmana**. La **durada total** és de **sis setmanes** fins al **18/11**. La documentació i la configuració estan versionades al repositori git amb el nom **P0.0-ASIXc2BC-G05**. Enllaç a Proofhub: [Tauler del projecte a Proofhub](https://itecbcn.proofhub.com/bapplite/#app/todos/project-9335566085/list-269936034851)

---

## 2. Esquema de xarxa

L’arquitectura desplegada es representa al diagrama següent. Pots descarregar el fitxer de l’esquema de xarxa: [Descarregar esquema de xarxa (Packet Tracer)](https://drive.google.com/file/d/1atEO0mJYaNl4XfbM8BtlbaUDIN4p2D8S/view?usp=sharing)

![Esquema de Xarxa](https://github.com/user-attachments/assets/12fdae6a-c0b8-4ae6-8dcf-2a22dcaad1b4)
</div>
![DHCP Server (vista general)](https://github.com/user-attachments/assets/5f981a11-8565-4214-b966-b415ec1f0aa3)
</div>

La topologia usa el **router R-NCC** com a encaminador central amb **servidors a la VLAN de serveis** i **adreçament IP separat** per facilitar l’**escalabilitat** i la **gestió**.

---

## 3. Infraestructura desplegada

### Router R-NCC

Encamina les **subxarxes internes**, pot actuar de **gateway de sortida**, disposa d’**interfícies** amb IP per a cada **VLAN**, **rutes estàtiques** i **regles bàsiques de tallafocs**. És el **punt de control** de polítiques d’accés entre VLANs i la xarxa de serveis.

![Router R-NCC (interfaces i rutes)](https://github.com/user-attachments/assets/placeholder-router.png)
</div>

### DHCP Server

Proporciona **adreces IP dinàmiques** a les VLANs definides amb **rangs per subxarxa** i **reserves** per a servidors i dispositius de xarxa. Inclou **opcions** com **DNS**, **gateway** i **temps de concessió (lease)** evitant **solapaments** amb IPs estàtiques.

![DHCP Server (pools i opcions)](https://github.com/user-attachments/assets/5f981a11-8565-4214-b966-b415ec1f0aa3)
</div>
![CLI Windows amb IP aplicada](https://github.com/user-attachments/assets/416d0ece-f402-453e-94bf-5b63f4b9742f)
</div>

### Database Server B-NCC i Web Server W-NCC

**B-NCC** allotja la **base de dades** principal (MySQL/MariaDB) amb **còpies de seguretat** i **permisos limitats**. **W-NCC** serveix **contingut dinàmic** i es connecta amb la BBDD mitjançant l’**usuari aplicatiu**. S’han provat **consultes**, **connexions des del web** i **rutes entre servidors**.

![Database Server B-NCC (estat i consultes)](https://github.com/user-attachments/assets/91df8564-9cf4-4e05-aa68-cafbcc95e472)
</div>
![Web Server W-NCC (pàgina i proves)](https://github.com/user-attachments/assets/af5c4b39-2aa8-4296-9ccc-3d070ed0ceb5)
</div>

### File Server F-NCC

El servidor de fitxers ofereix **comparticions SMB/CIFS** amb **gestió d’usuaris i permisos**, **estructura de directoris per grups**, **quotes** i **còpies de seguretat**. S’han verificat **operacions de lectura i escriptura** des de diferents usuaris.

![File Server (estructura de directoris)](https://github.com/user-attachments/assets/211a4257-f1f7-44bf-867b-4d86b34adac8)
</div>
![File Server (permisos i grups)](https://github.com/user-attachments/assets/b4742b48-9bf8-4428-8813-ab1be867ebdc)
</div>

Exemples d’**administració** i **comprovacions**: **gestió de quotes**, **processos de backup** i **verificacions d’integritat**.

![File Server (exemples de proves)](https://github.com/user-attachments/assets/1bbe8d27-f2f8-4d51-90b4-1d4010cf4fb3)
</div>

### Clients CLIWIN i CLILIN

**CLIWIN** obté IP via **DHCP**, fa **proves de ping** i accedeix a **recursos compartits**. **CLILIN** utilitza **ip addr**, **ip route** i **ping**, i munta **comparticions SMB/NFS** segons la configuració. S’ha validat la **connectivitat** i els **permisos**.

![Clients (configuració i IP)](https://github.com/user-attachments/assets/caf48c54-ff97-4b35-a357-fea9f64cbae5)
</div>
![Clients (proves de ping)](https://github.com/user-attachments/assets/a0ccc30e-6beb-4853-978c-330ec4e448c9)
</div>

Captures de proves: **configuració d’interfícies**, **resultats de ping** i **muntatge de comparticions** en Windows i Linux.

![Clients (muntatges i accessos)](https://github.com/user-attachments/assets/6e622666-f19e-4617-b766-fc59e595a6a2)
</div>

---

## 4. Configuració de serveis

A continuació es mostren fragments de configuració i explicacions per a cada servei implementat.

### DHCP fitxer de configuració (/etc/dhcp/dhcpd.conf)

![Configuració DHCP (dhcpd.conf)](https://github.com/user-attachments/assets/a26df952-47a8-4a5e-9c8d-e798f4a00059)
</div>

El fitxer defineix **subxarxes**, **rangs d’IP** i **opcions globals** com **DNS** i **gateway**. Inclou **reserves** per a servidors i dispositius de xarxa (exemple: `option domain-name-servers 8.8.8.8;`).

### MySQL creació de la base de dades

![Creació BBDD (sentències SQL)](https://github.com/user-attachments/assets/9bc4d813-f384-4563-afff-7484127ffb04)
</div>

Exemples d’ordres utilitzades per crear la base de dades i l’usuari aplicatiu:

```sql
CREATE DATABASE aplicacio_db;
CREATE USER 'appuser'@'%' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON aplicacio_db.* TO 'appuser'@'%';
FLUSH PRIVILEGES;
```
