<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PeopleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'        => 'Admin',
                'password'    => Hash::make('password'),
                'share_token' => Str::random(32),
            ]
        );

        if (! $user->share_token) {
            $user->update(['share_token' => Str::random(32)]);
        }

        $people = [
            // Generation 1 (Great-grandparents)
            ['id' => 1, 'name' => 'William Thompson', 'gender' => 'male',   'birth_date' => '1920-03-15', 'death_date' => '1995-11-02', 'parent_id' => null],
            ['id' => 2, 'name' => 'Eleanor Thompson', 'gender' => 'female', 'birth_date' => '1923-07-08', 'death_date' => '2001-04-17', 'parent_id' => null],

            // Generation 2 (Grandparents) - children of William & Eleanor
            ['id' => 3, 'name' => 'Robert Thompson',  'gender' => 'male',   'birth_date' => '1948-05-21', 'death_date' => null,         'parent_id' => 1],
            ['id' => 4, 'name' => 'Margaret Thompson','gender' => 'female', 'birth_date' => '1951-09-14', 'death_date' => null,         'parent_id' => 1],
            ['id' => 5, 'name' => 'Charles Thompson', 'gender' => 'male',   'birth_date' => '1953-12-03', 'death_date' => '2020-06-30', 'parent_id' => 1],

            // Generation 3 (Parents) - children of Robert
            ['id' => 6, 'name' => 'James Thompson',   'gender' => 'male',   'birth_date' => '1975-02-18', 'death_date' => null,         'parent_id' => 3],
            ['id' => 7, 'name' => 'Sarah Thompson',   'gender' => 'female', 'birth_date' => '1977-08-25', 'death_date' => null,         'parent_id' => 3],
            ['id' => 8, 'name' => 'Michael Thompson', 'gender' => 'male',   'birth_date' => '1980-11-09', 'death_date' => null,         'parent_id' => 3],

            // Generation 3 (Parents) - children of Margaret
            ['id' => 9, 'name' => 'Emily Clarke',     'gender' => 'female', 'birth_date' => '1979-04-12', 'death_date' => null,         'parent_id' => 4],
            ['id' => 10,'name' => 'David Clarke',     'gender' => 'male',   'birth_date' => '1982-01-30', 'death_date' => null,         'parent_id' => 4],

            // Generation 4 (Children) - children of James
            ['id' => 11,'name' => 'Olivia Thompson',  'gender' => 'female', 'birth_date' => '2002-06-17', 'death_date' => null,         'parent_id' => 6],
            ['id' => 12,'name' => 'Noah Thompson',    'gender' => 'male',   'birth_date' => '2005-03-22', 'death_date' => null,         'parent_id' => 6],

            // Generation 4 (Children) - children of Sarah
            ['id' => 13,'name' => 'Ava Thompson',     'gender' => 'female', 'birth_date' => '2004-09-05', 'death_date' => null,         'parent_id' => 7],

            // Generation 4 (Children) - children of Emily
            ['id' => 14,'name' => 'Liam Clarke',      'gender' => 'male',   'birth_date' => '2006-12-01', 'death_date' => null,         'parent_id' => 9],
            ['id' => 15,'name' => 'Sophia Clarke',    'gender' => 'female', 'birth_date' => '2008-07-19', 'death_date' => null,         'parent_id' => 9],
        ];

        foreach ($people as $person) {
            \App\Models\Person::create(array_merge($person, ['user_id' => $user->id]));
        }
    }
}
