<!-- Aggregated News from World Sources (Reuters/AP Style) -->
<section class="world-news-section py-5 bg-dark text-white">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">
                    <i class="fas fa-globe mr-2"></i>
                    {{ __('frontend.World News') }}
                </h4>
                <p class="text-muted mb-0 small">{{ __('frontend.Aggregated from trusted sources worldwide') }}</p>
            </div>
            <div class="source-filters d-none d-md-flex">
                <button class="source-filter-btn active" data-source="all">All</button>
                @foreach($newsSources ?? [] as $source)
                <button class="source-filter-btn" data-source="{{ $source->slug }}">{{ $source->name }}</button>
                @endforeach
            </div>
        </div>

        <div class="row" id="aggregated-news-container">
            @foreach($aggregatedNews ?? [] as $news)
            <div class="col-lg-4 col-md-6 mb-4 news-item" data-source="{{ $news->source->slug ?? 'unknown' }}">
                <article class="world-news-card">
                    <a href="{{ $news->original_url }}" target="_blank" rel="noopener">
                        <div class="card-header-info">
                            @if($news->source && $news->source->logo_url)
                            <img src="{{ $news->source->logo_url }}" alt="{{ $news->source->name }}" class="source-logo">
                            @endif
                            <span class="source-name">{{ $news->source->name ?? 'Unknown Source' }}</span>
                            <span class="publish-time">{{ $news->published_at->diffForHumans() }}</span>
                        </div>
                        @if($news->image)
                        <div class="card-image">
                            <img src="{{ $news->image }}" alt="{{ $news->title }}">
                            @if($news->is_breaking)
                            <span class="breaking-tag">Breaking</span>
                            @endif
                        </div>
                        @endif
                        <div class="card-content">
                            <h5 class="card-title">{{ Str::limit($news->title, 80) }}</h5>
                            <p class="card-excerpt">{{ Str::limit(strip_tags($news->content), 100) }}</p>
                            <div class="card-footer-info">
                                @if($news->category)
                                <span class="category-tag">{{ $news->category->name }}</span>
                                @endif
                                <span class="read-more">
                                    Read Full Story <i class="fas fa-external-link-alt ml-1"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </article>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('news.index', ['source' => 'aggregated']) }}" class="btn btn-outline-light btn-lg">
                <i class="fas fa-globe mr-2"></i>{{ __('frontend.Explore More World News') }}
            </a>
        </div>
    </div>
</section>

<style>
.world-news-section {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}

/* Source Filters */
.source-filters {
    gap: 8px;
}

.source-filter-btn {
    background: rgba(255,255,255,0.1);
    border: none;
    color: #94a3b8;
    padding: 6px 14px;
    font-size: 12px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.2s;
}

.source-filter-btn:hover,
.source-filter-btn.active {
    background: var(--colorPrimary);
    color: #fff;
}

/* World News Card */
.world-news-card {
    background: rgba(255,255,255,0.05);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
    border: 1px solid rgba(255,255,255,0.1);
}

.world-news-card:hover {
    background: rgba(255,255,255,0.08);
    transform: translateY(-4px);
    border-color: rgba(255,255,255,0.2);
}

.world-news-card a {
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.card-header-info {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.card-header-info .source-logo {
    width: 20px;
    height: 20px;
    object-fit: contain;
    margin-right: 8px;
}

.card-header-info .source-name {
    font-size: 12px;
    font-weight: 600;
    color: #e2e8f0;
    flex: 1;
}

.card-header-info .publish-time {
    font-size: 11px;
    color: #64748b;
}

.world-news-card .card-image {
    position: relative;
    height: 160px;
    overflow: hidden;
}

.world-news-card .card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.world-news-card .breaking-tag {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #dc3545;
    color: #fff;
    padding: 3px 8px;
    font-size: 10px;
    font-weight: 600;
    border-radius: 3px;
}

.world-news-card .card-content {
    padding: 16px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.world-news-card .card-title {
    font-size: 15px;
    font-weight: 600;
    line-height: 1.4;
    margin-bottom: 8px;
    color: #f1f5f9;
}

.world-news-card .card-excerpt {
    font-size: 13px;
    color: #94a3b8;
    line-height: 1.5;
    flex: 1;
}

.card-footer-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.category-tag {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--colorPrimary);
    letter-spacing: 0.5px;
}

.read-more {
    font-size: 11px;
    color: #64748b;
    transition: color 0.2s;
}

.world-news-card:hover .read-more {
    color: var(--colorPrimary);
}
</style>

<script>
document.querySelectorAll('.source-filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.source-filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const source = this.dataset.source;
        document.querySelectorAll('.news-item').forEach(item => {
            if (source === 'all' || item.dataset.source === source) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>
