<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\YoutubeJsonTracker>
 */
class YoutubeJsonTrackerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tipo_aparato' => $this->faker->randomElement(['Lavadora', 'Refrigerador', 'Secadora', 'Microondas']),
            'marca' => $this->faker->randomElement(['Samsung', 'LG', 'Whirlpool', 'Mabe']),
            'kg' => $this->faker->randomFloat(2, 5, 20),
            'modelo_comercial' => $this->faker->word(),
            'modelo' => strtoupper($this->faker->bothify('??####')),
            'tecnologia' => $this->faker->randomElement(['Inverter', 'Convencional']),
            'tema_error' => $this->faker->sentence(3),
            'guardado_original' => $this->faker->randomElement(['si', 'no']),
            'ruta_ubicacion_original' => 'D:/videos/originales/' . $this->faker->uuid(),
            'guardado_editado' => $this->faker->randomElement(['si', 'no']),
            'ruta_ubicacion_editado' => 'D:/videos/editados/' . $this->faker->uuid(),
            'fecha_subido' => $this->faker->date(),
            'tipo_video' => $this->faker->randomElement(['Corto short', 'Largo', 'Tutorial', 'En vivo']),
            'titulo' => $this->faker->sentence(4),
            'mes' => $this->faker->randomElement([
                'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
            ]),
            'red_social' => $this->faker->randomElement(['YouTube', 'TikTok', 'Facebook', 'Instagram', 'Otra']),
            'formato' => $this->faker->randomElement(['Vertical', 'Horizontal', 'Cuadrado']),
            'youtube_link' => 'https://www.youtube.com/watch?v=' . $this->faker->bothify('###########'),
            'link_lista' => null,
            'publicado_comunidad' => $this->faker->randomElement(['si', 'no']),
            'descripcion' => $this->faker->paragraph(),
        ];
    }
}
