# Use official PHP image
FROM php:8.2-cli

# Install MySQL extension
RUN docker-php-ext-install mysqli

# Set working directory
WORKDIR /app

# Copy all project files into the container
COPY . /app

# Expose port
EXPOSE 10000

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:10000"]
