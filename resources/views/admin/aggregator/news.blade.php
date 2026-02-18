@extends('admin.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-newspaper mr-2"></i>{{ __('admin.Aggregated News') }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.aggregator.index') }}">News Sources</a></div>
            <div class="breadcrumb-item active">Aggregated News</div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    @endif

    <!-- Filters -->
    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-filter mr-2"></i>Filters</h4>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Source</label>
                        <select name="source" class="form-control select2">
                            <option value="">All Sources</option>
                            @foreach($sources as $source)
                            <option value="{{ $source->id }}" {{ request('source') == $source->id ? 'selected' : '' }}>
                                {{ $source->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Type</label>
                        <select name="type" class="form-control">
                            <option value="">All</option>
                            <option value="breaking" {{ request('type') == 'breaking' ? 'selected' : '' }}>Breaking</option>
                            <option value="trending" {{ request('type') == 'trending' ? 'selected' : '' }}>Trending</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Search</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search title...">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search mr-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- News Table -->
    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-list mr-2"></i>News Articles ({{ $news->total() }})</h4>
            <div class="card-header-action">
                <div class="btn-group">
                    <button type="button" class="btn btn-success btn-sm" id="bulkPublish">
                        <i class="fas fa-check mr-1"></i> Publish Selected
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="bulkReject">
                        <i class="fas fa-times mr-1"></i> Reject Selected
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" width="40">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th width="400">Article</th>
                            <th>Source</th>
                            <th class="text-center">Category</th>
                            <th class="text-center">Score</th>
                            <th class="text-center">Published</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($news as $article)
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="article-checkbox" value="{{ $article->id }}">
                            </td>
                            <td>
                                <div class="d-flex">
                                    @if($article->image)
                                    <img src="{{ $article->image }}" alt="" class="rounded mr-3" style="width: 80px; height: 60px; object-fit: cover;">
                                    @endif
                                    <div>
                                        <div class="font-weight-bold">
                                            {{ Str::limit($article->title, 70) }}
                                            @if($article->is_breaking)
                                            <span class="badge badge-danger">Breaking</span>
                                            @endif
                                            @if($article->is_trending)
                                            <span class="badge badge-warning">Trending</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{ Str::limit(strip_tags($article->content), 100) }}</small>
                                        <div class="mt-1">
                                            <a href="{{ $article->original_url }}" target="_blank" class="text-primary small">
                                                <i class="fas fa-external-link-alt mr-1"></i> View Original
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($article->source && $article->source->logo_url)
                                    <img src="{{ $article->source->logo_url }}" alt="" class="mr-2" style="width: 20px; height: 20px; object-fit: contain;">
                                    @endif
                                    <span>{{ $article->source->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light">{{ $article->category->name ?? 'Uncategorized' }}</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center">
                                    <small class="text-muted">Sentiment</small>
                                    <span class="badge badge-{{ $article->sentiment_score > 0 ? 'success' : ($article->sentiment_score < 0 ? 'danger' : 'secondary') }}">
                                        {{ $article->sentiment_score }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div>{{ $article->published_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $article->published_at->diffForHumans() }}</small>
                            </td>
                            <td class="text-center">
                                @if($article->status === 'published')
                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Published</span>
                                @elseif($article->status === 'rejected')
                                <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Rejected</span>
                                @else
                                <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Pending</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    @if($article->status !== 'published')
                                    <button type="button" class="btn btn-sm btn-success status-btn" data-id="{{ $article->id }}" data-status="published" title="Publish">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endif
                                    @if($article->status !== 'rejected')
                                    <button type="button" class="btn btn-sm btn-danger status-btn" data-id="{{ $article->id }}" data-status="rejected" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                                    <h5>No Aggregated News Found</h5>
                                    <p class="text-muted">Start fetching news from your configured sources.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($news->hasPages())
        <div class="card-footer">
            {{ $news->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select all
    $('#selectAll').change(function() {
        $('.article-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Status change
    $('.status-btn').click(function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        updateStatus([id], status);
    });

    // Bulk publish
    $('#bulkPublish').click(function() {
        var ids = getSelectedIds();
        if (ids.length > 0) {
            updateStatus(ids, 'published');
        } else {
            alert('Please select at least one article');
        }
    });

    // Bulk reject
    $('#bulkReject').click(function() {
        var ids = getSelectedIds();
        if (ids.length > 0) {
            updateStatus(ids, 'rejected');
        } else {
            alert('Please select at least one article');
        }
    });

    function getSelectedIds() {
        return $('.article-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
    }

    function updateStatus(ids, status) {
        $.ajax({
            url: '{{ route("admin.aggregator.news.status") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                ids: ids,
                status: status
            },
            success: function(response) {
                toastr.success(response.message);
                location.reload();
            },
            error: function() {
                toastr.error('Failed to update status');
            }
        });
    }
});
</script>
@endpush
