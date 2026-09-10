#!/bin/bash
set -e

# Run Laravel migrations (--force skips production confirmation prompt)
php artisan migrate --force

# Start Apache
exec apache2-foreground
