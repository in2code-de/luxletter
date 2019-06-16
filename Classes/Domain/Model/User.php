<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;

/**
 * Class User
 */
class User extends FrontendUser
{
    const TABLE_NAME = 'fe_users';
}
