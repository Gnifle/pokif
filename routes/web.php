<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get( '/', function() {
	
	return View::make( 'pages.home' );
} );

Route::get( '/pokedex', 'PokedexController@index' );

Route::get( '/pokedex/{generation}', 'PokedexController@index' );

Route::get( '/pokedex/{generation}/{pokemon}', 'PokemonController@show' )->where( [ 'generation' => '[0-9]', 'pokemon' => '[a-z0-9-]+' ] );

Route::get( '/tools', function() {
	
	return View::make( 'pages.tools' );
} );

Route::get( '/tools/importer', function() {
	
	return View::make( 'pages.tools.importer' );
} );

//Route::get( '/pokemon/{identifier}', 'PokemonController@show' );
Route::get( '/tools/importer/pokemon/{identifier}', 'PokemonController@show' );

//Route::post( 'import-pokemon', [ 'uses' => 'PokemonController@import' ] );