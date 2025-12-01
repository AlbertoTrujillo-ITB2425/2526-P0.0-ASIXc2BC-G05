#!/bin/bash
# Monitor de xarxa amb registre i cron
# Aquest script comprova la connectivitat de diversos dispositius i serveis de la xarxa.
# Guarda els resultats en un fitxer de log i programa la seva execució periòdica amb cron.

# Fitxer de registre
LOGFILE="/var/log/network_monitor.log"

# Funció per escriure missatges al log amb data i hora
log(){ echo "$(date '+%F %T') - $1" | tee -a "$LOGFILE"; }

# Funció per instal·lar dependències segons el gestor de paquets detectat
install_deps(){
  if command -v apt-get >/dev/null; then
    sudo apt-get update && sudo apt-get install -y iputils-ping netcat cron
  elif command -v dnf >/dev/null; then
    sudo dnf install -y iputils netcat cronie
  elif command -v yum >/dev/null; then
    sudo yum install -y iputils netcat cronie
  elif command -v zypper >/dev/null; then
    sudo zypper install -y iputils netcat cron
  else
    log "No s'ha detectat cap gestor de paquets compatible"; exit 1
  fi
}

# Comprovem que existeixin els comandos essencials
for cmd in ping nc crontab; do command -v "$cmd" >/dev/null || install_deps; done

log "Inici de monitorització"

# Hosts de la xarxa segons la topologia indicada
declare -A HOSTS=(
  [Router]="192.168.5.1"
  [W-NCC]="192.168.5.20"
  [D-NCC]="192.168.5.30"
  [F-NCC]="192.168.5.40"
  [B-NCC]="192.168.5.140"
  [DHCP]="192.168.5.150"
  [PC0-Linux]="192.168.5.160"
  [PC1-Windows]="192.168.5.161"
)

# Comprovació de connectivitat amb ping
for nom in "${!HOSTS[@]}"; do
  ip=${HOSTS[$nom]}
  if ping -c1 -W2 "$ip" &>/dev/null; then
    log "$nom ($ip) és accessible"
  else
    log "$nom ($ip) no és accessible"
  fi
done

# Serveis principals associats als servidors
declare -A SERVEIS=(
  [HTTP]="192.168.5.20:80"
  [HTTPS]="192.168.5.20:443"
  [DNS]="192.168.5.30:53"
  [FTP]="192.168.5.40:21"
  [SSH]="192.168.5.40:22"
)

# Comprovació de disponibilitat de serveis amb netcat
for servei in "${!SERVEIS[@]}"; do
  hostport=${SERVEIS[$servei]}
  host=${hostport%:*} port=${hostport#*:}
  if nc -z -w3 "$host" "$port" &>/dev/null; then
    log "Servei $servei ($hostport) disponible"
  else
    log "Servei $servei ($hostport) no disponible"
  fi
done

log "Fi de monitorització"

# Afegir al cron la tasca si no existeix ja
CRON_CMD="*/10 * * * * /usr/local/bin/network_monitor.sh"
(crontab -l 2>/dev/null | grep -F "$CRON_CMD") >/dev/null || \
  (crontab -l 2>/dev/null; echo "$CRON_CMD") | crontab -
