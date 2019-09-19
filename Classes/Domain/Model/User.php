<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model;

use In2code\Luxletter\Utility\StringUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;

/**
 * Class User
 */
class User extends FrontendUser
{
    const TABLE_NAME = 'fe_users';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\In2code\Luxletter\Domain\Model\Usergroup>
     */
    protected $usergroup;

    /**
     * @var \DateTime
     */
    protected $crdate = null;

    /**
     * Try to get a readable name in format "lastname, firstname" (if possible)
     *
     * @param string $splitCharacter
     * @return string
     */
    public function getReadableName(string $splitCharacter = ', '): string
    {
        $name = $this->getName();
        if ($this->getLastName() !== '' && $this->getFirstName() !== '') {
            $name = $this->getLastName() . $splitCharacter . $this->getFirstName();
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

    /**
     * @return \DateTime
     */
    public function getCrdate(): ?\DateTime
    {
        return $this->crdate;
    }

    /**
     * @param \DateTime $crdate
     * @return User
     */
    public function setCrdate(\DateTime $crdate): self
    {
        $this->crdate = $crdate;
        return $this;
    }

    /**
     * @return string
     */
    public function getUnsubscribeHash(): string
    {
        return StringUtility::getHashFromArguments([$this->getUid(), $this->getCrdate()->format('U')]);
    }
}
