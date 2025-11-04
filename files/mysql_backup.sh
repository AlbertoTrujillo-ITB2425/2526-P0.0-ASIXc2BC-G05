#!/bin/bash
DATE=$(date +"%Y%m%d%H%M")

DB_NAME="Educacio"
BACKUP_DIR="$HOME/DB_Backup"

mkdir -p $BACKUP_DIR

BACKUP_FILE="$BACKUP_DIR/$DB_NAME-backup-$DATE.sql"

MYSQL_USER="bchecker"
MYSQL_PASS="bchecker121"

mysqldump -u $MYSQL_USER -p$MYSQL_PASS $DB_NAME > $BACKUP_FILE

if [ $? -eq 0 ]; then
    echo "Copia de seguridad de $DB_NAME completada exitosamente: $BACKUP_FILE"
else
    echo "Hubo un error al hacer la copia de seguridad de $DB_NAME"
fi
