<?php

/**
 * Provides helper functions
 */
class Helpers
{
    /**
     * Checks if a given string is valid ISO-8601 datetime
     * @param string $dateTimeString
     * @return bool
     */
    public static function isISO8601DateTime(string $dateTimeString): bool
    {
        $dateTime = DateTime::createFromFormat(DateTime::ATOM, $dateTimeString);
        return is_object($dateTime) ?? false;
    }

    /**
     * Compares if 'endDate' is greater than 'startDate'
     * @param string $startDate
     * @param string $endDate
     * @return bool
     * @throws Exception
     */
    public static function compareDates(string $startDate, string $endDate): bool
    {
        $startDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);

        return $endDate > $startDate;
    }

    /**
     * Checks if given string is a valid HEX color string
     * @param string $hexColor
     * @return bool
     */
    public static function isValidHexColor(string $hexColor): bool
    {
        return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $hexColor) === 1;
    }

    /**
     * Returns difference between given in a positive float value calculated in precision of whole hours
     * @param $start_date
     * @param $end_date
     * @param string $durationUnit
     * @return float|null
     */
    public static function calculateDuration($start_date, $end_date, string $durationUnit = ConstructionStagesConsts::DURATIONUNIT_DEFAULT): ?float
    {
        if (!($start = strtotime($start_date)) || ($end_date !== null && !($end = strtotime($end_date)))) {
            return null;
        }

        $diff = $end_date === null ? 0 : ($end - $start) / 3600;

        switch (strtoupper($durationUnit)) {
            case ConstructionStagesConsts::DURATIONUNIT_HOURS:
                $value = round($diff, 2);
                break;
            case ConstructionStagesConsts::DURATIONUNIT_DEFAULT:
                $value = round($diff / 24, 2);
                break;
            case ConstructionStagesConsts::DURATIONUNIT_WEEKS:
                $value = round($diff / 168, 2);
                break;
            default:
                $value = null;
                break;
        }

        return $value > 0.00 ? $value : null;
    }
}