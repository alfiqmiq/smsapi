<?php
/** @noinspection ForgottenDebugOutputInspection */
declare(strict_types = 1);
define('PROGRAM_DIR', __DIR__);
error_reporting(0);
date_default_timezone_set('Europe/Warsaw');

use Report\Helpers\{LoadFileException, ParseOptions, ParseOptionsException};
use Report\Report;
use Dotenv\Dotenv;

set_error_handler(
    static function(int $severity, string $message, string $file, int $line)
    {
        throw new ErrorException($message, $severity, $severity, $file, $line);
    }
);

try {
    if ( PHP_SAPI !== 'cli' ) {
        throw new RuntimeException('This is CLI only script');
    }

    if ( PHP_VERSION_ID < 70400 ) {
        throw new RuntimeException('Please check PHP version (should be >= 7.4)');
    }

    if ( ! file_exists('../.env') || ! is_readable('../.env') ) {
        throw new RuntimeException('Required .env file missing');
    }

    require_once '../vendor/autoload.php';

    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
    $dotenv->required([
        'EMAIL_ACCOUNT_ADDRESS',
        'EMAIL_ACCOUNT_USER',
        'EMAIL_ACCOUNT_PASS',
        'EMAIL_ACCOUNT_HOST',
        'SMSAPI_TOKEN',
        'REPORT_EMAIL',
        'REPORT_PHONE'
    ])->notEmpty();

    $cliOptions = (ParseOptions::getInstance())->getOptions();

    $report = new Report();
    $report->setBaseFile($cliOptions->baseFile);
    foreach ( $cliOptions->filesCompareTo as $fileCompareTo ) {
        $report->addFileCompareTo($fileCompareTo);
    }
    $report->makeReport();
} catch (RuntimeException|LoadFileException $e) {
    echo <<<EOF
    An error occurred:
    {$e->getMessage()}
    EOF;
} catch (ParseOptionsException $e) {
    $programName = basename(__FILE__);
    echo <<<EOF
    DIFF REPORT

    Error: {$e->getMessage()}

    Usage:
        php {$programName} -f filename -c filename [-c filename]

        -f file compare to
        -c file to compare (may be occurred multiple times)

    EOF;
} catch (Throwable $e) {
    do {
        printf(
            <<<TXT
            File:    %s:%d
            Class:   %s
            Message: %s

            Trace:
            %s

            TXT,
            $e->getFile(),
            $e->getLine(),
            get_class($e),
            $e->getMessage(),
            $e->getTraceAsString(),
        );
    } while ( $e = $e->getPrevious() );
}


