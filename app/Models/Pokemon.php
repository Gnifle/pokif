<?php

namespace App\Models;

use App\Models\EggGroup;
use Eloquent;
use DB;
use Config;
use Illuminate\Support\Collection;

/**
 * App\Models\Pokemon
 *
 * @property int                 $id
 * @property string              $identifier
 * @property int                 $species_id
 * @property int                 $height
 * @property int                 $weight
 * @property int                 $base_experience
 * @property int                 $order
 * @property int                 $is_default
 * @property-read PokemonSpecies $species
 * @property-read Ability[]      $abilities
 * @property-read EggGroup[]     $egg_groups
 * @property-read PokemonForm    $form
 * @property-read string         $name
 * @method static Pokemon whereId( $value )
 * @method static Pokemon whereIdentifier( $value )
 * @method static Pokemon whereSpeciesId( $value )
 * @method static Pokemon whereHeight( $value )
 * @method static Pokemon whereWeight( $value )
 * @method static Pokemon whereBaseExperience( $value )
 * @method static Pokemon whereOrder( $value )
 * @method static Pokemon whereIsDefault( $value )
 */
class Pokemon extends Eloquent {
	
	/**
	 * @var bool
	 */
	public $timestamps = false;
	
	/**
	 * @var string
	 */
	protected $table = 'pokemon';
	
	/**
	 * @var array
	 */
	protected $guarded = [];
	
	/**
	 * Many-to-one inverse relationship with PokemonSpecies
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function species() {
		
		return $this->belongsTo( PokemonSpecies::class );
	}
	
	/**
	 * Many-to-many relationship with Ability
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function abilities() {
		
		return $this->belongsToMany( Ability::class, 'pokemon_abilities' );
	}
	
	/**
	 * Many-to-many relationship with Ability where pivot is_hidden == true
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function hidden_abilities() {
		
		return $this->belongsToMany( Ability::class, 'pokemon_abilities' )->wherePivot( 'is_hidden', true );
	}
	
	/**
	 * Many-to-many relationship with EggGroup
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function egg_groups() {
		
		return $this->hasMany( EggGroup::class );
	}
	
	/**
	 * One-to-many relationship with PokemonForm
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function form() {
		
		return $this->hasOne( PokemonForm::class );
	}
	
	public function name( $language ) {
		
		return $this->species->getNameAttribute( $language );
	}
	
	/**
	 * @return int The number (ID) of the Pokemon
	 */
	public function getNumberAttribute() {
		
		return $this->species_id;
	}
	
	/**
	 * @return string The name of the Pokemon (species)
	 */
	public function getNameAttribute() {
		
		return $this->species->name;
	}
	
	/**
	 * @return object The list of sprites for the Pokemon
	 */
	public function getSpritesAttribute() {
		
		$sprites = DB::table( 'pokemon_sprites' )
		             ->where( 'pokemon_id', $this->id )
		             ->value( 'sprites' );
		
		return json_decode( $sprites );
	}
	
	/**
	 * @return string The classification of the Pokemon (species)
	 */
	public function getClassificationAttribute() {
		
		return $this->species->genus;
	}
	
	public function getDexEntriesAttribute() {

		$dex_entries = [];
		
		$entries = DB::table( 'pokemon_dex_numbers' )
		             ->join( 'pokedexes', 'pokemon_dex_numbers.pokedex_id', '=', 'pokedexes.id' )
		             ->join( 'pokedex_prose', 'pokedex_prose.pokedex_id', '=', 'pokedexes.id' )
		             ->where( 'pokemon_dex_numbers.species_id', '=', $this->species_id )
		             ->where( 'pokedexes.region_id', '=', Config::get( 'generation' ) )
		             ->where( 'pokedex_prose.local_language_id', '=', Language::active() )
		             ->orWhere( 'pokedex_prose.local_language_id', '=', 'en' )
		             ->select( 'pokemon_dex_numbers.pokedex_number AS number', 'pokedex_prose.name AS pokedex_name' )
		             ->get();
		
		foreach( $entries as $entry ) {
			
			$dex_entries[ $entry->pokedex_name ] = $entry->number;
		}
		
		return $dex_entries;
	}
	
	/**
	 * @return Collection List of forms for the Pokemon
	 */
	public function getFormsAttribute() {
		
		$same_species_ids = array_column( $this->sameSpecies( true ), 'id' );
		
		return PokemonForm::whereIn( 'pokemon_id', $same_species_ids )->get();
	}
	
	/**
	 * @return Collection List of alternate (non-default) forms for the Pokemon
	 */
	public function getAlternateFormsAttribute() {
		
		$same_species_ids = array_column( $this->sameSpecies( false ), 'id' );
		
		return PokemonForm::whereIn( 'pokemon_id', $same_species_ids )->get();
	}
	
	/**
	 * Gets all Pokemon with the same species ID as the current Pokemon instance. Can, optionally, include the default
	 * form.
	 *
	 * @param bool $include_self (optional) Whether to include the default form. Defaults to false.
	 *
	 * @return array List of same species forms.
	 */
	private function sameSpecies( $include_self = false ) {
		
		$query = $this->where( 'species_id', $this->id );
		
		if( ! $include_self ) {
			
			$query = $query->where( 'is_default', false );
		}
		
		return $query->get()->toArray();
	}
}
