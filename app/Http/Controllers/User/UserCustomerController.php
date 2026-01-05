<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
class UserCustomerController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');
        
        if (!$query) {
            return response()->json([]);
        }
        
        $customers = Customer::where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('phone', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'phone', 'email', 'address']);
        
        return response()->json($customers);
    }
    
   public function store(Request $request)
{
    // Validate the input
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20|unique:customers,phone',
        'email' => 'nullable|email|max:255|unique:customers,email',
        'address' => 'nullable|string|max:500',
    ]);

   
    if ($validator->fails()) {
        Log::error('Customer validation errors: ', $validator->errors()->toArray());

        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
            'message' => 'Validation failed'
        ], 422);
    }

 $existingCustomer = Customer::where('email', $request->email)->first();

 
    if ($existingCustomer) {
        return response()->json([
            'success' => false,
            'message' => 'The email has already been taken.',
            'customer_exists' => true
        ], 400); 
    }

    try {
        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Customer added successfully',
            'customer' => $customer
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to add customer: ', ['error' => $e->getMessage()]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to add customer: ' . $e->getMessage()
        ], 500); // Internal Server Error
    }
}

    public function show(Customer $customer)
    {
        return response()->json($customer);
    }

}