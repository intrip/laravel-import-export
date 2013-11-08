<?php
// routes

// Home controller
Route::controller('importer','Jacopo\LaravelImportExport\HomeController');
// Import controller
Route::controller('importer-import','Jacopo\LaravelImportExport\ImportController');
// Export controller
Route::controller('importer-export','Jacopo\LaravelImportExport\ExportController');
