<div class="admin-product-form">

    {{-- Basic information --}}
    <section class="admin-form-panel">
        <header class="admin-form-panel-head">
            <span class="admin-form-panel-icon" aria-hidden="true">
                <x-admin.icon name="package" class="!h-5 !w-5" />
            </span>
            <div>
                <h3 class="admin-form-panel-title">Event details</h3>
                <p class="admin-form-panel-desc">Title, date, location, and description shown on the events page</p>
            </div>
        </header>
        <div class="admin-form-panel-body">
            <div class="admin-form-grid">

                <div class="span-2">
                    <label class="admin-label" for="title">Event title <span class="admin-label-required">*</span></label>
                    <input id="title" name="title" class="admin-input @error('title') admin-input-error @enderror"
                           value="{{ old('title', $event->title) }}" required
                           placeholder="e.g. Farmer Training Day – Rulindo 2024">
                    @error('title')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

                <div class="span-2">
                    <label class="admin-label" for="slug">URL slug</label>
                    <input id="slug" name="slug" class="admin-input @error('slug') admin-input-error @enderror"
                           value="{{ old('slug', $event->slug) }}" placeholder="Auto-generated if empty">
                    <p class="admin-field-hint">Public link: /events/your-slug</p>
                    @error('slug')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="admin-label" for="event_date">Event date</label>
                    <input type="date" id="event_date" name="event_date"
                           class="admin-input @error('event_date') admin-input-error @enderror"
                           value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}">
                    @error('event_date')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="admin-label" for="location">Location</label>
                    <input id="location" name="location"
                           class="admin-input @error('location') admin-input-error @enderror"
                           value="{{ old('location', $event->location) }}"
                           placeholder="e.g. Rulindo District, Rwanda">
                    @error('location')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="admin-label" for="status">Status <span class="admin-label-required">*</span></label>
                    <select id="status" name="status" class="admin-input @error('status') admin-input-error @enderror">
                        <option value="draft"     @selected(old('status', $event->status) === 'draft')>Draft — not visible on website</option>
                        <option value="published" @selected(old('status', $event->status) === 'published')>Published — live on website</option>
                    </select>
                    @error('status')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

                <div class="span-2">
                    <label class="admin-label" for="description">Description</label>
                    <textarea id="description" name="description"
                              class="admin-textarea @error('description') admin-input-error @enderror"
                              rows="5"
                              placeholder="What happened at this event? Who attended?">{{ old('description', $event->description) }}</textarea>
                    @error('description')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

            </div>
        </div>
    </section>

    {{-- Cover image --}}
    <section class="admin-form-panel">
        <header class="admin-form-panel-head">
            <span class="admin-form-panel-icon admin-form-panel-icon--accent" aria-hidden="true">
                <x-admin.icon name="package" class="!h-5 !w-5" />
            </span>
            <div>
                <h3 class="admin-form-panel-title">Cover image</h3>
                <p class="admin-form-panel-desc">Thumbnail shown on the events listing (max 10 MB)</p>
            </div>
        </header>
        <div class="admin-form-panel-body">
            @if($event->coverImageUrl())
                <div style="margin-bottom:1rem">
                    <img src="{{ $event->coverImageUrl() }}" alt="Current cover"
                         style="height:160px;width:auto;max-width:100%;border-radius:8px;object-fit:cover;border:1px solid var(--admin-border)">
                    <p style="font-size:.78rem;margin-top:.4rem;color:var(--admin-text-subtle)">Upload a new file to replace</p>
                </div>
            @endif
            <input type="file" id="cover_image" name="cover_image" accept="image/*"
                   class="admin-input @error('cover_image') admin-input-error @enderror">
            @error('cover_image')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
    </section>

    {{-- Photos & Videos --}}
    <section class="admin-form-panel">
        <header class="admin-form-panel-head">
            <span class="admin-form-panel-icon" aria-hidden="true">
                <x-admin.icon name="package" class="!h-5 !w-5" />
            </span>
            <div>
                <h3 class="admin-form-panel-title">Event Photos &amp; Videos</h3>
                <p class="admin-form-panel-desc">Upload multiple photos and/or videos from this event — JPG, PNG, WebP, MP4, MOV — max 100 MB per file</p>
            </div>
        </header>
        <div class="admin-form-panel-body">

            {{-- Existing media --}}
            @if($event->exists && $event->media->isNotEmpty())
                <p style="font-size:.8rem;font-weight:700;color:var(--admin-text-subtle);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.6rem">
                    {{ $event->media->count() }} existing file(s) — click × to remove
                </p>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:.65rem;margin-bottom:1.5rem">
                    @foreach($event->media as $item)
                        <div style="position:relative;border-radius:8px;overflow:hidden;border:1px solid var(--admin-border)">
                            @if($item->isImage())
                                <img src="{{ $item->url() }}" alt=""
                                     style="width:100%;aspect-ratio:1;object-fit:cover;display:block">
                            @else
                                <div style="aspect-ratio:1;display:flex;align-items:center;justify-content:center;
                                            background:linear-gradient(135deg,#1e293b,#334155)">
                                    <svg width="26" height="26" fill="none" stroke="rgba(255,255,255,.5)" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/>
                                    </svg>
                                </div>
                                <span style="position:absolute;bottom:.25rem;left:.25rem;
                                             font-size:.55rem;font-weight:800;text-transform:uppercase;
                                             background:rgba(16,73,140,.85);color:white;padding:.15rem .4rem;border-radius:4px">VIDEO</span>
                            @endif
                            <form method="POST"
                                  action="{{ route('admin.events.media.destroy', [$event, $item]) }}"
                                  onsubmit="return confirm('Remove this file?')"
                                  style="position:absolute;top:.25rem;right:.25rem">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        style="width:1.5rem;height:1.5rem;border-radius:50%;border:none;cursor:pointer;
                                               background:rgba(239,68,68,.9);color:white;
                                               display:flex;align-items:center;justify-content:center">
                                    <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Drop zone --}}
            <div id="ev-drop-zone"
                 style="border:2px dashed var(--admin-border);border-radius:10px;padding:2.25rem 1.5rem;
                        text-align:center;cursor:pointer;transition:border-color .2s,background .2s"
                 onclick="document.getElementById('ev-upload').click()"
                 ondragover="event.preventDefault();this.style.borderColor='var(--admin-primary)';this.style.background='rgba(16,73,140,.04)'"
                 ondragleave="this.style.borderColor='';this.style.background=''"
                 ondrop="evDrop(event)">
                <svg width="40" height="40" fill="none" stroke="var(--admin-text-subtle)" stroke-width="1.5" viewBox="0 0 24 24"
                     style="margin:0 auto .85rem" aria-hidden="true">
                    <path stroke-linecap="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                </svg>
                <p style="font-size:.9rem;font-weight:700;color:var(--admin-text);margin-bottom:.3rem">
                    Click or drag &amp; drop photos and videos
                </p>
                <p style="font-size:.78rem;color:var(--admin-text-subtle)">
                    JPG · PNG · WebP · MP4 · MOV · up to 100 MB each · multiple files supported
                </p>
            </div>

            <input type="file" id="ev-upload" name="media[]"
                   accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime"
                   multiple style="display:none"
                   onchange="evPreview(this.files)">

            <div id="ev-preview"
                 style="display:none;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:.6rem;margin-top:.85rem">
            </div>
            @error('media.*')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
    </section>
