# 2526-P0.0-ASIXc2BC-G05

## Índex

1. [Introducció](#introducció)
2. [Objectius](#objectius)
3. [Planificació del projecte](#planificació-del-projecte)
4. [Esquema de xarxa](#esquema-de-xarxa)
    - [Descàrrega de l'esquema](#descàrrega-de-lesquema)
    - [Visualització de l'esquema](#visualització-de-lesquema)
5. [Infraestructura desplegada](#infraestructura-desplegada)
6. [Configuració de serveis](#configuració-de-serveis)
7. [Proves realitzades](#proves-realitzades)
8. [Conclusions i millores](#conclusions-i-millores)

---

## 1. Introducció

Aquest document recull la memòria tècnica del projecte **P0.0-ASIXc2gC-Gnn** corresponent al Mòdul 0379, Projecte intermodular d'administració de sistemes informàtics en xarxa. El projecte consisteix en el desplegament d’una infraestructura per a una aplicació multicapa que integra diversos serveis (Web Server, Monitor de xarxes, SSH, BBDD, DHCP, DNS, FTP) en diferents màquines virtuals i subxarxes, seguint el model de zones DMZ, Intranet i NAT.

---

## 2. Objectius

- Preparar una infraestructura funcional per allotjar una aplicació multicapa.
- Planificar i documentar totes les tasques del projecte.
- Desplegar i configurar els serveis requerits (web, BBDD, DNS, DHCP, FTP…).
- Carregar i visualitzar dades obertes a la base de dades.
- Garantir l’accés centralitzat amb l’usuari `bchecker` a tots els sistemes.
- Documentar l’arquitectura mitjançant un diagrama de xarxa.

---

## 3. Planificació del projecte

La planificació s’ha realitzat utilitzant Proofhub, estructurant el treball en 3 sprints quinzenals de 10 hores cadascun. El backlog i la divisió de tasques han estat definits segons la nomenclatura i requeriments facilitats:

- Sprints: 3 (2 setmanes cadascun, 5h/setmana)
- Durada total: 6 setmanes (fins al 18/11)
- Documentació i configuració versionats al repositori git: **P0.0-ASIXc2gC-Gnn**

---

## 4. Esquema de xarxa

L’arquitectura desplegada es representa en el següent diagrama:

### Descàrrega de l'esquema

Podeu descarregar el fitxer de l’esquema de xarxa aquí:  
[Descarregar esquema de xarxa (Packet Tracer)](./2526-P0.0-ASIXc2BC-G05_EsquemaDeXarxa.pkt)

### Visualització de l'esquema

A continuació es mostra una imatge representativa de l’esquema de xarxa:

![Esquema de Xarxa](./esquema_de_xarxa.png)

---

## 5. Infraestructura desplegada

**Xarxes:**
- DMZ
- Intranet
- NAT

**Dispositius principals:**
- Router (R-NCC)
- Web Server (W-NCC)
- SSH Server
- BBDD (B-NCC, MySQL, dades carregades)
- DHCP Server
- DNS Server (resolució de R-NCC i R)
- FTP Server (F-NCC)
- 2 PCs clients (1 Windows, 1 Linux)

---

## 6. Configuració de serveis

*Detall de la configuració de cada servei: usuaris, contrasenyes, configuració de xarxa, etc.*

---

## 7. Proves realitzades

- Aplicació de mostra per visualitzar contingut de la base de dades.
- Validació de connectivitat i serveis desplegats.
- (Opcional) Detecció i separació de dades susceptibles dins la base de dades.

---

## 8. Conclusions i millores

*Valoració dels resultats, dificultats trobades, propostes de millora…*

---

> **Nota:** Cal pujar els fitxers `2526-P0.0-ASIXc2BC-G05_EsquemaDeXarxa.pkt` i la corresponent imatge `esquema_de_xarxa.png` al repositori per tal que siguin accessibles des d’aquest document.
