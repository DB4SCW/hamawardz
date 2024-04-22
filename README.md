# Hamawardz

## About Hamawardz

Hamawardz is a simple, free ham radio award checking and downloading software. It is based on the [Laravel](https://laravel.com) PHP Framework.

As it is based on Laravel, it runs on pretty much all the webservers (Apache, NGINX) and on the tiniest of computers (even the raspberry pi). 

Hamawardz is tested on Apache only.

Read about how this project came to be on my [Blog](https://www.db4scw.de/creating-hamawardz/).

## Technology

Hamawardz is able to run using all the database engines permitted by Laravel (SQLITE, MySQL, MSSQL, Postgres, etc.). 

Hamawardz is tested on SQLITE (which is plenty for most users and allows Hamawardz to run very efficiently on tiny machines) and MySQL (used on [hamawardz.app](https://hamawardz.app) because of the larger volume).

## Features

Hamawardz offers:

- Checking of your eligibility for multiple awards in one go without logging in or having to create a account
- Downloading of customizable (landscape) awards as PDF
- Event callsigns participating in multiple events at the same time
- Multiple users permitted to upload for 1 event callsigns
- Ability to have multiple event manager accounts for 1 event
- Multiple rulesets for different awards in 1 event
- Fully configurable autoimport for QSOs from other log programs, if the logbook database is accessible on the same database connection

## Hamawardz is not for you, if...

- Your event does not have fixed participants (hamawardz requires each event callsign to be explicitly registered in the software and to actively upload logs)
- Your award rules are something like "Have x QSOs with any random German operator"
- Your ruleset requires that the applicants for awards have to send in their logs instead of the event callsign operators

## Currently supported award rulesets:
1. Each QSO counts. No fuss: Get 15 QSOs, count 15 QSOs. Dupes, bands, modes, everything goes.
2. Each distinct callsign counts only once. The classic for "work each event callsign at least once"-type of awards. Dupes get discarded.
3. Each callsign counts one on each mode. Means you can work one callsign on different modes for additional credit. Multiple QSOs with one callsign on the same mode get discarded.
4. Each callsign counts one on each band. Means you can work one callsign on different bands for additional credit. Multiple QSOs with one callsign on the same band get discarded.
5. Each callsign counts one on each band and mode. Means you can work one callsign on different bands and modes for additional credit. 
6. Each callsign counts one on each main mode (CW, DIGITAL, VOICE). Means you can work one callsign on different mainmodes for additional credit.
7. Each callsign counts one on each band and main mode (CW, DIGITAL, VOICE). Means you can work one callsign on different bands main modes for additional credit.
8. Each callsign of one DXCC counts as 1. Same as Ruleset 2, but only counts a certain DXCC. Classic "work all German callsigns".
9. Each callsign of one continent counts as 1. Same as Ruleset 2, but only counts a certain continent. Classic "work all Oceanian callsigns".
10. BETA: Any number of QSOs inside a certain subtimeframe counts 1. Classic for "work this callsign at least once in each timeframe". Requires additional configuration.

## Installation

### Step 0: Get a domain, or a subdomain
You'll need one for this piece of software. The internet provides plenty of options.

### Step 1: Install Apache, PHP and Composer
This is just the list of commands to install all those prerequisites on a new current Ubuntu system. You'll find plenty of extensive tutorials on how to to that, this is just here to get you started asap.

```bash
sudo apt-get update -y
sudo apt-get install apache2 -y
sudo add-apt-repository -y ppa:ondrej/php
sudo apt-get update -y
sudo apt-get install libapache2-mod-php php php-common php-xml php-mysql php-gd php-opcache php-mbstring php-tokenizer php-json php-bcmath php-zip php-sqlite unzip -y
sudo a2enmod rewrite
sudo service apache2 restart
curl -sS https://getcomposer.org/installer | php 
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

If you like to use MySQL instead of SQLITE as a backend, also install MySQL.

You really don't have to though, because for 99,5% of users, SQLITE should be just fine.

```bash
sudo apt-get install mysql-server -y
sudo mysql_secure_installation
```

### Step 2: Configure Apache
First, create a vhost configuration file for hamawardz. Of course, you can substitute the name of the file with whatever you like:
```bash
sudo nano /etc/apache2/sites-available/hamawardz.conf
```

In the file, create the virtual host. Of course, customize directories, Domains and Admin-Emails to your liking.
```
<VirtualHost *:80>
    ServerAdmin admin@example.com
    ServerName mydomain.com
    DocumentRoot "/var/www/hamawardz/public"

    <Directory /var/www/hamawardz>
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
sudo a2ensite hamawardz.conf
sudo service apache2 restart
```

### Step 3: Install hamawardz
Clone this repo

```bash
cd /var/www
sudo -u www-data git clone https://git.erklaeranlage.de/Erklaeranlage/hamawardz.git hamawardz
cd hamawardz
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
DB_DATABASE=/var/www/hamawardz/database/database.sqlite
DB_USERNAME=
DB_PASSWORD=
```

Afterwards, create the sqlite file:
```bash
sudo -u www-data touch /var/www/hamawardz/database/database.sqlite
```

MySQL (set your MySQL login data. Use root or a user that can change the database schema. If the hamawardz-database doesn't exist yet, create it first):
```
DEFAULT_CONNECTION=mysql

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hamawardz
DB_USERNAME=root
DB_PASSWORD=RootP#ssword
```

Have a look at the rest of the env file and change other values according to your needs, e.g.:
- APP_ENV (e.g. production or local)
- APP_DEBUG (set to false to hide internal errors)
- APP_URL (your URL)
- APP_IMPRESSUM_URL (sets the impressum url in the footer, defaults to homepage if empty)
- APP_DATA_PROTECTION_URL (sets the data protection declaration url in the footer, defaults to homepage if empty)

Migrate the database and create the link for storage of award background images. After that, restart apache for good measure.
```bash
sudo -u www-data php artisan migrate
sudo -u www-data php artisan storage:link
sudo service apache2 restart
```

### Step 4: Secure your hamawardz installation

Configure your apache server with a Let's Encrypt SSL certificate using certbot (plenty of guides out there), or place your install behind a reverse proxy (if you choose that, I think you know what to do already).

### Step 5: Finished

Login to Hamawardz, using username "administrator" and password "welcome#01". Please remember to change that password immediately.

Have fun!

73, de Stefan, DB4SCW

## Updating hamawardz to a new version

Just cd into your folder, git pull and afterwards, migrate the database. You are up and running the newest version!

```bash
cd /var/www/hamawardz
sudo -u www-data git pull origin master --rebase
sudo -u www-data php artisan migrate
```
## Security Vulnerabilities

If you discover a security vulnerability within Laravel itself, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). 

If you discover a security vulnerability within Hamawardz, please send an e-mail to DB4SCW, Stefan Wolf via [db4scw@darc.de](mailto:db4scw@darc.de). 

All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
