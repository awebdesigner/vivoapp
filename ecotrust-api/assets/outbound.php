<?php

	header( 'Access-Control-Allow-Origin: *' ); 
	header( 'Content-type: application/json' );

	
	require_once '../../../../wp-load.php';


	$request = ( object ) $_REQUEST;

	$method = isset( $request->method ) ? $request->method : '';

	$account = isset( $request->account ) ? $request->account : '';

	$token = isset( $request->token ) ? $request->token : '';

	$exclude = isset( $request->exclude ) ? ( array ) $request->exclude : [];

	$page = isset( $request->page ) ? ( int ) $request->page : 1;

	$per_page = isset( $request->per_page ) ? ( int ) $request->per_page : 25;

	$query = isset( $request->query ) ? ( string ) $request->query : '';


	if ( !empty( $method ) ) :

		if ( !empty( $token ) ) : 

			if ( $app->is_token( $token ) ) : 

				if ( !empty( $account ) || in_array( $method, $app->open_api_methods() ) ) : 

					if ( $app->is_ppo( $account ) || in_array( $method, $app->open_api_methods() )  ) : 

						if ( $app->api_auth( $account, $token ) || in_array( $method, $app->open_api_methods() )  ) :
							
							switch( $method ) :

								case 'get_translations' :

									$api->return_translations();

									break;

								case 'get_countries' :

									$api->return_countries();

									break;

								case 'get_seasons' :

									$api->return_seasons();

									break;

								case 'get_districts' :

									$api->return_districts();

									break;

								case 'get_villages' :

									$api->return_villages();

									break;

								case 'get_crop_types' :
				
									$api->return_crop_types();

									break;

								case 'get_varieties' :
				
									$api->return_varieties();

									break;

								case 'get_fertilizers' :
				
									$api->return_fertilizers();

									break;

								case 'get_ppos' :

									$api->return_ppos();

									break;
							
								case 'get_farmers' :

									$api->return_farmers();

									break;

								case 'get_fields' :
							
									$api->return_fields();

									break;									

								case 'get_plantings' :

									$api->return_plantings();

									break;

								case 'get_follow_ups' :

									$api->return_follow_ups();

									break;

								case 'get_harvests' :

									$api->return_harvests();

									break;

								case 'get_agro_dealers' :

									$api->return_agro_dealers();

									break;

								default :
							
									echo json_encode( [ 
										'result' => 'error',
										'message' => $app->get_locale_text( 'invalid-method' )
									], JSON_PRETTY_PRINT );

									break;
							
							endswitch;

						else :

							echo json_encode( [ 
								'result' => 'error',
								'message' => $app->get_locale_text( 'account-and-token-do-not-match' )
							], JSON_PRETTY_PRINT );

						endif;

					else :

						echo json_encode( [ 
							'result' => 'error',
							'message' => $app->get_locale_text( 'invalid-account' )
						], JSON_PRETTY_PRINT );

					endif;

				else :

					echo json_encode( [ 
						'result' => 'error',
						'message' => $app->get_locale_text( 'account-missing-in-the-request' )
					], JSON_PRETTY_PRINT );

				endif;

			else :

				echo json_encode( [ 
					'result' => 'error',
					'message' => $app->get_locale_text( 'invalid-token' )
				], JSON_PRETTY_PRINT );

			endif;

		else :

			echo json_encode( [ 
				'result' => 'error',
				'message' => $app->get_locale_text( 'token-missing-in-the-request' )
			], JSON_PRETTY_PRINT );

		endif;

	else :

		echo json_encode( [ 
			'result' => 'error',
			'message' => $app->get_locale_text( 'method-missing-in-the-request' )
		], JSON_PRETTY_PRINT );

	endif;

?>