#!/usr/bin/env bash

# Stop script upon error
set -e

PATH=/usr/local/bin:$PATH
export PATH

# Parse command line parameters
while [[ $# -gt 0 ]]; do
    case "$1" in
    --doi)
        doi=$2
        shift
        ;;
    --outdir)
        outdir=$2
        shift
        ;;
    --wasabi)
        wasabi_upload=true
        ;;
    *)
        echo "Invalid option: $1"
        exit 1  ## Could be optional.
        ;;
    esac
    shift
done

# Allow all scripts to base themselves from the directory where backup script 
# is located
APP_SOURCE=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

# Setup logging
LOGDIR="$APP_SOURCE/logs"
LOGFILE="$LOGDIR/wasabi_${doi}_$(date +'%Y%m%d_%H%M%S').log"
mkdir -p $LOGDIR
touch "$LOGFILE"

# Default is to copy TEST readme file to dev directory in Wasabi
SOURCE_PATH="$APP_SOURCE/runtime/curators"
DESTINATION_PATH="wasabi:gigadb-datasets/dev/pub/10.5524"

doi_directory_range () {
  # Determine DOI range directory to use based on starting DOI
  if [ "$doi" -le 101000 ]; then
      dir_range="100001_101000"
  elif [ "$doi" -le 102000 ] && [ "$doi" -ge 101001 ]; then
      dir_range="101001_102000"
  elif [ "$doi" -le 103000 ] && [ "$doi" -ge 102001 ]; then
      dir_range="102001_103000"
  fi
}

copy_to_wasabi () {
  # Create directory path to datasets
  source_dataset_path="${SOURCE_PATH}/readme_${doi}.txt"
  destination_dataset_path="${DESTINATION_PATH}/${dir_range}/${doi}"

  # Check directory for current DOI exists
  if [ -f "$source_dataset_path" ]; then
    echo "$(date +'%Y/%m/%d %H:%M:%S') DEBUG  : Found file $source_dataset_path" >> "$LOGFILE"
    echo "$(date +'%Y/%m/%d %H:%M:%S') INFO  : Attempting to copy file to ${destination_dataset_path}"  >> "$LOGFILE"

  docker-compose run --rm rclone rclone ls wasabi:gigadb-datasets/dev
#    # Continue running script if there is an error executing rclone copy
#    set +e
#    # Perform data transfer to Wasabi
#    rclone copy "$source_dataset_path" "$destination_dataset_path" \
#        --create-empty-src-dirs \
#        --log-file="$LOGFILE" \
#        --log-level INFO \
#        --stats-log-level DEBUG >> "$LOGFILE"
#
#    # Check exit code for rclone command
#    rclone_exit_code=$?
#    if [ $rclone_exit_code -eq 0 ]; then
#      echo "$(date +'%Y/%m/%d %H:%M:%S') INFO  : Successfully copied file to Wasabi for DOI: $doi" >> "$LOGFILE"
#    else 
#      echo "$(date +'%Y/%m/%d %H:%M:%S') ERROR  : Problem with copying file to Wasabi - rclone has exit code: $rclone_exit_code" >> "$LOGFILE"
#    fi
  else
    echo "$(date +'%Y/%m/%d %H:%M:%S') DEBUG  : Could not find file $source_dataset_path" >> "$LOGFILE"
  fi
}

if [[ $(uname -n) =~ compute ]];then
  . /home/centos/.bash_profile
  docker run --rm -v /home/centos/readmeFiles:/app/readmeFiles registry.gitlab.com/$GITLAB_PROJECT/production_tool:$GIGADB_ENV /app/yii readme/create --doi "$doi" --outdir "$outdir"
else
  docker-compose run --rm tool /app/yii readme/create --doi "$doi" --outdir "$outdir"
fi

if [ "$wasabi_upload" ]; then
  echo "Uploading readme file to Wasabi..."
  dir_range=""
  doi_directory_range
  copy_to_wasabi
fi
