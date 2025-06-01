#!/bin/bash

USER_ID=$1
WORLD_NAME=$2
WORLDS_DIR="/home/usuario/luanti/worlds"


if [ -z "$USER_ID" ] || [ -z "$WORLD_NAME" ]; then
    echo "Uso: $0 <ID_usuario> <nombre_mundo>"
    exit 1
fi

CONTAINER_NAME="${USER_ID}_${WORLD_NAME}"
MARKED_WORLD_NAME="${WORLD_NAME}_DELETED"

echo "Deteniendo contenedor..."
if podman stop "$CONTAINER_NAME"; then
    echo "Contenedor detenido."
else
    echo "Error: No se pudo detener el contenedor."
    exit 1
fi

echo "Eliminando contenedor..."
if podman rm "$CONTAINER_NAME"; then
    echo "Contenedor eliminado."
else
    echo "Error: No se pudo eliminar el contenedor."
    exit 1
fi

mv "$WORLDS_DIR/$USER_ID/$WORLD_NAME" "$WORLDS_DIR/$USER_ID/$MARKED_WORLD_NAME"
if [ $? -eq 0 ]; then
    echo "Mundo marcado como eliminado."

    echo "Actualizando base de datos..."
    UPDATE_RESULT=$(psql -h localhost -U insert_user -d luanti -c "UPDATE worlds SET status = 'deleted' WHERE user_id = '$USER_ID' AND name = '$WORLD_NAME';")

    if [ $? -eq 0 ]; then
        echo "Cambio exitoso en base de datos."
        echo "$UPDATE_RESULT"
    else
        echo "Error: Falló la actualización en la base de datos."
        echo "$UPDATE_RESULT"
        exit 1
    fi
else
    echo "Error al renombrar el directorio."
    exit 1
fi

echo "Mundo '$CONTAINER_NAME' inactivo."
exit 0
