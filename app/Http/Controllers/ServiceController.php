<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * The catalogue. Every role may read it; only an admin may change it.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Service::class);

        $services = Service::query()
            ->with('serviceCategory:id,name')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->trim();

                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->filled('category'), fn ($query) => $query->where('service_category_id', $request->integer('category')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('services.index', [
            'services' => $services,
            'categories' => ServiceCategory::query()->orderBy('name')->get(),
            'filters' => $request->only('search', 'category'),
            'canManage' => $request->user()->can('update', Service::class),
        ]);
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Service::create($data + ['slug' => $this->uniqueSlug($data['name'])]);

        return back()->with('status', 'Layanan tersimpan.');
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $data = $request->validated();

        $service->update($data + [
            'slug' => $data['name'] === $service->name
                ? $service->slug
                : $this->uniqueSlug($data['name'], $service->id),
        ]);

        return back()->with('status', 'Layanan diperbarui.');
    }

    /**
     * Services are deactivated rather than deleted so historic order lines
     * keep a resolvable reference.
     */
    public function destroy(Service $service): RedirectResponse
    {
        $this->authorize('delete', $service);

        $service->update(['is_active' => false]);

        return back()->with('status', "Layanan {$service->name} dinonaktifkan.");
    }

    /**
     * A slug that does not collide with an existing service.
     */
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = str($name)->slug()->value() ?: 'layanan';
        $slug = $base;
        $suffix = 2;

        while (Service::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}