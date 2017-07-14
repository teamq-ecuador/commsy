<?php

namespace CommsyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use CommsyBundle\CalDAV\AuthPDO;
use CommsyBundle\CalDAV\PrincipalPDO;
use CommsyBundle\CalDAV\CalendarPDO;

use
    Sabre\DAV,
    Sabre\CalDAV,
    Sabre\DAVACL,
    Sabre\CardDAV;

class CalDAVController extends Controller
{
    /**
     * @Route("/{portalId}/caldav/")
     * @Template()
     */
    public function caldavAction($portalId, Request $request) {
        $this->caldavServer($portalId)->exec();

        return new Response();
    }

    /**
     * @Route("/{portalId}/caldav/{userId}/")
     * @Template()
     */
    public function caldavPrincipalAction($portalId, $userId, Request $request) {
        $this->caldavServer($portalId)->exec();

        return new Response();
    }

    /**
     * @Route("/{portalId}/caldav/calendars/{userId}/")
     * @Template()
     */
    public function caldavCalendarsAction($portalId, $userId, Request $request) {
        $this->caldavServer($portalId)->exec();

        return new Response();
    }

    /**
     * @Route("/{portalId}/caldav/calendars/{userId}/{calendarId}/")
     * @Template()
     */
    public function caldavCalendarAction($portalId, $userId, $calendarId, Request $request) {
        $this->caldavServer($portalId)->exec();

        return new Response();
    }

    /**
     * @Route("/{portalId}/caldav/calendars/{userId}/{calendarId}/{object}/")
     * @Template()
     */
    public function caldavCalendarObjectAction($portalId, $userId, $calendarId, $object, Request $request) {
        $this->caldavServer($portalId)->exec();

        return new Response();
    }

    /**
     * @Route("/{portalId}/caldav/principals/{userId}/")
     * @Template()
     */
    public function caldavPrincipalsAction($portalId, $userId, Request $request) {
        $this->caldavServer($portalId)->exec();

        return new Response();
    }

    //Mapping PHP errors to exceptions
    function exception_error_handler($errno, $errstr, $errfile, $errline ) {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    private function caldavServer ($portalId) {
        $commsyPdo = new \PDO('mysql:dbname=commsy;host=commsy_db', 'commsy', 'commsy');
        $pdo = new \PDO('mysql:dbname=caldav;host=commsy_db', 'commsy', 'commsy');

        set_error_handler(array($this, "exception_error_handler"));

        // Files we need
        //require_once '../../../vendor/autoload.php';

        // Backends
        if (false) {
            $authBackend = new AuthPDO($commsyPdo, $this->container, $portalId);
            $authBackend->setRealm('CommSy');
            $principalBackend = new PrincipalPDO($commsyPdo, $this->container, $portalId);
            $calendarBackend = new CalendarPDO($commsyPdo, $this->container, $portalId);
        } else {
            $authBackend = new DAV\Auth\Backend\PDO($pdo);
            $authBackend->setRealm('CommSy');
            $principalBackend = new DAVACL\PrincipalBackend\PDO($pdo);
            $calendarBackend = new CalDAV\Backend\PDO($pdo);
        }

        // Directory tree
        $tree = array(
            new DAVACL\PrincipalCollection($principalBackend),
            new CalDAV\CalendarRoot($principalBackend, $calendarBackend)
        );


        // The object tree needs in turn to be passed to the server class
        $server = new DAV\Server($tree);

        // You are highly encouraged to set your WebDAV server base url. Without it,
        // SabreDAV will guess, but the guess is not always correct. Putting the
        // server on the root of the domain will improve compatibility.
        $server->setBaseUri('/app_dev.php/'.$portalId.'/caldav/');

        // Authentication plugin
        $authPlugin = new DAV\Auth\Plugin($authBackend,'SabreDAV');
        $server->addPlugin($authPlugin);

        // CalDAV plugin
        $caldavPlugin = new CalDAV\Plugin();
        $server->addPlugin($caldavPlugin);

        // CardDAV plugin
        #$carddavPlugin = new CardDAV\Plugin();
        #$server->addPlugin($carddavPlugin);

        // ACL plugin
        #$aclPlugin = new DAVACL\Plugin();
        #$server->addPlugin($aclPlugin);

        // Support for html frontend
        $browser = new DAV\Browser\Plugin();
        $server->addPlugin($browser);

        // And off we go!
        //$server->exec();

        return $server;
    }
}