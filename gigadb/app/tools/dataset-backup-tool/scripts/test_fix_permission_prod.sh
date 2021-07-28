#!/bin/bash

set -e

echo "Fix permission starts at"`date`
echo "Set up: change a file to non globally available"
chmod 000 /home/gigadb/ken/README.md

echo "List the permission of the file:"
ls -l /home/gigadb/ken/README.md

echo "Change non globally readable files to globally readable file"
find /home/gigadb/ken/ ! -perm -g+r,u+r,o+r -exec chmod a+r {} \;

echo "List the permission of the file after fix:"
ls -l /home/gigadb/ken/README.md

echo "Fix permission completed at"`date`

echo "Change back the permission to -rw-r--r--"
chmod 644 /home/gigadb/ken/README.md

echo "Return the file permission to original state:"
ls -l /home/gigadb/ken/README.md