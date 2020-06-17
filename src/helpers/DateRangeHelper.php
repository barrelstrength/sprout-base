<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\helpers;

use Craft;
use craft\helpers\DateTimeHelper;
use DateTime;
use DateTimeZone;
use Exception;

class DateRangeHelper
{
    /**
     * Convert DateTime to UTC to get correct result when querying SQL. SQL data is always on UTC.
     *
     * @param $dateSetting
     *
     * @return DateTime|null
     * @throws Exception
     */
    public static function getUtcDateTime($dateSetting)
    {
        $timeZone = new DateTimeZone('UTC');

        $dateTime = DateTimeHelper::toDateTime($dateSetting, true);

        if (!$dateTime) {
            return null;
        }

        return $dateTime->setTimezone($timeZone);
    }

    public static function getStartEndDateRange($value): array
    {
        // The date function still return date based on the cpPanel timezone settings
        $dateTime = [
            'startDate' => date('Y-m-d H:i:s'),
            'endDate' => date('Y-m-d H:i:s')
        ];

        switch ($value) {

            case 'thisWeek':
                $dateTime['startDate'] = date('Y-m-d H:i:s', strtotime('-7 days'));
                break;

            case 'thisMonth':

                $dateTime['startDate'] = date('Y-m-1 00:00:00');
                $dateTime['endDate'] = date('Y-m-t 00:00:00');

                break;

            case 'lastMonth':

                $dateTime['startDate'] = date('Y-m-1 00:00:00', strtotime('-1 month'));
                $dateTime['endDate'] = date('Y-m-t 00:00:00', strtotime('-1 month'));

                break;

            case 'thisQuarter':
                $dateTime = self::thisQuarter();
                break;

            case 'lastQuarter':
                $dateTime = self::lastQuarter();
                break;

            case 'thisYear':
                $dateTime['startDate'] = date('Y-1-1 00:00:00');
                $dateTime['endDate'] = date('Y-12-t 00:00:00');
                break;

            case 'lastYear':
                $dateTime['startDate'] = date('Y-1-1 00:00:00', strtotime('-1 year'));
                $dateTime['endDate'] = date('Y-12-t 00:00:00', strtotime('-1 year'));
                break;
        }

        return $dateTime;
    }

    public static function getDateRanges($withQuarter = true): array
    {
        $currentMonth = date('F');
        $lastMonth = date('F', strtotime(date('Y-m').' -1 month'));
        $thisQuarter = self::thisQuarter();
        $thisQuarterInitialMonth = date('F', strtotime($thisQuarter['startDate']));
        $thisQuarterFinalMonth = date('F', strtotime($thisQuarter['endDate']));
        $thisQuarterYear = date('Y', strtotime($thisQuarter['endDate']));

        $lastQuarter = self::lastQuarter();
        $lastQuarterInitialMonth = date('F', strtotime($lastQuarter['startDate']));
        $lastQuarterFinalMonth = date('F', strtotime($lastQuarter['endDate']));
        $lastQuarterYear = date('Y', strtotime($lastQuarter['endDate']));

        $currentYear = date('Y');
        $previousYear = date('Y', strtotime('-1 year'));

        $ranges = [
            'thisWeek' => Craft::t('sprout', 'Last 7 Days'),
            'thisMonth' => Craft::t('sprout', 'This Month ({month})', ['month' => $currentMonth]),
            'lastMonth' => Craft::t('sprout', 'Last Month ({month})', ['month' => $lastMonth])
        ];

        if ($withQuarter) {
            $ranges = array_merge($ranges, [
                'thisQuarter' => Craft::t('sprout', 'This Quarter ({iMonth} - {fMonth} {year})', [
                    'iMonth' => $thisQuarterInitialMonth,
                    'fMonth' => $thisQuarterFinalMonth,
                    'year' => $thisQuarterYear
                ]),
                'lastQuarter' => Craft::t('sprout', 'Last Quarter ({iMonth} - {fMonth} {year})', [
                    'iMonth' => $lastQuarterInitialMonth,
                    'fMonth' => $lastQuarterFinalMonth,
                    'year' => $lastQuarterYear
                ]),
            ]);
        }

        $ranges = array_merge($ranges, [
            'thisYear' => Craft::t('sprout', 'This Year ({year})', ['year' => $currentYear]),
            'lastYear' => Craft::t('sprout', 'Last Year ({year})', ['year' => $previousYear]),
            'customRange' => Craft::t('sprout', 'Custom Date Range')
        ]);

        return $ranges;
    }

    public static function thisQuarter(): array
    {
        $startDate = '';
        $endDate = '';
        $current_month = date('m');
        $current_year = date('Y');
        if ($current_month >= 1 && $current_month <= 3) {
            // timestamp or 1-January 12:00:00 AM
            $startDate = strtotime('1-January-'.$current_year);
            // timestamp or 1-April 12:00:00 AM means end of 31 March
            $endDate = strtotime('31-March-'.$current_year);
        } else if ($current_month >= 4 && $current_month <= 6) {
            // timestamp or 1-April 12:00:00 AM
            $startDate = strtotime('1-April-'.$current_year);
            // timestamp or 1-July 12:00:00 AM means end of 30 June
            $endDate = strtotime('30-June-'.$current_year);
        } else if ($current_month >= 7 && $current_month <= 9) {
            // timestamp or 1-July 12:00:00 AM
            $startDate = strtotime('1-July-'.$current_year);
            // timestamp or 1-October 12:00:00 AM means end of 30 September
            $endDate = strtotime('30-September-'.$current_year);
        } else if ($current_month >= 10 && $current_month <= 12) {
            // timestamp or 1-October 12:00:00 AM
            $startDate = strtotime('1-October-'.$current_year);
            // timestamp or 1-January Next year 12:00:00 AM means end of 31 December this year
            $endDate = strtotime('31-December-'.$current_year);
        }

        return [
            'startDate' => date('Y-m-d H:i:s', $startDate),
            'endDate' => date('Y-m-d H:i:s', $endDate)
        ];
    }

    public static function lastQuarter(): array
    {
        $startDate = '';
        $endDate = '';
        $current_month = date('m');
        $current_year = date('Y');

        if ($current_month >= 1 && $current_month <= 3) {
            // timestamp or 1-October Last Year 12:00:00 AM
            $startDate = strtotime('1-October-'.($current_year - 1));
            // timestamp or 1-January  12:00:00 AM means end of 31 December Last year
            $endDate = strtotime('31-December-'.($current_year - 1));
        } else if ($current_month >= 4 && $current_month <= 6) {
            // timestamp or 1-January 12:00:00 AM
            $startDate = strtotime('1-January-'.$current_year);
            $endDate = strtotime('31-March-'.$current_year);
            // timestamp or 1-April 12:00:00 AM means end of 31 March
        } else if ($current_month >= 7 && $current_month <= 9) {
            // timestamp or 1-April 12:00:00 AM
            $startDate = strtotime('1-April-'.$current_year);
            // timestamp or 1-July 12:00:00 AM means end of 30 June
            $endDate = strtotime('30-June-'.$current_year);
        } else if ($current_month >= 10 && $current_month <= 12) {
            // timestamp or 1-July 12:00:00 AM
            $startDate = strtotime('1-July-'.$current_year);
            // timestamp or 1-October 12:00:00 AM means end of 30 September
            $endDate = strtotime('30-September-'.$current_year);
        }

        return [
            'startDate' => date('Y-m-d H:i:s', $startDate),
            'endDate' => date('Y-m-d H:i:s', $endDate)
        ];
    }
}