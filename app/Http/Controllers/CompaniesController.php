<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CompaniesController extends Controller
{
    public function index()
    {
        $companies = DB::connection('vpAppTemplate')
            ->table('companies')
            ->orderBy('id')
            ->get()
            ->toArray();

        return view('settings.companies', compact('companies'));
    }

    public function storeOrUpdate(Request $request)
    {
        $companies = $request->input('companies', []);

        foreach ($companies as $index => $companyData) {
            $validator = Validator::make($companyData, [
                'id' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if ($value !== 'New' && !is_numeric($value)) {
                            $fail('The ID must be "New" or an integer.');
                        }
                    },
                ],
                'name' => 'required|string|max:50',
            ], [
                'id.required' => 'The ID field is required.',
                'id.in' => 'The ID value is invalid.',
                'id.integer' => 'The ID must be an integer.',
                'name.required' => 'O nome da Unidade na linha ' . ($index + 1) . ' é necessário.',
                'name.string' => 'O nome deve conter Letras.',
                'name.max' => 'O nome não deve possuir mais de 50 caractéres.',
            ]);

            if ($validator->fails()) {
                // Redirect back with errors if validation fails
                return back()->withErrors($validator)->withInput();
            }

            $status = isset($companyData['status']) ? 1 : 0; // If checkbox is checked, status is 1; otherwise, it's 0.

            // Ensure company ID 1 always remains active
            if (isset($companyData['id']) && $companyData['id'] == 1) {
                $status = 1; // Force status to 1 for company ID 1
            }

            // Check if the company ID is provided and exists
            if (isset($companyData['id']) && !empty($companyData['id']) && $companyData['id'] !== "New") {
                // Update existing company
                DB::connection('vpAppTemplate')->table('companies')
                    ->where('id', $companyData['id'])
                    ->update([
                        'status' => $status,
                        'name' => $companyData['name'],
                    ]);
            } else {
                DB::connection('vpAppTemplate')->table('companies')->insert([
                    'status' => $status,
                    'name' => $companyData['name'],
                    // Add any other default values or fields you need
                ]);
            }
        }

        return back()->with('success', 'Unidades atualizadas');
    }


}
