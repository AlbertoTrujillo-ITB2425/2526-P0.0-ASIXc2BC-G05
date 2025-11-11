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
11. [Resolució de problemes avançada](#11-resolució-de-problemes-avançada)

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

```cisco
! Configuració bàsica
hostname R-NCC
enable secret cisco123
username admin privilege 15 secret admin123

! Configuració d'interfícies
interface GigabitEthernet0/0
 description "Connexió a Internet"
 ip address dhcp
 ip nat outside
 no shutdown

interface GigabitEthernet0/1
 description "DMZ - Serveis públics"
 ip address 192.168.5.1 255.255.255.192
 ip nat inside
 no shutdown

interface GigabitEthernet0/2
 description "Intranet - Serveis interns"
 ip address 192.168.5.65 255.255.255.192
 ip nat inside
 no shutdown

interface GigabitEthernet0/3
 description "NAT - Clients i DHCP"
 ip address 192.168.5.129 255.255.255.192
 ip nat inside
 no shutdown

! Routing estàtic per defecte
ip route 0.0.0.0 0.0.0.0 GigabitEthernet0/0

! Configuració NAT/PAT
access-list 10 permit 192.168.5.0 0.0.0.63      ! DMZ
access-list 10 permit 192.168.5.64 0.0.0.63     ! Intranet
access-list 10 permit 192.168.5.128 0.0.0.63    ! NAT
ip nat inside source list 10 interface GigabitEthernet0/0 overload

! Port forwarding per serveis públics
ip nat inside source static tcp 192.168.5.20 80 interface GigabitEthernet0/0 80
ip nat inside source static tcp 192.168.5.20 443 interface GigabitEthernet0/0 443
ip nat inside source static tcp 192.168.5.40 21 interface GigabitEthernet0/0 21
ip nat inside source static tcp 192.168.5.40 22 interface GigabitEthernet0/0 22

! ACLs de seguretat
! Permetre accés a serveis públics des d'Internet
ip access-list extended INTERNET_IN
 permit tcp any host 192.168.5.20 eq 80
 permit tcp any host 192.168.5.20 eq 443
 permit tcp any host 192.168.5.40 eq 21
 permit tcp any host 192.168.5.40 eq 22
 permit udp any host 192.168.5.30 eq 53
 deny ip any any log

! ACL per controlar accés entre xarxes internes
ip access-list extended DMZ_TO_INTRANET
 permit tcp host 192.168.5.20 host 192.168.5.80 eq 3306
 deny ip 192.168.5.0 0.0.0.63 192.168.5.64 0.0.0.63 log
 permit ip any any

ip access-list extended NAT_TO_DMZ
 permit ip 192.168.5.128 0.0.0.63 192.168.5.0 0.0.0.63
 permit ip any any

! Aplicar ACLs a interfícies
interface GigabitEthernet0/0
 ip access-group INTERNET_IN in

interface GigabitEthernet0/1
 ip access-group DMZ_TO_INTRANET out

! SSH Configuration
ip domain-name g5.local
crypto key generate rsa modulus 2048
line vty 0 4
 login local
 transport input ssh
 exec-timeout 10 0

! Logs i debugging
logging buffered 16384
logging console warnings
service timestamps log datetime msec

! Guardar configuració
copy running-config startup-config
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

### 9.1 Script de monitorització de xarxa actualitzat

```bash
cat << 'EOF' > /usr/local/bin/network_monitor.sh
#!/bin/bash

LOGFILE="/var/log/network_monitor.log"
DATE=$(date '+%Y-%m-%d %H:%M:%S')

echo "$DATE - === INICI MONITORITZACIÓ ===" >> $LOGFILE

# Hosts per xarxa
declare -A HOSTS
HOSTS["Router"]="192.168.5.1"
HOSTS["Web-Server"]="192.168.5.20"
HOSTS["DNS-Server"]="192.168.5.30"
HOSTS["File-Server"]="192.168.5.40"
HOSTS["DB-Server"]="192.168.5.80"
HOSTS["DHCP-Server"]="192.168.5.140"
HOSTS["Client-Windows"]="192.168.5.130"
HOSTS["Client-Linux"]="192.168.5.131"

# Verificar conectivitat
for name in "${!HOSTS[@]}"; do
    ip="${HOSTS[$name]}"
    if ping -c 1 -W 2 "$ip" > /dev/null 2>&1; then
        echo "$DATE - OK: $name ($ip) accessible" >> $LOGFILE
    else
        echo "$DATE - ERROR: $name ($ip) no accessible" >> $LOGFILE
    fi
done

# Verificar serveis crítics
declare -A SERVICES
SERVICES["Web-HTTP"]="192.168.5.20:80"
SERVICES["Web-HTTPS"]="192.168.5.20:443"
SERVICES["DNS"]="192.168.5.30:53"
SERVICES["FTP"]="192.168.5.40:21"
SERVICES["SSH-FTP"]="192.168.5.40:22"
SERVICES["MySQL"]="192.168.5.80:3306"

for service in "${!SERVICES[@]}"; do
    host_port="${SERVICES[$service]}"
    host=$(echo $host_port | cut -d':' -f1)
    port=$(echo $host_port | cut -d':' -f2)
    
    if nc -z -w5 $host $port > /dev/null 2>&1; then
        echo "$DATE - OK: $service ($host_port) disponible" >> $LOGFILE
    else
        echo "$DATE - ERROR: $service ($host_port) no disponible" >> $LOGFILE
    fi
done

echo "$DATE - === FI MONITORITZACIÓ ===" >> $LOGFILE
EOF

chmod +x /usr/local/bin/network_monitor.sh

# Executar cada 10 minuts
echo "*/10 * * * * /usr/local/bin/network_monitor.sh" | sudo crontab -
```

---

## 10. Manteniment i backup

### 10.1 Script de backup actualitzat per la nova topologia

```bash
cat << 'EOF' > /usr/local/bin/system_backup.sh
#!/bin/bash

BACKUP_DIR="/backups"
DATE=$(date +%Y%m%d_%H%M%S)
LOG_FILE="/var/log/backup.log"

mkdir -p $BACKUP_DIR
echo "$(date) - Iniciant backup del sistema" >> $LOG_FILE

# Backup per servidor segons la seva funció
case "$(hostname -I | awk '{print $1}')" in
    "192.168.5.20") # Web Server
        echo "$(date) - Backup Web Server..." >> $LOG_FILE
        tar -czf $BACKUP_DIR/web_configs_$DATE.tar.gz \
            /etc/apache2/ \
            /var/www/ \
            /etc/ssl/ 2>> $LOG_FILE
        ;;
    "192.168.5.30") # DNS Server
        echo "$(date) - Backup DNS Server..." >> $LOG_FILE
        tar -czf $BACKUP_DIR/dns_configs_$DATE.tar.gz \
            /etc/bind/ \
            /var/cache/bind/ 2>> $LOG_FILE
        ;;
    "192.168.5.40") # File Server
        echo "$(date) - Backup File Server..." >> $LOG_FILE
        tar -czf $BACKUP_DIR/file_configs_$DATE.tar.gz \
            /etc/vsftpd.conf \
            /etc/samba/ \
            /srv/samba/ \
            /home/ftpuser/ 2>> $LOG_FILE
        ;;
    "192.168.5.80") # Database Server
        echo "$(date) - Backup Database Server..." >> $LOG_FILE
        mysqldump -u root -p'password' --all-databases > $BACKUP_DIR/mysql_$DATE.sql 2>> $LOG_FILE
        tar -czf $BACKUP_DIR/db_configs_$DATE.tar.gz /etc/mysql/ 2>> $LOG_FILE
        ;;
    "192.168.5.140") # DHCP Server
        echo "$(date) - Backup DHCP Server..." >> $LOG_FILE
        tar -czf $BACKUP_DIR/dhcp_configs_$DATE.tar.gz \
            /etc/dhcp/ \
            /var/lib/dhcp/ 2>> $LOG_FILE
        ;;
