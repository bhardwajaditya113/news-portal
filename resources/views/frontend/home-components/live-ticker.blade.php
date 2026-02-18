<!-- Real-time Breaking News Ticker (Bloomberg/CNN Style) -->
<div class="breaking-ticker bg-danger text-white py-2" id="breaking-ticker">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-auto">
                <span class="ticker-badge">
                    <i class="fas fa-bolt mr-1"></i> BREAKING
                </span>
            </div>
            <div class="col">
                <div class="ticker-wrapper">
                    <div class="ticker-content" id="ticker-content">
                        @if(isset($liveTickerNews) && $liveTickerNews->count() > 0)
                            @foreach($liveTickerNews as $news)
                                <a href="{{ $news->original_url }}" target="_blank" class="ticker-news-item text-white text-decoration-none">
                                    @if($news->is_breaking)<span class="badge bg-warning text-dark mr-1">BREAKING</span>@endif
                                    {{ Str::limit($news->title, 100) }}
                                    <small class="opacity-75">({{ $news->source->name ?? 'News' }} • {{ $news->published_at->diffForHumans() }})</small>
                                </a>
                                <span class="mx-3">•</span>
                            @endforeach
                        @else
                            Loading breaking news...
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-auto d-none d-md-block">
                <span class="ticker-time" id="ticker-time"></span>
            </div>
        </div>
    </div>
</div>

<!-- Live Stock/Crypto Ticker (Bloomberg Style) -->
<div class="market-ticker bg-dark text-white py-1">
    <div class="container-fluid">
        <div class="ticker-scroll" id="market-ticker">
            <div class="ticker-item">
                <span class="ticker-symbol">S&P 500</span>
                <span class="ticker-value">4,567.80</span>
                <span class="ticker-change positive">+0.42%</span>
            </div>
            <div class="ticker-item">
                <span class="ticker-symbol">NASDAQ</span>
                <span class="ticker-value">14,234.50</span>
                <span class="ticker-change positive">+0.67%</span>
            </div>
            <div class="ticker-item">
                <span class="ticker-symbol">DOW</span>
                <span class="ticker-value">35,678.90</span>
                <span class="ticker-change negative">-0.12%</span>
            </div>
            <div class="ticker-item">
                <span class="ticker-symbol">SENSEX</span>
                <span class="ticker-value">72,456.30</span>
                <span class="ticker-change positive">+0.89%</span>
            </div>
            <div class="ticker-item">
                <span class="ticker-symbol">NIFTY</span>
                <span class="ticker-value">21,890.50</span>
                <span class="ticker-change positive">+0.78%</span>
            </div>
            <div class="ticker-item">
                <span class="ticker-symbol">BTC</span>
                <span class="ticker-value">$67,234</span>
                <span class="ticker-change positive">+2.34%</span>
            </div>
            <div class="ticker-item">
                <span class="ticker-symbol">ETH</span>
                <span class="ticker-value">$3,456</span>
                <span class="ticker-change positive">+1.89%</span>
            </div>
            <div class="ticker-item">
                <span class="ticker-symbol">GOLD</span>
                <span class="ticker-value">$2,345.60</span>
                <span class="ticker-change negative">-0.15%</span>
            </div>
            <div class="ticker-item">
                <span class="ticker-symbol">USD/INR</span>
                <span class="ticker-value">83.25</span>
                <span class="ticker-change negative">-0.08%</span>
            </div>
        </div>
    </div>
</div>

<style>
/* Breaking News Ticker */
.breaking-ticker {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}

.ticker-badge {
    background: #fff;
    color: #dc3545;
    padding: 4px 12px;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 1px;
    border-radius: 2px;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.ticker-wrapper {
    overflow: hidden;
    white-space: nowrap;
}

.ticker-content {
    display: inline-block;
    animation: ticker-scroll 30s linear infinite;
    padding-left: 100%;
}

@keyframes ticker-scroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-100%); }
}

.ticker-time {
    font-size: 12px;
    opacity: 0.9;
}

/* Market Ticker */
.market-ticker {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.ticker-scroll {
    display: flex;
    overflow: hidden;
    animation: market-scroll 40s linear infinite;
}

@keyframes market-scroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

.ticker-item {
    display: flex;
    align-items: center;
    padding: 0 20px;
    border-right: 1px solid rgba(255,255,255,0.1);
    white-space: nowrap;
}

.ticker-symbol {
    font-weight: 600;
    font-size: 11px;
    color: #94a3b8;
    margin-right: 8px;
}

.ticker-value {
    font-weight: 500;
    font-size: 12px;
    margin-right: 6px;
}

.ticker-change {
    font-size: 11px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 3px;
}

.ticker-change.positive {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.ticker-change.negative {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}
</style>

<script>
// Update breaking news ticker
function updateBreakingTicker() {
    fetch('/api/breaking-news')
        .then(r => r.json())
        .then(data => {
            if (data.length > 0) {
                const content = data.map(n => `<span class="ticker-news-item">${n.title}</span>`).join(' <span class="mx-3">•</span> ');
                document.getElementById('ticker-content').innerHTML = content;
            }
        })
        .catch(() => {});
}

// Update time
function updateTickerTime() {
    const now = new Date();
    document.getElementById('ticker-time').textContent = now.toLocaleTimeString('en-US', { 
        hour: '2-digit', minute: '2-digit', second: '2-digit' 
    });
}

setInterval(updateTickerTime, 1000);
updateTickerTime();
</script>
