<?php

namespace Database\Seeders;

use App\Models\Asset;
use Illuminate\Database\Seeder;

class AvailableStockSeeder extends Seeder
{
    /**
     * Run the database seeds for available laptop stock.
     */
    public function run(): void
    {
        $availableLaptops = [
            ['brand' => 'Acer', 'model' => 'Aspire A515', 'serial_number' => '00BFE300'],
            ['brand' => 'Acer', 'model' => 'Aspire A515', 'serial_number' => 'A80104FB'],
            ['brand' => 'Acer', 'model' => 'Aspire A515', 'serial_number' => '57021BEC'],
            ['brand' => 'Acer', 'model' => 'Aspire E1-471', 'serial_number' => '81D77A7600'],
            ['brand' => 'Acer', 'model' => 'Aspire E1-471', 'serial_number' => '9045517600'],
            ['brand' => 'Acer', 'model' => 'Aspire E1-471', 'serial_number' => '602EF27600'],
            ['brand' => 'Acer', 'model' => 'Aspire E1-471', 'serial_number' => '81D5687600'],
            ['brand' => 'Acer', 'model' => 'Aspire E1-471', 'serial_number' => '81D7A47600'],
            ['brand' => 'Acer', 'model' => 'Aspire E1-471', 'serial_number' => '81D6197600'],
            ['brand' => 'Acer', 'model' => 'Aspire E1-471', 'serial_number' => '81D6D17600'],
            ['brand' => 'Acer', 'model' => 'Aspire E1-471', 'serial_number' => '9045747600'],
            ['brand' => 'Acer', 'model' => 'Aspire E1-471', 'serial_number' => '90456B7600'],
            ['brand' => 'Acer', 'model' => 'Aspire E1-471', 'serial_number' => '90454B7600'],
            ['brand' => 'Acer', 'model' => 'Nitro', 'serial_number' => 'PPLGSN009'],
            ['brand' => 'Apple', 'model' => 'MacBook Pro', 'serial_number' => 'PPLGSN008'],
            ['brand' => 'Asus', 'model' => 'Tuff Gaming', 'serial_number' => 'PPLGSN010'],
            ['brand' => 'Dell', 'model' => 'Inspiron 15', 'serial_number' => 'DEL-001'],
            ['brand' => 'Lenovo', 'model' => 'Ideapad 100', 'serial_number' => 'pf0g0va2'],
            ['brand' => 'Lenovo', 'model' => 'Ideapad 100', 'serial_number' => 'pf0g0yen'],
            ['brand' => 'Lenovo', 'model' => 'Ideapad 3', 'serial_number' => '0AC03CBF'],
            ['brand' => 'Lenovo', 'model' => 'Ideapad 3', 'serial_number' => 'PF4YKGBZ'],
            ['brand' => 'Lenovo', 'model' => 'Ideapad 3', 'serial_number' => 'PF4YFYPD'],
            ['brand' => 'Lenovo', 'model' => 'Ideapad 3', 'serial_number' => 'PF4YK2C8'],
            ['brand' => 'Lenovo', 'model' => 'Ideapad 3', 'serial_number' => 'PPLGSN001'],
            ['brand' => 'Lenovo', 'model' => 'Ideapad 3', 'serial_number' => 'PPLGSN002'],
            ['brand' => 'Lenovo', 'model' => 'Ideapad 3', 'serial_number' => 'PPLGSN003'],
            ['brand' => 'Lenovo', 'model' => 'Ideapad 3', 'serial_number' => 'PPLGSN004'],
            ['brand' => 'Lenovo', 'model' => 'Ideapad 3', 'serial_number' => 'PPLGSN005'],
            ['brand' => 'Lenovo', 'model' => 'Ideapad 3', 'serial_number' => 'PPLGSN006'],
            ['brand' => 'Lenovo', 'model' => 'LOQ', 'serial_number' => 'PPLGSN007'],
            ['brand' => 'Lenovo', 'model' => 'ThinkPad X1', 'serial_number' => 'LNV-001'],
        ];

        foreach ($availableLaptops as $laptop) {
            Asset::query()->updateOrCreate(
                [
                    'serial_number' => $laptop['serial_number'],
                ],
                [
                    'category' => 'Laptop',
                    'brand' => $laptop['brand'],
                    'model' => $laptop['model'],
                    'barcode' => $laptop['serial_number'],
                    'qr_code_hash' => hash('sha256', $laptop['serial_number']),
                    'condition' => 'good',
                    'status' => 'available',
                ]
            );
        }
    }
}
