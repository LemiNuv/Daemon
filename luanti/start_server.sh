#!/bin/bash

set -x

# Rutas absolutas
TEMPLATES_DIR="/home/usuario/luanti/templates"
WORLDS_DIR="/home/usuario/luanti/worlds"

# Parámetros
TEMPLATE_NAME=$1
USER_ID=$2
WORLD_NAME=$3

# Comprobaciones
if [ -z "$TEMPLATE_NAME" ] || [ -z "$USER_ID" ] || [ -z "$WORLD_NAME" ]; then
    echo "Uso: $0 <plantilla> <id_usuario> <nombre_mundo>"
    exit 1
fi

if [ ! -d "$TEMPLATES_DIR/$TEMPLATE_NAME" ]; then
    echo "Error: La plantilla '$TEMPLATE_NAME' no existe en $TEMPLATES_DIR"
    exit 1
fi

# Limpieza del nombre del mundo
WORLD_NAME=$(echo "$WORLD_NAME" | tr ' ' '_')

# Directorio del usuario
USER_PATH="$WORLDS_DIR/$USER_ID"
mkdir -p "$USER_PATH"

WORLD_PATH="$USER_PATH/$WORLD_NAME"

# Crear mundo
# echo Creando mundo "'$WORLD_NAME' (plantilla: '$TEMPLATE_NAME')..."
# mkdir -p "$WORLD_PATH"
# cp -r "$TEMPLATES_DIR/$TEMPLATE_NAME" "$WORLD_PATH/"

# Comprobación para saber si el mundo ya se creó, lo que indica que hay mods
if [ -d "$WORLD_PATH" ]; then
    echo "El mundo '$WORLD_NAME' ya existe. No se sobreescribirá."
else
    mkdir -p "$WORLD_PATH"
    cp -r "$TEMPLATES_DIR/$TEMPLATE_NAME" "$WORLD_PATH/"
    echo "Plantilla copiada al nuevo mundo."
fi

# Asignar puerto
BASE_PORT=30000
PORT=$((BASE_PORT + $(find "$WORLDS_DIR" -type d | wc -l)))

# Configurar contenedor
CONTAINER_NAME="${USER_ID}_${WORLD_NAME}"
podman rm -f "$CONTAINER_NAME" 2>/dev/null

echo "Iniciando servidor en puerto $PORT..."
if podman run -d \
    --name="$CONTAINER_NAME" \
    -e PUID=1000 \
    -e PGID=1000 \
    -e CLI_ARGS="--gameid $TEMPLATE_NAME" \
    -p $PORT:30000/udp \
    -v "$WORLD_PATH:/config/.minetest/games" \
    lscr.io/linuxserver/luanti:latest; then

    echo "Servidor iniciado para '$WORLD_NAME' (puerto: $PORT). Registrando en PostgreSQL..."
    psql -h localhost -U insert_user -d luanti -c \
        "INSERT INTO worlds (user_id, name, template, status, port) \
         VALUES ('$USER_ID', '$WORLD_NAME', '$TEMPLATE_NAME', 'active', $PORT);"

    if [ $? -eq 0 ]; then
        echo "¡Mundo registrado en la BD correctamente!"
    else
        echo "Error: No se pudo insertar en PostgreSQL. Revisa conexión o permisos."
    fi
else
    echo "Error: No se pudo iniciar el contenedor."
    exit 1
fi
