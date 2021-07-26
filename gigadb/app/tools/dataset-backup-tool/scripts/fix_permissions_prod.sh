#!/bin/bash

set -e

echo "Start to change all non globally readable files to globally readable files at"`date`
find /data/gigadb/pub/10.5524/ ! -perm -g+r,u+r,o+r -exec chmod a+r {} \;
echo "All files have been changed to globally readable at"`date`