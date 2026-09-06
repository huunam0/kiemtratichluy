<?php

namespace App\Services;

class OptionShuffleService
{
    /**
     * Generate dynamic anti-cheat option mapping & display content for 2, 3, or 4 options.
     *
     * @param array $question Database row from `questions` table
     * @return array Array containing `option_mapping` (for DB) and `display_options` (for API/UI)
     */
    public function generateDynamicShuffledOptions(array $question): array
    {
        $allKeys = ['A', 'B', 'C', 'D'];
        $availableKeys = [];
        $originalContents = [];

        // Filter available options
        foreach ($allKeys as $key) {
            $colName = 'option_' . strtolower($key);
            if (isset($question[$colName]) && trim((string)$question[$colName]) !== '') {
                $availableKeys[] = $key;
                $originalContents[$key] = $question[$colName];
            }
        }

        // Shuffle available keys
        $shuffledKeys = $availableKeys;
        shuffle($shuffledKeys);

        // Display labels match available count: ['A', 'B'] for 2-options, ['A', 'B', 'C'] for 3-options, etc.
        $displayLabels = array_slice(['A', 'B', 'C', 'D'], 0, count($availableKeys));

        $optionMapping = [];
        $displayOptions = [];

        foreach ($displayLabels as $idx => $displayLabel) {
            $origKey = $shuffledKeys[$idx];
            $optionMapping[$displayLabel] = $origKey;
            $displayOptions[] = [
                'label'   => $displayLabel,
                'content' => $originalContents[$origKey]
            ];
        }

        return [
            'option_mapping'  => $optionMapping,
            'display_options' => $displayOptions
        ];
    }

    /**
     * Evaluate student choice against original correct option.
     *
     * @param string $studentChoice Displayed label selected by student ('A', 'B', etc.)
     * @param array $optionMapping Decoded mapping array
     * @param string $correctOption Original correct option ('A', 'B', 'C', 'D')
     * @return array ['is_correct' => bool, 'original_choice' => string]
     */
    public function evaluateChoice(string $studentChoice, array $optionMapping, string $correctOption): array
    {
        if (!isset($optionMapping[$studentChoice])) {
            return [
                'is_correct'      => false,
                'original_choice' => null
            ];
        }

        $originalChoice = $optionMapping[$studentChoice];
        return [
            'is_correct'      => ($originalChoice === $correctOption),
            'original_choice' => $originalChoice
        ];
    }
}
