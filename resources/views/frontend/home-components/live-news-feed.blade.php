{{-- Real-Time Live News Feed Component --}}
<div id="live-news-feed" class="live-feed-container">
    {{-- Live Status Bar --}}
    <div class="live-status-bar d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center">
            <span class="live-indicator">
                <span class="live-dot"></span>
                LIVE
            </span>
            <span class="ml-3 text-muted" id="article-count">
                <i class="fas fa-newspaper mr-1"></i>
                <span id="total-count">0</span> articles
            </span>
        </div>
        <div class="d-flex align-items-center">
            <span class="text-muted mr-3" id="last-update">Updated just now</span>
            <button class="btn btn-sm btn-outline-primary" id="refresh-feed" title="Refresh">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    {{-- Breaking News Alert --}}
    <div id="breaking-alert" class="breaking-news-alert d-none">
        <div class="alert alert-danger d-flex align-items-center mb-3">
            <span class="breaking-badge mr-2">BREAKING</span>
            <span id="breaking-title" class="breaking-text flex-grow-1"></span>
            <a href="#" id="breaking-link" class="btn btn-sm btn-light ml-2">Read More</a>
        </div>
    </div>

    {{-- Live News Grid --}}
    <div class="row" id="live-news-grid">
        {{-- News items will be inserted here --}}
        <div class="col-12 text-center py-5" id="loading-indicator">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading live news feed...</p>
        </div>
    </div>

    {{-- Load More --}}
    <div class="text-center mt-4" id="load-more-container" style="display: none;">
        <button class="btn btn-outline-primary btn-lg" id="load-more">
            <i class="fas fa-plus mr-2"></i>Load More News
        </button>
    </div>
</div>

<style>
.live-feed-container {
    position: relative;
}

.live-status-bar {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    padding: 10px 15px;
    border-radius: 8px;
    border-left: 4px solid #dc3545;
}

.live-indicator {
    display: inline-flex;
    align-items: center;
    background: #dc3545;
    color: #fff;
    padding: 4px 12px;
    border-radius: 4px;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 1px;
}

.live-dot {
    width: 8px;
    height: 8px;
    background: #fff;
    border-radius: 50%;
    margin-right: 6px;
    animation: live-pulse 1.5s infinite;
}

@keyframes live-pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.8); }
}

.breaking-news-alert .alert {
    animation: breaking-flash 2s ease-in-out infinite;
    border: none;
    border-radius: 8px;
}

@keyframes breaking-flash {
    0%, 100% { background-color: #dc3545; }
    50% { background-color: #c82333; }
}

.breaking-badge {
    background: #fff;
    color: #dc3545;
    padding: 4px 10px;
    border-radius: 4px;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 1px;
}

.breaking-text {
    color: #fff;
    font-weight: 600;
}

.news-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    height: 100%;
}

.news-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.news-card.new-article {
    animation: new-highlight 3s ease-out;
}

@keyframes new-highlight {
    0% { box-shadow: 0 0 30px rgba(40, 167, 69, 0.8); }
    100% { box-shadow: 0 2px 15px rgba(0,0,0,0.08); }
}

.news-card .card-img-top {
    height: 180px;
    object-fit: cover;
}

.news-card .source-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(0,0,0,0.7);
    color: #fff;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.news-card .breaking-badge-card {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #dc3545;
    color: #fff;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
    animation: badge-pulse 1s infinite;
}

