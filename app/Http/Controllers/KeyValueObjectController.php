<?php

namespace App\Http\Controllers;

use App\Models\KeyValueObject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class KeyValueObjectController extends Controller
{
    private static function clearCacheSet(string $key): void {
        Cache::tags(['key:' . $key])->flush();
        Cache::forget("key_value_objects");
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $keyValueObjects = Cache::remember('key_value_objects', 600, function () {
            return KeyValueObject::orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->take(200)
                ->get();
        });

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
            'value' => $validated['value'],
        ]);

        self::clearCacheSet($keyValueObject->key);

        return response()->json($keyValueObject, 201);
    }

    /**
     * Get all object key.
     */
    public function getByKey(Request $request, string $key): JsonResponse
    {
        $cacheKey = 'key_value_object_' . $key;

        if ($request->has('timestamp')) {
            $timestamp = $request->query('timestamp');
            $cacheKey .= '_timestamp_' . $timestamp;

            $object = Cache::tags(['key:'.$key])->remember($cacheKey, 600, function () use ($key, $timestamp) {
                return KeyValueObject::where('key', $key)
                    ->where('created_at', '<=', date('Y-m-d H:i:s', $timestamp))
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
            });
        } else {
            $object = Cache::tags(['key:'.$key])->remember($cacheKey, 600, function () use ($key) {
                return KeyValueObject::where('key', $key)
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
            });
        }

        return response()->json($object);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KeyValueObject $keyValueObject): JsonResponse
    {
        $key = $keyValueObject->key;
        $keyValueObject->delete();

        self::clearCacheSet($key);

        return response()->json(null, 204);
    }
}
