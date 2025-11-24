# 2526-P0.0-ASIXc2BC-G05

Índex

1. [Planificació del projecte](#1-planificació-del-projecte)  
2. [Esquema de xarxa](#2-esquema-de-xarxa)  
3. [Infraestructura desplegada](#3-infraestructura-desplegada)  
4. [Desplegament inicial (demo)](#4-desplegament-inicial-demo)  
5. [Manuals i documentació](#5-manuals-i-documentació)  
6. [Fitxers clau](#6-fitxers-clau)  
7. [Bibliografia i referències](#7-bibliografia-i-referències)

---

## 1. Planificació del projecte

Aquest projecte és el treball de pràctiques del **grup G05 (ASIXc2BC)**.

- Eina de planificació: **Proofhub**  
- Estructura: **3 sprints** de **2 setmanes**  
- Dedicació: **5 hores setmanals**  
- Durada total: **6 setmanes** (fins al **18/11**)  
- Repositori principal: **P0.0-ASIXc2BC-G05**

Enllaç al tauler de tasques (Proofhub):  
https://itecbcn.proofhub.com/bapplite/#app/todos/project-9335566085/list-269936034851

---

## 2. Esquema de xarxa

L’arquitectura del projecte està basada en:

- Un **router central (R‑NCC)**  
- **VLAN de serveis** per als servidors  
- **VLANs separades** per als clients  
- Adreçament IP separat per facilitar **escalabilitat** i **gestió**

**Descarregar esquema de xarxa (Packet Tracer):**  
https://drive.google.com/file/d/1sruDIO3lY_b99p6khwERN0n-WELGoI5u/view?usp=sharing

Imatge de referència (topologia):  
![Esquema de xarxa](https://github.com/user-attachments/assets/bae3db11-eba9-46ba-a99c-0463bbbf78d0)

---

## 3. Infraestructura desplegada

Resum dels principals components de la infraestructura:

### Router R‑NCC
- Funció:  
  - Encaminador entre subxarxes  
  - Punt de control entre VLANs  
- Configuració: `./files/router_r-ncc.conf`

### DHCP Server
- Funció:  
  - Assignació dinàmica d'adreces IP  
  - Reserves per a màquines específiques  
- Configuració: `./files/dhcp/dhcpd.conf`

### Database Server (B‑NCC — MySQL/MariaDB)
- Funció:  
  - Hostatge de la base de dades `Educacio`  
  - Execució d’scripts d’inicialització  
  - Gestió de còpies de seguretat  
- Fitxers principals:  
  - Inicialització: `./files/mysql_init.sql`  
  - Backup: `./files/backup_mysql.sh`

### Web Server (W‑NCC)
- Funció:  
  - Servir l’aplicació web (DocumentRoot: `public/`)  
  - Connexió amb la base de dades `Educacio`  
- Configuració d’exemple:  
  - `./files/webserver_config.conf`  
  - `./files/apache2/equipaments.conf` (virtualhost Apache)

### File Server (F‑NCC)
- Funció:  
  - Compartició de fitxers a través de la DMZ

### Clients de prova
- **CLIWIN**: client Windows (DHCP, accés a recursos de xarxa)  
- **CLILIN**: client Linux (DHCP o IP estàtica, proves de connectivitat)

Tots els fitxers de configuració i scripts es troben a la carpeta `files/` del repositori.

---

## 4. Desplegament inicial (demo)

Apartat pensat perquè qualsevol administrador pugui desplegar ràpidament la demo en un entorn de proves.

### Requisits bàsics

- Servidor **Linux** (Ubuntu/Debian recomanat) o màquina virtual  
- Accés d’**administrador** (SSH/terminal)  
- Connexió a Internet  
- (Opcional) Fitxer `backup.sql` per carregar dades d’exemple

---

### Passos ràpids de desplegament

#### 1) Clonar el repositori al directori públic del servidor web

```bash
sudo git clone https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05.git /var/www/html
```

#### 2) Instal·lar el mínim necessari (Apache, PHP, MySQL)

```bash
sudo apt update
sudo apt install -y apache2 php php-mysql mysql-server git
```

#### 3) Crear la base de dades i l’usuari per a la demo

> La contrasenya és d’exemple. **Canvia-la en entorns reals.**

```bash
sudo mysql -u root -p <<'SQL'
CREATE DATABASE IF NOT EXISTS `Educacio` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER IF NOT EXISTS 'bchecker'@'localhost' IDENTIFIED BY 'bchecker121';
GRANT ALL PRIVILEGES ON `Educacio`.* TO 'bchecker'@'localhost';
FLUSH PRIVILEGES;
SQL
```

#### 4) Importar dades d’exemple (opcional)

Si disposes de `backup.sql`:

```bash
sudo mysql -u root -p Educacio < backup.sql
```

#### 5) Configurar Apache i HTTPS bàsic per a la demo

```bash
sudo cp files/apache2/equipaments.conf /etc/apache2/sites-available/equipaments.conf
sudo a2ensite equipaments.conf
sudo a2enmod ssl headers rewrite
sudo systemctl reload apache2
```

Generar un certificat **autofirmat** per a proves:

```bash
sudo openssl req -x509 -newkey rsa:4096 -keyout /etc/ssl/private/equipaments.key \
  -out /etc/ssl/certs/equipaments.crt -days 365 -nodes
```

Assegura’t que els camins del certificat (`equipaments.crt` / `equipaments.key`) coincideixen amb els definits al VirtualHost.

#### 6) Comprovació final

- Obre un navegador i accedeix a:  
  - `http://<IP-del-servidor>`  
  - o `https://<domini-o-IP>`

- Si hi ha problemes:  
  - Revisa els logs d’Apache: `/var/log/apache2/error.log`

> **Nota:** Aquest procediment està pensat per a **entorns de proves**. En entorns productius cal:  
> - Canviar contrasenyes per defecte  
> - Restringir l’accés a la base de dades  
> - Utilitzar certificats vàlids (p. ex. Let’s Encrypt)  
> - Aplicar polítiques de seguretat adequades (firewall, actualitzacions, etc.)

---

## 5. Manuals i documentació

Per mantenir aquest README senzill i visual, la documentació detallada s’ha separat en manuals específics:

- **Guia per administradors (admins):**  
  `docs/documentacio_admin.md`  
  Conté:  
  - Configuració avançada de xarxa i serveis  
  - Gestió de còpies de seguretat  
  - Manteniment de l’aplicació i dels servidors  

- **Guia per usuaris (users):**  
  `docs/documentacio_cli.md`  
  Conté:  
  - Com accedir a l’aplicació  
  - Funcionament bàsic de la interfície  
  - Com consultar la informació i utilitzar les funcionalitats principals

---

## 6. Fitxers clau

En aquesta secció es recullen els fitxers més importants, amb enllaç directe:

- **router_r-ncc.conf** — configuració del router  
  - Ruta: `./files/router_r-ncc.conf`  
  - [Descarregar router_r-ncc.conf](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/router_r-ncc.conf)

- **dhcpd.conf** — configuració del servidor DHCP  
  - Ruta: `./files/dhcp/dhcpd.conf`  
  - [Descarregar dhcpd.conf](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/dhcp/dhcpd.conf)

- **mysql_init.sql** — creació i estructura inicial de la BD `Educacio`  
  - Ruta: `./files/mysql_init.sql`  
  - [Descarregar mysql_init.sql](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/mysql_init.sql)

- **backup_mysql.sh** — script de còpia de seguretat  
  - Ruta: `./files/backup_mysql.sh`  
  - [Descarregar backup_mysql.sh](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/backup_mysql.sh)

- **Configuració del servidor web**  
  - `./files/webserver_config.conf`  
    - [Descarregar webserver_config.conf](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/webserver_config.conf)  
  - `./files/apache2/equipaments.conf`  
    - [Descarregar equipaments.conf](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/apache2/equipaments.conf)

---

## 7. Bibliografia i referències

- **Mostrar dades MySQL amb PHP:**  
  [Tutorial de SiteGround](https://www.siteground.es/tutoriales/php-mysql/mostrar-datos-tablas-mysql/)

- **Configurar un router Linux (IP forwarding):**  
  [Guia Deephacking](https://deephacking.tech/configurar-linux-para-que-actue-como-router-ip-forwarding/)

- **Desplegar servidor FTP en Ubuntu:**  
  [Guia IONOS — instal·lació i configuració FTP](https://www.ionos.com/es-us/digitalguide/servidores/configuracion/servidor-ftp-en-ubuntu-instalacion-y-configuracion/)
