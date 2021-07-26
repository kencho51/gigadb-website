#!/bin/bash

set -e
set -u

echo "Backup to Tencet COS starts at" `date`
rclone --version --checksum sync /data/gigadb/pub/10.5524/ cos:cngbdb-share-backup-2-1255501786/cngbdb/giga/gigadb/
echo "Backup to Tencent COS completed at" `date`