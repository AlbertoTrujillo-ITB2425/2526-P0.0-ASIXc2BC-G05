# 2526-P0.0-ASIXc2BC-G05

## Índex

1. [Planificació del projecte](#planificació-del-projecte)
2. [Esquema de xarxa](#esquema-de-xarxa)
    - [Descàrrega de l'esquema](#descàrrega-de-lesquema)
    - [Visualització de l'esquema](#visualització-de-la-esquema)
3. [Infraestructura desplegada](#infraestructura-desplegada)
4. [Configuració de serveis](#configuració-de-serveis)

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

<div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-start;">
  <figure style="flex:1 1 48%; margin:0;">
    <img src="https://github.com/user-attachments/assets/12fdae6a-c0b8-4ae6-8dcf-2a22dcaad1b4" alt="Esquema de Xarxa" style="width:100%; height:auto; border:1px solid #ddd; padding:4px;">
    <figcaption style="font-size:0.9em; color:#444; margin-top:6px;">Diagrama general: router R-NCC enlaça VLANs de serveis i clients; servidors centrals i clients finals.</figcaption>
  </figure>
  <figure style="flex:1 1 48%; margin:0;">
    <div style="display:flex; gap:0.5rem; flex-direction:column;">
      <img src="https://github.com/user-attachments/assets/5f981a11-8565-4214-b966-b415ec1f0aa3" alt="DHCP Server" style="width:100%; height:auto; border:1px solid #ddd; padding:4px;">
      <figcaption style="font-size:0.85em; color:#444; margin-top:6px;">Servidor DHCP: rangs, reserves i opcions configurades.</figcaption>
    </div>
  </figure>
</div>

Descripció resumida:
- Topologia amb R-NCC com a encaminador central i servidors en VLAN de serveis.
- Plans d'adreçament IP separats per facilitar escalabilitat i gestió.

---

## 3. Infraestructura desplegada

He ajustat la disposició perquè, en aquesta secció, les explicacions quedin sempre al lateral esquerre i les fotos al lateral dret. Això s'aplica a cada subsecció: Router, DHCP, BBDD/Web, File Server i Clients.

### Router R-NCC

<div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-start;">
  <!-- Text a l'esquerra -->
  <div style="flex:1 1 48%; min-width:260px;">
    <h4 style="margin-top:0;">Router R-NCC</h4>
    <p>Descripció del Router R-NCC:</p>
    <ul>
      <li>Encamina les subxarxes internes i actua com a gateway per a sortida si cal.</li>
      <li>Interfícies amb IPs per a cada VLAN, rutes estàtiques i regles de tallafocs bàsiques.</li>
      <li>Serveix com a punt de control per a polítiques d'accés entre VLANs i la xarxa de serveis.</li>
    </ul>
  </div>

  <!-- Imatges a la dreta -->
  <figure style="flex:1 1 48%; margin:0; text-align:right;">
    <img src="https://github.com/user-attachments/assets/placeholder-router.png" alt="Router R-NCC" style="width:90%; height:auto; border:1px solid #ddd; padding:4px;">
    <figcaption style="font-size:0.9em; color:#444; margin-top:6px;">Vista del router amb les interfícies i rutes configurades.</figcaption>
  </figure>
</div>

---

### DHCP Server

<div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-start;">
  <!-- Text a l'esquerra -->
  <div style="flex:1 1 48%; min-width:260px;">
    <h4 style="margin-top:0;">DHCP Server</h4>
    <p>El servidor DHCP proporciona adreces IP dinàmiques als clients de les VLANs definides. Principals punts:</p>
    <ul>
      <li>Rangs d'IP per subxarxa i reserves per a servidors i dispositius de xarxa.</li>
      <li>Opcions com DNS, gateway i temps de concessió (lease).</li>
      <li>S'han evitat solapaments amb IPs estàtiques.</li>
    </ul>
  </div>

  <!-- Imatges a la dreta: una gran i una petita sota -->
  <figure style="flex:1 1 48%; margin:0; text-align:right;">
    <img src="https://github.com/user-attachments/assets/5f981a11-8565-4214-b966-b415ec1f0aa3" alt="DHCP Server" style="width:70%; height:auto; border:1px solid #ddd; padding:4px; display:block; margin-left:auto;">
    <img src="https://github.com/user-attachments/assets/416d0ece-f402-453e-94bf-5b63f4b9742f" alt="Aplicar IP CLIWIN" style="width:45%; height:auto; border:1px solid #ddd; padding:4px; display:inline-block; margin-top:8px;">
    <figcaption style="font-size:0.85em; color:#444; margin-top:6px;">Configuració del DHCP i exemple CLIWIN amb IP aplicada (a la dreta, CLILIN descrit en text si la captura no està disponible).</figcaption>
  </figure>
</div>

---

### Database Server B-NCC & Web Server W-NCC

<div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-start;">
  <!-- Text a l'esquerra -->
  <div style="flex:1 1 48%; min-width:260px;">
    <h4 style="margin-top:0;">Database Server B-NCC & Web Server W-NCC</h4>
    <p>Descripció i funcions:</p>
    <ul>
      <li>B-NCC: allotja la base de dades principal (MySQL/MariaDB), còpies de seguretat i permisos limitats.</li>
      <li>W-NCC: servidor web que serveix contingut dinàmic i es connecta amb la BBDD mitjançant l'usuari aplicatiu.</li>
      <li>Proves realitzades: consultes de prova, connexions des del web i comprovació de rutes entre servidors.</li>
    </ul>
  </div>

  <!-- Imatges a la dreta (apilades) -->
  <figure style="flex:1 1 48%; margin:0; text-align:right;">
    <img src="https://github.com/user-attachments/assets/91df8564-9cf4-4e05-aa68-cafbcc95e472" alt="Database Server B-NCC" style="width:80%; height:auto; border:1px solid #ddd; padding:4px; display:block; margin-left:auto;">
    <img src="https://github.com/user-attachments/assets/af5c4b39-2aa8-4296-9ccc-3d070ed0ceb5" alt="Web Server 1" style="width:80%; height:auto; border:1px solid #ddd; padding:4px; display:block; margin-left:auto; margin-top:8px;">
    <figcaption style="font-size:0.85em; color:#444; margin-top:6px;">Estat i consultes de la BBDD; pàgina web i proves d'accés.</figcaption>
  </figure>
</div>

---

### File Server F-NCC

<div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-start;">
  <!-- Text a l'esquerra -->
  <div style="flex:1 1 48%; min-width:260px;">
    <h4 style="margin-top:0;">File Server F-NCC</h4>
    <p>El servidor de fitxers proporciona compartir SMB/CIFS, gestió d'usuaris i permisos. Punts clau:</p>
    <ul>
      <li>Estructura de directoris i permisos definits per grups d'usuaris.</li>
      <li>Quotas, còpies de seguretat i comprovacions d'integritat implementades.</li>
      <li>Proves des de clients per validar lectura i escriptura amb diferents usuaris.</li>
    </ul>
  </div>

  <!-- Galeria dreta amb 3 imatges alineades verticalment -->
  <figure style="flex:1 1 48%; margin:0; text-align:right;">
    <img src="https://github.com/user-attachments/assets/211a4257-f1f7-44bf-867b-4d86b34adac8" alt="File Server 1" style="width:70%; height:auto; border:1px solid #ddd; padding:4px; display:block; margin-left:auto;">
    <img src="https://github.com/user-attachments/assets/b4742b48-9bf8-4428-8813-ab1be867ebdc" alt="File Server 2" style="width:70%; height:auto; border:1px solid #ddd; padding:4px; display:block; margin-left:auto; margin-top:8px;">
    <img src="https://github.com/user-attachments/assets/1bbe8d27-f2f8-4d51-90b4-1d4010cf4fb3" alt="File Server 4" style="width:70%; height:auto; border:1px solid #ddd; padding:4px; display:block; margin-left:auto; margin-top:8px;">
    <figcaption style="font-size:0.85em; color:#444; margin-top:6px;">Estructura de directoris, permisos i exemples d'accés des de clients.</figcaption>
  </figure>
</div>

---

### Clients CLIWIN & CLILIN

<div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-start;">
  <!-- Text a l'esquerra -->
  <div style="flex:1 1 48%; min-width:260px;">
    <h4 style="margin-top:0;">Clients CLIWIN & CLILIN</h4>
    <p>Proves i comportament dels clients:</p>
    <ul>
      <li>CLIWIN: obtenció d'IP via DHCP, ping a servidors i accés a recursos compartits.</li>
      <li>CLILIN: comandes `ip addr`, `ip route`, `ping` i muntatge de comparticions SMB/NFS segons configuració.</li>
      <li>Validació de permisos i connectivitat en diferents escenaris.</li>
    </ul>
  </div>

  <!-- Imatges a la dreta, dues columnas petites -->
  <figure style="flex:1 1 48%; margin:0; text-align:right;">
    <div style="display:flex; justify-content:flex-end; gap:8px; flex-wrap:wrap;">
      <img src="https://github.com/user-attachments/assets/caf48c54-ff97-4b35-a357-fea9f64cbae5" alt="Clients CLI 1" style="width:48%; height:auto; border:1px solid #ddd; padding:4px;">
      <img src="https://github.com/user-attachments/assets/a0ccc30e-6beb-4853-978c-330ec4e448c9" alt="Clients CLI 2" style="width:48%; height:auto; border:1px solid #ddd; padding:4px;">
      <img src="https://github.com/user-attachments/assets/6e622666-f19e-4617-b766-fc59e595a6a2" alt="Clients CLI 3" style="width:48%; height:auto; border:1px solid #ddd; padding:4px; margin-top:8px;">
    </div>
    <figcaption style="font-size:0.85em; color:#444; margin-top:6px;">Proves de connectivitat i configuració de xarxa als clients Windows i Linux.</figcaption>
  </figure>
</div>

---

## 4. Configuració de serveis

A continuació es mostren fragments de configuració i explicacions per a cada servei implementat, junt amb captures quan és rellevant.

### DHCP Fitxer de configuració (`/etc/dhcp/dhcpd.conf`):

<div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-start;">
  <figure style="flex:1 1 48%; margin:0;">
    <img src="https://github.com/user-attachments/assets/a26df952-47a8-4a5e-9c8d-e798f4a00059" alt="Configuració DHCP" style="width:100%; height:auto; border:1px solid #ddd; padding:4px;">
    <figcaption style="font-size:0.85em; color:#444; margin-top:6px;">Contingut del fitxer dhcpd.conf amb subxarxes i pools.</figcaption>
  </figure>

  <div style="flex:1 1 48%; min-width:220px;">
    <p style="margin-top:0;">Explicació del fitxer:</p>
    <ul>
      <li>Definició de subxarxes, rangs d'IP i opcions globals (DNS, gateway).</li>
      <li>Reserves per servidors i dispositius de xarxa.</li>
      <li>Exemple: <code>option domain-name-servers 8.8.8.8;</code></li>
    </ul>
  </div>
</div>

---

### MySQL Creacio de la base de dades

<div style="display:flex; gap:1rem; flex-wrap:wrap;">
  <figure style="flex:1 1 48%; margin:0;">
    <img src="https://github.com/user-attachments/assets/9bc4d813-f384-4563-afff-7484127ffb04" alt="Creació BBDD" style="width:100%; height:auto; border:1px solid #ddd; padding:4px;">
    <figcaption style="font-size:0.85em; color:#444; margin-top:6px;">Ordres SQL d'exemple per crear la base de dades i l'usuari aplicatiu.</figcaption>
  </figure>

  <div style="flex:1 1 48%; min-width:220px;">
    <p style="margin-top:0;">Exemples resumits d'ordres utilitzades:</p>
    <pre style="background:#f6f8fa; padding:8px; border:1px solid #e1e4e8; overflow:auto;">CREATE DATABASE aplicacio_db;
CREATE USER 'appuser'@'%' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON aplicacio_db.* TO 'appuser'@'%';
FLUSH PRIVILEGES;</pre>
  </div>
</div>
