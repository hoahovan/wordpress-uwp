<?php

/*
 * Plugin Name: Snappy List Builder
 * Plugin URI: http://wordpressplugincourse/plugins/snappy-list-builder
 * Description: The ultimate email list building plugin for Wordpress. 
 	 Capture new subscribers. Reward subscriber with a custom download upon opt-in. Build unlimited lists. 
 	 Import and export subscribers easily with .csv
 * Version: 1.0
 * Author: Joel Funk @ Code College
 * Author URI: http://joelfunk.codecollege.ca
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text domain: snappy-list-builder
  
*/

/* !0. TABLE OF CONTENTS */

/*
 		1. HOOKS
 			1.1 - Registers all our custom shortcodes
 			1.2 - Register custom admin column headers
 			1.3 - Register custom admin column data
 		2. SHORTCODES
 			2.1 - slb_register_shortcodes()
 			2.2 - slb_form_shortcode()
 		3. FILTERS
 			3.1 - slb_subscriber_column_headers()
 			3.2 - slb_subscriber_column_data()
 			3.3 - slb_list_column_headers()
 			3.4 - slb_list_column_data()
 		4. EXTERNAL SCRIPTS
 		5. ACTIONS
 		6. HELPERS
 		7. CUSTOM POST TYPES
 		8. ADMIN PAGES
 		9. SETTINGS

 */

/* !1. HOOKS */

// 1.1
// Hint: Registers all our custom shortcodes on init
add_action( 'init', 'slb_register_shortcodes');

// 1.2
// Hint: Register custom admin column headers
add_filter( 'manage_edit-slb_subscriber_columns', 'slb_subscriber_column_headers' );
add_filter( 'manage_edit-slb_list_columns', 'slb_list_column_headers' );

// 1.3
// Hint: Register custom admin column data
add_filter( 'manage_slb_subscriber_posts_custom_column', 'slb_subscriber_column_data', 1, 2 );
add_filter( 'manage_slb_list_posts_custom_column', 'slb_list_column_data', 1, 2 );


/* !2. SHORTCODES */

// 2.1 
function slb_register_shortcodes() {

	add_shortcode( 'slb_form', 'slb_form_shortcode' );

}

// 2.2
function slb_form_shortcode( $args, $content="" ) {

	// Get the list id
	$list_id = 0;
	if ( isset($args['id']) ) {
		$list_id = (int) $args['id'];
	}

	// Setup our output variable - the format html
	$output = '
		
		<div class="slb">
			<form id="slb_form" name="slb_form" class="slb-form" method="post"
			action="/wp-admin/admin-ajax.php?action=slb_save_subscription">
				<input type="hidden" name="slb_list" value="' . $list_id . '">
				<p class="slb-input-container">
					<label>Your name</label> <br>
					<input type="text" name="slb_fname" value="" placeholder="First name" />
					<input type="text" name="slb_lname" value="" placeholder="Last name" />
				</p>

				<p class="slb-input-container">
					<label>Your Email</label> <br>
					<input type="email" name="slb_email" value="" placeholder="ex. you@email.com" />
				</p>';

				// Including content in our form html if content is passed into the function
				if( strlen($content) ) {
					$output .= '<div class="slb-content">' . wpautop($content) . '</div>';
				}

				// Completing our form html
				$output .= '<p class="slb-input-container">
					<input type="submit" name="slb_submit" value="Sign Me Up" />
				</p>

			</form>
		</div>

	';

	// Return our result
	return $output;

}

/* !3. FILTERS */

// 3.1
function slb_subscriber_column_headers( $columns ) {

	// Creating custom column header data
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'name' => __('Subscriber Name'),
		'email' => __('Email Address')
		);

	// Returning new columns
	return $columns;

}


// 3.2
function slb_subscriber_column_data( $column, $post_id ) {

	// Setup our return text 
	$output = '';

	switch( $column ) {
		case 'name':
			// Get the custom name data
			$fname = get_field('slb_fname', $post_id);
			$lname = get_field('slb_lname', $post_id);
			$output .= $fname . ' ' . $lname;
			break;
		case 'email':
			// Get the custom email data
			$email = get_field('slb_email', $post_id);
			$output .= $email;
			break;
	}

	// Return the output
	echo $output;

}


// 3.3 
function slb_list_column_headers( $columns ) {

	// Creating custom column header data
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __('List Name')		
		);

	// Returning new columns
	return $columns;

}

// 3.4
function slb_list_column_data( $column, $post_id ) {

	// Setup our return text 
	$output = '';

	// switch( $column ) {
	// 	case 'custom':
	// 		break;
	// }

	// Return the output
	echo $output;

}


