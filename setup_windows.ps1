# Windows PHP & MySQL Development Environment Setup Script
# ======================================================
# This script checks for and installs PHP and MySQL using Chocolatey,
# then starts the MySQL service and the PHP built-in development server.
#
# !!! IMPORTANT !!!
# 1. This script requires Administrator privileges to install software and manage services.
#    Right-click the .ps1 file and "Run as administrator".
# 2. This script relies on Chocolatey (https://chocolatey.org).
#    If Chocolatey is not installed, the script will guide you.
#
# By default, the PHP development server will run on localhost:8000
# and serve files from the current directory.
# MySQL will typically run on localhost:3306.

# --- Configuration ---
$PhpDevServerHost = "localhost"
$PhpDevServerPort = "8000"
$MySqlDefaultPort = "3306" # Standard MySQL port
# Get the directory where the script is located, which will be the web root
$WebRootDir = Get-Location # Use current working directory

# --- Helper Functions ---
function Write-Info ($Message) {
    Write-Host "[INFO] $Message" -ForegroundColor Cyan
}

function Write-Success ($Message) {
    Write-Host "[SUCCESS] $Message" -ForegroundColor Green
}

function Write-Warning ($Message) {
    Write-Host "[WARNING] $Message" -ForegroundColor Yellow
}

function Write-Error ($Message) {
    Write-Host "[ERROR] $Message" -ForegroundColor Red
    # Read-Host "Press Enter to exit..." # Optional: pause before exit
    exit 1
}

