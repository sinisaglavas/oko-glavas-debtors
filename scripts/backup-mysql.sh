#!/usr/bin/env bash

set -euo pipefail

: "${DB_HOST:?DB_HOST is required}"
: "${DB_PORT:=3306}"
: "${DB_DATABASE:?DB_DATABASE is required}"
: "${DB_USERNAME:?DB_USERNAME is required}"
: "${DB_PASSWORD:?DB_PASSWORD is required}"

backup_dir="${BACKUP_DIR:-$HOME/backups/oko-glavas-debtors}"
retention_days="${BACKUP_RETENTION_DAYS:-30}"
timestamp="$(date '+%Y-%m-%d_%H-%M-%S')"
backup_file="$backup_dir/${DB_DATABASE}_${timestamp}.sql.gz"

mkdir -p "$backup_dir"

MYSQL_PWD="$DB_PASSWORD" mysqldump \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --user="$DB_USERNAME" \
    --single-transaction \
    --routines \
    --events \
    --skip-lock-tables \
    "$DB_DATABASE" | gzip > "$backup_file"

find "$backup_dir" -type f -name '*.sql.gz' -mtime "+$retention_days" -delete

echo "Backup created: $backup_file"
