<?php

namespace Database\Seeders;

use App\Models\admin;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        admin::create([
            'name' => 'admin khaled',
            'email' => 'adminkhaled@gmail.com',
            'password' => bcrypt('khaled123'),
            'role' => 'super_admin',

        ]);

        DB::table('countries')->insert([
            ['en_name' => 'United States', 'ar_name' => 'الولايات المتحدة', 'flag' => null, 'state' => 'active'],
            ['en_name' => 'Canada', 'ar_name' => 'كندا', 'flag' => null, 'state' => 'active'],
            ['en_name' => 'United Kingdom', 'ar_name' => 'المملكة المتحدة', 'flag' => null, 'state' => 'active'],
            ['en_name' => 'Germany', 'ar_name' => 'ألمانيا', 'flag' => null, 'state' => 'active'],
            ['en_name' => 'France', 'ar_name' => 'فرنسا', 'flag' => null, 'state' => 'inactive'],
            ['en_name' => 'Italy', 'ar_name' => 'إيطاليا', 'flag' => null, 'state' => 'active'],
            ['en_name' => 'Australia', 'ar_name' => 'أستراليا', 'flag' => null, 'state' => 'active'],
            ['en_name' => 'Japan', 'ar_name' => 'اليابان', 'flag' => null, 'state' => 'inactive'],
            ['en_name' => 'China', 'ar_name' => 'الصين', 'flag' => null, 'state' => 'active'],
            ['en_name' => 'India', 'ar_name' => 'الهند', 'flag' => null, 'state' => 'active']
        ]);


        DB::table('regions')->insert([
            ['country_id' => 1, 'en_name' => 'California', 'ar_name' => 'كاليفورنيا'],
            ['country_id' => 1, 'en_name' => 'New York', 'ar_name' => 'نيويورك'],
            ['country_id' => 2, 'en_name' => 'Ontario', 'ar_name' => 'أونتاريو'],
            ['country_id' => 2, 'en_name' => 'Quebec', 'ar_name' => 'كيبك'],
            ['country_id' => 3, 'en_name' => 'England', 'ar_name' => 'إنجلترا'],
            ['country_id' => 3, 'en_name' => 'Scotland', 'ar_name' => 'اسكتلندا'],
            ['country_id' => 4, 'en_name' => 'Bavaria', 'ar_name' => 'بافاريا'],
            ['country_id' => 4, 'en_name' => 'Berlin', 'ar_name' => 'برلين'],
            ['country_id' => 5, 'en_name' => 'Île-de-France', 'ar_name' => 'إيل دو فرانس'],
            ['country_id' => 5, 'en_name' => 'Provence-Alpes-Côte d\'Azur', 'ar_name' => 'بروفنس ألب كوت دازور']
        ]);


        DB::table('categories')->insert([
            // الآباء
            ['id' => 1, 'ar_name' => 'الإلكترونيات', 'en_name' => 'Electronics', 'icon' => null, 'state' => 'active', 'parent_id' => null, '_lft' => 1, '_rgt' => 6],
            ['id' => 2, 'ar_name' => 'المركبات', 'en_name' => 'Vehicles', 'icon' => null, 'state' => 'active', 'parent_id' => null, '_lft' => 7, '_rgt' => 12],
            ['id' => 3, 'ar_name' => 'العقارات', 'en_name' => 'Real Estate', 'icon' => null, 'state' => 'active', 'parent_id' => null, '_lft' => 13, '_rgt' => 18],
            ['id' => 4, 'ar_name' => 'الأزياء', 'en_name' => 'Fashion', 'icon' => null, 'state' => 'active', 'parent_id' => null, '_lft' => 19, '_rgt' => 24],
            ['id' => 5, 'ar_name' => 'الوظائف', 'en_name' => 'Jobs', 'icon' => null, 'state' => 'active', 'parent_id' => null, '_lft' => 25, '_rgt' => 30],

            // الأبناء
            ['id' => 6,  'ar_name' => 'هواتف محمولة', 'en_name' => 'Mobile Phones', 'icon' => null, 'state' => 'active', 'parent_id' => 1, '_lft' => 2, '_rgt' => 3],
            ['id' => 7,  'ar_name' => 'أجهزة كمبيوتر', 'en_name' => 'Computers', 'icon' => null, 'state' => 'active', 'parent_id' => 1, '_lft' => 4, '_rgt' => 5],

            ['id' => 8,  'ar_name' => 'سيارات', 'en_name' => 'Cars', 'icon' => null, 'state' => 'active', 'parent_id' => 2, '_lft' => 8, '_rgt' => 9],
            ['id' => 9,  'ar_name' => 'دراجات نارية', 'en_name' => 'Motorcycles', 'icon' => null, 'state' => 'active', 'parent_id' => 2, '_lft' => 10, '_rgt' => 11],

            ['id' => 10, 'ar_name' => 'شقق للبيع', 'en_name' => 'Apartments for Sale', 'icon' => null, 'state' => 'active', 'parent_id' => 3, '_lft' => 14, '_rgt' => 15],
            ['id' => 11, 'ar_name' => 'منازل للإيجار', 'en_name' => 'Houses for Rent', 'icon' => null, 'state' => 'active', 'parent_id' => 3, '_lft' => 16, '_rgt' => 17],

            ['id' => 12, 'ar_name' => 'ملابس رجالية', 'en_name' => 'Men\'s Clothing', 'icon' => null, 'state' => 'active', 'parent_id' => 4, '_lft' => 20, '_rgt' => 21],
            ['id' => 13, 'ar_name' => 'ملابس نسائية', 'en_name' => 'Women\'s Clothing', 'icon' => null, 'state' => 'active', 'parent_id' => 4, '_lft' => 22, '_rgt' => 23],

            ['id' => 14, 'ar_name' => 'وظائف بدوام كامل', 'en_name' => 'Full-Time Jobs', 'icon' => null, 'state' => 'active', 'parent_id' => 5, '_lft' => 26, '_rgt' => 27],
            ['id' => 15, 'ar_name' => 'وظائف جزئية', 'en_name' => 'Part-Time Jobs', 'icon' => null, 'state' => 'active', 'parent_id' => 5, '_lft' => 28, '_rgt' => 29],
        ]);



    }
}
