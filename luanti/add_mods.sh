#!/bin/bash
set -x

TEMPLATE_NAME=$1
USER_ID=$2
WORLD_NAME=$3

shift 3
MODS=("$@")

WORLD_NAME=$(echo "$WORLD_NAME" | tr ' ' '_')

USER_PATH="/home/usuario/luanti/worlds/$USER_ID"
WORLD_PATH="$USER_PATH/$WORLD_NAME"

if [ -d "$WORLD_PATH" ]; then
    echo "El mundo ya existe."
    exit 1
fi

mkdir -p "$WORLD_PATH"

cp -r "/home/usuario/luanti/templates/$TEMPLATE_NAME" "$WORLD_PATH/$TEMPLATE_NAME"

# Copiamos los mods
mkdir -p "$WORLD_PATH/$TEMPLATE_NAME/mods"
for mod in "${MODS[@]}"; do
    MOD_PATH="/home/usuario/luanti/mods/$mod"
    if [ -d "$MOD_PATH" ]; then
        cp -r "$MOD_PATH" "$WORLD_PATH/$TEMPLATE_NAME/mods/"
    else
        echo "Mod '$mod' no encontrado. Saltando."
    fi
done

/home/usuario/luanti/start_server.sh "$TEMPLATE_NAME" "$USER_ID" "$WORLD_NAME"
