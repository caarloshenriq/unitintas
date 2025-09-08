<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::all();
        return view('livewire.pages.customers', compact('customers'));
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:customers,email',
            'phone' => 'nullable|string|max:20',
        ]);

        Customer::create($request->all());

        $logController = new LogController();
        $logController->registerLog('Customer', 'Create');

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
        ]);
        $customer->update($request->all());

        $logController = new LogController();
        $logController->registerLog('Customer', 'Update - id: ' . $customer->id);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        $logController = new LogController();
        $logController->registerLog('Customer', 'Delete - id: ' . $customer->id);
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
