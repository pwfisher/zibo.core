<?php

namespace zibo\core\console;

use \Exception;

/**
 * Command line argument parser
 */
class ArgumentParser {

    /**
     * Gets an array of the argument string
     * @param string $string Argument string
     * @return array Array with the arguments
     * @throws Exception when the provided string is invalid
     */
    public static function getArguments($string) {
        if (!is_string($string)) {
            throw new Exception('Provided string is invalid');
        }

        $arguments = array();
        $argument = '';

        $open = false;

        $stringLength = strlen($string);
        for ($i = 0; $i < $stringLength; $i++) {
            $char = $string[$i];

            if ($open) {
                if ($char == $open) {
                    $arguments[] = $argument;
                    $argument = '';
                } else {
                    $argument .= $char;
                }

                continue;
            }

            if ($char == '"' || $char == '\'') {
                $open = $char;
                continue;
            }

            if ($char == ' ') {
                $arguments[] = $argument;
                $argument = '';
                continue;
            }

            $argument .= $char;
        }

        if ($argument) {
            $arguments[] = $argument;
        }

         foreach ($arguments as $index => $argument) {
             $arguments[$index] = trim($argument);
             if ($arguments[$index] == '') {
                 unset($arguments[$index]);
             }
         }

        return $arguments;
    }

    /**
     * Parse the arguments for the command line interface
     *
     * <p>This method will parse the arguments which can be passed in different
     * ways: variables, flags and/or values.</p>
     * <ul>
     * <li>--named-boolean</li>
     * <li>--named-variable="your value"</li>
     * <li>-f</li>
     * <li>-afc</li>
     * <li>plain values</li>
     * </ul>
     *
     * <p>An example:<br />
     * <p>index.php agenda/event/15 --detail --comments=no --title="Agenda events"
     *  -afc nice</p>
     * <p>will result in</p>
     * <p>array(<br />
     * &nbsp;&nbsp;&nbsp;&nbsp;0 =&gt; "agenda/event/15"<br />
     * &nbsp;&nbsp;&nbsp;&nbsp;'detail' =&gt; true<br />
     * &nbsp;&nbsp;&nbsp;&nbsp;'comments' =&gt; no<br />
     * &nbsp;&nbsp;&nbsp;&nbsp;'title' =&gt; "Agenda events"<br />
     * &nbsp;&nbsp;&nbsp;&nbsp;'a' =&gt; true<br />
     * &nbsp;&nbsp;&nbsp;&nbsp;'f' =&gt; true<br />
     * &nbsp;&nbsp;&nbsp;&nbsp;'c' =&gt; true<br />
     * &nbsp;&nbsp;&nbsp;&nbsp;1 =&gt; "nice"<br />
     * )
     * </p>
     *
     * @param array $arguments The arguments from the command line
     * @return array Parsed arguments
     */
    public static function parseArguments(array $arguments) {
        $parsedArguments = array();

        foreach ($arguments as $argument) {
            if (substr($argument, 0, 2) == '--') {
                // variables: --key=value or --key
                $eqPos = strpos($argument, '=');
                if ($eqPos === false) {
                    $key = substr($argument, 2);
                    if (!isset($parsedArguments[$key])) {
                        $parsedArguments[$key] = true;
                    }
                } else {
                    $key = substr($argument, 2, $eqPos - 2);
                    $parsedArguments[$key] = substr($argument, $eqPos + 1);
                }
            } elseif (substr($argument, 0, 1) == '-') {
                // flags: -n or -arf
                if (substr($argument, 2, 1) == '='){
                    $key = substr($argument, 1, 1);
                    $parsedArguments[$key] = substr($argument, 3);
                } else {
                    $flags = str_split(substr($argument, 1));
                    foreach ($flags as $flag){
                        if (!isset($parsedArguments[$flag])) {
                            $parsedArguments[$flag] = true;
                        }
                    }
                }
            } else {
                // values
                $parsedArguments[] = $argument;
            }
        }

        return $parsedArguments;
    }

}