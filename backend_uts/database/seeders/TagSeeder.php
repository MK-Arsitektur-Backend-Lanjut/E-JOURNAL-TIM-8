<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            'Computer Science',
            'Data Science',
            'Machine Learning',
            'Artificial Intelligence',
            'Cyber Security',
            'Software Engineering',
            'Information Systems',
            'Database Systems',
            'Network Engineering',
            'Cloud Computing',
            'Internet of Things',
            'Digital Library',
            'Educational Technology',
            'Project Management',
            'Business Intelligence',
            'Health Informatics',
            'Knowledge Management',
            'Research Methodology',
            'Human Computer Interaction',
            'Mobile Development',
        ];

        foreach ($subjects as $subject) {
            Tag::firstOrCreate(
                ['slug' => Str::slug($subject)],
                ['name' => $subject]
            );
        }
    }
}
