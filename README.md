![Statamic 3.0+](https://img.shields.io/badge/Statamic-3.0+-FF269E?style=for-the-badge&link=https://statamic.com)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/4fe2d8a500d94b05b4198a49f1bc9d03)](https://www.codacy.com/gh/kind-work/two-fa/dashboard?utm_source=github.com&utm_medium=referral&utm_content=kind-work/two-fa&utm_campaign=Badge_Grade)

## Two Factor Login for Statamic 3

Statamic 2FA is a middleware addon for [Statamic 3](https://github.com/statamic/cms) that adds 2FA (2 factor) auth to the control panel of Statamic 3 using time based codes.

## Pricing

Statamic 2FA is commercial software. You do not need a licence for development but when you are ready to deploy the site to production please purchase a licence per site on the [Statamic Marketplace](https://statamic.com/marketplace/addons/2fa).

## Installation

### Install the addon using composer

You can install this addon via composer with the following command or from the Statamic control panel.

```bash
composer require kind-work/two-fa
```

## Usage

Add the `two_fa` field to your user blueprint. Edit your user profile in the control panel (CP) to set up 2FA protection for your account.

```yaml
title: User
sections:
  main:
    display: Main
    fields:
      - handle: name
        field:
          type: text
          display: Name
      - handle: email
        field:
          type: text
          input: email
          display: 'Email Address'
      - handle: roles
        field:
          type: user_roles
          width: 50
      - handle: groups
        field:
          type: user_groups
          width: 50
      - handle: avatar
        field:
          type: assets
          max_files: 1
      - handle: two_fa
        field:
          type: two_fa
          localizable: false
          display: 'Two FA'
```

## Force 2FA Login

To force 2FA for all users set an environment variable `FORCE_2FA` to `true`.

To force 2FA for specific roles, publish the config file and edit as appropriate.

```bash
php artisan vendor:publish --tag="two-fa-config"
```

## QRCode Rendering

By default QR codes are rendered as inline PNG files. This requires the Imagick PHP extension. If you would rather not or can not install Imagick you can render the QR codes as SVGs instead. You can customize this in your configuration file or by setting the environment variable `QR_CODE_TYPE` to `SVG`.

## Max Attempts

By default accounts are locked out after 5 attempts to enter a 2FA code. You can change this in the config or by setting the environment variable `2FA_MAX_ATTEMPTS`.

## Remember Time

A user can choose to remember the browser when they enter their 2FA code, so they do not have to re-enter their 2FA code on each login. By default the time to remember the code is 30 days. You can change this default by setting the number of minutes that the browser should be remembered in the config or the environment variable `2FA_REMEMBER_TIME`.

## Database

If you store your users in a database run the following command to generate a database migration.

```bash
php artisan vendor:publish --tag="twofa-migrations"
```

## Changelog

Please see the [Release Notes](https://statamic.com/addons/jrc9designstudio/2fa/release-notes) for more information what has changed recently.

## Security

If you discover any security-related issues, please email security@kind.work instead of using the issue tracker.

## License

This is commercial software. You may use the package for your sites. Each site requires its own license. You can purchase a licence from The [Statamic Marketplace](https://statamic.com/marketplace/addons/2fa).
