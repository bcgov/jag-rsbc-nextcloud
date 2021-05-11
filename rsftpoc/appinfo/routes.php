<?php

return [
	'resources' => [
		'note' => ['url' => '/notes'],
		'note_api' => ['url' => '/api/0.1/notes']
	],
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'note#uploadfile', 'url' => '/note/{id}/uploadfile', 'verb' => 'POST'],
		['name' => 'note#deletefile', 'url' => '/note/{id}/deletefile', 'verb' => 'POST'],
		['name' => 'note#listfiles', 'url' => '/note/{id}/listfiles', 'verb' => 'GET'],
		['name' => 'note#uploadSettings', 'url' => '/settings/upload', 'verb' => 'POST'],
		['name' => 'note#downloadSettings', 'url' => '/settings/download', 'verb' => 'GET'],
		['name' => 'note_api#preflighted_cors', 'url' => '/api/0.1/{path}',
			'verb' => 'OPTIONS', 'requirements' => ['path' => '.+']]
	]
];
