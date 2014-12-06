<?php

return [

	'table' => 'revisions',

	'templates' => [
		'diff'    => [
			'start' => '<div>',
			'body'  => '<p class="diff-string">'
							.'<span class="diff-key">:key</span>:'
							.'<span class="diff-old">:old</span> &rarr; <span class="diff-new">:new</span>'
						.'</p>',
			'end'   => '</div>',
		],
	],

];