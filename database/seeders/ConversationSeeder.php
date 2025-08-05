<?php

namespace Database\Seeders;

use App\Models\ConversationType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConversationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['type' => 'Personal', 'created_at' => now()],
            ['type' => 'Group', 'created_at' => now()],
        ];

        ConversationType::insert($types);
    }
}
