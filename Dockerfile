FROM ubuntu
ENV TZ=America/Sao_Paulo
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone && \
apt-get update && apt-get install -y apache2 software-properties-common curl && \
add-apt-repository -y ppa:ondrej/php && apt-get update && apt-get install -y php7.4-cli \
libapache2-mod-php7.4 php7.4-mysql php7.4-curl php-memcached \
php7.4-dev php7.4-pgsql php7.4-sqlite3 php7.4-mbstring php7.4-gd php7.4-json php7.4-xmlrpc \
php7.4-xml php7.4-zip php7.4-bcmath php7.4-soap php7.4-intl vim \
php7.4-readline redis-server git && update-alternatives --set php /usr/bin/php7.4 && update-alternatives --set phar /usr/bin/phar7.4 \
&& pecl -d php_suffix=7.4 install redis && chown www-data:www-data /var/lock/ && chown www-data:www-data /var/run/ && \
chown www-data:www-data /var/log/ && a2enmod rewrite && apt-get clean
ENV APACHE_LOCK_DIR="/var/lock"
ENV APACHE_PID_FILE="/var/run/apache2.pid"
ENV APACHE_RUN_USER="www-data"
ENV APACHE_RUN_GROUP="www-data"
ENV APACHE_LOG_DIR="/var/log/apache2"

ADD  docker-assets/index.php /var/www/
COPY docker-assets/php.ini /etc/php/7.4/apache/php.ini
COPY docker-assets/php.ini /etc/php/7.4/cli/php.ini
COPY docker-assets/apache2.conf /etc/apache2/apache2.conf
COPY docker-assets/000-default.conf /etc/apache2/sites-available/000-default.conf



VOLUME /var/www
USER root
WORKDIR /var/www


LABEL description="Web SeteAPI"
LABEL version="2.0.0"

EXPOSE 80

ENTRYPOINT ["/usr/sbin/apachectl"]
CMD ["-D", "FOREGROUND"]

