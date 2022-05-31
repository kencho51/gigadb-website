#!/usr/bin/env bash

source ./.env

export PATH=/usr/local/bin/:$PATH
# preflight checks
which docker-compose

# instantiate a container for a PostgreSQL 9.3 instance

docker-compose up -d pg9_3
sleep 5
docker-compose ps
docker-compose logs pg9_3

# Calculate dates
latest=$(date --date="1 days ago" +"%Y%m%d")
twoDaysAgo=$(date --date="2 days ago" +"%Y%m%d")
threeDaysAgo=$(date --date="3 days ago" +"%Y%m%d")
backupDate="$1"

# Default converted backup (from Ansible properties) and legacy postgres version
defaultDB="/home/centos/database_bootstrap.backup"
version=$(docker-compose run --rm updater psql --version | cut -d' ' -f 3 | tr -d '\n\r' )

# Run files-url-updater
if [[ -z $backupDate || $backupDate -eq "latest" ]];then
  echo yes | docker-compose run --rm updater ./yii dataset-files/download-restore-backup --latest
  downloadLatestRestoreStatus=$?
else
  echo yes | docker-compose run --rm updater ./yii dataset-files/download-restore-backup --date $backupDate
  downloadSpecificRestoreStatus=$?
fi

# Convert backup using legacy postgresql client
if [[ $downloadLatestRestoreStatus -eq 0 && -z $downloadSpecificRestoreStatus ]];then
  docker-compose run --rm updater pg_dump --host=pg9_3 -p 5432  --username=gigadb  --clean --create --schema=public --no-privileges --no-tablespaces --dbname=gigadb --file=converted/gigadbv3_${latest}_v${version}.backup
  convertStatus=$?
fi

if [[ $downloadSpecificRestoreStatus -eq 0 && -z $downloadLatestRestoreStatus ]];then
  docker-compose run --rm updater pg_dump --host=pg9_3 -p 5432  --username=gigadb  --clean --create --schema=public --no-privileges --no-tablespaces --dbname=gigadb --file=converted/gigadbv3_${backupDate}_v${version}.backup
  convertStatus=$?
fi

# shut down the PostgreSQL 9.3 instance
docker-compose down -v

# Terminate other connections to RDS instance
export PGPASSWORD=$DB_PG_PASSWORD; psql -U $DB_PG_USER -d postgres -h $DB_PG_HOST -p 5432 -c  "SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname='gigadb';"

# Load the backup dump to RDS using native postgresql client
if [[ $downloadSpecificRestoreStatus -eq 0 && $convertStatus -eq 0 && -z $downloadLatestRestoreStatus ]];then
  echo "Loading gigadbv3_${backupDate}_v${version}.backup"
  export PGPASSWORD=$DB_PG_PASSWORD; psql -U $DB_PG_USER -d postgres -h $DB_PG_HOST -p 5432 -c "drop database if exists $DB_PG_DATABASE"
  export PGPASSWORD=$DB_PG_PASSWORD; psql -U $DB_PG_USER -d postgres -h $DB_PG_HOST -p 5432 -c "create database $DB_PG_DATABASE owner $DB_PG_USER"
  export PGPASSWORD=$DB_PG_PASSWORD; psql -U $DB_PG_USER -h $DB_PG_HOST -p 5432 < converted/gigadbv3_${backupDate}_v${version}.backup
  loadStatus=$?
elif [[ $downloadLatestRestoreStatus -eq 0 && $convertStatus -eq 0 && -z $downloadSpecificRestoreStatus ]];then
  echo "Loading gigadbv3_${latest}_v${version}.backup"
  export PGPASSWORD=$DB_PG_PASSWORD; psql -U $DB_PG_USER -d postgres -h $DB_PG_HOST -p 5432 -c "drop database if exists $DB_PG_DATABASE"
  export PGPASSWORD=$DB_PG_PASSWORD; psql -U $DB_PG_USER -d postgres -h $DB_PG_HOST -p 5432 -c "create database $DB_PG_DATABASE owner $DB_PG_USER"
  export PGPASSWORD=$DB_PG_PASSWORD; psql -U $DB_PG_USER -h $DB_PG_HOST -p 5432 < converted/gigadbv3_${latest}_v${version}.backup
  loadStatus=$?
elif [[ -f converted/gigadbv3_${twoDaysAgo}_v${version}.backup ]];then
  echo "Loading gigadbv3_${twoDaysAgo}_v${version}.backup from two days ago"
  export PGPASSWORD=$DB_PG_PASSWORD; psql -U $DB_PG_USER -d postgres -h $DB_PG_HOST -p 5432 -c "drop database if exists $DB_PG_DATABASE"
  export PGPASSWORD=$DB_PG_PASSWORD; psql -U $DB_PG_USER -d postgres -h $DB_PG_HOST -p 5432 -c "create database $DB_PG_DATABASE owner $DB_PG_USER"
  export PGPASSWORD=$DB_PG_PASSWORD; psql -U $DB_PG_USER -h $DB_PG_HOST -p 5432 < converted/gigadbv3_${twoDaysAgo}_v${version}.backup
  loadStatus=$?
elif [[ -f converted/gigadbv3_${threeDaysAgo}_v${version}.backup ]];then
  echo "Loading gigadbv3_${threeDaysAgo}_v${version}.backup from three days ago"
  export PGPASSWORD=$DB_PG_PASSWORD; psql -U $DB_PG_USER -d postgres -h $DB_PG_HOST -p 5432 -c "drop database if exists $DB_PG_DATABASE"
  export PGPASSWORD=$DB_PG_PASSWORD; psql -U $DB_PG_USER -d postgres -h $DB_PG_HOST -p 5432 -c "create database $DB_PG_DATABASE owner $DB_PG_USER"
  export PGPASSWORD=$DB_PG_PASSWORD; psql -U $DB_PG_USER -h $DB_PG_HOST -p 5432 < converted/gigadbv3_${threeDaysAgo}_v${version}.backup
  loadStatus=$?
else
  echo "Loading default backup /home/centos/database_bootstrap.backup"
  export PGPASSWORD=$DB_PG_PASSWORD; psql -U $DB_PG_USER -d postgres -h $DB_PG_HOST -p 5432 -c "drop database if exists $DB_PG_DATABASE"
  export PGPASSWORD=$DB_PG_PASSWORD; psql -U $DB_PG_USER -d postgres -h $DB_PG_HOST -p 5432 -c "create database $DB_PG_DATABASE owner $DB_PG_USER"
  export PGPASSWORD=$DB_PG_PASSWORD; psql -U $DB_PG_USER -h $DB_PG_HOST -p 5432 < "$defaultDB"
  loadStatus=$?
fi

# Update schema

if [[ $loadStatus -eq 0 ]];then
  pushd .
  cd ../gigadb-website-develop
  docker-compose run --rm  application ./protected/yiic custommigrations dropconstraints
  docker-compose run --rm  application ./protected/yiic custommigrations dropindexes
  docker-compose run --rm  application ./protected/yiic custommigrations droptriggers
  docker-compose run --rm  application ./protected/yiic migrate --migrationPath=application.migrations.schema --interactive=0
  docker-compose run --rm  application ./protected/yiic migrate --migrationPath=application.migrations.fix_import --interactive=0
  popd
fi

exit 0