<!-- Latest Aggregated News Section (Google News / Reuters Style) -->
<section class="aggregated-news-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-header d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 font-weight-bold">
                            <i class="fas fa-newspaper text-primary mr-2"></i>
                            {{ __('Latest Headlines') }}
                        </h4>
                        <p class="text-muted mb-0 small">Real-time news from trusted sources worldwide</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="live-indicator mr-3">
                            <span class="live-dot"></span>
                            <span class="text-success small font-weight-bold">LIVE</span>
                        </span>
                        <span class="text-muted small" id="last-updated">Updated {{ now()->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Main News Column -->
            <div class="col-lg-8">
                <div class="news-grid">
                    @if(isset($latestAggregated) && $latestAggregated->count() > 0)
                        @foreach($latestAggregated->take(12) as $index => $news)
                            @if($index == 0)
                                <!-- Featured Article -->
                                <div class="featured-article mb-4">
                                    <div class="card border-0 shadow-sm overflow-hidden">
                                        @if($news->image)
                                            <img src="{{ $news->image }}" class="card-img-top featured-img" alt="{{ $news->title }}">
                                        @else
                                            <div class="card-img-top featured-img bg-gradient-primary d-flex align-items-center justify-content-center">
                                                <i class="fas fa-newspaper fa-4x text-white opacity-50"></i>
                                            </div>
                                        @endif
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                @if($news->source)
                                                    <span class="source-badge">{{ $news->source->name }}</span>
                                                @endif
                                                @if($news->is_breaking)
                                                    <span class="badge badge-danger ml-2">BREAKING</span>
                                                @endif
                                                <span class="text-muted small ml-auto">{{ $news->published_at->diffForHumans() }}</span>
                                            </div>
                                            <h5 class="card-title font-weight-bold">
                                                <a href="{{ $news->original_url }}" target="_blank" class="text-dark stretched-link">
                                                    {{ $news->title }}
                                                </a>
                                            </h5>
                                            <p class="card-text text-muted">{{ Str::limit($news->summary, 200) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Regular Article -->
                                <div class="news-item mb-3">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="row no-gutters">
                                            <div class="col-4">
                                                @if($news->image)
                                                    <img src="{{ $news->image }}" class="news-thumb" alt="{{ $news->title }}">
                                                @else
                                                    <div class="news-thumb bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-8">
                                                <div class="card-body py-2 px-3">
                                                    <div class="d-flex align-items-center mb-1">
                                                        @if($news->source)
                                                            <small class="text-primary font-weight-bold">{{ $news->source->name }}</small>
                                                        @endif
                                                        @if($news->is_breaking)
                                                            <span class="badge badge-danger badge-sm ml-1">BREAKING</span>
                                                        @endif
                                                    </div>
                                                    <h6 class="card-title mb-1">
                                                        <a href="{{ $news->original_url }}" target="_blank" class="text-dark">
                                                            {{ Str::limit($news->title, 80) }}
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">{{ $news->published_at->diffForHumans() }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-spinner fa-spin fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Loading latest news...</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Breaking News Widget -->
                @if(isset($breakingAggregated) && $breakingAggregated->count() > 0)
                <div class="breaking-widget mb-4">
                    <div class="widget-header bg-danger text-white p-3 rounded-top">
                        <h6 class="mb-0 font-weight-bold">
                            <i class="fas fa-bolt mr-2"></i> Breaking News
                        </h6>
                    </div>
                    <div class="widget-body bg-white border rounded-bottom p-0">
                        @foreach($breakingAggregated as $news)
                            <a href="{{ $news->original_url }}" target="_blank" class="breaking-item d-block p-3 border-bottom text-decoration-none">
                                <span class="d-block text-dark font-weight-bold mb-1">{{ Str::limit($news->title, 80) }}</span>
                                <small class="text-muted">{{ $news->source->name ?? 'News' }} • {{ $news->published_at->diffForHumans() }}</small>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Trending Topics Widget -->
                @if(isset($trendingTopics) && $trendingTopics->count() > 0)
                <div class="trending-widget mb-4">
                    <div class="widget-header bg-dark text-white p-3 rounded-top">
                        <h6 class="mb-0 font-weight-bold">
                            <i class="fas fa-fire mr-2"></i> Trending Topics
                        </h6>
                    </div>
                    <div class="widget-body bg-white border rounded-bottom">
                        @foreach($trendingTopics->take(8) as $index => $topic)
                            <div class="trending-item d-flex align-items-center p-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <span class="rank-badge {{ $index < 3 ? 'top-rank' : '' }}">{{ $index + 1 }}</span>
                                <span class="topic-name flex-grow-1 ml-2">{{ $topic->topic }}</span>
                                <span class="news-count text-muted small">{{ $topic->news_count ?? 0 }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- News Sources Widget -->
                @if(isset($newsSources) && $newsSources->count() > 0)
                <div class="sources-widget">
                    <div class="widget-header bg-primary text-white p-3 rounded-top">
                        <h6 class="mb-0 font-weight-bold">
                            <i class="fas fa-broadcast-tower mr-2"></i> News Sources
                        </h6>
                    </div>
                    <div class="widget-body bg-white border rounded-bottom p-3">
                        <div class="sources-grid">
                            @foreach($newsSources->take(6) as $source)
                                <div class="source-item text-center p-2">
                                    @if($source->logo)
                                        <img src="{{ $source->logo }}" alt="{{ $source->name }}" class="source-logo mb-1">
                                    @else
                                        <div class="source-logo-placeholder mb-1">{{ substr($source->name, 0, 2) }}</div>
                                    @endif
                                    <small class="d-block text-muted">{{ Str::limit($source->name, 15) }}</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

<style>
.aggregated-news-section {
    background: #f8fafc;
}

.live-indicator {
    display: flex;
    align-items: center;
    gap: 6px;
}

.live-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.3); }
}

.featured-img {
    height: 300px;
    object-fit: cover;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, var(--colorPrimary) 0%, #1e40af 100%);
}

.source-badge {
    background: #e0f2fe;
    color: #0369a1;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.news-thumb {
    width: 100%;
    height: 100px;
    object-fit: cover;
    border-radius: 4px 0 0 4px;
}

.badge-sm {
    font-size: 9px;
    padding: 2px 4px;
}

/* Widget Styles */
.breaking-item:hover {
    background: #fef2f2;
}

.rank-badge {
    width: 24px;
    height: 24px;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
}

.rank-badge.top-rank {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #fff;
}

.sources-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}

.source-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
    border-radius: 8px;
}

.source-logo-placeholder {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #fff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    margin: 0 auto;
}

.news-item .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.news-item .card-title a:hover {
    color: var(--colorPrimary) !important;
}
</style>
