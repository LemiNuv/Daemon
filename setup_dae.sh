#!/bin/bash
set -e

echo -e "\n"
echo "|) /\ [- |\/| () |\|"
sleep 1
echo -e "\n"

if [ "$(id -u)" -ne 0 ]; then
    echo "Este script requiere root. Ejecutando con sudo..."
    exec sudo "$0" "$@"
    exit 1
fi

# --- Variables ---
LUANTI_DIR="/home/usuario/luanti"
VHOST_CONF="/etc/apache2/sites-available/daemon.conf"
DB_NAME="luanti"
DB_USER="insert_user"
DB_PASS="usuario"
PGPASS_FILE="/home/usuario/.pgpass"
APACHE_WEB_DIR="/home/usuario/daemon_web"

# 1. COMPROBAR DEPENDENCIAS
echo "▶ Comprobando programas necesarios..."
for pkg in apache2 libapache2-mod-php php php-pgsql podman postgresql postgresql-contrib python3.12 python3-flask unzip; do
    if ! dpkg -s "$pkg" &> /dev/null; then
        echo "❌ Falta $pkg. Instalando..."
        apt-get install -y "$pkg"
    else
        echo "✔  $pkg está instalado."
    fi
done

# 2. DESACTIVAR SITIO POR DEFECTO DE APACHE
echo "▶ Desactivando sitio default de Apache..."
a2dissite 000-default.conf || true

# 3. CONFIGURAR VIRTUALHOST
echo "▶ Configurando VirtualHost..."
cat <<EOF > "$VHOST_CONF"
<VirtualHost *:80>
    ServerName daemon.local
    DocumentRoot $APACHE_WEB_DIR
    <Directory $APACHE_WEB_DIR>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

sudo a2ensite daemon.conf
sudo systemctl restart apache2

echo "▶ Asignando permisos a /home/usuario/daemon_web..."
sudo chmod 755 "/home/usuario"

# 4. BASE DE DATOS
echo "▶ Configurando PostgreSQL..."
sudo -u postgres psql -c "CREATE USER $DB_USER WITH PASSWORD '$DB_PASS';" || echo "ℹ  Usuario ya existe"
sudo -u postgres psql -c "CREATE DATABASE $DB_NAME OWNER $DB_USER;" || echo "ℹ  Base de datos ya existe"


# 5. CREAR TABLAS (básico)
sudo -u postgres psql -d "$DB_NAME" <<EOF
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username text NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    password VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS worlds (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    template TEXT NOT NULL,
    status TEXT DEFAULT 'active',
    port INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
EOF

# 6. CREAR .pgpass
echo "▶ Creando archivo .pgpass..."
echo "localhost:5432:$DB_NAME:$DB_USER:$DB_PASS" > "$PGPASS_FILE"
chown usuario:usuario "$PGPASS_FILE"
chmod 600 "$PGPASS_FILE"

echo "▶ Asignando permisos necesarios al usuario insert_user..."
sudo -u postgres psql -d luanti -c "GRANT USAGE ON SCHEMA public TO insert_user;"
sudo -u postgres psql -d luanti -c "GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO insert_user;"
sudo -u postgres psql -d luanti -c "GRANT USAGE, SELECT, UPDATE ON ALL SEQUENCES IN SCHEMA public TO insert_user;"

echo "▶ Realizando pruebas de PostgreSQL con el usuario $DB_USER..."
TEST_USER="test_user_$(date +%s)"  # Nombre único para evitar colisiones
TEST_WORLD="test_world_$(date +%s)"

# -----------------------------------

echo -e "\n🔹 [Prueba 1] INSERT de usuario y mundo..."
sudo -u usuario psql -h localhost -U "$DB_USER" -d "$DB_NAME" <<EOF
-- Insertar usuario
INSERT INTO users (username, password) VALUES ('$TEST_USER', 'testpass123');

-- Insertar mundo asociado al usuario (usando el serial del INSERT anterior)
INSERT INTO worlds (user_id, name, template, port) 
VALUES (currval('users_id_seq'), '$TEST_WORLD', 'flat', 8080);

-- Mostrar resultados
SELECT * FROM users WHERE username = '$TEST_USER';
SELECT * FROM worlds WHERE name = '$TEST_WORLD';
EOF

echo -e "\n🔹 [Prueba 2] UPDATE del mundo..."
sudo -u usuario psql -h localhost -U "$DB_USER" -d "$DB_NAME" <<EOF
-- Actualizar el estado del mundo
UPDATE worlds SET status = 'inactive' WHERE name = '$TEST_WORLD';

-- Verificar el cambio
SELECT name, status FROM worlds WHERE name = '$TEST_WORLD';
EOF

echo -e "\n🔹 [Prueba 3] DELETE de los registros de prueba..."
sudo -u usuario psql -h localhost -U "$DB_USER" -d "$DB_NAME" <<EOF
-- Eliminar el mundo primero (por la FOREIGN KEY)
DELETE FROM worlds WHERE name = '$TEST_WORLD';

-- Luego eliminar el usuario
DELETE FROM users WHERE username = '$TEST_USER';

-- Verificar que se eliminaron
SELECT * FROM users WHERE username = '$TEST_USER';
SELECT * FROM worlds WHERE name = '$TEST_WORLD';
EOF

echo -e "\n✅ Todas las pruebas completadas sin errores (INSERT, SELECT, UPDATE, DELETE)."

# --------------------------------------------

# 7. Daemon
echo "▶ Creando servicio systemd para la API..."
cat <<EOF | sudo tee /etc/systemd/system/daemon-api.service > /dev/null
[Unit]
Description=Daemon API para el servidor Luanti
After=network.target postgresql.service

[Service]
User=usuario
WorkingDirectory=/home/usuario/daemon_web
ExecStart=/usr/bin/python3 /home/usuario/daemon_web/api_launcher.py
Restart=always
RestartSec=10

Environment="PATH=/usr/bin"
Environment="PYTHONUNBUFFERED=1"
PrivateTmp=true

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl daemon-reexec
sudo systemctl daemon-reload
sudo systemctl enable --now daemon-api.service
sudo systemctl start daemon-api.service

echo "✅ Servicio daemon-api.service creado y activado correctamente."

echo "▶ Asignando permisos a scritps..."
chmod +x /home/usuario/luanti/*.sh

# 8. FIN
echo "✅ Entorno Daemon preparado correctamente."

echo -e "\n"
echo "|\| () |/\| -] \/ (|"
echo -e "\n"
