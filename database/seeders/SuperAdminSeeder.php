<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * تشغيل Seeder.
     */
    public function run()
    {
        // حساب أول للسوبر أدمن
        User::updateOrCreate(
            ['email' => 'castrofares@gmail.com'], // تحقق إذا كان هذا البريد الإلكتروني موجودًا
            [
                'name' => 'castro fares',
                'password' => Hash::make('12345678'), // كلمة المرور
                'role' => 'super_admin', // تعيين الدور
            ]
        );

        // حساب ثاني للسوبر أدمن
        User::updateOrCreate(
            ['email' => 'arsolaayoub@gmail.com'], // تحقق إذا كان هذا البريد الإلكتروني موجودًا
            [
                'name' => 'arsola ayoub',
                'password' => Hash::make('12345678'), // كلمة المرور
                'role' => 'super_admin', // تعيين الدور
            ]
        );
    }
}
