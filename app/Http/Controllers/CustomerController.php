<?php

namespace App\Http\Controllers;

use App\Http\Controllers\CustomerController;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{

    public function index()
    {
        try {
            $customers = DB::table('customers')->orderByDesc('updated_at')->get();
            return response()->json(['customers' => $customers]);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {

            $data = $request->validate([
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'email' => 'required|email|unique:customers|max:255',
                'birthdate' => 'nullable|date',
            ]);

            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            $result = DB::table('customers')->insert($data);
            $customer='';
            if($result){
            $customer = DB::table('customers')->where('email', $data['email'])->first();
            }
            return response()->json(['customer' => $customer, 'message' => 'Customer created successfully'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show(string $id)
    {
        try {
            $customer = DB::table('customers')->where('id', $id)->first();
            return response()->json(['customer' => $customer]);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $customer = DB::table('customers')->where('id', $id)->first();

            if (!$customer) {
                return response()->json(['message' => 'No customer exists with this ID.']);
            }

            $data = $request->validate([
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'email' => 'required|string|max:255',
                'birthdate' => 'nullable|date',
            ]);

            $checkemail = DB::table('customers')->where('id', '!=', $id)
                ->where('email', $request->email)->first();
            if ($checkemail) {
                return response()->json(['message' => 'This email has already been taken.']);
            }
            $data['updated_at'] = date('Y-m-d H:i:s');
            DB::table('customers')->where('id', $id)->update($data);
            $updatecustomer = DB::table('customers')->where('id', $id)->first();
            return response()->json(['customer' => $updatecustomer, 'message' => 'Customer updated successfully']);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 422);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $customer = DB::table('customers')->where('id', $id)->first();

            if (!$customer) {
                return response()->json(['message' => 'No customer exists with this ID, or Customer has already been deleted.']);
            }

            DB::table('customers')->where('id', $id)->delete();
            return response()->json(['message' => 'Customer deleted successfully']);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
}
