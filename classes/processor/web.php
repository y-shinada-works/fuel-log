<?php
/**
 * Web application Log prosessor
 *
 * @package    Log\processor
 * @author     y_shinada
 * @license    MIT License
 */

namespace Log;

/**
 * Webアプリ向けログ出力項目の定義
 */
class Processor_Web
{
    /**
     * ログ出力可能な項目を定義
     *
     * @param  array $record
     * @return array ログ出力可能な項目
     */
    public function __invoke(array $record)
    {
        $record = $this->merge_extra($record, $this->trace());

        if (\Fuel::$is_cli) {
            return $record;
        }

        $record = $this->merge_extra(
            $record,
            [
                'url'        => \Input::uri(),
                'method'     => \Input::method(),
                'ip'         => \Input::real_ip(),
                'session_id' => \Session::key('session_id'),
            ]
        );

        return $record;
    }

    /**
     * トレースログで出力可能な項目を定義
     *
     * @return array          出力可能な項目
     */
    public function trace()
    {
        $trace = debug_backtrace();

        array_shift($trace);
        array_shift($trace);
        array_shift($trace);

        // 不要なバックトレースはスキップする
        // スキップされる条件は以下
        //   - 先頭3件のバックトレース(Fuel\Coreなどのトレースのため)
        //   - クラスの名前空間に Monolog が含まれるもの
        //   - ファイルパスに log/log が含まれるもの
        $i = 0;
        while (
            isset($trace[$i]['class'])
            && (
                (false !== strpos($trace[$i]['class'], 'Monolog\\'))
                || (false !== strpos($trace[$i]['file'], 'classes/log'))
            )
        ) {
            $i++;
        }

        return [
            'file'      => isset($trace[$i]['file']) ? $trace[$i]['file'] : null,
            'line'      => isset($trace[$i]['line']) ? $trace[$i]['line'] : null,
            'class'     => isset($trace[$i]['class']) ? $trace[$i]['class'] : null,
            'function'  => isset($trace[$i]['function']) ? $trace[$i]['function'] : null,
        ];
    }

    /**
     * 拡張項目のマージ
     *
     * @param  array $record 元となる項目
     * @param  array $extra  追加する拡張項目
     * @return array         項目
     */
    protected function merge_extra($record, $extra)
    {
        if (!isset($record['extra'])) {
            $record['extra'] = [];
        }

        $record['extra'] = array_merge($record['extra'], $extra);

        return $record;
    }
}
