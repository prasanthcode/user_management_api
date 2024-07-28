# User Data Management API

## Overview

This API provides a set of endpoints to manage user data, interact with a MySQL database, and handle email notifications. The API includes functionalities to upload user data, view user data, back up the database, and restore it.

## Technology Stack
Language: PHP
Framework: Symfony
Database: MySQL
## Requirements
Data.csv File
A data.csv file should be created with the following columns: name, email, username, address, role. The file includes 10 users with a mix of roles (USER, ADMIN), and at least 2-3 valid email IDs. An example of the CSV data is as follows:

name,email,username,address,role
John Doe,john.doe@example.com,johndoe,123 Main St,USER
Jane Smith,jane.smith@example.com,janesmith,456 Elm St,ADMIN
Michael Johnson,michael.j@example.com,mjohnson,789 Pine St,USER
Emily Davis,emily.d@example.com,emilydavis,101 Oak St,ADMIN
David Brown,david.b@example.com,davidbrown,202 Maple St,USER
Sarah Wilson,sarah.w@example.com,sarahwilson,303 Birch St,USER
Daniel Lee,daniel.l@example.com,daniellee,404 Cedar St,ADMIN
Jessica Martinez,jessica.m@example.com,jessicam,505 Walnut St,USER
Paul Garcia,paul.g@example.com,paulgarcia,606 Ash St,USER
Laura Clark,laura.c@example.com,lauraclark,707 Cherry St,ADMIN

# APIs
## 1. Upload and Store Data API
Endpoint: POST /api/upload
Description: Allows an admin to upload the data.csv file.
Functionality:
Parse the data.csv file.
Save the data into the database.
Send an email to each user upon successful storage.
Ensure email sending does not block the API response.

## 2. View Data API
Endpoint: GET /api/users
Description: Allows viewing of all user data stored in the database.

## 3. Backup Database API
Endpoint: GET /api/backup
Description: Allows an admin to take a backup of the database.
Functionality:
Generate a backup file (e.g., backup.sql).

## 4. Restore Database API
Endpoint: POST /api/restore
Description: Allows an admin to restore the database from the backup.sql file.
Functionality:
Restore the database using the backup file.

## Email Sending
Utilizes an email service to send emails to users upon successful data storage.
Ensures emails are sent asynchronously to avoid blocking the API response.

# Setup Instructions
## Clone the Repository:

git clone https://github.com/yourusername/user_management_api.git
cd user_management_api

## Install Dependencies:

composer install

## Configure Environment Variables:

Create a .env file in the root directory and set your database credentials and email service configuration:

DATABASE_URL="mysql://username:password@localhost:3306/dbname"
MAILER_DSN="smtp://user:password@smtp.example.com:port"

# Set Up the Database:

## Run the following commands to create the database schema:

php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force

## Run the Server:

php bin/console server:run

# Usage Examples
## Upload Data:

curl -X POST http://localhost:8000/api/upload -F "file=@data.csv"

## View Users:

curl http://localhost:8000/api/users

## Backup Database:

curl http://localhost:8000/api/backup

## Restore Database:

curl -X POST http://localhost:8000/api/restore -F "file=@backup.sql"
