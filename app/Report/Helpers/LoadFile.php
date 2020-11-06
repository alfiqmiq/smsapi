<?php
declare(strict_types = 1);

namespace Report\Helpers;

use Report\ReportException;
use stdClass;

class LoadFile
{
    protected string $fileName;
    protected string $fileContentType;
    protected int $fileSize;
    protected string $fileEncoding;
    protected string $fileContent;

    /**
     * LoadFile constructor.
     *
     * @param string $fileName
     *
     * @throws LoadFileException
     */
    public function __construct (string $fileName)
    {
        if ( ! file_exists($fileName) || ! is_readable($fileName) ) {
            throw new LoadFileException("File: {$fileName}, wrong filename/path or file is not readable");
        }

        $this->fileName = $fileName;
        $this->getFileContentType($fileName);
        $this->fileSize = filesize($fileName);
        $this->fileContent = file_get_contents($fileName);
        $this->fileEncoding = mb_detect_encoding($this->fileContent, mb_detect_order(), true);
    }

    /**
     * @param string $fileName
     *
     * @throws LoadFileException
     */
    protected function getFileContentType (string $fileName): void
    {
        $contentType = mime_content_type($fileName);
        if ( $contentType != 'text/plain' ) {
            throw new LoadFileException("File: {$fileName} is not text file");
        }

        $this->fileContentType = $contentType;
    }

    public function getFile (): object
    {
        $file = new stdClass();
        $file->name = $this->fileName;
        $file->contentType = $this->fileContentType;
        $file->size = $this->fileSize;
        $file->encoding = $this->fileEncoding;
        $file->content = $this->fileContent;

        return $file;
    }
}
