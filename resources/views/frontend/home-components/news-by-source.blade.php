<!-- News By Source Section (Google News / Apple News Style) -->
<section class="news-by-source-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-header mb-4">
                    <h4 class="font-weight-bold mb-1">
                        <i class="fas fa-globe text-info mr-2"></i>
                        {{ __('News From Top Sources') }}
                    </h4>
                    <p class="text-muted small mb-0">Curated news from the world's most trusted publications</p>
                </div>
            </div>
        </div>
        
        @if(isset($newsBySource) && count($newsBySource) > 0)
            <div class="sources-news-grid">
                @foreach($newsBySource as $slug => $data)
                    <div class="source-news-card mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
                                <div class="d-flex align-items-center">
                                    @if($data['source']->logo)
                                        <img src="{{ $data['source']->logo }}" alt="{{ $data['source']->name }}" class="source-logo mr-2">
                                    @else
                                        <div class="source-logo-badge mr-2">{{ substr($data['source']->name, 0, 2) }}</div>
                                    @endif
                                    <div>
                                        <h6 class="mb-0 font-weight-bold">{{ $data['source']->name }}</h6>
                                        <small class="text-muted">{{ $data['source']->country }} • Credibility: {{ number_format($data['source']->credibility_score * 100) }}%</small>
                                    </div>
                                </div>
                                <a href="{{ $data['source']->website_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    Visit <i class="fas fa-external-link-alt ml-1"></i>
                                </a>
                            </div>
                            <div class="card-body p-0">
                                @if($data['news']->count() > 0)
                                    @foreach($data['news'] as $news)
                                        <a href="{{ $news->original_url }}" target="_blank" class="source-news-item d-flex p-3 {{ !$loop->last ? 'border-bottom' : '' }} text-decoration-none">
                                            <div class="flex-grow-1 pr-3">
                                                <h6 class="mb-1 text-dark font-weight-medium">{{ Str::limit($news->title, 80) }}</h6>
                                                <div class="d-flex align-items-center">
                                                    @if($news->is_breaking)
                                                        <span class="badge badge-danger badge-sm mr-2">BREAKING</span>
                                                    @endif
                                                    <small class="text-muted">{{ $news->published_at->diffForHumans() }}</small>
                                                </div>
                                            </div>
                                            @if($news->image)
                                                <img src="{{ $news->image }}" alt="" class="source-news-thumb">
                                            @endif
                                        </a>
                                    @endforeach
                                @else
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-newspaper fa-2x mb-2 opacity-50"></i>
                                        <p class="mb-0 small">No recent news</p>
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer bg-light text-center py-2">
                                <a href="{{ route('news.source', ['sourceSlug' => $slug]) }}" class="text-primary small font-weight-bold">
                                    View all from {{ $data['source']->name }} <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-spinner fa-spin fa-3x text-muted mb-3"></i>
                <p class="text-muted">Loading news sources...</p>
            </div>
        @endif
    </div>
</section>

<style>
.news-by-source-section {
    background: #fff;
}

.sources-news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
    gap: 24px;
}

@media (max-width: 768px) {
    .sources-news-grid {
        grid-template-columns: 1fr;
    }
}

.source-logo {
    width: 36px;
    height: 36px;
    object-fit: contain;
    border-radius: 8px;
}

.source-logo-badge {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #fff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
}

.source-news-item:hover {
    background: #f8fafc;
}

.source-news-item h6:hover {
    color: var(--colorPrimary) !important;
}

.source-news-thumb {
    width: 80px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
    flex-shrink: 0;
}

.badge-sm {
    font-size: 9px;
    padding: 2px 5px;
}

.font-weight-medium {
    font-weight: 500;
}

.source-news-card .card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.source-news-card .card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}
</style>
