<?php
namespace PhotosCore\Document;

class PhotoDocument extends \phpillowDocument {
	
	protected static $type = 'photo';
	
	protected $requiredProperties = array(
		'title',
		'filename',
		'extension',
		'added',
		'description'
	);
	
	public function __construct() {
		$this->properties = array(
			'title' => new \phpillowStringValidator(),
			'filename' => new \phpillowStringValidator(),
			'extension' => new \phpillowStringValidator(),
			'added' => new \phpillowDateValidator(),
			'description' => new \phpillowStringValidator()
		);
		
		parent::__construct();
	}
	
	protected function generateId() {
		$time = new \DateTime();
		
		return $this->stringToId($this->storage->title . ' ' . $time->getTimestamp());
	}
	
	protected function getType() {
		return self::$type;
	}
}