@extends('backend/layouts/default')

{{-- Page title --}}
@section('title')
Account Sign in ::
@parent
@stop

@section('stylesheet')
@parent
  <link rel="stylesheet" href="{{ asset('assets/css/lib/auth-buttons.css') }}" type="text/css" media="screen" />
@stop

{{-- Page content --}}
@section('content')

<div class="row header">
    <div class="col-md-12">
        <h3>Sign in into your account</h3>
    </div>
</div>

<div class="row form-wrapper">
      {{-- @include('frontend/auth/form'); --}}

      @include('frontend/auth/links')
</div>
@stop
