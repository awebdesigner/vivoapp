<?php

	header( 'Access-Control-Allow-Origin: *' ); 
	header( 'Content-type: application/json' );

	require_once '../../../../wp-load.php';


	$request = ( object ) $_REQUEST;


	$method = isset( $request->method ) ? $request->method : '';

	$account = isset( $request->account ) ? $request->account : '';

	$token = isset( $request->token ) ? $request->token : '';

	$ppo = isset( $request->ppo ) ? $request->ppo : '';

	$data = isset( $request->data ) ? ( array ) $request->data : [];


	if ( !empty( $method ) ) :

		if ( !empty( $token ) || in_array( $method, $app->open_api_methods() )  ) : 

			if ( $app->is_token( $token ) || in_array( $method, $app->open_api_methods() )  ) : 

				if ( !empty( $account ) || in_array( $method, $app->open_api_methods() ) ) : 

					if ( $app->is_ppo( $account ) || in_array( $method, $app->open_api_methods() )  ) : 

						if ( $app->api_auth( $account, $token ) || in_array( $method, $app->open_api_methods() )  ) :
							
							switch( $method ) :

								case 'register_installation' :									

									$api->register_installation();

									break;

								case 'map_installation' :									

									$api->map_installation();									

									break;

								case 'update_installation' :									

									$api->update_installation();
									
									break;

								case 'post_villages' :

									$api->post_villages();

									break;

								case 'post_farmers' :
									
									$api->post_farmers();
									
									break;

								case 'post_fields' :
									
									$api->post_fields();
									
									break;

								case 'post_plantings' :

									$api->post_plantings();

									break;

								case 'post_follow_ups' :

									$api->post_follow_ups();

									break;

								case 'post_harvests' :

									$api->post_harvests();

									break;

								case 'post_agro_dealers' :

									$api->post_agro_dealers();

									break;
							
								case 'post_statistics' :

									$api->post_statistics();

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