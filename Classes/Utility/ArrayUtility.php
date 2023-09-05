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

    public static function convertArrayToIntegerList(array $array, string $glue = ','): string
    {
        $array = self::convertToIntegerArray($array);
        return implode(',', $array);
    }
}
