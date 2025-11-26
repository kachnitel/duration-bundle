<?php

namespace Kachnitel\DurationBundle\Tests\Unit;

use DateInterval;
use Kachnitel\DurationBundle\Service\DurationUtil;
use PHPUnit\Framework\TestCase;

class DurationUtilTest extends TestCase
{
    /**
     * @dataProvider toSecondsProvider
     */
    public function testToSeconds(string $input, int $expected): void
    {
        $result = DurationUtil::toSeconds($input);
        $this->assertEquals($expected, $result, "Failed parsing: {$input}");
    }

    public static function toSecondsProvider(): array
    {
        return [
            // HH:MM:SS format
            ['02:30:45', 9045],
            ['00:00:01', 1],
            ['01:00:00', 3600],
            ['10:30:15', 37815],

            // HH:MM format
            ['02:30', 9000],
            ['00:30', 1800],
            ['10:00', 36000],
            ['1:30', 5400],

            // Plain seconds
            ['150', 150],
            ['3600', 3600],

            // Human-readable with short units
            ['2h 30m', 9000],
            ['2h30m', 9000],
            ['90m', 5400],
            ['1h', 3600],
            ['30s', 30],
            ['1d 2h 30m', 95400],

            // Human-readable with long units
            ['2 hours 30 minutes', 9000],
            ['2.5 hours', 9000],
            ['90 minutes', 5400],
            ['1 hour', 3600],
            ['30 seconds', 30],

            // Mixed formats
            ['1h 30m 45s', 5445],
            ['2d 3h 15m', 184500],

            // Edge cases
            ['0', 0],
            ['1s', 1],
        ];
    }

    /**
     * @dataProvider toStringProvider
     */
    public function testToString(?int $seconds, bool $short, array $units, string $expected): void
    {
        $result = DurationUtil::toString($seconds, $short, $units);
        $this->assertEquals($expected, $result);
    }

    public static function toStringProvider(): array
    {
        return [
            // Short format
            [9000, true, ['y', 'mo', 'w', 'd', 'h', 'm', 's'], '2h 30m'],
            [3661, true, ['h', 'm', 's'], '1h 1m 1s'],
            [3600, true, ['h', 'm'], '1h'],
            [90, true, ['h', 'm', 's'], '1m 30s'],
            [0, true, ['h', 'm', 's'], ''],
            [null, true, ['h', 'm', 's'], ''],

            // Long format
            [9000, false, ['y', 'mo', 'w', 'd', 'h', 'm', 's'], '2 hours 30 minutes'],
            [3661, false, ['h', 'm', 's'], '1 hour 1 minute 1 second'],
            [3600, false, ['h', 'm'], '1 hour'],
            [7200, false, ['h', 'm'], '2 hours'],

            // Custom units
            [93784, true, ['h', 'm'], '26h 3.0666666666667m'],
            [93784, true, ['d', 'h', 'm'], '1d 2h 3.0666666666667m'],
            [604800, true, ['w'], '1w'],
            [86400, true, ['d'], '1d'],

            // Decimal handling for last unit
            [90, true, ['m'], '1.5m'],
            [90, false, ['m'], '1.5 minutes'],
            [3690, true, ['h', 'm'], '1h 1.5m'],

            // Edge cases
            [1, true, ['s'], '1s'],
            [1, false, ['s'], '1 second'],
            [2, false, ['s'], '2 seconds'],
        ];
    }

    public function testToHhMm(): void
    {
        $this->assertEquals('02:30', DurationUtil::toHhMm(9000));
        $this->assertEquals('00:00', DurationUtil::toHhMm(0));
        $this->assertEquals('01:00', DurationUtil::toHhMm(3600));
        $this->assertEquals('00:01', DurationUtil::toHhMm(60));
        $this->assertEquals('10:30', DurationUtil::toHhMm(37800));
        $this->assertEquals('00:00', DurationUtil::toHhMm(45)); // 45 seconds rounds down to 00:00
    }

    public function testIntervalToSeconds(): void
    {
        $interval = new DateInterval('PT2H30M');
        $this->assertEquals(9000, DurationUtil::intervalToSeconds($interval));

        $interval = new DateInterval('PT1H');
        $this->assertEquals(3600, DurationUtil::intervalToSeconds($interval));

        $interval = new DateInterval('P1D');
        $this->assertEquals(86400, DurationUtil::intervalToSeconds($interval));

        $interval = new DateInterval('PT30S');
        $this->assertEquals(30, DurationUtil::intervalToSeconds($interval));
    }

    public function testSecondsToInterval(): void
    {
        $interval = DurationUtil::secondsToInterval(9000);
        $this->assertInstanceOf(DateInterval::class, $interval);
        $this->assertEquals(2, $interval->h);
        $this->assertEquals(30, $interval->i);

        $interval = DurationUtil::secondsToInterval(3600);
        $this->assertEquals(1, $interval->h);
        $this->assertEquals(0, $interval->i);

        $interval = DurationUtil::secondsToInterval(86400);
        $this->assertEquals(1, $interval->d);
    }

    public function testRoundTripConversion(): void
    {
        $testCases = [9000, 3600, 86400, 5445, 0];

        foreach ($testCases as $seconds) {
            $interval = DurationUtil::secondsToInterval($seconds);
            $convertedBack = DurationUtil::intervalToSeconds($interval);
            $this->assertEquals($seconds, $convertedBack, "Round trip failed for {$seconds} seconds");
        }
    }

    public function testToStringRoundTrip(): void
    {
        $testCases = [9000, 3600, 5400, 3661];

        foreach ($testCases as $seconds) {
            $string = DurationUtil::toString($seconds);
            $convertedBack = DurationUtil::toSeconds($string);
            $this->assertEquals($seconds, $convertedBack, "Round trip failed for {$seconds} seconds via '{$string}'");
        }
    }
}
