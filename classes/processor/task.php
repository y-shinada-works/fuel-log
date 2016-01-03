<?php
/**
 * Task Log prosessor
 *
 * @package    Log\processor
 * @author     y_shinada
 * @license    MIT License
 */

namespace Log;

/**
 * バッチ向けログ出力項目の定義
 */
class Processor_Task extends Processor_Web
{
    /**
     * ログ出力可能な項目を定義
     *
     * @param  array $record
     * @return array ログ出力可能な項目
     */
    public function __invoke(array $record)
    {
        return $this->merge_extra($record, $this->trace());
    }
}
