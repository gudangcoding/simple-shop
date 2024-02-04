<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Barang>
 */
class BarangFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $barang =  [
            'nama_barang' => $this->faker->word,
            'harga' => $this->faker->randomFloat(2, 10, 100000),
            'stok' => $this->faker->numberBetween(1, 100),
            'keterangan' => $this->faker->paragraph,
            'gambar' => $this->faker->imageUrl(),
            'gambar2' => [
                $this->faker->imageUrl(),
                $this->faker->imageUrl(),
                $this->faker->imageUrl(),
                $this->faker->imageUrl(),
            ]

        ];
        // dd($barang);
        return $barang;
    }
}