esac

# Neteja de backups antics
find $BACKUP_DIR -name "*_$DATE.*" -mtime +30 -delete

echo "$(date) - Backup completat per $(hostname -I | awk '{print $1}')" >> $LOG_FILE
EOF

chmod +x /usr/local/bin/system_backup.sh
```

---

## 11. Resolució de problemes avançada

### 11.1 Diagnòstic per xarxes

```bash
cat << 'EOF' > /usr/local/bin/network_diagnosis.sh
#!/bin/bash

echo "=== DIAGNÒSTIC DE XARXA G5 Systems ==="
echo "Data: $(date)"
echo "IP Local: $(hostname -I | awk '{print $1}')"
echo

# Determinar quina xarxa som
LOCAL_IP=$(hostname -I | awk '{print $1}')
case "$LOCAL_IP" in
    192.168.5.2[0-9]|192.168.5.[3-5][0-9]|192.168.5.6[0-3])
        NETWORK="DMZ"
        GATEWAY="192.168.5.1"
        ;;
    192.168.5.6[4-9]|192.168.5.[7-9][0-9]|192.168.5.1[0-2][0-7])
        NETWORK="Intranet"
        GATEWAY="192.168.5.65"
        ;;
    192.168.5.12[8-9]|192.168.5.1[3-8][0-9]|192.168.5.19[0-1])
        NETWORK="NAT"
        GATEWAY="192.168.5.129"
        ;;
    *)
        NETWORK="Desconeguda"
        GATEWAY="N/A"
        ;;
