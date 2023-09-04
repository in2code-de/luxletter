<?php

declare(strict_types=1);
namespace In2code\Luxletter\Utility;

class ArrayUtility
{
    public static function convertToIntegerArray(array $array): array
    {
        foreach ($array as &$value) {
            $value = (int)$value;
        }
        return $array;
    }
}
