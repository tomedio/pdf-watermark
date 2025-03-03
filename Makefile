.PHONY: install test cs cs-fix

# Install dependencies
install:
	composer install

# Run tests
test:
	vendor/bin/phpunit

# Check coding standards
cs:
	vendor/bin/phpcs

# Fix coding standards
cs-fix:
	vendor/bin/phpcbf

# Install Ghostscript (Ubuntu/Debian)
install-gs-debian:
	sudo apt-get update
	sudo apt-get install ghostscript

# Install Ghostscript (macOS)
install-gs-mac:
	brew install ghostscript

# Run example
example:
	php examples/example.php
