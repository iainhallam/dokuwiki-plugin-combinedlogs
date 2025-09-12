<?php

/**
 * DokuWiki Plugin combinedlogs (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Iain Hallam <first.last@example.com>
 */

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Extension\Event;
use dokuwiki\Logger;

// phpcs:disable PSR1.Classes.ClassDeclaration -- DokuWiki plugins must not be in a namespace
// phpcs:disable Squiz.Classes.ValidClassName -- DokuWiki requires snake case class names
class action_plugin_combinedlogs extends ActionPlugin
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = Logger::getInstance($this->getConf('facility'));
    }

    /** @inheritDoc */
    public function register(EventHandler $controller)
    {
        $controller->register_hook('LOGGER_DATA_FORMAT', 'AFTER', $this, 'handleLoggerDataFormat');
    }

    /**
     * Event handler for LOGGER_DATA_FORMAT
     *
     * @see https://www.dokuwiki.org/devel:events:LOGGER_DATA_FORMAT
     * @param Event $event Event object by reference
     * @param mixed $param optional parameter passed when event was registered
     * @return void
     */
    public function handleLoggerDataFormat(Event $event)
    {
        global $conf;

        // Work out the file name
        if ($this->getConf('by_date') == 1) {
            $event->data['logfile'] = $this->logger->getLogFile($event->data['datetime']);
        } else {
            $event->data['logfile'] = $conf['logdir'] . '/' . $this->getConf('facility') . '.log';
        }

        // Modify the first log line to include the facility name
        $event->data['loglines'][0] = preg_replace(
            '/^([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})\t/',  // YYYY-MM-DD HH:MM:SS
            "$1\t" . strtoupper($event->data['facility']) . "\t",
            $event->data['loglines'][0]
        );
    }
}
