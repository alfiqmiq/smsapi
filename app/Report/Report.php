<?php
declare(strict_types = 1);

namespace Report;

use DateTime;
use DateTimeZone;
use PHPMailer\PHPMailer\Exception;
use stdClass;
use Report\Helpers\{LoadFile, SendEmail, SendSms};
use SebastianBergmann\Diff\{Differ, Output\UnifiedDiffOutputBuilder};

class Report
{
    protected DateTimeZone $timeZone;
    protected string $reportDate;

    protected stdClass $baseFile;
    protected array $filesCompareTo = [];

    protected array $encodingDiffs = [];
    protected int $contentDiffsCounter = 0;

    protected SendEmail $mailHandler;
    protected SendSms $smsHandler;

    public function __construct ()
    {
        $this->timeZone = new DateTimeZone('Europe/Warsaw');
        $this->reportDate = (new DateTime())
            ->setTimezone($this->timeZone)
            ->format('Y-m-d H:i:s.u');

        $this->smsHandler = SendSms::getInstance();
        $this->mailHandler = SendEmail::getInstance();
    }

    /**
     * @param string $fileName
     *
     * @return Report
     * @throws Helpers\LoadFileException
     */
    public function setBaseFile (string $fileName): Report
    {
        $this->baseFile = (new LoadFile($fileName))->getFile();
        return $this;
    }

    /**
     * @param string $fileName
     *
     * @return Report
     * @throws Helpers\LoadFileException
     */
    public function addFileCompareTo (string $fileName): Report
    {
        $this->filesCompareTo[] = (new LoadFile($fileName))->getFile();
        return $this;
    }

    protected function saveDiffFile (string $content): void
    {
        file_put_contents(PROGRAM_DIR . '/diff.txt', $content);
    }

    protected function checkEncoding ($baseFile, $fileCompareTo): void
    {
        if ( $baseFile->encoding != $fileCompareTo->encoding ) {
            $this->encodingDiffs[] = sprintf(
                'File %s has encoding %s but file %s has encoding %s',
                $baseFile->name,
                $baseFile->encoding,
                $fileCompareTo->name,
                $fileCompareTo->encoding
            );
        }
    }

    protected function checkFileDiff (stdClass $baseFile, stdClass $fileCompareTo): string
    {
        $builder = new UnifiedDiffOutputBuilder(
            "--- {$baseFile->name}\n+++ {$fileCompareTo->name}\n",
            true
        );

        if ( $baseFile->content == $fileCompareTo->content ) {
            return "Contents are identical\n";
        }

        $this->contentDiffsCounter++;
        $differ = new Differ($builder);
        return $differ->diff($baseFile->content, $fileCompareTo->content);
    }

    protected function printReportHeader (): string
    {
        return sprintf(
            <<<TXT
            %'=104s
            DIFF REPORT (%s)
            %'=104s

            TXT,
            '',
            $this->reportDate,
            ''
        );
    }

    protected function printFilesHeader (stdClass $baseFile, stdClass $fileCompareTo): string
    {
        return sprintf(
            <<<TXT
            %'-104s
            %20s%-40s   %-40s
            %'-104s
            %-20s%40s    %40s
            %-20s%40s    %40s
            %-20s%40s    %40s
            %'-104s
            TXT,
            '',
            '',
            'Base file',
            'File compare to',
            '',
            'File:',
            $baseFile->name,
            $fileCompareTo->name,
            'Encoding:',
            $baseFile->encoding,
            $fileCompareTo->encoding,
            'Size:',
            $baseFile->size,
            $fileCompareTo->size,
            ''
        );
    }

    /**
     * @throws ReportException|Exception
     */
    public function makeReport (): void
    {
        if ( ! isset($this->baseFile) ) {
            throw new ReportException('Please set base file');
        }

        if ( ! count($this->filesCompareTo) ) {
            throw new ReportException('No files to compare');
        }

        $output = [];
        $output[] = $this->printReportHeader();

        foreach ( $this->filesCompareTo as $fileCompareTo ) {
            $output[] = $this->printFilesHeader($this->baseFile, $fileCompareTo);
            $output[] = $this->checkFileDiff($this->baseFile, $fileCompareTo);
            $this->checkEncoding($this->baseFile, $fileCompareTo);
        }

        $output = implode("\n", $output);

        if ( $this->contentDiffsCounter ) {
            $this->mailHandler->send("Diff report ({$this->reportDate})", sprintf('<pre>%s</pre>', $output));
        }

        if ( count($this->encodingDiffs) ) {
            $this->smsHandler->send(implode("\n", $this->encodingDiffs));
        }

        $this->saveDiffFile($output);
        echo $output;
    }
}
