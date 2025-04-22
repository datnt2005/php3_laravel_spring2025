<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Province;
use App\Models\District;
use App\Models\Ward;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $response = Http::get('https://raw.githubusercontent.com/kenzouno1/DiaGioiHanhChinhVN/master/data.json');
        $data = $response->json();

        foreach ($data as $provinceData) {
            $province = Province::create(['name' => $provinceData['Name']]);

            foreach ($provinceData['Districts'] as $districtData) {
                $district = District::create([
                    'name' => $districtData['Name'],
                    'province_id' => $province->id
                ]);

                foreach ($districtData['Wards'] as $wardData) {
                    Ward::create([
                        'name' => $wardData['Name'],
                        'district_id' => $district->id
                    ]);
                }
            }
        }
    }
}