<?php

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use DateTime;
use DateTimeZone;

class TimeHHelper extends AppHelper
{

    public $helpers = [
        'Time'
    ];

    protected static $_timezoneGroups = [
        'UTC' => DateTimeZone::UTC,
        'Africa' => DateTimeZone::AFRICA,
        'America' => DateTimeZone::AMERICA,
        'Antarctica' => DateTimeZone::ANTARCTICA,
        'Asia' => DateTimeZone::ASIA,
        'Atlantic' => DateTimeZone::ATLANTIC,
        'Europe' => DateTimeZone::EUROPE,
        'Indian' => DateTimeZone::INDIAN,
        'Pacific' => DateTimeZone::PACIFIC,
    ];

    protected $_now = false;

    protected $_today = false;

    protected $_start = false;

    protected $_timeDiffToUtc = 0;

    /**
     * {@inheritDoc}
     */
    public function beforeRender()
    {
        $this->_now = time();
        $this->_today = mktime(0, 0, 0);

        // @td reimplement unsing Cake 2.2 CakeTime (?)
        $timezone = Configure::read('Saito.Settings.timezone');
        if (empty($timezone)) {
            $timezone = 'UTC';
        }
        $timezone = new DateTimeZone($timezone);
        $timeInTimezone = new DateTime('now', $timezone);
        $timeOnServer = new DateTime('now');
        $this->_timeDiffToUtc = $timeOnServer->getOffset() - $timeInTimezone->getOffset();
    }

    /**
     * Get timezone list for select popup
     *
     * @return array timezones
     */
    public function getTimezoneSelectOptions()
    {
        $options = [];
        foreach (self::$_timezoneGroups as $groupTitle => $groupId) {
            $timeZones = DateTimeZone::listIdentifiers($groupId);
            foreach ($timeZones as $timeZoneTitle) {
                $timezone = new DateTimeZone($timeZoneTitle);

                $timeInTimezone = new DateTime('now', $timezone);
                $timeDiffToUtc = $timeInTimezone->getOffset() / 3600;

                if ($timeDiffToUtc > 0) {
                    $timeDiffToUtc = '+' . $timeDiffToUtc;
                }

                $tz = $timeZoneTitle . ' – ' . $timeInTimezone->format('H:m');
                if ($timeDiffToUtc !== 0) {
                    $tz .= ' (' . $timeDiffToUtc . ')';
                }

                $options[$groupTitle][$timeZoneTitle] = $tz;
            }
        }

        return $options;
    }

    /**
     * Format timestamp to readable string
     *
     * @param Time $timestamp timestamp
     * @param string $format format
     * @param array $options options
     * @return string
     */
    public function formatTime(Time $timestamp, $format = 'normal', array $options = [])
    {
        // Stopwatch::start('formatTime');
        $options += ['wrap' => []];

        $timestamp = $timestamp->toUnixString();
        $timestamp = $timestamp - $this->_timeDiffToUtc;

        switch ($format) {
            case 'normal':
                $string = $this->_formatRelative($timestamp);
                break;
            case 'short':
                $string = date('d.m.', $timestamp);
                break;
            case 'eng':
                $string = strftime('%F %T', $timestamp);
                break;
            default:
                $string = strftime($format, $timestamp);
        }

        if ($options['wrap'] !== false) {
            $string = $this->timeTag($string, $timestamp, $options['wrap']);
        }

        // Stopwatch::stop('formatTime');

        return $string;
    }

    /**
     * Format timestamp relative to age
     *
     * @param int $timestamp unix-timestamp
     * @return string formated time
     */
    protected function _formatRelative($timestamp)
    {
        if ($timestamp > $this->_today || $timestamp > ($this->_now - 21600)) {
            // today or in the last 6 hours
            $time = strftime("%H:%M", $timestamp);
        } elseif ($timestamp > ($this->_today - 64800)) {
            // yesterday but in the last 18 hours
            $time = __('yesterday') . ' ' . strftime("%H:%M", $timestamp);
        } else {
            // yesterday and 18 hours and older
            $time = strftime("%d.%m.%Y", $timestamp);
        }

        return $time;
    }

    /**
     * Create HTML time tag
     *
     * @performance Is used hundreds of times on homepage.
     *
     * @param string $content Content for tag
     * @param int $timestamp unix-timestamp
     * @param array $options options will become attributes in time-tag
     * @return string HTML
     */
    public function timeTag($content, $timestamp, array $options = [])
    {
        $options += [
            'datetime' => date(DATE_RFC3339, $timestamp),
            'title' => strftime("%F %T", $timestamp)
        ];
        $attributes = [];
        foreach ($options as $attribute => $value) {
            $attributes[$attribute] = "$attribute=\"$value\"";
        }
        $attributes = implode(' ', $attributes);
        $timestamp = "<time $attributes>$content</time>";

        return $timestamp;
    }

    /**
     * timestamp to iso
     *
     * @param mixed $date date
     * @return bool|null|string
     */
    public function mysqlTimestampToIso($date)
    {
        if ($date === null) {
            return null;
        }
        if ($date instanceof Time) {
            $unixTimeStamp = $date->timestamp;
        } else {
            $unixTimeStamp = strtotime($date);
        }

        if ($unixTimeStamp < 0) {
            $unixTimeStamp = 0;
        }

        return date('c', $unixTimeStamp);
    }
}
