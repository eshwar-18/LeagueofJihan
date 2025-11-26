# CPS510 A9 - SETUP AND EXECUTION GUIDE

## Alternate Method
1. Connect to TMU ORACLE12 OpenVPN
2. Click on https://webdev.scs.ryerson.ca/~a49chowd/a9/index.php

## 1. Accessing the Webdev Server

1. Open MobaXterm
2. Select: Session → SSH
3. Remote host: webdev.scs.ryerson.ca
4. Username: your_username
5. Click OK and enter password when prompted

## 2. Creating Directory Structure

Create the webdev directory if it doesn't exist, then create a9 folder inside:
```
mkdir -p ~/webdev/a9
cd ~/webdev/a9
```

Upload all project files to this directory.

Or just drag and drop this entire a9 folder into the webdev directory.

## 3. Permission Settings

From inside the a9 directory:

Directories:
```
chmod 755 .
chmod 755 tables
chmod 755 queries
```

PHP files:
```
chmod 644 *.php
chmod 644 tables/*.php
chmod 644 queries/*.php
```

## 4. Database Configuration

Edit `.env` file with your Oracle credentials:
```
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## 5. Running the Application

Open browser and navigate to:
```
https://webdev.scs.ryerson.ca/~your_username/a9/
```

## 6. Initializing the Database

Recommended order for first-time setup:

1. Drop Tables
2. Create Tables
3. Populate Tables

Each page displays confirmation messages when operations complete.

## 7. Testing

- CRUD pages allow add, edit, delete, and search operations
- Query pages return joined and filtered results
- All tables enforce referential integrity
