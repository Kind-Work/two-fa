<?php

return [
  'activated' => '2FA aktiviert',
  'button' => 'Aktivieren',
  'label' => 'Zeitbasierter 2FA Code',
  'key_label' => 'Key',
  'url_label' => 'URL',
  'other_user_msg' =>
    'Sie haben nicht die Berechtigung, die 2FA-Einstellungen eines anderen Benutzers zu bearbeiten.',
  'get_app' => "Sie haben noch keine 2FA-App? Holen Sie sich eine für:",
  'enable' => [
    'description' =>
      'Erhöhen Sie die Sicherheit meines Kontos, indem Sie beim Einloggen einen zeitbasierten 2FA Code verlangen.',
    'button' => 'Mein Konto mit 2FA schützen',
  ],
  'errors' => [
    'code' => 'Der Code wurde nicht akzeptiert. Bitte versuchen Sie es erneut.',
    'unknown' =>
      'Bei der Aktivierung Ihres 2FA-Codes ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.',
  ],
  'android' => [
    [
      'name' => 'Google Authenticator',
      'url' =>
        'https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2',
    ],
    [
      'name' => 'LastPass Authenticator',
      'url' =>
        'https://play.google.com/store/apps/details?id=com.lastpass.authenticator',
    ],
    [
      'name' => 'Authy',
      'url' => 'https://play.google.com/store/apps/details?id=com.authy.authy',
    ],
    [
      'name' => 'Duo Mobile',
      'url' =>
        'https://play.google.com/store/apps/details?id=com.duosecurity.duomobile',
    ],
    [
      'name' => 'Microsoft Authenticator',
      'url' =>
        'https://play.google.com/store/apps/details?id=com.azure.authenticator',
    ],
    [
      'name' => 'Google Authenticator',
      'url' =>
        'https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2',
    ],
  ],
  'ios' => [
    [
      'name' => 'Google Authenticator',
      'url' => 'https://apps.apple.com/us/app/google-authenticator/id388497605',
    ],
    [
      'name' => 'LastPass Authenticator',
      'url' =>
        'https://apps.apple.com/us/app/lastpass-authenticator/id1079110004',
    ],
    [
      'name' => 'Authy',
      'url' => 'https://apps.apple.com/us/app/authy/id494168017',
    ],
    [
      'name' => 'Duo Mobile',
      'url' => 'https://apps.apple.com/us/app/duo-mobile/id422663827',
    ],
    [
      'name' => 'Microsoft Authenticator',
      'url' =>
        'https://apps.apple.com/us/app/microsoft-authenticator/id983156458',
    ],
    [
      'name' => 'Google Authenticator',
      'url' => 'https://apps.apple.com/ca/app/google-authenticator/id388497605',
    ],
  ],
];
