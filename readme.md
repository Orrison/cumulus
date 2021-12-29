# Cumulus

[![Latest Version on Packagist](https://img.shields.io/packagist/v/orrison/cumulus.svg?style=flat-square)](https://packagist.org/packages/orrison/cumulus)
[![Total Downloads](https://img.shields.io/packagist/dt/orrison/cumulus.svg?style=flat-square)](https://packagist.org/packages/orrison/cumulus)

Cumulus is a package to be used with the Laravel Vapor CLI. It provides a set of commands to import DNS records for your Vapor custom domain into Cloudflare.

No more having to copy each manually which takes too long and is prone to errors. Cumulus will create any missing DNS records for you as well as update any incorrect or changed records.

---

## Installation
```bash
composer global require orrison/cumulus --with-all-dependencies
```

---

## Usage
In order to use Cumulus, you will need a valid Cloudflare API Access Token. You can get one by following the instructions in their [Documentation](https://developers.cloudflare.com/api/tokens/create).

The "Edit DNS Zone" template is perfect for this. You will just need set the "Zone Resources" options to either `All Zones` or the correct option for your use case.

Once you have your token you can run the following command to input your token. It is then stored for use in future commands.
```bash
cumulus cloudlfare:login
```