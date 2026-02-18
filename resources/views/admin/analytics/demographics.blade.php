@extends('admin.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-globe mr-2"></i>{{ __('admin.Demographics Analytics') }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Demographics</div>
        </div>
    </div>

    <!-- World Map -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-map-marked-alt mr-2"></i>Geographic Distribution</h4>
                    <div class="card-header-action">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary active" data-view="visits">Visits</button>
                            <button class="btn btn-sm btn-outline-primary" data-view="pageviews">Pageviews</button>
                            <button class="btn btn-sm btn-outline-primary" data-view="engagement">Engagement</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="world-map-container" style="height: 450px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Countries -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-flag mr-2"></i>Top Countries</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Country</th>
                                    <th class="text-center">Visitors</th>
                                    <th class="text-center">% Share</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($demographics['by_country'] ?? [] as $index => $country)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span class="flag-icon flag-icon-{{ strtolower($country->country) }} mr-2"></span>
                                        {{ $country->country_name ?? $country->country }}
                                    </td>
                                    <td class="text-center">{{ number_format($country->count) }}</td>
                                    <td class="text-center">
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar" style="width: {{ $country->percentage ?? 0 }}%"></div>
                                        </div>
                                        <small>{{ number_format($country->percentage ?? 0, 1) }}%</small>
                                    </td>
                                    <td>
                                        <span class="text-{{ ($country->trend ?? 0) >= 0 ? 'success' : 'danger' }}">
                                            <i class="fas fa-arrow-{{ ($country->trend ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                                            {{ abs($country->trend ?? 0) }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Cities -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-city mr-2"></i>Top Cities</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>City</th>
                                    <th>Country</th>
                                    <th class="text-center">Visitors</th>
                                    <th class="text-center">Avg. Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($demographics['by_city'] ?? [] as $index => $city)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $city->city }}</strong></td>
                                    <td>
                                        <span class="flag-icon flag-icon-{{ strtolower($city->country) }} mr-1"></span>
                                        {{ $city->country }}
                                    </td>
                                    <td class="text-center">{{ number_format($city->count) }}</td>
                                    <td class="text-center">{{ $city->avg_time ?? '0s' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Languages -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-language mr-2"></i>Languages</h4>
                </div>
                <div class="card-body">
                    <canvas id="languageChart" height="250"></canvas>
                </div>
                <div class="card-footer p-0">
                    <div class="list-group list-group-flush">
                        @foreach($demographics['by_language'] ?? [] as $lang)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $lang->language }}</span>
                            <span class="badge badge-primary">{{ number_format($lang->count) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Devices -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-mobile-alt mr-2"></i>Devices</h4>
                </div>
                <div class="card-body">
                    <canvas id="deviceChart" height="250"></canvas>
                </div>
                <div class="card-footer">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h4 mb-0 text-primary">{{ $demographics['devices']['desktop'] ?? 0 }}%</div>
                            <small class="text-muted">Desktop</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 mb-0 text-success">{{ $demographics['devices']['mobile'] ?? 0 }}%</div>
                            <small class="text-muted">Mobile</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 mb-0 text-warning">{{ $demographics['devices']['tablet'] ?? 0 }}%</div>
                            <small class="text-muted">Tablet</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Browsers -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fab fa-chrome mr-2"></i>Browsers</h4>
                </div>
                <div class="card-body">
                    <canvas id="browserChart" height="250"></canvas>
                </div>
                <div class="card-footer p-0">
                    <div class="list-group list-group-flush">
                        @foreach($demographics['by_browser'] ?? [] as $browser)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span>
                                <i class="fab fa-{{ strtolower($browser->browser) }} mr-2"></i>
                                {{ $browser->browser }}
                            </span>
                            <span class="badge badge-light">{{ $browser->percentage ?? 0 }}%</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audience Segments -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-users mr-2"></i>Audience Segments</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="segment-card new-visitors">
                                <div class="segment-icon">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="segment-data">
                                    <h3>{{ $demographics['segments']['new_visitors'] ?? 0 }}%</h3>
                                    <p>New Visitors</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="segment-card returning">
                                <div class="segment-icon">
                                    <i class="fas fa-redo"></i>
                                </div>
                                <div class="segment-data">
                                    <h3>{{ $demographics['segments']['returning'] ?? 0 }}%</h3>
                                    <p>Returning Visitors</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="segment-card engaged">
                                <div class="segment-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="segment-data">
                                    <h3>{{ $demographics['segments']['engaged'] ?? 0 }}%</h3>
                                    <p>Highly Engaged</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="segment-card subscribers">
                                <div class="segment-icon">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div class="segment-data">
                                    <h3>{{ $demographics['segments']['subscribers'] ?? 0 }}%</h3>
                                    <p>Newsletter Subscribers</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Peak Hours Heatmap -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-clock mr-2"></i>Peak Traffic Hours</h4>
                </div>
                <div class="card-body">
                    <div id="heatmap-container"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Segment Cards */
.segment-card {
    display: flex;
    align-items: center;
    padding: 20px;
    border-radius: 12px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: #fff;
}

.segment-card.new-visitors { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.segment-card.returning { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
.segment-card.engaged { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.segment-card.subscribers { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }

.segment-icon {
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin-right: 16px;
}

.segment-data h3 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 4px;
}

.segment-data p {
    margin: 0;
    opacity: 0.9;
    font-size: 13px;
}

/* Heatmap */
#heatmap-container {
    height: 300px;
    width: 100%;
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap/dist/maps/world.js"></script>

<script>
// Device Chart
new Chart(document.getElementById('deviceChart'), {
    type: 'doughnut',
    data: {
        labels: ['Desktop', 'Mobile', 'Tablet'],
        datasets: [{
            data: [
                {{ $demographics['devices']['desktop'] ?? 45 }},
                {{ $demographics['devices']['mobile'] ?? 45 }},
                {{ $demographics['devices']['tablet'] ?? 10 }}
            ],
            backgroundColor: ['#6366f1', '#10b981', '#f59e0b']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
    }
});

// Language Chart
new Chart(document.getElementById('languageChart'), {
    type: 'pie',
    data: {
        labels: {!! json_encode(($demographics['by_language'] ?? collect())->pluck('language')) !!},
        datasets: [{
            data: {!! json_encode(($demographics['by_language'] ?? collect())->pluck('count')) !!},
            backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
});

// Browser Chart
new Chart(document.getElementById('browserChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(($demographics['by_browser'] ?? collect())->pluck('browser')) !!},
        datasets: [{
            data: {!! json_encode(($demographics['by_browser'] ?? collect())->pluck('percentage')) !!},
            backgroundColor: '#6366f1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { max: 100 } }
    }
});

// World Map
const map = new jsVectorMap({
    selector: '#world-map-container',
    map: 'world',
    backgroundColor: 'transparent',
    regionStyle: {
        initial: {
            fill: '#e4e4e4',
            stroke: '#fff',
            strokeWidth: 0.5
        },
        hover: { fill: '#6366f1' }
    },
    series: {
        regions: [{
            values: {!! json_encode(($demographics['map_data'] ?? collect())->pluck('value', 'code')) !!},
            scale: ['#c8eeff', '#0073e6'],
            normalizeFunction: 'polynomial'
        }]
    },
    onRegionTooltipShow: function(event, tooltip, code) {
        const value = map.series.regions[0].values[code] || 0;
        tooltip.text(`${tooltip.text()}: ${value.toLocaleString()} visitors`);
    }
});
</script>
@endpush
