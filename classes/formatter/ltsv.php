<?php
/**
 * LTSV Log formatter
 *
 * @package    Log\Formatter
 * @author     y_shinada
 * @license    MIT License
 * @see        http://ltsv.org
 */

namespace Log;

/**
 * LTSVフォーマッタ
 *
 * @see \Monolog\Formatter\LineFormatter
 */
class Formatter_Ltsv extends \Monolog\Formatter\NormalizerFormatter
{
    // TODO: support \Config::load()
    const SIMPLE_FORMAT = "%datetime% %level_name% %extra.file% %extra.line% %message%";

    protected $format;

    /**
     * {@inheritdoc}
     */
    public function __construct($format = null, $dateFormat = null)
    {
        $_format      = $format ?: static::SIMPLE_FORMAT;
        $this->format = explode(' ', $_format);
        parent::__construct($dateFormat);
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $record_rows = parent::format($record);

        $output  = $this->format;
        $outputs = [];
        foreach ($record_rows['extra'] as $extra_label => $val) {
            if (false !== in_array("%extra.${var}%", $output)) {
                $idx           = array_search("%extra.${var}%", $this->format);
                $outputs[$idx] = $extra_label.':'.$this->convert_string($val);
                unset($record_rows['extra'][$extra_label]);
            }
        }

        foreach ($record_rows as $label => $val) {
            $idx = array_search("%${var}%", $this->format);
            if ($idx !== false) {
                $outputs[$idx] = $label.':'.$this->convert_string($val);
            }
        }
        ksort($outputs);

        return implode("\t", $outputs)."\n";
    }

    /**
     * {@inheritdoc}
     */
    public function formatBatch(array $records)
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalize($data)
    {
        if (is_bool($data) || is_null($data)) {
            return var_export($data, true);
        }

        if ($data instanceof \Exception) {
            $previousText = '';
            if ($previous = $data->getPrevious()) {
                do {
                    $previousText .= ', '.get_class($previous).': '.$previous->getMessage().' at '.$previous->getFile().':'.$previous->getLine();
                } while ($previous = $previous->getPrevious());
            }

            return '[object] ('.get_class($data).': '.$data->getMessage().' at '.$data->getFile().':'.$data->getLine().$previousText.')';
        }

        return parent::normalize($data);
    }

    /**
     * オブジェクトを文字列に変換
     *
     * @param  mixed $data オブジェクト
     * @return string      変換後文字列
     */
    protected function convert_string($data)
    {
        if (null === $data || is_scalar($data)) {
            return (string) $data;
        }

        $data = $this->normalize($data);
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return $this->toJson($data);
        }

        return str_replace('\\/', '/', json_encode($data));
    }
}
