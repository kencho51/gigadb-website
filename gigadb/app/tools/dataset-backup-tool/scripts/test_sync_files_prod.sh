#!/bin/bash

set -e
set -u

echo "Testing backup to Tencet COS starts at" `date`

echo "List all directories in the backup bucket:"
rclone lsd cos:cngbdb-share-backup-2-1255501786/cngbdb/giga/gigadb/pub/10.5524/
echo "Dry run to sync a dummy file!!!!!!"
rclone --dry-run sync /home/gigadb/ken/README.md --checksum cos:cngbdb-share-backup-2-1255501786/cngbdb/giga/gigadb/pub/10.5524/

echo "Testing backup completed at" `date`
