@extends('frontend.layouts.master')

@section('content')
<!-- Source Header -->
<section class="source-header py-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-3">
                        <li class="breadcrumb-item"><a href="/" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('news') }}" class="text-white-50">News</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">{{ $source->name }}</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center mb-4">
                    @if($source->logo)
                        <img src="{{ $source->logo }}" alt="{{ $source->name }}" class="source-logo-large mr-4">
                    @else
                        <div class="source-logo-placeholder-large mr-4">{{ substr($source->name, 0, 2) }}</div>
                    @endif
                    <div>
                        <h1 class="text-white mb-2 font-weight-bold">{{ $source->name }}</h1>
                        <div class="source-meta">
                            <span class="meta-item">
                                <i class="fas fa-globe mr-1"></i>{{ $source->country }}
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-language mr-1"></i>{{ strtoupper($source->language) }}
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-shield-alt mr-1"></i>Credibility: {{ number_format($source->credibility_score * 100) }}%
                            </span>
                        </div>
                    </div>
                </div>
                <a href="{{ $source->website_url }}" target="_blank" class="btn btn-outline-light">
                    <i class="fas fa-external-link-alt mr-2"></i>Visit {{ $source->name }}
                </a>
            </div>
            <div class="col-lg-4 text-lg-right mt-4 mt-lg-0">
                <div class="source-stats-header">
                    <div class="stat-circle">
                        <span class="stat-number">{{ $news->total() }}</span>
                        <span class="stat-text">Articles</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="source-content py-5">
    <div class="container">
        <div class="row">
            <!-- News Grid -->
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="font-weight-bold mb-0">
                        <i class="fas fa-newspaper text-primary mr-2"></i>Latest from {{ $source->name }}
                    </h5>
                    <div class="view-options">
                        <button class="btn btn-sm btn-outline-secondary active" data-view="list">
                            <i class="fas fa-list"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" data-view="grid">
                            <i class="fas fa-th"></i>
                        </button>
                    </div>
                </div>

                <div class="news-list" id="news-container">
                    @forelse($news as $item)
                        <div class="news-card mb-4">
                            <div class="card border-0 shadow-sm overflow-hidden">
                                <div class="row no-gutters">
                                    <div class="col-md-4">
                                        @if($item->image)
                                            <img src="{{ $item->image }}" class="news-card-img" alt="{{ $item->title }}">
                                        @else
                                            <div class="news-card-img bg-gradient d-flex align-items-center justify-content-center">
                                                <i class="fas fa-newspaper fa-3x text-white opacity-50"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                @if($item->is_breaking)
                                                    <span class="badge badge-danger mr-2">BREAKING</span>
                                                @endif
                                                @if($item->is_trending)
                                                    <span class="badge badge-warning mr-2">TRENDING</span>
                                                @endif
                                                <span class="text-muted small ml-auto">
                                                    <i class="far fa-clock mr-1"></i>{{ $item->published_at->diffForHumans() }}
                                                </span>
                                            </div>
                                            <h5 class="card-title mb-2">
                                                <a href="{{ $item->original_url }}" target="_blank" class="text-dark">
                                                    {{ $item->title }}
                                                </a>
                                            </h5>
                                            <p class="card-text text-muted mb-3">{{ Str::limit($item->summary, 150) }}</p>
                                            <div class="d-flex align-items-center">
                                                @if($item->keywords && is_array($item->keywords))
                                                    @foreach(array_slice($item->keywords, 0, 3) as $keyword)
                                                        <a href="{{ route('news.topic', ['topic' => $keyword]) }}" class="badge badge-light mr-1">#{{ $keyword }}</a>
                                                    @endforeach
                                                @endif
                                                <a href="{{ $item->original_url }}" target="_blank" class="btn btn-sm btn-primary ml-auto">
                                                    Read <i class="fas fa-external-link-alt ml-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-newspaper fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No news articles found</h5>
                            <p class="text-muted">Check back later for updates from {{ $source->name }}</p>
                        </div>
                    @endforelse
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $news->links() }}
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Source Info -->
                <div class="sidebar-widget mb-4">
                    <div class="widget-header">
                        <h6 class="mb-0 font-weight-bold">
                            <i class="fas fa-info-circle text-primary mr-2"></i>About {{ $source->name }}
                        </h6>
                    </div>
                    <div class="widget-body">
                        <div class="source-info-grid">
                            <div class="info-item">
                                <span class="info-label">Country</span>
                                <span class="info-value">{{ $source->country }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Language</span>
                                <span class="info-value">{{ strtoupper($source->language) }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Credibility Score</span>
                                <div class="credibility-bar">
                                    <div class="credibility-fill" style="width: {{ $source->credibility_score * 100 }}%"></div>
                                </div>
                                <span class="info-value">{{ number_format($source->credibility_score * 100) }}%</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Priority Rank</span>
                                <span class="info-value">{{ $source->priority }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Last Updated</span>
                                <span class="info-value">{{ $source->last_fetched_at ? $source->last_fetched_at->diffForHumans() : 'N/A' }}</span>
                            </div>
                        </div>
                        <a href="{{ $source->website_url }}" target="_blank" class="btn btn-block btn-outline-primary mt-3">
                            <i class="fas fa-external-link-alt mr-2"></i>Visit Website
                        </a>
                    </div>
                </div>

                <!-- Other Sources -->
                @if(isset($otherSources) && $otherSources->count() > 0)
                <div class="sidebar-widget mb-4">
                    <div class="widget-header">
                        <h6 class="mb-0 font-weight-bold">
                            <i class="fas fa-broadcast-tower text-info mr-2"></i>Other Sources
                        </h6>
                    </div>
                    <div class="widget-body p-0">
                        @foreach($otherSources as $otherSource)
                            <a href="{{ route('news.source', ['sourceSlug' => $otherSource->slug]) }}" class="other-source-item">
                                @if($otherSource->logo)
                                    <img src="{{ $otherSource->logo }}" alt="{{ $otherSource->name }}" class="source-mini-logo">
                                @else
                                    <div class="source-mini-placeholder">{{ substr($otherSource->name, 0, 2) }}</div>
                                @endif
                                <div class="source-info">
                                    <span class="source-name">{{ $otherSource->name }}</span>
                                    <span class="source-country">{{ $otherSource->country }}</span>
                                </div>
                                <span class="credibility-badge">{{ number_format($otherSource->credibility_score * 100) }}%</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- RSS Feed Info -->
                <div class="sidebar-widget">
                    <div class="widget-header">
                        <h6 class="mb-0 font-weight-bold">
                            <i class="fas fa-rss text-warning mr-2"></i>RSS Feed
                        </h6>
                    </div>
                    <div class="widget-body">
                        <p class="small text-muted mb-2">Subscribe to {{ $source->name }}'s RSS feed:</p>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm" value="{{ $source->rss_feed_url }}" readonly id="rss-url">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-outline-secondary" onclick="copyRssUrl()">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Source Header */
.source-header {
    position: relative;
}

.source-logo-large {
    width: 80px;
    height: 80px;
    object-fit: contain;
    background: #fff;
    border-radius: 12px;
    padding: 10px;
}

.source-logo-placeholder-large {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #fff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 28px;
}

.source-meta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.meta-item {
    color: rgba(255,255,255,0.7);
    font-size: 14px;
}

.source-stats-header .stat-circle {
    width: 120px;
    height: 120px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-left: auto;
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #fff;
}

.stat-text {
    color: rgba(255,255,255,0.6);
    font-size: 12px;
    text-transform: uppercase;
}

/* News Cards */
.news-card-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.bg-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.news-card .card {
    transition: all 0.3s ease;
}

.news-card .card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
}

.news-card .card-title a:hover {
    color: var(--colorPrimary) !important;
}

/* Sidebar */
.sidebar-widget {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    overflow: hidden;
}

.widget-header {
    padding: 15px 20px;
    border-bottom: 1px solid #f1f5f9;
}

.widget-body {
    padding: 20px;
}

.source-info-grid .info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
}

.source-info-grid .info-item:last-child {
    border-bottom: none;
}

.info-label {
    color: #64748b;
    font-size: 13px;
}

.info-value {
    font-weight: 600;
    color: #1e293b;
}

.credibility-bar {
    flex: 1;
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    margin: 0 10px;
    overflow: hidden;
}

.credibility-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    border-radius: 3px;
}

