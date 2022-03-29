.PHONY: lint phpunit phpdoc phpcs phpcbf clean

test: lint install phpcs

lint:
	find . -name \*.php -not -path "./vendor/*" -print0 | xargs -0 -n 1 -P 4 php -d display_errors=stderr -l > /dev/null

install:
	test -f /tmp/phpcs || curl -L https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar -o /tmp/phpcs && chmod +x /tmp/phpcs
	test -f /tmp/phpcbf || curl -L https://squizlabs.github.io/PHP_CodeSniffer/phpcbf.phar -o /tmp/phpcbf && chmod +x /tmp/phpcbf
	test -d /tmp/wpcs || git clone -b master --depth 1 https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards.git /tmp/wpcs
	/tmp/phpcs --config-set installed_paths /tmp/wpcs

phpcs:
	/tmp/phpcs -ps . --standard=WordPress-Extra --extensions=php --runtime-set ignore_warnings_on_exit true

phpcbf:
	/tmp/phpcbf -ps . --standard=WordPress-Extra

clean:
	rm -rf /tmp/phpcs
	rm -rf /tmp/phpcbf
	rm -rf /tmp/wpcs
