@extends('admin.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-broadcast-tower mr-2"></i>Real-Time News Feed Control Center</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Real-Time Feed</div>
        </div>
    </div>

    <!-- Master Control Panel -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h4><i class="fas fa-sliders-h mr-2"></i>Master Control</h4>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <div id="feed-status-indicator" class="mb-3">
                                <div class="status-circle {{ $status['running'] ? 'running' : 'stopped' }}">
                                    <i class="fas {{ $status['running'] ? 'fa-play' : 'fa-stop' }}"></i>
                                </div>
                                <h4 class="mt-2" id="status-text">{{ $status['running'] ? 'LIVE' : 'STOPPED' }}</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="stat-box">
                                        <h2 id="stat-sources">{{ $stats['sources_count'] ?? 0 }}</h2>
                                        <small>Active Sources</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-box">
                                        <h2 id="stat-articles">{{ $stats['total_articles'] ?? 0 }}</h2>
                                        <small>Total Articles</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-box">
                                        <h2 id="stat-breaking">{{ $stats['breaking_news'] ?? 0 }}</h2>
                                        <small>Breaking News</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <button id="btn-start" class="btn btn-success btn-lg mb-2 {{ $status['running'] ? 'd-none' : '' }}" style="width: 150px;">
                                <i class="fas fa-play mr-2"></i>START
                            </button>
                            <button id="btn-stop" class="btn btn-danger btn-lg mb-2 {{ !$status['running'] ? 'd-none' : '' }}" style="width: 150px;">
                                <i class="fas fa-stop mr-2"></i>STOP
                            </button>
                            <br>
                            <button id="btn-reset" class="btn btn-warning btn-sm">
                                <i class="fas fa-redo mr-1"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Stats Row -->
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary">
                    <i class="fas fa-sync fa-spin" id="fetch-spinner" style="display: none;"></i>
                    <i class="fas fa-database" id="fetch-icon"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Sources Processed</h4></div>
                    <div class="card-body" id="sources-processed">{{ $status['sources_processed'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Articles Fetched</h4></div>
                    <div class="card-body" id="articles-fetched">{{ $status['articles_fetched'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Current Source</h4></div>
                    <div class="card-body" id="current-source" style="font-size: 14px;">{{ $status['current_source'] ?? 'None' }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Errors</h4></div>
                    <div class="card-body" id="errors-count">{{ $status['errors'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Live Feed Log -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-stream mr-2"></i>Live Feed Activity</h4>
                    <div class="card-header-action">
                        <span class="badge badge-success" id="connection-status">
                            <i class="fas fa-circle mr-1"></i>Connected
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="feed-log" style="height: 400px; overflow-y: auto; font-family: monospace; font-size: 12px; background: #1a1a2e; color: #eee; padding: 15px;">
                        <div class="log-entry text-muted">Waiting for feed activity...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Articles Stream -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-bolt mr-2 text-warning"></i>New Articles</h4>
                    <div class="card-header-action">
                        <span class="badge badge-primary" id="new-count">0</span> new
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="new-articles" style="height: 400px; overflow-y: auto;">
                        @forelse($recentNews->take(10) as $news)
                        <div class="p-3 border-bottom article-item">
                            <div class="d-flex">
                                @if($news->image_url)
                                <img src="{{ $news->image_url }}" class="mr-2 rounded" style="width: 60px; height: 60px; object-fit: cover;" onerror="this.style.display='none'">
                                @endif
                                <div class="flex-grow-1">
                                    <a href="{{ $news->original_url }}" target="_blank" class="text-dark font-weight-bold" style="font-size: 13px; line-height: 1.3;">
                                        {{ Str::limit($news->title, 80) }}
                                    </a>
                                    <div class="mt-1">
                                        <small class="text-muted">
                                            <i class="fas fa-newspaper mr-1"></i>{{ $news->source?->name ?? 'Unknown' }}
                                            <span class="mx-1">•</span>
                                            {{ $news->fetched_at?->diffForHumans() ?? 'Just now' }}
                                        </small>
                                        @if($news->is_breaking)
                                        <span class="badge badge-danger ml-1">BREAKING</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="p-3 text-center text-muted">No articles yet</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sources Status -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-satellite-dish mr-2"></i>News Sources ({{ $sources->count() }})</h4>
                    <div class="card-header-action">
                        <a href="{{ route('admin.aggregator.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-cog mr-1"></i>Manage Sources
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($sources->take(24) as $source)
                        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-3">
                            <div class="source-card p-2 rounded border {{ $source->is_active ? 'border-success' : 'border-secondary' }}" data-source-id="{{ $source->id }}">
                                <div class="d-flex align-items-center">
                                    <div class="source-status-dot {{ $source->is_active ? 'bg-success' : 'bg-secondary' }} mr-2"></div>
                                    <div class="flex-grow-1 text-truncate">
                                        <strong style="font-size: 11px;">{{ Str::limit($source->name, 18) }}</strong>
                                        <div class="text-muted" style="font-size: 10px;">
                                            P:{{ $source->priority }} | C:{{ number_format($source->credibility_score * 100) }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="source-last-fetch text-muted mt-1" style="font-size: 9px;">
                                    {{ $source->last_fetched_at?->diffForHumans() ?? 'Never' }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($sources->count() > 24)
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.aggregator.index') }}" class="btn btn-outline-primary">
                            View All {{ $sources->count() }} Sources
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.status-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 24px;
    color: #fff;
    transition: all 0.3s ease;
}
.status-circle.running {
    background: linear-gradient(135deg, #28a745, #20c997);
    animation: pulse 2s infinite;
}
.status-circle.stopped {
    background: linear-gradient(135deg, #6c757d, #495057);
}
@keyframes pulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
    50% { transform: scale(1.05); box-shadow: 0 0 20px 10px rgba(40, 167, 69, 0.3); }
}
.stat-box {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}
.stat-box h2 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: #6777ef;
}
.source-card {
    transition: all 0.2s ease;
    background: #fff;
}
.source-card:hover {
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
.source-card.active-fetch {
    background: #d4edda;
    border-color: #28a745 !important;
}
.source-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.log-entry {
    padding: 4px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.log-entry.success { color: #28a745; }
.log-entry.error { color: #dc3545; }
.log-entry.info { color: #17a2b8; }
.log-entry.warning { color: #ffc107; }
.article-item {
    transition: background 0.3s ease;
}
.article-item.new {
    background: #fffde7;
    animation: highlight 2s ease-out;
}
@keyframes highlight {
    from { background: #fff9c4; }
    to { background: transparent; }
}
#feed-log::-webkit-scrollbar {
    width: 6px;
}
#feed-log::-webkit-scrollbar-thumb {
    background: #6777ef;
    border-radius: 3px;
}
</style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let eventSource = null;
    let newArticleCount = 0;
    let isRunning = {{ $status['running'] ? 'true' : 'false' }};
    
    function log(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const logHtml = `<div class="log-entry ${type}">[${timestamp}] ${message}</div>`;
        $('#feed-log').prepend(logHtml);
        
        // Keep only last 100 entries
        const entries = $('#feed-log .log-entry');
        if (entries.length > 100) {
            entries.slice(100).remove();
        }
    }
    
    function updateStats(status, stats) {
        $('#sources-processed').text(status.sources_processed || 0);
        $('#articles-fetched').text(status.articles_fetched || 0);
        $('#current-source').text(status.current_source || 'None');
        $('#errors-count').text(status.errors || 0);
        $('#stat-articles').text(stats.total_articles || 0);
        $('#stat-breaking').text(stats.breaking_news || 0);
        $('#stat-sources').text(stats.sources_count || 0);
    }
    
    function addNewArticle(article) {
        const html = `
            <div class="p-3 border-bottom article-item new">
                <div class="d-flex">
                    ${article.image ? `<img src="${article.image}" class="mr-2 rounded" style="width: 60px; height: 60px; object-fit: cover;" onerror="this.style.display='none'">` : ''}
                    <div class="flex-grow-1">
                        <a href="${article.url}" target="_blank" class="text-dark font-weight-bold" style="font-size: 13px; line-height: 1.3;">
                            ${article.title.substring(0, 80)}${article.title.length > 80 ? '...' : ''}
                        </a>
                        <div class="mt-1">
                            <small class="text-muted">
                                <i class="fas fa-newspaper mr-1"></i>${article.source}
                                <span class="mx-1">•</span>
                                ${article.published_at}
                            </small>
                            ${article.is_breaking ? '<span class="badge badge-danger ml-1">BREAKING</span>' : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#new-articles').prepend(html);
        
        // Keep only last 20
        const items = $('#new-articles .article-item');
        if (items.length > 20) {
            items.slice(20).remove();
        }
        
        newArticleCount++;
        $('#new-count').text(newArticleCount);
    }
    
    function highlightSource(sourceId) {
        $('.source-card').removeClass('active-fetch');
        $(`.source-card[data-source-id="${sourceId}"]`).addClass('active-fetch');
    }
    
    function startFeedLoop() {
        if (!isRunning) return;
        
        $('#fetch-spinner').show();
        $('#fetch-icon').hide();
        
        $.ajax({
            url: '{{ route("admin.realtime-feed.cycle") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(result) {
                if (result.running === false) {
                    log('Feed service is not running', 'warning');
                    return;
                }
                
                if (result.source) {
                    highlightSource(result.source_id);
                    
                    if (result.success) {
                        log(`✓ ${result.source}: ${result.articles_count} articles found, ${result.new_articles?.length || 0} new (${result.response_time}ms)`, 'success');
                        
                        // Add new articles
                        if (result.new_articles && result.new_articles.length > 0) {
                            result.new_articles.forEach(article => addNewArticle(article));
                        }
                    } else {
                        log(`✗ ${result.source}: ${result.error}`, 'error');
                    }
                }
                
                // Update stats via separate call
                $.get('{{ route("admin.realtime-feed.status") }}', function(data) {
                    updateStats(data.status, data.stats);
                });
                
                // Continue loop
                if (isRunning) {
                    setTimeout(startFeedLoop, 1000);
                }
            },
            error: function(xhr) {
                log('Connection error: ' + xhr.statusText, 'error');
                if (isRunning) {
                    setTimeout(startFeedLoop, 3000);
                }
            },
            complete: function() {
                $('#fetch-spinner').hide();
                $('#fetch-icon').show();
            }
        });
    }
    
    // Start button
    $('#btn-start').click(function() {
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Starting...');
        
        $.ajax({
            url: '{{ route("admin.realtime-feed.start") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(response) {
                isRunning = true;
                $('#btn-start').addClass('d-none').prop('disabled', false).html('<i class="fas fa-play mr-2"></i>START');
                $('#btn-stop').removeClass('d-none');
                $('.status-circle').removeClass('stopped').addClass('running').html('<i class="fas fa-play"></i>');
                $('#status-text').text('LIVE');
                log('Feed service started', 'success');
                
                // Start the loop
                startFeedLoop();
            },
            error: function() {
                $('#btn-start').prop('disabled', false).html('<i class="fas fa-play mr-2"></i>START');
                log('Failed to start feed service', 'error');
            }
        });
    });
    
    // Stop button
    $('#btn-stop').click(function() {
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Stopping...');
        
        $.ajax({
            url: '{{ route("admin.realtime-feed.stop") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(response) {
                isRunning = false;
                $('#btn-stop').addClass('d-none').prop('disabled', false).html('<i class="fas fa-stop mr-2"></i>STOP');
                $('#btn-start').removeClass('d-none');
                $('.status-circle').removeClass('running').addClass('stopped').html('<i class="fas fa-stop"></i>');
                $('#status-text').text('STOPPED');
                $('.source-card').removeClass('active-fetch');
                log('Feed service stopped', 'warning');
            },
            error: function() {
                $('#btn-stop').prop('disabled', false).html('<i class="fas fa-stop mr-2"></i>STOP');
                log('Failed to stop feed service', 'error');
            }
        });
    });
    
    // Reset button
    $('#btn-reset').click(function() {
        if (!confirm('Reset all feed statistics?')) return;
        
        $.ajax({
            url: '{{ route("admin.realtime-feed.reset") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function() {
                location.reload();
            }
        });
    });
    
    // Auto-start if was running
    if (isRunning) {
        log('Resuming feed service...', 'info');
        startFeedLoop();
    }
    
    // Refresh stats every 5 seconds even when not fetching
    setInterval(function() {
        $.get('{{ route("admin.realtime-feed.status") }}', function(data) {
            updateStats(data.status, data.stats);
        });
    }, 5000);
});
</script>
@endpush
