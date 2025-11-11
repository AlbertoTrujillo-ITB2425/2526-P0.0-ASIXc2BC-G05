# Manual d'Administrador — 2526-P0.0-ASIXc2BC-G05

Versió: 2.1  
Última actualització: 2025-11-11  
Autor: Equip G5  
Repositori (configuracions i scripts): https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/tree/main/files

Índex
- [1. Planificació del projecte](#1-planificació-del-projecte)
- [2. Esquema de xarxa](#2-esquema-de-xarxa)
- [3. Infraestructura desplegada](#3-infraestructura-desplegada)
- [4. Configuració de serveis](#4-configuració-de-serveis)
- [5. Configuració del Router R‑NCC](#5-configuració-del-router-r-ncc)
- [6. Servidor FTP / File Server](#6-servidor-ftp--file-server)
- [7. Configuració DNS](#7-configuració-dns)
- [8. Seguretat de xarxa](#8-seguretat-de-xarxa)
- [9. Eines de monitorització](#9-eines-de-monitorització)
- [10. Manteniment i backup](#10-manteniment-i-backup)
- [11. Resolució d'errors avançada](#11-resolució-derrors-avançada)
- [Annex — Llista de fitxers clau](#annex--llista-de-fitxers-clau)

---

## 1. Planificació del projecte
- Metodologia: 3 sprints de 2 setmanes, 5 h/setmana.  
- Durada: 6 setmanes (fins 18/11).  
- Gestió: Proofhub; control de versions al repositori indicat.  
- Enllaç Proofhub: https://itecbcn.proofhub.com/bapplite/#app/todos/project-9335566085/list-269936034851

Objectiu: infraestructura amb separació de zones (DMZ, Intranet, NAT), serveis públics limitats i procediments de manteniment.

---

## 2. Esquema de xarxa
Topologia: tres segments interconnectats pel Router R‑NCC.

- DMZ (serveis públics): 192.168.5.0/26 — gateway 192.168.5.1
  - W‑NCC (web): 192.168.5.20
  - D‑NCC (DNS): 192.168.5.30
  - F‑NCC (FTP/Files): 192.168.5.40
- Intranet (serveis interns): 192.168.5.64/26 — gateway 192.168.5.65
  - B‑NCC (BD): 192.168.5.80
- NAT (clients/DHCP): 192.168.5.128/26 — gateway 192.168.5.129
  - DHCP: 192.168.5.140
  - Clients amb reserva: 192.168.5.130, 192.168.5.131

Diagrama Packet Tracer: https://drive.google.com/file/d/1sruDIO3lY_b99p6khwERN0n-WELGoI5u/view?usp=sharing

Rutes i portes: vegeu configuració del router i fitxers de cada servei al directori /files.

---

## 3. Infraestructura desplegada
- Router R‑NCC: encaminador central amb NAT/PAT, ACLs i port‑forwarding. Fitxer: /files/router_r-ncc.conf  
- DHCP Server (NAT): IP 192.168.5.140, pool i reserves. Fitxer: /files/dhcp/dhcpd.conf  
- Web Server (DMZ): IP 192.168.5.20, ports 80/443. Fitxer: /files/webserver_config.conf  
- DNS Server (DMZ): IP 192.168.5.30, zones forward i reverse. Fitxers: /files/bind/  
- File/FTP Server (DMZ): IP 192.168.5.40, protocols FTP/SFTP/SMB. Fitxer: /files/vsftpd.conf  
- Database Server (Intranet): IP 192.168.5.80, port 3306, accés restringit. Fitxers: /files/mysql_init.sql, /files/backup_mysql.sh  
- Clients (NAT): reserves per CLILIN i CLIWIN a /files/dhcp/dhcpd.conf

---

## 4. Configuració de serveis
Localització dels fitxers amb la configuració completa:

- DHCP: /files/dhcp/dhcpd.conf  
  - Pool NAT: 192.168.5.132–192.168.5.139  
  - Gateway: 192.168.5.129  
  - DNS: 192.168.5.30

- Router: /files/router_r-ncc.conf  
  - Interfícies: WAN (dhcp), DMZ (192.168.5.1/26), Intranet (192.168.5.65/26), NAT (192.168.5.129/26)  
  - NAT/PAT, ACLs, port‑forwarding per serveis públics

- DNS (BIND): /files/bind/db.g5.local i fitxers reverse per cada subxarxa

- Web: /files/webserver_config.conf (exemple VirtualHost)

- FTP: /files/vsftpd.conf (configuració TLS, PASV 40000–50000, chroot)

- Base de dades: /files/mysql_init.sql; backups: /files/backup_mysql.sh

- Monitorització i diagnòstic: /files/network_analyser.sh, /files/network_diagnosis.sh

---

## 5. Configuració del Router R‑NCC
Contingut del fitxer de configuració principal: /files/router_r-ncc.conf

Punts clau del fitxer:
- Assignació d'adreces a les quatre interfícies (WAN, DMZ, Intranet, NAT).  
- Ruta per defecte via interfície WAN.  
- Access‑list per identificar subxarxes internes i permetre l'exportació NAT/PAT.  
- Regles d'ip nat inside source per sobrecàrrega (overload).  
- Entrades d'ip nat inside source static per forward de ports 80, 443, 21, 22.  
- ACL INTERNET_IN per permetre només els serveis exposats i fer deny ip any any log per la resta.  
- ACLs internes per controlar fluxos entre DMZ i Intranet.  
- Configuració SSH amb domini g5.local i claus RSA.  
- Logging i guardat de la configuració.

---

## 6. Servidor FTP / File Server
Fitxer de configuració: /files/vsftpd.conf

Contingut essencial:
- Mode d'escolta IPv4, usuari local habilitat, escriure habilitat.  
- Chroot d'usuaris locals i fitxer de llista de chroot.  
- Adreça d'escolta 192.168.5.40, rang pasiu 40000–50000.  
- TLS activat amb rutes a certificats i claus.  
- Fitxer de log a /var/log/vsftpd.log.  
- Límits de connexions i per IP.

Protocols disponibles al servei: FTP, SFTP, SMB/CIFS (configuració SMB no inclosa en aquest fitxer).

---

## 7. Configuració DNS
Fitxer forward: /files/bind/db.g5.local  
Fitxers reverse: /files/bind/db.192.168.5.0, /files/bind/db.192.168.5.64, /files/bind/db.192.168.5.128

Contingut forward:
- SOA amb serial 2025111101.  
- NS apuntant a dns.g5.local.  
- A records per router, dns, web, ftp, db, dhcp i clients reservats.  
- CNAME per www i files.

Contingut reverse: PTR per a router, web, dns, ftp, db, clients i DHCP segons la subxarxa.

---

## 8. Seguretat de xarxa
Control configurat a nivell d'infraestructura i màquina:

- ACLs al router:
  - INTERNET_IN: permet trànsit TCP cap a web (80/443), FTP (21), SFTP/SSH (22) i UDP 53 cap al servidor DNS; la resta es denega amb registre.  
  - DMZ_TO_INTRANET: permet només que el servidor web accedeixi a la base de dades al port 3306 i denega altres connexions entre DMZ i Intranet (amb registre).  
  - NAT_TO_DMZ: permet comunicació des de la subxarxa NAT a la DMZ.

- Firewalls a host (exemples amb UFW) per a web, base de dades i file server: regles per permetre ports necessaris i limitar orígens.

- SSH configurat al router amb autenticació local i transport SSH.

- Logging activat al router i per servei a sistemes corresponents.

---

## 9. Eines de monitorització
Ubicació dels scripts:
- /files/network_analyser.sh — script de monitoratge periòdic que registra pings i disponibilitat de ports a /var/log/network_analyser.log.  
- /files/network_diagnosis.sh — script de diagnòstic on‑demand que comprova IP local, gateway, ping a DNS i Internet, resolució DNS, taula de rutes i comprovació de ports segons la xarxa.

Execució prevista: network_analyser.sh mitjançant cron cada 10 minuts; network_diagnosis.sh per execució manual.

---

## 10. Manteniment i backup
Scripts i comportament:
- /files/backup_mysql.sh — dump de MySQL a fitxer gzip amb retenció de fitxers amb més de 7 dies.  
- /files/system_backup.sh — accions de backup segons IP/rol (backup específic per servidor de bases de dades, sincronització per servidor de fitxers, tar de configuracions en nodes genèrics).

Ubicació dels backups: directori per defecte indicat dins dels scripts (/var/backups/g5 o paràmetre passat al script).

---

## 11. Resolució d'errors avançada
Scripts i comandes de suport:
- /files/network_diagnosis.sh — comprovacions automàtiques i sortida per pantalla.  
- Comandes útils: ip route show, ss/netstat, nc, ping, traceroute, nslookup, journalctl, tail -f /var/log/syslog, tcpdump.

Procés bàsic:
1. Identificar abast de la incidència (host, segment, servei).  
2. Comprovar connectivitat bàsica (ping, gateway).  
3. Comprovar serveis locals i regles de firewall.  
4. Analitzar logs i aplicar procediments de restauració amb els fitxers de backup disponibles.

---

Annex — Llista de fitxers clau (directori /files)
- /files/router_r-ncc.conf  
- /files/dhcp/dhcpd.conf  
- /files/vsftpd.conf  
- /files/bind/db.g5.local  
- /files/bind/db.192.168.5.0  
- /files/bind/db.192.168.5.64  
- /files/bind/db.192.168.5.128  
- /files/webserver_config.conf  
- /files/network_analyser.sh  
- /files/network_diagnosis.sh  
- /files/system_backup.sh  
- /files/mysql_init.sql  
- /files/backup_mysql.sh

---

Fitxers de referència addicionals al repositori:
- https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/tree/main/files

--- 