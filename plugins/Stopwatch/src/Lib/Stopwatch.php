<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Stopwatch\Lib;

class Stopwatch
{

    protected static $_startupTime = 0;

    protected static $_instance = null;

    protected static $_enableTimer = false;

    protected static $_wallStart = 0;

    protected static $_userStart = 0;

    protected static $_wallLast = 0;

    protected static $_userLast = 0;

    protected static $_events;

    protected static $_sums = [];

    protected static $_starts = [];

    protected static $_stopwatchTime = 0;

    protected static $_stopwatchCalls = 0;

    /**
     * get instance
     *
     * @return null|Stopwatch
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new Stopwatch();
        }

        return self::$_instance;
    }

    /**
     * {@inheritDoc}
     */
    protected function __construct()
    {
    }

    /**
     * {@inheritDoc}
     */
    private function __clone()
    {
    }

    /**
     * reset
     *
     * @return void
     */
    public static function reset()
    {
        self::$_startupTime = 0;
        self::$_instance = null;
        self::$_enableTimer = false;
        self::$_wallStart = 0;
        self::$_userStart = 0;
        self::$_wallLast = 0;
        self::$_userLast = 0;
        self::$_events = [];
        self::$_sums = [];
        self::$_starts = [];
        self::$_stopwatchTime = 0;
        self::$_stopwatchCalls = 0;
    }

    /**
     * add event
     *
     * @param string $x event-name
     * @param string|null $event type
     * @return void
     */
    protected static function _addEvent($x, $event = null)
    {
        if (self::$_enableTimer === false) {
            return;
        }

        list($usec, $sec) = explode(' ', microtime());
        $wtime = ((float)$sec + (float)$usec);
        if (!self::$_wallStart) {
            self::$_wallStart = $wtime;
        }

        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
        $dat = @getrusage();
        // phpcs:enable Generic.PHP.NoSilencedErrors.Discouraged
        if (empty($dat)) {
            // some hosters disable getrusage() while hardening their PHP
            $utime = 0;
        } else {
            $utime = ($dat['ru_utime.tv_sec'] + $dat['ru_utime.tv_usec'] / 1000000);
        }

        if (!self::$_userStart) {
            self::$_userStart = $utime;
        }

        $udiff = ($wtime - self::$_wallStart == 0) ? 0 : $utime - self::$_userLast;
        self::$_userLast = $utime;

        $wdiff = ($wtime - self::$_wallStart == 0) ? 0 : $wtime - self::$_wallLast;
        self::$_wallLast = $wtime;

        if (!isset(self::$_starts[$x])) {
            self::$_starts[$x]['wtime'] = $wtime;
            self::$_starts[$x]['utime'] = $utime;
        } else {
            if (!isset(self::$_sums[$x]['wtime'])) {
                self::$_sums[$x]['wtime'] = 0;
                self::$_sums[$x]['utime'] = 0;
                self::$_sums[$x]['times'] = 0;
            }
            self::$_sums[$x]['wtime'] = self::$_sums[$x]['wtime'] + $wtime - self::$_starts[$x]['wtime'];
            self::$_sums[$x]['utime'] = self::$_sums[$x]['utime'] + $utime - self::$_starts[$x]['utime'];
            self::$_sums[$x]['times'] = self::$_sums[$x]['times'] + 1;
            unset(self::$_starts[$x]);
        }

        switch ($event) {
            case 'start':
                $x = '* ' . $x;
                break;
            case 'stop':
                $x = '† ' . $x;
                break;
        }

        self::$_events[] = [
            'title' => $x,
            'wtime' => $wtime - self::$_wallStart,
            'utime' => $utime - self::$_userStart,
            'wdiff' => $wdiff,
            'udiff' => $udiff,
            'mem' => memory_get_usage(),
        ];

        // endtime
        list($eusec, $esec) = explode(' ', microtime());
        $ewtime = ((float)$esec + (float)$eusec);
        self::$_stopwatchTime += ($ewtime - $wtime);
        self::$_stopwatchCalls++;
    }

    /**
     * time to Cake start
     *
     * @return mixed
     */
    protected static function _timeToCake()
    {
        return TIME_START - $_SERVER['REQUEST_TIME_FLOAT'];
    }

    /**
     * time from cake to stopwatch
     *
     * @return float
     */
    protected static function _timeFromCakeToStopwatch(): float
    {
        return self::$_startupTime - TIME_START;
    }

