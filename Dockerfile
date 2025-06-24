FROM mcr.microsoft.com/devcontainers/php:0-8.2

# Install unzip
RUN apt-get update && apt-get install -y unzip

# Install Laravel Installer (optional)
RUN composer global require laravel/installer

# Add composer bin to PATH
ENV PATH="/root/.composer/vendor/bin:$PATH"
