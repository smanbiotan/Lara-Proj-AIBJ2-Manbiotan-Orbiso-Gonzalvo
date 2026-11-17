<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::withCount('appointments')
            ->search($request->query('q'))
            ->when(in_array($request->query('status'), Customer::STATUSES, true), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->query('account') === 'registered', fn ($q) => $q->where(fn ($w) => $w->whereNotNull('password')->orWhereNotNull('google_id')))
            ->when($request->query('account') === 'guest', fn ($q) => $q->whereNull('password')->whereNull('google_id'))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customers.form', ['customer' => new Customer(['status' => 'Active'])]);
    }

    public function store(Request $request)
    {
        Customer::create($this->validated($request));
        return redirect()->route('admin.customers.index')->with('success', 'Customer added.');
    }

    public function show(Customer $customer)
    {
        $appointments = $customer->appointments()->with('serviceRecord')->orderByDesc('appointment_date')->get();
        return view('admin.customers.show', compact('customer', 'appointments'));
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request, $customer));
        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer details updated.');
    }

    public function destroy(Customer $customer)
    {
        $name = $customer->name;
        $customer->delete(); // appointments cascade by foreign key
        return redirect()->route('admin.customers.index')->with('success', "{$name} and their appointment history were deleted.");
    }

    private function validated(Request $request, ?Customer $customer = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer?->id)],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]{7,30}$/'],
            'status' => ['required', Rule::in(Customer::STATUSES)],
        ]);
        $data['email'] = strtolower($data['email']);
        return $data;
    }
}
