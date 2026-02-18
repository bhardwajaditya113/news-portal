@extends('frontend.layouts.master')

@section('content')
    <!-- LIVE NEWS TICKER - Real-time Breaking News -->
    @include('frontend.home-components.live-ticker')
    
    <!-- breaking news  carousel-->
    @include('frontend.home-components.breaking-news')
    <!-- End breaking news carousel -->

    <!-- Hero news Secion-->
    @include('frontend.home-components.hero-slider')

    <!-- End Hero news Section-->
    
    <!-- TRENDING SECTION - World-class Feature -->
    @include('frontend.home-components.trending-section')
    
    @if ($ad && $ad->home_top_bar_ad_status == 1)
    <a href="{{ $ad->home_top_bar_ad_url }}">
        <div class="large_add_banner">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="large_add_banner_img">
                            <img src="{{ $ad->home_top_bar_ad }}" alt="adds">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </a>
    @endif

    <!-- LATEST AGGREGATED NEWS - Real-time from Multiple Sources -->
    @include('frontend.home-components.aggregated-news')

    <!-- NEWS BY SOURCE - Multi-source Display -->
    @include('frontend.home-components.news-by-source')

    <!-- Popular news category -->
    @include('frontend.home-components.main-news')
    <!-- End Popular news category -->
@endsection
