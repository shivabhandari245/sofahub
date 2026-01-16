<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchaseModel;
use App\Models\ProductModel;
use App\Models\User;
use App\Models\UserCategory;
use App\Models\UserSupplier;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $users = User::pluck('id')->toArray();

        // Ensure categories exist
        if (UserCategory::count() == 0) {
            foreach (['Electronics','Clothing','Grocery','Furniture','Stationery'] as $cat) {
                UserCategory::create(['name' => $cat]);
            }
        }

        // Ensure suppliers exist
        if (UserSupplier::count() == 0) {
            for ($i = 1; $i <= 10; $i++) {
                UserSupplier::create([
                    'name' => 'Supplier '.$i,
                    'contact' => $faker->phoneNumber
                ]);
            }
        }

        $categories = UserCategory::pluck('name')->toArray();
        $suppliers  = UserSupplier::all();

        DB::transaction(function () use ($faker, $users, $categories, $suppliers) {

            for ($i = 0; $i < 1000; $i++) {

                $quantity  = rand(1, 100);
                $unitCost  = rand(50, 3000);
                $totalCost = $quantity * $unitCost;

                $supplier = $suppliers->random();
                $category = collect($categories)->random();
                $userId   = collect($users)->random();
                $product  = ucfirst($faker->words(rand(1, 3), true));

                // ✅ Create Purchase
                $purchase = PurchaseModel::create([
                    'user_id'          => $userId,
                    'product_name'     => $product,
                    'category'         => $category,
                    'supplier_name'    => $supplier->name,
                    'supplier_contact' => $supplier->contact,
                    'quantity'         => $quantity,
                    'unit_cost'        => $unitCost,
                    'totalcost'        => $totalCost,
                    'quality'          => $faker->randomElement(['High','Medium','Low']),
                    'delivery_date'    => Carbon::now()->subDays(rand(0, 90)),
                    'status'           => $faker->randomElement(['Purchased','Pending']),
                    'created_at'       => Carbon::now()->subDays(rand(0, 90)),
                    'updated_at'       => Carbon::now(),
                ]);

                // ✅ Create Product (Stock)
                ProductModel::create([
                    'user_id'           => $userId,
                    'name'              => $product,
                    'category'          => $category,
                    'quality'           => $purchase->quality,
                    'quantity'          => $quantity,
                    'cost_per_product'  => $unitCost,
                    'total_cost'        => $totalCost,
                    'source'            => 'purchase',
                    'created_at'        => $purchase->created_at,
                    'updated_at'        => Carbon::now(),
                ]);
            }
        });
    }
}
