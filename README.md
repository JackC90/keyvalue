<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>


# Key-value API

Welcome to the Key-Value Store API! This API allows you to create, retrieve, and manage key-value pairs efficiently. Below are the details of the available endpoints, including request formats and expected responses.

Endpoints
---------

1\. Create Record
-----------------

**POST** `/api/object` This endpoint allows you to create a new record in the key-value store.

Request Body
------------

```json
{
    "key": "test001",
    "value": "{ \"name\": \"New Object\" }"
}
```

Response
--------

On successful creation, the API will return the created record along with timestamps for creation and last update:

```json
{
    "key": "test001",
    "value": "{ \"name\": \"Newer Object\" }",
    "updated_at": "2024-12-10T07:19:49.000000Z",
    "created_at": "2024-12-10T07:19:49.000000Z",
    "id": 11
}
```

2\. Get All Records
-------------------

**GET** `/api/object/get_all_records` This endpoint retrieves all records stored in the key-value store.

Response
--------

The response will be an array of records, each containing its ID, key, value, and timestamps:

```json
[
    {
        "id": 11,
        "key": "test001",
        "value": "{ \"name\": \"Newer Object\" }",
        "created_at": "2024-12-10T07:19:49.000000Z",
        "updated_at": "2024-12-10T07:19:49.000000Z"
    },
    {
        "id": 10,
        "key": "test001",
        "value": "{ \"name\": \"Newer Object\" }",
        "created_at": "2024-12-10T07:18:22.000000Z",
        "updated_at": "2024-12-10T07:18:22.000000Z"
    }
]
```

3\. Get Record by Key
---------------------

**GET** `/api/object/{key}` This endpoint retrieves a specific record by its key. You can also optionally query for a specific timestamp.

Query Parameters (Optional)
---------------------------

*   `timestamp`: A Unix timestamp to filter records.

Response
--------

The response will include the requested record's details:

```json
{
    "id": 11,
    "key": "test001",
    "value": "{ \"name\": \"Newer Object\" }",
    "created_at": "2024-12-10T07:19:49.000000Z",
    "updated_at": "2024-12-10T07:19:49.000000Z"
}
```


