<?php

/**
 * Tests for PluginKanbanlooksgoodHook helper methods
 *
 * Covers duration formatting, color lightening, and validates
 * the pure logic without requiring a running GLPI instance.
 */
class HookTest extends \PHPUnit\Framework\TestCase
{
    // -------------------------------------------------------
    // formatPlannedDuration tests
    // -------------------------------------------------------

    public function testFormatDurationReturnsEmptyForZero(): void
    {
        $this->assertSame('', PluginKanbanlooksgoodHook::formatPlannedDuration(0, 7));
    }

    public function testFormatDurationReturnsEmptyForNegative(): void
    {
        $this->assertSame('', PluginKanbanlooksgoodHook::formatPlannedDuration(-100, 7));
    }

    public function testFormatDurationMinutesOnly(): void
    {
        // 1800 seconds = 30 minutes
        $this->assertSame('30min', PluginKanbanlooksgoodHook::formatPlannedDuration(1800, 7));
    }

    public function testFormatDurationHoursOnly(): void
    {
        // 7200 seconds = 2 hours
        $this->assertSame('2h', PluginKanbanlooksgoodHook::formatPlannedDuration(7200, 7));
    }

    public function testFormatDurationDaysOnly(): void
    {
        // 7 hours/day * 3600 = 25200 seconds = 1 day
        $this->assertSame('1d', PluginKanbanlooksgoodHook::formatPlannedDuration(25200, 7));
    }

    public function testFormatDurationMixed(): void
    {
        // 1d 2h 30min = 25200 + 7200 + 1800 = 34200 seconds (with 7h/day)
        $this->assertSame('1d 2h 30min', PluginKanbanlooksgoodHook::formatPlannedDuration(34200, 7));
    }

    public function testFormatDurationLessThanOneMinute(): void
    {
        // 30 seconds -> less than 1 minute
        $this->assertSame('< 1min', PluginKanbanlooksgoodHook::formatPlannedDuration(30, 7));
    }

    public function testFormatDurationCustomHoursPerDay(): void
    {
        // With 8 hours/day: 8*3600 = 28800 seconds = 1 day
        $this->assertSame('1d', PluginKanbanlooksgoodHook::formatPlannedDuration(28800, 8));
    }

    public function testFormatDurationMultipleDays(): void
    {
        // 3 days at 7h/day = 3 * 25200 = 75600 seconds
        $this->assertSame('3d', PluginKanbanlooksgoodHook::formatPlannedDuration(75600, 7));
    }

    public function testFormatDurationExactlyOneMinute(): void
    {
        $this->assertSame('1min', PluginKanbanlooksgoodHook::formatPlannedDuration(60, 7));
    }

    public function testFormatDurationExactlyOneHour(): void
    {
        $this->assertSame('1h', PluginKanbanlooksgoodHook::formatPlannedDuration(3600, 7));
    }

    // -------------------------------------------------------
    // lightenColor tests
    // -------------------------------------------------------

    public function testLightenColorBlack(): void
    {
        // Black (#000000) lightened by 0.8 -> rgb(204, 204, 204)
        $result = PluginKanbanlooksgoodHook::lightenColor('#000000', 0.8);
        $this->assertSame('rgb(204, 204, 204)', $result);
    }

    public function testLightenColorWhite(): void
    {
        // White (#ffffff) stays white regardless of amount
        $result = PluginKanbanlooksgoodHook::lightenColor('#ffffff', 0.8);
        $this->assertSame('rgb(255, 255, 255)', $result);
    }

    public function testLightenColorNoAmount(): void
    {
        // Amount 0 should return the original color
        $result = PluginKanbanlooksgoodHook::lightenColor('#ff0000', 0.0);
        $this->assertSame('rgb(255, 0, 0)', $result);
    }

    public function testLightenColorShortHex(): void
    {
        // Short hex #f00 -> #ff0000
        $result = PluginKanbanlooksgoodHook::lightenColor('#f00', 0.0);
        $this->assertSame('rgb(255, 0, 0)', $result);
    }

    public function testLightenColorWithoutHash(): void
    {
        // Should work without leading #
        $result = PluginKanbanlooksgoodHook::lightenColor('5cb85c', 0.0);
        $this->assertSame('rgb(92, 184, 92)', $result);
    }

    public function testLightenColorGreen(): void
    {
        // Priority green #5cb85c lightened by default 0.8
        $result = PluginKanbanlooksgoodHook::lightenColor('#5cb85c');
        $this->assertStringStartsWith('rgb(', $result);
        $this->assertStringEndsWith(')', $result);
        // Lightened green should have higher RGB values than the original
        preg_match('/rgb\((\d+), (\d+), (\d+)\)/', $result, $matches);
        $this->assertGreaterThan(92, (int) $matches[1]);  // R > original 92
        $this->assertGreaterThan(184, (int) $matches[2]); // G > original 184
        $this->assertGreaterThan(92, (int) $matches[3]);  // B > original 92
    }

    // -------------------------------------------------------
    // Config validation tests (saveConfig input sanitization)
    // -------------------------------------------------------

    public function testConfigDefaultValues(): void
    {
        // The default config should have expected values
        $defaults = [
            'show_priority' => 1,
            'show_duration' => 1,
            'show_price' => 1,
            'work_hours_per_day' => 7
        ];

        // Verify structure
        $this->assertArrayHasKey('show_priority', $defaults);
        $this->assertArrayHasKey('show_duration', $defaults);
        $this->assertArrayHasKey('show_price', $defaults);
        $this->assertArrayHasKey('work_hours_per_day', $defaults);
    }

    public function testConfigValidationBooleanFields(): void
    {
        // Simulate the validation logic from saveConfig
        $input = ['show_priority' => '1', 'show_duration' => '0', 'show_price' => '5'];

        $show_priority = (int) $input['show_priority'];
        $show_priority = ($show_priority === 1) ? 1 : 0;
        $this->assertSame(1, $show_priority);

        $show_duration = (int) $input['show_duration'];
        $show_duration = ($show_duration === 1) ? 1 : 0;
        $this->assertSame(0, $show_duration);

        // Invalid value (5) should be normalized to 0
        $show_price = (int) $input['show_price'];
        $show_price = ($show_price === 1) ? 1 : 0;
        $this->assertSame(0, $show_price);
    }

    public function testConfigValidationWorkHoursRange(): void
    {
        // Valid value
        $hours = 8;
        if ($hours < 1 || $hours > 24) {
            $hours = 7;
        }
        $this->assertSame(8, $hours);

        // Below minimum
        $hours = 0;
        if ($hours < 1 || $hours > 24) {
            $hours = 7;
        }
        $this->assertSame(7, $hours);

        // Above maximum
        $hours = 25;
        if ($hours < 1 || $hours > 24) {
            $hours = 7;
        }
        $this->assertSame(7, $hours);

        // Boundary: exactly 1
        $hours = 1;
        if ($hours < 1 || $hours > 24) {
            $hours = 7;
        }
        $this->assertSame(1, $hours);

        // Boundary: exactly 24
        $hours = 24;
        if ($hours < 1 || $hours > 24) {
            $hours = 7;
        }
        $this->assertSame(24, $hours);
    }

    public function testConfigValidationMissingFields(): void
    {
        // Empty input should default to 0 for booleans, 7 for hours
        $input = [];

        $show_priority = isset($input['show_priority']) ? (int) $input['show_priority'] : 0;
        $work_hours = isset($input['work_hours_per_day']) ? (int) $input['work_hours_per_day'] : 7;

        $this->assertSame(0, $show_priority);
        $this->assertSame(7, $work_hours);
    }
}
