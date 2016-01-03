<?php
/**
 * Logger
 *
 * @package    Log
 * @author     y_shinada
 * @license    MIT License
 * @see        http://ltsv.org
 */

namespace Log;

/**
 * ログクラス
 *
 * @see Fuel\Core\Log
 */
class Log extends \Fuel\core\Log
{
    /**
     * container for the Monolog instance
     */
    protected static $monolog = null;

    /**
     * Copy of the Monolog log levels
     */
    protected static $levels = [
        100 => 'DEBUG',
        200 => 'INFO',
        250 => 'NOTICE',
        300 => 'WARNING',
        400 => 'ERROR',
        500 => 'CRITICAL',
        550 => 'ALERT',
        600 => 'EMERGENCY',
    ];

    /**
     * Initialize the class
     */
    public static function _init()
    {
        // load the file config
        \Config::load('file', true);
        \Config::load('log.yml', true);

        $modules = \Config::get('log.modules');
        foreach ($modules as $module => $options) {
            extract($options);

            $logger = new \Monolog\Logger('fuelphp');

            $pascal_case = function ($str) { return ucfirst(strtolower($str)); };

            $handler_class_name   = "Log\\Handler_".$pascal_case($handler);
            $formatter_class_name = "Log\\Formatter_".$pascal_case($format);
            $processor_class_name = "Log\\Processor_".$pascal_case($processor);

            $handler   = new $handler_class_name($module, $options);
            $formatter = new $formatter_class_name(null, 'Y-m-d H:i:s');
            $processor = new $processor_class_name();

            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
            $logger->pushProcessor($processor);
            static::$monolog[$module] = $logger;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function levels()
    {
        return static::$levels;
    }

    /**
     * Return the monolog instance
     * @param  string $format format name
     * @return Log Monolog instance
     */
    public static function factory($module='app')
    {
        // make sure we have an instance
        static::$monolog || static::_init();

        // return the created instance

        if (\Fuel::$is_cli) {
            // タスクからの場合
            return static::$monolog['task'];
        } elseif (($req = \Request::active()) && !is_null($req->module)) {
            // モジュールからの場合
            $module = $req->module;
            return static::$monolog[$module];
        }
        // その他はアプリケーションログへ
        return static::$monolog['app'];
    }

    /**
     * Logs a message with the Info Log Level
     *
     * @param   string  $msg     The log message
     * @param   string  $method  The method that logged
     * @return  bool    If it was successfully logged
     */
    public static function i($msg, $format='app')
    {
        return static::append(\Fuel::L_INFO, $msg, $format);
    }

    /**
     * Logs a message with the Debug Log Level
     *
     * @param   string  $msg     The log message
     * @param   string  $method  The method that logged
     * @return  bool    If it was successfully logged
     */
    public static function d($msg, $format='app')
    {
        return static::append(\Fuel::L_DEBUG, $msg, $format);
    }

    /**
     * Logs a message with the Warning Log Level
     *
     * @param   string  $msg     The log message
     * @param   string  $method  The method that logged
     * @return  bool    If it was successfully logged
     */
    public static function w($msg, $format='app')
    {
        return static::append(\Fuel::L_WARNING, $msg, $format);
    }

    /**
     * Logs a message with the Error Log Level
     *
     * @param   string  $msg     The log message
     * @param   string  $method  The method that logged
     * @return  bool    If it was successfully logged
     */
    public static function e($msg, $format='app')
    {
        return static::append(\Fuel::L_ERROR, $msg, $format);
    }


    /**
     * Write Log File
     *
     * Generally this function will be called using the global log_message() function
     *
     * @access	public
     * @param	int|string	the error level
     * @param	string	the error message
     * @param	string	information about the method
     * @return	bool
     */
    public static function append($level, $msg, $module='app')
    {
        // defined default error labels
        static $oldlabels = [
            1  => 'Error',
            2  => 'Warning',
            3  => 'Debug',
            4  => 'Info',
        ];

        // get the levels defined to be logged
        $loglabels = \Config::get('log_threshold');

        // bail out if we don't need logging at all
        if ($loglabels == \Fuel::L_NONE) {
            return false;
        }

        // if it's not an array, assume it's an "up to" level
        if (!is_array($loglabels)) {
            $a = [];
            foreach (static::$levels as $l => $label) {
                $l >= $loglabels && $a[] = $l;
            }
            $loglabels = $a;
        }

        $logger = static::factory($module);

        // convert the level to monolog standards if needed
        if (is_int($level) && isset($oldlabels[$level])) {
            $level = strtoupper($oldlabels[$level]);
        }

        if (is_string($level)) {
            if (! $level = array_search($level, static::$levels)) {
                $level = 250;    // can't map it, convert it to a NOTICE
            }
        }

        // make sure $level has the correct value
        if ((is_int($level) && ! isset(static::$levels[$level]))
                || (is_string($level) && ! array_search(strtoupper($level), static::$levels))) {
            throw new \FuelException('Invalid level "'.$level.'" passed to logger()');
        }

        // do we need to log the message with this level?
        if (!in_array($level, $loglabels)) {
            return false;
        }

        // log the message
        $logger->log($level, $msg);

        return true;
    }
}
