<?php
use Symfony\Component\HttpFoundation\File\File;

use Symfony\Component\HttpFoundation\Response;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../vendor/pillow/bootstrap.php';

require_once __DIR__.'/../core/documents/PhotoDocument.php';
require_once __DIR__.'/../core/views/PhotoView.php';

use Symfony\Component\HttpFoundation\Request;

// Register main application
$app = new Silex\Application();
//$app['autoloader']->registerNamespace('PhotoCore', __DIR__.'/../core');
$app['debug'] = true;

// Register Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__ . '/../templates',
));

// CouchDB connection
phpillowConnection::createInstance('localhost', 5984, 'root', 'root');
phpillowConnection::getInstance()->setDatabase("photos");

// Routes definition

// homepage
$app->get('/', function (Silex\Application $app) {
	$photos = PhotoView::entries();
	
	if (sizeof($photos->rows) > 0) {
		return $app['twig']->render('welcome.twig', array(
			'photos' => $photos->rows
		));
	}
});

// display a photo
$app->get('/photo/{id}', function (Silex\Application $app, $id) {
	$photo = new PhotoDocument();
	$photo->fetchById($id);
	
	return $app['twig']->render('photo.twig', array('photo' => $photo));
});

// add form
$app->get('/add', function (Silex\Application $app) {
	return $app['twig']->render('add.twig');
});

$app->post('/create', function (Request $request) use ($app) {
	$title = $request->get("title");
	$uploadedFile = $request->files->get("photo");
	
	if ($title != null && $uploadedFile != null) {
		$now = new DateTime();
		
		$photo = new PhotoDocument();
		$photo->title = $title;
		$photo->filename = $uploadedFile->getClientOriginalName();
		$photo->added = $now->getTimestamp();
		$photo->attachFile($uploadedFile, $uploadedFile->getClientOriginalName());
		
		$photo->save();
		
		return $app->redirect('/photo/' . $photo->_id);
	} else {
		throw new Exception("Missing parameters in upload form");
	}
});

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