<?php

/**
 * Copyright (c) 2017-2020 gyselroth™  (http://www.gyselroth.net)
 *
 * @package \gyselroth\Helper
 * @author  gyselroth™  (http://www.gyselroth.com)
 * @link    http://www.gyselroth.com
 * @license Apache-2.0
 */

namespace Tests;

use Gyselroth\HelperLog\LoggerWrapper;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class HelperTestCase extends TestCase {
    public static string $rootPath;

    protected string $_pathToLogfile = __DIR__ . '/tmp/app.log';
    protected $_logMock;

    protected ?LoggerWrapper $_logger;

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        self::emptyTempFolder();
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpParamsInspection */
        $this->_logger = new LoggerWrapper($this->_setUpLogger(), true, '.');
    }

    protected function tearDown(): void
    {
        self::emptyTempFolder();
        if (\is_object($this->_logger)) {
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection ImplicitMagicMethodCallInspection */
            $this->_logger->__destruct();
            $this->_logger = null;
        }
    }

    private function emptyTempFolder(): void
    {
        $tmpPath = self::getGlobalTmpPath();
        if (is_dir($tmpPath)) {
            self::rmdirRecursive($tmpPath);
        }
    }

    /**
     * @param  bool $useStdOut
     * @param  string $logLevel
     * @return Logger
     * @throws \Exception
     */
    private function _setUpLogger($useStdOut = false, $logLevel = 'DEBUG'): Logger
    {
        $path =  $useStdOut ? 'php://stdout' : __DIR__ . '/../var/logs/phpunit.log';

        return (new Logger('phpunit'))
//            ->pushProcessor(new Monolog\Processor\UidProcessor())
            ->pushHandler(new StreamHandler($path, $logLevel));
    }

    /**
     * @param  bool $createIfNotExists
     * @return string
     * @throws \RuntimeException
     */
    public static function getGlobalTmpPath($createIfNotExists = false): string
    {
        $path = self::getRootPath() . '/tmp';
        if ($createIfNotExists
            && !\is_dir($path)
            && !\mkdir($path)
        ) {
            throw new \RuntimeException(\sprintf('Failed create directory "%s"', $path));
        }

        return $path;
    }

    /**
     * @return string
     * @singleton
     */
    private static function getRootPath(): string
    {
        if (empty(self::$rootPath)) {
            /** @noinspection ReturnFalseInspection */
            $pathDelimiter = false !== \strpos(__DIR__, '/vendor/')
                // __DIR__ is e.g. '/srv/www/trunk/vendor/gyselroth/....../HelperFile
                ? '/vendor/'

                // Fallback: helper-package seems to be itself the project at hand
                // (is not installed in one of composer's vendor sub directories)
                // __DIR__ is e.g. '/srv/www/trunk/src/Gyselroth/Helper'
                : '/src/';

            self::$rootPath = \explode($pathDelimiter, __DIR__)[0];
        }

        return self::$rootPath;
    }

    /**
     * @param  string|array $path
     * @return bool
     */
    private static function rmdirRecursive($path): bool
    {
        if (\is_array($path)) {
            foreach ($path as $pathSingle) {
                self::rmdirRecursive($pathSingle);
            }

            return true;
        }

        if (!\is_dir($path)) {
            return false;
        }

        /** @noinspection ReturnFalseInspection */
        $files = \scandir($path);

        if (!$files) {
            return false;
        }

        foreach ($files as $file) {
            if ('.' !== $file
                && '..' !== $file
            ) {
                $rmPath = $path . '/' . $file;

                if (\is_dir($rmPath)) {
                    self::rmdirRecursive($rmPath);
                } else {
                    \unlink($rmPath);
                }
            }
        }

        /** @noinspection ReturnFalseInspection */
        \reset($files);

        return \rmdir($path);
    }
}

