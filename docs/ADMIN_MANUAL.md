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
10. [Recomanacions per entorns productius](#10-recomanacions-per-entorns-productius)  

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

La xarxa es divideix en subxarxes /26, interconnectades pel router R‑NCC:

- **NAT (clients + DHCP)** — `192.168.5.128/25`  
- **DMZ (serveis públics)** — `192.168.5.0/25`  
- **Intranet (serveis interns)** — `192.168.5.254/32`  

Esquema complet (Packet Tracer):  
[Esquema de xarxa](https://drive.google.com/file/d/1sruDIO3lY_b99p6khwERN0n-WELGoI5u/view?usp=sharing)

---

## 3. Infraestructura desplegada

| Rol                         | Host   | IP / Xarxa           | Lloc / Funció                    |
|-----------------------------|--------|----------------------|----------------------------------|
| Router WAN/DMZ/Intranet/NAT| R‑NCC  | 192.168.5.1 / .128 / .254 | Router central               |
| Web Server                  | W‑NCC  | 192.168.5.20/25 (DMZ)   | Apache + PHP                   |
| DNS Server                  | D‑NCC  | 192.168.5.30/25 (DMZ)   | BIND9                          |
| File/FTP Server             | F‑NCC  | 192.168.5.40/25 (DMZ)   | FTP / fitxers                  |
| DB Server                   | B‑NCC  | 192.168.5.140/25 (Intranet) | MySQL/MariaDB              |
| DHCP Server                 | —      | 192.168.5.150/25 (NAT)  | isc-dhcp-server                |
| Client Windows              | CLIWIN | 192.168.5.160/25 (NAT)  | Proves client                  |
| Client Linux                | CLILIN | 192.168.5.161/25 (NAT)  | Proves client                  |

---

## 4. Guia ràpida de desplegament

### 4.1 Ordre recomanat

1. [Configurar el router R‑NCC](#52-configuració-del-router-r-ncc)  
2. [Configurar el servidor DHCP (NAT)](#51-configuració-dhcp)  
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

# 2) Ping al gateway (R-NCC)
ping -c 2 192.168.5.1

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

**Host:** DHCP Server — `192.168.5.140/26` (xarxa NAT)  
**Fitxer al projecte:** [`dhcpd.conf`](../files/dhcp/dhcpd.conf)

Instal·lació bàsica:

```bash
sudo apt install -y isc-dhcp-server
sudo nano /etc/dhcp/dhcpd.conf
```

Contingut de referència:

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
  fixed-address 192.168.5.161;
}

host CLIWIN {
  hardware ethernet 52:54:00:1E:47:7A;
  fixed-address 192.168.5.160;
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
> - Assegura’t que `INTERFACESv4="enp0sX"` al fitxer `/etc/default/isc-dhcp-server` apunta a la interfície de la xarxa NAT.

---

### 5.2 Configuració del Router R-NCC

**Host:** R‑NCC (Linux router)  
**Fitxer de referència:** [`router_r-ncc.conf`](../files/router_r-ncc.conf)

#### 5.2.1 Assignar IPs a interfícies

```bash
sudo ip addr add 192.168.5.0/25   dev enp0s8   # DMZ
sudo ip addr add 192.168.5.128/25  dev enp0s9   # Intranet
sudo ip addr add 192.168.5.254/32 dev enp0s10  # NAT

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
# HTTP/HTTPS → Web Server
sudo iptables -t nat -A PREROUTING -i enp0s3 -p tcp --dport 80  -j DNAT --to 192.168.5.20:80
sudo iptables -t nat -A PREROUTING -i enp0s3 -p tcp --dport 443 -j DNAT --to 192.168.5.20:443

# FTP/SSH → File Server
sudo iptables -t nat -A PREROUTING -i enp0s3 -p tcp --dport 21  -j DNAT --to 192.168.5.40:21
sudo iptables -t nat -A PREROUTING -i enp0s3 -p tcp --dport 22  -j DNAT --to 192.168.5.40:22
```

Fer les regles persistents:

```bash
sudo apt install -y iptables-persistent
sudo iptables-save | sudo tee /etc/iptables/rules.v4
```

> **Nota:** en un entorn productiu caldria definir una **política de firewall explícita** i restringir ports al mínim necessari.

---

### 5.3 Servidor FTP / File Server (F-NCC)

**Host:** F‑NCC — `192.168.5.40/26`  
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

**Host:** D‑NCC — `192.168.5.30/26`  
**Programari:** BIND9

Fitxers de zona al projecte:

- Zona directa `g5.local`:  
  [`db.g5.local`](../files/dns/db.g5.local)
- Zones reverses:  
  [`db.192.168.5.0`](../files/dns/db.192.168.5.0)  
  [`db.192.168.5.64`](../files/dns/db.192.168.5.64)  
  [`db.192.168.5.128`](../files/dns/db.192.168.5.128)

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

1. Instal·lar:

```bash
sudo apt install -y apache2 php php-mysql git
```

2. Clonar el projecte (si aquest host fa de servidor web):

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

Revisar que els camins al VirtualHost coincideixen.

#### DB Server (B‑NCC)

1. Instal·lar:

```bash
sudo apt install -y mariadb-server
```

2. Crear BD i usuari:

```bash
sudo mysql -u root -p <<'SQL'
CREATE DATABASE IF NOT EXISTS `Educacio` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER IF NOT EXISTS 'bchecker'@'localhost' IDENTIFIED BY 'bchecker121';
GRANT ALL PRIVILEGES ON `Educacio`.* TO 'bchecker'@'localhost';
FLUSH PRIVILEGES;
SQL
```

3. Importar dades (exemple):

```bash
sudo mysql -u root -p Educacio < /ruta/al/repo/files/db_backup.sql
```

4. Connexió des de l’aplicació:

Configuració a: [`config.php`](../public/includes/config.php)

---

## 6. Seguretat i firewall

### Exemple: Web Server (W‑NCC) amb `ufw`

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing

sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow from 192.168.5.128/25 to any port 22   # Intranet
sudo ufw allow from 192.168.5.254/32 to any port 22  # NAT

sudo ufw enable
sudo ufw status
```

Pautes generals:

- Limitar **port 3306** (MySQL) perquè només l’host web hi pugui accedir.
- Permetre **port 53** (DNS) per a totes les xarxes internes.
- Documentar els canvis de firewall i revisar-los periòdicament.

---

## 7. Monitorització i backups

### 7.1 `network_analyser.sh` — monitorització

Fitxer al projecte:  
[`network_analyser.sh`](../files/network_analyser.sh)

**Objectiu:** comprovar l’estat de serveis crítics (ping, ports, etc.) i registrar-ho a un log.

Instal·lació amb `cron` (cada 10 minuts):

```bash
sudo cp files/network_analyser.sh /usr/local/bin/network_analyser.sh
sudo chmod +x /usr/local/bin/network_analyser.sh

sudo crontab -e
# Afegir:
*/10 * * * * /usr/local/bin/network_analyser.sh >> /var/log/network_analyser.log 2>&1
```

---

### 7.2 `system_backup.sh` — còpies de seguretat

Fitxer al projecte:  
[`system_backup.sh`](../files/system_backup.sh)

**Objectiu:** fer còpies periòdiques de:

- Fitxers de configuració crítics (xarxa, serveis).
- Base de dades `Educacio`.

Execució recomanada (cada dia a les 03:00):

```bash
sudo cp files/system_backup.sh /usr/local/bin/system_backup.sh
sudo chmod +x /usr/local/bin/system_backup.sh

sudo crontab -e
0 3 * * * /usr/local/bin/system_backup.sh >> /var/log/system_backup.log 2>&1
```

Comprova:

- Espai suficient a la carpeta de backups.
- Permisos per accedir a fitxers i MySQL.

---

## 8. Taula-resum de la topologia

| Dispositiu  | IP               | Xarxa     | Funció                     |
|-------------|------------------|-----------|----------------------------|
| R‑NCC (WAN) | DHCP             | Internet  | Gateway principal          |
| R‑NCC DMZ   | 192.168.5.1/25   | DMZ       | Gateway DMZ                |
| R‑NCC Intra | 192.168.5.65/25  | Intranet  | Gateway Intranet           |
| R‑NCC NAT   | 192.168.5.129/25 | NAT       | Gateway NAT                |
| W‑NCC       | 192.168.5.20/25  | DMZ       | Servidor Web               |
| D‑NCC       | 192.168.5.30/25  | DMZ       | Servidor DNS               |
| F‑NCC       | 192.168.5.40/25  | DMZ       | Servidor FTP/fitxers       |
| B‑NCC       | 192.168.5.140/25 | Intranet  | Servidor de base de dades  |
| DHCP        | 192.168.5.150/25 | NAT       | Servidor DHCP              |
| CLIWIN      | 192.168.5.160/25 | NAT       | Client Windows             |
| CLILIN      | 192.168.5.161/25 | NAT       | Client Linux               |

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

## 10. Recomanacions per entorns productius

Per evolucionar aquest projecte a un entorn més real:

- Substituir certificats **autofirmats** per **Let’s Encrypt** o equival.
- Utilitzar usuaris de BD amb **privilegis mínims** i credencials gestionades via:
  - Variables d’entorn.
  - Fitxers de config fora del DocumentRoot.
- Limitar l’accés SSH a IPs o xarxes concretes (bastion, VPN).
- Configurar còpies de seguretat remotes (p. ex. rsync a un servidor de backup).
- Afegir monitorització centralitzada (Zabbix, Prometheus, etc.).
- Documentar processos de recuperació davant fallades (DRP bàsic).

---

**Última actualització:** 2025-12-1  
**Versió del manual:** 3.1
