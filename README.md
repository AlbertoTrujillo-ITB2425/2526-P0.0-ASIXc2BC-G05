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

L’arquitectura desplegada es representa al diagrama següent. Pots descarregar el fitxer de l’esquema de xarxa: [Descarregar esquema de xarxa (Packet Tracer)](https://drive.google.com/file/d/1atEO0mJYaNl4XfbM8BtlbaUDIN4p2D8S/view?usp=sharing)

![Esquema de Xarxa](https://github.com/user-attachments/assets/12fdae6a-c0b8-4ae6-8dcf-2a22dcaad1b4)
</div>
![DHCP Server (vista general)](https://github.com/user-attachments/assets/5f981a11-8565-4214-b966-b415ec1f0aa3)
</div>

La topologia usa el **router R-NCC** com a encaminador central amb **servidors a la VLAN de serveis** i **adreçament IP separat** per facilitar l’**escalabilitat** i la **gestió**.

---

## 3. Infraestructura desplegada

### Router R-NCC
- Funció: encaminador entre subxarxes, gateway de sortida, punt de control entre VLANs i la xarxa de serveis.
- Fitxer complet: ./files/router_r-ncc.conf

### DHCP Server
Text box — Descripción breve:
- Funció: assigna adreces IP dinàmiques per les VLANs, amb reserves per servidors i dispositius.
- Fitxer complet: ./files/dhcpd.conf

### Database Server B-NCC:
- B-NCC: servidor de base de dades (MySQL/MariaDB) amb còpies de seguretat i permisos restringits.

- Fitxers: ./files/mysql_init.sql, ./files/backup_mysql.sh, ./files/webserver_config.conf

### Web Server W-NCC
- Funció: servidor que serveix aplicació i es connecta amb B-NCC amb usuari d'aplicació.
- ***Estructura***:
- public/: arxius servits pel servidor web (DocumentRoot)
- src/: codi font (models, controllers, helpers)
- config/: configuració (config.php). No commetis secrets.
- sql/: DDL i scripts d'inicialització
- logs/: logs d'execució (ignorar a git)

---

### File Server F-NCC

- Funció: poder compartir fitchers a la xarxa amb altres usuaris corporatius o clients a traves de la DMZ
---

### Clients (CLIWIN i CLILIN)
Text box — Descripció breu:
- CLIWIN: client Windows que obté IP per DHCP i accedeix a recursos compartits (NFS via client o SFTP).
- CLILIN: client Linux que utilitza DHCP o IP estàtica, munta NFS i comprova conectivitat.

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

El fitxer defineix **subxarxes**, **rangs d’IP** i **opcions globals** com **DNS** i **gateway**. Inclou **reserves** per a servidors i dispositius de xarxa (exemple: `option domain-name-servers 8.8.8.8;`).

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
