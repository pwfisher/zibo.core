<?php

namespace zibo\library\dependency\io;

use zibo\library\dependency\DependencyCallArgument;
use zibo\library\dependency\DependencyContainer;
use zibo\library\filesystem\File;

/**
 * Cache decorator for another DependencyIO. This IO will get the dependencies
 * from the wrapped IO and generate a PHP script to include. When the generated
 * PHP script exists, this will be used to define the container. It should be
 * faster since only 1 include is done which contains plain PHP variable
 * initialization;
 */
class CachedDependencyIO implements DependencyIO {

    /**
     * DependencyIO which is cached by this DependencyIO
     * @var zibo\core\dependency\io\DependencyIO
     */
    private $io;

    /**
     * File to write the cache to
     * @var zibo\library\filesystem\File
     */
    private $file;

    /**
     * Constructs a new cached DependencyIO
     * @param DependencyIO $io the DependencyIO which needs a cache
     * @param zibo\library\filesystem\File $file The file for the cache
     * @return null
     */
    public function __construct(DependencyIO $io, File $file) {
        $this->io = $io;
        $this->setFile($file);
    }

    /**
     * Sets the file for the generated code
     * @param zibo\library\filesystem\File $file The file to generate the code in
     * @return null
     */
    public function setFile(File $file) {
        $this->file = $file;
    }

    /**
     * Gets the file for the generated code
     * @return zibo\library\filesystem\File The file to generate the code in
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * Gets a dependency container
     * @return zibo\library\dependency\DependencyContainer
     */
    public function getContainer() {
        if ($this->file->exists()) {
            // the generated script exists, include it
            include $this->file->getPath();

        	if (isset($container)) {
        	    // the script defined a container, return it
                return $container;
        	}
        }
        // we have no container, use the wrapped IO to get one
    	$container = $this->io->getContainer();

    	// generate the PHP code for the obtained container
    	$php = $this->generatePhp($container);

    	// make sure the parent directory of the script exists
    	$parent = $this->file->getParent();
    	$parent->create();

    	// write the PHP code to file
    	$this->file->write($php);

    	// return the contianer
    	return $container;
    }

    /**
	 * Generates a PHP source file for the provided dependency container
	 * @param zibo\library\dependency\DependencyContainer $container
	 * @return string
     */
    protected function generatePhp(DependencyContainer $container) {
        $output = "<?php\n\n";
        $output .= "/*\n";
        $output .= " * This file is generated by zibo\library\dependency\io\CachedDependencyIO.\n";
        $output .= " */\n";
        $output .= "\n";
        $output .= "use zibo\\library\\dependency\\Dependency;\n";
        $output .= "use zibo\\library\\dependency\\DependencyCall;\n";
        $output .= "use zibo\\library\\dependency\\DependencyCallArgument;\n";
        $output .= "use zibo\\library\\dependency\\DependencyContainer;\n";
        $output .= "\n";
        $output .= '$container' . " = new DependencyContainer();\n";
        $output .= "\n";

        $dependencies = $container->getDependencies();
        foreach ($dependencies as $interface => $interfaceDependencies) {
            foreach ($interfaceDependencies as $dependency) {
                $callIndex = 1;

                $calls = $dependency->getCalls();
                if ($calls) {
                    foreach ($calls as $call) {
                        $argumentIndex = 1;

                        $arguments = $call->getArguments();
                        if ($arguments) {
                            foreach ($arguments as $argument) {
                                $output .= '$a' . $argumentIndex . ' = new DependencyCallArgument(';
                                $output .= var_export($argument->getName(), true) . ', ';
                                $output .= var_export($argument->getType(), true) . ', ';
                                $output .= var_export($argument->getProperties(), true) . ");\n";
                                $argumentIndex++;
                            }
                        }

                        $output .= '$c' . $callIndex . ' = new DependencyCall(';
                        $output .= var_export($call->getMethodName(), true) . ', ';
                        $output .= var_export($call->getId(), true) . ");\n";

                        for ($i = 1; $i < $argumentIndex; $i++) {
                            $output .= '$c' . $callIndex . '->addArgument($a' . $i . ");\n";
                        }

                        $callIndex++;
                    }
                }

                $constructorArguments = $dependency->getConstructorArguments();
                if ($constructorArguments) {
                    $argumentIndex = 1;

                    foreach ($constructorArguments as $argument) {
                        $output .= '$a' . $argumentIndex . ' = new DependencyCallArgument(';
                        $output .= var_export($argument->getName(), true) . ', ';
                        $output .= var_export($argument->getType(), true) . ', ';
                        $output .= var_export($argument->getProperties(), true) . ");\n";
                        $argumentIndex++;
                    }

                    $output .= '$c' . $callIndex . " = new DependencyCall('__construct');\n";

                    for ($i = 1; $i < $argumentIndex; $i++) {
                        $output .= '$c' . $callIndex . '->addArgument($a' . $i . ");\n";
                    }

                    $callIndex++;
                }

                $output .= '$d = new Dependency(';
                $output .= var_export($dependency->getClassName(), true) . ', ';
                $output .= var_export($dependency->getId(), true) . ");\n";

                for ($i = 1; $i < $callIndex; $i++) {
                    $output .= '$d->addCall($c' . $i . ");\n";
                }

                $output .= '$d->setInterfaces(' . var_export($dependency->getInterfaces(), true) . ");\n";

                $output .= '$container->addDependency(';
                $output .= '$d);';
                $output .= "\n\n";
            }
        }

        return $output;
    }

}