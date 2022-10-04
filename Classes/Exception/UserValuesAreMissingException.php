<?php

declare(strict_types=1);
namespace In2code\Luxletter\Exception;

use Exception;

/**
 * Class UserValuesAreMissingException
 * if an essential value in fe_users is missing (like crdate)
 */
class UserValuesAreMissingException extends Exception
{
}
