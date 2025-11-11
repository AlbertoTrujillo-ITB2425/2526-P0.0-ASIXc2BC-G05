#!/bin/bash

# Configuración
BACKUP_DIR="/backups"
DATE=$(date +%Y%m%d_%H%M%S)
LOG_FILE="/var/log/backup.log"
HOST_IP=$(ip -4 addr show enp2s0 | grep -oP '(?<=inet\s)\d+(\.\d+){3}')

# Función para loguear mensajes
log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "$LOG_FILE"
}

# Función para ejecutar comandos con control de errores
run_cmd() {
    "$@"
    if [ $? -ne 0 ]; then
        log "❌ Error ejecutando: $*"
        exit 1
    fi
}

# Crear directorio de backups si no existe
[ ! -d "$BACKUP_DIR" ] && run_cmd mkdir -p "$BACKUP_DIR"

log "Iniciando backup en $HOST_IP (interfaz enp2s0)"

case "$HOST_IP" in
    "192.168.5.20") # Web Server
        log "Backup Web Server..."
        run_cmd tar -czf "$BACKUP_DIR/web_configs_$DATE.tar.gz" \
            /etc/apache2/ /var/www/ /etc/ssl/
        ;;
    "192.168.5.30") # DNS Server
        log "Backup DNS Server..."
        run_cmd tar -czf "$BACKUP_DIR/dns_configs_$DATE.tar.gz" \
            /etc/bind/ /var/cache/bind/
        ;;
    "192.168.5.40") # File Server
        log "Backup File Server..."
        run_cmd tar -czf "$BACKUP_DIR/file_configs_$DATE.tar.gz" \
            /etc/vsftpd.conf /etc/samba/ /srv/samba/ /home/ftpuser/
        ;;
    "192.168.5.80") # Database Server
        log "Backup Database Server..."
        run_cmd mysqldump -u root -p'1234' --all-databases > "$BACKUP_DIR/mysql_$DATE.sql"
        run_cmd tar -czf "$BACKUP_DIR/db_configs_$DATE.tar.gz" /etc/mysql/
        ;;
    "192.168.5.140") # DHCP Server
        log "Backup DHCP Server..."
        run_cmd tar -czf "$BACKUP_DIR/dhcp_configs_$DATE.tar.gz" \
            /etc/dhcp/ /var/lib/dhcp/
        ;;
    *)
        log "⚠️ IP $HOST_IP no reconocida en enp2s0, no se ejecuta ningún backup."
        ;;
esac

# Eliminar backups antiguos (>30 días)
run_cmd find "$BACKUP_DIR" -type f -mtime +30 -delete

log "✅ Backup completado correctamente para $HOST_IP (enp2s0)"
