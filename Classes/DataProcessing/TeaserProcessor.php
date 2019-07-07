<?php
declare(strict_types=1);
namespace In2code\Luxletter\DataProcessing;

use In2code\Luxletter\Utility\DatabaseUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Class TeaserProcessor
 * to render default CTypes of TYPO3 with a cropped bodytext
 */
class TeaserProcessor implements DataProcessorInterface
{
    /**
     * Get rows of teasered elements and crop tt_content.bodytext
     *
     * @param ContentObjectRenderer $cObj The data of the content element or page
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     * @return array the processed data as key/value store
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        $processedData['flexformConfiguration'] = $this->getFlexFormConfiguration($processedData);
        $processedData['teaserElements'] = [];
        if (!empty($processedData['flexformConfiguration'])) {
            $records = GeneralUtility::intExplode(',', $processedData['flexformConfiguration']['records'], true);
            foreach ($records as $record) {
                $processedData['teaserElements'][] = [
                    'html' => $this->getHtmlOfTeaserElement($record),
                    'data' => $this->getDataFromTeaserElement($record)
                ];
            }
        }
        return $processedData;
    }

    /**
     * @param array $processedData
     * @return array
     */
    protected function getFlexFormConfiguration(array $processedData)
    {
        if (!empty($processedData['data']['pi_flexform'])) {
            $ffService = ObjectUtility::getObjectManager()->get(FlexFormService::class);
            return $ffService->convertFlexFormContentToArray($processedData['data']['pi_flexform']);
        }
        return [];
    }

    /**
     * @param int $identifier
     * @return int
     */
    protected function getHtmlOfTeaserElement(int $identifier): string
    {
        $contentObject = ObjectUtility::getContentObject();
        $contentConfiguration = [
            'tables' => 'tt_content',
            'source' => $identifier,
            'dontCheckPid' => true
        ];
        return $contentObject->cObjGetSingle('RECORDS', $contentConfiguration);
    }

    /**
     * @param int $identifier
     * @return array
     */
    protected function getDataFromTeaserElement(int $identifier): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable('tt_content');
        $rows = (array)$queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where('uid=' . (int)$identifier)
            ->execute()
            ->fetchAll();
        if (!empty($rows[0])) {
            return $rows[0];
        }
        return [];
    }
}
