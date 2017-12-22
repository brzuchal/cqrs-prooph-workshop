FROM php:7.2-apache-stretch

RUN pecl install redis \
  && pecl install xdebug-2.6.0alpha1 \
  && docker-php-ext-enable redis xdebug
RUN apt-get update -q \
  && apt-get install -yqq zlib1g-dev \
  && docker-php-ext-install zip \
  && docker-php-ext-enable zip \
  && rm -rf /var/lib/apt/lists/*
ARG UID
RUN useradd -r -u $UID -g www-data php
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
  && php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
  && php composer-setup.php --install-dir=/usr/local/bin \
  && php -r "unlink('composer-setup.php');" \
  && mkdir /usr/lib/composer \
  && chgrp www-data /usr/lib/composer \
  && chmod g+w /usr/lib/composer \
  && echo "#!/bin/bash\nCOMPOSER_HOME=/usr/lib/composer php -dxdebug.remote_autostart=0 -dxdebug.remote_enable=0 -dxdebug.profiler_enable=0 /usr/local/bin/composer.phar \$@" > /usr/local/bin/composer \
  && chmod 0777 /usr/local/bin/composer
USER php:www-data
