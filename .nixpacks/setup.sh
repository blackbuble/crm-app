#!/bin/bash

# Custom setup script for Nixpacks
# This runs during the setup phase

echo "🔧 Running custom setup..."

# Install additional PHP extensions if needed
# (Most are already included in nixpacks.json)

# Set PHP configuration
echo "memory_limit = 256M" >> /etc/php/8.3/cli/conf.d/99-custom.ini
echo "upload_max_filesize = 20M" >> /etc/php/8.3/cli/conf.d/99-custom.ini
echo "post_max_size = 20M" >> /etc/php/8.3/cli/conf.d/99-custom.ini
echo "max_execution_time = 300" >> /etc/php/8.3/cli/conf.d/99-custom.ini

echo "✅ Custom setup completed!"
