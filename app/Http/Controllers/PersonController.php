<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
class PersonController extends Controller
{
    public function updateSenses( Request $request
    ) {
        $request->validate([
            'person_id' => 'required|exists:person,id',
            'bodytemp' => 'required|numeric',
            'heart_rate' => 'required|numeric',
        ]);
        $person = Person::find($request->person_id);
        if (!$person) {
            return response()->json(['message' => 'Person not found'], 404);
        }
        $person->heart_rate = $request->heart_rate;
        $person->bodytemp = $request->bodytemp;
        $person->save();
        return response()->json(['success' => true, 'message' => 'Senses updated'], 200);

    }
    
    public function show($id)
    {
        $person = Person::find($id);
        if (!$person) {
            return response()->json(['message' => 'Person not found'], 404);
        }
        return view('info', ['person' => $person]);
    }
}
