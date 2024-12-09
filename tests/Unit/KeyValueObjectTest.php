<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\KeyValueObject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class KeyValueObjectTestModel extends KeyValueObject
{
    protected $table = 'key_value_objects';
    
    
    protected $fillable = ['key', 'value', 'created_at'];
    protected $guarded = [];
}

class KeyValueObjectTest extends TestCase
{
    use RefreshDatabase;

    private function createTestObject(string $key, array $value, string $date = null): KeyValueObject
    {
        return KeyValueObjectTestModel::create([
            'key' => $key,
            'value' => json_encode($value),
            'created_at' => $date ? Carbon::parse($date) : now(),
        ]);
    }

    public function test_can_get_all_records()
    {
        // Create 150 records to test pagination
        KeyValueObject::factory()->count(2)->create();

        $response = $this->getJson('/api/object/get_all_records');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_can_create_object()
    {
        $data = [
            'key' => 'mykey',
            'value' => json_encode([
                'name' => 'Test Product',
                'description' => 'Test Description',
                'price' => 1000,
                'address' => [
                    'street' => 'Test Street',
                    'city' => 'Test City',
                    'country' => 'Test Country',
                ],
                'active' => true,
            ])
        ];

        $response = $this->postJson('/api/object', $data);

        $response->assertStatus(201)
            ->assertJson([
                'key' => $data['key'],
                'value' => $data['value']
            ]);
    }

    public function test_get_latest_by_key()
    {
        // Create multiple objects with same key but different dates
        $oldObject = $this->createTestObject(
            'mykey',
            ['name' => 'Old Object'],
            '2024-01-01 00:00:00'
        );

        $newObject = $this->createTestObject(
            'mykey',
            ['name' => 'New Object'],
            '2024-01-01 00:10:00'
        );

        $response = $this->getJson('/api/object/mykey');

        // gets latest if no timestamp given
        $response->assertStatus(200)
            ->assertJson([
                'key' => 'mykey',
                'value' => json_encode(['name' => 'New Object'])
            ]);
    }

    public function test_get_by_key_with_timestamp()
    {
        // Create objects with different timestamps
        $oldObject = $this->createTestObject(
            'mykey',
            ['name' => 'Old Object'],
            '2024-01-01 00:00:00'
        );

        $middleObject = $this->createTestObject(
            'mykey',
            ['name' => 'Middle Object'],
            '2024-02-01 00:00:00'
        );

        $newObject = $this->createTestObject(
            'mykey',
            ['name' => 'New Object'],
            '2024-03-01 00:00:00'
        );

        // Get object at middle timestamp
        $timestamp = strtotime('2024-02-15 00:00:00');
        $response = $this->getJson("/api/object/mykey?timestamp={$timestamp}");

        $response->assertStatus(200)
            ->assertJson([
                'key' => 'mykey',
                'value' => json_encode(['name' => 'Middle Object'])
            ]);
    }

    public function test_returns_null_when_no_object_found()
    {
        $response = $this->getJson('/api/object/nonexistent_key');

        $response->assertStatus(200)
            ->assertJson([]);
    }

    public function test_validation_fails_with_invalid_data()
    {
        $response = $this->postJson('/api/object', [
            'key' => '',
            'value' => 'not_a_json'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['key', 'value']);
    }

    public function test_get_by_key_before_earliest_timestamp()
    {
        $object = $this->createTestObject(
            'mykey',
            ['name' => 'Test Object'],
            '2024-01-01 00:00:00'
        );

        // Try to get object before it existed
        $timestamp = strtotime('2022-01-01 00:00:00');
        $response = $this->getJson("/api/object/mykey?timestamp={$timestamp}");
        // should be empty
        $response->assertStatus(200)
            ->assertJson([]);
    }
}
