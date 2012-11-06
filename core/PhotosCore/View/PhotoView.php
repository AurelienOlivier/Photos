<?php
namespace PhotosCore\View;

class PhotoView extends \phpillowView {
	protected $viewDefinitions = array(
		// Index blog entries by their title, and list all comments
		'entries' => 'function(doc) {
			if (doc.type == "photo") {
				emit(doc._id, {id: doc._id, title: doc.title, extension: doc.extension, files: doc._attachments, description: doc._description});
			}
		}',
	);
	
	protected function getViewName() {
		return 'photos';
	}
}