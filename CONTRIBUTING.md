# Contributing

All contributions are **welcome** and **very much appreciated**.

We accept contributions via Pull Requests on [Github](https://github.com/ajgarlag/openid-connect-provider-bundle).

## Pull Request guidelines

- **Add tests!** - We strongly encourage adding tests as well since the PR might not be accepted without them.

- **Document any change in behaviour** - Make sure the `README.md`, `CHANGELOG.md` and any other relevant documentation are kept up-to-date.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please [squash them](http://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages) before submitting.

## Development

You need an environment with PHP 8.2 or higher with Composer to develop this bundle.

### Installing dependencies

Download all the needed packages required to develop the project:

```sh
composer update --prefer-stable
```

### Code linting

This bundle enforces the PER-CS and Symfony code standards during development by using the [PHP CS Fixer](https://cs.symfony.com/) utility. Before committing any code, you can run the utility to fix any potential rule violations:

```sh
vendor/bin/php-cs-fixer fix
```

### Automated refactoring

You can apply automated refactoring using [Rector](https://getrector.com) utility. Before committing any code, you can run the utility to fix any potential rule violations:

```sh
vendor/bin/rector
```

### Running static analysis

You can run static anaysis of code using [PHPStan](https://phpstan.org/):

```sh
vendor/bin/phpstan
```

### Testing

You can run the test suite using [PHPUnit](https://phpunit.de) with the through the [Symfony PHPUnit Bridge]([symfony/phpunit-bridge](https://symfony.com/packages/PHPUnit%20Bridge)):

```sh
vendor/bin/simple-phpunit
```

There is a special test suite to ensure that the original behavior of the `league/oauth2-server-bundle` remains unchanged. You can run this special test suite using the following command:

```sh
vendor/bin/simple-phpunit -c phpunit.league.xml
```

**Happy coding**!
