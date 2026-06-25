<footer class="site-footer" role="contentinfo" style="background:var(--site-primary-dark);color:#e2e8f0;padding:3rem 0 0">
    <div class="site-container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(14rem,1fr));gap:2.5rem;padding-bottom:2.5rem">
        {{-- Brand --}}
        <div>
            <a href="{{ route('home') }}"
               class="inline-block text-xl font-bold text-white mb-3 hover:text-blue-200 transition-colors">
                {{ config('site.name') }}
            </a>
            <p class="text-sm leading-relaxed" style="color:#94a3b8;max-width:20rem">
                {{ config('site.tagline') }}
            </p>
            {{-- Social --}}
            <div class="flex gap-2 mt-4">
                @foreach (config('site.social') as $social)
                    <a href="{{ $social['url'] }}"
                       target="_blank" rel="noopener noreferrer"
                       title="{{ $social['label'] }}"
                       class="w-9 h-9 flex items-center justify-center rounded-full text-sm font-bold
                              bg-white/10 text-white hover:bg-white/20 transition-colors">
                        {{ strtoupper(substr($social['label'], 0, 1)) }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Quick links --}}
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400 mb-4">Quick Links</h3>
            <ul class="space-y-2 text-sm">
                @foreach (config('site.navigation') as $item)
                    <li>
                        <a href="{{ route($item['route']) }}"
                           class="text-slate-300 hover:text-white transition-colors">
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
                <li>
                    <a href="{{ route('admin.login', absolute: false) }}"
                       class="text-slate-300 hover:text-white transition-colors">
                        Admin login
                    </a>
                </li>
            </ul>
        </div>

        {{-- Contact --}}
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400 mb-4">Contact</h3>
            <ul class="space-y-2 text-sm text-slate-300">
                <li>
                    <a href="mailto:{{ config('site.contact.email') }}"
                       class="hover:text-white transition-colors flex items-center gap-2">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z"/></svg>
                        {{ config('site.contact.email') }}
                    </a>
                </li>
                <li class="flex items-center gap-2">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M3 5a2 2 0 0 1 2-2h3l2 4-2.5 1.5a11 11 0 0 0 5 5L14 11l4 2v3a2 2 0 0 1-2 2A16 16 0 0 1 3 5z"/></svg>
                    {{ config('site.contact.phone') }}
                </li>
                <li class="flex items-start gap-2">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="mt-0.5 shrink-0" aria-hidden="true"><path stroke-linecap="round" d="M17.657 16.657L13.414 20.9a2 2 0 0 1-2.827 0L6.343 16.657a8 8 0 1 1 11.314 0z"/><circle cx="12" cy="11" r="3"/></svg>
                    {{ config('site.contact.address') }}
                </li>
            </ul>
        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="site-container border-t py-4 flex items-center justify-between text-xs text-slate-500"
         style="border-color:rgba(255,255,255,0.1)">
        <p>&copy; {{ date('Y') }} {{ config('site.name') }}. All rights reserved.</p>
        <p>Made with care in Rwanda 🇷🇼</p>
    </div>
</footer>
