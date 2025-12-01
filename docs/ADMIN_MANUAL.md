# Manual d’Administrador — 2526-P0.0-ASIXc2BC-G05

Guia tècnica per al desplegament, configuració i manteniment de la infraestructura del projecte.

---

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
9. [Dades susceptibles de separació (model de BD)](#9-dades-susceptibles-de-separació-model-de-bd)  

---

## 1. Visió general del projecte

Projecte de pràctiques del **grup G05 (ASIXc2BC)**, amb objectiu de:

- Dissenyar i desplegar una infraestructura de xarxa **segmentada**.
- Publicar una **aplicació web** que consulta una base de dades d’equipaments educatius.
- Implementar serveis de xarxa bàsics (DHCP, DNS, FTP, Web, BD).
- Afegir **scripts de monitorització** i **còpies de seguretat**.

Planificació (tauler de tasques):  
[Tauler del projecte a Proofhub](https://itecbcn.proofhub.com/bapplite/#app/todos/project-9335566085/list-269936034851)

> Per una **demo ràpida en un únic servidor**, consulta el  
> [`README.md`](../README.md#4-desplegament-inicial-demo-ràpida).

---

## 2. Esquema de xarxa

La xarxa es divideix en:

- **DMZ (serveis públics)** — `192.168.5.0/25`  
- **Intranet (serveis interns)** — `192.168.5.128/25`  
- **Enllaç NAT / sortida a Internet** — `192.168.5.254/32` (IP del router cap a fora o cap a un altre segment)

Esquema complet (Packet Tracer):  
[Esquema de xarxa](https://drive.google.com/file/d/1sruDIO3lY_b99p6khwERN0n-WELGoI5u/view?usp=sharing)

---

## 3. Infraestructura desplegada

| Rol                          | Host   | IP / Xarxa              | Lloc / Funció                    |
|------------------------------|--------|-------------------------|----------------------------------|
| Router WAN/DMZ/Intranet/NAT | R‑NCC  | DMZ: 192.168.5.1/25     | Router central                   |
|                              |        | Intra: 192.168.5.129/25 |                                  |
|                              |        | NAT: 192.168.5.254/32   | Sortida a Internet / NAT        |
| Web Server                   | W‑NCC  | 192.168.5.20/25 (DMZ)   | Apache + PHP                     |
| DNS Server                   | D‑NCC  | 192.168.5.30/25 (DMZ)   | BIND9                            |
| File/FTP Server              | F‑NCC  | 192.168.5.40/25 (DMZ)   | FTP / fitxers                    |
| DB Server                    | B‑NCC  | 192.168.5.140/25 (Intra)| MySQL/MariaDB                    |
| DHCP Server                  |        | 192.168.5.150/25 (Intra)| isc-dhcp-server                  |
| Client Windows               | CLIWIN | 192.168.5.161/25 (Intra)| Proves client                    |
| Client Linux                 | CLILIN | 192.168.5.160/25 (Intra)| Proves client                    |

---

## 4. Guia ràpida de desplegament

### 4.1 Ordre recomanat

1. [Configurar el router R‑NCC](#52-configuració-del-router-r-ncc)  
2. [Configurar el servidor DHCP (Intranet)](#51-configuració-dhcp)  
3. [Configurar DNS (D‑NCC)](#54-configuració-dns-d-ncc)  
4. [Configurar Web + Base de dades](#55-web--base-de-dades)  
5. [Configurar File/FTP Server](#53-servidor-ftp--file-server-f-ncc)  
6. [Configurar firewall i regles de seguretat](#6-seguretat-i-firewall)  
7. [Configurar scripts de monitorització i backups](#7-monitorització-i-backups)  

---

### 4.2 Comprovacions bàsiques

Des de cada host:

```bash
# 1) Verificar IP i interfícies
ip addr show

# 2) Ping als gateways del router
ping -c 2 192.168.5.1    # des d'un host a la DMZ
ping -c 2 192.168.5.129  # des d'un host a la Intranet

# 3) Ping al DNS (quan estigui configurat)
ping -c 2 192.168.5.30

# 4) Provar resolució DNS
dig web.g5.local @192.168.5.30
```

Si alguna prova falla:

- Revisa IPs, màscares i gateway per defecte.
- Comprova que el DNS està actiu (`systemctl status bind9`).
- Examina logs rellevants (`/var/log/syslog`, etc.).

---

## 5. Configuració de serveis

### 5.1 Configuració DHCP

**Host:** DHCP Server — `192.168.5.150/25` (xarxa Intranet)  
**Fitxer al projecte:** [`dhcpd.conf`](../files/dhcp/dhcpd.conf)

Instal·lació bàsica:

```bash
sudo apt install -y isc-dhcp-server
sudo nano /etc/dhcp/dhcpd.conf
```

Contingut de referència (xarxa Intranet `192.168.5.128/25`):

```bash
option domain-name "g5.local";
option domain-name-servers 192.168.5.30;
default-lease-time 86400;
max-lease-time 604800;
authoritative;

subnet 192.168.5.128 netmask 255.255.255.128 {
  range 192.168.5.132 192.168.5.139;
  option routers 192.168.5.129;
  option subnet-mask 255.255.255.128;
  option domain-name-servers 192.168.5.30;
  option broadcast-address 192.168.5.255;
}

host CLILIN {
  hardware ethernet 52:54:00:39:be:b1;
  fixed-address 192.168.5.160;
}

host CLIWIN {
  hardware ethernet 52:54:00:1E:47:7A;
  fixed-address 192.168.5.161;
}
```

Activar el servei:

```bash
sudo systemctl enable isc-dhcp-server
sudo systemctl restart isc-dhcp-server
sudo systemctl status isc-dhcp-server
```

> **Troubleshooting:**  
> - Revisa `/var/log/syslog` o `/var/log/dhcpd.log`.  
> - Assegura’t que `INTERFACESv4="enp0sX"` a `/etc/default/isc-dhcp-server` sigui la interfície de la Intranet.

---

### 5.2 Configuració del Router R-NCC

**Host:** R‑NCC (Linux router)  
**Fitxer de referència:** [`router_r-ncc.conf`](../files/router_r-ncc.conf)

#### 5.2.1 Assignar IPs a interfícies

```bash
# DMZ
sudo ip addr add 192.168.5.1/25   dev enp0s8

# Intranet
sudo ip addr add 192.168.5.129/25 dev enp0s9

# NAT / sortida a Internet (IP única)
sudo ip addr add 192.168.5.254/32 dev enp0s10

sudo ip link set enp0s8 up
sudo ip link set enp0s9 up
sudo ip link set enp0s10 up
```

#### 5.2.2 Activar IP forwarding

```bash
echo 1 | sudo tee /proc/sys/net/ipv4/ip_forward
sudo sed -i 's/^#net.ipv4.ip_forward=1/net.ipv4.ip_forward=1/' /etc/sysctl.conf
sudo sysctl -p
```

#### 5.2.3 NAT/PAT cap a Internet

> Assumint interfície de sortida `enp0s3` (WAN).

```bash
sudo iptables -t nat -A POSTROUTING -s 192.168.5.0/24 -o enp0s3 -j MASQUERADE
```

#### 5.2.4 Redirecció de ports cap a la DMZ

```bash
# HTTP/HTTPS → Web Server (W-NCC)
sudo iptables -t nat -A PREROUTING -i enp0s3 -p tcp --dport 80  -j DNAT --to 192.168.5.20:80
sudo iptables -t nat -A PREROUTING -i enp0s3 -p tcp --dport 443 -j DNAT --to 192.168.5.20:443

# FTP/SSH → File Server (F-NCC)
sudo iptables -t nat -A PREROUTING -i enp0s3 -p tcp --dport 21  -j DNAT --to 192.168.5.40:21
sudo iptables -t nat -A PREROUTING -i enp0s3 -p tcp --dport 22  -j DNAT --to 192.168.5.40:22
```

Fer les regles persistents:

```bash
sudo apt install -y iptables-persistent
sudo iptables-save | sudo tee /etc/iptables/rules.v4
```

---

### 5.3 Servidor FTP / File Server (F-NCC)

**Host:** F‑NCC — `192.168.5.40/25` (DMZ)  
**Servei:** FTP segur (vsftpd) + opcionalment SMB per a xarxa interna.

1. Instal·lar:

```bash
sudo apt install -y vsftpd openssl
```

2. Generar certificat FTPS:

```bash
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/ssl/private/vsftpd.key \
  -out /etc/ssl/certs/vsftpd.crt
```

3. Configuració bàsica a `/etc/vsftpd.conf`:

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

4. Reiniciar:

```bash
sudo systemctl restart vsftpd
sudo systemctl status vsftpd
```

Logs: `/var/log/vsftpd.log`, `/var/log/syslog`.

---

### 5.4 Configuració DNS (D-NCC)

**Host:** D‑NCC — `192.168.5.30/25` (DMZ)  
**Programari:** BIND9

Fitxers de zona al projecte:

- Zona directa `g5.local`:  
  [`db.g5.local`](../files/dns/db.g5.local)
- Zones reverses (segons segments):  
  [`db.192.168.5.0`](../files/dns/db.192.168.5.0) (DMZ /25)  
  [`db.192.168.5.128`](../files/dns/db.192.168.5.128) (Intranet /25)

1. Instal·lació:

```bash
sudo apt install -y bind9 bind9utils
```

2. Exemple de registres (zona directa):

```bind
@       IN      NS      dns.g5.local.
dns     IN      A       192.168.5.30
web     IN      A       192.168.5.20
ftp     IN      A       192.168.5.40
db      IN      A       192.168.5.140
```

3. Validació i reinici:

```bash
sudo named-checkconf
sudo named-checkzone g5.local /etc/bind/db.g5.local
sudo systemctl restart bind9
sudo systemctl status bind9
```

4. Proves:

```bash
dig web.g5.local @192.168.5.30
dig -x 192.168.5.20 @192.168.5.30
```

---

### 5.5 Web + Base de dades

#### Web Server (W‑NCC)

**IP:** `192.168.5.20/25` (DMZ)

1. Instal·lar:

```bash
sudo apt install -y apache2 php php-mysql git
```

2. Clonar el projecte:

```bash
cd /var/www/html
sudo git clone https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05.git .
```

3. VirtualHost:

Fitxer al repo: [`equipaments.conf`](../files/apache2/equipaments.conf)

```bash
sudo cp files/apache2/equipaments.conf /etc/apache2/sites-available/equipaments.conf
sudo a2ensite equipaments.conf
sudo a2enmod ssl headers rewrite
sudo systemctl reload apache2
```

4. Certificat:

```bash
sudo openssl req -x509 -newkey rsa:4096 -keyout /etc/ssl/private/equipaments.key \
  -out /etc/ssl/certs/equipaments.crt -days 365 -nodes
```

Comprova que els camins del certificat a `equipaments.conf` són correctes.

#### DB Server (B‑NCC)

**IP:** `192.168.5.140/25` (Intranet)

1. Instal·lar:

```bash
sudo apt install -y mariadb-server
```

2. Crear BD i usuari:

```bash
sudo mysql -u root -p <<'SQL'
CREATE DATABASE IF NOT EXISTS `Educacio` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER IF NOT EXISTS 'bchecker'@'%' IDENTIFIED BY 'bchecker121';
GRANT ALL PRIVILEGES ON `Educacio`.* TO 'bchecker'@'%';
FLUSH PRIVILEGES;
SQL
```

3. Importar dades:

```bash
sudo mysql -u root -p Educacio < /ruta/al/repo/files/db_backup.sql
```

4. Connexió des de l’aplicació:

Configuració a: [`config.php`](../public/includes/config.php)  
(assegura’t que el `host` és `192.168.5.140` si el web i la BD són en màquines diferents).

---

## 6. Seguretat i firewall

### Exemple: Web Server (W‑NCC) amb `ufw`

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing

sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow from 192.168.5.128/25 to any port 22   # SSH administració des d'Intranet

sudo ufw enable
sudo ufw status
```

Pautes generals:

- Limitar **MySQL (3306)** a la IP del Web Server o a la DMZ segons necessitats.
- Permetre **DNS (53 tcp/udp)** des de DMZ i Intranet cap a D‑NCC (`192.168.5.30`).
- Tancar ports no utilitzats i documentar les excepcions.

---

## 7. Monitorització i backups

### 7.1 `network_analyser.sh` — monitorització

Fitxer al projecte:  
[`network_analyser.sh`](../files/network_analyser.sh)

Objectiu: comprovar l’estat de hosts i ports crítics i escriure un log periòdic.

Instal·lació amb `cron` (cada 10 minuts):

```bash
sudo cp files/network_analyser.sh /usr/local/bin/network_analyser.sh
sudo chmod +x /usr/local/bin/network_analyser.sh

sudo crontab -e
*/10 * * * * /usr/local/bin/network_analyser.sh >> /var/log/network_analyser.log 2>&1
```

---

### 7.2 `system_backup.sh` — còpies de seguretat

Fitxer al projecte:  
[`system_backup.sh`](../files/system_backup.sh)

Objectiu: fer còpies de:

- Fitxers de configuració (xarxa, serveis).
- Base de dades `Educacio`.

Execució recomanada (cada dia a les 03:00):

```bash
sudo cp files/system_backup.sh /usr/local/bin/system_backup.sh
sudo chmod +x /usr/local/bin/system_backup.sh

sudo crontab -e
0 3 * * * /usr/local/bin/system_backup.sh >> /var/log/system_backup.log 2>&1
```

Revisa:

- Espai disponible al disc de backups.
- Permisos del script per llegir configs i accedir a MySQL.

---

## 8. Taula-resum de la topologia

| Dispositiu  | IP               | Xarxa     | Funció                           |
|-------------|------------------|-----------|----------------------------------|
| R‑NCC NAT   | 192.168.5.254/32 | NAT/WAN   | Gateway principal cap a fora     |
| R‑NCC DMZ   | 192.168.5.1/25   | DMZ       | Gateway DMZ                      |
| R‑NCC Intra | 192.168.5.129/25 | Intranet  | Gateway Intranet                 |
| W‑NCC       | 192.168.5.20/25  | DMZ       | Servidor Web                     |
| D‑NCC       | 192.168.5.30/25  | DMZ       | Servidor DNS                     |
| F‑NCC       | 192.168.5.40/25  | DMZ       | Servidor FTP/fitxers             |
| B‑NCC       | 192.168.5.140/25 | Intranet  | Servidor de base de dades        |
| DHCP        | 192.168.5.150/25 | Intranet  | Servidor DHCP                    |
| CLIWIN      | 192.168.5.161/25 | Intranet  | Client Windows                   |
| CLILIN      | 192.168.5.160/25 | Intranet  | Client Linux                     |

---

## 9. Dades susceptibles de separació (model de BD)

La importació inicial del CSV produïa una BD **no normalitzada**. Per millorar:

- Integritat de les dades.  
- Rendiment de les consultes.  
- Evitar redundància.

S’ha aplicat un esquema normalitzat amb taules separades:

- **`Adreces`**
  - `ID_Centre`, `Adreça`, `Codi_Postal`, `Municipi`, etc.
- **`Centres`**
  - `ID_Centre`, `Nom_Centre`, `ID_Institució`, etc.
- **`Geolocalització`**
  - `ID_Centre`, `Latitud`, `Longitud`.
- **`Filtres`**
  - `ID_Centre`, tipologia, titularitat, categories.
- **`Valors`**
  - `ID_Centre`, dades de contacte (telèfon, correu, etc.).

**Clau comuna:** `ID_Centre` s’utilitza com a clau forana per relacionar totes les taules.

---

**Última actualització:** 2025-12-01  
**Versió del manual:** 3.3
