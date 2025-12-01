# 2526-P0.0-ASIXc2BC-G05

Sistema d’informació d’**equipaments educatius** desplegat sobre una infraestructura de xarxa segmentada (DMZ, Intranet, NAT) amb serveis web, base de dades, DNS, FTP i scripts de monitorització i backup.

> Aquest document és una **vista general** i una **guia ràpida de desplegament demo**.  
> Per a details tècnics complets, consulta el  
> **[Manual d’Administrador](docs/ADMIN_MANUAL.md)** i el  
> **[Manual d’Usuari](docs/CLIENT_MANUAL.md)**.

---

## Índex

1. [Planificació del projecte](#1-planificació-del-projecte)  
2. [Esquema de xarxa](#2-esquema-de-xarxa)  
3. [Infraestructura desplegada](#3-infraestructura-desplegada)  
4. [Desplegament inicial (demo ràpida)](#4-desplegament-inicial-demo-ràpida)  
5. [Manuals i documentació](#5-manuals-i-documentació)  
6. [Proves de funcionalitat](#6-proves-de-funcionalitat)  
7. [Integració MySQL–PHP i proves de consulta](#7-integració-mysqlphp-i-proves-de-consulta)  
8. [Bibliografia i referències](#8-bibliografia-i-referències)  

---

## 1. Planificació del projecte

Aquest projecte és el treball de pràctiques del **grup G05 (ASIXc2BC)**.

| Element             | Detall                               |
|---------------------|---------------------------------------|
| Grup                | G05 (ASIXc2BC)                       |
| Estructura temporal | 3 sprints de 2 setmanes              |
| Dedicació           | 5 hores setmanals                    |
| Durada total        | 6 setmanes (fins al 01/12)           |
| Repositori          | `2526-P0.0-ASIXc2BC-G05`             |
| Eina de planificació| Proofhub                             |

Enllaç al tauler de tasques (Proofhub):  
[Tauler del projecte a Proofhub](https://itecbcn.proofhub.com/bapplite/#app/todos/project-9335566085/list-269936034851)

---

## 2. Esquema de xarxa

La infraestructura s’organitza en **subxarxes segmentades** per millorar seguretat i gestió:

- Un **router central (R‑NCC)** que interconnecta totes les xarxes.
- **DMZ** per a serveis públics (Web, DNS, FTP).  
- **Intranet** per a serveis interns (Base de dades).  
- **Xarxa NAT** per a clients i servidor DHCP.

**Esquema de xarxa (Packet Tracer):**  
[Descarregar esquema de xarxa](https://drive.google.com/file/d/1sruDIO3lY_b99p6khwERN0n-WELGoI5u/view?usp=sharing)

<img width="1374" height="735" alt="Esquema de xarxa" src="https://github.com/user-attachments/assets/af805f32-8df6-4d3b-9bea-7029340f8a50" />

---

## 3. Infraestructura desplegada

Resum dels principals rols de la infraestructura.  
Els fitxers indicats són **enllaços directes** dins el repositori.

### 3.1 Components principals

| Rol           | Host   | Serveis principals                         |
|---------------|--------|--------------------------------------------|
| Router        | R‑NCC  | Routing, NAT/PAT, firewall bàsic          |
| Web Server    | W‑NCC  | Apache + PHP, aplicació web (`public/`)   |
| DB Server     | B‑NCC  | MySQL/MariaDB, BD `Educacio`              |
| DNS Server    | D‑NCC  | BIND, resolució noms `g5.local`           |
| File/FTP      | F‑NCC  | FTP segur, compartició de fitxers         |
| DHCP          | —      | Assignació IP automàtica a clients        |
| Clients       | CLIWIN / CLILIN | Proves d’accés i serveis        |

---

### 3.2 Router R‑NCC

- Funció:
  - Encaminador entre subxarxes (DMZ, Intranet, NAT).
  - Punt de control central (NAT, redirecció de ports).
- Configuració principal (exemple, comandes i IPs):
  [`router_r-ncc.conf`](files/router_r-ncc.conf)

---

### 3.3 DHCP Server

- Funció:
  - Assignació dinàmica d’adreces IP.
  - Reserves per a clients específics (CLIWIN, CLILIN).
- Configuració actual:
  [`dhcpd.conf`](files/dhcp/dhcpd.conf)

---

### 3.4 Database Server (B‑NCC — MySQL/MariaDB)

- Funció:
  - Hostatge de la base de dades **`Educacio`**.
  - Suport a l’aplicació web (consultes, filtres, etc.).
- Fitxers principals:
  - Script d’inicialització de taules i dades:  
    [`mysql_init.sql`](files/mysql_init.sql)
  - Còpia de seguretat de la BD:  
    [`db_backup.sql`](files/db_backup.sql)

---

### 3.5 Web Server (W‑NCC)

- Funció:
  - Servir l’aplicació web (DocumentRoot: `public/`).
  - Gestionar peticions HTTP/HTTPS.
- Fitxers de configuració:
  - Configuració genèrica de servidor web (exemple):  
    [`webserver_config.conf`](files/webserver_config.conf)
  - VirtualHost Apache per a `g5.local`:  
    [`equipaments.conf`](files/apache2/equipaments.conf)

Lògica d’aplicació (exemple de connexió a la BD):  
[`config.php`](public/includes/config.php)

---

### 3.6 File Server (F‑NCC)

- Funció:
  - Compartició de fitxers (FTP, possiblement SMB).
  - Útil per pujar/baixar fitxers relacionats amb el projecte.

---

### 3.7 Clients de prova

- **CLIWIN**: client Windows per provar:
  - DHCP, resolució DNS, accés web, FTP.
- **CLILIN**: client Linux per provar:
  - DHCP/IP estàtica, ping, traceroute, accés a serveis.

---

## 4. Desplegament inicial (demo ràpida)

> Objectiu: desplegar una **demo funcional** en un **únic servidor Linux** (Apache + PHP + MySQL) per provar l’aplicació web i la BD sense muntar tota la topologia de xarxa.

Per desplegament complet amb rols separats (R‑NCC, W‑NCC, B‑NCC, etc.), consulta el  
**[Manual d’Administrador](docs/ADMIN_MANUAL.md)**.

### 4.1 Requisits bàsics

- Servidor **Linux** (Ubuntu/Debian recomanat) o màquina virtual.
- Accés d’**administrador** (SSH/terminal).
- Connexió a Internet.
- Fitxer [`db_backup.sql`](files/db_backup.sql) accessible (forma part del repo).

---

### 4.2 Passos ràpids

#### 1) Clonar el repositori

```bash
sudo git clone https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05.git /var/www/html
```

> Si `/var/www/html` ja conté fitxers, buida o ajusta el path abans de clonar.

---

#### 2) Instal·lar Apache, PHP i MySQL

```bash
sudo apt update
sudo apt install -y apache2 php php-mysql mysql-server git
```

Comprova que el servei Apache està actiu:

```bash
sudo systemctl status apache2
```

---

#### 3) Crear la base de dades i l’usuari

> Les credencials que es mostren són **exclusivament per a demo/laboratori**.  
> En entorns reals, cal canviar-les.

```bash
sudo mysql -u root -p <<'SQL'
CREATE DATABASE IF NOT EXISTS `Educacio` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER IF NOT EXISTS 'bchecker'@'localhost' IDENTIFIED BY 'bchecker121';
GRANT ALL PRIVILEGES ON `Educacio`.* TO 'bchecker'@'localhost';
FLUSH PRIVILEGES;
SQL
```

---

#### 4) Importar les dades

```bash
mysql -u bchecker -p Educacio < files/db_backup.sql
# Contrasenya: bchecker121
```

---

#### 5) Configurar Apache (VirtualHost + HTTPS de prova)

```bash
sudo cp files/apache2/equipaments.conf /etc/apache2/sites-available/equipaments.conf
sudo a2ensite equipaments.conf
sudo a2enmod ssl headers rewrite
sudo systemctl reload apache2
```

Generar certificat **autofirmat**:

```bash
sudo openssl req -x509 -newkey rsa:4096 -keyout /etc/ssl/private/equipaments.key \
  -out /etc/ssl/certs/equipaments.crt -days 365 -nodes
```

- Revisa que els camins dels certificats a  
  [`equipaments.conf`](files/apache2/equipaments.conf)  
  coincideixin amb els fitxers creats.

---

#### 6) Comprovació final

- Obre un navegador i accedeix a:
  - `http://<IP-del-servidor>`
  - o `https://<domini-o-IP>`

- Si no respon:
  - Revisa Apache: `/var/log/apache2/error.log`
  - Comprova el firewall (p. ex. `ufw`) i permet `80/tcp` i `443/tcp`.

> Recomanació: documentar la IP i el domini utilitzat i afegir-ho al **hosts** del client per facilitar proves (p. ex. `g5.cat`).

---

## 5. Manuals i documentació

Per evitar sobrecarregar aquest README, la documentació detallada es troba en fitxers específics:

### 5.1 Guia per administradors

[`ADMIN_MANUAL.md`](docs/ADMIN_MANUAL.md)

Inclou:

- Configuració detallada de:
  - Router R‑NCC (routing, NAT, iptables).
  - DHCP, DNS (BIND), FTP, Web, Base de dades.
- Configuració de firewall (`ufw`/iptables).
- Scripts de monitorització i backup.
- Taula-resum d’IP i rols.
- Explicació de la normalització de la BD.

---

### 5.2 Guia per usuaris finals

[`CLIENT_MANUAL.md`](docs/CLIENT_MANUAL.md)

Inclou:

- Com accedir a l’aplicació (`https://g5.cat`).
- Com utilitzar les funcionalitats de cerca i filtratge.
- Problemes habituals i solucions bàsiques (connexió, IP, etc.).

---

## 6. Proves de funcionalitat

Taula de proves bàsiques amb captures:

| Servei       | Descripció                                           | Comprovació (imatge) |
|--------------|-------------------------------------------------------|----------------------|
| Router       | Connectivitat entre tots els segments de xarxa.      | (sense imatge)       |
| DHCP         | `ipconfig /renew` a Windows per comprovar respostes. | <img src="https://github.com/user-attachments/assets/0fa7feb0-9d64-49b8-a9be-1ba97135d521" alt="DHCP" width="400" /> |
| SSH          | Accés per SSH des d'una altra màquina.               | <img src="https://github.com/user-attachments/assets/12294633-81be-40ae-a520-f143ecceec5f" alt="SSH" width="400" /> |
| Base de Dades| Validar existència de la BD i llistat de taules.     | <img src="https://github.com/user-attachments/assets/a4668b46-e030-4504-a653-2446b3f09c33" alt="Base de Dades" width="400" /> |
| Web          | Accedir al domini des d'un client extern.            | <img src="https://github.com/user-attachments/assets/e07be053-3257-40ec-9803-fafe2e0ef14e" alt="Web" width="400" /> |
| FTP          | Pujar un fitxer al servidor amb credencials vàlides. | <img src="https://github.com/user-attachments/assets/c432403f-5f0e-43bf-b722-2f60fedc9b51" alt="FTP" width="400" /> |

---

## 7. Integració MySQL–PHP i proves de consulta

La configuració de connexió a la BD es centralitza a:

[`config.php`](public/includes/config.php)

```php
<?php require_once "includes/header.php"; ?>
<?php
// includes/config.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = 'localhost';
$user = 'bchecker';
$pass = 'bchecker121';
$db   = 'Educacio';

try {
    $conn = new mysqli($host, $user, $pass, $db, 3306);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Error de connexió: " . $e->getMessage());
}
?>
```

A partir d’aquesta connexió, les pàgines PHP poden:

- Llistar centres.
- Aplicar filtres (nom, districte, tipus…).
- Fer consultes sobre taules normalitzades (p. ex. `Centres`, `Adreces`, `Filtres`).

> En entorns productius es recomana:
> - Configurar l’usuari de BD amb **mínims privilegis**.
> - Utilitzar variables d’entorn o fitxers de config fora del DocumentRoot.
> - Activar logs d’errors PHP limitats i protegits.

---

## 8. Bibliografia i referències

- Mostrar dades MySQL amb PHP:  
  [SiteGround: Mostrar dades de taules MySQL amb PHP](https://www.siteground.es/tutoriales/php-mysql/mostrar-datos-tablas-mysql/)

- Configurar un router Linux (IP forwarding):  
  [Deephacking: Configurar Linux com a router (IP forwarding)](https://deephacking.tech/configurar-linux-para-que-actue-como-router-ip-forwarding/)

- Desplegar servidor FTP en Ubuntu:  
  [IONOS: Servidor FTP en Ubuntu — instal·lació i configuració](https://www.ionos.com/es-us/digitalguide/servidores/configuracion/servidor-ftp-en-ubuntu-instalacion-y-configuracion/)

- Configurar servidor Web amb domini local:  
  [DigitalOcean: Apache Virtual Hosts a Ubuntu 18.04 (ES)](https://www.digitalocean.com/community/tutorials/how-to-set-up-apache-virtual-hosts-on-ubuntu-18-04-es)

- Instalar mysql a Ubuntu Server
  [InstalarMySQL al Ubuntu](https://www.digitalocean.com/community/tutorials/how-to-install-mysql-on-ubuntu-20-04-es)

- Manual PHP
  [Manual General PHP](https://www.php.net/manual/es/)

- Manual Apache
  [Manual com configurar Apache](https://httpd.apache.org/docs/)

  

---
