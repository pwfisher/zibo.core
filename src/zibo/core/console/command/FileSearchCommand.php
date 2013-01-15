<?php

namespace zibo\core\console\command;

use zibo\core\console\output\Output;
use zibo\core\console\InputValue;
use zibo\core\Zibo;

use zibo\library\filesystem\File;

/**
 * Command to search for files relative to the Zibo directory structure
 */
class FileSearchCommand extends AbstractCommand {

    /**
     * Constructs a new file command
     * @return null
     */
    public function __construct() {
        parent::__construct('file', 'Search for files relative to the Zibo directory structure.');
        $this->addArgument('path', 'Relative path of the file');
    }

    /**
     * Executes the command
     * @param zibo\core\console\InputValue $input The input
     * @param zibo\core\console\output\Output $output Output interface
     * @return null
     */
    public function execute(InputValue $input, Output $output) {
        $file = ltrim($input->getArgument('path'), '/');

        $output->write('Application files:');

        $files = $this->zibo->getFiles($file);
        if ($files) {
            foreach ($files as $f) {
                $output->write('- ' . $f);
            }
        } else {
            $output->write('<none>');
        }

        $output->write('');

        $hasPublic = false;
        $output->write('Public files:');

        $publicFile = new File($this->zibo->getPublicDirectory(), $file);
        if ($publicFile->exists()) {
            $output->write('- ' . $file);

            $hasPublic = true;
        }

        $files = $this->zibo->getFiles(Zibo::DIRECTORY_PUBLIC . '/' . $file);
        if ($files) {
            foreach ($files as $f) {
                $output->write('- ' . $f);
            }
        } elseif (!$hasPublic) {
            $output->write('<none>');
        }

        $output->write('');
    }

}