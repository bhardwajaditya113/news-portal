<!-- Personalized For You Section (Google News Style) -->
<section class="for-you-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-header d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">
                            <i class="fas fa-user-circle text-primary mr-2"></i>
                            {{ __('frontend.For You') }}
                        </h4>
                        <p class="text-muted small mb-0">{{ __('frontend.Personalized news based on your interests') }}</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <a href="#" class="btn btn-sm btn-outline-primary mr-2" data-toggle="modal" data-target="#preferencesModal">
                            <i class="fas fa-cog mr-1"></i> {{ __('frontend.Customize') }}
                        </a>
                        <a href="{{ route('news.index', ['filter' => 'personalized']) }}" class="btn btn-sm btn-primary">
                            {{ __('frontend.View All') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Featured Article -->
            <div class="col-lg-6 mb-4">
                @if(isset($personalizedNews) && $personalizedNews->count() > 0)
                @php $featured = $personalizedNews->first(); @endphp
                <article class="featured-card">
                    <a href="{{ route('news.show', $featured->slug ?? $featured->id) }}">
                        <div class="featured-image">
                            <img src="{{ $featured->image ?? asset('frontend/assets/images/placeholder.jpg') }}" alt="{{ $featured->title }}">
                            @if($featured->category)
                            <span class="category-badge">{{ $featured->category->name }}</span>
                            @endif
                            @if($featured->is_breaking ?? false)
                            <span class="breaking-badge"><i class="fas fa-bolt mr-1"></i>Breaking</span>
                            @endif
                        </div>
                        <div class="featured-content">
                            <h3 class="featured-title">{{ $featured->title }}</h3>
                            <p class="featured-excerpt">{{ Str::limit(strip_tags($featured->content ?? $featured->description ?? ''), 150) }}</p>
                            <div class="featured-meta">
                                <span class="meta-source">
                                    @if(isset($featured->source))
                                    <img src="{{ $featured->source->logo_url ?? '' }}" alt="" class="source-logo">
                                    {{ $featured->source->name }}
                                    @else
                                    {{ $featured->author ?? config('app.name') }}
                                    @endif
                                </span>
                                <span class="meta-time">
                                    <i class="far fa-clock mr-1"></i>
                                    {{ $featured->published_at ? $featured->published_at->diffForHumans() : $featured->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </a>
                </article>
                @endif
            </div>

            <!-- News List -->
            <div class="col-lg-6">
                <div class="news-list">
                    @foreach(($personalizedNews ?? collect())->skip(1)->take(4) as $news)
                    <article class="news-list-item">
                        <a href="{{ route('news.show', $news->slug ?? $news->id) }}">
                            <div class="row no-gutters">
                                <div class="col-4">
                                    <div class="news-thumb">
                                        <img src="{{ $news->image ?? asset('frontend/assets/images/placeholder.jpg') }}" alt="{{ $news->title }}">
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="news-content">
                                        @if($news->category)
                                        <span class="news-category">{{ $news->category->name }}</span>
                                        @endif
                                        <h5 class="news-title">{{ Str::limit($news->title, 65) }}</h5>
                                        <div class="news-meta">
                                            <span class="news-source">
                                                @if(isset($news->source))
                                                {{ $news->source->name }}
                                                @else
                                                {{ $news->author ?? config('app.name') }}
                                                @endif
                                            </span>
                                            <span class="news-time">{{ $news->published_at ? $news->published_at->diffForHumans() : $news->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </article>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.for-you-section {
    background: #fff;
}

/* Featured Card */
.featured-card {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    height: 100%;
}

.featured-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.featured-card a {
    text-decoration: none;
    color: inherit;
}

.featured-image {
    position: relative;
    height: 280px;
    overflow: hidden;
}

.featured-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.featured-card:hover .featured-image img {
    transform: scale(1.05);
}

.category-badge {
    position: absolute;
    top: 16px;
    left: 16px;
    background: var(--colorPrimary);
    color: #fff;
    padding: 4px 12px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.breaking-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    background: #dc3545;
    color: #fff;
    padding: 4px 12px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 4px;
    animation: pulse 1.5s infinite;
}

.featured-content {
    padding: 20px;
}

.featured-title {
    font-size: 22px;
    font-weight: 700;
    line-height: 1.3;
    margin-bottom: 12px;
    color: #1e293b;
}

.featured-excerpt {
    color: #64748b;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 16px;
}

.featured-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: #94a3b8;
}

.meta-source {
    display: flex;
    align-items: center;
}

.source-logo {
    width: 16px;
    height: 16px;
    object-fit: contain;
    margin-right: 6px;
}

/* News List */
.news-list-item {
    border-bottom: 1px solid #f1f5f9;
    padding: 16px 0;
    transition: background 0.2s ease;
}

.news-list-item:last-child {
    border-bottom: none;
}

.news-list-item:hover {
    background: #f8fafc;
}

.news-list-item a {
    text-decoration: none;
    color: inherit;
}

.news-thumb {
    height: 90px;
    border-radius: 8px;
    overflow: hidden;
}

.news-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.news-content {
    padding-left: 16px;
}

.news-category {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--colorPrimary);
    letter-spacing: 0.5px;
}

.news-title {
    font-size: 15px;
    font-weight: 600;
    line-height: 1.4;
    margin: 6px 0 8px;
    color: #1e293b;
}

.news-meta {
    display: flex;
    gap: 12px;
    font-size: 11px;
    color: #94a3b8;
}
</style>
