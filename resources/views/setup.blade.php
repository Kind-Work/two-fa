@inject('str', 'Statamic\Support\Str')
@extends('statamic::outside')
@section('title', __("twofa::setup.title"))

@section("content")

@include('statamic::partials.outside-logo')

<div class="card auth-card mx-auto">
  <div>
    <form method="POST" action="{{ cp_route('two-fa.activate') }}">
      {!! csrf_field() !!}
      <input type="hidden" value="{{ $secretKey }}" name="key" />

      @if (isset($error))
        <p class="two-fa-error rounded-lg p-1 mb-2">{{ $error }}</p>
      @endif

      <div class="mb-2 text-center">
        @if ($qrCodeType == 'SVG')
          {!! $qrCode !!}
        @else
          <img
            src="{{ $qrCode }}"
            class="inline-block max-w-full h-auto"
          />
        @endif
      </div>
      <div class="mb-4">
        <label class="mb-1" for="secret">{{ __("twofa::setup.label") }}</label>
        <input
          type="number"
          class="two-fa-input input-text"
          name="secret"
          id="secret"
          pattern="\d{6}"
          maxlength="6"
          minlength="6"
          step="1"
          autocomplete="one-time-code"
          required
        />
      </div>
      <div class="flex justify-between items-center">
        <button type="submit" class="btn btn-primary">{{ __("twofa::setup.button") }}</button>
      </div>
      <div class="mt-4 text-sm text-grey-70">
        <p class="break-all"><strong>Key:</strong> {{ $secretKey }}</p>
        <p class="break-all"><strong>URL:</strong> {{ $url }}</p>
      </div>
    </form>
  </div>
</div>

@endsection