/* !4. EXTERNAL SCRIPTS */



/* !5. ACTIONS	*/

// 5.1
// Hint: Saves subscription data to an existing or new subscriber
function slb_save_subscription() {

	// Setup default result data
	$result = array(
		'status' => 0,
		'message' => 'Subscription was not save'
		);

	// Array for storing errors

	try {

		// Get list_id
		$list_id = (int) $_POST['slb_list'];

		// Prepare subscriber data
		$subscriber_data = array(
			'fname' => esc_attr( $_POST['slb_fname'] ),
			'lname' => esc_attr( $_POST['slb_lname'] ),
			'email' => esc_attr( $_POST['slb_email'] )
			);

		// Attempt to create/save subscriber
		$subscriber_id = slb_save_subscriber( $subscriber_data );

		// If subscriber was save successfully $subscriber_id will be greater than 0
		if( $subscriber_id ) {

			// If subscriber already has this subscription
			if( slb_subscriber_has_subscription( $subscriber_id, $list_id ) ) {
				
				// Get list object
				$list = get_post( $list_id );

				// Return detailed error
				$result['message'] .= esc_attr( $subscriber_data['email'] );
				$result['message'] .= ' is already subscribed to ' . $list->post_title . '.';

			}

			else {
				// Save new subscription
				$subscription_saved = slb_add_subscription( $subscriber_id, $list_id );

				// If subscription was saved successfully
				if( $subscription_saved ) {

					// Subscription saved!
					$result['status'] = 1;
					$result['message'] = 'Subscription saved';

				}

			}

		}

	} catch( Exception $e ) {
		// A PHP error occurred
		$result['message'] = 'Caught exception: ' . $e->getMessage();
	}

	// Return result as json
	slb_return_json( $result );

}

// 5.2
// Hint: Create a new subscriber or updatea and existing one
function slb_save_subscriber( $subscriber_data ) {

	// Setup default subscriber id
	// 0 mean the subscriber was not saved
	$subscriber_id = 0;


	try {

		$subscriber_id = slb_get_subscriber_id( $subscriber_data['email'] );

		// If the subscriber does not already exists...
		if( !$subscriber_id ) {

			// Add new subscriber to database
			$subscriber_id = wp_insert_post(
				array(
						'post_type' => 'slb_subscriber',
						'post_title' => $subscriber_data['fname'] . " " . $subscriber_data['lname'],
						'post_status' => 'publish',
					),
					true
				);
		}

		// Add/update custom meta data
		update_field( slb_get_acf_key['slb_fname'], $subscriber_data['fname'], $subscriber_id );
		update_field( slb_get_acf_key['slb_lname'], $subscriber_data['lname'], $subscriber_id );
		update_field( slb_get_acf_key['slb_email'], $subscriber_data['email'], $subscriber_id);

	} catch ( Exception $e ) {
		// A PHP error occurred

	}

	// Return subscriber_id
	return $subscriber_id;

}


/* !6. HELPERS */

// 6.1
// Hint: returns true or false
function slb_subscriber_has_subscription( $subscriber_id, $list_id ) {

	// Set default return value
	$has_subscription = false;

	// Get subscriber
	$subscriber = get_post( $subscriber_id );

	// Get subscriptions
	$subscriptions = slb_get_subscriptions( $subscriber_id );

	// Check subscriptions for $list_id
	if ( in_array($list_id, $subscriptions) ) {

		// Found the $list_id in $subscriptions
		// this subscriber is already subscribed to this list
		$has_subscription = true;

	} else {

		// Did not find $list_id in 

	}

}

// 6.2
// Hint: retrieves a subscriber_id from an email address
function slb_get_subscriber_id( $email ) {

	$subscriber_id = 0;

	try {

		// Check if subscriber already exists
		$subscriber_query = new WP_Query(
			array(
					'post_type' => 'slb_subscriber',
					'posts_per_page' => 1,
					'meta_key' => 'slb_email',
					'meta_query' => array(
						array(
							'key' => 'slb_email',
							'value' => $email, // or whatever it is you're using here
							'compare' => '='
							),
						),
				)
			);

		// If the subscriber exists...
		if( $subscriber_query->have_posts() ) {

			// Get the subscriber_id
			$subscriber_query->the_post();
			$subscriber_id = get_the_ID();

		}

	} catch( Exception $e ) {
		// A PHP error occurred
	}

	// Reset the Wordpress post object
	wp_reset_query();

	return (int)$subscriber_id;

}















