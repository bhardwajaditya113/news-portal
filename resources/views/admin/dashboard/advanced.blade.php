@extends('admin.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-chart-line mr-2"></i>{{ __('admin.Advanced Analytics Dashboard') }}</h1>
        <div class="section-header-breadcrumb">
            <div class="btn-group mr-3">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-calendar-alt mr-1"></i> {{ ucfirst($period) }}
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item {{ $period === 'today' ? 'active' : '' }}" href="?period=today">Today</a>
                    <a class="dropdown-item {{ $period === 'yesterday' ? 'active' : '' }}" href="?period=yesterday">Yesterday</a>
                    <a class="dropdown-item {{ $period === 'week' ? 'active' : '' }}" href="?period=week">This Week</a>
                    <a class="dropdown-item {{ $period === 'month' ? 'active' : '' }}" href="?period=month">This Month</a>
                    <a class="dropdown-item {{ $period === 'quarter' ? 'active' : '' }}" href="?period=quarter">This Quarter</a>
                    <a class="dropdown-item {{ $period === 'year' ? 'active' : '' }}" href="?period=year">This Year</a>
                </div>
            </div>
            <button class="btn btn-success" onclick="window.location.href='{{ route('admin.dashboard.export') }}?period={{ $period }}'">
                <i class="fas fa-download mr-1"></i> Export Report
            </button>
        </div>
    </div>

    <!-- Real-time Stats Bar -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="d-flex align-items-center">
                                <div class="realtime-pulse mr-3"></div>
                                <span class="font-weight-bold">LIVE</span>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="small text-white-50">Active Users</div>
                                    <div class="h4 mb-0" id="realtime-users">{{ $analytics['realtime']['active_users'] ?? 0 }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="small text-white-50">Views (Last Hour)</div>
                                    <div class="h4 mb-0" id="realtime-hour">{{ $analytics['realtime']['views_last_hour'] ?? 0 }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="small text-white-50">Views (Last 5 Min)</div>
                                    <div class="h4 mb-0" id="realtime-5min">{{ $analytics['realtime']['views_last_5min'] ?? 0 }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="small text-white-50">Currently Reading</div>
                                    <div class="h4 mb-0" id="realtime-reading">{{ $analytics['realtime']['currently_reading'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Statistics -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>{{ __('admin.Total Views') }}</h4>
                    </div>
                    <div class="card-body d-flex align-items-center">
                        <span class="h4 mb-0">{{ number_format($analytics['overview']['total_views']['value'] ?? 0) }}</span>
                        <span class="badge badge-{{ ($analytics['overview']['total_views']['trend'] ?? 'up') === 'up' ? 'success' : 'danger' }} ml-2">
                            <i class="fas fa-arrow-{{ ($analytics['overview']['total_views']['trend'] ?? 'up') === 'up' ? 'up' : 'down' }}"></i>
                            {{ abs($analytics['overview']['total_views']['change'] ?? 0) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success">
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>{{ __('admin.Unique Visitors') }}</h4>
                    </div>
                    <div class="card-body d-flex align-items-center">
                        <span class="h4 mb-0">{{ number_format($analytics['overview']['unique_visitors']['value'] ?? 0) }}</span>
                        <span class="badge badge-{{ ($analytics['overview']['unique_visitors']['trend'] ?? 'up') === 'up' ? 'success' : 'danger' }} ml-2">
                            <i class="fas fa-arrow-{{ ($analytics['overview']['unique_visitors']['trend'] ?? 'up') === 'up' ? 'up' : 'down' }}"></i>
                            {{ abs($analytics['overview']['unique_visitors']['change'] ?? 0) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>{{ __('admin.Avg. Session') }}</h4>
                    </div>
                    <div class="card-body d-flex align-items-center">
                        <span class="h4 mb-0">{{ $analytics['overview']['avg_session_duration']['formatted'] ?? '0s' }}</span>
                        <span class="badge badge-{{ ($analytics['overview']['avg_session_duration']['trend'] ?? 'up') === 'up' ? 'success' : 'danger' }} ml-2">
                            <i class="fas fa-arrow-{{ ($analytics['overview']['avg_session_duration']['trend'] ?? 'up') === 'up' ? 'up' : 'down' }}"></i>
                            {{ abs($analytics['overview']['avg_session_duration']['change'] ?? 0) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>{{ __('admin.Bounce Rate') }}</h4>
                    </div>
                    <div class="card-body">
                        <span class="h4 mb-0">{{ $analytics['overview']['bounce_rate']['value'] ?? 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Content Stats -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-2">
                <div class="card-stats">
                    <div class="card-stats-title">Content Overview</div>
                    <div class="card-stats-items">
                        <div class="card-stats-item">
                            <div class="card-stats-item-count">{{ $quickStats['total_internal_news'] }}</div>
                            <div class="card-stats-item-label">Internal</div>
                        </div>
                        <div class="card-stats-item">
                            <div class="card-stats-item-count">{{ $quickStats['total_aggregated_news'] }}</div>
                            <div class="card-stats-item-label">Aggregated</div>
                        </div>
                        <div class="card-stats-item">
                            <div class="card-stats-item-count">{{ $quickStats['pending_news'] }}</div>
                            <div class="card-stats-item-label">Pending</div>
                        </div>
                    </div>
                </div>
                <div class="card-icon shadow-primary bg-primary">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Articles</h4>
                    </div>
                    <div class="card-body">
                        {{ number_format($quickStats['total_internal_news'] + $quickStats['total_aggregated_news']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-2">
                <div class="card-icon shadow-success bg-success">
                    <i class="fas fa-rss"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Active Sources</h4>
                    </div>
                    <div class="card-body">
                        {{ $quickStats['active_sources'] }} / {{ $quickStats['total_sources'] }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-2">
                <div class="card-icon shadow-danger bg-danger">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Breaking News</h4>
                    </div>
                    <div class="card-body">
                        {{ $quickStats['breaking_news'] }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-2">
                <div class="card-icon shadow-warning bg-warning">
                    <i class="fas fa-fire"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Trending Topics</h4>
                    </div>
                    <div class="card-body">
                        {{ $quickStats['trending_topics'] }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Charts Row -->
    <div class="row">
        <!-- Traffic Chart -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-area mr-2"></i>Traffic Overview</h4>
                    <div class="card-header-action">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary active" data-chart-type="views">Views</button>
                            <button class="btn btn-sm btn-outline-primary" data-chart-type="visitors">Visitors</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="trafficChart" height="280"></canvas>
                </div>
            </div>
        </div>

        <!-- Device Breakdown -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-mobile-alt mr-2"></i>Device Breakdown</h4>
                </div>
                <div class="card-body">
                    <canvas id="deviceChart" height="280"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Demographics & Sources -->
    <div class="row">
        <!-- World Map -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-globe mr-2"></i>Geographic Distribution</h4>
                </div>
                <div class="card-body">
                    <div id="world-map" style="height: 350px;"></div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        @foreach(($analytics['demographics']['by_country'] ?? collect())->take(5) as $country)
                        <div class="col">
                            <div class="d-flex align-items-center">
                                <span class="flag-icon flag-icon-{{ strtolower($country->country) }} mr-2"></span>
                                <div>
                                    <div class="font-weight-bold">{{ $country->country }}</div>
                                    <div class="text-muted small">{{ number_format($country->count) }} visits</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Sources Performance -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-rss mr-2"></i>News Sources</h4>
                    <div class="card-header-action">
                        <a href="{{ route('admin.aggregator.index') }}" class="btn btn-sm btn-primary">Manage</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach(($analytics['sources']['sources'] ?? collect())->take(6) as $source)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                @if($source['logo'])
                                <img src="{{ $source['logo'] }}" alt="{{ $source['name'] }}" class="mr-2" style="width: 24px; height: 24px; object-fit: contain;">
                                @else
                                <i class="fas fa-rss mr-2 text-primary"></i>
                                @endif
                                <div>
                                    <div class="font-weight-bold">{{ $source['name'] }}</div>
                                    <small class="text-muted">{{ $source['recent_news'] }} new today</small>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-{{ $source['is_active'] ? 'success' : 'secondary' }}">
                                    {{ $source['is_active'] ? 'Active' : 'Inactive' }}
                                </span>
                                <div class="small text-muted">{{ $source['total_news'] }} total</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trending & Top Content -->
    <div class="row">
        <!-- Trending Topics -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-fire mr-2 text-danger"></i>Trending Topics</h4>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach(($analytics['trending']['trending_topics'] ?? collect())->take(10) as $index => $topic)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="badge badge-{{ $index < 3 ? 'danger' : 'primary' }} mr-2">{{ $index + 1 }}</span>
                                <div>
                                    <div class="font-weight-bold">#{{ $topic->topic }}</div>
                                    <small class="text-muted">{{ $topic->news_count }} articles</small>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-success">
                                    <i class="fas fa-chart-line"></i> {{ number_format($topic->engagement_score) }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performing Content -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-star mr-2 text-warning"></i>Top Articles</h4>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach(($analytics['content']['top_aggregated'] ?? collect())->take(6) as $news)
                        <a href="{{ $news->original_url }}" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex">
                                @if($news->image)
                                <img src="{{ $news->image }}" alt="" class="mr-3 rounded" style="width: 60px; height: 45px; object-fit: cover;">
                                @endif
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold text-truncate" style="max-width: 200px;">{{ $news->title }}</div>
                                    <small class="text-muted">
                                        {{ $news->source->name ?? 'Unknown' }} • {{ number_format($news->views_count) }} views
                                    </small>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Performance -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-folder mr-2"></i>Category Performance</h4>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Engagement Stats -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-heart mr-2 text-danger"></i>Engagement Metrics</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 col-sm-4 col-6 text-center">
                            <div class="h3 text-primary">{{ number_format($analytics['engagement']['total_shares'] ?? 0) }}</div>
                            <div class="text-muted">Total Shares</div>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 text-center">
                            <div class="h3 text-info">{{ number_format($analytics['engagement']['comments'] ?? 0) }}</div>
                            <div class="text-muted">Comments</div>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 text-center">
                            <div class="h3 text-success">{{ $analytics['engagement']['avg_read_time'] ?? 0 }}s</div>
                            <div class="text-muted">Avg Read Time</div>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 text-center">
                            <div class="h3 text-warning">{{ $analytics['engagement']['avg_scroll_depth'] ?? 0 }}%</div>
                            <div class="text-muted">Avg Scroll Depth</div>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 text-center">
                            <div class="h3 text-danger">{{ number_format($analytics['engagement']['new_subscribers'] ?? 0) }}</div>
                            <div class="text-muted">New Subscribers</div>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 text-center">
                            <div class="h3 text-secondary">{{ $analytics['demographics']['countries_count'] ?? 0 }}</div>
                            <div class="text-muted">Countries</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Breaking News Section -->
    @if(count($analytics['trending']['breaking_news'] ?? []) > 0)
    <div class="row">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0"><i class="fas fa-bolt mr-2"></i>Breaking News Alert</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach(($analytics['trending']['breaking_news'] ?? collect())->take(4) as $breaking)
                        <div class="col-md-3">
                            <div class="card mb-0">
                                @if($breaking->image)
                                <img src="{{ $breaking->image }}" class="card-img-top" alt="" style="height: 120px; object-fit: cover;">
                                @endif
                                <div class="card-body p-2">
                                    <h6 class="card-title mb-1">{{ Str::limit($breaking->title, 60) }}</h6>
                                    <small class="text-muted">{{ $breaking->source->name ?? 'Unknown' }} • {{ $breaking->published_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</section>

<style>
.realtime-pulse {
    width: 12px;
    height: 12px;
    background: #fff;
    border-radius: 50%;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(255,255,255,0.7); }
    70% { box-shadow: 0 0 0 10px rgba(255,255,255,0); }
    100% { box-shadow: 0 0 0 0 rgba(255,255,255,0); }
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card-statistic-2 .card-icon {
    width: 80px;
    height: 80px;
    line-height: 80px;
    font-size: 30px;
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
// Traffic Chart
const trafficCtx = document.getElementById('trafficChart').getContext('2d');
const trafficChart = new Chart(trafficCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($analytics['traffic']['chart_labels'] ?? []) !!},
        datasets: [{
            label: 'Page Views',
            data: {!! json_encode($analytics['traffic']['chart_views'] ?? []) !!},
            borderColor: '#6777ef',
            backgroundColor: 'rgba(103, 119, 239, 0.1)',
            fill: true,
            tension: 0.4
        }, {
            label: 'Unique Visitors',
            data: {!! json_encode($analytics['traffic']['chart_visitors'] ?? []) !!},
            borderColor: '#63ed7a',
            backgroundColor: 'rgba(99, 237, 122, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Device Chart
const deviceCtx = document.getElementById('deviceChart').getContext('2d');
new Chart(deviceCtx, {
    type: 'doughnut',
    data: {
        labels: ['Desktop', 'Mobile', 'Tablet'],
        datasets: [{
            data: [
                {{ $analytics['traffic']['by_device']['desktop'] ?? 0 }},
                {{ $analytics['traffic']['by_device']['mobile'] ?? 0 }},
                {{ $analytics['traffic']['by_device']['tablet'] ?? 0 }}
            ],
            backgroundColor: ['#6777ef', '#63ed7a', '#ffa426']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Category Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(($analytics['content']['category_performance'] ?? collect())->pluck('name')->take(8)) !!},
        datasets: [{
            label: 'Views',
            data: {!! json_encode(($analytics['content']['category_performance'] ?? collect())->pluck('views')->take(8)) !!},
            backgroundColor: '#6777ef'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Real-time updates
function updateRealtimeStats() {
    fetch('{{ route("admin.dashboard.realtime") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('realtime-users').textContent = data.active_users;
            document.getElementById('realtime-hour').textContent = data.views_last_hour;
            document.getElementById('realtime-5min').textContent = data.views_last_5min;
            document.getElementById('realtime-reading').textContent = data.currently_reading;
        });
}

setInterval(updateRealtimeStats, 30000); // Update every 30 seconds
</script>
@endpush