function Test-Admin {
    $currentUser = New-Object Security.Principal.WindowsPrincipal $(New-Object Security.Principal.WindowsIdentity).GetCurrent()
    return $currentUser.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

function Check-CommandExists ($CommandName) {
    return (Get-Command $CommandName -ErrorAction SilentlyContinue) -ne $null
}

# --- Main Script ---

# 1. Check for Administrator Privileges
if (-not (Test-Admin)) {
    Write-Error "This script must be run as Administrator. Please right-click the script and select 'Run as administrator'."
}

Write-Info "Starting PHP & MySQL development environment setup..."
Write-Info "Web root directory will be: $WebRootDir"
Write-Info "PHP development server will run on: http://$PhpDevServerHost`:$PhpDevServerPort"
Write-Host "-----------------------------------------------------"

# 2. Check and Install Chocolatey (if not present)
Write-Info "Checking for Chocolatey package manager..."
if (-not (Check-CommandExists "choco")) {
    Write-Warning "Chocolatey not found."
    Write-Host "Chocolatey is required to install PHP and MySQL easily."
    Write-Host "To install Chocolatey:"
    Write-Host "1. Open PowerShell as Administrator."
    Write-Host "2. Run the following command:"
    Write-Host "   Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))"
    Write-Host "3. After installation, close and reopen this PowerShell window (as Administrator) and re-run this script."
    Write-Error "Please install Chocolatey and re-run this script."
} else {
    Write-Success "Chocolatey is installed: $(choco --version)"
}
Write-Host "-----------------------------------------------------"

# 3. Check and Install PHP
Write-Info "Checking for PHP..."
$phpInstalled = $false
$phpCommand = "php" # Default command name
try {
    $phpInfo = choco list --local-only --exact php -r 2>$null
    if ($phpInfo -match "php") { # Simple check if 'php' is in the output
        # Verify PHP is on PATH
        if (Check-CommandExists "php") {
            $phpVersionOutput = php -v 2>$null | Select-Object -First 1
            Write-Success "PHP is already installed and on PATH: $($phpVersionOutput)"
            $phpInstalled = $true
        } else {
            Write-Warning "PHP found by Chocolatey, but 'php' command not on PATH. Will attempt to refresh PATH or find executable."
            # Attempt to find it if not on PATH, common after choco install without shell restart
            $env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
            if(Check-CommandExists "php"){
                $phpVersionOutput = php -v 2>$null | Select-Object -First 1
                Write-Success "PHP is now on PATH: $($phpVersionOutput)"
                $phpInstalled = $true
            } else {
                 # Try common choco install path for PHP
                $phpExePath = Get-ChildItem -Path "C:\tools\php*" -Filter "php.exe" -Recurse -ErrorAction SilentlyContinue | Select-Object -First 1
                if ($phpExePath) {
                    Write-Info "Found PHP executable at $($phpExePath.FullName). Using this path."
                    $phpCommand = $phpExePath.FullName
                    $phpVersionOutput = & $phpCommand -v 2>$null | Select-Object -First 1
                    Write-Success "PHP is already installed (using direct path): $($phpVersionOutput)"
                    $phpInstalled = $true
                } else {
                    Write-Warning "PHP installed via choco, but 'php' command not found on PATH and common install locations. Installation will proceed."
                    # Let it fall through to installation logic
                }
            }
        }
    }
} catch {
    # choco list can throw if package not found, or php -v if not in PATH yet
    Write-Warning "Could not determine PHP status reliably. Will proceed with installation check."
}

if (-not $phpInstalled) {
    Write-Warning "PHP not found or not fully configured. Attempting to install/reconfigure PHP..."
    try {
        # Install PHP. You can specify versions like choco install php --version=8.2
        # The --params helps ensure PHP is added to PATH correctly by some versions of the choco package.
        # Using a more specific path for PHP to avoid conflicts if multiple PHP versions are in C:\tools
        $phpInstallDir = "C:\tools\php_dev_script" 
        choco install php --params "'/InstallDir:$phpInstallDir'" -y --force 
        
        # Refresh environment variables for the current session
        $env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
        
        # Explicitly add the new PHP install dir to PATH for current session if not already there
        if ($env:Path -notlike "*$phpInstallDir*") {
            $env:Path = "$phpInstallDir;$($env:Path)"
        }
        # And the PHP extensions directory
        if ($env:Path -notlike "*$phpInstallDir\ext*") {
            $env:Path = "$phpInstallDir\ext;$($env:Path)"
        }


        if (-not (Check-CommandExists "php")) {
             Write-Warning "PHP installed, but 'php' command not immediately available on PATH. You might need to restart PowerShell."
             Write-Warning "Attempting to find PHP executable in specified install directory..."
             $phpExePathUser = Join-Path -Path $phpInstallDir -ChildPath "php.exe"
             if (Test-Path $phpExePathUser) {
                 Write-Info "Found PHP at $phpExePathUser. Will use this path directly for this session."
                 $phpCommand = $phpExePathUser
             } else {
                 Write-Error "Could not find PHP executable after installation at $phpExePathUser. Please ensure PHP is in your PATH and restart PowerShell."
             }
        } else {
            $phpCommand = "php" # Should be on path now
        }
        $phpVersionOutput = & $phpCommand -v 2>$null | Select-Object -First 1
        Write-Success "PHP installed/configured successfully: $($phpVersionOutput)"
    } catch {
        Write-Error "Failed to install/configure PHP using Chocolatey. Error: $($_.Exception.Message)"
    }
} else {
     # If PHP was already installed, ensure $phpCommand is set correctly (it might be a full path or just 'php')
     if (-not (Check-CommandExists $phpCommand) -and $phpCommand -ne "php") { # If $phpCommand was a full path and it's not found
        Write-Warning "Previously found PHP path '$phpCommand' is no longer valid. Defaulting to 'php' and hoping it's on PATH."
        $phpCommand = "php" # Fallback
     }
     if (-not (Check-CommandExists $phpCommand)) {
        Write-Warning "PHP was marked as installed, but '$phpCommand' is not found. Refreshing PATH."
        $env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
        if (-not (Check-CommandExists $phpCommand)) {
            Write-Error "PHP is installed but '$phpCommand' not on PATH. Please restart your PowerShell terminal or add it manually."
        }
     }
}
Write-Host "-----------------------------------------------------"

# 4. Check and Install MySQL Server
Write-Info "Checking for MySQL Server..."
$mysqlInstalled = $false
try {
    # Common package names: mysql, mysql-community-server. mysql is often an alias.
    $mysqlInfo = choco list --local-only --exact mysql -r 2>$null
    if ($mysqlInfo -match "mysql") {
        Write-Success "MySQL (or a related package) appears to be installed via Chocolatey."
        $mysqlInstalled = $true
    } else {
        $mysqlInfoCommunity = choco list --local-only --exact mysql-community-server -r 2>$null
        if ($mysqlInfoCommunity -match "mysql-community-server") {
            Write-Success "MySQL Community Server appears to be installed via Chocolatey."
            $mysqlInstalled = $true
        }
    }
} catch {}

if (-not $mysqlInstalled) {
    Write-Warning "MySQL Server not found via Chocolatey. Attempting to install MySQL Server..."
    Write-Info "This may take some time and might involve pop-up windows for MySQL installer."
    try {
        # mysql package often points to mysql-community-server or similar
        choco install mysql -y --force
        Write-Success "MySQL Server installation command executed. Check for any installer prompts."
        Write-Info "You might need to configure MySQL (e.g., root password) after installation if prompted by its own installer."
    } catch {
        Write-Error "Failed to install MySQL Server using Chocolatey. Error: $($_.Exception.Message)"
    }
}
Write-Host "-----------------------------------------------------"

# 5. Start MySQL Service
Write-Info "Attempting to start MySQL service..."
# MySQL service name can vary (e.g., MySQL, MySQL57, MySQL80). Try common ones.
$mysqlServiceNames = @("MySQL80", "MySQL57", "MySQL") # Prioritize newer versions
$mysqlService = $null
$mysqlServiceNameFound = $null
$mysqlServiceActive = $false

foreach ($name in $mysqlServiceNames) {
    $service = Get-Service -Name $name -ErrorAction SilentlyContinue
    if ($service) {
        $mysqlService = $service
        $mysqlServiceNameFound = $name
        break
    }
}

if ($mysqlService) {
    Write-Info "Found MySQL service: '$($mysqlServiceNameFound)'."
    if ($mysqlService.Status -ne "Running") {
        Write-Info "MySQL service '$($mysqlServiceNameFound)' is not running. Starting..."
        try {
            Start-Service -Name $mysqlServiceNameFound
            # Wait a few seconds for the service to actually start
            Start-Sleep -Seconds 5 
            $mysqlService.Refresh() # Refresh service status
            if ($mysqlService.Status -eq "Running") {
                Write-Success "MySQL service '$($mysqlServiceNameFound)' started."
                $mysqlServiceActive = $true
            } else {
                 Write-Warning "Attempted to start MySQL service '$($mysqlServiceNameFound)', but current status is '$($mysqlService.Status)'."
            }
            Set-Service -Name $mysqlServiceNameFound -StartupType Automatic -ErrorAction SilentlyContinue # Set to start on boot
            Write-Info "MySQL service '$($mysqlServiceNameFound)' set to automatic startup."
        } catch {
            Write-Error "Failed to start MySQL service '$($mysqlServiceNameFound)'. Error: $($_.Exception.Message)"
        }
    } else {
        Write-Success "MySQL service '$($mysqlServiceNameFound)' is already running."
        Set-Service -Name $mysqlServiceNameFound -StartupType Automatic -ErrorAction SilentlyContinue # Ensure it's set to automatic
        $mysqlServiceActive = $true
    }
} else {
    Write-Warning "Could not find a common MySQL service (tried $(($mysqlServiceNames -join ', ')))."
    Write-Warning "Please ensure MySQL is installed correctly and start its service manually if needed."
    Write-Warning "You can list services with 'Get-Service MySQL*' to find the correct name."
}

if ($mysqlServiceActive) {
    Write-Info "MySQL server should be accessible on localhost (or 127.0.0.1) at port $MySqlDefaultPort."
} else {
    Write-Warning "MySQL service may not be running. Default port is $MySqlDefaultPort if it starts successfully."
}
Write-Host "-----------------------------------------------------"

# 6. Start PHP Development Server
Write-Info "Starting PHP built-in development server..."
Write-Info "Serving files from: $WebRootDir"
Write-Info "Access it at: http://$PhpDevServerHost`:$PhpDevServerPort"
Write-Info "Press Ctrl+C in this window to stop the PHP development server."

# Change to the web root directory
try {
    Set-Location -Path $WebRootDir -ErrorAction Stop
} catch {
    Write-Error "Could not change to web root directory: $WebRootDir. Error: $($_.Exception.Message)"
}


# Start the PHP server. This will run in the foreground in the current PowerShell window.
Write-Info "Using PHP command: '$phpCommand'"
try {
    # Use the determined php command (could be full path or just 'php')
    & $phpCommand -S "$PhpDevServerHost`:$PhpDevServerPort" -t .
} catch {
    Write-Error "Failed to start PHP development server. Error: $($_.Exception.Message)"
    Write-Error "Ensure PHP is correctly installed and accessible via the command '$phpCommand'."
    Write-Error "Current PATH: $($env:Path)"
}

# The script will end when the PHP server is stopped (Ctrl+C)
Write-Success "PHP development server stopped."
Write-Info "Setup script finished."

