<?php
/**
 * SQL Log prosessor
 *
 * @package    Log\processor
 * @author     y_shinada
 * @license    MIT License
 */

namespace Log;

/**
 * SQL向けログ出力項目の定義
 */
class Processor_Sql extends Processor_Web
{
    public function __invoke(array $record)
    {
        return $this->merge_extra($record, $this->trace());
    }
}
