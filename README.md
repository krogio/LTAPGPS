We added some features to the code provided by Mikrotik on their GPS forum. Thanks to Mikrotik for providing this free of charge and for supporting the changes that were needed early on.

https://wiki.mikrotik.com/wiki/Manual:GPS-tracking

---

## WEB SERVER REQUIREMENTS
The following walkthrough is based on the below requirements and we advise sticking to the exact package versions listed.

---

- Webhost and server of your choice - We chose Digital Ocean
  We selected the 1GB Memory / 25 GB Disk / London - Ubuntu 16.04.6 x64 - This costs $5 a month with backup for an additional $1 a month
- A web user other than root - Walkthrough Below
- A basic firewall - Walkthrough Below
- Apache2 - Walkthrough Below
- PHP7.2 - Walkthrough Below
- SQLite3 - Walkthrough Below
- SSL Cert - Walkthrough Below
- RouterOS device with a working GPS module - Walkthrough Available Here - https://scoop.co.za/blog/post/vehicle-gps-tracking-with-mikrotik-s-ltap
- RouterOS v6.40rc30 or above - Walkthrough Available Here - https://scoop.co.za/blog/post/vehicle-gps-tracking-with-mikrotik-s-ltap
- Set GPS format in RouterOS to dd - Walkthrough Available Here - https://scoop.co.za/blog/post/vehicle-gps-tracking-with-mikrotik-s-ltap

---

## SIGN UP AND CREATE A BARE UBUNTU 16.04.06 DROPLET
Once your droplet is created at https://www.digitalocean.com/ you will receive an email with the server_ip, root username and password which you can use for initial ssh access

---

1. SSH with root user into your new server
   ssh root@server_ip - Mac or linux command
   or we recommend Putty for Windows
2. Change your default root password to something you will remember

---

## CREATING A NEW USER
https://www.digitalocean.com/community/tutorials/initial-server-setup-with-ubuntu-18-04

---

1.adduser www-user<br/>
2.usermod -aG sudo www-user

---

## SETTING UP A BASIC FIREWALL
https://www.digitalocean.com/community/tutorials/initial-server-setup-with-ubuntu-18-04

---

1. Print a list of applications available to the firewall.
   ufw app list
2. Allow OpenSSH before we enable the firewall so we can still access our server for configuration
   ufw allow ‘OpenSSH’
3. Enable the firewall
   ufw enable
4. Check that the firewall is running. If your connection is terminated this means OpenSSH was not added correctly
   or something else is wrong.
   ufw status

---
## INSTALLING APACHE2 WEBSERVER
https://www.digitalocean.com/community/tutorials/how-to-install-the-apache-web-server-on-ubuntu-18-04
\*change to the domain name you plan to use

---

1.Login with new www-user<br>
2.sudo apt update<br>
3.sudo apt install apache2<br>
4.sudo ufw app list<br>
5.sudo ufw allow ‘Apache’<br>
6.sudo ufw status<br>
7.sudo systemctl status apache2<br>
8.sudo mkdir -p /var/www/\*gps.domain.co.za/html<br>
9.sudo chown -R $USER:$USER /var/www/\*gps.domain.co.za/html<br>
10.Sudo chmod -R 755 /var/www/\*gps.domain.co.za<br>
11.nano /var/www/\*gps.domain.co.za/html/index.html<br>

Add this content to the file as a placeholder and test

\<html><br>
    \<head><br>
        \<title>Welcome to Example.com!\</title><br>
    \</head><br>
    \<body><br>
        \<p>Success!  The example.com server block is working!\</p><br>
    \</body><br>
\</html><br>

Ctrl+O and then Enter to save file
Ctrl+X to exit nano editor

12.sudo nano /etc/apache2/sites-available/gps.domain.co.za.conf

\<VirtualHost \*:80><br>
ServerAdmin admin@gps.domain.co.za<br>
ServerName gps.domain.co.za<br>
DocumentRoot /var/www/gps.domain.co.za/html<br>
ErrorLog ${APACHE_LOG_DIR}/error.log<br>
    CustomLog ${APACHE_LOG_DIR}/access.log combined<br>
\</VirtualHost>

Ctrl+O and then Enter to save file
Ctrl+X to exit nano editor

13.sudo a2ensite gps.domain.co.za.conf
14.sudo a2dissite 000-default.conf
15.sudo apache2ctl configtest
16.sudo systemctl restart apache2

---

## INSTALLING PHP7.2 AND MODULES

1. sudo apt-get install software-properties-common python-software-properties
2. sudo add-apt-repository -y ppa:ondrej/php
3. sudo apt-get update
4. sudo apt-get install php7.2 php7.2-cli php7.2-common php-pear php7.2-curl php7.2-dev php7.2-gd php7.2-mbstring php7.2-zip php7.2-mysql php7.2-xml php7.2-sqlite3 php7.2-fpm
5. php -v
6. sudo a2enmod proxy_fcgi setenvif
7. Sudo a2enconf php7.2-fpm
8. sudo service apache2 reload

---

## INSTALLING SQLITE3

1. sudo apt install sqlite3
2. cd /var/www/gps.domain.co.za/html
3. sudo chmod -R a+w sqlite_db/
4. ls -l

---

## INSTALL AND CONFIGURING SSL

1. add-apt-repository ppa:certbot/certbot
2. apt update
3. apt install python-certbot-apache
4. certbot --apache -d gps.domain.co.za
5. cerbot renew --dry-run

---

## UPLOAD YOUR FILES VIA FTP OR CLONE OUR GIT REPOSITORY

1. Download or Clone from https://github.com/krogio/LTAPGPS
2. Upload files to you /var/gps.domain.co.za/html directory

---

## CUSTOMIZE LOGO AND IMAGES

1. Replace the image in images
2. Upload files to your /var/gps.domain.co.za/html directory
3. Remove DB placeholder values by deleting them on a local copy of the db file. We used DB Browser for SQL Lite
