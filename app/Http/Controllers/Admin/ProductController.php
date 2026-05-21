<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();

        $products = Product::query()
            ->with('images')
            ->withCount('images')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->when(in_array($status, [Product::STATUS_ACTIVE, Product::STATUS_INACTIVE], true), fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'search', 'status'));
    }

    public function create(): View
    {
        $product = new Product(['status' => Product::STATUS_ACTIVE, 'stock_quantity' => 0]);

        return view('admin.products.create', compact('product'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['images']);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);

        $product = Product::create($data);
        $imageCount = $this->storeImages($product, $request->file('images'));

        $message = 'Product created successfully.';
        if ($imageCount > 0) {
            $message .= " {$imageCount} image(s) uploaded.";
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', $message);
    }

    public function show(Product $product): View
    {
        $product->load('images');

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $product->load('images');

        return view('admin.products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        unset($data['images'], $data['remove_images']);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name'], $product->id);

        $product->update($data);

        foreach ($request->input('remove_images', []) as $imageId) {
            $image = ProductImage::query()->where('product_id', $product->id)->find($imageId);
            if ($image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
        }

        $imageCount = $this->storeImages($product, $request->file('images'));

        $message = 'Product updated successfully.';
        if ($imageCount > 0) {
            $message .= " {$imageCount} image(s) uploaded.";
        }

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', $message);
    }

    public function destroy(Product $product): RedirectResponse
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $slug = Str::slug($value) ?: 'product';
        $original = $slug;
        $i = 1;

        while (Product::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $original.'-'.$i++;
        }

        return $slug;
    }

    /**
     * @return int Number of images stored
     */
    private function storeImages(Product $product, mixed $files): int
    {
        $files = $this->normalizeUploadedFiles($files);
        $sort = (int) $product->images()->max('sort_order');
        $count = 0;

        foreach ($files as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $path = $file->store('products', 'public');

            if (! $path) {
                continue;
            }

            $isPrimary = ! $product->images()->exists();

            ProductImage::create([
                'product_id' => $product->id,
                'path' => $path,
                'sort_order' => ++$sort,
                'is_primary' => $isPrimary,
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * @return array<int, \Illuminate\Http\UploadedFile>
     */
    private function normalizeUploadedFiles(mixed $files): array
    {
        if ($files === null) {
            return [];
        }

        if (! is_array($files)) {
            return $files->isValid() ? [$files] : [];
        }

        return array_values(array_filter($files, fn ($file) => $file && $file->isValid()));
    }
}
