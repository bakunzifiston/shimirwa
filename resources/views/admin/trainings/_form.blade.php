<div class="admin-product-form">

    {{-- Basic information --}}
    <section class="admin-form-panel">
        <header class="admin-form-panel-head">
            <span class="admin-form-panel-icon" aria-hidden="true">
                <x-admin.icon name="package" class="!h-5 !w-5" />
            </span>
            <div>
                <h3 class="admin-form-panel-title">Module information</h3>
                <p class="admin-form-panel-desc">Title, category and short summary shown on the training listing page</p>
            </div>
        </header>
        <div class="admin-form-panel-body">
            <div class="admin-form-grid">
                <div class="span-2">
                    <label class="admin-label" for="title">Title <span class="admin-label-required">*</span></label>
                    <input id="title" name="title" class="admin-input @error('title') admin-input-error @enderror"
                           value="{{ old('title', $training->title) }}" required placeholder="e.g. How to Use Soy Flour in Baking">
                    @error('title')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

                <div class="span-2">
                    <label class="admin-label" for="slug">URL slug</label>
                    <input id="slug" name="slug" class="admin-input @error('slug') admin-input-error @enderror"
                           value="{{ old('slug', $training->slug) }}" placeholder="Auto-generated from title if empty">
                    <p class="admin-field-hint">Public link: /training/your-slug</p>
                    @error('slug')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="admin-label" for="category">Category <span class="admin-label-required">*</span></label>
                    <select id="category" name="category" class="admin-input @error('category') admin-input-error @enderror">
                        @foreach(\App\Models\Training::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" @selected(old('category', $training->category) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="admin-label" for="status">Status <span class="admin-label-required">*</span></label>
                    <select id="status" name="status" class="admin-input @error('status') admin-input-error @enderror">
                        <option value="draft"     @selected(old('status', $training->status) === 'draft')>Draft — not visible on website</option>
                        <option value="published" @selected(old('status', $training->status) === 'published')>Published — live on website</option>
                    </select>
                    <p class="admin-field-hint">Set to Published to make it visible to the public.</p>
                    @error('status')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>

                <div class="span-2">
                    <label class="admin-label" for="excerpt">Short summary</label>
                    <textarea id="excerpt" name="excerpt"
                              class="admin-textarea @error('excerpt') admin-input-error @enderror"
                              rows="3" maxlength="500"
                              placeholder="One or two sentences shown on the training card (max 500 characters)">{{ old('excerpt', $training->excerpt) }}</textarea>
                    @error('excerpt')<p class="admin-field-error">{{ $message }}</p>@enderror
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
                <p class="admin-form-panel-desc">Thumbnail shown on the listing card (recommended 16:9, max 8 MB)</p>
            </div>
        </header>
        <div class="admin-form-panel-body">
            @if($training->coverImageUrl())
                <div style="margin-bottom:1rem">
                    <img src="{{ $training->coverImageUrl() }}" alt="Current cover"
                         style="height:160px;width:auto;max-width:100%;border-radius:8px;object-fit:cover;border:1px solid var(--admin-border)">
                    <p style="font-size:.78rem;margin-top:.4rem;color:var(--admin-text-subtle)">Current cover — upload a new file to replace</p>
                </div>
            @endif
            <input type="file" id="cover_image" name="cover_image" accept="image/*"
                   class="admin-input @error('cover_image') admin-input-error @enderror">
            @error('cover_image')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
    </section>

    {{-- Body content --}}
    <section class="admin-form-panel">
        <header class="admin-form-panel-head">
            <span class="admin-form-panel-icon" aria-hidden="true">
                <x-admin.icon name="chart" class="!h-5 !w-5" />
            </span>
            <div>
                <h3 class="admin-form-panel-title">Full content</h3>
                <p class="admin-form-panel-desc">The main body text. Press Enter for new paragraphs.</p>
            </div>
        </header>
        <div class="admin-form-panel-body">
            <textarea id="body" name="body"
                      class="admin-textarea @error('body') admin-input-error @enderror"
                      rows="14"
                      placeholder="Write the training content here…">{{ old('body', $training->body) }}</textarea>
            @error('body')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
    </section>

    {{-- Photos & Videos --}}
    <section class="admin-form-panel">
        <header class="admin-form-panel-head">
            <span class="admin-form-panel-icon" aria-hidden="true">
                <x-admin.icon name="package" class="!h-5 !w-5" />
            </span>
            <div>
                <h3 class="admin-form-panel-title">Photos &amp; Videos</h3>
                <p class="admin-form-panel-desc">Upload event photos and videos. Accepted: JPG, PNG, WebP, GIF, MP4, WebM, MOV — max 100 MB per file</p>
            </div>
        </header>
        <div class="admin-form-panel-body">

            {{-- Existing media (edit mode) --}}
            @if($training->exists && $training->media->isNotEmpty())
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:.75rem;margin-bottom:1.5rem">
                    @foreach($training->media as $item)
                        <div style="position:relative;border-radius:8px;overflow:hidden;border:1px solid var(--admin-border);background:var(--admin-bg)">
                            @if($item->isImage())
                                <img src="{{ $item->url() }}" alt=""
                                     style="width:100%;aspect-ratio:1;object-fit:cover;display:block">
                            @else
                                <div style="aspect-ratio:1;display:flex;align-items:center;justify-content:center;
                                            background:linear-gradient(135deg,#1e293b,#334155)">
                                    <svg width="28" height="28" fill="none" stroke="rgba(255,255,255,.6)" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/>
                                    </svg>
                                </div>
                                <span style="position:absolute;top:.3rem;left:.3rem;
                                             font-size:.55rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;
                                             background:rgba(16,73,140,.85);color:white;
                                             padding:.15rem .4rem;border-radius:4px">VIDEO</span>
                            @endif
                            {{-- Delete button --}}
                            <form method="POST"
                                  action="{{ route('admin.trainings.media.destroy', [$training, $item]) }}"
                                  onsubmit="return confirm('Remove this media item?')"
                                  style="position:absolute;top:.3rem;right:.3rem">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        style="width:1.6rem;height:1.6rem;border-radius:50%;border:none;cursor:pointer;
                                               background:rgba(239,68,68,.9);color:white;display:flex;align-items:center;justify-content:center"
                                        title="Remove">
                                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Upload new media --}}
            <div id="media-drop-zone"
                 style="border:2px dashed var(--admin-border);border-radius:10px;padding:2rem 1.5rem;
                        text-align:center;cursor:pointer;transition:border-color .2s,background .2s"
                 onclick="document.getElementById('media-upload').click()"
                 ondragover="event.preventDefault();this.style.borderColor='var(--admin-primary)';this.style.background='rgba(16,73,140,.04)'"
                 ondragleave="this.style.borderColor='';this.style.background=''"
                 ondrop="handleDrop(event)">
                <svg width="36" height="36" fill="none" stroke="var(--admin-text-subtle)" stroke-width="1.5" viewBox="0 0 24 24"
                     style="margin:0 auto .75rem" aria-hidden="true">
                    <path stroke-linecap="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                </svg>
                <p style="font-size:.875rem;font-weight:600;color:var(--admin-text);margin-bottom:.25rem">
                    Click to upload or drag &amp; drop
                </p>
                <p style="font-size:.78rem;color:var(--admin-text-subtle)">
                    Photos (JPG/PNG/WebP) and Videos (MP4/WebM/MOV) — up to 100 MB each
                </p>
            </div>

            <input type="file" id="media-upload" name="media[]"
                   accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime"
                   multiple style="display:none"
                   onchange="previewMedia(this.files)">

            {{-- Preview strip --}}
            <div id="media-preview"
                 style="display:none;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:.6rem;margin-top:.85rem">
            </div>

            @error('media.*')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
    </section>
