<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PokemonTest extends TestCase {
	
	/**
	 * A basic test example.
	 *
	 * @return void
	 */
	public function testExample() {
		
		$this->visit( '/pokedex/6/413' )->see( 'Illuminate' );
	}
}
