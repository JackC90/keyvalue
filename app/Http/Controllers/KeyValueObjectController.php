<?php

namespace App\Http\Controllers;

use App\Models\KeyValueObject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class KeyValueObjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $keyValueObjects = KeyValueObject::orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->take(200)
            ->get();
        return response()->json($keyValueObjects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'required|json'
        ]);

        $keyValueObject = KeyValueObject::create([
            'key' => $validated['key'],
            'value' => json_encode($validated['value']),
        ]);

        return response()->json($keyValueObject, 201);
    }

    /**
     * Get all object key.
     */
    public function getByKey(Request $request, string $key): JsonResponse
    {
        $query = KeyValueObject::where('key', $key);

        if ($request->has('timestamp')) {
            $timestamp = $request->query('timestamp');
            $object = $query->where('created_at', '<=', date('Y-m-d H:i:s', $timestamp))
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
        } else {
            $object = $query->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
        }
        return response()->json($object);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KeyValueObject $keyValueObject): JsonResponse
    {
        $keyValueObject->delete();
        return response()->json(null, 204);
    }
}
