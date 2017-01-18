<?php

    class api {
		
		private $app = null;
     
		
        public function __construct() {
            global $app;
            
            $this->app = $app;
			
			$this->rewrite_rules();
        }
		
		private function rewrite_rules() {			
			add_action( 'init', function() {
				$plugin_dir = str_replace( get_home_url() . '/', '', WP_PLUGIN_URL ) . '/ecotrust-api/';
				add_rewrite_rule( 'api/inbound$', $plugin_dir . 'assets/inbound.php', 'top' );
				add_rewrite_rule( 'api/outbound$', $plugin_dir . 'assets/outbound.php', 'top' );
			}, 10, 0 );
		}
		
		public static function file_get_contents_curl( $url ) {
			$ch = curl_init();

			curl_setopt( $ch, CURLOPT_HEADER, 0 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_POST, 0 );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
			curl_setopt( $ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

			$data = curl_exec( $ch );
			curl_close( $ch );

			return $data;
		}
		public function return producers(){
			global $request, $exclude, $per_page, $page,$query;
			
		}
		
		public function return_translations() {
			global $request, $exclude, $per_page, $page, $query;
			
			$ids = $this->app->get_translations([
				'post__not_in' => $exclude,
				'posts_per_page' => $per_page,
				'paged' => $page,
				's' => $query
			]);

			foreach( $ids as $entry ) :

				$translation = new translation( $entry );

				$translations[] = ( object ) [
					'code' => $translation->code,
					'english' => $translation->en,
					'french' => $translation->fr
				];

			endforeach;

			echo json_encode( [ 
				'result' => 'success',
				'translations' => isset( $translations ) ? $translations : []
			], JSON_PRETTY_PRINT );
		}
		
		public function return_countries() {
			global $request, $exclude, $per_page, $page, $query;
			
			$ids = $this->app->get_countries([
				'post__not_in' => $exclude,
				'posts_per_page' => $per_page,
				'paged' => $page,
				's' => $query
			]);

			foreach( $ids as $entry ) :

				$country = new country( $entry );

				$countries[] = ( object ) [
					'id' => $country->id,
					'name' => $country->name,
					'french_name' => $country->french_name,
					'code' => $country->code
				];

			endforeach;

			echo json_encode( [ 
				'result' => 'success',
				'countries' => isset( $countries ) ? $countries : []
			], JSON_PRETTY_PRINT );
		}
		
		public function return_seasons() {
			global $request, $exclude, $per_page, $page, $query;
			
			$countries = isset( $request->countries ) ? ( array ) $request->countries : [];

			if ( count( $countries ) == 0 ) :
			
				$paging = $per_page != -1 ? [
					'offset' => $per_page * ( $page - 1 ),
					'number' => $per_page
				] : [];

				$season_ids = $this->app->get_seasons(
					array_merge( $paging, [
						'exclude' => $exclude,
						'name__like' => $query
					])
				);

			else :

				$ids = [];

				foreach( $countries as $entry ) :

					$country = new country( $entry );

					$ids = array_unique(
						array_merge( $ids, $country->get_seasons() )
					);

				endforeach;

				if ( count( $ids ) > 0 ) :
			
					$params = [
						'hide_empty' => false,
						'orderby' => 'name',
						'order' => 'ASC',
						'include' => $ids,
						'exclude' => $exclude,
						'name__like' => $query
					];

					foreach( get_terms( 'season', $params ) as $term )
						$entries[] = $term->term_id;
			
				endif;
			
				$season_ids = array_slice( 
					( isset( $entries ) ? $entries : [] ), 
					( $per_page * ( $page - 1 ) ), 
					$per_page 
				);

			endif;

			foreach( $season_ids as $entry ) :

				$season = new season( $entry );

				$seasons[] = ( object ) [
					'id' => $season->id,
					'name' => $season->name,
					'country' => $season->country != 0 ? $season->country : null,
					'year' => $season->start->year != 0 ? $season->start->year : null,
					'period' => ( object ) [
						'start' => $season->start->month != 0 ? $season->start->month : null,
						'end' => $season->end->month != 0 ? $season->end->month : null
					],
					'is_active' => $season->is_archived ? false : true
				];

			endforeach;

			echo json_encode( [ 
				'result' => 'success',
				'seasons' => isset( $seasons ) ? $seasons : []
			], JSON_PRETTY_PRINT );
		}
		
		public function return_districts() {
			global $request, $exclude, $per_page, $page, $query;
			
			$countries = isset( $request->countries ) ? ( array ) $request->countries : [];
			$ppos = isset( $request->ppos ) ? ( array ) $request->ppos : [];

			if ( count( $countries ) == 0 && count( $ppos ) == 0 ) :
			
				$paging = $per_page != -1 ? [
					'offset' => $per_page * ( $page - 1 ),
					'number' => $per_page
				] : [];

				$district_ids = $this->app->get_districts(
					array_merge( $paging, [
						'exclude' => $exclude,
						'name__like' => $query
					])
				);

			elseif ( count( $countries ) > 0 && count( $ppos ) == 0 ) :

				$ids = [];

				foreach( $countries as $entry ) :

					$country = new country( $entry );

					$ids = array_unique(
						array_merge( $ids, $country->get_districts() )
					);

				endforeach;
			
				if ( count( $ids ) > 0 ) :

					$params = [
						'hide_empty' => false,
						'orderby' => 'name',
						'order' => 'ASC',
						'include' => $ids,
						'exclude' => $exclude,
						'name__like' => $query
					];

					foreach( get_terms( 'district', $params ) as $term )
						$entries[] = $term->term_id;
			
				endif;
			
				$district_ids = array_slice( 
					( isset( $entries ) ? $entries : [] ), 
					( $per_page * ( $page - 1 ) ), 
					$per_page 
				);

			elseif ( count( $countries ) == 0 && count( $ppos ) > 0 ) :

				$ids = [];

				foreach( $ppos as $entry ) :

					$ppo = new ppo( $entry );

					$ids = array_unique(
						array_merge( $ids, $ppo->get_districts() )
					);

				endforeach;
			
				if ( count( $ids ) > 0 ) :

					$params = [
						'hide_empty' => false,
						'orderby' => 'name',
						'order' => 'ASC',
						'include' => $ids,
						'exclude' => $exclude,
						'name__like' => $query
					];

					foreach( get_terms( 'district', $params ) as $term )
						$entries[] = $term->term_id;
			
				endif;
			
				$district_ids = array_slice( 
					( isset( $entries ) ? $entries : [] ), 
					( $per_page * ( $page - 1 ) ), 
					$per_page 
				);

			else :

				$ids = $this->app->get_districts([
					'exclude' => $exclude,
					'name__like' => $query
				]);

				foreach( $ids as $entry ) :

					$district = new district( $entry );

					$intersection = array_intersect( $district->get_ppos(), $ppos );

					if ( ( in_array( $district->country, $countries ) ) && ( count( $intersection ) > 0 ) )
						$entries[] = $district->id;

				endforeach;	
			
				$district_ids = array_slice( 
					( isset( $entries ) ? $entries : [] ), 
					( $per_page * ( $page - 1 ) ), 
					$per_page 
				);

			endif;

			foreach( $district_ids as $entry ) :

				$district = new district( $entry );

				$districts[] = ( object ) [
					'id' => $district->id,
					'name' => $district->name,
					'country' => $district->country
				];

			endforeach;

			echo json_encode( [ 
				'result' => 'success',
				'districts' => isset( $districts ) ? $districts : []
			], JSON_PRETTY_PRINT );
		}
		
		public function return_villages() {
			global $request, $exclude, $per_page, $page, $query;
			
			$countries = isset( $request->countries ) ? ( array ) $request->countries : [];
			$districts = isset( $request->districts ) ? ( array ) $request->districts : [];			
			$ppos = isset( $request->ppos ) ? ( array ) $request->ppos : [];
			
			$entries = [];
			
			if ( count( $ppos ) > 0 ) :
			
				foreach( $ppos as $entry ) :
			
					$ppo = new ppo( $entry );
			
					$ppo_entries = array_merge( isset( $ppo_entries ) ? $ppo_entries : [], $ppo->get_villages() );
					
				endforeach;
			
				$entries = array_merge( $entries, isset( $ppo_entries ) ? $ppo_entries : [] );
			
			endif;
			
			if ( count( $countries ) > 0 ) :
			
				foreach( $countries as $entry ) :
			
					$country = new country( $entry );
			
					$country_entries = array_merge( isset( $country_entries ) ? $country_entries : [], $country->get_villages() );
					
				endforeach;
			
				$entries = isset( $country_entries ) ? array_intersect( $entries, $country_entries ) : $entries;
			
			endif;
			
			if ( count( $districts ) > 0 ) :
			
				foreach( $districts as $entry ) :
			
					$district = new district( $entry );
			
					$district_entries = array_merge( isset( $district_entries ) ? $district_entries : [], $district->get_villages() );
					
				endforeach;
			
				$entries = isset( $district_entries ) ? array_intersect( $entries, $district_entries ) : $entries;
			
			endif;
			
			$paging = $per_page != -1 ? [
				'offset' => $per_page * ( $page - 1 ),
				'number' => $per_page
			] : [];

			$params = count( $entries ) > 0 ? [ 'include' => $entries ] : [];
			
			$village_ids = $this->app->get_villages(
				array_merge( [
					'exclude' => $exclude,
					'name__like' => $query
				], $paging, $params )
			);

			foreach( $village_ids as $entry ) :

				$village = new village( $entry );

				$villages[] = ( object ) [
					'id' => $village->id,
					'name' => $village->name,
					'district' => $village->district != 0 ? $village->district : null
				];

			endforeach;

			echo json_encode( [ 
				'result' => 'success',
				'villages' => isset( $villages ) ? $villages : []
			], JSON_PRETTY_PRINT );
		}
		
		public function return_crop_types() {
			global $request, $exclude, $per_page, $page, $query;
			
			$paging = $per_page != -1 ? [
				'offset' => $per_page * ( $page - 1 ),
				'number' => $per_page
			] : [];

			$crop_type_ids = $this->app->get_crop_types(
				array_merge( $paging, [
					'exclude' => $exclude,
					'name__like' => $query
				])
			);

			foreach( $crop_type_ids as $entry ) :

				$crop_type = new crop_type( $entry );

				$crop_types[] = ( object ) [
					'id' => $crop_type->id,
					'name' => $crop_type->name
				];

			endforeach;

			echo json_encode( [ 
				'result' => 'success',
				'crop_types' => isset( $crop_types ) ? $crop_types : []
			], JSON_PRETTY_PRINT );
		}
		
		public function return_varieties() {
			global $request, $exclude, $per_page, $page, $query;
			
			$crop_types = isset( $request->crop_types ) ? ( array ) $request->crop_types : [];

			if ( count( $crop_types ) == 0 ) :
			
				$paging = $per_page != -1 ? [
					'offset' => $per_page * ( $page - 1 ),
					'number' => $per_page
				] : [];

				$variety_ids = $this->app->get_varieties(
					array_merge( $paging, [
						'exclude' => $exclude,
						'name__like' => $query
					])
				);

			else :

				$ids = [];

				foreach( $crop_types as $entry ) :

					$crop_type = new crop_type( $entry );

					$ids = array_unique(
						array_merge( $ids, $crop_type->get_varieties() )
					);

				endforeach;

				if ( count( $ids ) > 0 ) :
			
					$params = [
						'hide_empty' => false,
						'orderby' => 'name',
						'order' => 'ASC',
						'include' => $ids,
						'exclude' => $exclude,
						'name__like' => $query
					];

					foreach( get_terms( 'variety', $params ) as $term )
						$entries[] = $term->term_id;
			
				endif;
			
				$variety_ids = array_slice( 
					( isset( $entries ) ? $entries : [] ), 
					( $per_page * ( $page - 1 ) ), 
					$per_page 
				);

			endif;

			foreach( $variety_ids as $entry ) :

				$variety = new variety( $entry );

				$varieties[] = ( object ) [
					'id' => $variety->id,
					'name' => $variety->name,
					'crop_type' => $variety->crop_type
				];

			endforeach;

			echo json_encode( [ 
				'result' => 'success',
				'varieties' => isset( $varieties ) ? $varieties : []
			], JSON_PRETTY_PRINT );
		}
		
		public function return_fertilizers() {
			global $request, $exclude, $per_page, $page, $query;
			
			$paging = $per_page != -1 ? [
				'offset' => $per_page * ( $page - 1 ),
				'number' => $per_page
			] : [];

			$fertilizer_ids = $this->app->get_fertilizers(
				array_merge( $paging, [
					'exclude' => $exclude,
					'name__like' => $query
				])
			);

			foreach( $fertilizer_ids as $entry ) :

				$fertilizer = new fertilizer( $entry );

				$fertilizers[] = ( object ) [
					'id' => $fertilizer->id,
					'name' => $fertilizer->name
				];

			endforeach;

			echo json_encode( [ 
				'result' => 'success',
				'fertilizers' => isset( $fertilizers ) ? $fertilizers : []
			], JSON_PRETTY_PRINT );
		}
		
		public function return_ppos() {
			global $request, $exclude, $per_page, $page, $query;
			
			$countries = isset( $request->countries ) ? ( array ) $request->countries : [];

			$countries = count( $country_ids ) > 0 ? [
				'meta_query' => [
					[
						'key' => 'country',
						'value' => $countries,
						'compare' => 'IN'
					]
				]
			] : [];

			$paging = $per_page != -1 ? [
				'offset' => $per_page * ( $page - 1 ),
				'number' => $per_page
			] : [];		

			$ppo_ids = $this->app->get_ppos(
				array_merge( [
					'exclude' => $exclude,
					'search' => !empty( $query ) ? '*' . $query . '*' : '',
				], $countries, $paging )
			);

			foreach( $ppo_ids as $entry ) :

				$ppo = new ppo( $entry );

				$ppos[] = ( object ) [
					'id' => $ppo->id,
					'name' => ( object ) [
						'user' => $ppo->username,
						'first' => $ppo->first_name,
						'last' => $ppo->last_name
					],
					'email' => $ppo->email,
					'password' => $ppo->app_password,
					'phones' => $ppo->phone_numbers,
					'country' => $ppo->country != 0 ? $ppo->country : null,
					'avatar' => $ppo->get_avatar( 'thumbnail', false )
				];

			endforeach;

			echo json_encode( [ 
				'result' => 'success',
				'ppos' => isset( $ppos ) ? $ppos : []
			], JSON_PRETTY_PRINT );
		}
		
		public function return_farmers() {
			global $request, $exclude, $per_page, $page, $query;
			
			if ( $page == 1 ) :
			
				$args = [
					'meta_query' => [
						[
							'key' => 'created_by',
							'value' => $request->account
						]
					]
				];
			
				foreach( $this->app->get_farmers( $args ) as $id )
					$object = new farmer( $id, true );
			
			endif;
			
			$countries = isset( $request->countries ) ? ( array ) $request->countries : [];			
			$districts = isset( $request->districts ) ? ( array ) $request->districts : [];			
			$seasons = isset( $request->seasons ) ? ( array ) $request->seasons : $this->app->open_seasons();			
			$ppos = isset( $request->ppos ) ? ( array ) $request->ppos : [];
			
			$meta_query = [];
			
			if ( count( $seasons ) > 0 ) :
			
				$season_farmers = array();
			
				foreach( $this->app->array_flatten( $seasons, [] ) as $entry ) :

					$season = new season( $entry );

					$season_farmers = array_unique(
						array_merge( $season_farmers, $season->get_farmers() )
					);			

				endforeach;
			
			endif;
			
			if ( count( $ppos ) > 0 ) :
			
				$ppo_farmers = array();
			
				foreach( $ppos as $entry ) :
			
					$ppo = new ppo( $entry );
			
					$ppo_farmers = array_unique(
						array_merge( $ppo_farmers, $ppo->get_farmers() )
					);			
			
				endforeach;
			
			endif;
			
			if ( isset( $season_farmers ) && isset( $ppo_farmers ) )
				$include = array_intersect( $season_farmers, $ppo_farmers );
			
			elseif ( isset( $season_farmers ) && !isset( $ppo_farmers ) )
				$include = $season_farmers;
			
			elseif ( !isset( $season_farmers ) && isset( $ppo_farmers ) )
				$include = $ppo_farmers;	
			
			if ( count( $countries ) > 0 )
				$meta_query[] = [
					'key' => 'country',
					'value' => $countries,
					'compare' => 'IN'
				];
			
			if ( count( $districts ) > 0 )
				$meta_query[] = [
					'key' => 'district',
					'value' => $districts,
					'compare' => 'IN'
				];

			$paging = $per_page != -1 ? [
				'offset' => $per_page * ( $page - 1 ),
				'number' => $per_page
			] : [];
			
			
			if ( !isset( $include ) || ( isset( $include ) && count( $include ) > 0 ) ) :

				$farmer_ids = $this->app->get_farmers(
					array_merge(
						( isset( $include ) ? [ 'include' => $include ] : [] ),
						[
							'exclude' => $exclude,
							'search' => !empty( $query ) ? '*' . $query . '*' : '',
						], 
						( count( $meta_query ) > 0 ? [ 'meta_query' => $meta_query ] : [] ), 
						$paging 
					)
				);
			
			else :
			
				$farmer_ids = [];
			
			endif;

			foreach( $farmer_ids as $entry ) :

				$farmer = new farmer( $entry, true );

				$farmers[] = ( object ) [
					'id' => $farmer->id,
					'name' => ( object ) [
						'first' => $farmer->first_name,
						'last' => $farmer->last_name
					],
					'phones' => $farmer->phone_numbers,
					'country' => $farmer->country != 0 ? $farmer->country : null,
					'district' => $farmer->district != 0 ? $farmer->district : null,
					'village' => $farmer->village != 0 ? $farmer->village : null,
					'avatar' => $farmer->get_avatar( 'thumbnail', false ),
					'created_by' => $farmer->created_by != 0 ? $farmer->created_by : null
				];

			endforeach;

			echo json_encode( [ 
				'result' => 'success',
				'farmers' => isset( $farmers ) ? $farmers : []
			], JSON_PRETTY_PRINT );
		}
		
		public function return_agro_dealers() {
			global $request, $exclude, $per_page, $page, $query;
			
			if ( $page == 1 ) :
			
				$args = [
					'meta_query' => [
						[
							'key' => 'created_by',
							'value' => $request->account
						]
					]
				];
			
				foreach( $this->app->get_agro_dealers( $args ) as $id )
					$object = new agro_dealer( $id, true );
			
			endif;
			
			$countries = isset( $request->countries ) ? ( array ) $request->countries : [];			
			$districts = isset( $request->districts ) ? ( array ) $request->districts : [];			
			$ppos = isset( $request->ppos ) ? ( array ) $request->ppos : [];
			
			$meta_query = [];
			
			if ( count( $ppos ) > 0 ) :
			
				$ppo_agro_dealers = array();
			
				foreach( $ppos as $entry ) :
			
					$ppo = new ppo( $entry );
			
					$ppo_agro_dealers = array_unique( array_merge( $ppo_agro_dealers, $ppo->get_agro_dealers() ) );			
			
				endforeach;
			
			endif;
			
			if ( count( $countries ) > 0 )
				$meta_query[] = [
					'key' => 'country',
					'value' => $countries,
					'compare' => 'IN'
				];
			
			if ( count( $districts ) > 0 )
				$meta_query[] = [
					'key' => 'district',
					'value' => $districts,
					'compare' => 'IN'
				];

			$paging = $per_page != -1 ? [
				'offset' => $per_page * ( $page - 1 ),
				'number' => $per_page
			] : [];
			
			
			if ( !isset( $ppo_agro_dealers ) || ( isset( $ppo_agro_dealers ) && count( $ppo_agro_dealers ) > 0 ) ) :

				$agro_dealer_ids = $this->app->get_agro_dealers(
					array_merge(
						( isset( $ppo_agro_dealers ) ? [ 'include' => $ppo_agro_dealers ] : [] ),
						[
							'exclude' => $exclude,
							'search' => !empty( $query ) ? '*' . $query . '*' : '',
						], 
						(  count( $meta_query ) > 0 ? [ 'meta_query' => $meta_query ] : [] ), 
						$paging 
					)
				);
			
			else :
			
				$agro_dealer_ids = [];
			
			endif;

			foreach( $agro_dealer_ids as $entry ) :

				$agro_dealer = new agro_dealer( $entry, true );

				$agro_dealers[] = ( object ) [
					'id' => $agro_dealer->id,
					'country' => $agro_dealer->country != 0 ? $agro_dealer->country : null,
					'district' => $agro_dealer->district != 0 ? $agro_dealer->district : null,
					'village' => $agro_dealer->village != 0 ? $agro_dealer->village : null,
					'dealer' => $agro_dealer->dealer,
					'assistant' => $agro_dealer->assistant,
					'store' => $agro_dealer->store,
					'created_by' => $agro_dealer->created_by != 0 ? $agro_dealer->created_by : null
				];

			endforeach;

			echo json_encode( [ 
				'result' => 'success',
				'agro_dealers' => isset( $agro_dealers ) ? $agro_dealers : []
			], JSON_PRETTY_PRINT );
		}
		
		public function return_fields() {
			global $request, $exclude, $per_page, $page, $query;
			
			if ( $page == 1 ) :
			
				$args = [
					'meta_query' => [
						[
							'key' => 'created_by',
							'value' => $request->account
						]
					]
				];
			
				foreach( $this->app->get_fields( $args ) as $id )
					$object = new field( $id, true );
			
			endif;
			
			$countries = isset( $request->countries ) ? ( array ) $request->countries : [];			
			$districts = isset( $request->districts ) ? ( array ) $request->districts : [];			
			$villages = isset( $request->villages ) ? ( array ) $request->villages : [];			
			$seasons = isset( $request->seasons ) ? ( array ) $request->seasons : $this->app->open_seasons();			
			$ppos = isset( $request->ppos ) ? ( array ) $request->ppos : [];
			
			$meta_query = [];	
			
			if ( count( $countries ) > 0 )
				$meta_query[] = [
					'key' => 'country',
					'value' => $countries,
					'compare' => 'IN'
				];
			
			if ( count( $districts ) > 0 )
				$meta_query[] = [
					'key' => 'district',
					'value' => $districts,
					'compare' => 'IN'
				];
			
			if ( count( $villages ) > 0 )
				$meta_query[] = [
					'key' => 'village',
					'value' => $villages,
					'compare' => 'IN'
				];
			
			if ( count( $seasons ) > 0 )
				$meta_query[] = [
					'key' => 'season',
					'value' => $this->app->array_flatten( $seasons, [] ),
					'compare' => 'IN'
				];
			
			if ( count( $ppos ) > 0 )
				$meta_query[] = [
					'key' => 'created_by',
					'value' => $ppos,
					'compare' => 'IN'
				];
			
			
			$ids = $this->app->get_fields([
				'post__not_in' => $exclude,
				'posts_per_page' => $per_page,
				'paged' => $page,
				's' => $query,
				'meta_query' => $meta_query
			]);

			foreach( $ids as $entry ) :

				$field = new field( $entry, true );

				$fields[] = ( object ) [
					'id' => $field->id,
					'name' => $field->name,
					'location' => ( object ) [
						'country' => $field->country != 0 ? $field->country : null,
						'district' => $field->district != 0 ? $field->district : null,
						'village' => $field->village != 0 ? $field->village : null,
						'cordinates' => $field->cordinates
					],
					'farmer' => $field->farmer != 0 ? $field->farmer : null,
					'crop_type' => $field->crop_type,
					'variety' => $field->variety,
					'varieties' => [ $field->variety ],
					'timestamp' => $field->timestamp,
					'created_by' => $field->created_by != 0 ? $field->created_by : null,
				];

			endforeach;

			echo json_encode( [ 
				'result' => 'success',
				'fields' => isset( $fields ) ? $fields : []
			], JSON_PRETTY_PRINT );
		}
		
		public function return_plantings() {
			global $request, $exclude, $per_page, $page, $query;
			
			if ( $page == 1 ) :
			
				$args = [
					'meta_query' => [
						[
							'key' => 'created_by',
							'value' => $request->account
						]
					]
				];
			
				foreach( $this->app->get_plantings( $args ) as $id )
					$object = new planting( $id, true );
			
			endif;
			
			$countries = isset( $request->countries ) ? ( array ) $request->countries : [];			
			$districts = isset( $request->districts ) ? ( array ) $request->districts : [];			
			$villages = isset( $request->villages ) ? ( array ) $request->villages : [];			
			$seasons = isset( $request->seasons ) ? ( array ) $request->seasons : $this->app->open_seasons();			
			$ppos = isset( $request->ppos ) ? ( array ) $request->ppos : [];			
			$crop_types = isset( $request->crop_types ) ? ( array ) $request->crop_types : [];			
			$varieties = isset( $request->varieties ) ? ( array ) $request->varieties : [];
			
			$meta_query = [];	
			
			if ( count( $countries ) > 0 )
				$meta_query[] = [
					'key' => 'country',
					'value' => $countries,
					'compare' => 'IN'
				];
			
			if ( count( $districts ) > 0 )
				$meta_query[] = [
					'key' => 'district',
					'value' => $districts,
					'compare' => 'IN'
				];
			
			if ( count( $villages ) > 0 )
				$meta_query[] = [
					'key' => 'village',
					'value' => $villages,
					'compare' => 'IN'
				];
			
			if ( count( $seasons ) > 0 )
				$meta_query[] = [
					'key' => 'season',
					'value' => $this->app->array_flatten( $seasons, [] ),
					'compare' => 'IN'
				];
			
			if ( count( $crop_types ) > 0 )
				$meta_query[] = [
					'key' => 'crop_type',
					'value' => $crop_types,
					'compare' => 'IN'
				];
			
			if ( count( $varieties ) > 0 )
				$meta_query[] = [
					'key' => 'variety',
					'value' => $varieties,
					'compare' => 'IN'
				];
			
			if ( count( $ppos ) > 0 )
				$meta_query[] = [
					'key' => 'created_by',
					'value' => $ppos,
					'compare' => 'IN'
				];
			
			$ids = $this->app->get_plantings([
				'post__not_in' => $exclude,
				'posts_per_page' => $per_page,
				'paged' => $page,
				's' => $query,
				'meta_query' => $meta_query
			]);
			
			foreach( $ids as $entry ) :

				$planting = new planting( $entry, true );

				$plantings[] = ( object ) [
					'id' => $planting->id,
					'name' => $planting->name,
					'location' => ( object ) [
						'country' => $planting->country != 0 ? $planting->country : null,
						'district' => $planting->district != 0 ? $planting->district : null,
						'village' => $planting->village != 0 ? $planting->village : null
					],
					'season' => $planting->season,
					'field' => $planting->field != 0 ? $planting->field : null,
					'seed' => $planting->seed,
					'fertilizer' => $planting->fertilizer,
					'issued_sign_post' => $planting->issued_sign_post == 'yes' ? 1 : 0,
					'timestamp' => $planting->timestamp,
					'created_by' => $planting->created_by != 0 ? $planting->created_by : null
				];

			endforeach;

			echo json_encode( [ 
				'result' => 'success',
				'plantings' => isset( $plantings ) ? $plantings : []
			], JSON_PRETTY_PRINT );
		}
		
		public function return_follow_ups() {
			global $request, $exclude, $per_page, $page, $query;
			
			if ( $page == 1 ) :
			
				$args = [
					'meta_query' => [
						[
							'key' => 'created_by',
							'value' => $request->account
						]
					]
				];
			
				foreach( $this->app->get_follow_ups( $args ) as $id )
					$object = new follow_up( $id, true );
			
			endif;
			
			$countries = isset( $request->countries ) ? ( array ) $request->countries : [];			
			$districts = isset( $request->districts ) ? ( array ) $request->districts : [];			
			$villages = isset( $request->villages ) ? ( array ) $request->villages : [];			
			$seasons = isset( $request->seasons ) ? ( array ) $request->seasons : $this->app->open_seasons();			
			$ppos = isset( $request->ppos ) ? ( array ) $request->ppos : [];			
			$plantings = isset( $request->plantings ) ? ( array ) $request->plantings : [];		
			
			$meta_query = [];	
			
			if ( count( $countries ) > 0 )
				$meta_query[] = [
					'key' => 'country',
					'value' => $countries,
					'compare' => 'IN'
				];
			
			if ( count( $districts ) > 0 )
				$meta_query[] = [
					'key' => 'district',
					'value' => $districts,
					'compare' => 'IN'
				];
			
			if ( count( $villages ) > 0 )
				$meta_query[] = [
					'key' => 'village',
					'value' => $villages,
					'compare' => 'IN'
				];
			
			if ( count( $seasons ) > 0 )
				$meta_query[] = [
					'key' => 'season',
					'value' => $this->app->array_flatten( $seasons, [] ),
					'compare' => 'IN'
				];
			
			if ( count( $plantings ) > 0 )
				$meta_query[] = [
					'key' => 'planting',
					'value' => $plantings,
					'compare' => 'IN'
				];
			
			if ( count( $ppos ) > 0 )
				$meta_query[] = [
					'key' => 'created_by',
					'value' => $ppos,
					'compare' => 'IN'
				];
			
			
			$ids = $this->app->get_follow_ups([
				'post__not_in' => $exclude,
				'posts_per_page' => $per_page,
				'paged' => $page,
				's' => $query,
				'meta_query' => $meta_query
			]);

			foreach( $ids as $entry ) :

				$follow_up = new follow_up( $entry );

				$follow_ups[] = ( object ) [
					'id' => $follow_up->id,
					'name' => $follow_up->name,
					'location' => ( object ) [
						'country' => $follow_up->country != 0 ? $follow_up->country : null,
						'district' => $follow_up->district != 0 ? $follow_up->district : null,
						'village' => $follow_up->village != 0 ? $follow_up->village : null
					],
					'planting' => $follow_up->planting != 0 ? $follow_up->planting : null,
					'notes' => $follow_up->notes,
					'status' => $follow_up->status,
					'fertilizer' => $follow_up->fertilizer,
					'issued_sign_post' => $follow_up->issued_sign_post == "yes" ? true : false ,
					'timestamp' => $follow_up->timestamp,
					'created_by' => $follow_up->created_by != 0 ? $follow_up->created_by : null,
				];

			endforeach;

			echo json_encode( [ 
				'result' => 'success',
				'follow_ups' => isset( $follow_ups ) ? $follow_ups : []
			], JSON_PRETTY_PRINT );
		}
		
		public function return_harvests() {
			global $request, $exclude, $per_page, $page, $query;
			
			$countries = isset( $request->countries ) ? ( array ) $request->countries : [];
			
			$districts = isset( $request->districts ) ? ( array ) $request->districts : [];
			
			$villages = isset( $request->villages ) ? ( array ) $request->villages : [];
			
			$seasons = isset( $request->seasons ) ? ( array ) $request->seasons : [];
			
			$ppos = isset( $request->ppos ) ? ( array ) $request->ppos : [];
			
			$plantings = isset( $request->plantings ) ? ( array ) $request->plantings : [];
			
			$meta_query = array();	
			
			if ( count( $countries ) > 0 )
				$meta_query[] = [
					'key' => 'country',
					'value' => $countries,
					'compare' => 'IN'
				];
			
			if ( count( $districts ) > 0 )
				$meta_query[] = [
					'key' => 'district',
					'value' => $districts,
					'compare' => 'IN'
				];
			
			if ( count( $villages ) > 0 )
				$meta_query[] = [
					'key' => 'village',
					'value' => $villages,
					'compare' => 'IN'
				];
			
			if ( count( $seasons ) > 0 )
				$meta_query[] = [
					'key' => 'season',
					'value' => $seasons,
					'compare' => 'IN'
				];
			
			if ( count( $plantings ) > 0 )
				$meta_query[] = [
					'key' => 'planting',
					'value' => $plantings,
					'compare' => 'IN'
				];
			
			if ( count( $ppos ) > 0 )
				$meta_query[] = [
					'key' => 'created_by',
					'value' => $ppos,
					'compare' => 'IN'
				];
			
			
			$ids = $this->app->get_harvests([
				'post__not_in' => $exclude,
				'posts_per_page' => $per_page,
				'paged' => $page,
				's' => $query,
				'meta_query' => $meta_query
			]);

			foreach( $ids as $entry ) :

				$harvest = new harvest( $entry );

				$harvests[] = ( object ) [
					'id' => $harvest->id,
					'name' => $harvest->name,
					'location' => ( object ) [
						'country' => $harvest->country != 0 ? $harvest->country : null,
						'district' => $harvest->district != 0 ? $harvest->district : null,
						'village' => $harvest->village != 0 ? $harvest->village : null
					],
					'farmer' => $harvest->farmer != 0 ? $harvest->farmer : null,
					'field' => $harvest->field != 0 ? $harvest->field : null,
					'planting' => $harvest->planting != 0 ? $harvest->planting : null,
					'measurements' => ( object ) [
						'plot_size' => $harvest->plot_size,
						'number_of_rows' => $harvest->number_of_rows,
						'row_to_row' => $harvest->row_to_row,
						'number_of_plants' => $harvest->number_of_plants
					],
					'harvest' => ( object ) [
						'measurement_unit' => $harvest->measurement_unit == 'cobs' ? 1 : 0,
						'small_cobs' => $harvest->small_cobs,
						'big_cobs' => $harvest->big_cobs,
						'rotten_cobs' => $harvest->rotten_cobs,
						'quantity_harvested' => $harvest->quantity_harvested
					],
					'moisture' => $harvest->moisture,
					'timestamp' => $harvest->timestamp,
					'created_by' => $harvest->created_by != 0 ? $harvest->created_by : null,
				];

			endforeach;

			echo json_encode( [ 
				'result' => 'success',
				'harvests' => isset( $harvests ) ? $harvests : []
			], JSON_PRETTY_PRINT );
		}
				
		
		public function register_installation() {
			global $request;
			
			if ( isset( $request->version ) && !empty( $request->version ) ) :

				if ( isset( $request->device_info ) && !empty( $request->device_info ) ) :

					$id = $this->app->add_installation();

					if ( $id ) :

						$installation = new installation( $id );

						$data = ( object ) array(
							'version' => $request->version,
							'info' => json_decode( 
								str_replace( "\\", '', $request->device_info ) 
							)
						);

						$installation->update([
							'manufacturer' => isset( $data->info->manufacturer ) ? $data->info->manufacturer : '',
							'model' => isset( $data->info->model ) ? $data->info->model : '',
							'os' => isset( $data->info->platform ) ? $data->info->platform : '',
							'os_version' => isset( $data->info->version ) ? $data->info->version : '',
							'uuid' => isset( $data->info->uuid ) ? $data->info->uuid : '',
							'serial' => isset( $data->info->serial ) ? $data->info->serial : '',
							'app_version' => $data->version,
							'installation_date' => date( 'Y-m-d H:i:s' )
						]);

						echo json_encode( [ 
							'result' => 'success',
							'data' => [
								'id' => $installation->id,
								'token' => $installation->token
							]
						], JSON_PRETTY_PRINT );

					else :

						echo json_encode( [ 
							'result' => 'error',
							'message' => 'Installation registration failed'
						], JSON_PRETTY_PRINT );

					endif;

				else :

					echo json_encode( [ 
						'result' => 'error',
						'message' => 'Device info missing in request'
					], JSON_PRETTY_PRINT );

				endif;

			else :

				echo json_encode( [ 
					'result' => 'error',
					'message' => 'Version number missing in request'
				], JSON_PRETTY_PRINT );

			endif;
		}
		
		public function map_installation() {
			global $request;
			
			if ( isset( $request->token ) && !empty( $request->token ) ) :

				if ( $this->app->is_token( $request->token ) ) :

					if ( isset( $request->user ) && !empty( $request->user ) ) : 

						if ( $this->app->is_ppo( $request->user ) ) :

							if ( isset( $request->id ) && !empty( $request->id ) ) :

								$installation = new installation( $request->id );

								if ( is_int( $installation->id ) && $installation->id > 0 ) :

									$installation->update([
										'app_user' => $request->user
									]);

									echo json_encode( [ 
										'result' => 'success'
									], JSON_PRETTY_PRINT );

								else :

									echo json_encode( [ 
										'result' => 'error',
										'message' => 'Invalid ID'
									], JSON_PRETTY_PRINT );

								endif;

							else :

								echo json_encode( [ 
									'result' => 'error',
									'message' => 'ID missing in request'
								], JSON_PRETTY_PRINT );

							endif;

						else :

							echo json_encode( [ 
								'result' => 'error',
								'message' => 'Invalid user'
							], JSON_PRETTY_PRINT );

						endif;

					else :

						echo json_encode( [ 
							'result' => 'error',
							'message' => 'User missing in request'
						], JSON_PRETTY_PRINT );

					endif;

				else :

					echo json_encode( [ 
						'result' => 'error',
						'message' => 'Invalid token'
					], JSON_PRETTY_PRINT );

				endif;

			else :

				echo json_encode( [ 
					'result' => 'error',
					'message' => 'Token missing in request'
				], JSON_PRETTY_PRINT );

			endif;
		}
		
		public function update_installation() {
			global $request;
			
			if ( isset( $request->token ) && !empty( $request->token ) ) :

				if ( $this->app->is_token( $request->token ) ) :

					if ( isset( $request->version ) && !empty( $request->version ) ) : 

						if ( isset( $request->id ) && !empty( $request->id ) ) :

							$installation = new installation( $request->id );

							if ( is_int( $installation->id ) && $installation->id > 0 ) :

								$installation->update([
									'app_version' => $request->version
								]);

								echo json_encode( [ 
									'result' => 'success'
								], JSON_PRETTY_PRINT );

							else :

								echo json_encode( [ 
									'result' => 'error',
									'message' => 'Invalid ID'
								], JSON_PRETTY_PRINT );

							endif;

						else :

							echo json_encode( [ 
								'result' => 'error',
								'message' => 'ID missing in request'
							], JSON_PRETTY_PRINT );

						endif;

					else :

						echo json_encode( [ 
							'result' => 'error',
							'message' => 'Version missing in request'
						], JSON_PRETTY_PRINT );

					endif;

				else :

					echo json_encode( [ 
						'result' => 'error',
						'message' => 'Invalid token'
					], JSON_PRETTY_PRINT );

				endif;

			else :

				echo json_encode( [ 
					'result' => 'error',
					'message' => 'Token missing in request'
				], JSON_PRETTY_PRINT );

			endif;
		}
		
		public function post_villages() {
			global $request;
			
			if ( isset( $request->data ) && is_array( $request->data ) ) :

				$pairs = [];

				foreach( $request->data as $entry ) :

					$info = ( object ) $entry;

					if ( $this->app->is_district( $info->district ) ) :

						$id = $this->app->add_village( $info->name, $info->district, $info->account );

						if ( $id )
							$pairs[] = ( object ) [
								'id' => $info->id,
								'remote_id' => $id
							];

					endif;

				endforeach;

				echo json_encode( [ 
					'result' => 'success',
					'pairs' => $pairs
				], JSON_PRETTY_PRINT );

			else :

				echo json_encode( [ 
					'result' => 'error',
					'message' => 'Data missing in request'
				], JSON_PRETTY_PRINT );

			endif;
		}
		
		public function post_farmers() {
			global $request;
			
			if ( isset( $request->data ) && is_array( $request->data ) ) :

				$pairs = [];

				foreach( $request->data as $entry ) :

					$info = ( object ) $entry;

					//if ( $this->app->is_village( $info->village ) ) :

						$id = $this->app->add_farmer();

						if ( $id ) :		

							$farmer = new farmer( $id );

							$farmer->update([
								'first_name' => $info->first_name,
								'last_name' => $info->last_name,
								'phone_number' => $info->phone_1,
								'phone_number_2' => $info->phone_2,
								'village' => $info->village,
								'created_by' => $request->account
							]);

							$avatar = $info->avatar;

							$extract = substr( $avatar, 0, strpos( $avatar, ';' ) );

							$format = basename( $extract );

							$farmer->upload_avatar( str_replace( 'data:image/' . $format . ';base64,', '', $avatar ), $format );

							$pairs[] = ( object ) [
								'id' => $info->id,
								'remote_id' => $id
							];

						endif;

					//endif;

				endforeach;

				echo json_encode( [ 
					'result' => 'success',
					'pairs' => $pairs
				], JSON_PRETTY_PRINT );

			else :

				echo json_encode( [ 
					'result' => 'error',
					'message' => 'Data missing in request'
				], JSON_PRETTY_PRINT );

			endif;
		}
		
		public function post_fields() {
			global $request;
			
			if ( isset( $request->data ) && is_array( $request->data ) ) :

				$pairs = [];

				foreach( $request->data as $entry ) :

					$info = ( object ) $entry;

					$id = $this->app->add_field();

					if ( $id ) :

						$field = new field( $id );

						$field->update([
							'farmer' => isset( $info->farmer ) ? $info->farmer : '',
							'variety' => isset( $info->variety ) ? 
										$info->variety : 
										( isset( $info->varieties ) && count( $info->varieties ) > 0 ? $info->varieties[ 0 ] : '' ),
							'gps_lat' => isset( $info->gps_lat ) ? $info->gps_lat : '',
							'gps_lng' => isset( $info->gps_lng ) ? $info->gps_lng : '',
							'gps_alt' => isset( $info->gps_alt ) ? $info->gps_alt : '',
							'date' => isset( $info->date ) ? $info->date : '',
							'time' => isset( $info->time ) ? $info->time : '',
							'created_by' => $request->account
						]);

						$pairs[] = ( object ) [
							'id' => $info->id,
							'remote_id' => $id,
							'name' => $field->name
						];

					endif;

				endforeach;

				echo json_encode( [ 
					'result' => 'success',
					'pairs' => $pairs
				], JSON_PRETTY_PRINT );

			else :

				echo json_encode( [ 
					'result' => 'error',
					'message' => 'Data missing in request'
				], JSON_PRETTY_PRINT );

			endif;
		}
		
		public function post_plantings() {
			global $request;
			
			if ( isset( $request->data ) && is_array( $request->data ) ) :

				$pairs = [];

				foreach( $request->data as $entry ) :

					$info = ( object ) $entry;
			
					//if ( $this->app->is_field( $info->field ) ) :
			
						$id = $this->app->add_planting();

						if ( $id ) :

							$planting = new planting( $id );
			
							$planting->update([
								'field' => $info->field,
								'variety' => $info->variety,
								'quantity' => $info->quantity,
								'season' => $info->season,
								'date' => $info->date,
								'time' => $info->time,
								'fertilizer' => $info->fertilizer,
								'fertilizer_quantity' => $info->fertilizer_quantity,
								'sign_post' => $info->sign_post,
								'created_by' => $request->account
							]);

							$pairs[] = ( object ) [
								'id' => $info->id,
								'remote_id' => $id,
								'name' => $planting->name
							];

						endif;
			
					//endif;

				endforeach;

				echo json_encode( [ 
					'result' => 'success',
					'pairs' => $pairs
				], JSON_PRETTY_PRINT );

			else :

				echo json_encode( [ 
					'result' => 'error',
					'message' => 'Data missing in request'
				], JSON_PRETTY_PRINT );

			endif;
		}
		
		public function post_follow_ups() {
			global $request;
			
			if ( isset( $request->data ) && is_array( $request->data ) ) :

				$pairs = [];

				foreach( $request->data as $entry ) :

					$info = ( object ) $entry;
			
					//if ( $this->app->is_planting( $info->planting ) ) :

						$id = $this->app->add_follow_up();

						if ( $id ) :

							$follow_up = new follow_up( $id );

							$follow_up->update([
								'planting' => $info->planting,
								'field_status' => $info->field_status,
								'crop_status' => $info->crop_status,
								'notes' => $info->notes,
								'date' => $info->date,
								'time' => $info->time,
								'fertilizer' => $info->fertilizer,
								'fertilizer_quantity' => $info->fertilizer_quantity,
								'sign_post' => $info->sign_post,
								'created_by' => $request->account
							]);

							foreach( ( array ) $info->photos as $index => $photo ) :

								$extract = substr( $photo, 0, strpos( $photo, ';' ) );

								$format = basename( $extract );

								$follow_up->upload_photo( ( $index + 1 ), str_replace( 'data:image/' . $format . ';base64,', '', $photo ), $format );

							endforeach;

							$pairs[] = ( object ) [
								'id' => $info->id,
								'remote_id' => $id,
								'name' => $follow_up->name
							];

						endif;
			
					//endif;

				endforeach;

				echo json_encode( [ 
					'result' => 'success',
					'pairs' => $pairs
				], JSON_PRETTY_PRINT );

			else :

				echo json_encode( [ 
					'result' => 'error',
					'message' => 'Data missing in request'
				], JSON_PRETTY_PRINT );

			endif;
		}
		
		public function post_harvests() {
			global $request;
			
			if ( isset( $request->data ) && is_array( $request->data ) ) :

				$pairs = [];

				foreach( $request->data as $entry ) :

					$info = ( object ) $entry;
			
					//if ( $this->app->is_planting( $info->planting ) ) :

						$id = $this->app->add_harvest();

						if ( $id ) :

							$harvest = new harvest( $id );

							$harvest->update([
								'planting' => $info->planting,
								'length' => $info->length,
								'rows' => $info->rows,
								'row_to_row' => $info->row_to_row,
								'plants' => $info->plants,
								'quantity' => $info->quantity,
								'participants' => $info->participants,
								'date' => $info->date,
								'time' => $info->time,
								'created_by' => $request->account
							]);

							foreach( ( array ) $info->photos as $index => $photo ) :

								$extract = substr( $photo, 0, strpos( $photo, ';' ) );

								$format = basename( $extract );

								$harvest->upload_photo( ( $index + 1 ), str_replace( 'data:image/' . $format . ';base64,', '', $photo ), $format );

							endforeach;

							$pairs[] = ( object ) [
								'id' => $info->id,
								'remote_id' => $id,
								'name' => $harvest->name
							];

						endif;
			
					//endif;

				endforeach;

				echo json_encode( [ 
					'result' => 'success',
					'pairs' => $pairs
				], JSON_PRETTY_PRINT );

			else :

				echo json_encode( [ 
					'result' => 'error',
					'message' => 'Data missing in request'
				], JSON_PRETTY_PRINT );

			endif;
		}
		
		public function post_agro_dealers() {
			global $request;
			
			if ( isset( $request->data ) && is_array( $request->data ) ) :

				$pairs = [];

				foreach( $request->data as $entry ) :

					$info = ( object ) $entry;

					$id = $this->app->add_agro_dealer();

					if ( $id ) :

						$agro_dealer = new agro_dealer( $id );

						$agro_dealer->update([
							'village' => isset( $info->village ) ? $info->village : '',
							'lat' => isset( $info->lat ) ? $info->lat : '',
							'lng' => isset( $info->lng ) ? $info->lng : '',
							'd_fname' => isset( $info->d_fname ) ? $info->d_fname : '',
							'd_lname' => isset( $info->d_lname ) ? $info->d_lname : '',
							'd_phone1' => isset( $info->d_phone1 ) ? $info->d_phone1 : '',
							'd_phone2' => isset( $info->d_phone2 ) ? $info->d_phone2 : '',
							'p_fname' => isset( $info->p_fname ) ? $info->p_fname : '',
							'p_lname' => isset( $info->p_lname ) ? $info->p_lname : '',
							'p_phone1' => isset( $info->p_phone1 ) ? $info->p_phone1 : '',
							'p_phone2' => isset( $info->p_phone2 ) ? $info->p_phone2 : '',
							'business' => isset( $info->business ) ? $info->business : '',
							'store' => isset( $info->store ) ? $info->store : '',
							'volume' => isset( $info->volume ) ? $info->volume : '',
							'remarks' => isset( $info->remarks ) ? $info->remarks : '',
							'date' => isset( $info->date ) ? $info->date : '',
							'time' => isset( $info->time ) ? $info->time : '',
							'created_by' => $request->account
						]);

						$avatar = $info->d_avatar;

						$extract = substr( $avatar, 0, strpos( $avatar, ';' ) );

						$format = basename( $extract );

						$agro_dealer->upload_avatar( str_replace( 'data:image/' . $format . ';base64,', '', $avatar ), $format );

						foreach( ( array ) $info->photos as $index => $photo ) :

							$extract = substr( $photo, 0, strpos( $photo, ';' ) );

							$format = basename( $extract );

							$agro_dealer->upload_store_image( ( $index + 1 ), str_replace( 'data:image/' . $format . ';base64,', '', $photo ), $format );

						endforeach;

						$pairs[] = ( object ) [
							'id' => $info->id,
							'remote_id' => $id
						];

					endif;

				endforeach;

				echo json_encode( [ 
					'result' => 'success',
					'pairs' => $pairs
				], JSON_PRETTY_PRINT );

			else :

				echo json_encode( [ 
					'result' => 'error',
					'message' => 'Data missing in request'
				], JSON_PRETTY_PRINT );

			endif;
		}
		
		public function post_statistics() {
			global $request;
			
			if ( isset( $request->data ) && is_array( $request->data ) ) :

				if ( $this->app->is_ppo( $request->account ) ) :
			
					$info = ( object ) $request->data;

					$ppo = new ppo( $request->account );

					update_user_meta( $ppo->id, 'local_villages_count', $info->villages );
					update_user_meta( $ppo->id, 'local_farmers_count', $info->farmers );
					update_user_meta( $ppo->id, 'local_fields_count', $info->fields );
					update_user_meta( $ppo->id, 'local_plantings_count', $info->plantings );
					update_user_meta( $ppo->id, 'local_follow_ups_count', $info->follow_ups );
					update_user_meta( $ppo->id, 'local_harvests_count', $info->harvests );
			
					$date = date( 'd-m-Y H:i:s' );
			
					update_user_meta( $ppo->id, 'last_successful_synchronization_date', $date );

					$pairs = ( object ) [
						'remote_villages_count' => count( $ppo->get_villages() ),
						'remote_farmers_count' => count( $ppo->get_farmers() ),
						'remote_fields_count' => count( $ppo->get_fields() ),
						'remote_plantings_count' => count( $ppo->get_plantings() ),
						'remote_follow_ups_count' => count( $ppo->get_follow_ups() ),
						'remote_harvests_count' => count( $ppo->get_harvests() ),
						'last_successful_synchronization_date' => $date
					];

				endif;

				echo json_encode( [ 
					'result' => 'success',
					'pairs' => $pairs
				], JSON_PRETTY_PRINT );

			else :

				echo json_encode( [ 
					'result' => 'error',
					'message' => 'Data missing in request'
				], JSON_PRETTY_PRINT );

			endif;
		}
		
	}

    $api = new api();

?>