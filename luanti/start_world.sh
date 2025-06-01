#!/bin/bash

USER_ID=$1
WORLD_NAME=$2

if [ -z "$USER_ID" ] || [ -z "$WORLD_NAME" ]; then
    echo "Uso: $0 <ID_usuario> <nombre_mundo>"
    exit 1
fi

CONTAINER_NAME="${USER_ID}_${WORLD_NAME}"

podman start "$CONTAINER_NAME"

# Para saber si no hubo un error al iniciar
if [ $? -eq 0 ]; then
    echo "Iniciando contenedor.."
    psql -h localhost -U insert_user -d luanti -c "UPDATE worlds SET status = 'active' WHERE user_id = '$USER_ID' AND name = '$WORLD_NAME';"

    # Verificar el cambio
    if [ $? -eq 0 ]; then
        echo "Cambio exitoso."
	echo "Contenedor iniciado."
    else
        echo "Error: No se pudo realizar el cambio. Revisa ~/.pgpass y permisos."
    fi
else
    echo "Error al iniciar el contenedor. Los datos no fueron cambiados."
    exit 1

fi

echo "Mundo '$CONTAINER_NAME' activo."
