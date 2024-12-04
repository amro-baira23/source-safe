<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $group = Group::inRandomOrder()->first();
        $path = fake()->numerify("fake-path###");
        Storage::makeDirectory("projects_files/$group->name$group->id");
        fopen(storage_path("app/projects_files/$group->name$group->id/$path" . "__1.txt"),"w");
        return [
            "name" => fake()->numerify("file###"),
            "group_id" => $group->id,
            "path" => $path,
            "active" => 1,
            "status" => 1,            
        ];
    }
}
