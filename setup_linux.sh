#!/bin/bash

# Linux PHP & MySQL Development Environment Setup Script
# =====================================================
# This script checks for and installs PHP and MySQL, then starts
# the MySQL service and the PHP built-in development server.
#
# It's primarily designed for Debian/Ubuntu-based systems using 'apt'.
# You may need to adapt package names and commands for other distributions.
#
# !!! IMPORTANT !!!
# This script requires sudo privileges to install software and manage services.
# Run it with: sudo ./setup_linux.sh
#
# By default, the PHP development server will run on localhost:8000
# and serve files from the current directory.
# MySQL will typically run on localhost:3306.

# --- Configuration ---
PHP_DEV_SERVER_HOST="localhost"
PHP_DEV_SERVER_PORT="8000"
MYSQL_DEFAULT_PORT="3306" # Standard MySQL port
# Get the directory where the script is located, which will be the web root
WEB_ROOT_DIR="$(pwd)" # Use current working directory

# --- Helper Functions ---
function print_info {
    echo "[INFO] $1"
}

function print_success {
    echo "[SUCCESS] $1"
}

function print_warning {
    echo "[WARNING] $1"
}

function print_error {
    echo "[ERROR] $1"
    exit 1
}

function check_command {
    command -v "$1" >/dev/null 2>&1
}

# --- Main Script ---

# 1. Check for sudo privileges
if [ "$(id -u)" -ne 0 ]; then
    print_error "This script must be run as root or with sudo privileges. Example: sudo ./setup_linux.sh"
fi

print_info "Starting PHP & MySQL development environment setup..."
print_info "Web root directory will be: $WEB_ROOT_DIR"
print_info "PHP development server will run on: $PHP_DEV_SERVER_HOST:$PHP_DEV_SERVER_PORT"
echo "-----------------------------------------------------"

# 2. Check and Install PHP
print_info "Checking for PHP..."
if check_command php; then
    PHP_VERSION=$(php -v | head -n 1)
    print_success "PHP is already installed: $PHP_VERSION"
else
    print_warning "PHP not found. Attempting to install PHP..."
    # Update package lists
    if ! apt-get update -y; then
        print_error "Failed to update package lists. Please check your internet connection and repository configuration."
    fi

    # Install PHP and common extensions (php-cli for command line, php-mysql for MySQL, etc.)
    # You can add more extensions like php-xml, php-mbstring, php-zip as needed.
    if ! apt-get install -y php php-cli php-mysql php-xml php-mbstring php-zip; then
        print_error "Failed to install PHP. Please check the error messages above."
    fi
    PHP_VERSION=$(php -v | head -n 1)
    print_success "PHP installed successfully: $PHP_VERSION"
fi
echo "-----------------------------------------------------"

# 3. Check and Install MySQL Server
print_info "Checking for MySQL Server..."
# Check for mysqld (server daemon) or mysql (client, often installed with server)
if check_command mysqld || check_command mysql; then
    # Attempt to get MySQL version if client is available
    if check_command mysql; then
      MYSQL_VERSION=$(mysql --version 2>/dev/null || echo "MySQL Server (version unknown)")
    else
      MYSQL_VERSION="MySQL Server (version check requires client)"
    fi
    print_success "MySQL appears to be installed: $MYSQL_VERSION"
else
    print_warning "MySQL Server not found. Attempting to install MySQL Server..."
    if ! apt-get update -y; then # Ensure lists are fresh if PHP wasn't installed
        print_warning "Failed to update package lists (second attempt). Continuing with install..."
    fi

    # Install MySQL Server
    # This might prompt for a root password during installation on some systems.
    if ! apt-get install -y mysql-server; then
        print_error "Failed to install MySQL Server. Please check the error messages above."
    fi
    MYSQL_VERSION=$(mysql --version 2>/dev/null || echo "MySQL Server (version unknown)")
    print_success "MySQL Server installed successfully. $MYSQL_VERSION"
    print_info "It's recommended to run 'sudo mysql_secure_installation' to secure your MySQL installation."
fi
echo "-----------------------------------------------------"

# 4. Start MySQL Server
print_info "Attempting to start and enable MySQL service..."
# Use systemctl if available, otherwise try service
MYSQL_SERVICE_ACTIVE=false
if check_command systemctl; then
    if ! systemctl is-active --quiet mysql; then
        print_info "MySQL service is not active. Starting..."
        if ! systemctl start mysql; then
            print_error "Failed to start MySQL service using systemctl."
        else
            MYSQL_SERVICE_ACTIVE=true
        fi
    else
        print_info "MySQL service is already active."
        MYSQL_SERVICE_ACTIVE=true
    fi
    if ! systemctl is-enabled --quiet mysql; then
        print_info "MySQL service is not enabled to start on boot. Enabling..."
        if ! systemctl enable mysql; then
            print_warning "Failed to enable MySQL service to start on boot."
        else
            print_info "MySQL service enabled for boot."
        fi
    else
        print_info "MySQL service is already enabled for boot."
    fi
    print_success "MySQL service managed with systemctl."
elif check_command service; then
    # Fallback for older systems without systemctl
    if ! service mysql status > /dev/null 2>&1; then
         print_info "MySQL service is not active. Starting..."
        if ! service mysql start; then
            print_error "Failed to start MySQL service using 'service' command."
        else
            MYSQL_SERVICE_ACTIVE=true
        fi
    else
        print_info "MySQL service is already active (checked via 'service' command)."
        MYSQL_SERVICE_ACTIVE=true
    fi
    # Enabling on boot with 'service' is more varied (e.g., update-rc.d), so we'll skip auto-enabling here for simplicity.
    print_warning "Could not automatically enable MySQL on boot (systemctl not found). Please do this manually if needed."
    print_success "MySQL service managed with 'service'."
else
    print_warning "Could not determine how to manage MySQL service (systemctl and service commands not found)."
    print_warning "Please ensure MySQL is running manually."
fi

if [ "$MYSQL_SERVICE_ACTIVE" = true ]; then
    print_info "MySQL server should be accessible on localhost (or 127.0.0.1) at port $MYSQL_DEFAULT_PORT."
else
    print_warning "MySQL service may not be running. Default port is $MYSQL_DEFAULT_PORT if it starts successfully."
fi
echo "-----------------------------------------------------"

# 5. Start PHP Development Server
print_info "Starting PHP built-in development server..."
print_info "Serving files from: $WEB_ROOT_DIR"
print_info "Access it at: http://$PHP_DEV_SERVER_HOST:$PHP_DEV_SERVER_PORT"
print_info "Press Ctrl+C to stop the PHP development server."

# Change to the web root directory before starting the server
cd "$WEB_ROOT_DIR" || print_error "Could not change to web root directory: $WEB_ROOT_DIR"

# Start the PHP server. This will run in the foreground.
php -S "$PHP_DEV_SERVER_HOST:$PHP_DEV_SERVER_PORT" -t .

# The script will end when the PHP server is stopped (Ctrl+C)
print_success "PHP development server stopped."
print_info "Setup script finished."
