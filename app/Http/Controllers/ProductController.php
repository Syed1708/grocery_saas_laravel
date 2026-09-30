<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSection;
use App\Models\ProductStock;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['section', 'category', 'brand', 'unit', 'stocks']);

        // Search by Name or Barcode
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('name_en', 'like', "%{$s}%")
                  ->orWhere('barcode', 'like', "%{$s}%")
                  ->orWhere('sku', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $products = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::active()->get();
        $brands = Brand::active()->get();

        return view('products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $sections = ProductSection::active()->get();
        $categories = Category::active()->get();
        $brands = Brand::active()->get();
        $units = Unit::active()->get();
        $branches = Branch::active()->get();

        $autoBarcode = date('ymd') . rand(1000, 9999);
        $autoSku = 'PRD-' . strtoupper(Str::random(5));

        return view('products.create', compact('sections', 'categories', 'brands', 'units', 'branches', 'autoBarcode', 'autoSku'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:191',
            'name_en'         => 'nullable|string|max:191',
            'barcode'         => 'required|string|max:100|unique:products,barcode',
            'sku'             => 'required|string|max:50|unique:products,sku',
            'section_id'      => 'nullable|exists:product_sections,id',
            'category_id'     => 'required|exists:categories,id',
            'brand_id'        => 'nullable|exists:brands,id',
            'unit_id'         => 'required|exists:units,id',
            'purchase_price'  => 'required|numeric|min:0',
            'selling_price'   => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'vat_percent'     => 'nullable|numeric|min:0|max:100',
            'expiry_date'     => 'nullable|date',
            'alert_quantity'  => 'required|numeric|min:0',
            'status'          => 'required|in:active,inactive',
            'initial_stock'   => 'nullable|numeric|min:0',
        ]);

        $product = Product::create($validated);

        // Seed initial stock for active branch
        $activeBranchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;
        if ($activeBranchId) {
            ProductStock::create([
                'product_id' => $product->id,
                'branch_id'  => $activeBranchId,
                'quantity'   => $request->input('initial_stock', 0),
            ]);
        }

        return redirect()->route('products.index')->with('success', 'পণ্যটি সফলভাবে ক্যাটালগে যুক্ত হয়েছে!');
    }

    public function edit(Product $product)
    {
        $sections = ProductSection::active()->get();
        $categories = Category::active()->get();
        $brands = Brand::active()->get();
        $units = Unit::active()->get();

        return view('products.edit', compact('product', 'sections', 'categories', 'brands', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:191',
            'name_en'         => 'nullable|string|max:191',
            'barcode'         => 'required|string|max:100|unique:products,barcode,' . $product->id,
            'sku'             => 'required|string|max:50|unique:products,sku,' . $product->id,
            'section_id'      => 'nullable|exists:product_sections,id',
            'category_id'     => 'required|exists:categories,id',
            'brand_id'        => 'nullable|exists:brands,id',
            'unit_id'         => 'required|exists:units,id',
            'purchase_price'  => 'required|numeric|min:0',
            'selling_price'   => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'vat_percent'     => 'nullable|numeric|min:0|max:100',
            'expiry_date'     => 'nullable|date',
            'alert_quantity'  => 'required|numeric|min:0',
            'status'          => 'required|in:active,inactive',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'পণ্যের তথ্য সফলভাবে আপডেট করা হয়েছে!');
    }

    public function destroy(Product $product)
    {
        $product->stocks()->delete();
        $product->delete();

        return redirect()->route('products.index')->with('success', 'পণ্যটি তালিকা থেকে মুছে ফেলা হয়েছে!');
    }

    // Barcode Sticker Sheet Print View
    public function printBarcodes(Request $request, Product $product)
    {
        $quantity = (int) $request->input('quantity', 24); // 24 stickers on A4 by default
        return view('products.barcodes', compact('product', 'quantity'));
    }
}