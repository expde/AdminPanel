<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = DB::table('categories')
            ->leftJoin('categories as parent', 'categories.parent_id', '=', 'parent.id')
            ->select('categories.*', 'parent.name as parent_name')
            ->orderBy('categories.sort_order')
            ->orderBy('categories.name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = DB::table('categories')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;

        // Ensure unique slug
        while (DB::table('categories')->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $data = [
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'is_active' => $request->has('is_active'),
            'sort_order' => $request->sort_order ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/categories'), $filename);
            $data['image'] = 'uploads/categories/' . $filename;
        }

        DB::table('categories')->insert($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show($id)
    {
        $category = DB::table('categories')
            ->leftJoin('categories as parent', 'categories.parent_id', '=', 'parent.id')
            ->select('categories.*', 'parent.name as parent_name')
            ->where('categories.id', $id)
            ->first();

        if (!$category) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Category not found.');
        }

        // Get subcategories
        $subcategories = DB::table('categories')
            ->where('parent_id', $id)
            ->orderBy('name')
            ->get();

        // Get products in this category
        $products = DB::table('products')
            ->where('category_id', $id)
            ->orderBy('name')
            ->paginate(20);

        return view('admin.categories.show', compact('category', 'subcategories', 'products'));
    }

    public function edit($id)
    {
        $category = DB::table('categories')->where('id', $id)->first();

        if (!$category) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Category not found.');
        }

        $parentCategories = DB::table('categories')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->where('id', '!=', $id) // Prevent self-parenting
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, $id)
    {
        $category = DB::table('categories')->where('id', $id)->first();

        if (!$category) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Category not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $slug = Str::slug($request->name);
        if ($slug !== $category->slug) {
            $originalSlug = $slug;
            $counter = 1;

            while (DB::table('categories')->where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $data = [
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'is_active' => $request->has('is_active'),
            'sort_order' => $request->sort_order ?? 0,
            'updated_at' => now(),
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image && file_exists(public_path($category->image))) {
                unlink(public_path($category->image));
            }

            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/categories'), $filename);
            $data['image'] = 'uploads/categories/' . $filename;
        }

        DB::table('categories')->where('id', $id)->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = DB::table('categories')->where('id', $id)->first();

        if (!$category) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Category not found.');
        }

        // Check if category has products
        $productCount = DB::table('products')->where('category_id', $id)->count();
        if ($productCount > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with products. Please move or delete products first.');
        }

        // Check if category has subcategories
        $subcategoryCount = DB::table('categories')->where('parent_id', $id)->count();
        if ($subcategoryCount > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with subcategories. Please delete subcategories first.');
        }

        // Delete image
        if ($category->image && file_exists(public_path($category->image))) {
            unlink(public_path($category->image));
        }

        DB::table('categories')->where('id', $id)->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $category = DB::table('categories')->where('id', $id)->first();

        if (!$category) {
            return response()->json(['error' => 'Category not found.'], 404);
        }

        $newStatus = !$category->is_active;
        DB::table('categories')->where('id', $id)->update([
            'is_active' => $newStatus,
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $newStatus,
            'message' => 'Category status updated successfully.'
        ]);
    }
}
