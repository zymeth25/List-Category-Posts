#!/usr/bin/env bash

# Update the system and install SVN + PHP7.0 + MySQL 5.7
if [ ! -x /usr/bin/mysql ];
then
    sudo debconf-set-selections <<< 'mysql-server-5.7 mysql-server/root_password password rootpass'
    sudo debconf-set-selections <<< 'mysql-server-5.7 mysql-server/root_password_again password rootpass'

    apt-get update

    apt-get install -y subversion apache2 php7.0 php7.0-xml php7.0-mbstring libapache2-mod-php7.0 mysql-server-5.7 php7.0-mysql
fi

# Create the WordPress database and corresponding user
if [ ! -f /var/log/databasesetup ];
then
    echo "CREATE USER 'wordpressuser'@'localhost' IDENTIFIED BY 'wordpresspass'" | mysql -uroot -prootpass
    echo "CREATE DATABASE wordpress" | mysql -uroot -prootpass
    echo "GRANT ALL ON wordpress.* TO 'wordpressuser'@'localhost'" | mysql -uroot -prootpass
    echo "flush privileges" | mysql -uroot -prootpass

    touch /var/log/databasesetup
fi

# Enable the default apache site located in /var/www
if [ ! -f /var/log/webserversetup ];
then
    a2enmod rewrite
    sedcmd='/var\/www/ c\DocumentRoot /var/www\
       <Directory /var/www>\
       AllowOverride All\
       </Directory>'
    sed -i "$sedcmd" /etc/apache2/sites-available/000-default.conf
    service apache2 restart

    touch /var/log/webserversetup
fi

# Install the wp-cli utility
if [ ! -x /usr/local/bin/wp ];
then
    cd /usr/local/bin
    wget -O wp https://raw.github.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
    chmod a+x wp
fi

# Install WordPress, configure it and import the test data
if [ ! -f /var/www/wordpress ];
then
    cd /var/www
    sudo rm -rf *
    sudo chown www-data:www-data .
    sudo -u www-data wp core download
    sudo -u www-data wp core config --dbname=wordpress --dbuser=wordpressuser --dbpass=wordpresspass
    sudo -u www-data wp core install --url="http://localhost:8080" --title="Testing the LCP plugin" --admin_user=adminuser --admin_password=adminpass --admin_email="admin@example.com"
    sudo -u www-data wp plugin install wordpress-importer --activate
    sudo -u www-data wget https://raw.githubusercontent.com/manovotny/wptest/master/wptest.xml
    sudo -u www-data wp import wptest.xml --authors=create
    sudo rm wptest.xml
    sudo ln -s /vagrant/ /var/www/wp-content/plugins/list-category-posts
    sudo touch wordpress
fi

# Install PHPUnit
if [ ! -x /usr/local/bin/phpunit ];
then
    cd /usr/local/bin
    wget -O phpunit https://phar.phpunit.de/phpunit.phar
    chmod a+x phpunit
fi

# Initiate the testing framework
if [ -x /usr/local/bin/phpunit -a -f /var/www/wordpress ];
then
    cd /var/www/wp-content/plugins/list-category-posts
    sudo -u www-data WP_TESTS_DIR=/var/www/wp-tests-lib/includes WP_CORE_DIR=/var/www/ bash bin/install-wp-tests.sh wordpress_test root rootpass localhost latest
fi
