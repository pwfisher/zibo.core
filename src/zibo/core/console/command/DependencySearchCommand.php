<?php

namespace zibo\core\console\command;

use zibo\core\console\output\Output;
use zibo\core\console\InputValue;

/**
 * Command to show an overview of the defined dependencies
 */
class DependencySearchCommand extends AbstractCommand {

    /**
     * Constructs a new di command
     * @return null
     */
    public function __construct() {
        parent::__construct('dependency', 'Show an overview of the defined dependencies');
        $this->addArgument('query', 'Query to search the dependencies', false, true);
    }

    /**
     * Executes the command
     * @param zibo\core\console\InputValue $input
     * @param zibo\core\console\output\Output $output Output interface
     * @return null
     */
    public function execute(InputValue $input, Output $output) {
        $dependencyContainer = $this->zibo->getEnvironment()->getDependencyContainer();
        $dependencies = $dependencyContainer->getDependencies();

        // filter the dependencies on the search query
        $query = $input->getArgument('query');
        if ($query) {
            foreach ($dependencies as $interface => $null) {
                if (stripos($interface, $query) !== false) {
                    continue;
                }

                unset($dependencies[$interface]);
            }

            $output->write('Defined dependencies for "' . $query . '":');
        } else {
            $output->write('Defined dependencies:');
        }

        ksort($dependencies);

        // write the dependencies
        $tab = '    ';
        foreach ($dependencies as $interface => $interfaceDependencies) {
            $output->write('- ' . $interface);

            foreach ($interfaceDependencies as $dependency) {
                $id = $dependency->getId();

                $output->write($tab . '#' . $id . ' ' . $dependency->getClassName());

                $padding = $tab . str_repeat(' ', strlen($id) + 2);
                $argumentPadding = $padding . $tab;

                $constructor = $dependency->getConstructorArguments();
                if ($constructor) {
                    $output->write($padding . '->__construct(');
					$output->write($argumentPadding . str_replace("\n", "\n" . $argumentPadding, implode(",\n", $constructor)));
                    $output->write($padding . ')');
                }

                $calls = $dependency->getCalls();
                if ($calls) {
                    foreach ($calls as $call) {
                        $output->write($padding . '->' . $call->getMethodName() . '(');
                        $output->write($argumentPadding . str_replace("\n", "\n" . $argumentPadding, implode(",\n" . $argumentPadding, $call->getArguments())));
                        $output->write($padding . ')');
                    }
                }
            }

            $output->write('');
        }
    }

}