esac

echo "Xarxa detectada: $NETWORK"
echo "Gateway: $GATEWAY"
echo

# Test de conectivitat bàsic
echo "1. TEST DE CONECTIVITAT:"
echo "- Ping al gateway ($GATEWAY):"
ping -c 3 $GATEWAY
echo

echo "- Ping a DNS (192.168.5.30):"
ping -c 3 192.168.5.30
echo

echo "- Ping a Internet (8.8.8.8):"
ping -c 3 8.8.8.8
echo

# Resolució DNS
echo "2. RESOLUCIÓ DNS:"
nslookup web.g5.local 192.168.5.30
echo

# Verificar rutes
echo "3. TAULA DE RUTING:"
ip route show
echo

# Ports oberts
echo "4. PORTS OBERTS LOCALS:"
netstat -tlnp | head -20
echo

# Test de serveis per xarxa
echo "5. TEST DE SERVEIS SEGONS XARXA:"
case "$NETWORK" in
    "DMZ")
        echo "Tests per DMZ:"
        nc -z -v 192.168.5.20 80 2>&1 | grep -E "(succeeded|failed)"
        nc -z -v 192.168.5.30 53 2>&1 | grep -E "(succeeded|failed)"
        nc -z -v 192.168.5.40 21 2>&1 | grep -E "(succeeded|failed)"
        ;;
    "Intranet")
        echo "Tests per Intranet:"
        nc -z -v 192.168.5.80 3306 2>&1 | grep -E "(succeeded|failed)"
        nc -z -v 192.168.5.20 80 2>&1 | grep -E "(succeeded|failed)"
        ;;
    "NAT")
        echo "Tests per NAT:"
        nc -z -v 192.168.5.140 67 2>&1 | grep -E "(succeeded|failed)"
        nc -z -v 192.168.5.20 80 2>&1 | grep -E "(succeeded|failed)"
        nc -z -v 192.168.5.40 21 2>&1 | grep -E "(succeeded|failed)"
        ;;
esac

echo
echo "=== FI DEL DIAGNÒSTIC ==="
EOF

chmod +x /usr/local/bin/network_diagnosis.sh
```

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

---

**© 2025 - Manual d'Administrador - ITB Centre d'Estudis Tecnològics**
