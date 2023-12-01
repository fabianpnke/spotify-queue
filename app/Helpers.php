<?php

namespace App;

class Helpers
{
    public static function formatMilliseconds(float $duration_ms): string
    {
        $total_seconds = $duration_ms / 1000;

        $minutes = floor($total_seconds / 60);
        $seconds = $total_seconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public static function formatMillisecondsQueue(float $duration_ms): string
    {
        $total_seconds = $duration_ms / 1000;

        $minutes = floor($total_seconds / 60);

        return $minutes < 1
            ? 'folgt gleich'
            : 'in ca. '.$minutes.' Min.';
    }
}
