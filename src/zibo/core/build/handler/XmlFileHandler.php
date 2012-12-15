<?php

namespace zibo\core\build\handler;

use zibo\library\filesystem\File;
use zibo\library\xml\dom\Document;

/**
 * Default copy implementation of a FileHandler
 */
class XmlFileHandler implements FileHandler {

    /**
     * Handles a file in the a Zibo module
     * @param zibo\library\filesystem\File $source The source file
     * @param zibo\library\filesystem\File $destination The destination
     * file
     * @return null
     */
    public function handleFile(File $source, File $destination) {
        $sourceDocument = new Document();
        $sourceDocument->load($source->getPath());
        $sourceRoot = $sourceDocument->documentElement;

        $destinationDocument = new Document();
        if ($destination->exists()) {
            $destinationDocument->load($destination->getPath());
            $destinationRoot = $destinationDocument->documentElement;
        } else {
            $destinationRoot = $destinationDocument->createElement($sourceRoot->tagName);
            $destinationRoot = $destinationDocument->appendChild($destinationRoot);
        }

        foreach ($sourceRoot->childNodes as $sourceNode) {
            $nodeElement = $destinationDocument->importNode($sourceNode, true);
            $destinationRoot->appendChild($nodeElement);
        }

        $parent = $destination->getParent();
        $parent->create();

        $destinationDocument->save($destination->getPath());
    }

}