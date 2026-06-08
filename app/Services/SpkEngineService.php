<?php

namespace App\Services;

use App\Models\User;
use App\Models\Scholarship;
use App\Models\Weighting;
use Illuminate\Support\Collection;

class SpkEngineService
{
    /**
     * Run the complete SPK Matchmaking engine (Filtering + SAW Ranking) for a user.
     * 
     * @param User $user
     * @return Collection
     */
    public function calculateRecommendations(User $user): Collection
    {
        // Fetch active scholarships
        $scholarships = Scholarship::where('status_aktif', true)->get();

        // 1. TAHAP 1: Filtering Stage
        $filtered = $this->filterScholarships($scholarships, $user);

        if ($filtered->isEmpty()) {
            return collect();
        }

        // 2. TAHAP 2: Convert Raw Values to Eligibility Scores (Scale 1-5)
        $decisionMatrix = $this->buildDecisionMatrix($filtered, $user);

        // 3. TAHAP 3: Normalization and SAW Preference Value Calculation
        return $this->calculateSawRankings($decisionMatrix);
    }

    /**
     * Stage 1: Filter scholarships based on user's IPK and parent income.
     */
    private function filterScholarships(Collection $scholarships, User $user): Collection
    {
        return $scholarships->filter(function (Scholarship $scholarship) use ($user) {
            // Filter IPK: IPK Mahasiswa >= IPK Minimum Syarat Beasiswa
            $ipkOk = $user->ipk >= $scholarship->ipk_minimum;

            // Filter Penghasilan: Penghasilan Orang Tua <= Batas Maksimum Penghasilan Beasiswa
            $penghasilanOk = $user->penghasilan_ortu <= $scholarship->batas_penghasilan;

            // Filter Batas Waktu: Batas waktu pendaftaran belum terlampaui
            $waktuOk = true;
            if ($scholarship->batas_waktu) {
                $waktuOk = $scholarship->batas_waktu->isFuture();
            }

            return $ipkOk && $penghasilanOk && $waktuOk;
        });
    }

    /**
     * Stage 2: Convert raw parameters of filtered scholarships into Eligibility Scores (Scale 1-5).
     */
    private function buildDecisionMatrix(Collection $scholarships, User $user): Collection
    {
        return $scholarships->map(function (Scholarship $scholarship) use ($user) {
            // C1: Kesesuaian Semester (Semester Range)
            // Range Semester = semester_max - semester_min + 1
            $c1Raw = $scholarship->semester_max - $scholarship->semester_min + 1;

            // C2: Status Akademik (Pre-defined score from database)
            $c2Raw = $scholarship->skor_status_c2;

            // C3: Kesesuaian IPK (Surplus IPK score: Scale 1-5)
            $c3Raw = $this->calculateC3Score($user->ipk, $scholarship->ipk_minimum);

            // C4: Kesesuaian Penghasilan (Rasio Penghasilan score: Scale 1-5)
            $c4Raw = $this->calculateC4Score($user->penghasilan_ortu, $scholarship->batas_penghasilan);

            return [
                'scholarship' => $scholarship,
                'raw_values' => [
                    'C1' => $c1Raw,
                    'C2' => $c2Raw,
                    'C3' => $c3Raw,
                    'C4' => $c4Raw,
                ]
            ];
        });
    }

    /**
     * Calculate C3 Score based on Surplus IPK (User IPK - Min IPK).
     */
    public function calculateC3Score(float $userIpk, float $minIpk): int
    {
        $surplus = $userIpk - $minIpk;

        if ($surplus < 0) {
            return 0; // Gugur / Ineligible
        }
        if ($surplus < 0.10) {
            return 1;
        }
        if ($surplus < 0.30) {
            return 2;
        }
        if ($surplus < 0.50) {
            return 3;
        }
        if ($surplus < 0.75) {
            return 4;
        }
        return 5;
    }

    /**
     * Calculate C4 Score based on Rasio Penghasilan (User Income / Max Limit).
     */
    public function calculateC4Score(float $userPenghasilan, float $maxPenghasilan): int
    {
        if ($maxPenghasilan <= 0) {
            return 1;
        }

        $rasio = $userPenghasilan / $maxPenghasilan;

        if ($rasio > 1.00) {
            return 0; // Gugur / Ineligible
        }
        if ($rasio > 0.85) {
            return 1;
        }
        if ($rasio > 0.65) {
            return 2;
        }
        if ($rasio > 0.45) {
            return 3;
        }
        if ($rasio > 0.25) {
            return 4;
        }
        return 5;
    }

    /**
     * Stage 3: Normalize decision matrix and calculate preference values (Vi) using SAW.
     */
    private function calculateSawRankings(Collection $decisionMatrix): Collection
    {
        // Fetch current criteria weights from database
        $dbWeights = Weighting::join('criteria', 'weighting.criteria_id', '=', 'criteria.id')
            ->pluck('weighting.bobot', 'criteria.kode_kriteria')
            ->toArray();

        // Default weights as a fallback (matching PDF definitions)
        $weights = array_merge([
            'C1' => 0.25,
            'C2' => 0.20,
            'C3' => 0.30,
            'C4' => 0.25
        ], $dbWeights);

        // Find max value for each criterion (all criteria are Benefit)
        $maxValues = [
            'C1' => $decisionMatrix->max('raw_values.C1') ?: 1,
            'C2' => $decisionMatrix->max('raw_values.C2') ?: 1,
            'C3' => $decisionMatrix->max('raw_values.C3') ?: 1,
            'C4' => $decisionMatrix->max('raw_values.C4') ?: 1,
        ];

        // Perform normalisation and calculate final score (Vi)
        $scored = $decisionMatrix->map(function ($item) use ($maxValues, $weights) {
            $normalized = [];
            $vi = 0.0;

            foreach (['C1', 'C2', 'C3', 'C4'] as $criteria) {
                $raw = $item['raw_values'][$criteria];
                $max = $maxValues[$criteria];
                
                // Normalization formula for Benefit criteria: rij = xij / max(xij)
                $r = $max > 0 ? (float) ($raw / $max) : 0.0;
                $normalized[$criteria] = $r;

                // Weighted preference contribution: Vi += Wj * rij
                $vi += $weights[$criteria] * $r;
            }

            // Map Vi score to readable percentage and recommendation status
            $percentage = round($vi * 100, 1);
            $rekomendasi = 'TIDAK DIREKOMENDASIKAN';
            if ($vi >= 0.80) {
                $rekomendasi = 'SANGAT DIREKOMENDASIKAN';
            } elseif ($vi >= 0.50) {
                $rekomendasi = 'DIREKOMENDASIKAN';
            }

            return [
                'scholarship' => $item['scholarship'],
                'raw_values' => $item['raw_values'],
                'normalized_values' => $normalized,
                'nilai_preferensi' => round($vi, 6),
                'skor_persen' => $percentage,
                'rekomendasi' => $rekomendasi,
            ];
        });

        // Sort by Vi descending and assign ranks
        $ranked = $scored->sortByDesc('nilai_preferensi')->values();

        return $ranked->map(function ($item, $index) {
            $item['peringkat'] = $index + 1;
            return $item;
        });
    }
}
