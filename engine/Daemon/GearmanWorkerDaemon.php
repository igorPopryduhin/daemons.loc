<?php

use Engine\Daemon\DaemonPHP;

require_once 'DaemonException.php';

/**
 * The daemon wrapper for the GearmanWorker class.
 *
 * <code>
 * class MyGearmanWorker extends GearmanWorkerDaemon {
 *     public function __construct() {
 *         parent::__construct();
 *         $worker = $this->_getWorker();
 *         $worker->addFunction( 'send_email', array( $this, 'sendEmail' ) );
 *     }
 *
 *     public function sendEmail( $job ) {
 *         // sending email stuff...
 *     }
 * }
 * </code>
 */
class GearmanWorkerDaemon extends DaemonPHP {

    /**
     * @access private
     * @var GearmanWorker
     */
    private $_worker = null;

    /**
     * Creates new instance of GearmanWorkerDaemon class.
     *
     * @access public
     * @param string|array $servers An array or comma separated list of job servers in the format host:port. If no port is specified, it defaults to 4730.
     * @param string $path Absolute path to the pid file.
     */
    public function __construct( $servers = '127.0.0.1:4730', $path = null ) {
        parent::__construct( $path );

        $this->_worker = new GearmanWorker();
        if ( is_array( $servers ) ) {
            $servers = implode( ',', $servers );
        }
        $this->_worker->addServers( $servers );
    }

    /**
     * Waits for a job to be assigned and then calls the appropriate callback function.
     *
     * @access public
     */
    public function run() {
        while ( $this->_worker->work() );
    }

    /**
     * Returns current worker.
     *
     * @access protected
     * @return GearmanWorker Current instance of GearmanWorker class.
     */
    protected function _getWorker() {
        return $this->_worker;
    }

}