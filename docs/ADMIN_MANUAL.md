# Manual d’Administrador — 2526-P0.0-ASIXc2BC-G05

## Índex

1. [Visió general del projecte](#1-visió-general-del-projecte)  
2. [Esquema de xarxa](#2-esquema-de-xarxa)  
3. [Infraestructura desplegada](#3-infraestructura-desplegada)  
4. [Guia ràpida de desplegament](#4-guia-ràpida-de-desplegament)  
5. [Configuració de serveis](#5-configuració-de-serveis)  
   - [DHCP](#51-configuració-dhcp)  
   - [Router R-NCC (Linux router + NAT)](#52-configuració-del-router-r-ncc)  
   - [Servidor FTP / File Server](#53-servidor-ftp--file-server-f-ncc)  
   - [DNS](#54-configuració-dns-d-ncc)  
   - [Web + Base de dades](#55-web--base-de-dades)  
6. [Seguretat i firewall](#6-seguretat-i-firewall)  
7. [Monitorització i backups](#7-monitorització-i-backups)  
8. [Taula-resum de la topologia](#8-taula-resum-de-la-topologia)  
9. [Dades susceptibles de separació](#9-dades-susceptibles-de-separació)  

---

## 1. Visió general del projecte

Projecte de pràctiques del **grup G05 (ASIXc2BC)**, que desplega una infraestructura de xarxa amb:

- Router central (R‑NCC).
- Xarxa **DMZ** amb serveis públics (web, DNS, FTP).
- Xarxa **Intranet** amb base de dades.
- Xarxa **NAT** per a clients i servidor DHCP.
- Scripts de **monitorització** i **backup**.

Planificació del projecte:  
[Tauler de tasques a Proofhub](https://itecbcn.proofhub.com/bapplite/#app/todos/project-9335566085/list-269936034851)

> Per a un desplegament **demo ràpid** en un sol servidor, consulta el  
> [`README.md`](../README.md#4-desplegament-inicial-demo).

---

## 2. Esquema de xarxa

Arquitectura dividida en 3 subxarxes (totes /26) interconnectades pel router R‑NCC.

- **NAT (clients + DHCP)** — `192.168.5.128/26`  
- **DMZ (serveis públics)** — `192.168.5.0/26`  
- **Intranet (serveis interns)** — `192.168.5.64/26`  

Diagrama complet (Packet Tracer):  
[Esquema de xarxa](https://drive.google.com/file/d/1sruDIO3lY_b99p6khwERN0n-WELGoI5u/view?usp=sharing)

---

## 3. Infraestructura desplegada

| Rol                         | Host   | IP / Xarxa           | Lloc          |
|-----------------------------|--------|----------------------|---------------|
| Router WAN/DMZ/Intranet/NAT | R‑NCC  | 192.168.5.1 / .65 / .129 | Router central |
| Web Server                  | W‑NCC  | 192.168.5.20/26 (DMZ)   | Apache + PHP  |
| DNS Server                  | D‑NCC  | 192.168.5.30/26 (DMZ)   | BIND          |
| File/FTP Server             | F‑NCC  | 192.168.5.40/26 (DMZ)   | FTP / fitxers |
| DB Server                   | B‑NCC  | 192.168.5.80/26 (Intranet) | MySQL/MariaDB |
| DHCP Server                 |        | 192.168.5.140/26 (NAT)  | isc-dhcp-server |
| Client Windows              | CLIWIN | 192.168.5.130/26 (NAT)  | Client        |
| Client Linux                | CLILIN | 192.168.5.131/26 (NAT)  | Client        |

---

## 4. Guia ràpida de desplegament

Aquesta secció és una **checklist**. Si alguna cosa dona error, revisa la subsecció específica (DHCP, DNS, etc.).

### 4.1 Ordre recomanat

1. [Configurar el router R‑NCC](#52-configuració-del-router-r-ncc)  
2. [Configurar DHCP a la xarxa NAT](#51-configuració-dhcp)  
3. [Configurar DNS (D‑NCC)](#54-configuració-dns-d-ncc)  
4. [Configurar Web + DB](#55-web--base-de-dades)  
5. [Configurar File/FTP Server](#53-servidor-ftp--file-server-f-ncc)  
6. [Aplicar regles de firewall](#6-seguretat-i-firewall)  
7. [Activar monitorització i backups](#7-monitorització-i-backups)  

---

### 4.2 Comprovacions bàsiques

Des de cada servidor comprova:

```bash
# 1) IP correcta i interfície activa
ip addr show

# 2) Ping al router
ping -c 2 192.168.5.1

# 3) Ping al DNS
ping -c 2 192.168.5.30

# 4) Resolució DNS (un cop BIND estigui configurat)
dig web.g5.local @192.168.5.30
```

Si alguna d’aquestes proves falla, arregla IP/xarxa o DNS abans de continuar.

---

## 5. Configuració de serveis

### 5.1 Configuració DHCP

**Host:** DHCP Server — `192.168.5.140/26` (xarxa NAT)  
**Fitxer del projecte:** [`dhcpd.conf`](../files/dhcp/dhcpd.conf)

Instal·lació:

```bash
sudo apt install -y isc-dhcp-server
sudo nano /etc/dhcp/dhcpd.conf
```

Exemple de configuració (ús actual al projecte):

```bash
option domain-name "g5.local";
option domain-name-servers 192.168.5.30;
default-lease-time 86400;
max-lease-time 604800;
authoritative;

subnet 192.168.5.128 netmask 255.255.255.192 {
  range 192.168.5.132 192.168.5.139;
  option routers 192.168.5.129;
  option subnet-mask 255.255.255.192;
  option domain-name-servers 192.168.5.30;
  option broadcast-address 192.168.5.191;
}

host CLILIN {
  hardware ethernet 52:54:00:39:be:b1;
  fixed-address 192.168.5.131;
}

host CLIWIN {
  hardware ethernet 52:54:00:1E:47:7A;
  fixed-address 192.168.5.130;
}
```

Activar servei:

```bash
sudo systemctl enable isc-dhcp-server
sudo systemctl restart isc-dhcp-server
sudo systemctl status isc-dhcp-server
```

Si tens errors:

- Mira `/var/log/syslog` o `/var/log/dhcpd.log`  
- Confirma que la interfície és correcta a `/etc/default/isc-dhcp-server` (`INTERFACESv4="enp0sX"`).  

---

### 5.2 Configuració del Router R-NCC

**Host:** R‑NCC (Linux actuant com a router)  
**Fitxer de referència al projecte:**  
[`router_r-ncc.conf`](../files/router_r-ncc.conf)

#### Passos essencials

1. **Assignar IPs a les interfícies:**

```bash
sudo ip addr add 192.168.5.1/26   dev enp0s8   # DMZ
sudo ip addr add 192.168.5.65/26  dev enp0s9   # Intranet
sudo ip addr add 192.168.5.129/26 dev enp0s10  # NAT
sudo ip link set enp0s8 up
sudo ip link set enp0s9 up
sudo ip link set enp0s10 up
```

2. **Activar IP forwarding:**

```bash
echo 1 | sudo tee /proc/sys/net/ipv4/ip_forward
sudo sed -i 's/^#net.ipv4.ip_forward=1/net.ipv4.ip_forward=1/' /etc/sysctl.conf
sudo sysctl -p
```

3. **Configurar NAT/PAT cap a Internet (WAN enp0s3):**

```bash
sudo iptables -t nat -A POSTROUTING -s 192.168.5.0/24 -o enp0s3 -j MASQUERADE
```

4. **Redirecció de ports cap a serveis de la DMZ:**

```bash
# HTTP/HTTPS → Web Server
sudo iptables -t nat -A PREROUTING -i enp0s3 -p tcp --dport 80  -j DNAT --to 192.168.5.20:80
sudo iptables -t nat -A PREROUTING -i enp0s3 -p tcp --dport 443 -j DNAT --to 192.168.5.20:443

# FTP/SSH → File Server
sudo iptables -t nat -A PREROUTING -i enp0s3 -p tcp --dport 21  -j DNAT --to 192.168.5.40:21
sudo iptables -t nat -A PREROUTING -i enp0s3 -p tcp --dport 22  -j DNAT --to 192.168.5.40:22
```

Fer-ho persistent:

```bash
sudo apt install -y iptables-persistent
sudo iptables-save | sudo tee /etc/iptables/rules.v4
```

Si hi ha problemes de navegació des de clients, comprova:

- IPs i gateways dels clients.  
- Que `ip_forward` estigui a 1.  
- Que no hi hagi un altre firewall bloquejant el tràfic.

---

### 5.3 Servidor FTP / File Server (F-NCC)

**Host:** F‑NCC — `192.168.5.40/26`  
**Objectiu:** FTP segur + possible SMB per a xarxa interna.

1. **Instal·lar vsftpd:**

```bash
sudo apt install -y vsftpd openssl
```

2. **Generar certificat per FTPS (si cal):**

```bash
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/ssl/private/vsftpd.key \
  -out /etc/ssl/certs/vsftpd.crt
```

3. **Configurar `/etc/vsftpd.conf`:** (resum)

```bash
listen=YES
listen_ipv6=NO
anonymous_enable=NO
local_enable=YES
write_enable=YES
chroot_local_user=YES
allow_writeable_chroot=YES

listen_address=192.168.5.40
pasv_enable=YES
pasv_min_port=40000
pasv_max_port=50000
pasv_address=192.168.5.40

ssl_enable=YES
rsa_cert_file=/etc/ssl/certs/vsftpd.crt
rsa_private_key_file=/etc/ssl/private/vsftpd.key

xferlog_enable=YES
log_ftp_protocol=YES
```

4. **Reiniciar servei:**

```bash
sudo systemctl restart vsftpd
sudo systemctl status vsftpd
```

Revisa `/var/log/vsftpd.log` i `/var/log/syslog` si hi ha errors.

---

### 5.4 Configuració DNS (D-NCC)

**Host:** D‑NCC — `192.168.5.30/26`  
**Programari:** BIND9  

Fitxers de zona al projecte:

- Zona directa `g5.local`:  
  [`db.g5.local`](../files/dns/db.g5.local)  
- Zones reverses (exemples):  
  [`db.192.168.5.0`](../files/dns/db.192.168.5.0)  
  [`db.192.168.5.64`](../files/dns/db.192.168.5.64)  
  [`db.192.168.5.128`](../files/dns/db.192.168.5.128)  

1. **Instal·lar BIND:**

```bash
sudo apt install -y bind9 bind9utils
```

2. **Exemple de registres (zona directa):**

```bind
@       IN      NS      dns.g5.local.
dns     IN      A       192.168.5.30
web     IN      A       192.168.5.20
ftp     IN      A       192.168.5.40
db      IN      A       192.168.5.80
```

3. **Validar configuració i reiniciar:**

```bash
sudo named-checkconf
sudo named-checkzone g5.local /etc/bind/db.g5.local
sudo systemctl restart bind9
sudo systemctl status bind9
```

4. **Provar resolució:**

```bash
dig web.g5.local @192.168.5.30
dig -x 192.168.5.20 @192.168.5.30
```

---

### 5.5 Web + Base de dades

**Web Server W‑NCC (Apache + PHP)**  
**DB Server B‑NCC (MySQL/MariaDB)**

#### Web Server

1. **Instal·lar Apache + PHP:**

```bash
sudo apt install -y apache2 php php-mysql git
```

2. **Clonar el projecte:**

```bash
cd /var/www/html
sudo git clone https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05.git .
```

3. **Activar VirtualHost (equipaments.conf):**

Fitxer al repo:  
[`equipaments.conf`](../files/apache2/equipaments.conf)

```bash
sudo cp files/apache2/equipaments.conf /etc/apache2/sites-available/equipaments.conf
sudo a2ensite equipaments.conf
sudo a2enmod ssl headers rewrite
sudo systemctl reload apache2
```

4. **Certificat HTTPS de proves:**

```bash
sudo openssl req -x509 -newkey rsa:4096 -keyout /etc/ssl/private/equipaments.key \
  -out /etc/ssl/certs/equipaments.crt -days 365 -nodes
```

Comprova que els camins al VirtualHost coincideixen.

#### Base de dades (B‑NCC)

1. **Instal·lar MySQL/MariaDB:**

```bash
sudo apt install -y mariadb-server
```

2. **Crear BD i usuari:**

```bash
sudo mysql -u root -p <<'SQL'
CREATE DATABASE IF NOT EXISTS `Educacio` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER IF NOT EXISTS 'bchecker'@'localhost' IDENTIFIED BY 'bchecker121';
GRANT ALL PRIVILEGES ON `Educacio`.* TO 'bchecker'@'localhost';
FLUSH PRIVILEGES;
SQL
```

3. **(Opcional) Importar dades:**  

```bash
sudo mysql -u root -p Educacio < /path/to/backup.sql
# o utilitza directament el backup del projecte:
# sudo mysql -u root -p Educacio < /ruta/al/repo/files/db_backup.sql
```

Si l’aplicació no es connecta:

- Revisa la configuració de connexió:  
  [`config.php`](../public/includes/config.php)  
- Comprova que el firewall del servidor DB permet el port 3306 des del Web Server.  

---

## 6. Seguretat i firewall

Exemple de configuració amb `ufw`.

### Web Server (W‑NCC)

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing

sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow from 192.168.5.64/26 to any port 22
sudo ufw allow from 192.168.5.128/26 to any port 22

sudo ufw enable
sudo ufw status
```

Aplica una lògica similar a:

- DB: només 3306 des de W‑NCC.  
- FTP: 21, 22, 40000–50000/tcp.  
- DNS: port 53/udp+tcp per a totes les xarxes internes.

---

## 7. Monitorització i backups

### 7.1 Script de monitorització — `network_analyser.sh`

Objectiu: comprovar hosts i ports crítics cada X temps i registrar-ho en un log.

Fitxer al projecte:  
[`network_analyser.sh`](../files/network_analyser.sh)

Instal·lació amb `cron` (cada 10 minuts):

```bash
sudo cp files/network_analyser.sh /usr/local/bin/network_analyser.sh
sudo chmod +x /usr/local/bin/network_analyser.sh

sudo crontab -e
# Afegir:
*/10 * * * * /usr/local/bin/network_analyser.sh >> /var/log/network_analyser.log 2>&1
```

---

### 7.2 Script de backup — `system_backup.sh`

Objectiu: fer còpies de seguretat de configuracions i BD, rotar backups i registrar-ho.

Fitxer al projecte:  
[`system_backup.sh`](../files/system_backup.sh)

Ús recomanat (un cop al dia de matinada):

```bash
sudo cp files/system_backup.sh /usr/local/bin/system_backup.sh
sudo chmod +x /usr/local/bin/system_backup.sh

sudo crontab -e
# Exemple: cada dia a les 03:00
0 3 * * * /usr/local/bin/system_backup.sh >> /var/log/system_backup.log 2>&1
```

Comprova:

- Espai en disc a la carpeta de backups.  
- Que el script pot accedir a fitxers de config i a MySQL.  

---

## 8. Taula-resum de la topologia

| Dispositiu  | IP               | Xarxa     | Funció                     |
|-------------|------------------|-----------|----------------------------|
| R‑NCC (WAN) | DHCP             | Internet  | Gateway principal          |
| R‑NCC DMZ   | 192.168.5.1/26   | DMZ       | Gateway DMZ                |
| R‑NCC Intra | 192.168.5.65/26  | Intranet  | Gateway Intranet           |
| R‑NCC NAT   | 192.168.5.129/26 | NAT       | Gateway NAT                |
| W‑NCC       | 192.168.5.20/26  | DMZ       | Servidor Web               |
| D‑NCC       | 192.168.5.30/26  | DMZ       | Servidor DNS               |
| F‑NCC       | 192.168.5.40/26  | DMZ       | Servidor FTP/fitxers       |
| B‑NCC       | 192.168.5.80/26  | Intranet  | Servidor de base de dades  |
| DHCP        | 192.168.5.140/26 | NAT       | Servidor DHCP              |
| CLIWIN      | 192.168.5.130/26 | NAT       | Client Windows             |
| CLILIN      | 192.168.5.131/26 | NAT       | Client Linux               |

---

## 9. Dades susceptibles de separació

La importació inicial del fitxer CSV va generar una base de dades **no normalitzada**.  
Per millorar **integritat**, **rendiment** i **evitar redundància**, s’ha aplicat **normalització**, separant les dades en:

- **`Adreces`**  
  - Conté: `ID_Centre` i informació de la **ubicació** (adreça física completa).
- **`Centres`**  
  - Conté: `ID_Centre`, **`Nom_Centre`** i l’**`ID_Institució`** (si pertany a una institució superior).
- **`Geolocalització`**  
  - Conté: `ID_Centre` i les **coordenades** (`Latitud` i `Longitud`).
- **`Filtres`**  
  - Conté: `ID_Centre`, **categories** i **etiquetes** (tipus de centre, titularitat, etc.).
- **`Valors`**  
  - Conté: `ID_Centre` i dades de **contacte** (p. ex. `Telèfon`).

Es manté sempre **`ID_Centre`** com a **clau forana** per relacionar totes les taules.

---

**Última actualització:** 2025-12-1  
**Versió del manual:** 3.0
