<?php

namespace App\Http\Controllers;

use App\Models\Person;

class PersonController extends Controller
{
    public function show(Person $person): \Illuminate\View\View
    {
        $person->load(['companies' => function ($query) {
            $query->orderBy('company_person.is_current', 'desc')
                  ->orderBy('company_person.started_at', 'desc');
        }]);

        return view('people.show', compact('person'));
    }
}
