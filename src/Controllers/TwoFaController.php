<?php

namespace KindWork\TwoFa\Controllers;

use Log;
use Config;
use Statamic\Facades\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FAQRCode\Google2FA;
use PragmaRX\Google2FA\Support\Constants;
use Illuminate\Contracts\Encryption\DecryptException;

class TwoFaController extends Controller
{
  private $google2fa;
  private $window = 1;
  private $user;
  private $currentUser;

  private function boot($userId = null)
  {
    // Set up 2FA
    $this->google2fa = new Google2FA();

    // Get current user for future reference
    $this->currentUser = User::current();

    // Is there a user id param
    if ($userId) {
      // Find the user
      $user = User::find($userId);
      // Check to see if we have the user, and the user is current
      // or if the user can edit user passwords
      if (
        $user &&
        ($user->id() == $this->currentUser->id() ||
          $this->currentUser->can('users:edit-passwords'))
      ) {
        $this->user = $user;
      }
    } else {
      $this->user = $this->currentUser;
    }
  }

  /**
   * Show the profile for the given user.
   *
   * @return View
   */
  public function index()
  {
    $this->currentUser = User::current();

    // Check to see if the account is locked, if so log out and show locked msg.
    if ($this->currentUser->data()['two_fa_locked'] ?? false) {
      Auth::logout();
      return view('twofa::locked');
    }

    return view('twofa::index');
  }

  public function setup($error = null)
  {
    $google2fa = new Google2FA();
    $secretKey = $google2fa->generateSecretKey();
    $email = User::current()->email;
    $name = Config::get('app.name');

    if (Config::get('two-fa.qrCodeType') == 'SVG') {
      $google2fa->setQrcodeService(
        new \PragmaRX\Google2FAQRCode\QRCode\Bacon(
          new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        )
      );
    }

    return view('twofa::setup', [
      'error' => $error,
      'secretKey' => $secretKey,
      'url' => $google2fa->getQRCodeUrl($name, $email, $secretKey),
      'qrCodeType' => Config::get('two-fa.qrCodeType'),
      'qrCode' => $google2fa->getQRCodeInline(
        Config::get('app.name'),
        $email,
        $secretKey
      ),
    ]);
  }

  /**
   * Show the profile for the given user.
   * @param  Request  $request
   * @return JSON (success or fail)
   */
  public function activate(Request $request)
  {
    $this->boot($request->input('id'));

    // Set up variables to check key & check if key is valid
    $secret = $request->input('secret');
    $key = $request->input('key');

    // Check that we have a user, key and code and the code and key match, if they do save the key to activate
    if (
      $valid =
        $this->user &&
        $key &&
        $secret &&
        $this->google2fa->verifyKey($key, $secret, $this->window)
    ) {
      $this->user->set('two_fa', Crypt::encryptString($key));
      $this->user->set('2FA', '✓');
      $this->user->save();
      // If we are activating our own 2FA auto auth and remove invalid 2fa count
      if ($this->user->id() == $this->currentUser->id()) {
        $request->session()->put('two_fa_authenticated', true);
        $request->session()->pull('invalid_2fa_count');
      }
    }

    if ($request->ajax()) {
      // Return the success based on $valid
      return [
        'success' => $valid,
      ];
    }

    if ($valid) {
      return redirect(cp_route('index'));
    }

    $error = __('twofa::errors.setup-code');

    return $this->setup($error);
  }

  public function authenticate(Request $request)
  {
    $this->boot();

    // Check to see if the account is locked, if so log out and show locked msg.
    if ($this->currentUser->data()['two_fa_locked'] ?? false) {
      Auth::logout();
      return view('twofa::locked');
    }

    // Set up variables
    $secret = $request->input('code');
    $keyValue = $this->currentUser->data()['two_fa'] ?? false;
    try {
      $key = Crypt::decryptString($keyValue);
    } catch (DecryptException $e) {
      $key = false;
      Log::error($e->getMessage());
    }
    $invalid2faCount = $request->session()->get('invalid_2fa_count', 0);

    // Check code and if passes set auth on session and reset invalid count for session
    if (
      $key &&
      $secret &&
      $this->google2fa->verifyKey($key, $secret, $this->window)
    ) {
      $request->session()->put('two_fa_authenticated', true);
      $request->session()->pull('invalid_2fa_count');
      return redirect(cp_route('index'));
    }

    // Set default error msg
    if (!$key) {
      $error = __('twofa::errors.setup');
      return view('twofa::setup', ['error' => $error]);
    } elseif (!$secret) {
      $error = __('twofa::errors.code');
    } else {
      $error = __('twofa::errors.unknown');
    }

    // If the invalid count is too high lock the account
    if ($invalid2faCount > 5) {
      $this->currentUser->set('two_fa_locked', true);
      $this->currentUser->save();
      Auth::logout();
      return view('twofa::locked');
    }

    // Increment the invalid count (we already returned way above if it was correct)
    $request->session()->put('invalid_2fa_count', $invalid2faCount + 1);

    // Return the view with the error
    return view('twofa::index', ['error' => $error]);
  }

  public function disable(Request $request)
  {
    $this->boot($request->input('id'));

    // Set up variables & check if key is valid
    $secret = $request->input('secret');
    $keyValue = $this->currentUser->data()['two_fa'] ?? false;
    try {
      $key = Crypt::decryptString($keyValue);
    } catch (DecryptException $e) {
      $key = false;
      Log::error($e->getMessage());
    }

    // Check that we have a user, key ad code and the code and key match and if they are we can disable 2FA
    if (
      $this->user &&
      $key &&
      $secret &&
      ($valid = $this->google2fa->verifyKey($key, $secret, $this->window))
    ) {
      $this->user->set('two_fa', null);
      $this->user->set('2FA', '✕');
      $this->user->save();

      // If we are deactivating our own 2FA destroy the session 2fa data
      if ($this->user->id() == $this->currentUser->id()) {
        $request->session()->pull('two_fa_authenticated');
        $request->session()->pull('invalid_2fa_count');
      }
    }

    // Return if we were successful disabling 2FA
    return [
      'success' => $valid,
    ];
  }
}
