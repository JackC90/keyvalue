<?php

namespace App\Http\Controllers;

use App\Models\KeyValueObject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use \Illuminate\Validation\ValidationException;

/**
 * @OA\Info(
 *     title="Key-Value Object API",
 *     version="1.0.0",
 *     description="API for managing key-value objects.",
 *     @OA\Contact(
 *         name="JC",
 *         email="email@example.com"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 */


class KeyValueObjectController extends Controller
{
     /**
     * @OA\Schema(
     *     schema="KeyValueObject",
     *     type="object",
     *     required={"key", "value"},
     *     @OA\Property(property="key", type="string", maxLength=255),
     *     @OA\Property(property="value", type="string", format="json"),
     *     @OA\Property(property="created_at", type="string", format="date-time"),
     *     @OA\Property(property="updated_at", type="string", format="date-time")
     * )
     */

    private static function clearCacheSet(string $key): void {
        Cache::tags(['key:' . $key])->flush();
        Cache::forget("key_value_objects");
    }

    /**
     * @OA\Get(
     *     path="/api/object/get_all_records",
     *     summary="Display a listing of all key-value objects.",
     *     @OA\Response(
     *         response=200,
     *         description="A list of key-value objects",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/KeyValueObject"))
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $keyValueObjects = Cache::remember('key_value_objects', 600, function () {
                return KeyValueObject::orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->take(200)
                    ->get();
            });

            return response()->json($keyValueObjects);
        } catch (\Exception $e) {
            Log::error('Error fetching key-value objects: ' . $e->getMessage());

            return response()->json([
                'error' => 'An error occurred while fetching key-value objects.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/object",
     *     summary="Store a newly created key-value object.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"key","value"},
     *             @OA\Property(property="key", type="string", maxLength=255),
     *             @OA\Property(property="value", type="string", format="json")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Key-value object created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/KeyValueObject")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
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
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error storing key-value object: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while storing the key-value object.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/object/{key}",
     *     summary="Get a key-value object by key.",
     *     @OA\Parameter(
     *         name="key",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Key-value object retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/KeyValueObject")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Key-value object not found"
     *     )
     * )
     */
    public function getByKey(Request $request, string $key): JsonResponse
    {
        try {
            $validated = $request->validate([
                'timestamp' => 'nullable|integer|min:0'
            ]);

            $cacheKey = 'key_value_object_' . $key;

            if ($request->has('timestamp')) {
                $timestamp = $request->query('timestamp');
                $cacheKey .= '_timestamp_' . $timestamp;

                $object = Cache::tags(['key:' . $key])->remember($cacheKey, 600, function () use ($key, $timestamp) {
                    return KeyValueObject::where('key', $key)
                        ->where('created_at', '<=', date('Y-m-d H:i:s', $timestamp))
                        ->orderBy('created_at', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                });
            } else {
                $object = Cache::tags(['key:' . $key])->remember($cacheKey, 600, function () use ($key) {
                    return KeyValueObject::where('key', $key)
                        ->orderBy('created_at', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                });
            }

            return response()->json($object);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error fetching key-value object: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while fetching the key-value object.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage. (internal use)
     */
    public function destroy(KeyValueObject $keyValueObject): JsonResponse
    {
        $key = $keyValueObject->key;
        $keyValueObject->delete();

        self::clearCacheSet($key);

        return response()->json(null, 204);
    }
}