</div>

<script>
function previewMedia(files) {
    const grid = document.getElementById('media-preview');
    grid.innerHTML = '';
    if (!files.length) { grid.style.display = 'none'; return; }
    grid.style.display = 'grid';

    Array.from(files).forEach(file => {
        const wrap = document.createElement('div');
        wrap.style.cssText = 'position:relative;border-radius:8px;overflow:hidden;border:1px solid var(--admin-border);background:var(--admin-bg)';

        if (file.type.startsWith('video/')) {
            const icon = document.createElement('div');
            icon.style.cssText = 'aspect-ratio:1;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#1e293b,#334155)';
            icon.innerHTML = '<svg width="24" height="24" fill="none" stroke="rgba(255,255,255,.6)" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/></svg>';
            const label = document.createElement('span');
            label.style.cssText = 'position:absolute;bottom:.3rem;left:.3rem;font-size:.55rem;font-weight:800;text-transform:uppercase;background:rgba(16,73,140,.85);color:white;padding:.15rem .4rem;border-radius:4px';
            label.textContent = 'VIDEO';
            wrap.appendChild(icon);
            wrap.appendChild(label);
        } else {
            const img = document.createElement('img');
            img.style.cssText = 'width:100%;aspect-ratio:1;object-fit:cover;display:block';
            const reader = new FileReader();
            reader.onload = e => { img.src = e.target.result; };
            reader.readAsDataURL(file);
            wrap.appendChild(img);
        }

        const name = document.createElement('p');
        name.style.cssText = 'font-size:.6rem;padding:.2rem .3rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--admin-text-subtle)';
        name.textContent = file.name;
        wrap.appendChild(name);
        grid.appendChild(wrap);
    });
}

function handleDrop(e) {
    e.preventDefault();
    e.currentTarget.style.borderColor = '';
    e.currentTarget.style.background = '';
    const input = document.getElementById('media-upload');
    const dt = new DataTransfer();
    Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
    input.files = dt.files;
    previewMedia(input.files);
}
</script>
