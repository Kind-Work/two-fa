<?php

namespace KindWork\TwoFa\Middleware;

use Config;
use Closure;
use Carbon\Carbon;
use Statamic\Facades\User;
use Illuminate\Http\Request;

class CheckTwoFa
{
  protected $user;
  protected $request;

  public function handle(Request $request, Closure $next)
  {
    $this->request = $request;
    $this->user = User::current()->data();

    if ($this->isAuthed()) {
      if ($this->isTwoFAPath() && !$request->ajax()) {
        return redirect(cp_route('index'));
      }
      return $next($this->request);
    }

    if (!$this->isTwoFAPath() && $this->isCodeRequired()) {
      // If we get here go to the two FA page to get the code
      return redirect(cp_route('two-fa'));
    }

    if (!$this->isTwoFAPath() && $this->isSetupForced()) {
      return redirect(cp_route('two-fa.setup'));
    }

    // Otherwise lets continue
    return $next($this->request);
  }

  private function isAuthed()
  {
    // Check to see if we already authenticated with 2FA on the session
    if ($this->request->session()->get('two_fa_authenticated')) {
      return true;
    }

    // Else if there is a valid remember token
    if (
      $token = $this->request->cookie('two_fa_rember_token') &&
      (
        Config::get('statamic.users.repository') != 'eloquent' ||
        ($this->user['two_fa_rember_token'] != null && $this->user['two_fa_rember_token_expires_at'] != null)
      )
    ) {
      $user = User::current();
      $rememberToken = Config::get('statamic.users.repository') == 'eloquent' ? $this->user['two_fa_rember_token'] : $user->getMeta('two_fa_rember_token');
      $tokenExpiresAt = Config::get('statamic.users.repository') == 'eloquent' ? $this->user['two_fa_rember_token_expires_at'] : $user->getMeta('two_fa_rember_token_expires_at');
      $currentTimestamp = Carbon::now()->timestamp;
      if ($token == $rememberToken && $tokenExpiresAt > $currentTimestamp) {
        // Add auth back to session
        $this->request->session()->put('two_fa_authenticated', true);
        return true;
      }
    }

    return false;
  }

  private function isCodeRequired()
  {
    return $this->isEnabled() &&
      // Make sure we are not already authenticated with 2FA on the session
      !$this->isAuthed();
  }

  private function isEnabled()
  {
    return // Make sure we have a user
      $this->user &&
        // Make sure two_fa is set
        isset($this->user['two_fa']) &&
        // Make sure we have a key
        !empty($this->user['two_fa']);
  }

  private function isTwoFAPath()
  {
    $cp = config('statamic.cp.route');
    return $this->request->path() === "$cp/two-fa" ||
      $this->request->path() === "$cp/two-fa/setup" ||
      $this->request->path() === "$cp/two-fa/activate-two-fa";
  }

  private function isSetupForced()
  {
    $force = Config::get('two-fa.force');

    // If 2FA Forced for all users
    if ($force === true) {
      return true;
    }

    // If Forced for the current user role
    if ($force == 'roles-only' && $this->rolesIntersect()) {
      return true;
    }

    // If Forced Exception List does not contain the current role
    if ($force == 'roles-except' && !$this->rolesIntersect()) {
      return true;
    }

    // Otherwise it is not forced
    return false;
  }

  private function rolesIntersect()
  {
    $roles = Config::get('two-fa.roles');
    return (in_array('super', $roles) && $this->user['super'] === true) ||
      array_intersect($roles, $this->user['roles']);
  }
}
