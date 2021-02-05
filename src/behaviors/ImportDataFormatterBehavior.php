<?php

namespace import\behaviors;

use Carbon\Carbon;
use yii\base\Behavior;

/**
 * Class AbstractFormatterBehavior
 *
 * @package import\behaviors
 */
class ImportDataFormatterBehavior extends Behavior
{

    /**
     * @param string $amount
     * @param float  $multiplier
     * @param bool   $removeSEPSymbol
     *
     * @return false|float
     */
    public function asString2Amount(string $amount, $multiplier = 1.0, $removeSEPSymbol = true)
    {
        if ($removeSEPSymbol) {
            $amount = preg_replace('/[\s,]/', '', $amount);
        }

        return round($this->asApplyMultiplier((float)$amount, $multiplier), 0);
    }

    /**
     * @param mixed $value
     * @param mixed $default
     *
     * @return mixed
     */
    public function asDefaultOnEmpty($value, $default)
    {
        if ($value === null || $value === [] || $value === '') {
            return $default;
        }

        return $value;
    }

    /**
     * @param array $data
     * @param bool  $check
     *
     * @return mixed
     */
    public function asFirstValidated(array $data, $check = true)
    {
        foreach ($data as $item) {
            if ($item === $check) {
                return $item;
            }
        }
    }

    /**
     * @param string $datetime
     * @param string $sourceFormat
     * @param string $outputFormat
     *
     * @return string
     */
    public function asDateConversion(string $datetime, $sourceFormat = 'Y-m-d H:i:s', $outputFormat = 'Y-m-d'): string
    {
        return Carbon::createFromFormat($sourceFormat, $datetime)->format($outputFormat);
    }

    /**
     * @param string      $string
     * @param string      $delimiter
     * @param int         $index
     * @param string|null $default
     *
     * @return string
     */
    public function asSegment(string $string, string $delimiter, int $index, $default = null): ?string
    {
        $data = explode($delimiter, $string);

        return $data[$index - 1] ?? $default;
    }

    /**
     * @param float $val
     * @param float $multiplier
     *
     * @return float
     */
    public function asApplyMultiplier(float $val, float $multiplier)
    {
        return $val * $multiplier;
    }

    /**
     * @param mixed  $val
     * @param string $format
     *
     * @return string
     */
    public function asNow($val, string $format): string
    {
        return Carbon::now()->format($format);
    }


}
