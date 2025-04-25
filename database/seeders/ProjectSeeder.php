<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectLink;
use App\Models\ProjectURL;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::factory()->count(25)->create()->each(function ($project) {
            // attach tags
            $project->tags()->attach(Tag::factory(5)->create());

            // attach repo url
            $project->urls()->create([
                'repository_url' => fake()->url(),
            ]);

            // attach link
            $project->links()->create([
                'url' => fake()->url(),
                'type' => fake()->randomElement(['website', 'documentation', 'blog']),
            ]);

            // attach owner
            $project->users()->attach(User::inRandomOrder()->first(), ['role' => 'owner']);
        });
    }
}
