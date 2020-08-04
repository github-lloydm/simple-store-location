<?php 
/*
Plugin Name: LAM Store Locator
Plugin URI: http://lam-portfolio.000webhostapp.com/
Description: This plugin will help you Create your Store locations information, You can add as many as you have.

Version: 1.1.0
Author: Lloyd A. Mangin
Author URI: http://lam-portfolio.000webhostapp.com/
License: GPL2
*/

require_once( ABSPATH . "wp-includes/pluggable.php" );

if ( ! defined( 'WPINC' ) )  die; 
if ( ! defined( 'ABSPATH' ) ){ exit;  }



include 'admin_settings.php';



/*
-------------------------------------------------------------------------------------------------------------------------
-
*** This Function will add style in the wp_head
-
-------------------------------------------------------------------------------------------------------------------------
*/


function sl_style(){
	$option  = get_option( 'my_option_name' );
	$api_    = $option['gmap_api'];

	$api_key = (!empty($api_))? $api_:'AIzaSyANV_Rz5rjLNGfbhIHipuoSMvtEW7311GA';
	
	if( is_page('44') || is_page('48')){	
		wp_enqueue_style('sl-style-id', plugin_dir_url(__FILE__).'css/sl_style.css', array(), '1.0', 'all');
	    wp_enqueue_style('sl-style-id');

	  	echo '<script src="https://maps.googleapis.com/maps/api/js?libraries=places&key='.$api_key.'"></script>';     
	}  	
}


add_action('wp_enqueue_scripts', 'sl_style');

/*
-------------------------------------------------------------------------------------------------------------------------
-
*** This part here will include the store-location-posttype. this program will create a custom post type name store location.
-
-------------------------------------------------------------------------------------------------------------------------
*/

include 'store-location-posttype.php';


/*
-------------------------------------------------------------------------------------------------------------------------
-
*** This function will make a shortcode for the list of address/Location available.
-
-------------------------------------------------------------------------------------------------------------------------
*/

add_shortcode('Store-location-list', function($atts){

    $query = new WP_Query( array('post_type' => 'lam-store-locations', 'posts_per_page' => '-1', 'order' => 'ASC') );

    $atts = extract(shortcode_atts( array(
		'list_class' => 'list-class', 
		'template'	 => null
	), $atts));

    $templates = array('template1', 'template2','template3');

    if( $query->have_posts() ):

        $ctr = 1;

        //$html .= '<pre>'. print_r( $template,true) .'</pre>';
    	
        if( in_array($template, $templates) ):
        	
        	if( $template == "template1" ):

        		$html .= '<ol id="template1">';

        	elseif( $template == "template2" ):

        		$html .= '<div id="template2">';

        	elseif( $template == "template3" ):

        		$html .= '<div id="template3">';

        	endif;
        	
		        while( $query->have_posts() ): $query->the_post();
		        	$id = get_the_ID();
		        	$address = get_post_meta(get_the_ID(), 'store_locator_address', true);
		        	$tel = get_post_meta(get_the_ID(), 'store_locator_tell_number', true);

		        	if(	$template == "template1"):
			        	$html .= "<li><a href='javascript:google.maps.event.trigger(gmarkers[$id],\"click\");'>".get_the_title()."</a></li>";
			        
			        elseif( $template == "template2"):
			        	$html .= '
			        				<div class="template-2">
						                
						                <a class="template-2-title" href="javascript:google.maps.event.trigger(gmarkers['.$id.'],\'click\');">'.get_the_title().'</a>
						                
						                <div class="loc-content">
						                	<div class="temp2-address">
						                		<span class="gmarker_icon"><img width="30" height="30" src="'.plugin_dir_url(__FILE__).'images/gmarker-black.png" /></span>
						                		&nbsp;
						                		'.str_replace(',', ',<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $address).'
						                	</div> 
						                	<p><span class="tel_icon"><img width="30" height="30" src="'.plugin_dir_url(__FILE__).'images/telephone.png" /></span>&nbsp;&nbsp;'.$tel.'</p>
						                </div>
						            </div>
			        			 ';

			        elseif( $template == "template3"):
			        	$html .= '<div class="template3_box" style="padding: 13px 0px 20px;">
			        				<p class="store-loc">
			        					<a class="template-2-title" href="javascript:google.maps.event.trigger(gmarkers['.$id.'],\'click\');">'.get_the_title().'</a>
			        				</p>
				                    <p><span class="tel_icon"><img width="30" height="30" src="'.plugin_dir_url(__FILE__).'images/telephone.png" /></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$tel.'</p>
			        			  </div>';
			        	

			        else:
			        	$html .= 'not found..';
			        	break;
		        	endif;

		        $ctr++;	
		        endwhile;

		        wp_reset_postdata();

		    //ENDING TAGS
		    if( $template == "template1" ):

        		$html .= '</ol>  <!-- TEMPLATE1 ENDING TAG -->';

        	elseif( $template == "template2" ):
        		
        		$html .= '<div class="clr_break">&nbsp;</div></div>  <!-- TEMPLATE2 ENDING TAG -->';

        	elseif( $template == "template3" ):

        		$html .= '</div>  <!-- TEMPLATE3 ENDING TAG -->';

        	endif;


        else:
        	echo '<ol class="store_locator_listing">';
	        	while( $query->have_posts() ): $query->the_post();
			        $id = get_the_ID();
	        		$html .= "<li><a href='javascript:google.maps.event.trigger(gmarkers[$id],\"click\");'>".get_the_title()."</a></li>";

	        	endwhile;	

        		wp_reset_postdata();
        	echo '</ol>';

        endif;
    		
    	
    	

    else:
        $html .= '<li>No Store Locations found..</li>';
    endif;
    
    return $html;
       
});


