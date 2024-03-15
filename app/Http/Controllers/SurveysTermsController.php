<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SurveyTerms;
use Illuminate\Support\Str;

class SurveysTermsController extends Controller
{
    protected $connection = 'vpAppTemplate';

    public function index()
    {
        $terms = SurveyTerms::all();
        return view('surveys.terms.listing', compact('terms'));
    }

    public function create()
    {
        return view('surveys.terms.create');
    }

    public function show($id = null)
    {
        $term = SurveyTerms::findOrFail($id);

        return view('surveys.terms.show', compact('term'));
    }

    public function search(Request $request)
    {
        // Search for terms based on the query
        $searchQuery = $request->input('query');
        $terms = SurveyTerms::where('name', 'LIKE', "%{$searchQuery}%")->get();

        return $terms ? response()->json($terms) : null;
    }

    public function form()
    {
        //$terms = SurveyTerms::all();
        $terms = SurveyTerms::paginate(10);

        return view('surveys.terms.form', compact('terms'));
    }

    public function storeOrUpdate(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:100'
        ], [
            'name.required' => 'Informe o Termo',
            'name.max' => 'Termo deve possuir no máximo 100 caracteres.'
        ]);

        $termName = SurveyTerms::cleanedName($validatedData['name']);

        $existingTerm = SurveyTerms::where('name', $termName)->first();
        if ($existingTerm) {
            // Handle the duplicate name scenario
            return response()->json(['success' => false, 'message' => 'Termo já existe!']);
        }

        /*
        $term = new SurveyTerms;
        //$term->user_id = $currentUserId;
        $term->name = $termName;
        $term->slug = $this->createUniqueSlug($termName);
        $term->save();
        */
        // Create and save the new term
        $term = SurveyTerms::create([
            //'user_id' => auth()->id(),
            'name' => $termName,
            'slug' => $this->createUniqueSlug($termName),
        ]);

        return response()->json(['success' => true, 'term' => $term, 'message' => 'Termo registrado!']);
    }

    public function createUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $count = SurveyTerms::whereRaw("slug RLIKE '^{$slug}(-[0-9]+)?$'")->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }

}
