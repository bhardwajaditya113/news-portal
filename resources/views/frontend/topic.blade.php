@extends('frontend.layouts.master')

@section('content')
<!-- Topic Header -->
<section class="topic-header py-5" style="background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-3">
                        <li class="breadcrumb-item"><a href="/" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('news') }}" class="text-white-50">News</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">#{{ ucfirst($topic) }}</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center mb-3">
                    <span class="topic-badge">#{{ ucfirst($topic) }}</span>
                    @if($topicInfo && $topicInfo->trend_velocity > 0)
                        <span class="trend-indicator ml-3">
                            <i class="fas fa-arrow-up"></i> Trending
                        </span>
                    @endif
                </div>
                <h1 class="text-white mb-3 font-weight-bold">{{ ucfirst($topic) }}</h1>
                <p class="text-white-50 mb-0">
                    @if($topicInfo)
                        {{ number_format($topicInfo->news_count) }} articles • {{ number_format($topicInfo->views_count ?? 0) }} views
                    @else
                        {{ $aggregatedNews->total() + $news->total() }} articles found
                    @endif
                </p>
            </div>
            <div class="col-lg-4 text-lg-right mt-4 mt-lg-0">
                <div class="topic-stats">
                    <div class="stat-item">
                        <span class="stat-value">{{ $aggregatedNews->total() }}</span>
                        <span class="stat-label">Global News</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ $news->total() }}</span>
                        <span class="stat-label">Local News</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="topic-content py-5">
    <div class="container">
        <div class="row">
            <!-- News Grid -->
            <div class="col-lg-8">
                <!-- Tabs -->
                <ul class="nav nav-pills mb-4" id="topicTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="all-tab" data-toggle="pill" href="#all-news" role="tab">
                            All News <span class="badge badge-primary ml-1">{{ $aggregatedNews->total() + $news->total() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="global-tab" data-toggle="pill" href="#global-news" role="tab">
                            Global <span class="badge badge-secondary ml-1">{{ $aggregatedNews->total() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="local-tab" data-toggle="pill" href="#local-news" role="tab">
                            Local <span class="badge badge-secondary ml-1">{{ $news->total() }}</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="topicTabContent">
                    <!-- All News Tab -->
                    <div class="tab-pane fade show active" id="all-news" role="tabpanel">
                        @php
                            $allNews = collect();
                            foreach($aggregatedNews as $item) {
                                $item->type = 'aggregated';
                                $allNews->push($item);
                            }
                            foreach($news as $item) {
                                $item->type = 'local';
                                $allNews->push($item);
                            }
                            $allNews = $allNews->sortByDesc(function($item) {
                                return $item->type === 'aggregated' ? $item->published_at : $item->created_at;
                            });
                        @endphp

                        @forelse($allNews as $item)
                            @if($item->type === 'aggregated')
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
                                                        <span class="source-badge">{{ $item->source->name ?? 'News' }}</span>
                                                        @if($item->is_breaking)
                                                            <span class="badge badge-danger ml-2">BREAKING</span>
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
                                                        <span class="badge badge-light mr-2">
                                                            <i class="fas fa-globe mr-1"></i>{{ $item->country }}
                                                        </span>
                                                        <a href="{{ $item->original_url }}" target="_blank" class="btn btn-sm btn-outline-primary ml-auto">
                                                            Read Full Story <i class="fas fa-external-link-alt ml-1"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="news-card mb-4">
                                    <div class="card border-0 shadow-sm overflow-hidden">
                                        <div class="row no-gutters">
                                            <div class="col-md-4">
                                                @if($item->image)
                                                    <img src="{{ asset($item->image) }}" class="news-card-img" alt="{{ $item->title }}">
                                                @else
                                                    <div class="news-card-img bg-gradient d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-newspaper fa-3x text-white opacity-50"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-md-8">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <span class="source-badge local">Local News</span>
                                                        @if($item->is_breaking_news)
                                                            <span class="badge badge-danger ml-2">BREAKING</span>
                                                        @endif
                                                        <span class="text-muted small ml-auto">
                                                            <i class="far fa-clock mr-1"></i>{{ $item->created_at->diffForHumans() }}
                                                        </span>
                                                    </div>
                                                    <h5 class="card-title mb-2">
                                                        <a href="{{ route('news-details', $item->slug) }}" class="text-dark">
                                                            {{ $item->title }}
                                                        </a>
                                                    </h5>
                                                    <p class="card-text text-muted mb-3">{{ Str::limit(strip_tags($item->content), 150) }}</p>
                                                    <div class="d-flex align-items-center">
                                                        @if($item->category)
                                                            <span class="badge badge-light mr-2">{{ $item->category->name }}</span>
                                                        @endif
                                                        <a href="{{ route('news-details', $item->slug) }}" class="btn btn-sm btn-outline-primary ml-auto">
                                                            Read More <i class="fas fa-arrow-right ml-1"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="text-center py-5">
                                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No news found for "{{ $topic }}"</h5>
                                <p class="text-muted">Try searching for a different topic</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Global News Tab -->
                    <div class="tab-pane fade" id="global-news" role="tabpanel">
                        @forelse($aggregatedNews as $item)
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
                                                    <span class="source-badge">{{ $item->source->name ?? 'News' }}</span>
                                                    @if($item->is_breaking)
                                                        <span class="badge badge-danger ml-2">BREAKING</span>
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
                                                <a href="{{ $item->original_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    Read Full Story <i class="fas fa-external-link-alt ml-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <p class="text-muted">No global news found for this topic</p>
                            </div>
                        @endforelse
                        
                        <div class="d-flex justify-content-center mt-4">
                            {{ $aggregatedNews->links() }}
                        </div>
                    </div>

                    <!-- Local News Tab -->
                    <div class="tab-pane fade" id="local-news" role="tabpanel">
                        @forelse($news as $item)
                            <div class="news-card mb-4">
                                <div class="card border-0 shadow-sm overflow-hidden">
                                    <div class="row no-gutters">
                                        <div class="col-md-4">
                                            @if($item->image)
                                                <img src="{{ asset($item->image) }}" class="news-card-img" alt="{{ $item->title }}">
                                            @else
                                                <div class="news-card-img bg-gradient d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-newspaper fa-3x text-white opacity-50"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="source-badge local">Local News</span>
                                                    <span class="text-muted small ml-auto">
                                                        <i class="far fa-clock mr-1"></i>{{ $item->created_at->diffForHumans() }}
                                                    </span>
                                                </div>
                                                <h5 class="card-title mb-2">
                                                    <a href="{{ route('news-details', $item->slug) }}" class="text-dark">
                                                        {{ $item->title }}
                                                    </a>
                                                </h5>
                                                <p class="card-text text-muted mb-3">{{ Str::limit(strip_tags($item->content), 150) }}</p>
                                                <a href="{{ route('news-details', $item->slug) }}" class="btn btn-sm btn-outline-primary">
                                                    Read More <i class="fas fa-arrow-right ml-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <p class="text-muted">No local news found for this topic</p>
                            </div>
                        @endforelse
                        
                        <div class="d-flex justify-content-center mt-4">
                            {{ $news->links() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Related Topics -->
                @if(isset($relatedTopics) && $relatedTopics->count() > 0)
                <div class="sidebar-widget mb-4">
                    <div class="widget-header">
                        <h6 class="mb-0 font-weight-bold">
                            <i class="fas fa-fire text-danger mr-2"></i>Related Topics
                        </h6>
                    </div>
                    <div class="widget-body">
                        <div class="related-topics-list">
                            @foreach($relatedTopics as $index => $relatedTopic)
                                <a href="{{ route('news.topic', ['topic' => $relatedTopic->topic]) }}" class="related-topic-item">
                                    <span class="topic-rank {{ $index < 3 ? 'top' : '' }}">{{ $index + 1 }}</span>
                                    <div class="topic-info">
                                        <span class="topic-name">#{{ $relatedTopic->topic }}</span>
                                        <span class="topic-count">{{ number_format($relatedTopic->news_count) }} articles</span>
                                    </div>
                                    <i class="fas fa-chevron-right text-muted"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Topic Info -->
                @if($topicInfo)
                <div class="sidebar-widget mb-4">
                    <div class="widget-header">
                        <h6 class="mb-0 font-weight-bold">
                            <i class="fas fa-chart-line text-primary mr-2"></i>Topic Stats
                        </h6>
                    </div>
                    <div class="widget-body">
                        <div class="topic-stats-grid">
                            <div class="stat-box">
                                <i class="fas fa-newspaper text-primary"></i>
                                <span class="value">{{ number_format($topicInfo->news_count) }}</span>
                                <span class="label">Articles</span>
                            </div>
                            <div class="stat-box">
                                <i class="fas fa-eye text-info"></i>
                                <span class="value">{{ number_format($topicInfo->views_count ?? 0) }}</span>
                                <span class="label">Views</span>
                            </div>
                            <div class="stat-box">
                                <i class="fas fa-chart-line {{ $topicInfo->trend_velocity > 0 ? 'text-success' : 'text-danger' }}"></i>
                                <span class="value">{{ $topicInfo->trend_velocity > 0 ? '+' : '' }}{{ $topicInfo->trend_velocity }}%</span>
                                <span class="label">Trend</span>
                            </div>
                            <div class="stat-box">
                                <i class="fas fa-star text-warning"></i>
                                <span class="value">{{ number_format($topicInfo->engagement_score, 1) }}</span>
                                <span class="label">Score</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Newsletter -->
                <div class="sidebar-widget newsletter-widget">
                    <div class="widget-body text-center py-4">
                        <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                        <h6 class="font-weight-bold">Stay Updated</h6>
                        <p class="small text-muted mb-3">Get the latest news on "{{ $topic }}" delivered to your inbox</p>
                        <form action="{{ route('subscribe-newsletter') }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="email" name="email" class="form-control" placeholder="Your email">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Topic Header */
.topic-header {
    position: relative;
    overflow: hidden;
}

.topic-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 8px 20px;
    border-radius: 25px;
    font-weight: 700;
    font-size: 18px;
}

.trend-indicator {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
}

.topic-stats {
    display: flex;
    gap: 30px;
}

.stat-item {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 28px;
    font-weight: 700;
    color: #fff;
}

.stat-label {
    color: rgba(255,255,255,0.6);
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
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

.source-badge {
    background: #e0f2fe;
    color: #0369a1;
    padding: 3px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.source-badge.local {
    background: #dcfce7;
    color: #166534;
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
    padding: 15px 20px;
}

.related-topic-item {
    display: flex;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s;
}

.related-topic-item:last-child {
    border-bottom: none;
}

.related-topic-item:hover {
    background: #f8fafc;
    margin: 0 -20px;
    padding-left: 20px;
    padding-right: 20px;
}

.topic-rank {
    width: 28px;
    height: 28px;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
    color: #64748b;
    margin-right: 12px;
}

.topic-rank.top {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #fff;
}

.topic-info {
    flex: 1;
}

.topic-name {
    display: block;
    font-weight: 600;
    color: #1e293b;
}

.topic-count {
    font-size: 11px;
    color: #94a3b8;
}

/* Topic Stats Grid */
.topic-stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.stat-box {
    text-align: center;
    padding: 15px;
    background: #f8fafc;
    border-radius: 10px;
}

.stat-box i {
    font-size: 20px;
    margin-bottom: 8px;
}

.stat-box .value {
    display: block;
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
}

.stat-box .label {
    font-size: 11px;
    color: #94a3b8;
    text-transform: uppercase;
}

/* Newsletter Widget */
.newsletter-widget {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

/* Nav Pills */
.nav-pills .nav-link {
    color: #64748b;
    font-weight: 500;
    border-radius: 25px;
    padding: 8px 20px;
}

.nav-pills .nav-link.active {
    background: var(--colorPrimary);
}

@media (max-width: 768px) {
    .topic-stats {
        justify-content: center;
        margin-top: 20px;
    }
    
    .news-card-img {
        height: 150px;
    }
}
</style>
@endsection
