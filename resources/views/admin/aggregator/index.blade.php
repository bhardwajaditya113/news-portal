@extends('admin.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-rss mr-2"></i>{{ __('admin.News Aggregator') }}</h1>
        <div class="section-header-breadcrumb">
            <a href="{{ route('admin.aggregator.initialize') }}" class="btn btn-info mr-2" onclick="return confirm('Initialize default news sources?')">
                <i class="fas fa-magic mr-1"></i> Initialize Defaults
            </a>
            <a href="{{ route('admin.aggregator.fetch-all') }}" class="btn btn-warning mr-2">
                <i class="fas fa-sync-alt mr-1"></i> Fetch All
            </a>
            <a href="{{ route('admin.aggregator.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Add Source
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    @endif

    <!-- Statistics Overview -->
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary">
                    <i class="fas fa-rss"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Total Sources</h4></div>
                    <div class="card-body">{{ $sources->total() }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Active Sources</h4></div>
                    <div class="card-body">{{ $sources->where('is_active', true)->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Aggregated News</h4></div>
                    <div class="card-body">{{ \App\Models\AggregatedNews::count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Breaking News</h4></div>
                    <div class="card-body">{{ \App\Models\AggregatedNews::where('is_breaking', true)->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sources Table -->
    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-list mr-2"></i>News Sources</h4>
            <div class="card-header-form">
                <form method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search sources..." value="{{ request('search') }}">
                        <div class="input-group-btn">
                            <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" width="50">#</th>
                            <th>Source</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th class="text-center">Priority</th>
                            <th class="text-center">Credibility</th>
                            <th class="text-center">Articles</th>
                            <th class="text-center">Last Fetched</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sources as $index => $source)
                        <tr>
                            <td class="text-center">{{ $sources->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($source->logo_url)
                                    <img src="{{ $source->logo_url }}" alt="{{ $source->name }}" class="rounded mr-2" style="width: 32px; height: 32px; object-fit: contain;">
                                    @else
                                    <div class="mr-2" style="width: 32px; height: 32px; background: #e3e6f0; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-rss text-primary"></i>
                                    </div>
                                    @endif
                                    <div>
                                        <div class="font-weight-bold">{{ $source->name }}</div>
                                        <small class="text-muted">{{ Str::limit($source->rss_url, 40) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $source->api_type === 'rss' ? 'info' : 'success' }}">
                                    {{ strtoupper($source->api_type) }}
                                </span>
                            </td>
                            <td>
                                @if($source->category)
                                <span class="badge badge-light">{{ $source->category->name ?? 'N/A' }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-{{ $source->priority >= 8 ? 'danger' : ($source->priority >= 5 ? 'warning' : 'secondary') }}">
                                    {{ $source->priority }}/10
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="progress" style="height: 20px; width: 60px; margin: auto;">
                                    <div class="progress-bar bg-{{ $source->credibility_score >= 80 ? 'success' : ($source->credibility_score >= 60 ? 'warning' : 'danger') }}" 
                                         style="width: {{ $source->credibility_score }}%;">
                                        {{ $source->credibility_score }}%
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <strong>{{ $source->news_count }}</strong>
                            </td>
                            <td class="text-center">
                                @if($source->last_fetched_at)
                                <small>{{ $source->last_fetched_at->diffForHumans() }}</small>
                                @else
                                <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <label class="custom-switch mb-0">
                                    <input type="checkbox" name="status" class="custom-switch-input toggle-status" 
                                           data-id="{{ $source->id }}" {{ $source->is_active ? 'checked' : '' }}>
                                    <span class="custom-switch-indicator"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('admin.aggregator.fetch', $source->id) }}" class="btn btn-sm btn-warning" title="Fetch Now">
                                        <i class="fas fa-sync-alt"></i>
                                    </a>
                                    <a href="{{ route('admin.aggregator.edit', $source->id) }}" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $source->id }}" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="fas fa-rss fa-3x text-muted mb-3"></i>
                                    <h5>No News Sources Found</h5>
                                    <p class="text-muted">Add your first news source or initialize defaults.</p>
                                    <a href="{{ route('admin.aggregator.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus mr-1"></i> Add Source
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($sources->hasPages())
        <div class="card-footer">
            {{ $sources->links() }}
        </div>
        @endif
    </div>
</section>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-danger mr-2"></i>Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this news source? This will also delete all aggregated news from this source.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle status
    $('.toggle-status').change(function() {
        var id = $(this).data('id');
        $.ajax({
            url: '{{ url("admin/aggregator") }}/' + id + '/toggle',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                toastr.success(response.message);
            },
            error: function() {
                toastr.error('Failed to update status');
            }
        });
    });

    // Delete confirmation
    $('.delete-btn').click(function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '{{ url("admin/aggregator") }}/' + id);
        $('#deleteModal').modal('show');
    });
});
</script>
@endpush
