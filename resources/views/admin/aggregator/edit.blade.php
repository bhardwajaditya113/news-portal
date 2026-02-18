@extends('admin.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-edit mr-2"></i>{{ __('admin.Edit News Source') }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.aggregator.index') }}">News Sources</a></div>
            <div class="breadcrumb-item active">Edit</div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-rss mr-2"></i>Source Configuration</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.aggregator.update', $source->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Source Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $source->name) }}" required placeholder="e.g., BBC News">
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="slug">Slug</label>
                                    <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" 
                                           value="{{ old('slug', $source->slug) }}" placeholder="auto-generated-if-empty">
                                    @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="website_url">Website URL</label>
                                    <input type="url" name="website_url" id="website_url" class="form-control @error('website_url') is-invalid @enderror" 
                                           value="{{ old('website_url', $source->website_url) }}" placeholder="https://example.com">
                                    @error('website_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Country</label>
                                    <input type="text" name="country" id="country" class="form-control @error('country') is-invalid @enderror" 
                                           value="{{ old('country', $source->country) }}" placeholder="e.g., UK">
                                    @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="language">Language</label>
                                    <input type="text" name="language" id="language" class="form-control @error('language') is-invalid @enderror" 
                                           value="{{ old('language', $source->language) }}" placeholder="e.g., en">
                                    @error('language')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- API Configuration -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="api_type">API Type <span class="text-danger">*</span></label>
                                    <select name="api_type" id="api_type" class="form-control @error('api_type') is-invalid @enderror" required>
                                        <option value="">-- Select API Type --</option>
                                        <option value="rest" {{ old('api_type', $source->api_type) == 'rest' ? 'selected' : '' }}>REST API</option>
                                        <option value="rss" {{ old('api_type', $source->api_type) == 'rss' ? 'selected' : '' }}>RSS Feed</option>
                                    </select>
                                    @error('api_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="api_endpoint">API Endpoint <span class="text-danger">*</span></label>
                                    <input type="url" name="api_endpoint" id="api_endpoint" class="form-control @error('api_endpoint') is-invalid @enderror" 
                                           value="{{ old('api_endpoint', $source->api_endpoint) }}" required placeholder="https://api.example.com/news">
                                    @error('api_endpoint')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="api_key">API Key</label>
                                    <input type="text" name="api_key" id="api_key" class="form-control @error('api_key') is-invalid @enderror" 
                                           value="{{ old('api_key', $source->api_key) }}" placeholder="Your API key if required">
                                    @error('api_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rss_feed_url">RSS Feed URL</label>
                                    <input type="url" name="rss_feed_url" id="rss_feed_url" class="form-control @error('rss_feed_url') is-invalid @enderror" 
                                           value="{{ old('rss_feed_url', $source->rss_feed_url) }}" placeholder="https://example.com/feed.xml">
                                    @error('rss_feed_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Configuration -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fetch_interval">Fetch Interval (minutes)</label>
                                    <input type="number" name="fetch_interval" id="fetch_interval" class="form-control @error('fetch_interval') is-invalid @enderror" 
                                           value="{{ old('fetch_interval', $source->fetch_interval ?? 30) }}" min="1">
                                    @error('fetch_interval')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="priority">Priority</label>
                                    <input type="number" name="priority" id="priority" class="form-control @error('priority') is-invalid @enderror" 
                                           value="{{ old('priority', $source->priority ?? 3) }}" min="1" max="5">
                                    @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="credibility_score">Credibility Score</label>
                                    <input type="number" name="credibility_score" id="credibility_score" class="form-control @error('credibility_score') is-invalid @enderror" 
                                           value="{{ old('credibility_score', $source->credibility_score ?? 85) }}" min="0" max="100">
                                    @error('credibility_score')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_active">
                                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $source->is_active) ? 'checked' : '' }}>
                                        Active
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="alert alert-info">
                                    <strong>Last Fetched:</strong> {{ $source->last_fetched_at ? $source->last_fetched_at->diffForHumans() : 'Never' }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Update News Source
                            </button>
                            <a href="{{ route('admin.aggregator.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                            <a href="{{ route('admin.aggregator.destroy', $source->id) }}" class="btn btn-danger" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash mr-2"></i>Delete
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
