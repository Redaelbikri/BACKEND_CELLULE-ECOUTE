<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@cellule.com'],
            [
                'name' => 'Cellule Admin',
                'password' => Hash::make('password123'),
                'role' => RoleEnum::ADMIN->value,
                'status' => UserStatusEnum::ACTIVE->value,
                'email_verified_at' => now(),
            ]
        );

        $students = [
            ['name' => 'Sara Etudiante', 'email' => 'student1@cellule.com'],
            ['name' => 'Youssef Etudiant', 'email' => 'student2@cellule.com'],
            ['name' => 'Imane Etudiante', 'email' => 'student3@cellule.com'],
        ];

        foreach ($students as $student) {
            User::query()->updateOrCreate(
                ['email' => $student['email']],
                [
                    'name' => $student['name'],
                    'password' => Hash::make('password123'),
                    'role' => RoleEnum::STUDENT->value,
                    'status' => UserStatusEnum::ACTIVE->value,
                    'email_verified_at' => now(),
                ]
            );
        }

        $counselors = [
            ['name' => 'Nadia Conseillere', 'email' => 'counselor1@cellule.com'],
            ['name' => 'Omar Conseiller', 'email' => 'counselor2@cellule.com'],
            ['name' => 'Salma Conseillere', 'email' => 'counselor3@cellule.com'],
        ];

        foreach ($counselors as $counselor) {
            User::query()->updateOrCreate(
                ['email' => $counselor['email']],
                [
                    'name' => $counselor['name'],
                    'password' => Hash::make('password123'),
                    'role' => RoleEnum::COUNSELOR->value,
                    'status' => UserStatusEnum::ACTIVE->value,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
