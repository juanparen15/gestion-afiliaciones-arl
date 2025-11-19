---
title: Requisitos del Sistema
description: Requisitos técnicos para instalar el Sistema de Gestión de Afiliaciones ARL
---

## Requisitos del Servidor

### Software Requerido

| Software | Versión Mínima | Recomendada |
|----------|----------------|-------------|
| PHP | 8.2 | 8.3+ |
| MySQL | 8.0 | 8.0+ |
| Composer | 2.0 | 2.7+ |
| Node.js | 18.0 | 20+ |
| NPM | 8.0 | 10+ |

### Extensiones PHP Requeridas

```bash
# Extensiones obligatorias
php-bcmath
php-ctype
php-curl
php-dom
php-fileinfo
php-json
php-mbstring
php-openssl
php-pdo
php-pdo_mysql
php-tokenizer
php-xml
php-zip
php-gd          # Para procesamiento de imágenes
php-intl        # Para internacionalización
```

### Verificar Extensiones

```bash
php -m | grep -E "bcmath|ctype|curl|dom|fileinfo|json|mbstring|openssl|pdo|tokenizer|xml|zip|gd|intl"
```

---

## Requisitos de Hardware

### Mínimos

| Recurso | Especificación |
|---------|----------------|
| CPU | 1 núcleo |
| RAM | 1 GB |
| Disco | 10 GB |
| Red | 100 Mbps |

### Recomendados (Producción)

| Recurso | Especificación |
|---------|----------------|
| CPU | 2+ núcleos |
| RAM | 4 GB |
| Disco | 50 GB SSD |
| Red | 1 Gbps |

---

## Entornos de Desarrollo

### Windows (Recomendado: Laragon)

[Laragon](https://laragon.org/) incluye:
- PHP 8.x
- MySQL 8.x
- Apache/Nginx
- Composer
- Node.js
- Git

**Instalación:**
1. Descargar Laragon Full desde [laragon.org](https://laragon.org/download/)
2. Ejecutar el instalador
3. Iniciar Laragon y activar todos los servicios

### macOS (Recomendado: Laravel Valet)

```bash
# Instalar Homebrew
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Instalar PHP y MySQL
brew install php mysql

# Instalar Composer
brew install composer

# Instalar Node.js
brew install node

# Instalar Valet
composer global require laravel/valet
valet install
```

### Linux (Ubuntu/Debian)

```bash
# Actualizar repositorios
sudo apt update && sudo apt upgrade -y

# Instalar PHP y extensiones
sudo apt install php8.2 php8.2-cli php8.2-common php8.2-mysql \
    php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml \
    php8.2-bcmath php8.2-intl

# Instalar MySQL
sudo apt install mysql-server
sudo mysql_secure_installation

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs
```

---

## Configuración de MySQL

### Crear Base de Datos

```sql
-- Conectar a MySQL
mysql -u root -p

-- Crear base de datos
CREATE DATABASE gestion_arl CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Crear usuario (producción)
CREATE USER 'arl_user'@'localhost' IDENTIFIED BY 'contraseña_segura';

-- Otorgar permisos
GRANT ALL PRIVILEGES ON gestion_arl.* TO 'arl_user'@'localhost';
FLUSH PRIVILEGES;

-- Salir
EXIT;
```

### Configuración Recomendada

Agregar a `my.cnf` o `my.ini`:

```ini
[mysqld]
# Codificación
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# Rendimiento
innodb_buffer_pool_size = 256M
max_connections = 100

# Logging (desarrollo)
general_log = 1
general_log_file = /var/log/mysql/general.log
```

---

## Servidor Web

### Apache

Asegúrate de tener habilitados los módulos:

```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo systemctl restart apache2
```

**Configuración VirtualHost:**

```apache
<VirtualHost *:80>
    ServerName arl.local
    DocumentRoot /var/www/gestion-afiliaciones-arl/public

    <Directory /var/www/gestion-afiliaciones-arl/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/arl-error.log
    CustomLog ${APACHE_LOG_DIR}/arl-access.log combined
</VirtualHost>
```

### Nginx

```nginx
server {
    listen 80;
    server_name arl.local;
    root /var/www/gestion-afiliaciones-arl/public;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Configuración de Email

Para las notificaciones automáticas, necesitas configurar un servidor SMTP:

### Opciones Recomendadas

| Servicio | Uso | Costo |
|----------|-----|-------|
| Mailtrap | Desarrollo | Gratis |
| Mailgun | Producción | Pago por uso |
| Amazon SES | Producción | Pago por uso |
| SMTP Institucional | Producción | Incluido |

---

## Verificación de Requisitos

Una vez instalado todo, ejecuta:

```bash
# Verificar PHP
php -v

# Verificar extensiones
php -m

# Verificar Composer
composer --version

# Verificar Node y NPM
node -v
npm -v

# Verificar MySQL
mysql --version
```

---

## Siguiente Paso

Una vez verificados todos los requisitos, continúa con la [Guía de Instalación](/instalacion/instalacion/).
