# Cumulus

[![Total Downloads](https://img.shields.io/packagist/dt/orrison/cumulus.svg?style=flat-square)](https://packagist.org/packages/orrison/cumulus)
[![License](http://poser.pugx.org/orrison/cumulus/license)](https://packagist.org/packages/orrison/cumulus)

Cumulus is a package to be used with the Laravel Vapor CLI. It provides a set of commands to import DNS records for your Vapor custom domain into Cloudflare.

No more having to copy each manually which takes too long and is prone to errors. Cumulus will create any missing DNS records for you as well as update any incorrect or changed records.

Keep in mind that in order for the import to work the domain must be a custom domain in your Laravel Vapor team as well as a zone in your Cloudflare account already. This package will take care of importing the DNS records for you, not adding the domains.

You will also need to have [Laravel Vapor CLI](https://docs.vapor.build/1.0/introduction.html#installing-the-vapor-cli) installed and authenticated on your machine.

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
cumulus cloudflare:login
```
If, for what ever reason, you would like to clear this token from local storage then you can run the following command.
```bash
cumulus cloudflare:logout
```

Alternatively, if you wish to load your API token from your server environment, you can do so by declaring `CLOUDFLARE_API_TOKEN` as an environment variable. When this is defined it will always take priority over your Cumulus config stored in your user profile.

---

### Importing DNS Records
Cumulus directly executes some commands via Laravel Vapor. So you will need to be logged in with the Vapor CLI that is either globally or per project installed. Learn about that in the [Vapor Documentation](https://docs.vapor.build/1.0/introduction.html#installing-the-vapor-cli).

Now that you have a valid Cloudflare API token and are logged in to a Vapor team that has access to the domain you are attempting to import, you can run the following command to import the DNS records for your domain.
```bash
cumulus records:import [THE_DOMAIN_NAME]
```
This will import any missing DNS records that Vapor specifies into the Cloudflare DNS zone. As well as update any incorrect or changed records.

If you would like to see what changes would be made before actually importing, you can add `--dry-run` to the end of the command.

The import command will attempt to "proxy" each added/updated record if it can be proxied. If you would not like the added records proxied, you can add `--no-proxy` to the end of the command.

### Info about DNS record generation and Subdomains

Cumulus will work regardless of the environment you have assigned the custom domain to. It simply imports the correct DNS records for the domain provided. Laravel Vapor generates and stores the DNS records when a domain is assigned to a project environment and successfully deployed.

When you use a subdomain for a project environment Laravel Vapor will automatically generate the correct DNS records and store them with the root domain. So in order to import the correct DNS records for the subdomain you will need to import the root domain.

For example if you have a subdomain `sub.example.com`, you would run the following command to import the DNS records:
```bash
cumulus records:import example.com
```
