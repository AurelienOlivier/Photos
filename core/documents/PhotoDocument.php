<?php

class PhotoDocument extends phpillowDocument {
	
	protected static $type = 'photo';
	
	protected $requiredProperties = array(
		'title',
		'filename',
		'added',
	);
	
	public function __construct() {
		$this->properties = array(
			'title' => new phpillowStringValidator(),
			'filename' => new phpillowStringValidator(),
			'added' => new phpillowDateValidator(),
		);
		
		parent::__construct();
	}
	
	protected function generateId() {
		$time = new DateTime();
		
		return $this->stringToId($this->storage->title . ' ' . $time->getTimestamp());
	}
	
	protected function getType() {
		return self::$type;
	}
}