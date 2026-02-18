<!-- Category News Sections (BBC/Reuters Style) -->
@foreach($categoryNews ?? [] as $category)
<section class="category-section py-5 {{ $loop->even ? 'bg-light' : '' }}">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <span class="category-icon" style="background: {{ $category->color ?? '#6366f1' }};">
                    <i class="{{ $category->icon ?? 'fas fa-newspaper' }}"></i>
                </span>
                <h4 class="section-title mb-0">{{ $category->name }}</h4>
            </div>
            <a href="{{ route('news.index', ['category' => $category->slug]) }}" class="see-all-link">
                {{ __('frontend.See All') }} <i class="fas fa-chevron-right ml-1"></i>
            </a>
        </div>

        <div class="row">
            <!-- Featured Article -->
            <div class="col-lg-5 mb-4 mb-lg-0">
                @if($category->news->count() > 0)
                @php $mainNews = $category->news->first(); @endphp
                <article class="category-featured-card h-100">
                    <a href="{{ route('news.show', $mainNews->slug) }}">
                        <div class="card-image">
                            <img src="{{ asset($mainNews->image) }}" alt="{{ $mainNews->title }}">
                            <div class="card-overlay"></div>
                            <div class="card-content">
                                <span class="card-label">{{ $category->name }}</span>
                                <h3 class="card-title">{{ Str::limit($mainNews->title, 80) }}</h3>
                                <p class="card-excerpt d-none d-md-block">{{ Str::limit(strip_tags($mainNews->content), 120) }}</p>
                                <div class="card-meta">
                                    <span><i class="far fa-clock mr-1"></i>{{ $mainNews->created_at->diffForHumans() }}</span>
                                    <span><i class="far fa-eye mr-1"></i>{{ number_format($mainNews->views ?? 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </article>
                @endif
            </div>

            <!-- News Grid -->
            <div class="col-lg-7">
                <div class="row">
                    @foreach($category->news->skip(1)->take(4) as $news)
                    <div class="col-md-6 mb-4">
                        <article class="category-news-card">
                            <a href="{{ route('news.show', $news->slug) }}">
                                <div class="card-thumb">
                                    <img src="{{ asset($news->image) }}" alt="{{ $news->title }}">
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">{{ Str::limit($news->title, 60) }}</h5>
                                    <div class="card-meta">
                                        <span class="meta-author">{{ $news->author->name ?? 'Admin' }}</span>
                                        <span class="meta-date">{{ $news->created_at->format('M d') }}</span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endforeach

<style>
/* Category Section */
.category-section {
    border-bottom: 1px solid #e5e7eb;
}

.category-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 14px;
    margin-right: 12px;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
}

.see-all-link {
    color: var(--colorPrimary);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.see-all-link:hover {
    color: var(--colorPrimary);
    opacity: 0.8;
}

/* Category Featured Card */
.category-featured-card {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.category-featured-card a {
    text-decoration: none;
    color: inherit;
}

.category-featured-card .card-image {
    position: relative;
    height: 380px;
}

.category-featured-card .card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.category-featured-card .card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.3) 50%, transparent 100%);
}

.category-featured-card .card-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 24px;
    color: #fff;
}

.category-featured-card .card-label {
    display: inline-block;
    background: var(--colorPrimary);
    padding: 4px 10px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-radius: 3px;
    margin-bottom: 12px;
}

.category-featured-card .card-title {
    font-size: 22px;
    font-weight: 700;
    line-height: 1.3;
    margin-bottom: 10px;
}

.category-featured-card .card-excerpt {
    font-size: 14px;
    opacity: 0.9;
    line-height: 1.5;
    margin-bottom: 12px;
}

.category-featured-card .card-meta {
    font-size: 12px;
    opacity: 0.8;
    display: flex;
    gap: 16px;
}

/* Category News Card */
.category-news-card {
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
}

.category-news-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.category-news-card a {
    text-decoration: none;
    color: inherit;
}

.category-news-card .card-thumb {
    height: 140px;
    overflow: hidden;
}

.category-news-card .card-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.category-news-card:hover .card-thumb img {
    transform: scale(1.08);
}

.category-news-card .card-body {
    padding: 14px;
}

.category-news-card .card-title {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.4;
    margin-bottom: 10px;
    color: #1e293b;
}

.category-news-card .card-meta {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #94a3b8;
}
</style>
