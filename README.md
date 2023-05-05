# Hamawards

## About Hamawards

Hamawards is a simple, free ham radio award checking and downloading software. It is based on the [Laravel](https://laravel.com) PHP Framework.

As it is based on Laravel, it runs on pretty much all the webservers (Apache, NGINX) and on the tiniest of computers (even the raspberry pi). 

Hamwards is tested on Apache only.

## Technology

Hamawards is able to run using all the database engines permitted by Laravel (SQLITE, MySQL, MSSQL, Postgres, etc.). 

Hamawards is tested on SQLITE (which is plenty for most users and allows hamawards to run very efficiently on tiny machines) and MySQL (used on hamawards.app because of the larger volume).

## Features

Hamwards offers:

- Checking of your eligibility for multiple awards
- Downloading of customizable awards as PDF
- Event callsigns participating in multiple Events at the same time
- Multiple users permitted to upload for event callsigns
- Multiple rulesets for different awards

## Installation

### Step 1: Install Apache, PHP and Composer
This is just the list of commands to install all those prerequisites on a new current Ubuntu system. You'll find plenty of extensive tutorials on how to to that, this is just here to get you started asap.

```bash
sudo apt-get update -y
sudo apt-get install apache2 -y
sudo add-apt-repository -y ppa:ondrej/php
sudo apt-get update -y
sudo apt-get install libapache2-mod-php php php-common php-xml php-mysql php-gd php-opcache php-mbstring php-tokenizer php-json php-bcmath php-zip php-sqlite unzip -y
sudo a2enmod rewrite
sudo systemctl restart apache2
curl -sS https://getcomposer.org/installer | php 
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

If you like to use MySQL instead of SQLITE as a backend, also install MySQL:

```bash
sudo apt-get install mysql-server -y
sudo mysql_secure_installation
```

### Step 2: Configure Apache
First, create a vhost configuration file for hamawards. Of course, you can substitute the name of the file with whatever you like:
```bash
sudo nano /etc/apache2/sites-available/hamawards.conf
```

In the file, create the virtual host. Of course, customize directories, Domains and Admin-Emails to your liking.
```
<VirtualHost *:80>
    ServerAdmin admin@example.com
    ServerName mydomain.com
    DocumentRoot "/var/www/laravel/public"

    <Directory /var/www/laravel>
    Options Indexes MultiViews FollowSymLinks
    AllowOverride All
    Order allow,deny
    allow from all
    Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

Activate the site and reload apache

```bash
sudo a2ensite hamawards.conf
sudo systemctl restart apache2
```

### Step 3: Install hamawards
Clone this repo

```bash
cd /var/www
sudo -u www-data git clone https://git.erklaeranlage.de/Erklaeranlage/hamawards.git hamawards
cd hamawards
sudo -u www-data composer install --no-dev
sudo -u www-data cp .env.example .env
sudo -u www-data php artisan key:generate
```

Open the .env file:
```bash
nano .env
```

Change the database configuration.
SQLITE (change the path to reflect your installation location):
```
DEFAULT_CONNECTION=sqlite

DB_CONNECTION=sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=/var/www/hamawards/database/database.sqlite
DB_USERNAME=
DB_PASSWORD=
```

MySQL (set your MySQL login data. Use root or a user that can change the database schema. If the hamawards-database doesn't exist yet, create it first.):
```
DEFAULT_CONNECTION=mysql

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hamawards
DB_USERNAME=root
DB_PASSWORD=RootP#ssword
```

Have a look at the rest of the env file and change other values according to your needs, e.g.:
- APP_ENV (e.g. production or local)
- APP_DEBUG (set to false to hide internal errors)
- APP_URL (your URL)

Migrate the database and create the link for storage of award background images
```bash
sudo -u www-data php artisan migrate
sudo -u www-data php artisan storage:link
```

### Step 4: Secure your hamawards installation

Configure your apache server with a Let's Encrypt SSL certificate using certbot (plenty of guides out there), or place your install behind a reverse proxy (if you choose that, I think you know what to do already).

### Step 5: Finished

Login to Hamawards, using username "administrator" and password "welcome#01". Please remember to change that password immediately.

Have fun!

73, de Stefan, DB4SCW

## Updating hamawards to a new version

Just cd into your folder, git pull and afterwards, migrate the database. You are up and running the newest verstion!

```bash
cd /var/www/hamawards
sudo -u www-data git pull origin master --rebase
sudo -u www-data php artisan migrate
```
## Security Vulnerabilities

If you discover a security vulnerability within Laravel itself, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). 

If you discover a security vulnerability within Hamawards, please send an e-mail to DB4SCW, Stefan Wolf via [db4scw@darc.de](mailto:db4scw@darc.de). 

All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