    /**
     * time until stopwatch start
     *
     * @return int
     */
    protected static function _timeToStopwatch()
    {
        return self::$_startupTime - $_SERVER['REQUEST_TIME_FLOAT'];
    }

    /**
     * Get output
     *
     * @return string|void
     */
    public static function getString()
    {
        if (self::$_enableTimer === false) {
            return;
        }

        self::start('now');

        $out = "";
        $out .= 'Time to Cake: ' . sprintf('%05.3f', self::_timeToCake()) . " s\n";
        $out .= 'Cake bootstrap: ' . sprintf('%05.3f', self::_timeFromCakeToStopwatch()) . " s\n";

        $out .= "W\tU\tW_delta\tU_delta\tMem [MB]\n";
        $_seriesIndex = 1;
        foreach (self::$_events as $k => $v) {
            $out .= '<span id="stopwatch-' . $_seriesIndex++ . '" class="stopwatch-row">';
            $out .= sprintf(
                "%05.3f\t%05.3f\t%05.3f\t%05.3f\t%5.1f\t%s\n",
                $v['wtime'],
                $v['utime'],
                $v['wdiff'],
                $v['udiff'],
                $v['mem'] / 1048576,
                $v['title']
            );
            $out .= '</span>';
        }

        $out .= "\n\n";

        for ($i = 0; $i < 100; $i++) {
            Stopwatch::start('e');
            Stopwatch::stop('e');
        }
        $_e = array_pop(self::$_sums);
        $_eW = $_e['wtime'] / 100;
        $_eU = $_e['utime'] / 100;

        self::$_events = array_slice(self::$_events, 0, -200);

        $out .= "W_sum\tU_sum\tW_%\tU_%\t#\tW_ø\n";

        $_lastTimestamp = end(self::$_events);
        $wlast = $_lastTimestamp['wtime'] / 100;
        $ulast = $_lastTimestamp['utime'] / 100;
        foreach (self::$_sums as $k => $v) {
            // on vagrant $ulast may be 0 for unknown reason when running test cases
            // ugly hack to suppress output in test-cases, where it isn't read anyway
            if (empty($ulast)) {
                break;
            }
            $v['wtime'] = $v['wtime'] - ($_eW * $v['times']);
            $v['utime'] = $v['utime'] - ($_eW * $v['times']);

            $out .= sprintf(
                "%05.3f\t%05.3f\t%04.1f\t%04.1f\t%u\t%05.3f\t%s\n",
                $v['wtime'],
                $v['utime'],
                $v['wtime'] / $wlast,
                $v['utime'] / $ulast,
                $v['times'],
                $v['wtime'] / $v['times'],
                $k
            );
        }

        $out .= "\n\n" . self::printStatistic();

        return $out;
    }

    /**
     * get json encoded
     *
     * @return string|void
     */
    public static function getJs()
    {
        if (self::$_enableTimer === false) {
            return;
        }
        $data = [];
        foreach (self::$_events as $v) {
            $data[] = [
                'label' => $v['title'],
                'data' => [[1, $v['wdiff']], [2, $v['udiff']]],
            ];
        }
        $out = json_encode($data);

        return $out;
    }

    /**
     * Init
     *
     * @return void
     */
    public static function init()
    {
        self::reset();
        self::$_startupTime = microtime(true);
    }

    /**
     * enable
     *
     * @return void
     */
    public static function enable()
    {
        self::$_enableTimer = true;
    }

    /**
     * disable
     *
     * @return void
     */
    public static function disable()
    {
        self::$_enableTimer = false;
    }

    /**
     * Start
     *
     * @param string $text id
     *
     * @return void
     */
    public static function start($text)
    {
        self::_addEvent($text, 'start');
    }

    /**
     * Stop.
     *
     * @param string $text id
     *
     * @return void
     */
    public static function stop($text)
    {
        self::_addEvent($text, 'stop');
    }

    /**
     * Print static
     *
     * @return string
     */
    public static function printStatistic()
    {
        return self::$_stopwatchCalls . " calls with ca " . sprintf(
            "%05.3f",
            self::$_stopwatchTime
        ) . ' sec overhead.';
    }

    /**
     * Gets current accumulated wall time
     *
     * @return float
     */
    public static function getWallTime(): float
    {
        self::start('getWallTime()');
        self::end('getWallTime()');
        $time = self::$_events[count(self::$_events) - 1]['wtime'] +
            self::_timeToStopwatch();

        return round($time, 3);
    }

    /**
     * Alias for self::stop
     *
     * @param string $text key
     * @return void
     */
    public static function end($text)
    {
        self::stop($text);
    }
}
