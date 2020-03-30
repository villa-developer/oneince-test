<?php

use App\Product;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = ['product1', 'product2', 'products3', 'products4'];

        foreach ($products as $product) {
            Product::create([
                'name'=> $product,
                'price' => rand(100, 500)
            ]);
        }
    }
}
