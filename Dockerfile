FROM php:8.4-cli

RUN apt-get -y update
RUN apt-get -y upgrade
RUN apt-get -y install git zip curl

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions zip
RUN git config --global --add safe.directory /app

## Install a more modern Node.js via NodeSource (Node 25)
RUN curl -fsSL https://deb.nodesource.com/setup_25.x | bash - \
	&& apt-get install -y nodejs

RUN adduser dev

WORKDIR /app

USER dev

CMD ["php", "-a"]