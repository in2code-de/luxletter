<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;

/**
 * Class User
 */
class User extends FrontendUser
{
    const TABLE_NAME = 'fe_users';

    /**
     * Try to get a readable name in format "lastname, firstname" (if possible)
     *
     * @return string
     */
    public function getReadableName(): string
    {
        $name = $this->getName();
        if ($this->getLastName() !== '' && $this->getFirstName() !== '') {
            $name = $this->getLastName() . ', ' . $this->getFirstName();
        }
        return $name;
    }

    /**
     * @return FileReference|null
     */
    public function getFirstImage(): ?FileReference
    {
        $images = $this->getImage();
        if ($images !== null) {
            foreach ($images as $image) {
                return $image;
            }
        }
        return null;
    }
}
