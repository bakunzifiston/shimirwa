<footer class="site-footer" role="contentinfo">
    <div class="site-container site-footer-grid">
        <div>
            <a href="{{ route('home') }}" class="site-brand" style="color:#fff;margin-bottom:1rem">
                {{ config('site.name') }}
            </a>
            <p style="margin:0;max-width:20rem;color:#94a3b8;font-size:0.9375rem">
                {{ config('site.tagline') }}
            </p>
            <div class="site-social">
                @foreach (config('site.social') as $social)
                    <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" title="{{ $social['label'] }}">
                        {{ strtoupper(substr($social['label'], 0, 1)) }}
                    </a>
                @endforeach
            </div>
        </div>
        <div>
            <h3>Quick links</h3>
            <ul>
                @foreach (config('site.navigation') as $item)
                    <li><a href="{{ route($item['route']) }}">{{ $item['label'] }}</a></li>
                @endforeach
                <li><a href="{{ route('admin.login', absolute: false) }}">Admin login</a></li>
            </ul>
        </div>
        <div>
            <h3>Contact</h3>
            <ul>
                <li><a href="mailto:{{ config('site.contact.email') }}">{{ config('site.contact.email') }}</a></li>
                <li>{{ config('site.contact.phone') }}</li>
                <li>{{ config('site.contact.address') }}</li>
            </ul>
        </div>
    </div>
    <div class="site-container site-footer-bottom">
        <p>&copy; {{ date('Y') }} {{ config('site.name') }}. All rights reserved.</p>
    </div>
</footer>
