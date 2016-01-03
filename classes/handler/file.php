<?php
/**
 * File Handler
 *
 * @package    Log
 * @author     y_shinada
 * @license    MIT License
 */

namespace Log;

/**
 * ファイルハンドラ
 */
class Handler_File extends \Monolog\Handler\AbstractProcessingHandler
{
    /**
     * 書き込みハンドラ
     * @var Resource
     */
    protected $stream;

    /**
     * モジュール名
     * @var string
     */
    protected $module;

    /**
     * 出力条件
     * @var array
     */
    protected $options = [
        'level' => \Monolog\Logger::DEBUG, // Log Level
        'bubble' => true,                  // bubble
        'path' => '/tmp',                  // Export path
    ];

    /**
     * 初期化
     *
     * @param string $module  モジュール名
     * @param array  $options 出力条件
     */
    public function __construct($module, $options = [])
    {
        $this->module = $module;

        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }

        parent::__construct($this->options['level'], $this->options['bubble']);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = null;
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $now = new \DateTime();
        $month = $now->format('Y/m');
        $root_dir = $this->options['path'];
        $monthly_dir = $root_dir.DS.$month;

        if (!file_exists($monthly_dir)) {
            $permission = \Config::get('file.chmod.folders', 0777);
            mkdir($monthly_dir, $permission, true);
        }

        if (null === $this->stream) {
            $logfile_path = $monthly_dir.DS.$this->module.'-'.$now->format('Ymd').'.log';
            $errorMessage = null;

            set_error_handler(function ($code, $msg) use (&$errorMessage) {
                $errorMessage = preg_replace('{^fopen\(.*?\): }', '', $msg);
            });

            $this->stream = fopen($logfile_path, 'a');
            restore_error_handler();

            if (!is_resource($this->stream)) {
                $this->stream = null;
                throw new \UnexpectedValueException(sprintf('The stream or file "%s" could not be opened: '.$errorMessage, $logfile_path));
            }
        }

        fwrite($this->stream, (string)$record['formatted']);
    }
}
