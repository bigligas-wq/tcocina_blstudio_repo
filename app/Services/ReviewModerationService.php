<?php

namespace App\Services;

use App\Models\ProductReview;

class ReviewModerationService
{
    /**
     * Basic content filtering for reviews.
     */
    public function autoModerate(ProductReview $review): bool
    {
        // List of prohibited words (can be expanded)
        $prohibitedWords = [
            'spam', 'scam', 'fake', 'bot',
        ];

        $comment = strtolower($review->comment ?? '');

        foreach ($prohibitedWords as $word) {
            if (str_contains($comment, $word)) {
                return false; // Flag for manual review
            }
        }

        // Check for excessive capitalization
        if ($review->comment && $this->hasExcessiveCaps($review->comment)) {
            return false;
        }

        // Check for excessive repetition
        if ($review->comment && $this->hasExcessiveRepetition($review->comment)) {
            return false;
        }

        return true;
    }

    /**
     * Check for excessive capitalization.
     */
    protected function hasExcessiveCaps(string $text): bool
    {
        $upper = preg_match_all('/[A-Z]/', $text);
        $lower = preg_match_all('/[a-z]/', $text);
        $total = $upper + $lower;

        if ($total === 0) return false;

        return ($upper / $total) > 0.5;
    }

    /**
     * Check for excessive repetition.
     */
    protected function hasExcessiveRepetition(string $text): bool
    {
        $words = explode(' ', strtolower($text));
        $wordCounts = array_count_values($words);

        foreach ($wordCounts as $count) {
            if ($count > 3) {
                return true;
            }
        }

        return false;
    }

    /**
     * Basic spam detection.
     */
    public function checkForSpam(ProductReview $review): bool
    {
        // Check if user has submitted multiple reviews in short time
        $recentReviews = ProductReview::where('user_id', $review->user_id)
            ->where('created_at', '>', now()->subHours(1))
            ->count();

        if ($recentReviews > 3) {
            return true;
        }

        // Check for identical comments
        $identicalReviews = ProductReview::where('comment', $review->comment)
            ->where('id', '!=', $review->id)
            ->count();

        if ($identicalReviews > 0) {
            return true;
        }

        return false;
    }
}
