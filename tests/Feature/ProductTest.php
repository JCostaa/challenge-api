<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => \App\Models\Product::factory()->create());

it('returns a successful response getAll', function () {
    $product = \App\Models\Product::first();

    $response = $this->get(route('product.getAll'));

    $response->assertStatus(200);
});

it('returns a successful response getOne', function () {
    $product = \App\Models\Product::first();

    $response = $this->get(route('product.getOneById', [$product]));

    $response->assertStatus(200);
});

test('returns a successful response update Product', function () {
    $product = \App\Models\Product::first();

    $updatedData = [
        'product_name' => 'Update testing name',
        'nutriscore_grade' => 'd',
    ];

    $response = $this->put(route('product.update', [$product]), $updatedData);

    $response->assertStatus(201);
});

test('Teste de exclusão de um produto', function () {
    $product = \App\Models\Product::first();

    $response = $this->delete(route('product.update', [$product]));

    $response->assertStatus(200);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => \App\Models\Enum\ProductStatus::Trash->value]);
});
