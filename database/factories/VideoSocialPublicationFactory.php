<?php

namespace Database\Factories;

use App\Models\YoutubeJsonTracker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VideoSocialPublication>
 */
class VideoSocialPublicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'youtube_json_tracker_id' => YoutubeJsonTracker::factory(),
            'platform' => $this->faker->randomElement(['facebook', 'tiktok']),
            'publicado' => $this->faker->randomElement(['si', 'no']),
            'tipo' => $this->faker->randomElement(['Corto short', 'Video-normal-corto', 'Video-normal']),
            'fecha' => $this->faker->date(),
            'link' => $this->faker->url(),
            'formato' => $this->faker->randomElement(['Vertical', 'Horizontal', 'Cuadrado']),
        ];
    }
}
