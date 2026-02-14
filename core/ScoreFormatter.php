<?php
/**
 * Score Formatter - Formats scores for display
 * Displays as 00.00 (2 decimal places) but rounds based on 3rd decimal place
 */

class ScoreFormatter {
    /**
     * Format score for display
     * Displays 2 decimal places (00.00) but rounds based on 3rd decimal place
     * 
     * @param float|string $score The score to format
     * @param int $decimals Number of decimal places to display (default: 2)
     * @return string Formatted score
     */
    public static function format($score, $decimals = 2) {
        if ($score === null || $score === '') {
            return '0.00';
        }
        
        $score = (float)$score;
        
        // Round to 2 decimal places (checking 3rd decimal for rounding)
        // PHP's round() function already does this correctly
        // round(85.234, 2) = 85.23
        // round(85.235, 2) = 85.24
        $rounded = round($score, $decimals);
        
        // Format with exactly 2 decimal places
        return number_format($rounded, $decimals, '.', '');
    }
    
    /**
     * Format score with thousands separator (for reports)
     * 
     * @param float|string $score The score to format
     * @param int $decimals Number of decimal places to display (default: 2)
     * @return string Formatted score with thousands separator
     */
    public static function formatWithSeparator($score, $decimals = 2) {
        if ($score === null || $score === '') {
            return number_format(0, $decimals);
        }
        
        $score = (float)$score;
        $rounded = round($score, $decimals);
        
        return number_format($rounded, $decimals);
    }
}
