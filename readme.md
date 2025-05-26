# PHP & MySQL Development Environment Setup

This project provides scripts to quickly set up a local PHP and MySQL development environment on Windows and Linux. The scripts will attempt to:

1.  Check if PHP is installed. If not, it will try to install it.
2.  Check if MySQL is installed. If not, it will try to install it.
3.  Start the MySQL server (typically on `localhost:3306`).
4.  Start the PHP built-in development server in the current directory (typically on `localhost:8000`).

## Prerequisites

* **Internet Connection:** Required for downloading PHP, MySQL, and any dependencies.
* **Administrative/Root Privileges:** Required to install software and manage services.

## Scripts

* `setup_windows.ps1`: PowerShell script for Windows.
* `setup_linux.sh`: Bash script for Linux (primarily Debian/Ubuntu-based).

## Instructions

### For Windows Users (`setup_windows.ps1`)

1.  **Download/Save:** Ensure you have the `setup_windows.ps1` script in the root directory of your PHP project (the directory you want to serve files from).
2.  **Chocolatey:** This script uses [Chocolatey](https://chocolatey.org/) (a package manager for Windows) to install PHP and MySQL.
    * If you don't have Chocolatey installed, the script will detect this and provide instructions. You'll need to:
        1.  Open PowerShell **as Administrator**.
        2.  Run the command provided by the script (or from the official Chocolatey website) to install Chocolatey.
        3.  **Close and reopen PowerShell as Administrator** and then re-run the `setup_windows.ps1` script.
3.  **Run the Script:**
    * Navigate to your project directory in PowerShell.
    * Right-click the `setup_windows.ps1` file and select "Run with PowerShell" (if already an Administrator) or, more reliably, open a PowerShell terminal **as Administrator** and run the script directly:
        ```powershell
        .\setup_windows.ps1
        ```
4.  **Follow Prompts:** The script will output information about its progress.
    * MySQL installation might involve its own installer pop-ups.
5.  **Access Your Server:**
    * Once the script completes the setup and starts the PHP server, it will display the address, usually: `http://localhost:8000`
    * MySQL server should be running on `localhost:3306`.
6.  **Stop the Server:** Press `Ctrl+C` in the PowerShell window where the PHP server is running to stop it.

### For Linux Users (`setup_linux.sh`)

1.  **Download/Save:** Ensure you have the `setup_linux.sh` script in the root directory of your PHP project.
2.  **Make Executable:** Open your terminal, navigate to the project directory, and make the script executable:
    ```bash
    chmod +x setup_linux.sh
    ```
3.  **Run the Script:** Execute the script with `sudo` because it needs to install packages and manage services:
    ```bash
    sudo ./setup_linux.sh
    ```
    * **Note for other distributions:** The script uses `apt-get` for package installation (common on Debian/Ubuntu). If you are using a different Linux distribution (e.g., Fedora, CentOS, Arch), you might need to modify the package installation commands (e.g., `yum`, `dnf`, `pacman`) and package names within the script.
4.  **Follow Prompts:** The script will output its progress. You might be prompted for your password for `sudo`.
5.  **MySQL Security (Recommended):** After MySQL is installed, it's highly recommended to run:
    ```bash
    sudo mysql_secure_installation
    ```
    This will guide you through setting a root password and other security-related settings for MySQL.
6.  **Access Your Server:**
    * Once the script completes the setup and starts the PHP server, it will display the address, usually: `http://localhost:8000`
    * MySQL server should be running on `localhost:3306`.
7.  **Stop the Server:** Press `Ctrl+C` in the terminal window where the PHP server is running to stop it.

## Important Notes

* **PHP Development Server:** The PHP built-in server is convenient for development but is **not recommended for production environments** as it's single-threaded and lacks many features of a full web server like Apache or Nginx.
* **Firewall:** If you have a firewall enabled, you might need to allow connections to the ports used by PHP (e.g., 8000) and MySQL (e.g., 3306).
* **Customization:** You can modify the server host and port settings at the beginning of each script if needed.
* **Troubleshooting:**
    * Ensure you are running the scripts with the necessary administrative/root privileges.
    * Check the output of the script for any error messages. These can often point to issues with package repositories, internet connectivity, or conflicts.
    * On Windows, ensure Chocolatey is installed correctly and that your PowerShell execution policy allows running local scripts (the script attempts to bypass for the process, but global policies might interfere).
    * If PHP or MySQL commands are not found after installation, try closing and reopening your terminal/PowerShell window to ensure PATH environment variables are updated.

This README should help users get started with the PHP development server using the provided scripts.
