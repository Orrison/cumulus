# Cumulus

[![Latest Version on Packagist](https://img.shields.io/packagist/v/orrison/cumulus.svg?style=flat-square)](https://packagist.org/packages/orrison/cumulus)
[![Total Downloads](https://img.shields.io/packagist/dt/orrison/cumulus.svg?style=flat-square)](https://packagist.org/packages/orrison/cumulus)

Cumulus is a package to be used with the Laravel Vapor CLI. It provides a set of commands to import DNS records for your Vapor custom domain into Cloudflare.

No more having to copy each manually which takes too long and is prone to errors. Cumulus will create any missing DNS records for you as well as update any incorrect or changed records.

Keep in mind that in order for the import to work the domain must be a custom domain in your Laravel Vapor team as well as a zone in your Cloudflare account already. This package will take care of import the DNS records for you, not adding the domains.

---

## Installation
```bash
composer global require orrison/cumulus --with-all-dependencies
```

---

## Usage

### Cloudflare Authentication
In order to use Cumulus, you will need a valid Cloudflare API Access Token. You can get one by following the instructions in their [Documentation](https://developers.cloudflare.com/api/tokens/create).

The "Edit DNS Zone" template is perfect for this. You will just need set the "Zone Resources" options to either `All Zones` or the correct option for your use case.

Once you have your token you can run the following command to input your token. It is then stored for use in future commands.
```bash
cumulus cloudlfare:login
```
If, for what ever reason, you would like to clear this token from local storage then you can run the following command.
```bash
cumulus cloudlfare:logout
```

---

### Importing DNS Records
Cumulus directly executes some commands via Laravel Vapor. So you will need to be logged in with the Vapor CLI that is either globally or per project installed. Learn about that in the [Vapor Documentation](https://docs.vapor.build/1.0/introduction.html#installing-the-vapor-cli).

Now that you have a valid Cloudflare API token and are logged in to a Vapor team that has access to the domain you are attempting to import, you can run the following command to import the DNS records for your domain.
```bash
cumulus cloudflare:import [THE_DOMAIN_NAME]
```
This will import any missing DNS records that Vapor specifies into the Cloudflare DNS zone. As well as update any incorrect or changed records.

If you would like to see what changes would be made before actually making the changes, you can add `--dry-run` to the end of the command.

The import command will attempt to "proxy" each added/updated record if it can be proxied. If you would not like the added records proxied, you can add `--no-proxy` to the end of the command.