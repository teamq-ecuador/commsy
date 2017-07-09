<?php

namespace CommsyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

use CommsyBundle\CalDAV\PDO;

use
    Sabre\DAV,
    Sabre\CalDAV,
    Sabre\DAVACL,
    Sabre\CardDAV;

class CalDAVController extends Controller
{
    /**
     * @Route("/caldav")
     * @Template()
     */
    public function caldavAction(Request $request) {

        //$pdo = new \PDO('sqlite:data/db.sqlite');
        //$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $commsyPdo = new \PDO('mysql:dbname=commsy;host=commsy_db', 'commsy', 'commsy');
        $pdo = new \PDO('mysql:dbname=caldav;host=commsy_db', 'commsy', 'commsy');

        set_error_handler(array($this, "exception_error_handler"));

        // Files we need
        //require_once '../../../vendor/autoload.php';

        // Backends
        $authBackend = new PDO($commsyPdo);
        $principalBackend = new DAVACL\PrincipalBackend\PDO($pdo);
        $calendarBackend = new CalDAV\Backend\PDO($pdo);

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
        $server->setBaseUri('/app_dev.php/caldav/');

        // Authentication plugin
        $authPlugin = new DAV\Auth\Plugin($authBackend,'SabreDAV');
        $server->addPlugin($authPlugin);

        // CalDAV plugin
        $caldavPlugin = new CalDAV\Plugin();
        $server->addPlugin($caldavPlugin);

        // CardDAV plugin
        $carddavPlugin = new CardDAV\Plugin();
        $server->addPlugin($carddavPlugin);

        // ACL plugin
        $aclPlugin = new DAVACL\Plugin();
        $server->addPlugin($aclPlugin);

        // Support for html frontend
        $browser = new DAV\Browser\Plugin();
        $server->addPlugin($browser);

        // And off we go!
        $server->exec();
    }

    //Mapping PHP errors to exceptions
    function exception_error_handler($errno, $errstr, $errfile, $errline ) {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
}