/* Other Sources */
.other-source-item {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #f1f5f9;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s;
}

.other-source-item:last-child {
    border-bottom: none;
}

.other-source-item:hover {
    background: #f8fafc;
}

.source-mini-logo {
    width: 36px;
    height: 36px;
    object-fit: contain;
    border-radius: 8px;
    margin-right: 12px;
}

.source-mini-placeholder {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #fff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
    margin-right: 12px;
}

.source-info {
    flex: 1;
}

.source-name {
    display: block;
    font-weight: 600;
    color: #1e293b;
    font-size: 14px;
}

.source-country {
    font-size: 11px;
    color: #94a3b8;
}

.credibility-badge {
    background: #dcfce7;
    color: #166534;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: 600;
}

/* View Options */
.view-options .btn.active {
    background: var(--colorPrimary);
    color: #fff;
    border-color: var(--colorPrimary);
}

@media (max-width: 768px) {
    .source-logo-large,
    .source-logo-placeholder-large {
        width: 60px;
        height: 60px;
        font-size: 20px;
    }
    
    .source-meta {
        gap: 10px;
    }
    
    .news-card-img {
        height: 150px;
    }
}
</style>

<script>
function copyRssUrl() {
    const input = document.getElementById('rss-url');
    input.select();
    document.execCommand('copy');
    alert('RSS URL copied to clipboard!');
}
</script>
@endsection
