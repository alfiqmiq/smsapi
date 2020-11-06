<?php
declare(strict_types = 1);

namespace Report\Helpers;

use stdClass;

class ParseOptions
{
    private static $instance;
    private array $options;
    private stdClass $parser;

    /**
     * ParseOptions constructor.
     *
     * @throws ParseOptionsException
     */
    private function __construct ()
    {
        $this->parser = new stdClass();
        $this->options = getopt('f:c:');
        $this->setOptionBaseFile();
        $this->setOptionFilesCompareTo();
    }

    public static function getInstance (): ParseOptions
    {
        if ( ! self::$instance instanceof self ) {
            self::$instance = new ParseOptions();
        }

        return self::$instance;
    }

    public function getOptions (): stdClass
    {
        return $this->parser;
    }

    /**
     * @throws ParseOptionsException
     */
    private function setOptionBaseFile (): void
    {
        if ( ! isset($this->options['f']) || ! is_string($this->options['f']) ) {
            throw new ParseOptionsException('option -f not set or type/value mismatch');
        }

        $this->parser->baseFile = trim($this->options['f']);
    }

    /**
     * @throws ParseOptionsException
     */
    private function setOptionFilesCompareTo (): void
    {
        if ( ! isset($this->options['c']) || ! (is_string($this->options['c']) || is_array($this->options['c'])) ) {
            throw new ParseOptionsException('Option(s) -c not set or type/value mismatch');
        }

        $this->parser->filesCompareTo = [];

        if ( is_string($this->options['c']) ) {
            $this->parser->filesCompareTo[] = trim($this->options['c']);
        } else {
            foreach ( $this->options['c'] as $optionC ) {
                $this->parser->filesCompareTo[] = trim($optionC);
            }
        }
    }
}
