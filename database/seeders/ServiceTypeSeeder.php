<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tableService = ServiceType::create([
            'name' => 'Table Service',
            'alias' => 'table-service',
        ]);

        $offsiteService = ServiceType::create([
            'name' => 'Offsite Service',
            'alias' => 'offsite-service',
        ]);

        $services = Service::all();

        foreach ($services as $service) {
            $serviceTypeId = ($service->type === 'takeaway-service' || $service->type === 'delivery-service') ? $offsiteService->id : ($service->type === Service::SERVE ? $tableService->id : 1000000);

            $service->serviceTypes()->attach($serviceTypeId, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
