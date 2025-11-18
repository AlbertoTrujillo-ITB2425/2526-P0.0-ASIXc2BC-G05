#!/bin/bash
# Instalador + Monitor de red con cron

LOGFILE="/var/log/network_monitor.log"

log(){ echo "$(date '+%F %T') - $1" | tee -a "$LOGFILE"; }

# Instalar dependencias según distro
install_deps(){
  if command -v apt-get >/dev/null; then sudo apt-get update && sudo apt-get install -y iputils-ping netcat cron
  elif command -v dnf >/dev/null; then sudo dnf install -y iputils netcat cronie
  elif command -v yum >/dev/null; then sudo yum install -y iputils netcat cronie
  elif command -v zypper >/dev/null; then sudo zypper install -y iputils netcat cron
  else log "❌ No se detectó gestor de paquetes"; exit 1; fi
}

# Verificar comandos
for cmd in ping nc crontab; do command -v $cmd >/dev/null || install_deps; done

log "=== INICI MONITORITZACIÓ ==="

# Hosts
declare -A HOSTS=(
 ["Router"]="192.168.5.1" ["Web"]="192.168.5.20" ["DNS"]="192.168.5.30"
 ["File"]="192.168.5.40" ["DB"]="192.168.5.80" ["DHCP"]="192.168.5.140"
 ["Win"]="192.168.5.130" ["Linux"]="192.168.5.131"
)

for h in "${!HOSTS[@]}"; do ip=${HOSTS[$h]}
  ping -c1 -W2 $ip &>/dev/null && log "✅ $h ($ip) accesible" || log "❌ $h ($ip) no accesible"
done

# Servicios
declare -A SVC=(
 ["HTTP"]="192.168.5.20:80" ["HTTPS"]="192.168.5.20:443" ["DNS"]="192.168.5.30:53"
 ["FTP"]="192.168.5.40:21" ["SSH"]="192.168.5.40:22" ["MySQL"]="192.168.5.80:3306"
)

for s in "${!SVC[@]}"; do hp=${SVC[$s]} h=${hp%:*} p=${hp#*:}
  nc -z -w3 $h $p &>/dev/null && log "✅ $s ($hp) disponible" || log "❌ $s ($hp) no disponible"
done

log "=== FI MONITORITZACIÓ ==="

# Añadir cron cada 10 min si no existe
( sudo crontab -l 2>/dev/null | grep -q network_monitor.sh ) || \
  (sudo crontab -l 2>/dev/null; echo "*/10 * * * * /usr/local/bin/network_monitor.sh") | sudo crontab -
