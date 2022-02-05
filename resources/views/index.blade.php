@inject('str', 'Statamic\Support\Str')
@extends('statamic::outside')
@section('title', __("twofa::auth.title"))

@section("content")

@include('statamic::partials.outside-logo')

<div class="card auth-card mx-auto">
  <div>
    <form method="POST">
      {!! csrf_field() !!}

      @if (isset($error))
        <p class="two-fa-error rounded-lg p-1 mb-2">{{ $error }}</p>
      @endif

      <div class="mb-4">
        <label class="mb-1" for="code">{{ __("twofa::auth.label") }}</label>
        <input type="number" class="two-fa-input input-text" name="code" id="code" pattern="\d{6}" maxlength="6" minlength="6" step="1" required>
      </div>
      <div class="mb-4">
        <label for="remember-me" class="flex items-center cursor-pointer">
          <input type="checkbox" name="remember" id="remember-me">
          <span class="ml-1">Remember me</span>
        </label>
      </div>
      <div class="flex justify-between items-center">
        <a href="{{ route('statamic.logout') }}">Cancel</a>
        <button type="submit" class="btn btn-primary">{{ __("twofa::auth.button") }}</button>
      </div>
    </form>
  </div>
</div>

@endsection