</div>

<script>
function evPreview(files) {
    const grid = document.getElementById('ev-preview');
    grid.innerHTML = '';
    if (!files.length) { grid.style.display = 'none'; return; }
    grid.style.display = 'grid';
    Array.from(files).forEach(file => {
        const wrap = document.createElement('div');
        wrap.style.cssText = 'position:relative;border-radius:8px;overflow:hidden;border:1px solid var(--admin-border);background:var(--admin-bg)';
        if (file.type.startsWith('video/')) {
            const box = document.createElement('div');
            box.style.cssText = 'aspect-ratio:1;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#1e293b,#334155)';
            box.innerHTML = '<svg width="22" height="22" fill="none" stroke="rgba(255,255,255,.55)" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/></svg>';
            const lbl = document.createElement('span');
            lbl.style.cssText = 'position:absolute;bottom:.25rem;left:.25rem;font-size:.55rem;font-weight:800;text-transform:uppercase;background:rgba(16,73,140,.85);color:white;padding:.15rem .4rem;border-radius:4px';
            lbl.textContent = 'VIDEO';
            wrap.appendChild(box); wrap.appendChild(lbl);
        } else {
            const img = document.createElement('img');
            img.style.cssText = 'width:100%;aspect-ratio:1;object-fit:cover;display:block';
            const r = new FileReader();
            r.onload = e => { img.src = e.target.result; };
            r.readAsDataURL(file);
            wrap.appendChild(img);
        }
        const nm = document.createElement('p');
        nm.style.cssText = 'font-size:.58rem;padding:.2rem .3rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--admin-text-subtle)';
        nm.textContent = file.name;
        wrap.appendChild(nm);
        grid.appendChild(wrap);
    });
}
function evDrop(e) {
    e.preventDefault();
    e.currentTarget.style.borderColor = '';
    e.currentTarget.style.background = '';
    const input = document.getElementById('ev-upload');
    const dt = new DataTransfer();
    Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
    input.files = dt.files;
    evPreview(input.files);
}
</script>
