<?php

return [
  /*
    |--------------------------------------------------------------------------
    | Force 2FA
    |--------------------------------------------------------------------------
    |
    | If we should enforce 2FA or not, valid values:
    | false - do not force 2FA
    | true - force 2FA for all users
    | "roles-only" - force 2FA only for specified user roles
    | "roles-except" - force 2FA for all roles except specified user roles
    |
    */

  'force' => env('FORCE_2FA', false),

  /*
  |--------------------------------------------------------------------------
  | QR Code PNG (Imagick) or SVG
  |--------------------------------------------------------------------------
  |
  | If we should use PNG or SVG files for the QR codes
  |
  */

  'qrCodeType' => env('QR_CODE_TYPE', 'PNG'),

  /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    |
    | If we using roles-only or roles-except this is the list of roles to use
    |
    */

  'roles' => ['super'],
];
