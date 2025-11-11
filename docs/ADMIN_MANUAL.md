# Manual de Administrador - 2526-P0.0-ASIXc2BC-G05

## Índex

1. [Planificació del projecte](#1-planificació-del-projecte)
2. [Esquema de xarxa](#2-esquema-de-xarxa)
3. [Infraestructura desplegada](#3-infraestructura-desplegada)
4. [Configuració de serveis](#4-configuració-de-serveis)
5. [Configuració del Router R-NCC](#5-configuració-del-router-r-ncc)
6. [Servidor FTP/File Server](#6-servidor-ftpfile-server)
7. [Configuració DNS](#7-configuració-dns)
8. [Seguretat de xarxa](#8-seguretat-de-xarxa)
9. [Eines de monitorització](#9-eines-de-monitorització)
10. [Manteniment i backup](#10-manteniment-i-backup)

---

## 1. Planificació del projecte

La planificació s'ha fet a **Proofhub** en **tres sprints** de **dues setmanes** i **cinc hores per setmana**. La **durada total** és de **sis setmanes** fins al **18/11**. La documentació i la configuració estan versionades al repositori git amb el nom **P0.0-ASIXc2BC-G05**. 

**Enllaç a Proofhub:** [Tauler del projecte a Proofhub](https://itecbcn.proofhub.com/bapplite/#app/todos/project-9335566085/list-269936034851)

---

## 2. Esquema de xarxa

L'arquitectura desplegada es representa al diagrama següent amb tres xarxes diferents interconnectades pel **Router R-NCC**.

**Descarregar esquema de xarxa:** [Packet Tracer](https://drive.google.com/file/d/1sruDIO3lY_b99p6khwERN0n-WELGoI5u/view?usp=sharing)

<img width="910" height="565" alt="Captura de pantalla de 2025-10-28 15-11-17" src="https://github.com/user-attachments/assets/bae3db11-eba9-46ba-a99c-0463bbbf78d0" />

### 2.1 Topologia de xarxa detallada:

#### **Xarxa NAT (Clients i DHCP) - 192.168.5.128/26**
- **Subxarxa:** 192.168.5.128/26 (192.168.5.128 - 192.168.5.191)
- **Gateway:** 192.168.5.129 (Interfície R-NCC)
- **DHCP Server:** 192.168.5.140/26
- **Client Linux (CLILIN):** 192.168.5.131/26 (via DHCP)
- **Client Windows (CLIWIN):** 192.168.5.130/26 (via DHCP)

#### **Xarxa DMZ (Serveis públics) - 192.168.5.0/26**
- **Subxarxa:** 192.168.5.0/26 (192.168.5.0 - 192.168.5.63)
- **Gateway:** 192.168.5.1 (Interfície R-NCC)
- **Web Server (W-NCC):** 192.168.5.20/26
- **DNS Server (D-NCC):** 192.168.5.30/26
- **File Server/FTP (F-NCC):** 192.168.5.40/26

#### **Xarxa Intranet (Serveis interns) - 192.168.5.64/26**
- **Subxarxa:** 192.168.5.64/26 (192.168.5.64 - 192.168.5.127)
- **Gateway:** 192.168.5.65 (Interfície R-NCC)
- **Database Server (B-NCC):** 192.168.5.80/26

#### **Router Central R-NCC**
- **Interfície DMZ:** 192.168.5.1/26
- **Interfície Intranet:** 192.168.5.65/26
- **Interfície NAT:** 192.168.5.129/26
- **Interfície WAN:** DHCP (connexió a Internet)

---

## 3. Infraestructura desplegada

### Router R-NCC
- **Funció:** Encaminador central entre les tres xarxes (DMZ, Intranet, NAT)
- **Interfícies:**
  - **GigabitEthernet0/0:** WAN (Internet) - DHCP
  - **GigabitEthernet0/1:** DMZ - 192.168.5.1/26
  - **GigabitEthernet0/2:** Intranet - 192.168.5.65/26
  - **GigabitEthernet0/3:** NAT - 192.168.5.129/26
- **Protocols:** NAT/PAT, Static Routes, ACLs
- **Fitxer de configuració:** [router_r-ncc.conf](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/router_r-ncc.conf)

### DHCP Server (Xarxa NAT)
- **Funció:** Assigna adreces IP dinàmiques als clients de la xarxa NAT
- **IP:** 192.168.5.140/26
- **Pool d'adreces:** 192.168.5.130-192.168.5.135
- **Gateway:** 192.168.5.129
- **DNS:** 192.168.5.30
- **Fitxer de configuració:** [dhcpd.conf](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/dhcp/dhcpd.conf)

### Web Server W-NCC (DMZ)
- **Funció:** Servidor web que serveix l'aplicació d'equipaments educatius
- **IP:** 192.168.5.20/26
- **Ports:** 80 (HTTP), 443 (HTTPS)
- **Accés:** Des d'Internet i xarxes internes
- **Fitxer de configuració:** [webserver_config.conf](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/webserver_config.conf)

### DNS Server D-NCC (DMZ)
- **Funció:** Resolució de noms per totes les xarxes
- **IP:** 192.168.5.30/26
- **Ports:** 53 (DNS TCP/UDP)
- **Zones:** g5.local, reverse zones
- **Accés:** Des de totes les xarxes

### File Server F-NCC (DMZ)
- **Funció:** Servidor FTP i compartició de fitxers
- **IP:** 192.168.5.40/26
- **Protocols:** FTP, SFTP, SMB/CIFS
- **Ports:** 21 (FTP), 22 (SFTP), 139/445 (SMB)
- **Accés:** Des d'Internet i xarxes internes

### Database Server B-NCC (Intranet)
- **Funció:** Servidor de base de dades MySQL amb accés restringit
- **IP:** 192.168.5.80/26
- **Ports:** 3306 (MySQL)
- **Accés:** Només des de DMZ (Web Server) i Intranet
- **Fitxers:**
  - [mysql_init.sql](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/mysql_init.sql)
  - [backup_mysql.sh](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/backup_mysql.sh)

### Clients (Xarxa NAT)
- **Client Linux (CLILIN):** 192.168.5.131/26 (assignació DHCP)
- **Client Windows (CLIWIN):** 192.168.5.130/26 (assignació DHCP)

---

## 4. Configuració de serveis

### 4.1 Configuració DHCP Actualitzada

**Fitxer:** `/etc/dhcp/dhcpd.conf`

```bash
# Configuració global del DHCP
option domain-name "g5.local";
option domain-name-servers 192.168.5.30;
default-lease-time 86400;    # 24 hores
max-lease-time 604800;       # 1 setmana
authoritative;

# Xarxa NAT - Clients
subnet 192.168.5.128 netmask 255.255.255.192 {
  range 192.168.5.132 192.168.5.139;
  option routers 192.168.5.129;
  option subnet-mask 255.255.255.192;
  option domain-name-servers 192.168.5.30;
  option broadcast-address 192.168.5.191;
}

# Reserves estàtiques per clients
host CLILIN {
  hardware ethernet 52:54:00:39:be:b1;
  fixed-address 192.168.5.131;
}

host CLIWIN {
  hardware ethernet 52:54:00:1E:47:7A;
  fixed-address 192.168.5.130;
}
```

---

## 5. Configuració del Router R-NCC

### 5.1 Configuració completa del router
```bash
#!/bin/bash
# Router Linux - Configuració resumida (~50 línies)

hostnamectl set-hostname R-NCC
useradd admin -m -s /bin/bash
echo "admin:admin123" | chpasswd
usermod -aG sudo admin

# Interfícies
ip link set enp0s3 up && dhclient enp0s3              # Internet (DHCP)
ip addr add 192.168.5.1/26 dev enp0s8 && ip link set enp0s8 up   # DMZ
ip addr add 192.168.5.65/26 dev enp0s9 && ip link set enp0s9 up  # Intranet
ip addr add 192.168.5.129/26 dev enp0s10 && ip link set enp0s10 up # NAT/DHCP

# Routing
ip route add default via $(ip route | grep default | awk '{print $3}') dev enp0s3
echo 1 > /proc/sys/net/ipv4/ip_forward

# NAT/PAT
iptables -t nat -A POSTROUTING -s 192.168.5.0/24 -o enp0s3 -j MASQUERADE

# Port forwarding
iptables -t nat -A PREROUTING -i enp0s3 -p tcp --dport 80  -j DNAT --to 192.168.5.20:80
iptables -t nat -A PREROUTING -i enp0s3 -p tcp --dport 443 -j DNAT --to 192.168.5.20:443
iptables -t nat -A PREROUTING -i enp0s3 -p tcp --dport 21  -j DNAT --to 192.168.5.40:21
iptables -t nat -A PREROUTING -i enp0s3 -p tcp --dport 22  -j DNAT --to 192.168.5.40:22

# ACL Internet → serveis públics
iptables -A FORWARD -i enp0s3 -p tcp -d 192.168.5.20 --dport 80  -j ACCEPT
iptables -A FORWARD -i enp0s3 -p tcp -d 192.168.5.20 --dport 443 -j ACCEPT
iptables -A FORWARD -i enp0s3 -p tcp -d 192.168.5.40 --dport 21  -j ACCEPT
iptables -A FORWARD -i enp0s3 -p tcp -d 192.168.5.40 --dport 22  -j ACCEPT
iptables -A FORWARD -i enp0s3 -p udp -d 192.168.5.30 --dport 53  -j ACCEPT
iptables -A FORWARD -i enp0s3 -j DROP

# ACL DMZ → Intranet (només MySQL)
iptables -A FORWARD -s 192.168.5.20 -d 192.168.5.80 -p tcp --dport 3306 -j ACCEPT
iptables -A FORWARD -s 192.168.5.0/26 -d 192.168.5.64/26 -j DROP

# ACL NAT → DMZ
iptables -A FORWARD -s 192.168.5.128/26 -d 192.168.5.0/26 -j ACCEPT

# SSH segur
apt-get install -y openssh-server rsyslog
sed -i 's/#Port 22/Port 22/' /etc/ssh/sshd_config
systemctl restart ssh && systemctl enable rsyslog

# Guardar regles iptables
iptables-save > /etc/iptables/rules.v4
```

---

## 6. Servidor FTP/File Server

### 6.1 Configuració vsftpd per F-NCC (192.168.5.40)

**Fitxer:** `/etc/vsftpd.conf`

```bash
# Configuració bàsica
listen=YES
listen_ipv6=NO
anonymous_enable=NO
local_enable=YES
write_enable=YES
local_umask=022

# Seguretat
chroot_local_user=YES
chroot_list_enable=YES
chroot_list_file=/etc/vsftpd.chroot_list
allow_writeable_chroot=YES

# Configuració de xarxa
listen_address=192.168.5.40
pasv_enable=YES
pasv_min_port=40000
pasv_max_port=50000
pasv_address=192.168.5.40

# SSL/TLS
ssl_enable=YES
rsa_cert_file=/etc/ssl/certs/vsftpd.crt
rsa_private_key_file=/etc/ssl/private/vsftpd.key
ssl_tlsv1=YES
ssl_sslv2=NO
ssl_sslv3=NO
require_ssl_reuse=NO
ssl_ciphers=HIGH

# Logs
xferlog_enable=YES
xferlog_file=/var/log/vsftpd.log
log_ftp_protocol=YES

# Limits
max_clients=50
max_per_ip=5
```

---

## 7. Configuració DNS

### 7.1 Configuració DNS per D-NCC (192.168.5.30)

**Fitxer:** `/etc/bind/db.g5.local`

```bind
;
; BIND data file for g5.local
;
$TTL    604800
@       IN      SOA     dns.g5.local. admin.g5.local. (
                     2025111101         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
;
@       IN      NS      dns.g5.local.
@       IN      A       192.168.5.1

; Servidors DMZ
dns     IN      A       192.168.5.30
web     IN      A       192.168.5.20
ftp     IN      A       192.168.5.40
files   IN      CNAME   ftp
www     IN      CNAME   web

; Servidor Intranet
db      IN      A       192.168.5.80
database IN     CNAME   db

; Router i xarxes
router  IN      A       192.168.5.1
gateway IN      CNAME   router

; Servidor DHCP
dhcp    IN      A       192.168.5.140

; Clients
clilin  IN      A       192.168.5.131
cliwin  IN      A       192.168.5.130
```

### 7.2 Zones reverse

**Fitxer:** `/etc/bind/db.192.168.5.0` (DMZ)

```bind
$TTL    604800
@       IN      SOA     dns.g5.local. admin.g5.local. (
                     2025111101
                         604800
                          86400
                        2419200
                         604800 )
;
@       IN      NS      dns.g5.local.

1       IN      PTR     router.g5.local.
20      IN      PTR     web.g5.local.
30      IN      PTR     dns.g5.local.
40      IN      PTR     ftp.g5.local.
```

**Fitxer:** `/etc/bind/db.192.168.5.64` (Intranet)

```bind
$TTL    604800
@       IN      SOA     dns.g5.local. admin.g5.local. (
                     2025111101
                         604800
                          86400
                        2419200
                         604800 )
;
@       IN      NS      dns.g5.local.

65      IN      PTR     router.g5.local.
80      IN      PTR     db.g5.local.
```

**Fitxer:** `/etc/bind/db.192.168.5.128` (NAT)

```bind
$TTL    604800
@       IN      SOA     dns.g5.local. admin.g5.local. (
                     2025111101
                         604800
                          86400
                        2419200
                         604800 )
;
@       IN      NS      dns.g5.local.

129     IN      PTR     router.g5.local.
130     IN      PTR     cliwin.g5.local.
131     IN      PTR     clilin.g5.local.
140     IN      PTR     dhcp.g5.local.
```

---

## 8. Seguretat de xarxa

### 8.1 Configuració del firewall per xarxa

#### Web Server W-NCC (192.168.5.20)
```bash
# UFW per Web Server
sudo ufw enable
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Permetre connexions web
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Permetre SSH només des de xarxes internes
sudo ufw allow from 192.168.5.64/26 to any port 22
sudo ufw allow from 192.168.5.128/26 to any port 22

# Permetre connexió a base de dades
sudo ufw allow out 3306/tcp
```

#### Database Server B-NCC (192.168.5.80)
```bash
# UFW per Database Server
sudo ufw enable
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Permetre MySQL només des del web server
sudo ufw allow from 192.168.5.20 to any port 3306

# SSH només des de xarxa intranet
sudo ufw allow from 192.168.5.64/26 to any port 22
```

#### File Server F-NCC (192.168.5.40)
```bash
# UFW per File Server
sudo ufw enable
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Serveis FTP
sudo ufw allow 21/tcp
sudo ufw allow 20/tcp
sudo ufw allow 40000:50000/tcp  # Ports passius FTP

# SSH/SFTP
sudo ufw allow 22/tcp

# SMB/CIFS només xarxes internes
sudo ufw allow from 192.168.5.128/26 to any port 139
sudo ufw allow from 192.168.5.128/26 to any port 445
```

---

## 9. Eines de monitorització
## Objectiu de l’script

Aquest script es va crear per garantir la **monitorització automàtica de la xarxa i dels serveis crítics** dins d’una infraestructura local.  

## Què fa

- Comprova cada 10 minuts si els **hosts principals** (routers, servidors i clients) responen al ping.  
- Verifica si els **ports essencials** (HTTP, DNS, FTP, MySQL, etc.) estan disponibles.  
- Registra els resultats en un **fitxer de log** amb data i hora.  
- Assegura un **control continu i preventiu** del sistema, millorant la fiabilitat i la resposta davant possibles fallades.  

[network_analyser](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/network_analyser.sh)

---

## 10. Manteniment i backup
## Objectiu de l’script

Aquest script es va crear per garantir la **còpia de seguretat automàtica** de les configuracions i dades crítiques dels servidors dins de la infraestructura local.  

## Què fa

- Detecta la IP del servidor mitjançant la interfície configurada i identifica la seva funció (Web, DNS, Fitxers, Base de Dades o DHCP).  
- Genera **backups específics** segons el tipus de servidor, comprimint configuracions i directoris rellevants.  
- Exporta les bases de dades MySQL amb la contrasenya definida i guarda els fitxers resultants.  
- Elimina automàticament els **backups antics de més de 30 dies**, mantenint l’espai controlat.  
- Registra totes les operacions en un **fitxer de log** amb data i hora per garantir traçabilitat i detecció d’errors.  


[system_backup](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/system_backup.sh)

---

## Resum de la topologia actualitzada

### Taula d'adreces IP:

| Dispositiu | IP | Xarxa | Funció |
|------------|----|---------|--------------------|
| R-NCC (WAN) | DHCP | Internet | Gateway principal |
| R-NCC (DMZ) | 192.168.5.1/26 | DMZ | Gateway DMZ |
| R-NCC (Intranet) | 192.168.5.65/26 | Intranet | Gateway Intranet |
| R-NCC (NAT) | 192.168.5.129/26 | NAT | Gateway NAT |
| W-NCC | 192.168.5.20/26 | DMZ | Servidor Web |
| D-NCC | 192.168.5.30/26 | DMZ | Servidor DNS |
| F-NCC | 192.168.5.40/26 | DMZ | Servidor FTP/Files |
| B-NCC | 192.168.5.80/26 | Intranet | Servidor BD |
| DHCP Server | 192.168.5.140/26 | NAT | Servidor DHCP |
| CLIWIN | 192.168.5.130/26 | NAT | Client Windows |
| CLILIN | 192.168.5.131/26 | NAT | Client Linux |

### Flux de tràfic:
1. **Internet → DMZ:** Accés públic a serveis web, FTP i DNS
2. **DMZ → Intranet:** Només Web Server pot accedir a Base de Dades
3. **NAT → DMZ:** Clients poden accedir a tots els serveis DMZ
4. **NAT → Internet:** Sortida a Internet via NAT/PAT

---

## Fitxers de configuració

Tots els fitxers de configuració i scripts es troben a la carpeta `/files` del repositori:

- [router_r-ncc.conf](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/router_r-ncc.conf)
- [dhcpd.conf](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/dhcp/dhcpd.conf)
- [mysql_init.sql](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/mysql_init.sql)
- [backup_mysql.sh](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/backup_mysql.sh)
- [backup.sql](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/backup.sql)
- [webserver_config.conf](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/webserver_config.conf)
- [equipaments.conf](https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/apache2/equipaments.conf)

---

**Última actualització:** 2025-11-11  
**Versió del manual:** 2.1  

