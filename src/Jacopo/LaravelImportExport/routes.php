<?php
// get base route
$base_route = Config::get('laravel-import-export::baseconf.base_application_route','importer');

// Home controller
Route::controller($base_route,'Jacopo\LaravelImportExport\HomeController');
// Import controller
Route::controller("{$base_route}-import",'Jacopo\LaravelImportExport\ImportController');
// Export controller
Route::controller("{$base_route}-export",'Jacopo\LaravelImportExport\ExportController');