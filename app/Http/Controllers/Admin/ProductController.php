<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.*', 'categories.name as category_name')
            ->orderBy('products.created_at', 'desc')
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = DB::table('categories')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;

        // Ensure unique slug
        while (DB::table('products')->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $productId = DB::table('products')->insertGetId([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'short_description' => $request->short_description,
            'price' => $request->price,
            'compare_price' => $request->compare_price,
            'cost_price' => $request->cost_price,
            'sku' => $request->sku,
            'barcode' => $request->barcode,
            'stock_quantity' => $request->stock_quantity,
            'low_stock_threshold' => $request->low_stock_threshold ?? 5,
            'weight' => $request->weight,
            'dimensions' => $request->dimensions,
            'category_id' => $request->category_id,
            'is_active' => $request->has('is_active'),
            'is_featured' => $request->has('is_featured'),
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Handle product images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $filename = time() . '_' . $index . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/products'), $filename);

                DB::table('product_images')->insert([
                    'product_id' => $productId,
                    'image_path' => 'uploads/products/' . $filename,
                    'alt_text' => $request->name,
                    'sort_order' => $index,
                    'is_primary' => $index === 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Handle dynamic attributes
        if ($request->has('attributes')) {
            foreach ($request->attributes as $attribute) {
                if (!empty($attribute['name']) && !empty($attribute['value'])) {
                    DB::table('product_attributes')->insert([
                        'product_id' => $productId,
                        'attribute_name' => $attribute['name'],
                        'attribute_value' => $attribute['value'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show($id)
    {
        $product = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.*', 'categories.name as category_name')
            ->where('products.id', $id)
            ->first();

        if (!$product) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Product not found.');
        }

        $images = DB::table('product_images')
            ->where('product_id', $id)
            ->orderBy('sort_order')
            ->get();

        $attributes = DB::table('product_attributes')
            ->where('product_id', $id)
            ->get();

        return view('admin.products.show', compact('product', 'images', 'attributes'));
    }

    public function edit($id)
    {
        $product = DB::table('products')->where('id', $id)->first();

        if (!$product) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Product not found.');
        }

        $categories = DB::table('categories')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $images = DB::table('product_images')
            ->where('product_id', $id)
            ->orderBy('sort_order')
            ->get();

        $attributes = DB::table('product_attributes')
            ->where('product_id', $id)
            ->get();

        return view('admin.products.edit', compact('product', 'categories', 'images', 'attributes'));
    }

    public function update(Request $request, $id)
    {
        $product = DB::table('products')->where('id', $id)->first();

        if (!$product) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Product not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $slug = Str::slug($request->name);
        if ($slug !== $product->slug) {
            $originalSlug = $slug;
            $counter = 1;

            while (DB::table('products')->where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        DB::table('products')->where('id', $id)->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'short_description' => $request->short_description,
            'price' => $request->price,
            'compare_price' => $request->compare_price,
            'cost_price' => $request->cost_price,
            'sku' => $request->sku,
            'barcode' => $request->barcode,
            'stock_quantity' => $request->stock_quantity,
            'low_stock_threshold' => $request->low_stock_threshold ?? 5,
            'weight' => $request->weight,
            'dimensions' => $request->dimensions,
            'category_id' => $request->category_id,
            'is_active' => $request->has('is_active'),
            'is_featured' => $request->has('is_featured'),
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'updated_at' => now(),
        ]);

        // Handle new product images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $filename = time() . '_' . $index . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/products'), $filename);

                DB::table('product_images')->insert([
                    'product_id' => $id,
                    'image_path' => 'uploads/products/' . $filename,
                    'alt_text' => $request->name,
                    'sort_order' => $index,
                    'is_primary' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Update dynamic attributes
        DB::table('product_attributes')->where('product_id', $id)->delete();
        if ($request->has('attributes')) {
            foreach ($request->attributes as $attribute) {
                if (!empty($attribute['name']) && !empty($attribute['value'])) {
                    DB::table('product_attributes')->insert([
                        'product_id' => $id,
                        'attribute_name' => $attribute['name'],
                        'attribute_value' => $attribute['value'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy($id)
    {
        $product = DB::table('products')->where('id', $id)->first();

        if (!$product) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Product not found.');
        }

        // Delete product images
        $images = DB::table('product_images')->where('product_id', $id)->get();
        foreach ($images as $image) {
            if (file_exists(public_path($image->image_path))) {
                unlink(public_path($image->image_path));
            }
        }
        DB::table('product_images')->where('product_id', $id)->delete();

        // Delete product attributes
        DB::table('product_attributes')->where('product_id', $id)->delete();

        // Delete product
        DB::table('products')->where('id', $id)->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
