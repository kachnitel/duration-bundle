<?php

namespace Kachnitel\DurationBundle\Service;

use DateInterval;
use DateTimeImmutable;

/**
 * Utility service for parsing, formatting, and converting durations.
 *
 * This service provides comprehensive duration handling including:
 * - Converting between DateInterval and seconds
 * - Parsing human-readable duration strings (e.g., "2h 30m", "HH:MM:SS", "2.5 hours")
 * - Formatting durations with customizable units and display formats
 */
class DurationUtil
{
    public const DATETIME_LOCAL_FORMAT = 'Y-m-d\TH:i';
    public const UNIT_CONFIG = [
        'y' => [
            'suffix' => [
                'short' => 'y',
                'long' => 'year',
                'longPlural' => 'years'
            ],
            'seconds' => 31536000
        ],
        'mo' => [
            'suffix' => [
                'short' => 'mo',
                'long' => 'month',
                'longPlural' => 'months'
            ],
            'seconds' => 2592000
        ],
        'w' => [
            'suffix' => [
                'short' => 'w',
                'long' => 'week',
                'longPlural' => 'weeks'
            ],
            'seconds' => 604800
        ],
        'd' => [
            'suffix' => [
                'short' => 'd',
                'long' => 'day',
                'longPlural' => 'days'
            ],
            'seconds' => 86400
        ],
        'h' => [
            'suffix' => [
                'short' => 'h',
                'long' => 'hour',
                'longPlural' => 'hours'
            ],
            'seconds' => 3600
        ],
        'm' => [
            'suffix' => [
                'short' => 'm',
                'long' => 'minute',
                'longPlural' => 'minutes'
            ],
            'seconds' => 60
        ],
        's' => [
            'suffix' => [
                'short' => 's',
                'long' => 'second',
                'longPlural' => 'seconds'
            ],
            'seconds' => 1
        ]
    ];

    /**
     * Convert a DateInterval to seconds.
     *
     * @param DateInterval $interval The interval to convert
     * @return int Total seconds represented by the interval
     */
    public static function intervalToSeconds(DateInterval $interval): int
    {
        return DateTimeImmutable::createFromFormat('U', '0')->add($interval)->getTimestamp();
    }

    /**
     * Convert seconds to a DateInterval.
     *
     * @param int $seconds Number of seconds
     * @return DateInterval DateInterval representing the duration
     */
    public static function secondsToInterval(int $seconds): DateInterval
    {
        return (DateTimeImmutable::createFromFormat('U', '0'))
            ->diff(DateTimeImmutable::createFromFormat('U', (string) $seconds));
    }

    /**
     * Convert seconds to a human-readable string.
     *
     * @param int|null $value Duration in seconds
     * @param bool $short Use short format (e.g., "2h 30m") vs long format (e.g., "2 hours 30 minutes")
     * @param array $units Array of units to include in output (e.g., ['h', 'm', 's'])
     * @return string Formatted duration string
     *
     * @example
     *   toString(3661) // "1h 1m 1s"
     *   toString(3661, false) // "1 hour 1 minute 1 second"
     *   toString(3661, true, ['h', 'm']) // "1h 1m"
     */
    public static function toString(
        ?int $value,
        bool $short = true,
        array $units = ['y', 'mo', 'w', 'd', 'h', 'm', 's']
    ): string {
        $duration = '';
        $seconds = (int) $value;

        $unitConfig = array_intersect_key(self::UNIT_CONFIG, array_flip($units));

        foreach ($unitConfig as $unit => $config) {
            $value = $seconds / $config['seconds'];

            // Always round down for clean display
            if ($value > 0) {
                $value = floor($value);
            }

            if ($value > 0) {
                $duration .= $value;
                $duration .= $short
                    ? $config['suffix']['short']
                    : ' ' . ($value > 1 ? $config['suffix']['longPlural'] : $config['suffix']['long']);
                $duration .= ' ';
            }
            $seconds %= $config['seconds'];
        }

        return trim($duration);
    }

    /**
     * Format seconds as HH:MM.
     *
     * @param int $seconds Number of seconds
     * @return string Formatted time string (e.g., "02:30")
     */
    public static function toHhMm(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Parse a duration string to seconds.
     *
     * Supports multiple formats:
     * - HH:MM:SS (e.g., "02:30:45")
     * - HH:MM (e.g., "02:30")
     * - H:M:S with variable digits (e.g., "2:5:3")
     * - Plain seconds (e.g., "150")
     * - Human-readable with units (e.g., "2h 30m", "2.5 hours", "90 minutes")
     *
     * @param string $value Duration string to parse
     * @return int Total seconds
     *
     * @example
     *   toSeconds("02:30") // 9000
     *   toSeconds("2h 30m") // 9000
     *   toSeconds("2.5 hours") // 9000
     *   toSeconds("150 minutes") // 9000
     */
    public static function toSeconds(string $value): int
    {
        // support all generated strings + HH:MM:SS / HH:MM / H:M:S / H:M / HH:M etc..
        if (preg_match('/^(\d+):(\d+):(\d+)$/', $value, $matches)) {
            $matches = array_map('intval', $matches);
            return $matches[1] * 3600 + $matches[2] * 60 + $matches[3];
        } elseif (preg_match('/^(\d+):(\d+)$/', $value, $matches)) {
            $matches = array_map('intval', $matches);
            return $matches[1] * 3600 + $matches[2] * 60;
        } elseif (preg_match('/^(\d+)$/', $value, $matches)) {
            return intval($value);
        }

        // parse string to int(seconds)
        $duration = 0;
        $matches = [];
        // support . or , as decimal separator
        preg_match_all('/(\d+(?:\.\d+)?|\d+(?:,\d+)?|\d+)\s*([a-z]+)/', $value, $matches);

        foreach ($matches[1] as $key => $match) {
            $unit = $matches[2][$key];

            foreach (self::UNIT_CONFIG as $config) {
                if (in_array($unit, $config['suffix'])) {
                    $duration += floatval($match) * $config['seconds'];
                    break;
                }
            }
        }

        return $duration;
    }
}
