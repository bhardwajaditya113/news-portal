@extends('frontend.layouts.master')

@section('content')
    <section class="wrap__section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mx-auto" style="max-width: 420px;">
                        <div class="card-body">
                            <h4 class="card-title mb-4">{{ __('frontend.Reset password') }}</h4>

                            <form method="POST" action="{{ route('password.store') }}">
                                @csrf
                                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                                <div class="form-group">
                                    <input class="form-control" placeholder="{{ __('frontend.Email') }}" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus>
                                    @error('email')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <input class="form-control" placeholder="{{ __('frontend.Password') }}" type="password" name="password" required>
                                    @error('password')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <input class="form-control" placeholder="{{ __('frontend.Confirm Password') }}" type="password" name="password_confirmation" required>
                                    @error('password_confirmation')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-block">{{ __('frontend.Reset password') }}</button>
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
