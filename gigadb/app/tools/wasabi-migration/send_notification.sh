#!/usr/bin/env bash

source "/app/.secrets"

serverName=$(uname -a | cut -f2 -d' ')

# If we're on the backup server then source proxy settings for sending message to the gitter room
if [ "$HOST_HOSTNAME" == "cngb-gigadb-bak" ];
then
    serverName="$HOST_HOSTNAME"
    source "/app/proxy_settings.sh" || exit 1
fi

date=$(date)

curl -X POST -i -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -H "Authorization: Bearer $GITTER_API_TOKEN" "https://api.gitter.im/v1/rooms/$GITTER_IT_NOTIFICATION_ROOM_ID/chatMessages" \
      -d '{"text":"From server ***'"$serverName"'*** : '"$1"'"}'