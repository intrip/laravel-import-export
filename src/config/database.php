<?php
return array(

'connections' => array(

    'import' => array(
			'driver'    => 'mysql', //sqlite | mysql | pgsql | sqlsrv
			'host'      => 'localhost',
			'database'  => 'laravel_import_export',
			'username'  => 'root',
			'password'  => 'root',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
    ),
),
);