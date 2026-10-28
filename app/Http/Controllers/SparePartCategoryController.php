<?php

namespace App\Http\Controllers;

use App\Models\SparePartCategory;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SparePartCategoryController extends Controller
{
    public function index(): View
    {
        $categories = SparePartCategory::withCount('spareParts')->orderBy('name')->get();

        return view('spare-parts.categories', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:spare_part_categories,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $category = SparePartCategory::create($validated);
        AuditService::log('created', 'SparePartCategory', $category->id, "Menambahkan kategori spare part {$category->name}");

        return back()->with('success', 'Kategori spare part berhasil ditambahkan!');
    }

    public function update(Request $request, SparePartCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:spare_part_categories,code,'.$category->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $category->update($validated);
        AuditService::log('updated', 'SparePartCategory', $category->id, "Memperbarui kategori spare part {$category->name}");

        return back()->with('success', 'Kategori spare part berhasil diperbarui!');
    }

    public function destroy(SparePartCategory $category): RedirectResponse
    {
        if ($category->spareParts()->exists()) {
            return back()->with('error', 'Kategori ini masih memiliki item spare part dan tidak dapat dihapus.');
        }

        $category->delete();
        AuditService::log('deleted', 'SparePartCategory', $category->id, "Menghapus kategori spare part {$category->name}");

        return back()->with('success', 'Kategori spare part berhasil dihapus!');
    }
}
