<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $items = InventoryItem::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->trim();

                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.index', [
            'items' => $items,
            'lowStockCount' => InventoryItem::query()->lowStock()->count(),
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', InventoryItem::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'unit' => ['required', 'string', 'max:32'],
            'quantity' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        InventoryItem::create($data);

        return back()->with('status', 'Barang inventori tersimpan.');
    }

    public function update(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $this->authorize('update', $inventoryItem);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'unit' => ['required', 'string', 'max:32'],
            'quantity' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $inventoryItem->update($data);

        return back()->with('status', 'Barang inventori diperbarui.');
    }

    public function destroy(InventoryItem $inventoryItem): RedirectResponse
    {
        $this->authorize('delete', $inventoryItem);

        $inventoryItem->update(['is_active' => false]);

        return back()->with('status', 'Barang inventori dinonaktifkan.');
    }
}