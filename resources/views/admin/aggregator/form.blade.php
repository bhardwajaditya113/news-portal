@extends('admin.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-plus mr-2"></i>{{ isset($source) ? __('admin.Edit News Source') : __('admin.Add News Source') }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.aggregator.index') }}">News Sources</a></div>
            <div class="breadcrumb-item active">{{ isset($source) ? 'Edit' : 'Create' }}</div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-rss mr-2"></i>Source Configuration</h4>
                </div>
                <div class="card-body">
                    <form action="{{ isset($source) ? route('admin.aggregator.update', $source->id) : route('admin.aggregator.store') }}" method="POST">
                        @csrf
                        @if(isset($source))
                        @method('PUT')
                        @endif

                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Source Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $source->name ?? '') }}" required placeholder="e.g., BBC News">
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="slug">Slug</label>
                                    <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" 
                                           value="{{ old('slug', $source->slug ?? '') }}" placeholder="auto-generated-if-empty">
                                    @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="rss_url">RSS/API URL <span class="text-danger">*</span></label>
                                    <input type="url" name="rss_url" id="rss_url" class="form-control @error('rss_url') is-invalid @enderror" 
                                           value="{{ old('rss_url', $source->rss_url ?? '') }}" required placeholder="https://feeds.example.com/rss">
                                    @error('rss_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="website_url">Website URL</label>
                                    <input type="url" name="website_url" id="website_url" class="form-control @error('website_url') is-invalid @enderror" 
                                           value="{{ old('website_url', $source->website_url ?? '') }}" placeholder="https://www.example.com">
                                    @error('website_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="logo_url">Logo URL</label>
                                    <input type="url" name="logo_url" id="logo_url" class="form-control @error('logo_url') is-invalid @enderror" 
                                           value="{{ old('logo_url', $source->logo_url ?? '') }}" placeholder="https://www.example.com/logo.png">
                                    @error('logo_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="2" placeholder="Brief description of this news source">{{ old('description', $source->description ?? '') }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- API Configuration -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="api_type">API Type <span class="text-danger">*</span></label>
                                    <select name="api_type" id="api_type" class="form-control @error('api_type') is-invalid @enderror" required>
                                        <option value="rss" {{ old('api_type', $source->api_type ?? 'rss') === 'rss' ? 'selected' : '' }}>RSS Feed</option>
                                        <option value="newsapi" {{ old('api_type', $source->api_type ?? '') === 'newsapi' ? 'selected' : '' }}>NewsAPI</option>
                                        <option value="gnews" {{ old('api_type', $source->api_type ?? '') === 'gnews' ? 'selected' : '' }}>GNews API</option>
                                        <option value="custom" {{ old('api_type', $source->api_type ?? '') === 'custom' ? 'selected' : '' }}>Custom API</option>
                                    </select>
                                    @error('api_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="api_key">API Key (if required)</label>
                                    <input type="text" name="api_key" id="api_key" class="form-control @error('api_key') is-invalid @enderror" 
                                           value="{{ old('api_key', $source->api_key ?? '') }}" placeholder="Your API key">
                                    @error('api_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fetch_interval">Fetch Interval (minutes)</label>
                                    <input type="number" name="fetch_interval" id="fetch_interval" class="form-control @error('fetch_interval') is-invalid @enderror" 
                                           value="{{ old('fetch_interval', $source->fetch_interval ?? 30) }}" min="5" max="1440">
                                    @error('fetch_interval')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Category & Region -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="default_category_id">Default Category</label>
                                    <select name="default_category_id" id="default_category_id" class="form-control select2 @error('default_category_id') is-invalid @enderror">
                                        <option value="">-- Select Category --</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('default_category_id', $source->default_category_id ?? '') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('default_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="language">Language</label>
                                    <select name="language" id="language" class="form-control @error('language') is-invalid @enderror">
                                        <option value="en" {{ old('language', $source->language ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                                        <option value="hi" {{ old('language', $source->language ?? '') === 'hi' ? 'selected' : '' }}>Hindi</option>
                                        <option value="bn" {{ old('language', $source->language ?? '') === 'bn' ? 'selected' : '' }}>Bengali</option>
                                        <option value="tr" {{ old('language', $source->language ?? '') === 'tr' ? 'selected' : '' }}>Turkish</option>
                                        <option value="es" {{ old('language', $source->language ?? '') === 'es' ? 'selected' : '' }}>Spanish</option>
                                        <option value="fr" {{ old('language', $source->language ?? '') === 'fr' ? 'selected' : '' }}>French</option>
                                        <option value="de" {{ old('language', $source->language ?? '') === 'de' ? 'selected' : '' }}>German</option>
                                        <option value="ar" {{ old('language', $source->language ?? '') === 'ar' ? 'selected' : '' }}>Arabic</option>
                                    </select>
                                    @error('language')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="region">Region/Country</label>
                                    <select name="region" id="region" class="form-control @error('region') is-invalid @enderror">
                                        <option value="global" {{ old('region', $source->region ?? 'global') === 'global' ? 'selected' : '' }}>Global</option>
                                        <option value="in" {{ old('region', $source->region ?? '') === 'in' ? 'selected' : '' }}>India</option>
                                        <option value="us" {{ old('region', $source->region ?? '') === 'us' ? 'selected' : '' }}>United States</option>
                                        <option value="uk" {{ old('region', $source->region ?? '') === 'uk' ? 'selected' : '' }}>United Kingdom</option>
                                        <option value="au" {{ old('region', $source->region ?? '') === 'au' ? 'selected' : '' }}>Australia</option>
                                        <option value="de" {{ old('region', $source->region ?? '') === 'de' ? 'selected' : '' }}>Germany</option>
                                        <option value="fr" {{ old('region', $source->region ?? '') === 'fr' ? 'selected' : '' }}>France</option>
                                        <option value="ae" {{ old('region', $source->region ?? '') === 'ae' ? 'selected' : '' }}>Middle East</option>
                                    </select>
                                    @error('region')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Scoring -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="priority">Priority (1-10)</label>
                                    <input type="range" name="priority" id="priority" class="form-control-range" 
                                           value="{{ old('priority', $source->priority ?? 5) }}" min="1" max="10" oninput="updatePriorityValue(this.value)">
                                    <small class="text-muted">Current: <span id="priorityValue">{{ old('priority', $source->priority ?? 5) }}</span></small>
                                    @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="credibility_score">Credibility Score (0-100)</label>
                                    <input type="range" name="credibility_score" id="credibility_score" class="form-control-range" 
                                           value="{{ old('credibility_score', $source->credibility_score ?? 70) }}" min="0" max="100" oninput="updateCredValue(this.value)">
                                    <small class="text-muted">Current: <span id="credValue">{{ old('credibility_score', $source->credibility_score ?? 70) }}</span>%</small>
                                    @error('credibility_score')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="max_articles_per_fetch">Max Articles Per Fetch</label>
                                    <input type="number" name="max_articles_per_fetch" id="max_articles_per_fetch" class="form-control @error('max_articles_per_fetch') is-invalid @enderror" 
                                           value="{{ old('max_articles_per_fetch', $source->max_articles_per_fetch ?? 20) }}" min="1" max="100">
                                    @error('max_articles_per_fetch')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Category Mapping (JSON) -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="category_mapping">Category Mapping (JSON)</label>
                                    <textarea name="category_mapping" id="category_mapping" class="form-control @error('category_mapping') is-invalid @enderror" 
                                              rows="3" placeholder='{"politics": 1, "sports": 2, "technology": 3}'>{{ old('category_mapping', isset($source) ? json_encode($source->category_mapping) : '') }}</textarea>
                                    <small class="text-muted">Map source categories to your category IDs. Example: {"source_category": category_id}</small>
                                    @error('category_mapping')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Settings -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" 
                                               {{ old('is_active', $source->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Active (Enable automatic fetching)</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="auto_publish" class="custom-control-input" id="auto_publish" 
                                               {{ old('auto_publish', $source->auto_publish ?? false) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="auto_publish">Auto Publish (Automatically publish fetched articles)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <a href="{{ route('admin.aggregator.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> {{ isset($source) ? 'Update Source' : 'Create Source' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function updatePriorityValue(val) {
    document.getElementById('priorityValue').textContent = val;
}

function updateCredValue(val) {
    document.getElementById('credValue').textContent = val;
}

// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    var slug = this.value.toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
    document.getElementById('slug').value = slug;
});
</script>
@endpush
