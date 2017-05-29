<?php

namespace Box\Spout\Writer;

use Box\Spout\Writer\Manager\OptionsManagerInterface;
use Box\Spout\Writer\Common\Options;
use Box\Spout\Writer\Entity\Worksheet;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Box\Spout\Writer\Factory\InternalFactoryInterface;
use Box\Spout\Writer\Manager\WorkbookManagerInterface;

/**
 * Class WriterMultiSheetsAbstract
 *
 * @package Box\Spout\Writer
 * @abstract
 */
abstract class WriterMultiSheetsAbstract extends WriterAbstract
{

    /** @var InternalFactoryInterface */
    private $internalFactory;

    /** @var WorkbookManagerInterface */
    private $workbookManager;

    /**
     * @param OptionsManagerInterface $optionsManager
     * @param InternalFactoryInterface $internalFactory
     */
    public function __construct(OptionsManagerInterface $optionsManager, InternalFactoryInterface $internalFactory)
    {
        parent::__construct($optionsManager);
        $this->internalFactory = $internalFactory;
    }

    /**
     * Sets whether new sheets should be automatically created when the max rows limit per sheet is reached.
     * This must be set before opening the writer.
     *
     * @api
     * @param bool $shouldCreateNewSheetsAutomatically Whether new sheets should be automatically created when the max rows limit per sheet is reached
     * @return WriterMultiSheetsAbstract
     * @throws \Box\Spout\Writer\Exception\WriterAlreadyOpenedException If the writer was already opened
     */
    public function setShouldCreateNewSheetsAutomatically($shouldCreateNewSheetsAutomatically)
    {
        $this->throwIfWriterAlreadyOpened('Writer must be configured before opening it.');

        $this->optionsManager->setOption(Options::SHOULD_CREATE_NEW_SHEETS_AUTOMATICALLY, $shouldCreateNewSheetsAutomatically);
        return $this;
    }

    /**
     * Configures the write and sets the current sheet pointer to a new sheet.
     *
     * @return void
     * @throws \Box\Spout\Common\Exception\IOException If unable to open the file for writing
     */
    protected function openWriter()
    {
        if (!$this->workbookManager) {
            $this->workbookManager = $this->internalFactory->createWorkbookManager($this->optionsManager);
            $this->workbookManager->addNewSheetAndMakeItCurrent();
        }
    }

    /**
     * Returns all the workbook's sheets
     *
     * @api
     * @return Common\Sheet[] All the workbook's sheets
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException If the writer has not been opened yet
     */
    public function getSheets()
    {
        $this->throwIfWorkbookIsNotAvailable();

        $externalSheets = [];
        $worksheets = $this->workbookManager->getWorksheets();

        /** @var Worksheet $worksheet */
        foreach ($worksheets as $worksheet) {
            $externalSheets[] = $worksheet->getExternalSheet();
        }

        return $externalSheets;
    }

    /**
     * Creates a new sheet and make it the current sheet. The data will now be written to this sheet.
     *
     * @api
     * @return Common\Sheet The created sheet
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException If the writer has not been opened yet
     */
    public function addNewSheetAndMakeItCurrent()
    {
        $this->throwIfWorkbookIsNotAvailable();
        $worksheet = $this->workbookManager->addNewSheetAndMakeItCurrent();

        return $worksheet->getExternalSheet();
    }

    /**
     * Returns the current sheet
     *
     * @api
     * @return Common\Sheet The current sheet
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException If the writer has not been opened yet
     */
    public function getCurrentSheet()
    {
        $this->throwIfWorkbookIsNotAvailable();
        return $this->workbookManager->getCurrentWorksheet()->getExternalSheet();
    }

    /**
     * Sets the given sheet as the current one. New data will be written to this sheet.
     * The writing will resume where it stopped (i.e. data won't be truncated).
     *
     * @api
     * @param Common\Sheet $sheet The sheet to set as current
     * @return void
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException If the writer has not been opened yet
     * @throws \Box\Spout\Writer\Exception\SheetNotFoundException If the given sheet does not exist in the workbook
     */
    public function setCurrentSheet($sheet)
    {
        $this->throwIfWorkbookIsNotAvailable();
        $this->workbookManager->setCurrentSheet($sheet);
    }

    /**
     * Checks if the workbook has been created. Throws an exception if not created yet.
     *
     * @return void
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException If the workbook is not created yet
     */
    protected function throwIfWorkbookIsNotAvailable()
    {
        if (!$this->workbookManager->getWorkbook()) {
            throw new WriterNotOpenedException('The writer must be opened before performing this action.');
        }
    }

    /**
     * Adds data to the currently opened writer.
     * If shouldCreateNewSheetsAutomatically option is set to true, it will handle pagination
     * with the creation of new worksheets if one worksheet has reached its maximum capicity.
     *
     * @param array $dataRow Array containing data to be written.
     *          Example $dataRow = ['data1', 1234, null, '', 'data5'];
     * @param \Box\Spout\Writer\Style\Style $style Style to be applied to the row.
     * @return void
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException If the book is not created yet
     * @throws \Box\Spout\Common\Exception\IOException If unable to write data
     */
    protected function addRowToWriter(array $dataRow, $style)
    {
        $this->throwIfWorkbookIsNotAvailable();
        $this->workbookManager->addRowToCurrentWorksheet($dataRow, $style);
    }

    /**
     * Closes the writer, preventing any additional writing.
     *
     * @return void
     */
    protected function closeWriter()
    {
        if ($this->workbookManager) {
            $this->workbookManager->close($this->filePointer);
        }
    }
}

