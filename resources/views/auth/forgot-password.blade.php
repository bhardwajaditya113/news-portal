@extends('frontend.layouts.master')

@section('content')
    <section class="wrap__section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mx-auto" style="max-width: 420px;">
                        <div class="card-body">
                            <h4 class="card-title mb-4">{{ __('frontend.Forgot password?') }}</h4>
                            <p class="text-muted mb-4">{{ __('frontend.Enter your email to receive a reset link.') }}</p>

                            @if (session('status'))
                                <div class="alert alert-success">{{ session('status') }}</div>
                            @endif

                            <form method="POST" action="{{ route('password.email') }}">
                                @csrf
                                <div class="form-group">
                                    <input class="form-control" placeholder="{{ __('frontend.Email') }}" type="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-block">{{ __('frontend.Send reset link') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <p class="text-center mt-4 mb-0">
                        <a href="{{ route('login') }}">{{ __('frontend.Back to login') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
