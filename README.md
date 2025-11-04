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

La planificació s'ha fet a **Proofhub** en **tres sprints** de **dues setmanes** i **cinc hores per setmana**. La **durada total** és de **sis setmanes** fins al **18/11**. La documentació i la configuració estan versionades al repositori git amb el nom **P0.0-ASIXc2BC-G05**. Enllaç a Proofhub: [Tauler del projecte a Proofhub](https://itecbcn.proofhub.com/bapplite/#app/todos/project-9335566085/list-269936034851)

---

## 2. Esquema de xarxa

L’arquitectura desplegada es representa al diagrama següent. Pots descarregar el fitxer de l’esquema de xarxa: [Descarregar esquema de xarxa (Packet Tracer)](https://drive.google.com/file/d/1sruDIO3lY_b99p6khwERN0n-WELGoI5u/view?usp=sharing)

<img width="910" height="565" alt="Captura de pantalla de 2025-10-28 15-11-17" src="https://github.com/user-attachments/assets/bae3db11-eba9-46ba-a99c-0463bbbf78d0" />

La topologia usa el **router R-NCC** com a encaminador central amb **servidors a la VLAN de serveis** i **adreçament IP separat** per facilitar l’**escalabilitat** i la **gestió**.

---

## 3. Infraestructura desplegada

### Router R-NCC
- Funció: encaminador entre subxarxes, gateway de sortida, punt de control entre VLANs i la xarxa de serveis.
- Fitxer complet: ./files/router_r-ncc.conf  
  - Descarregar: https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/router_r-ncc.conf

### DHCP Server
- Funció: assigna adreces IP dinàmiques per les VLANs, amb reserves per servidors i dispositius.
- Fitxer complet: ./files/dhcpd.conf  
  - Descarregar: https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/dhcpd.conf

### Database Server B-NCC:
- B-NCC: servidor de base de dades (MySQL/MariaDB) amb còpies de seguretat i permisos restringits.
- Fitxers: ./files/mysql_init.sql, ./files/backup_mysql.sh, ./files/webserver_config.conf  
  - Descarregar mysql_init.sql: https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/mysql_init.sql  
  - Descarregar backup_mysql.sh: https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/backup_mysql.sh

### Web Server W-NCC
- Funció: servidor que serveix aplicació i es connecta amb B-NCC amb usuari d'aplicació.
- Fitxer de configuració: ./files/webserver_config.conf  
  - Descarregar webserver_config.conf: https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/webserver_config.conf
- Estructura:
  - public/: arxius servits pel servidor web (DocumentRoot)
  - src/: codi font (models, controllers, helpers)
  - config/: configuració (config.php). No commetis secrets.
  - sql/: DDL i scripts d'inicialització
  - logs/: logs d'execució (ignorar a git)

---

### File Server F-NCC

- Funció: poder compartir fitxers a la xarxa amb altres usuaris corporatius o clients a través de la DMZ.

---

### Clients (CLIWIN i CLILIN)
- CLIWIN: client Windows que obté IP per DHCP i accedeix a recursos compartits.  
- CLILIN: client Linux que utilitza DHCP o IP estàtica i comprova conectivitat.

---

## 4. Configuració de serveis

A continuació es mostren fragments de configuració i explicacions per a cada servei implementat.

### DHCP fitxer de configuració (/etc/dhcp/dhcpd.conf)
``` bash
option domain-name "example.org";
option domain-name-servers ns1.example.org, ns2.example.org;

default-lease-time 600;
max-lease-time 7200;

ddns-update-style none;


default-lease-time 600;
max-lease-time 7200;
authoritative;

subnet 192.168.5.0 netmask 255.255.255.0 {
  option routers 192.168.5.1;
  option subnet-mask 255.255.255.0;
  option domain-name-servers 192.168.5.30;
}

host PC0_CLIWIN {
  hardware ethernet 52:54:00:1E:47:7A; #Cambia per la MAC del teu client Windows
  fixed-address 192.168.5.130; 
}

host PC1_CLILIN {
  hardware ethernet 52:54:00:39:be:b1; #Cambia per la MAC del teu client linux
  fixed-address 192.168.5.131;
}
```
- Fitxer complet i descarrega: ./files/dhcpd.conf  
  - Descarregar: https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/dhcpd.conf

### MySQL creació de la base de dades
```sql
CREATE DATABASE Educacio;
USE Educacio;

CREATE TABLE equipaments_educacio (
    register_id INTEGER PRIMARY KEY,
    name TEXT,
    institution_id TEXT,
    institution_name TEXT,
    created TEXT,
    modified TEXT,
    addresses_roadtype_id TEXT,
    addresses_roadtype_name TEXT,
    addresses_road_id INTEGER,
    addresses_road_name TEXT,
    addresses_start_street_number TEXT,
    addresses_end_street_number TEXT,
    addresses_neighborhood_id TEXT,
    addresses_neighborhood_name TEXT,
    addresses_district_id TEXT,
    addresses_district_name TEXT,
    addresses_zip_code TEXT,
    addresses_town TEXT,
    addresses_main_address INTEGER,
    addresses_type TEXT,
    values_id INTEGER,
    values_attribute_id INTEGER,
    values_category TEXT,
    values_attribute_name TEXT,
    values_value TEXT,
    values_outstanding INTEGER,
    values_description TEXT,
    secondary_filters_id INTEGER,
    secondary_filters_name TEXT,
    secondary_filters_fullpath TEXT,
    secondary_filters_tree TEXT,
    secondary_filters_asia_id TEXT,
    geo_epgs_25831_x REAL,
    geo_epgs_25831_y REAL,
    geo_epgs_4326_lat REAL,
    geo_epgs_4326_lon REAL,
    estimated_dates TEXT,
    start_date TEXT,
    end_date TEXT,
    timetable TEXT
);
```
- Fitxer complet i descarrega: ./files/mysql_init.sql  
  - Descarregar: https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/mysql_init.sql

### mySQL importar dades csv 
```sql
LOAD DATA LOCAL INFILE '/home/isard/equipaments_utf8.csv'
INTO TABLE equipaments_educacio
CHARACTER SET utf8mb4
FIELDS TERMINATED BY ','
OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(@register_id, name, institution_id, institution_name, created, modified,
 addresses_roadtype_id, addresses_roadtype_name, addresses_road_id,
 addresses_road_name, addresses_start_street_number, addresses_end_street_number,
 addresses_neighborhood_id, addresses_neighborhood_name, addresses_district_id,
 addresses_district_name, addresses_zip_code, addresses_town,
 @addresses_main_address, addresses_type, values_id, values_attribute_id,
 values_category, values_attribute_name, values_value, @values_outstanding,
 values_description, secondary_filters_id, secondary_filters_name,
 secondary_filters_fullpath, secondary_filters_tree, secondary_filters_asia_id,
 @geo_epgs_25831_x, @geo_epgs_25831_y, @geo_epgs_4326_lat, @geo_epgs_4326_lon,
 estimated_dates, start_date, end_date, timetable)
SET
 register_id = TRIM(LEADING 0xEFBBBF FROM @register_id),
 addresses_main_address = IF(@addresses_main_address = 'True', 1, 0),
 values_outstanding = IF(@values_outstanding = 'True', 1, 0),
 geo_epgs_25831_x = NULLIF(@geo_epgs_25831_x, ''),
 geo_epgs_25831_y = NULLIF(@geo_epgs_25831_y, ''),
 geo_epgs_4326_lat = NULLIF(@geo_epgs_4326_lat, ''),
 geo_epgs_4326_lon = NULLIF(@geo_epgs_4326_lon, '');
```
- Recorda col·locar el fitxer .csv en la ruta indicada abans d'executar.

### Desplegament de la aplicacio web
- Instalar git si no el tenim
```bash
sudo apt install git
```
```bash
cd /var/www/html
git clone https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05.git
```
- Fitxer de configuració del servidor web i descarrega: ./files/apache2/equipaments.conf
  - Descarregar: https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/apache2/equipaments.conf

### Activar HTTPS:
``` bash
sudo a2enmod ssl
sudo a2enmod headers
sudo a2enmod rewrite
sudo systemctl restart apache2
```
```bash
sudo openssl req -x509 -newkey rsa:4096 -keyout /etc/ssl/private/equipaments.key -out /etc/ssl/certs/equipaments.crt -days 365
.......+......+..+...+...+.......+............+..+.+..+.......+...+...........+..........+.....+....+...+..+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*.+......+......+.+.....+...+.+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*......+....+.........+.....+..........+..+....+....................+.+...+...........+...................+...+.....+..........+...+.........+.................+...+..........+........+........................+....+.........+...............+...........+......+.+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
...+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*..+.+......+...+..+....+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*...............+..+...+.+...+.....+...............+.+......+......+..+......+......+......+..........+.........+...+.....+.+.....+.........+.+...........+..................+.+.....+.......+...............+...+.....+.........................+......+......+..................+...........+...............+...+..........+...+...............+.....+.............+.....+.+......+...+............+...........................+...........+.+.........+...........+.+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Enter PEM pass phrase:
Verifying - Enter PEM pass phrase:
-----
You are about to be asked to enter information that will be incorporated
into your certificate request.
What you are about to enter is what is called a Distinguished Name or a DN.
There are quite a few fields but you can leave some blank
For some fields there will be a default value,
If you enter '.', the field will be left blank.
-----
Country Name (2 letter code) [AU]:ES
State or Province Name (full name) [Some-State]:Barcelona
Locality Name (eg, city) []:Barcelona
Organization Name (eg, company) [Internet Widgits Pty Ltd]:G5
Organizational Unit Name (eg, section) []:G5
Common Name (e.g. server FQDN or YOUR name) []:W-NCC
Email Address []:admin@G5.cat
``` 
- Afegim a /etc/apache2/webserver_config.conf
``` bash
 <VirtualHost *:443>
    ServerAdmin webmaster@g5.cat   
    ServerName g5.cat           
    DocumentRoot /var/www/html                

    <Directory /var/www/html/equipaments-app>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/equipaments.crt
    SSLCertificateKeyFile /etc/ssl/private/equipaments.key

    ErrorLog ${APACHE_LOG_DIR}/equipaments_ssl_error.log
    CustomLog ${APACHE_LOG_DIR}/equipaments_ssl_access.log combined
</VirtualHost>
```


---

## Fitxers de configuracio: 
Tots els fitxers de configuració i scripts es troben a la carpeta /files del repositori. Podeu descarregar-los directament fent clic als enllaços següents:

- router_r-ncc.conf — https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/router_r-ncc.conf  
- dhcpd.conf — https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/dhcp/dhcpd.conf
- mysql_init.sql — https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/mysql_init.sql  
- backup_mysql.sh — https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/backup_mysql.sh  
- webserver_config.conf — https://github.com/AlbertoTrujillo-ITB2425/2526-P0.0-ASIXc2BC-G05/blob/main/files/webserver_config.conf
