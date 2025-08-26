<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Utility;
use Illuminate\Http\Request;

class UtilitiesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('utilities.index');
    }

    public function getValues($key) {
        $values = explode(',', Utility::where('key', $key)->value('value'));
        return response()->json($values);
    }
    
    public function addValue(Request $request) {
        $utility = Utility::where('key', $request->key)->first();
        $values = explode(',', $utility->value);
        $values[] = $request->value;
        $utility->value = implode(',', $values);
        $utility->save();
        return response()->json(['success' => true]);
    }
    
    public function editValue(Request $request) {
        $utility = Utility::where('key', $request->key)->first();
        $values = explode(',', $utility->value);
        $values[$request->index] = $request->value;
        $utility->value = implode(',', $values);
        $utility->save();
        return response()->json(['success' => true]);
    }
    
    public function deleteValue(Request $request) {
        $utility = Utility::where('key', $request->key)->first();
        $values = explode(',', $utility->value);
        $deleteValue = trim($values[$request->index]);
        unset($values[$request->index]);
        $utility->value = implode(',', array_values($values));
        $utility->save();

        // Now update all properties where this column exists and contains the deleted value
        $column = $request->key;

        // Process properties in chunks (for performance on large data)
        Property::whereNotNull($column)->chunk(100, function ($properties) use ($column, $deleteValue) {
            foreach ($properties as $property) {
                $propValues = array_filter(explode(',', $property->{$column}));
                $propValues = array_map('trim', $propValues);

                if (in_array($deleteValue, $propValues)) {
                    $propValues = array_diff($propValues, [$deleteValue]);
                    $property->{$column} = implode(',', $propValues);
                    $property->save();
                }
            }
        });

        return response()->json(['success' => true]);
    }
    
    public function updateAgentLimit(Request $request)
    {
        Utility::updateOrInsert(
            ['key' => 'agent_limit_per_day'],
            ['value' => $request->value]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Agent limit updated successfully!',
            'value' => $request->value
        ]);
    }

    public function getAgentLimit()
    {
        $limit = Utility::where('key', 'agent_limit_per_day')
            ->value('value');

        return response()->json(['value' => $limit]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
