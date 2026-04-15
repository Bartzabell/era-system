<?php

namespace App\Services;

class PriorityScoreCalculator
{
    /**
     * Convert distance in km to 0-10 scale
     * 0-1 km = 0 points
     * 20 km = 10 points
     * Formula: (distance_km / 20) * 10, capped at 10
     */
    private function distanceToScore($distanceKm)
    {
        if ($distanceKm === null || $distanceKm <= 1) {
            return 0;
        }
        
        $score = ($distanceKm / 20) * 10;
        return min($score, 10); // Cap at 10
    }

    /**
     * Calculate priority score
     * Formula: (base_severity × 0.35) + (base_time × 0.25) + 
     *          (distance × 0.20) + (base_resources × 0.10) + (base_secondary × 0.10)
     */
    public function calculate($incident, $distanceKm = null)
    {
        // ✅ Handle null incident (quick reports)
        if (!$incident) {
            return [
                'priority_score' => 0,
                'priority_level' => 'P5',
                'priority_label' => 'Informational'
            ];
        }

        $baseSeverity = $incident->base_severity ?? 0;
        $baseTime = $incident->base_time ?? 0;
        $baseResources = $incident->base_resources ?? 0;
        $baseSecondary = $incident->base_secondary ?? 0;

        $distanceScore = $this->distanceToScore($distanceKm);

        $priorityScore = 
            ($baseSeverity * 0.35) + 
            ($baseTime * 0.25) + 
            ($distanceScore * 0.20) + 
            ($baseResources * 0.10) + 
            ($baseSecondary * 0.10);

        // Round to 2 decimal places
        $priorityScore = round($priorityScore, 2);

        return [
            'priority_score' => $priorityScore,
            'priority_level' => $this->getPriorityLevel($priorityScore),
            'priority_label' => $this->getPriorityLabel($priorityScore)
        ];
    }

    /**
     * Get priority level (P1-P5)
     */
    private function getPriorityLevel($score)
    {
        if ($score >= 8.5) return 'P1';
        if ($score >= 6.5) return 'P2';
        if ($score >= 4.5) return 'P3';
        if ($score >= 2.5) return 'P4';
        return 'P5';
    }

    /**
     * Get priority label
     */
    private function getPriorityLabel($score)
    {
        if ($score >= 8.5) return 'Critical';
        if ($score >= 6.5) return 'High';
        if ($score >= 4.5) return 'Moderate';
        if ($score >= 2.5) return 'Low';
        return 'Informational';
    }
}