<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Model;

use DateTime;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Exception\UserValuesAreMissingException;
use In2code\Luxletter\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Object\Exception;

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
     * @var DateTime
     */
    protected $crdate = null;

    /**
     * @var int
     */
    protected $luxletterLanguage = 0;

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
     * @return DateTime|null
     */
    public function getCrdate(): ?DateTime
    {
        return $this->crdate;
    }

    /**
     * @param DateTime $crdate
     * @return User
     */
    public function setCrdate(DateTime $crdate): self
    {
        $this->crdate = $crdate;
        return $this;
    }

    /**
     * @return int
     */
    public function getLuxletterLanguage(): int
    {
        return $this->luxletterLanguage;
    }

    /**
     * @param int $luxletterLanguage
     * @return User
     */
    public function setLuxletterLanguage(int $luxletterLanguage): User
    {
        $this->luxletterLanguage = $luxletterLanguage;
        return $this;
    }

    /**
     * @return string
     * @throws UserValuesAreMissingException
     * @throws MisconfigurationException
     * @throws Exception
     */
    public function getUnsubscribeHash(): string
    {
        if (is_a($this->crdate, DateTime::class)) {
            return StringUtility::getHashFromArguments([$this->getUid(), $this->getCrdate()->format('U')]);
        }
        throw new UserValuesAreMissingException('fe_users.crdate is empty for uid=' . $this->getUid(), 1574764265);
    }

    /**
     * @return bool
     */
    public function isValidEmail(): bool
    {
        return $this->getEmail() !== '' && GeneralUtility::validEmail($this->getEmail());
    }
}
