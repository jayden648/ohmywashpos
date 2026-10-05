<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Services\CustomerCodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerCodeGenerator $codes,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        $customers = Customer::query()
            ->withOrderSummary()
            ->search($request->query('search'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', [
            'customers' => $customers,
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Customer::class);

        return view('customers.create');
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        $customer = Customer::create([
            'customer_code' => $this->codes->next(),
            'name' => $request->string('name')->toString(),
            'phone' => $request->string('phone')->toString(),
            'email' => $request->input('email'),
            'address' => $request->input('address'),
            'notes' => $request->input('notes'),
        ]);

        return redirect()
            ->route('customers.show', $customer)
            ->with('status', "Pelanggan {$customer->customer_code} tersimpan.");
    }

    /**
     * Settled orders only; cancelled ones must not count towards history.
     *
     * The closure receives an Order builder, not a Customer builder, so a
     * Customer scope is not in scope here - the condition is written out.
     *
     * @var callable(\Illuminate\Database\Eloquent\Builder): void $countable
     */
    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);

        $countable = fn ($query) => $query->where('status', '!=', OrderStatus::Cancelled->value);

        $customer->loadCount([
            'orders',
            'orders as payable_orders_count' => $countable,
        ])->loadSum([
            'orders as total_spending' => $countable,
        ], 'total');

        $orders = $customer->orders()->latest()->paginate(10);

        return view('customers.show', [
            'customer' => $customer,
            'orders' => $orders,
        ]);
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);

        return view('customers.edit', ['customer' => $customer]);
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update([
            'name' => $request->string('name')->toString(),
            'phone' => $request->string('phone')->toString(),
            'email' => $request->input('email'),
            'address' => $request->input('address'),
            'notes' => $request->input('notes'),
        ]);

        return redirect()
            ->route('customers.show', $customer)
            ->with('status', 'Data pelanggan diperbarui.');
    }

    /**
     * Deactivate a customer rather than deleting them, so their order
     * history and spending totals remain intact.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        $customer->update(['is_active' => false]);

        return back()->with('status', "Pelanggan {$customer->customer_code} dinonaktifkan.");
    }

    public function restore(Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $customer->update(['is_active' => true]);

        return back()->with('status', "Pelanggan {$customer->customer_code} diaktifkan kembali.");
    }
}