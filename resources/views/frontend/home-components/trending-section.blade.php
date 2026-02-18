<!-- Trending Section (Google News / Reddit Style) -->
<section class="trending-section py-4 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-header d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-fire text-danger mr-2"></i>
                        <span class="font-weight-bold">{{ __('frontend.Trending Now') }}</span>
                    </h5>
                    <a href="{{ route('news', ['filter' => 'trending']) }}" class="text-primary small">
                        {{ __('frontend.See All') }} <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="trending-topics-slider">
                    @foreach($trendingTopics ?? [] as $index => $topic)
                    <a href="{{ route('news.topic', ['topic' => $topic->topic]) }}" class="trending-topic-item">
                        <span class="topic-rank {{ $index < 3 ? 'top-3' : '' }}">{{ $index + 1 }}</span>
                        <div class="topic-content">
                            <span class="topic-name">#{{ $topic->topic }}</span>
                            <span class="topic-count">{{ number_format($topic->news_count) }} articles</span>
                        </div>
                        <span class="topic-trend {{ $topic->trend_velocity > 0 ? 'up' : 'down' }}">
                            <i class="fas fa-arrow-{{ $topic->trend_velocity > 0 ? 'up' : 'down' }}"></i>
                        </span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.trending-section {
    border-bottom: 1px solid #e5e7eb;
}

.trending-topics-slider {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 10px;
    scrollbar-width: thin;
}

.trending-topics-slider::-webkit-scrollbar {
    height: 4px;
}

.trending-topics-slider::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.trending-topic-item {
    display: flex;
    align-items: center;
    background: #fff;
    padding: 10px 16px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    text-decoration: none;
    color: inherit;
    min-width: 200px;
    transition: all 0.2s ease;
}

.trending-topic-item:hover {
    border-color: var(--colorPrimary);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}

.topic-rank {
    width: 28px;
    height: 28px;
    background: #f1f5f9;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
    color: #64748b;
    margin-right: 12px;
}

.topic-rank.top-3 {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #fff;
}

.topic-content {
    flex: 1;
}

.topic-name {
    display: block;
    font-weight: 600;
    font-size: 14px;
    color: #1e293b;
    margin-bottom: 2px;
}

.topic-count {
    font-size: 11px;
    color: #94a3b8;
}

.topic-trend {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
}

.topic-trend.up {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.topic-trend.down {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}
</style>
