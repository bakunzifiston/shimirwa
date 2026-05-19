<section class="site-vm" aria-labelledby="vm-heading">
    <div class="site-container">
        <header class="site-vm-header site-reveal">
            <span class="site-eyebrow">Who we are</span>
            <h2 id="vm-heading">Vision &amp; Mission</h2>
            <p>The principles that guide every harvest, batch, and delivery.</p>
        </header>

        <div class="site-vm-grid">
            <article class="site-vm-card site-vm-card--vision site-reveal">
                <div class="site-vm-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1 1 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                </div>
                <h3>{{ config('site.vision.title') }}</h3>
                <p>{{ config('site.vision.text') }}</p>
            </article>

            <article class="site-vm-card site-vm-card--mission site-reveal">
                <div class="site-vm-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-2.247m0 0A8.966 8.966 0 0 1 3 12c0-1.264.26-2.468.732-3.564"/>
                    </svg>
                </div>
                <h3>{{ config('site.mission.title') }}</h3>
                <p>{{ config('site.mission.text') }}</p>
            </article>
        </div>
    </div>
</section>
