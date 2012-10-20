<?php
use Silex\Application\path;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../lib/pillow/bootstrap.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use PhotosCore\Document\PhotoDocument;
use PhotosCore\View\PhotoView;

// Register main application
$app = new Silex\Application();
$app['debug'] = true;

// Register Twig
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\SecurityServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__ . '/../templates',
));
// $app->register(new Silex\Provider\MonologServiceProvider(), array(
// 	'monolog.logfile' => __DIR__.'/../logs/development.log',
// ));

// Define security settings
$app['security.firewalls'] = array(
	'admin' => array(
		'pattern' => '^/',
		'anonymous' => array(),
		'form' => array('login_path' => '/login', 'check_path' => '/admin/login_check'),
		'logout' => array('logout_path' => '/logout', 'target_url' => '/'),
		'users' => array(
			// raw password is foo
			'admin' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
		),
	),
);

// CouchDB connection
phpillowConnection::createInstance('localhost', 5984, 'root', 'root');
phpillowConnection::getInstance()->setDatabase("photos");

// Routes definition

// homepage
$app->get('/', function (Silex\Application $app) {
	$photos = PhotoView::entries();
	
	return $app['twig']->render('welcome.twig', array(
		'photos' => $photos->rows,
		'app' => $app,
	));
})->bind("home");

// display a photo
$app->get('/photo/{id}', function (Silex\Application $app, $id) {
	$photo = new PhotoDocument();
	$photo->fetchById($id);
	
	return $app['twig']->render('photo.twig', array('photo' => $photo));
});

// login form
$app->get('/login', function(Request $request) use ($app) {
	return $app['twig']->render('login.twig', array(
		'error'         => $app['security.last_error']($request),
		'last_username' => $app['session']->get('_security.last_username'),
		'app' => $app,
	));
})->bind("login");

// add form
$app->get('/add', function (Silex\Application $app) {
	return $app['twig']->render('admin/add.twig', array(
		'app' => $app,
	));
})->bind("add");

$app->post('/create', function (Request $request) use ($app) {
	$title = $request->get("title");
	$uploadedFile = $request->files->get("photo");
	
	if ($title != null && $uploadedFile != null) {
		$now = new DateTime();
		
		$photo = new PhotoDocument();
		
		$photo->title = $title;
		$photo->filename = $uploadedFile->getClientOriginalName();
		$photo->extension = substr($photo->filename, strrpos($photo->filename, "."));
		
		$photo->added = $now->getTimestamp();
		
		$photo->attachFile($uploadedFile, $uploadedFile->getClientOriginalName());
		
		$photo->save();
		
		return $app->redirect('/photo/' . $photo->_id);
	} else {
		throw new Exception("Missing parameters in upload form");
	}
})->bind("create");

// user management
$app->get("/profile", function() {
	
})->bind("profile");

// image handler
$app->get('/photoview/{id}.{ext}', function (Silex\Application $app, $id, $ext) {
	$photo = new PhotoDocument();
	$photo->fetchById($id);
	
	$file = $photo->getFile($photo->filename);
	file_put_contents(__DIR__ . "/photoview/" . $id . "." .$ext, $file->data);
	
	return new Response($file->data, 200, array(
		'Content-Type' => $file->contentType
	));
});

$app->run();