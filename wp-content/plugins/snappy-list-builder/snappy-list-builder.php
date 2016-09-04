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
 		2. SHORTCODES
 			2.1 - slb_register_shortcodes()
 			2.2 - slb_form_shortcode()
 		3. FILTERS
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


/* !2. SHORTCODES */

// 2.1 
function slb_register_shortcodes() {

	add_shortcode( 'slb_form', 'slb_form_shortcode' );

}

// 2.2
function slb_form_shortcode( $args, $content="" ) {

	// Setup our output variable - the format html
	$output = '
		
		<div class="slb">
			<form id="slb_form" name="slb_form" class="slb-form" method="post">
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