add_shortcode('Store-location-map', function($atts){
	$atts = extract(shortcode_atts( array(
		'mapWidth'  => '100%',
		'mapHeight' => '500px'
	), $atts));



	return '<div id="map" style="width:'.$mapWidth.'; height:'.$mapHeight.'; " ></div>';
});


/*
-------------------------------------------------------------------------------------------------------------------------
-
*** This function will query all the available address/location from lam-store-locator posttype
-
-------------------------------------------------------------------------------------------------------------------------
*/

function get_location_js(){
	$query = new WP_Query( array('post_type' => 'lam-store-locations', 'posts_per_page' => '-1', 'order' => 'ASC') );

	if( $query->have_posts() ):
		$ctr = 1;
		while( $query->have_posts() ): $query->the_post();
			$id 	 = get_the_ID();
			$title   = get_the_title();
			$lat 	 = get_post_meta(get_the_ID(), 'store_locator_latitude');
			$lng     = get_post_meta(get_the_ID(), 'store_locator_longitude');
			$content = get_post_meta(get_the_ID(), 'store_locator_address', true);
			$tel     = get_post_meta(get_the_ID(), 'store_locator_tell_number', true);

			$locations[$ctr] = array('id' => "$id" , 'title' => "$title", 'content' => trim($content), 'telno' => $tel ,'lat' => "$lat[0]", 'lng' => "$lng[0]");
			$ctr++;
		endwhile; 
		
		wp_reset_postdata();

		return $locations;

	endif;
}

/*
-------------------------------------------------------------------------------------------------------------------------
-
*** This function will extract the location from get_location_js
-
-------------------------------------------------------------------------------------------------------------------------
*/

function Extract_location($array_data){
	if( is_array($array_data) ){
		$ctr = 1;
		foreach($array_data as $data){
			$comma = ($ctr - count($array_data))? ', ':'';

			echo '  [ 
					  "'.$data['id'].'", 
					  "'.$data['title'].'", 
					  "'.$data['content'].'", 
					  "'.$data['telno'].'",
					  "'.$data['lat'].'", 
					  "'.$data['lng'].'" 
					]'.$comma;
		$ctr++;	
		}

	}else{
		return 'Invalid Parameter. Not an Array.';
	}
}


/*
-------------------------------------------------------------------------------------------------------------------------
-
*** This function will insert an script in the wp_footer 
-
-------------------------------------------------------------------------------------------------------------------------
*/
//if( is_page(44) || is_page(48)){

		add_action('wp_footer', function(){
			$Address = get_location_js();

			$option  = get_option( 'my_option_name' );

			$gmaker_ = $option['gmap_marker']; 
			$gcenter = explode('|', $option['gmap_center_store']);	   

			$maptype = $option['gmap_type'];
			$gzoom   = $option['gmap_zoom'];
			//$sl_pages = $option['sl_pages'];	

			if( is_page(44) || is_page(48)):
		?>
				<script type="text/javascript">

						var locations = [<?php Extract_location($Address); ?>]; //<!--END OF SQUARE BRACKET-->

							<?php
								$image = (!empty($gmaker_))? $gmaker_ : plugin_dir_url( __FILE__ ) .'images/red-circle.png';
							?>
								gmarkers = [];


							     var image = {
			                                url: "<?php echo $image; ?>",
			                                scaledSize: new google.maps.Size(100, 100),
			                                origin: new google.maps.Point(0, 0)
			                            }

							    var map = new google.maps.Map(document.getElementById('map'), {
														        zoom: <?php echo $gzoom; ?>,
														        center: new google.maps.LatLng(<?php echo $gcenter[0]; ?>),
														        mapTypeId: google.maps.MapTypeId.<?php echo trim($maptype); ?>
							    							});

							    var infowindow = new google.maps.InfoWindow();


							    function createMarker(latlng, html) {
							        var marker = new google.maps.Marker({
							            position: latlng,
							            map: map,
							            icon: image
							        });

							        google.maps.event.addListener(marker, 'click', function() {
							            map.setCenter(marker.getPosition());
							            infowindow.setContent("<div id='infowindow'>"+ html +"</div>");
							            infowindow.open(map, marker);
							        });
							        return marker;
							    }
							    
							    for (var i = 0; i < locations.length; i++) {
							        gmarkers[locations[i][0]] =
							        createMarker(new google.maps.LatLng(locations[i][4], locations[i][5]), "<h4 style='margin:0;padding:0;'>"+locations[i][1]+"</h4> <br>"+locations[i][2]+"<br><br>"+locations[i][3]);

							    }
				</script>	    
		<?php
			endif;
		}); //END OF WP_FOOTER

//}//END OF CONDITION


 
 