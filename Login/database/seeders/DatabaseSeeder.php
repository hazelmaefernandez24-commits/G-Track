<?php

namespace Database\Seeders;

use App\Models\AcademicSchedule;
use App\Models\PNUser;
use Illuminate\Support\Facades\Hash;
use App\Models\StudentDetail;
use App\Models\LeisureSchedule;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create all PNUser records first
        $this->createPNUsers();

        // Then create all StudentDetail records
        $this->createStudentDetails();
    }

    private function createPNUsers(): void
    {
        // Test user for login - Simple credentials
        PNUser::create([
            'user_id' => 'test001',
            'user_fname' => 'Test',
            'user_lname' => 'User',
            'user_mInitial' => 'T',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'test@example.com',
            'user_password' => Hash::make('password123'),
            'status' => 'active',
            'user_role' => 'admin',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 001,
            'user_fname' => 'Jean',
            'user_lname' => 'Salvi',
            'user_mInitial' => 'J',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'jean@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'finance',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 002,
            'user_fname' => 'Jean Marie',
            'user_lname' => 'Tumulak',
            'user_mInitial' => 'A',
            'user_suffix' => 'A.D.',
            'gender' => 'F',
            'user_email' => 'education@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'educator',
            'is_temp_password' => false,
        ]);

        // Create admin user
        PNUser::create([
            'user_id' => 003,
            'user_fname' => 'Stefan',
            'user_lname' => 'Flores',
            'user_mInitial' => 'A',
            'user_suffix' => 'A.D.',
            'gender' => 'M',
            'user_email' => 'admin@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'admin',
            'is_temp_password' => false,
        ]);

        // Create all student users
        // Class 2025
        PNUser::create([
            'user_id' => 4,
            'user_fname' => 'Momshie Mars',
            'user_lname' => 'Avila',
            'user_mInitial' => 'D',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'momshie@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'inspector',
            'is_temp_password' => false,
        ]);


        PNUser::create([
            'user_id' => 5,
            'user_fname' => 'Junrey',
            'user_lname' => 'Ansing',
            'user_mInitial' => 'B',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'junrey@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'training',
            'is_temp_password' => false,
        ]);

        // Create cashier user
        PNUser::create([
            'user_id' => 140,
            'user_fname' => 'John',
            'user_lname' => 'Cashier',
            'user_mInitial' => 'D',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'cashier@pnphilippines.com',
            'user_password' => Hash::make('cashier123'),
            'status' => 'active',
            'user_role' => 'cashier',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 6,
            'user_fname' => 'Christina',
            'user_lname' => 'Manlunas',
            'user_mInitial' => 'B',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'christina@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'cook',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 7,
            'user_fname' => 'Janice',
            'user_lname' => 'Guard',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'guard@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'guard',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 8,
            'user_fname' => 'Aioie',
            'user_lname' => 'Cadorna',
            'user_mInitial' => 'H',
            'user_suffix' => 'Jr.',
            'gender' => 'M',
            'user_email' => 'aioie@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 9,
            'user_fname' => 'Josh Harvie',
            'user_lname' => 'Calub',
            'user_mInitial' => 'L',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'joshexample.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 10,
            'user_fname' => 'Eduard John',
            'user_lname' => 'Sarmiento',
            'user_mInitial' => 'W',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'wardi@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 11,
            'user_fname' => 'Ricky',
            'user_lname' => 'Casas',
            'user_mInitial' => 'T',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'ricky@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 12,
            'user_fname' => 'Gwyn',
            'user_lname' => 'Apawan',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'gwyn@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 13,
            'user_fname' => 'Jenvier',
            'user_lname' => 'Montano',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'jenvier@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 14,
            'user_fname' => 'Norkent',
            'user_lname' => 'Ricacho',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'norkent@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 15,
            'user_fname' => 'Jun Clark',
            'user_lname' => 'Catibod',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'jc@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 16,
            'user_fname' => 'Janno',
            'user_lname' => 'Crisostomo',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'janno@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 17,
            'user_fname' => 'Rosana Jane',
            'user_lname' => 'Wandasan',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'rosana@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 18,
            'user_fname' => 'Shiella',
            'user_lname' => 'Belarmino',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'shiella@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 19,
            'user_fname' => 'Jane Kyla',
            'user_lname' => 'Ruben',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'jane@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 20,
            'user_fname' => 'Freddie',
            'user_lname' => 'Novicio',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'freddie@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'kitchen',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 21,
            'user_fname' => 'Elsa',
            'user_lname' => 'Legista',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'elsa@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 22,
            'user_fname' => 'Cheed Loraine',
            'user_lname' => 'Veliganio',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'cheed@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 23,
            'user_fname' => 'Cyrelle',
            'user_lname' => 'Mascarinas',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'cy@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 24,
            'user_fname' => 'Merrydel',
            'user_lname' => 'Sombrio',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'mer@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 25,
            'user_fname' => 'Deniel',
            'user_lname' => 'Mendoza',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'den@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 26,
            'user_fname' => 'Wendolyn',
            'user_lname' => 'Dante',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'wen@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 27,
            'user_fname' => 'Jincent',
            'user_lname' => 'Caritan',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'jincent@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 28,
            'user_fname' => 'Jane Grace',
            'user_lname' => 'Bautista',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'janegrace@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 29,
            'user_fname' => 'Ruvy Ann',
            'user_lname' => 'Lacaba',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'ruvy@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 30,
            'user_fname' => 'Angelo',
            'user_lname' => 'Parrocho',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'gelo@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 31,
            'user_fname' => 'Valerie',
            'user_lname' => 'Ysulan',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'val@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 32,
            'user_fname' => 'Jasper Drake',
            'user_lname' => 'Ybanez',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'jasper@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 33,
            'user_fname' => 'Michael',
            'user_lname' => 'Jovita',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'gianangelobejec2003@gmail.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 34,
            'user_fname' => 'Nathaniel',
            'user_lname' => 'Retuerto',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'nat@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 35,
            'user_fname' => 'Angela Mae',
            'user_lname' => 'Villaester',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'gela@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 36,
            'user_fname' => 'Marie',
            'user_lname' => 'Dasian',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'marie@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 37,
            'user_fname' => 'Geralyn',
            'user_lname' => 'Monares',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'gera@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 38,
            'user_fname' => 'Renz',
            'user_lname' => 'Godinez',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'renz@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 39,
            'user_fname' => 'Mae',
            'user_lname' => 'Matanog',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'mae@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 40,
            'user_fname' => 'Gee Ann',
            'user_lname' => 'Pulod',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'geeann@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 41,
            'user_fname' => 'Gabriel',
            'user_lname' => 'Ceniza',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'gab@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 42,
            'user_fname' => 'Alfe',
            'user_lname' => 'Pagunsan',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'alfe@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 43,
            'user_fname' => 'Joshua',
            'user_lname' => 'Baguio',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'joshua@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 44,
            'user_fname' => 'Sofia Nicole',
            'user_lname' => 'Moreno',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'sofia@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 45,
            'user_fname' => 'Aisa',
            'user_lname' => 'Delos Santos',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'aisa@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 46,
            'user_fname' => 'Jossel',
            'user_lname' => 'Delos Santos',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'jossel@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 47,
            'user_fname' => 'Renalyn',
            'user_lname' => 'Bontilao',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'renalyn@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 48,
            'user_fname' => 'Gerlie Ann Katherine',
            'user_lname' => 'Daga-as',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'gerlie@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 49,
            'user_fname' => 'Sarah Mae',
            'user_lname' => 'Jomuad',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'sarah@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        // Class 2026
        PNUser::create([
            'user_id' => 50,
            'user_fname' => 'Gerald',
            'user_lname' => 'Reyes',
            'user_mInitial' => 'B',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'gerald@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 51,
            'user_fname' => 'Albert',
            'user_lname' => 'Reboquio',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'albert@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 52,
            'user_fname' => 'Jella',
            'user_lname' => 'Gesim',
            'user_mInitial' => 'B',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'jella@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 53,
            'user_fname' => 'Mariel',
            'user_lname' => 'Bawic',
            'user_mInitial' => 'S',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'mariel@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 54,
            'user_fname' => 'Judy',
            'user_lname' => 'Torenchilla',
            'user_mInitial' => 'L',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'judy@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 55,
            'user_fname' => 'Kristelle',
            'user_lname' => 'Veliganio',
            'user_mInitial' => 'L',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'kristelle@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 56,
            'user_fname' => 'Vanjo',
            'user_lname' => 'Hanzel',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'vanjo@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 57,
            'user_fname' => 'Christian',
            'user_lname' => 'Virtudazo',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'chris@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 58,
            'user_fname' => 'Mohammad',
            'user_lname' => 'Dimpas',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'mohammad@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 59,
            'user_fname' => 'Vinzon',
            'user_lname' => 'Salubre',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'vinzon@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 60,
            'user_fname' => 'Adrian',
            'user_lname' => 'Fabrigar',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'adrian@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 61,
            'user_fname' => 'Adrianne',
            'user_lname' => 'Montano',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'adrianne@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 62,
            'user_fname' => 'Seth',
            'user_lname' => 'Mizon',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'seth@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 63,
            'user_fname' => 'Mary Gwen',
            'user_lname' => 'Sacnanas',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'mary@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 64,
            'user_fname' => 'Joan',
            'user_lname' => 'Canillo',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'joan@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 65,
            'user_fname' => 'Juluis',
            'user_lname' => 'Goles',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'julius@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 66,
            'user_fname' => 'Jhon Xander',
            'user_lname' => 'Pila',
            'user_mInitial' => 'F',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'xander@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
             'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 67,
            'user_fname' => 'Jassy',
            'user_lname' => 'Faburada',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'jassy@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 68,
            'user_fname' => 'Jasper',
            'user_lname' => 'Ursal',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'ursaljasper@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 69,
            'user_fname' => 'Myko',
            'user_lname' => 'Cisneros',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'myko@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 70,
            'user_fname' => 'Jewel',
            'user_lname' => 'Baclayon',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'jewel@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 71,
            'user_fname' => 'Mitch',
            'user_lname' => 'Garcia',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'mitch@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 72,
            'user_fname' => 'Mary Joy',
            'user_lname' => 'Espinosa',
            'user_mInitial' => 'W',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'maryjoy@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 73,
            'user_fname' => 'John Hansel',
            'user_lname' => 'Fajardo',
            'user_mInitial' => 'K',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'johnhansel@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 74,
            'user_fname' => 'Regine Nina',
            'user_lname' => 'Forrosuelo',
            'user_mInitial' => 'S',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'regine@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 75,
            'user_fname' => 'Dino',
            'user_lname' => 'Lamag',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'dino@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 76,
            'user_fname' => 'Justine',
            'user_lname' => 'Fernandez',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'jai@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 77,
            'user_fname' => 'Jocelyn',
            'user_lname' => 'Villaceran',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'jelyn@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 78,
            'user_fname' => 'Alexandra',
            'user_lname' => 'Salvado',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'alexa@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 79,
            'user_fname' => 'Prince',
            'user_lname' => 'Mansueto',
            'user_mInitial' => 'H',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'prince@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => 80,
            'user_fname' => 'Gerald',
            'user_lname' => 'Reyes',
            'user_mInitial' => 'B',
            'user_suffix' => '',
            'gender' => 'M',
            'user_email' => 'gerald2@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);
    }




    private function createStudentDetails(): void
    {
        // Create all student details after all users are created
        // Class 2025

        StudentDetail::create([
            'user_id' => 11,
            'student_id' => '2025020008C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '0008',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 12,
            'student_id' => '2025010009C1',
            'batch' => '2025',
            'group' => 'PN1',
            'student_number' => '0009',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 13,
            'student_id' => '2025020010C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '00010',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 14,
            'student_id' => '2025010011C1',
            'batch' => '2025',
            'group' => 'PN1',
            'student_number' => '00011',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 15,
            'student_id' => '2025020012C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '00012',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 16,
            'student_id' => '2025010013C1',
            'batch' => '2025',
            'group' => 'PN1',
            'student_number' => '00013',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 17,
            'student_id' => '2025020014C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '00014',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 18,
            'student_id' => '2025010015C1',
            'batch' => '2025',
            'group' => 'PN1',
            'student_number' => '00015',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 19,
            'student_id' => '2025020016C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '00016',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 20,
            'student_id' => '2025010017C1',
            'batch' => '2025',
            'group' => 'PN1',
            'student_number' => '00017',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 21,
            'student_id' => '2025020018C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '00018',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 22,
            'student_id' => '2025010019C1',
            'batch' => '2025',
            'group' => 'PN1',
            'student_number' => '00019',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 23,
            'student_id' => '2025020020C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '00020',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 24,
            'student_id' => '2025010021C1',
            'batch' => '2025',
            'group' => 'PN1',
            'student_number' => '00021',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 25,
            'student_id' => '2025020022C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '00022',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 26,
            'student_id' => '2025010023C1',
            'batch' => '2025',
            'group' => 'PN1',
            'student_number' => '00023',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 27,
            'student_id' => '2025020024C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '00024',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 28,
            'student_id' => '2025010025C1',
            'batch' => '2025',
            'group' => 'PN1',
            'student_number' => '00025',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 29,
            'student_id' => '2025020026C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '00026',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 30,
            'student_id' => '2025010027C1',
            'batch' => '2025',
            'group' => 'PN1',
            'student_number' => '00027',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 31,
            'student_id' => '2025020028C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '00028',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 32,
            'student_id' => '2025010029C1',
            'batch' => '2025',
            'group' => 'PN1',
            'student_number' => '00029',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 33,
            'student_id' => '2025020030C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '00030',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 34,
            'student_id' => '2025010031C1',
            'batch' => '2025',
            'group' => 'PN1',
            'student_number' => '00031',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 35,
            'student_id' => '2025020032C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '00032',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 36,
            'student_id' => '2025010033C1',
            'batch' => '2025',
            'group' => 'PN1',
            'student_number' => '00033',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 37,
            'student_id' => '2025020034C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '00034',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 38,
            'student_id' => '2025010035C1',
            'batch' => '2025',
            'group' => 'PN1',
            'student_number' => '00035',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 39,
            'student_id' => '2025020036C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '00036',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 40,
            'student_id' => '2025010037C1',
            'batch' => '2025',
            'group' => 'PN1',
            'student_number' => '00037',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 41,
            'student_id' => '2025020038C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '00038',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 42,
            'student_id' => '2025010039C1',
            'batch' => '2025',
            'group' => 'PN1',
            'student_number' => '00039',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 43,
            'student_id' => '2025020040C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '00040',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 44,
            'student_id' => '2025010041C1',
            'batch' => '2025',
            'group' => 'PN1',
            'student_number' => '00041',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 45,
            'student_id' => '2025020042C1',
            'batch' => '2025',
            'group' => 'PN2',
            'student_number' => '00042',
            'training_code' => '',
        ]);

        // Class 2026
        StudentDetail::create([
            'user_id' => 50,
            'student_id' => '2026010001C1',
            'batch' => '2026',
            'group' => 'PN1',
            'student_number' => '0001',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 51,
            'student_id' => '2026020002C1',
            'batch' => '2026',
            'group' => 'PN2',
            'student_number' => '0002',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 52,
            'student_id' => '2026010003C1',
            'batch' => '2026',
            'group' => 'PN1',
            'student_number' => '0003',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 53,
            'student_id' => '2026020004C1',
            'batch' => '2026',
            'group' => 'PN2',
            'student_number' => '0004',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 54,
            'student_id' => '2026010005C1',
            'batch' => '2026',
            'group' => 'PN1',
            'student_number' => '0005',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 55,
            'student_id' => '2026020006C1',
            'batch' => '2026',
            'group' => 'PN2',
            'student_number' => '0006',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 56,
            'student_id' => '2026010007C1',
            'batch' => '2026',
            'group' => 'PN1',
            'student_number' => '0007',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 57,
            'student_id' => '2026020008C1',
            'batch' => '2026',
            'group' => 'PN2',
            'student_number' => '0008',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 58,
            'student_id' => '2026010009C1',
            'batch' => '2026',
            'group' => 'PN1',
            'student_number' => '0009',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 59,
            'student_id' => '2026020010C1',
            'batch' => '2026',
            'group' => 'PN2',
            'student_number' => '00010',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 60,
            'student_id' => '2026010011C1',
            'batch' => '2026',
            'group' => 'PN1',
            'student_number' => '00011',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 61,
            'student_id' => '202602012C1',
            'batch' => '2026',
            'group' => 'PN2',
            'student_number' => '00012',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 62,
            'student_id' => '2026010013C1',
            'batch' => '2026',
            'group' => 'PN1',
            'student_number' => '00013',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 63,
            'student_id' => '2026020014C1',
            'batch' => '2026',
            'group' => 'PN2',
            'student_number' => '00014',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 64,
            'student_id' => '2026010015C1',
            'batch' => '2026',
            'group' => 'PN1',
            'student_number' => '00015',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 65,
            'student_id' => '2026020016C1',
            'batch' => '2026',
            'group' => 'PN2',
            'student_number' => '00016',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 66,
            'student_id' => '2026010017C1',
            'batch' => '2026',
            'group' => 'PN1',
            'student_number' => '00017',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 67,
            'student_id' => '2026020018C1',
            'batch' => '2026',
            'group' => 'PN2',
            'student_number' => '00018',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 68,
            'student_id' => '2026010019C1',
            'batch' => '2026',
            'group' => 'PN1',
            'student_number' => '00019',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 69,
            'student_id' => '2026020020C1',
            'batch' => '2026',
            'group' => 'PN2',
            'student_number' => '00020',
            'training_code' => '',
        ]);

        // Class 2027
        StudentDetail::create([
            'user_id' => 70,
            'student_id' => '2027010001C1',
            'batch' => '2027',
            'group' => 'PN1',
            'student_number' => '0001',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 71,
            'student_id' => '2027010002C1',
            'batch' => '2027',
            'group' => 'PN2',
            'student_number' => '0002',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 72,
            'student_id' => '2027010003C1',
            'batch' => '2027',
            'group' => 'PN1',
            'student_number' => '0003',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 73,
            'student_id' => '2027020004C1',
            'batch' => '2027',
            'group' => 'PN2',
            'student_number' => '0004',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 74,
            'student_id' => '2027010005C1',
            'batch' => '2027',
            'group' => 'PN1',
            'student_number' => '0005',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 75,
            'student_id' => '2027020006C1',
            'batch' => '2027',
            'group' => 'PN2',
            'student_number' => '0006',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 76,
            'student_id' => '2027010007C1',
            'batch' => '2027',
            'group' => 'PN1',
            'student_number' => '0007',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 77,
            'student_id' => '2027020008C1',
            'batch' => '2027',
            'group' => 'PN2',
            'student_number' => '0008',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 78,
            'student_id' => '2027010009C1',
            'batch' => '2027',
            'group' => 'PN1',
            'student_number' => '0009',
            'training_code' => '',
        ]);

        StudentDetail::create([
            'user_id' => 79,
            'student_id' => '2027020010C1',
            'batch' => '2027',
            'group' => 'PN2',
            'student_number' => '00010',
            'training_code' => '',
        ]);
    }
}
