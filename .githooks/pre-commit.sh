#!/bin/sh

echo "Running Laravel Pint..."

# Run Laravel Pint
./vendor/bin/pint


# Check if Pint passes
if [ $? -ne 0 ]; then
    echo "Laravel Pint found issues. Please fix them before committing."
    exit 1
fi

exit 0
