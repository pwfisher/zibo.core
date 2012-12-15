<?php

namespace zibo\library;

use \Exception;

/**
 * Class to fork a callback into a new process
 *
 * When starting a thread, you have to keep on checking if the thread is still
 * alive, even when your main script is done. This is to prevent zombie processes.
 */
class Thread {

    /**
     * The callback for this thread
     * @param string|array|Callback
     */
    protected $callback;

    /**
     * The process id
     * @var integer
     */
    protected $pid;

    /**
     * Constructs a new thread
     * @param string|array|Callback $callback The callback to execute in the thread
     * @return null
     * @throws Exception when process control is not supported  by your PHP
     * configuration
     */
    public function __construct($callback) {
        if (!function_exists('pcntl_fork')) {
            throw new Exception('Could not create a Thread instance. Your PHP installation does not support process control, please install the pcntl plugin.');
        }

        $this->callback = new Callback($callback);
    }

    /**
     * Gets the callback of this thread
     * @return zibo\library\Callback
     */
    public function getCallback() {
        return $this->callback;
    }

    /**
     * Gets the current process id
     * @return integer
     */
    public function getPid() {
        return $this->pid;
    }

    /**
     * Executes the callback in a different thread. All arguments to this
     * method will be passed on to the callback.
     *
     * The callback will be invoked but the script will not wait for it to
     * finish. You have to keep on checking if the thread is still alive,
     * even when your main script is done. This is to prevent zombie processes.
     * @return null
     * @see isAlive()
     */
    public function run() {
        $pid = @pcntl_fork();
        if ($pid == -1) {
            throw new Exception('Could not run the thread: unable to fork the callback');
        }

        if ($pid) {
            // parent process code
            $this->pid = $pid;
        } else {
            // child process code
            pcntl_signal(SIGTERM, array($this, 'signalHandler'));

            try {
                $this->callback->invokeWithArrayArguments(func_get_args());
            } catch (Exception $exception) {
                echo get_class($exception) . "\n";
                echo $exception->getMessage() . "\n";
                echo $exception->getTraceAsString();
            }

            exit;
        }
    }

    /**
     * Checks if the current thread is running
     * @return boolean True if the thread is still running, false otherwise
     */
    public function isAlive() {
        $pid = pcntl_waitpid($this->pid, $status, WNOHANG);

        return $pid === 0;
    }

    /**
     * Attempts to stop the current process
     * @param integer $signal The signal to sent to the process (SIGKILL or SIGTERM)
     * @param boolean $wait Set to true to wait until the process is killed
     * @return null
     * @throws Exception when an invalid signal is provided
     */
    public function interrupt($signal = SIGKILL, $wait = false) {
        if (!$this->isAlive()) {
            return;
        }

        if ($signal != SIGKILL && $signal != SIGTERM) {
            throw new Exception('Could not interrupt the process: invalid signal provided, try SIGKILL or SIGTERM');
        }

        posix_kill($this->pid, $signal);

        if ($wait) {
            pcntl_waitpid($this->pid, $status = 0);
        }
    }

    /**
     * Handles the signals from the forked process
     * @param integer $signal The signal of the forked process
     * @return null
     */
    protected function signalHandler($signal) {
        if ($signal == SIGTERM) {
            exit;
        }
    }

}