<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceCategoryRequest;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ServiceCategory::class);

        $categories = ServiceCategory::query()
            ->withCount('services')
            ->orderBy('name')
            ->get();

        return view('service-categories.index', ['categories' => $categories]);
    }

    public function store(ServiceCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        ServiceCategory::create($data + ['slug' => str($data['name'])->slug()->value()]);

        return back()->with('status', 'Kategori layanan tersimpan.');
    }

    public function update(ServiceCategoryRequest $request, ServiceCategory $category): RedirectResponse
    {
        $category->update($request->validated() + [
            'slug' => str($request->string('name')->toString())->slug()->value(),
        ]);

        return back()->with('status', 'Kategori layanan diperbarui.');
    }

    /**
     * A category holding services is never removed; deactivate it instead.
     */
    public function destroy(ServiceCategory $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        return back()->with('status', 'Kategori layanan dihapus.');
    }
}