@inject('str', 'Statamic\Support\Str')
@extends('statamic::outside')
@section('body_class', 'rad-mode')
@section('title', __('twofa::errors.title'))

@section('content')

@include('statamic::partials.outside-logo')

<div class="card auth-card mx-auto">
  <div>
    <p class="two-fa-error rounded-lg p-1 mb-2">{{ __("twofa::errors.locked") }}</p>
    <p><a href="/">{{ __("twofa::errors.home") }}</a></p>
  </div>
</div>
@endsection
