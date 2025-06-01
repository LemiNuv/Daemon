#!/bin/bash

USER_ID=$1
WORLD_NAME=$2

if [ -z "$USER_ID" ] || [ -z "$WORLD_NAME" ]; then
    echo "Uso: $0 <ID_usuario> <nombre_mundo>"
    exit 1
fi

STATUS=$(psql -h localhost -U insert_user -d luanti -t -c "SELECT status FROM worlds WHERE user_id = '$USER_ID' AND name = '$WORLD_NAME';" | tr -d '[:space:]')

if [ -z "$STATUS" ]; then
    echo "Error: Mundo no encontrado"
    exit 1
else
    echo "$STATUS"
fi