@keyframes badge-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.news-card .card-title {
    font-size: 15px;
    font-weight: 600;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.news-card .card-text {
    font-size: 13px;
    color: #666;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.news-card .card-footer {
    background: transparent;
    border-top: 1px solid #eee;
    font-size: 12px;
}

#refresh-feed.spinning i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.no-image-placeholder {
    height: 180px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 48px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let lastId = 0;
    let isLoading = false;
    let refreshInterval;
    
    function createNewsCard(article) {
        const isNew = article.id > lastId;
        return `
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card news-card ${isNew ? 'new-article' : ''}" data-id="${article.id}">
                    <div class="position-relative">
                        ${article.image 
                            ? `<img src="${article.image}" class="card-img-top" alt="${article.title}" onerror="this.parentElement.innerHTML='<div class=\\'no-image-placeholder\\'><i class=\\'fas fa-newspaper\\'></i></div>'">`
                            : `<div class="no-image-placeholder"><i class="fas fa-newspaper"></i></div>`
                        }
                        <span class="source-badge">${article.source}</span>
                        ${article.is_breaking ? '<span class="breaking-badge-card">BREAKING</span>' : ''}
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="${article.url}" target="_blank" class="text-dark">${article.title}</a>
                        </h5>
                        <p class="card-text">${article.description || ''}</p>
                    </div>
                    <div class="card-footer text-muted">
                        <i class="far fa-clock mr-1"></i>${article.published_at}
                        ${article.is_featured ? '<span class="badge badge-warning ml-2">Featured</span>' : ''}
                    </div>
                </div>
            </div>
        `;
    }
    
    function loadNews(append = false) {
        if (isLoading) return;
        isLoading = true;
        
        const url = append 
            ? `{{ route('live-feed.latest') }}?last_id=0&limit=30`
            : `{{ route('live-feed.latest') }}?last_id=${lastId}&limit=20`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                const grid = document.getElementById('live-news-grid');
                const loading = document.getElementById('loading-indicator');
                
                if (loading) loading.style.display = 'none';
                
                // Update stats
                document.getElementById('total-count').textContent = data.total;
                document.getElementById('last-update').textContent = 'Updated just now';
                
                // Check for breaking news
                const breakingArticle = data.articles.find(a => a.is_breaking);
                if (breakingArticle) {
                    showBreakingAlert(breakingArticle);
                }
                
                if (data.articles.length > 0) {
                    if (!append && lastId === 0) {
                        grid.innerHTML = '';
                    }
                    
                    const newHtml = data.articles.map(createNewsCard).join('');
                    
                    if (append || lastId === 0) {
                        grid.innerHTML += newHtml;
                    } else {
                        grid.insertAdjacentHTML('afterbegin', newHtml);
                    }
                    
                    // Update lastId
                    if (data.articles.length > 0) {
                        const maxId = Math.max(...data.articles.map(a => a.id));
                        if (maxId > lastId) lastId = maxId;
                    }
                    
                    document.getElementById('load-more-container').style.display = 'block';
                }
                
                isLoading = false;
            })
            .catch(error => {
                console.error('Error loading news:', error);
                isLoading = false;
            });
    }
    
    function showBreakingAlert(article) {
        const alert = document.getElementById('breaking-alert');
        document.getElementById('breaking-title').textContent = article.title;
        document.getElementById('breaking-link').href = article.url;
        alert.classList.remove('d-none');
        
        // Auto-hide after 30 seconds
        setTimeout(() => {
            alert.classList.add('d-none');
        }, 30000);
    }
    
    function refreshFeed() {
        const btn = document.getElementById('refresh-feed');
        btn.classList.add('spinning');
        
        fetch(`{{ route('live-feed.latest') }}?last_id=${lastId}&limit=10`)
            .then(response => response.json())
            .then(data => {
                if (data.articles.length > 0) {
                    const grid = document.getElementById('live-news-grid');
                    const newHtml = data.articles.map(createNewsCard).join('');
                    grid.insertAdjacentHTML('afterbegin', newHtml);
                    
                    const maxId = Math.max(...data.articles.map(a => a.id));
                    if (maxId > lastId) lastId = maxId;
                    
                    document.getElementById('total-count').textContent = data.total;
                }
                document.getElementById('last-update').textContent = 'Updated just now';
                btn.classList.remove('spinning');
            })
            .catch(() => {
                btn.classList.remove('spinning');
            });
    }
    
    // Initial load
    loadNews();
    
    // Auto-refresh every 10 seconds
    refreshInterval = setInterval(refreshFeed, 10000);
    
    // Manual refresh
    document.getElementById('refresh-feed').addEventListener('click', refreshFeed);
    
    // Load more
    document.getElementById('load-more').addEventListener('click', function() {
        loadNews(true);
    });
    
    // Update "updated X seconds ago" text
    setInterval(() => {
        const el = document.getElementById('last-update');
        const text = el.textContent;
        if (text === 'Updated just now') {
            el.textContent = 'Updated 10s ago';
        } else if (text.includes('s ago')) {
            const seconds = parseInt(text) + 10;
            if (seconds >= 60) {
                el.textContent = 'Updated 1m ago';
            } else {
                el.textContent = `Updated ${seconds}s ago`;
            }
        }
    }, 10000);
});
</script>
