<?php

namespace PetrKnap\Web\Service;

use Netpromotion\Profiler\Profiler;
use Nette\Database\Context;
use PetrKnap\Web\Bootstrap;

class BackupService
{
    /**
     * @var string
     */
    private $destination;

    /**
     * @var array
     */
    private $directories;

    /**
     * @var Context
     */
    private $database;

    public function __construct($destination, array $directories, Context $database)
    {
        $this->destination = $destination;
        $this->directories = $directories;
        $this->database = $database;
    }

    public function backup()
    {
        Profiler::start("BackupService::backup()");
        foreach ($this->directories as $name => $config) {
            $tmpFile = tempnam(Bootstrap::TEMP_DIR, "backup_");
            $archive = new \ZipArchive();

            $rc = $archive->open($tmpFile, \ZipArchive::CREATE);
            if ($rc !== true) {
                throw new \RuntimeException(\ZipArchive::class, $rc);
            }

            /** @var \SplFileInfo[] $directoryIterator */
            $directoryIterator = iterator_to_array(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($config["path"])
                )
            );
            $this->database->beginTransaction();
            $updated = false;
            foreach ($directoryIterator as $fileInfo) {
                if ($fileInfo->isFile()) {
                    $path = realpath($fileInfo->getPathname());
                    $hashedPath = sha1($path).md5($path);
                    $hashedContent = sha1_file($path);
                    /** @noinspection SqlDialectInspection, SqlNoDataSourceInspection */
                    $oldHashedContent = $this->database->query(
                        "SELECT HEX(hashed_content) AS hashed_content FROM backup__hashed_files WHERE hashed_path = UNHEX(?)",
                        $hashedPath
                    )->fetchField("hashed_content");
                    if (strtolower($oldHashedContent) != strtolower($hashedContent)) {
                        $updated = true;
                        /** @noinspection SqlDialectInspection, SqlNoDataSourceInspection */
                        $this->database->query(
                            "INSERT INTO backup__hashed_files (hashed_path, hashed_content) VALUES (UNHEX(?), UNHEX(?)) ON DUPLICATE KEY UPDATE hashed_content = UNHEX(?)",
                            $hashedPath,
                            $hashedContent,
                            $hashedContent
                        );
                    }
                    $archive->addFile($path);
                }
            }
            $archive->close();
            if ($updated) {
                copy($tmpFile, sprintf(
                    "%s/%s (%s).zip",
                    $this->destination,
                    $name,
                    (new \DateTime())->format(\DateTime::ISO8601)
                ));
                $this->applyHistoryLimit($this->destination, $name, $config["limit"]);
            }
            $this->database->commit();
            unlink($tmpFile);
        }
        Profiler::finish("BackupService::backup()");
    }

    private function applyHistoryLimit($destination, $name, $historyLimit)
    {
        $backupFiles = [];
        /** @var \SplFileInfo[] $directoryIterator */
        $directoryIterator = iterator_to_array(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($destination)
            )
        );
        foreach ($directoryIterator as $fileInfo) {
            if ($fileInfo->isFile() && substr($fileInfo->getBasename(), 0, -31) == $name) {
                $backupFiles[] = $fileInfo->getPathname();
            }
        }
        rsort($backupFiles);
        foreach ($backupFiles as $backupFile) {
            if ($historyLimit == 0) {
                unlink($backupFile);
            } else {
                $historyLimit--;
            }
        }
    }
}
