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

He reorganitzat les captures per fer la lectura més amena: algunes imatges van en columna a la dreta, altres a l'esquerra, unes més petites i altres més grans, amb llegendes explicatives.

### Router R-NCC

<div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-start;">
  <figure style="flex:1 1 48%; margin:0;">
    <!-- Si tens una imatge del router, canvia la URL -->
    <img src="https://github.com/user-attachments/assets/placeholder-router.png" alt="Router R-NCC" style="width:100%; height:auto; border:1px solid #ddd; padding:4px;">
    <figcaption style="font-size:0.9em; color:#444; margin-top:6px;">Router R-NCC — encaminament entre VLANs, rutes i NAT si cal.</figcaption>
  </figure>

  <div style="flex:1 1 48%; min-width:220px;">
    <p style="margin-top:0;">Descripció del Router R-NCC:</p>
    <ul>
      <li>Encamina les subxarxes internes i actua com a gateway per a sortida si cal.</li>
      <li>Interfícies amb IPs per a cada VLAN, rutes estàtiques i regles de tallafocs bàsiques.</li>
    </ul>
  </div>
</div>

---

### DHCP Server

<div style="display:flex; gap:1rem; flex-wrap:wrap;">
  <figure style="flex:1 1 32%; margin:0;">
    <img src="https://github.com/user-attachments/assets/5f981a11-8565-4214-b966-b415ec1f0aa3" alt="DHCP Server" style="width:100%; height:auto; border:1px solid #ddd; padding:4px;">
    <figcaption style="font-size:0.85em; color:#444; margin-top:6px;">Interfície de configuració del servidor DHCP.</figcaption>
  </figure>

  <figure style="flex:1 1 64%; margin:0;">
    <img src="https://github.com/user-attachments/assets/416d0ece-f402-453e-94bf-5b63f4b9742f" alt="Aplicar IP CLIWIN" style="width:48%; height:auto; border:1px solid #ddd; padding:4px; margin-right:2%;">
    <img src="https://github.com/user-attachments/assets/placeholder-clilin.png" alt="Aplicar IP CLILIN" style="width:48%; height:auto; border:1px solid #ddd; padding:4px;">
    <figcaption style="font-size:0.85em; color:#444; margin-top:6px;">A l'esquerra: CLIWIN amb IP aplicada. A la dreta: CLILIN (captura no disponible, s'ha descrit en text).</figcaption>
  </figure>
</div>

Explicació:
- Rangs d'adreces, opcions i reserves per a servidors estan en el fitxer dhcpd.conf (veure Secció 4).

---

### Database Server B-NCC & Web Server W-NCC

<div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-start;">
  <figure style="flex:1 1 48%; margin:0;">
    <img src="https://github.com/user-attachments/assets/91df8564-9cf4-4e05-aa68-cafbcc95e472" alt="Database Server B-NCC" style="width:100%; height:auto; border:1px solid #ddd; padding:4px;">
    <figcaption style="font-size:0.85em; color:#444; margin-top:6px;">Servidor BBDD: estat i consultes de prova.</figcaption>
  </figure>

  <figure style="flex:1 1 48%; margin:0;">
    <div style="display:flex; gap:0.5rem; flex-direction:column;">
      <img src="https://github.com/user-attachments/assets/af5c4b39-2aa8-4296-9ccc-3d070ed0ceb5" alt="Web Server 1" style="width:100%; height:auto; border:1px solid #ddd; padding:4px;">
      <img src="https://github.com/user-attachments/assets/65501edd-9235-4c96-b090-8cf65ce86956" alt="Web Server 2" style="width:100%; height:auto; border:1px solid #ddd; padding:4px; margin-top:6px;">
      <figcaption style="font-size:0.85em; color:#444; margin-top:6px;">Servidor Web: pàgina inicial i proves d'accés i connexió amb la BBDD.</figcaption>
    </div>
  </figure>
</div>

---

### File Server F-NCC

<div style="display:flex; gap:1rem; flex-wrap:wrap;">
  <figure style="flex:1 1 32%; margin:0;">
    <img src="https://github.com/user-attachments/assets/211a4257-f1f7-44bf-867b-4d86b34adac8" alt="File Server 1" style="width:100%; height:auto; border:1px solid #ddd; padding:4px;">
  </figure>

  <figure style="flex:1 1 32%; margin:0;">
    <img src="https://github.com/user-attachments/assets/b4742b48-9bf8-4428-8813-ab1be867ebdc" alt="File Server 2" style="width:100%; height:auto; border:1px solid #ddd; padding:4px;">
  </figure>

  <figure style="flex:1 1 32%; margin:0;">
    <img src="https://github.com/user-attachments/assets/1bbe8d27-f2f8-4d51-90b4-1d4010cf4fb3" alt="File Server 4" style="width:100%; height:auto; border:1px solid #ddd; padding:4px;">
  </figure>

  <figcaption style="font-size:0.85em; color:#444; margin-top:6px;">Servidor de fitxers: estructura de directoris, permisos i exemples d'accés des de clients.</figcaption>
</div>

---

### Clients CLIWIN & CLILIN

<div style="display:flex; gap:1rem; flex-wrap:wrap;">
  <figure style="flex:1 1 48%; margin:0;">
    <img src="https://github.com/user-attachments/assets/caf48c54-ff97-4b35-a357-fea9f64cbae5" alt="Clients CLI 1" style="width:100%; height:auto; border:1px solid #ddd; padding:4px;">
    <figcaption style="font-size:0.85em; color:#444; margin-top:6px;">CLIWIN: obtenció d'IP via DHCP i proves de connectivitat.</figcaption>
  </figure>

  <figure style="flex:1 1 48%; margin:0;">
    <img src="https://github.com/user-attachments/assets/a0ccc30e-6beb-4853-978c-330ec4e448c9" alt="Clients CLI 2" style="width:100%; height:auto; border:1px solid #ddd; padding:4px;">
    <figcaption style="font-size:0.85em; color:#444; margin-top:6px;">CLILIN: comandes `ip addr`, `ping` i muntatge de recursos compartits.</figcaption>
  </figure>
</div>

---

## 4.Configuració de serveis

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

Taula d'aplicació d'IP als clients:

| Aplicar IP CLIWIN | Aplicar IP CLILIN |
|:-----------------:|:----------------:|
| Imatge: CLIWIN amb IP assignada per DHCP (veure captura) | Imatge no disponible — descripció: sortida de `ip addr` mostrant la IP i `ip route` amb el gateway configurat. |

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
