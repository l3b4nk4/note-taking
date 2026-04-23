# Use Ubuntu 24.04 as the base image
FROM ubuntu:24.04

# Prevent package installation prompts during build
ENV DEBIAN_FRONTEND=noninteractive

# Set the working directory inside the container
WORKDIR /var/www/html

# Update package list, install Apache + PHP, then clean apt cache
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        apache2 \
        libapache2-mod-php \
        php \
        php-cli \
        php-mysql \
    && rm -rf /var/lib/apt/lists/*
# Copy project files into Apache web root
COPY . /var/www/html

# Create storage directory and set ownership to Apache user
RUN mkdir -p /var/www/storage \
    && chown -R www-data:www-data /var/www/html /var/www/storage

# Declare persistent storage volume
VOLUME ["/var/www/storage"]

# Document that the container listens on HTTP port 80
EXPOSE 80

# Start Apache in foreground so container keeps running
CMD ["apachectl", "-D", "FOREGROUND"]