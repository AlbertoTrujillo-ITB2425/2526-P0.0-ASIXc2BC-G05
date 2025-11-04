# Documentació per a Administradors — P0.0-ASIXc2BC-G05

Aquest document resumeix les tasques d'instal·lació, operació i manteniment destinades a administradors. No inclou secrets; aquests han d'estar guardats fora del repositori (variables d'entorn, gestor de secrets o fitxers amb permisos restringits).

CONTINGUT
- Introducció
- Requisits i accessos
- Desplegament de l'aplicació web
- Gestió de la base de dades
  - Creació d'usuari admin limitat
  - Backups i restauració
  - Import de dades CSV
- Gestió DHCP
- Router R-NCC (resum de configuració)
- Fitxers de configuració i on trobar-los
- Secrets i bones pràctiques
- Monitorització i logs
- Tasques periòdiques i scripts
- Resolució d'incidències comunes
- Contactes i referències

---

## Introducció
Repositori: P0.0-ASIXc2BC-G05  
Ubicació fitxers de configuració: `/files` al repositori (veure enllaços README).  
Objectiu: servidor web (W-NCC), base dades (B-NCC), DHCP, servidor de fitxers (F-NCC) i clients.

---

## Requisits i accessos
- Accés SSH amb usuari administrador a cada màquina (web, db, router si aplica).
- Accés MySQL/MariaDB amb usuari amb permisos d'administració per crear usuaris i backups.
- Accés al dispositiu R-NCC per revisar rutes i VLANs (consola o SSH segons implementació).
- Eines recomanades: git, mysql client, mariadb-server, apache2/nginx, rsync, tar, cron.

---

## Desplegament de l'aplicació web (W-NCC)
1. Instal·lar dependències bàsiques:
   - Exemple Debian/Ubuntu:
     - sudo apt update
     - sudo apt install -y git apache2 php php-mysql unzip
2. Clonar el codi al DocumentRoot:
   - cd /var/www/html
   - git clone https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05.git
   - Ajustar propietats:
     - sudo chown -R www-data:www-data /var/www/html/2526-P0.0-ASIXc2BC-G05/public
3. Configuració del VirtualHost (ex. equipaments.conf):
   - Fitxer: ./files/apache2/equipaments.conf (veure repo)
   - Habilitar el site i reiniciar Apache:
     - sudo a2ensite equipaments.conf
     - sudo systemctl reload apache2
4. Fitxer de configuració de l'aplicació (no versionar):
   - Crear `config_admin.php` basant-se en la plantilla (veure exemple entregat).
   - Emmagatzemar credencials en variables d'entorn o un fitxer fora del repo.
   - Afegir `config_admin.php` a `.gitignore`.

Recomanació de seguretat:
- Forçar HTTPS (TLS), configurar certificats (Let's Encrypt) i redireccionar HTTP -> HTTPS.
- Limitar permisos d'arxius i directori (www-data amb mínims permisos).

---

## Gestió de la base de dades (B-NCC)

### Creació i usuari admin limitat
- Script suggerit: `mysql_init_admin.sql` (veure exemple).
- Crear usuari d'aplicació amb permisos mínims:
  - Usuari: `admin_app`@`localhost`
  - Permisos: SELECT, INSERT, UPDATE, DELETE sobre `Educacio.*`
  - No utilitzar root per l'aplicació.
- Si la web accedeix des d'una IP diferent, canviar l'host de l'usuari (`'admin_app'@'IP_DEL_WEB'`) amb precaució.

Comandes d'execució:
- mysql -u root -p < mysql_init_admin.sql

### Backups
- Script de backup: `backup_mysql.sh` (veure `/files/backup_mysql.sh`).
- Recomanacions:
  - Crear usuari `backup_user` amb permisos de `LOCK TABLES`, `SELECT`, `SHOW VIEW`, `TRIGGER` i `EVENT` si cal.
  - Emmagatzemar backups en un directori amb permisos restringits, i sincronitzar-los a un servidor remot amb rsync.
  - Exemple de crontab (backup diari a les 02:00):
    - 0 2 * * * /usr/local/bin/backup_mysql.sh >> /var/log/backup_mysql.log 2>&1
- Retenció: conservar mínim 7 dies; rotar amb `find /backups -type f -mtime +7 -delete` o eines específiques.

### Restauració
- Restorar amb mysql client:
  - mysql -u root -p Educacio < backup-file.sql
- Si falla, revisar permisos, versions de MySQL/MariaDB i caràcters (charset).

### Import de CSV
- Comanda SQL per IMPORT:
  - Utilitzar `LOAD DATA LOCAL INFILE '/path/equipaments_utf8.csv' INTO TABLE equipaments_educacio ...` (veure exemple al repo).
- Abans d'executar:
  - Col·locar el fitxer CSV al servidor de B-NCC.
  - Assegurar `local_infile=1` en la configuració MySQL si s'usa `LOAD DATA LOCAL`.
  - Fer un backup previ.

---

## Gestió DHCP
Fitxer de configuració principal: `/etc/dhcp/dhcpd.conf` — plantilla disponible a `/files/dhcp/dhcpd.conf`.

Punts clau:
- Revisar rangs, gateways i DNS.
- Hosts amb reserves (ex. PC0_CLIWIN, PC1_CLILIN) — actualitzar MAC addresses.
- Reiniciar servei després de canvis:
  - sudo systemctl restart isc-dhcp-server
- Logs a: `/var/log/syslog` (filtrar per `dhcpd`).

---

## Router R-NCC (resum)
- Paper: encaminador entre VLANs, gateway exterior.
- Comprovar:
  - Rutes estàtiques o protocols de rerouting si s'usa.
  - ACLs i polítiques de firewall entre VLANs i DMZ.
  - NAT/port forwarding si es necessiten serveis exposats a Internet (preferible PFS i revocations).
- Fitxer de configuració local: `./files/router_r-ncc.conf` (veure repo).
- Mantingues còpia de la configuració i documenta canvis; aplicar control de versions offline.

---

## Fitxers de configuració i ubicacions (resum)
- Repo /files:
  - router_r-ncc.conf — configuració router
  - dhcp/dhcpd.conf — DHCP
  - mysql_init.sql — esquema DB
  - backup_mysql.sh — script backups
  - apache2/equipaments.conf — Apache site
  - webserver_config.conf / config_admin.php (plantilla) — configuració app (no versionar amb secrets)
- Ajusta rutes locals segons la instal·lació a producció.

---

## Secrets i bones pràctiques
- No versionar contrasenyes ni claus. Afegir a .gitignore:
  - /config_admin.php
  - /.env
- Emmagatzemar secrets mitjançant:
  - Variables d'entorn (Systemd unit, `/etc/environment` amb permisos 600)
  - Docker secrets o Vault per entorns containeritzats
- Generar contrasenyes fortes (ex. openssl rand -base64 32) i rotar periòdicament.

---

## Monitorització i logs
- Logs importants:
  - Apache/Nginx: /var/log/apache2/*.log
  - MySQL: /var/log/mysql/*.log o /var/log/mysqld.log
  - DHCP: /var/log/syslog (filtrar)
  - Aplicació: ruta `logs/` dins del projecte (NO versionar)
- Recomanacions:
  - Integrar amb eina de monitorització (Prometheus + Grafana, Zabbix, Nagios).
  - Alertes per errors 5xx, temps de resposta elevat, espai en disc, fallades de backups i connexió DB.
  - Configurar rotació de logs (logrotate) per a arxius d'aplicació i sistemes.

---

## Tasques periòdiques i scripts
- Backups diaris de B-NCC (script `backup_mysql.sh`):
  - Comprovar integritat i restaurabilitat mensualment.
- Actualitzacions de seguretat:
  - Parar temps de manteniment per actualitzar paquets (apt upgrade) i reiniciar serveis si cal.
- Revisions de seguretat:
  - Analitzar permisos de fitxers i comptes, revisar logs d'accés i errors.
- Crontab:
  - 0 2 * * * /usr/local/bin/backup_mysql.sh
  - 0 4 * * 0 /usr/bin/apt update && /usr/bin/apt -y upgrade >> /var/log/apt_upgrade.log 2>&1

---

## Resolució d'incidències comunes
- Web: 500 Internal Server Error
  - Revisar logs Apache `/var/log/apache2/error.log`
  - Comprovar config d'aplicació (DB credentials)
- DB: Connexió refusada
  - Verificar que MySQL està en execució (systemctl status mysql)
  - Comprovar `bind-address` en `mysqld.cnf` si la connexió és remota
- DHCP: Clients no reben IP
  - Revisar servei (`systemctl status isc-dhcp-server`)
  - Comprovar conflictes d'IP, rangs i bloqueigs MAC
- Backups: fitxers corruptes o incapaços de restaurar
  - Comprovar la mida, checksum i provar restauració en entorn de staging
  - Revisar permisos i espai en disc

---

## Comandes útils ràpides
- Reiniciar serveis:
  - sudo systemctl restart apache2
  - sudo systemctl restart mysql
  - sudo systemctl restart isc-dhcp-server
- Ver estat:
  - sudo systemctl status apache2
  - sudo systemctl status mysql
- Comprovar ports (netstat / ss):
  - ss -tulnp | grep :80
  - ss -tulnp | grep :3306
- Consultar logs:
  - tail -f /var/log/apache2/error.log
  - tail -f /var/log/mysql/error.log

---

## Contactes i referències
- Repositoris i fitxers: https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05
- Proofhub (planificació): https://itecbcn.proofhub.com/bapplite/#app/todos/project-9335566085/list-269936034851
