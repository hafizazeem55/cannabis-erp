<?php

namespace Database\Seeders;

use App\Models\EnvironmentalThreshold;
use Illuminate\Database\Seeder;

class EnvironmentalThresholdSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            // Cloning
            ['stage' => 'cloning', 'parameter' => 'temperature', 'min' => 20, 'max' => 24, 'target' => 22, 'tol' => 3, 'severity' => 'warning'],
            ['stage' => 'cloning', 'parameter' => 'humidity', 'min' => 70, 'max' => 85, 'target' => 80, 'tol' => 3, 'severity' => 'warning'],
            ['stage' => 'cloning', 'parameter' => 'co2', 'min' => 800, 'max' => 1200, 'target' => 1000, 'tol' => 10, 'severity' => 'standard'],
            ['stage' => 'cloning', 'parameter' => 'ph', 'min' => 5.6, 'max' => 6.1, 'target' => 5.9, 'tol' => 3, 'severity' => 'standard'],
            ['stage' => 'cloning', 'parameter' => 'ec', 'min' => 0.40, 'max' => 0.80, 'target' => 0.60, 'tol' => 5, 'severity' => 'standard'],

            // Vegetative
            ['stage' => 'vegetative', 'parameter' => 'temperature', 'min' => 20, 'max' => 28, 'target' => 24, 'tol' => 5, 'severity' => 'critical'],
            ['stage' => 'vegetative', 'parameter' => 'humidity', 'min' => 50, 'max' => 70, 'target' => 60, 'tol' => 5, 'severity' => 'standard'],
            ['stage' => 'vegetative', 'parameter' => 'co2', 'min' => 1000, 'max' => 1600, 'target' => 1300, 'tol' => 10, 'severity' => 'standard'],
            ['stage' => 'vegetative', 'parameter' => 'ph', 'min' => 5.8, 'max' => 6.3, 'target' => 6.0, 'tol' => 3, 'severity' => 'standard'],
            ['stage' => 'vegetative', 'parameter' => 'ec', 'min' => 1.20, 'max' => 2.00, 'target' => 1.60, 'tol' => 7, 'severity' => 'warning'],

            // Flowering
            ['stage' => 'flowering', 'parameter' => 'temperature', 'min' => 18, 'max' => 26, 'target' => 22, 'tol' => 5, 'severity' => 'critical'],
            ['stage' => 'flowering', 'parameter' => 'humidity', 'min' => 40, 'max' => 60, 'target' => 50, 'tol' => 5, 'severity' => 'critical'],
            ['stage' => 'flowering', 'parameter' => 'co2', 'min' => 1100, 'max' => 1700, 'target' => 1400, 'tol' => 10, 'severity' => 'standard'],
            ['stage' => 'flowering', 'parameter' => 'ph', 'min' => 5.7, 'max' => 6.3, 'target' => 6.0, 'tol' => 3, 'severity' => 'standard'],
            ['stage' => 'flowering', 'parameter' => 'ec', 'min' => 1.60, 'max' => 2.40, 'target' => 2.00, 'tol' => 7, 'severity' => 'warning'],

            // Harvest
            ['stage' => 'harvest', 'parameter' => 'temperature', 'min' => 16, 'max' => 24, 'target' => 20, 'tol' => 5, 'severity' => 'warning'],
            ['stage' => 'harvest', 'parameter' => 'humidity', 'min' => 45, 'max' => 60, 'target' => 50, 'tol' => 5, 'severity' => 'warning'],
            ['stage' => 'harvest', 'parameter' => 'co2', 'min' => 800, 'max' => 1300, 'target' => 1000, 'tol' => 10, 'severity' => 'standard'],
            ['stage' => 'harvest', 'parameter' => 'ph', 'min' => 5.8, 'max' => 6.4, 'target' => 6.1, 'tol' => 3, 'severity' => 'standard'],
            ['stage' => 'harvest', 'parameter' => 'ec', 'min' => 1.00, 'max' => 1.80, 'target' => 1.30, 'tol' => 7, 'severity' => 'standard'],

            // Drying
            ['stage' => 'drying', 'parameter' => 'temperature', 'min' => 17, 'max' => 22, 'target' => 19, 'tol' => 4, 'severity' => 'warning'],
            ['stage' => 'drying', 'parameter' => 'humidity', 'min' => 50, 'max' => 60, 'target' => 55, 'tol' => 5, 'severity' => 'warning'],
            ['stage' => 'drying', 'parameter' => 'co2', 'min' => 500, 'max' => 900, 'target' => 700, 'tol' => 8, 'severity' => 'standard'],
            ['stage' => 'drying', 'parameter' => 'ph', 'min' => 5.8, 'max' => 6.3, 'target' => 6.0, 'tol' => 3, 'severity' => 'standard'],
            ['stage' => 'drying', 'parameter' => 'ec', 'min' => 0.80, 'max' => 1.20, 'target' => 1.00, 'tol' => 5, 'severity' => 'standard'],

            // Curing
            ['stage' => 'curing', 'parameter' => 'temperature', 'min' => 15, 'max' => 22, 'target' => 18, 'tol' => 4, 'severity' => 'warning'],
            ['stage' => 'curing', 'parameter' => 'humidity', 'min' => 50, 'max' => 60, 'target' => 55, 'tol' => 5, 'severity' => 'warning'],
            ['stage' => 'curing', 'parameter' => 'co2', 'min' => 500, 'max' => 900, 'target' => 700, 'tol' => 8, 'severity' => 'standard'],
            ['stage' => 'curing', 'parameter' => 'ph', 'min' => 5.8, 'max' => 6.3, 'target' => 6.0, 'tol' => 3, 'severity' => 'standard'],
            ['stage' => 'curing', 'parameter' => 'ec', 'min' => 0.80, 'max' => 1.20, 'target' => 1.00, 'tol' => 5, 'severity' => 'standard'],

            // Packaging / finished goods prep
            ['stage' => 'packaging', 'parameter' => 'temperature', 'min' => 18, 'max' => 24, 'target' => 21, 'tol' => 4, 'severity' => 'standard'],
            ['stage' => 'packaging', 'parameter' => 'humidity', 'min' => 40, 'max' => 55, 'target' => 47, 'tol' => 5, 'severity' => 'warning'],
            ['stage' => 'packaging', 'parameter' => 'co2', 'min' => 400, 'max' => 800, 'target' => 600, 'tol' => 8, 'severity' => 'standard'],
            ['stage' => 'packaging', 'parameter' => 'ph', 'min' => 5.8, 'max' => 6.3, 'target' => 6.0, 'tol' => 3, 'severity' => 'standard'],
            ['stage' => 'packaging', 'parameter' => 'ec', 'min' => 0.70, 'max' => 1.10, 'target' => 0.90, 'tol' => 5, 'severity' => 'standard'],

            // Completed
            ['stage' => 'completed', 'parameter' => 'temperature', 'min' => 15, 'max' => 22, 'target' => 18, 'tol' => 4, 'severity' => 'warning'],
            ['stage' => 'completed', 'parameter' => 'humidity', 'min' => 45, 'max' => 60, 'target' => 55, 'tol' => 5, 'severity' => 'warning'],
            ['stage' => 'completed', 'parameter' => 'co2', 'min' => 500, 'max' => 900, 'target' => 700, 'tol' => 8, 'severity' => 'standard'],
            ['stage' => 'completed', 'parameter' => 'ph', 'min' => 5.8, 'max' => 6.4, 'target' => 6.1, 'tol' => 3, 'severity' => 'standard'],
            ['stage' => 'completed', 'parameter' => 'ec', 'min' => 0.80, 'max' => 1.20, 'target' => 1.00, 'tol' => 5, 'severity' => 'standard'],
        ];

        foreach ($records as $record) {
            EnvironmentalThreshold::updateOrCreate(
                ['stage' => $record['stage'], 'parameter' => $record['parameter']],
                [
                    'min_value' => $record['min'],
                    'max_value' => $record['max'],
                    'target_value' => $record['target'],
                    'tolerance_percent' => $record['tol'],
                    'severity' => $record['severity'],
                    'is_active' => true,
                ]
            );
        }
    }
}
