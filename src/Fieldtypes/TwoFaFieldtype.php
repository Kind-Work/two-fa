<?php

namespace KindWork\TwoFa\Fieldtypes;

use Config;
use Spatie\Url\Url;
use Statamic\Facades\User;
use Illuminate\Http\Request;
use PragmaRX\Google2FAQRCode\Google2FA;
use PragmaRX\Google2FA\Support\Constants;

class TwoFaFieldtype extends \Statamic\Fields\Fieldtype
{
  private $url;
  private $name;
  private $email;
  private $userId;
  private $secretKey;
  private $google2fa;
  private $error;
  private $active;

  public function __construct(Request $request)
  {
    $this->url = $request->url();
  }

  private function setup()
  {
    // Get the current url
    $url = Url::fromString($this->url);

    // Get the CP base url
    $cpUrl = Url::fromString(cp_route('index'));

    // Set the initial offset to one
    $offset = 1;

    // While we have segments in the CP base url increment the offset
    while ($cpUrl->getSegment($offset)) {
      $offset += 1;
    }

    // Get the current user for future use
    $currentUser = User::current();

    // If the first section of the offset is users and we have a second
    // we assume that we are actually on a user edit profile page
    // also make sure that we can find the user and assign it to $user
    if (
      $url->getSegment($offset) == 'users' &&
      $url->getSegment($offset + 1) &&
      ($user = User::find($url->getSegment($offset + 1)))
    ) {
      // Check to see if the current user is equal to the selected user
      // or that the current user is able to edit user passwords
      if (
        $user->id() == $currentUser->id() ||
        $currentUser->can('users:edit-passwords')
      ) {
        // Get the app name
        $this->name = Config::get('app.name');

        // Store the user id & email
        $this->email = $user->email();
        $this->userId = $user->id();

        // Set up 2FA
        $this->active = isset($user->data()['two_fa']) ? true : false;
        $this->google2fa = new Google2FA();
        if (Config::get('two-fa.qrCodeType') == 'SVG') {
          $this->google2fa->setQrcodeService(
            new \PragmaRX\Google2FAQRCode\QRCode\Bacon(
              new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            )
          );
        }
        $this->secretKey = $this->google2fa->generateSecretKey();
        return true;
      }

      $this->error = __('twofa::activate.other_user_msg');
      return false;
    }

    // Set non user route error
    $this->error = __('twofa::errors.invalid_resource');
    return false;
  }

  public function preload()
  {
    // Get the data needed for setup
    $this->setup();

    // If the name was not defined then we are not on a user edit page, display an error
    if ($this->name == null) {
      return [
        'state' => 'error',
        'error' => $this->error,
      ];
    }

    // Otherwise lets return the data needed for the Vue component
    return [
      'state' => $this->active ? 'active' : 'idle',
      'actions' => [
        'activate' => cp_route('two-fa.activate'),
        'disable' => cp_route('two-fa.disable'),
      ],
      'email' => $this->email,
      'id' => $this->userId,
      'key' => $this->secretKey,
      'url' => $this->google2fa->getQRCodeUrl(
        $this->name,
        $this->email,
        $this->secretKey
      ),
      'qrCode' => $this->google2fa->getQRCodeInline(
        $this->name,
        $this->email,
        $this->secretKey
      ),
    ];
  }
}
