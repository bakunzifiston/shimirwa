@php
    $statuses = [\App\Models\Product::STATUS_ACTIVE => 'Active', \App\Models\Product::STATUS_INACTIVE => 'Inactive'];
@endphp

<div class="admin-product-form">
    {{-- Basic information --}}
    <section class="admin-form-panel">
        <header class="admin-form-panel-head">
            <span class="admin-form-panel-icon" aria-hidden="true">
                <x-admin.icon name="package" class="!h-5 !w-5" />
            </span>
            <div>
                <h3 class="admin-form-panel-title">Basic information</h3>
                <p class="admin-form-panel-desc">Name and description shown on the shop</p>
            </div>
        </header>
        <div class="admin-form-panel-body">
            <div class="admin-form-grid">
                <div class="span-2">
                    <label class="admin-label" for="name">Product name <span class="admin-label-required">*</span></label>
                    <input id="name" name="name" class="admin-input @error('name') admin-input-error @enderror"
                           value="{{ old('name', $product->name) }}" required placeholder="e.g. Premium Soy Flour — 1kg">
                    @error('name')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
                <div class="span-2">
                    <label class="admin-label" for="slug">URL slug</label>
                    <input id="slug" name="slug" class="admin-input @error('slug') admin-input-error @enderror"
                           value="{{ old('slug', $product->slug) }}" placeholder="Auto-generated from name if empty">
                    <p class="admin-field-hint">Used in the shop link: /shop/your-slug</p>
                    @error('slug')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
                <div class="span-2">
                    <label class="admin-label" for="description">Description</label>
                    <textarea id="description" name="description" class="admin-textarea @error('description') admin-input-error @enderror"
                              rows="5" placeholder="Full product description for customers…">{{ old('description', $product->description) }}</textarea>
                    @error('description')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </section>

    {{-- Pricing & stock --}}
    <section class="admin-form-panel">
        <header class="admin-form-panel-head">
            <span class="admin-form-panel-icon admin-form-panel-icon--accent" aria-hidden="true">
                <x-admin.icon name="chart" class="!h-5 !w-5" />
            </span>
            <div>
                <h3 class="admin-form-panel-title">Pricing &amp; stock</h3>
                <p class="admin-form-panel-desc">Prices in RWF; stock updates when orders are placed</p>
            </div>
        </header>
        <div class="admin-form-panel-body">
            <div class="admin-form-grid">
                <div>
                    <label class="admin-label" for="price">Price (RWF) <span class="admin-label-required">*</span></label>
                    <div class="admin-input-group">
                        <span class="admin-input-prefix">RWF</span>
                        <input id="price" type="number" step="0.01" min="0" name="price"
                               class="admin-input admin-input--prefixed @error('price') admin-input-error @enderror"
                               value="{{ old('price', $product->price) }}" required placeholder="0">
                    </div>
                    @error('price')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="admin-label" for="discount_price">Discount price</label>
                    <div class="admin-input-group">
                        <span class="admin-input-prefix">RWF</span>
                        <input id="discount_price" type="number" step="0.01" min="0" name="discount_price"
                               class="admin-input admin-input--prefixed @error('discount_price') admin-input-error @enderror"
                               value="{{ old('discount_price', $product->discount_price) }}" placeholder="Optional">
                    </div>
                    <p class="admin-field-hint">Must be lower than regular price</p>
                    @error('discount_price')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="admin-label" for="stock_quantity">Stock quantity <span class="admin-label-required">*</span></label>
                    <input id="stock_quantity" type="number" min="0" name="stock_quantity"
                           class="admin-input @error('stock_quantity') admin-input-error @enderror"
                           value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required>
                    @error('stock_quantity')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </section>

    {{-- Images --}}
    <section class="admin-form-panel">
        <header class="admin-form-panel-head">
            <span class="admin-form-panel-icon" aria-hidden="true">
                <x-admin.icon name="box" class="!h-5 !w-5" />
            </span>
            <div>
                <h3 class="admin-form-panel-title">Images</h3>
                <p class="admin-form-panel-desc">Upload product photos for the shop listing and detail page</p>
            </div>
        </header>
        <div class="admin-form-panel-body">
            <label class="admin-file-upload @error('images') admin-file-upload--error @enderror">
                <span class="admin-file-upload-icon" aria-hidden="true">+</span>
                <span class="admin-file-upload-text"><strong>Click to upload</strong> or drag files here</span>
                <span class="admin-file-upload-hint">JPEG, PNG, WebP or GIF · Max 10 MB each · Up to 10 images</span>
                <input id="images" type="file" name="images[]" class="admin-file-upload-input"
                       accept="image/jpeg,image/png,image/webp,image/gif" multiple>
            </label>
            @error('images')<p class="admin-field-error">{{ $message }}</p>@enderror
            @error('images.*')<p class="admin-field-error">{{ $message }}</p>@enderror

            {{-- New image preview grid (populated by JS) --}}
            <div id="new-image-preview" class="admin-image-grid" style="margin-top:1rem;display:none"></div>

            @if ($product->exists && $product->images->isNotEmpty())
                <p class="admin-label" style="margin-top:1.25rem">Current images</p>
                <div class="admin-image-grid">
                    @foreach ($product->images as $image)
                        <label class="admin-image-tile">
                            <img src="{{ $image->url() }}" alt="">
                            <span class="admin-image-tile-remove">
                                <input type="checkbox" name="remove_images[]" value="{{ $image->id }}">
                                <span>Remove</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif

<script>
(function () {
    const input   = document.getElementById('images');
    const preview = document.getElementById('new-image-preview');
    if (!input || !preview) return;

    input.addEventListener('change', function () {
        preview.innerHTML = '';
        const files = Array.from(this.files);
        if (!files.length) { preview.style.display = 'none'; return; }

        preview.style.display = '';
        files.forEach(function (file) {
            if (!file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                const tile = document.createElement('div');
                tile.className = 'admin-image-tile';
                tile.style.position = 'relative';

                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = file.name;
                img.style.cssText = 'width:100%;height:100%;object-fit:cover;border-radius:inherit';

                const badge = document.createElement('span');
                badge.textContent = 'New';
                badge.style.cssText = 'position:absolute;top:4px;left:4px;font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;background:#10498C;color:#fff;pointer-events:none';

                tile.appendChild(img);
                tile.appendChild(badge);
                preview.appendChild(tile);
            };
            reader.readAsDataURL(file);
        });
    });
})();
</script>
        </div>
    </section>

    {{-- Status --}}
    <section class="admin-form-panel">
        <header class="admin-form-panel-head">
            <span class="admin-form-panel-icon admin-form-panel-icon--muted" aria-hidden="true">
                <x-admin.icon name="filter" class="!h-5 !w-5" />
            </span>
            <div>
                <h3 class="admin-form-panel-title">Status</h3>
                <p class="admin-form-panel-desc">Control visibility on the public shop</p>
            </div>
        </header>
        <div class="admin-form-panel-body">
            <div class="admin-status-options">
                @foreach ($statuses as $value => $label)
                    <label class="admin-status-option">
                        <input type="radio" name="status" value="{{ $value }}"
                               @checked(old('status', $product->status ?? 'active') === $value) required>
                        <span class="admin-status-option-box">
                            <span class="admin-status-option-title">{{ $label }}</span>
                            <span class="admin-status-option-desc">
                                {{ $value === 'active' ? 'Visible on the shop' : 'Hidden from customers' }}
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('status')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>
    </section>
</div>
