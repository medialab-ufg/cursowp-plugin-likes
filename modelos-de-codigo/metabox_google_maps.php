<?php
/**
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 */ 


/**
 * Para imprimir o mapa no post se estiver num loop: <?php _SLUG_MetaMapa::printMap() ?>
 * fora do loop: <?php _SLUG_MetaMapa::printMap($post_id) ?>
 */


// SUBSTITUA  _SLUG_ pelo slug do metabox

class _SLUG_MetaMapa {
    const meta_key = '_SLUG_';
    
    protected static $metabox_config = array(
        '_SLUG_', // slug do metabox
        'Título do metabox _SLUG_', // título do metabox
        array('post'), // post types
        'normal' // onde colocar o metabox
    );

    static function init() {
        add_action('add_meta_boxes', array(__CLASS__, 'addMetaBox'));
        add_action('save_post', array(__CLASS__, 'savePost'));
    }

    static function addMetaBox() {
        wp_enqueue_script('google-maps-v3', 'http://maps.google.com/maps/api/js?sensor=false');
        foreach(self::$metabox_config[2] as $post_type)
            add_meta_box(
                self::$metabox_config[0],
                self::$metabox_config[1],
                array(__CLASS__, 'metabox'), 
                $post_type,
                self::$metabox_config[3]

            );
    }
    
    
    static function filterValue($meta_key, $value){
        global $post;
    
        switch ($meta_key){
            case 'outro_dado':
                return strtoupper($value);
            break;
        
            default:
                return $value;
            break;
        }
        
    }
    
    static function metabox(){
        global $post;
        
        wp_nonce_field( 'save_'.__CLASS__, __CLASS__.'_noncename' );
        
        if( !$location=get_post_meta($post->ID, self::meta_key, true) ) {
            
            $location = array(
                'lat'=>'-23.56367', 
                'lon'=>'-46.65372', 
                'zoom' => 14,
                'maptype' => 'roadmap',
                'show-pin' => true
            );
        }
        ?>
        <script type="text/javascript">
        (function($){
            $(function(){
               google.maps.event.addDomListener(window, 'load', function(){
                    var prefix = "<?php echo self::meta_key ?>";
                    var canvas_id = prefix + "_canvas";
                    /**
                    *
                    * Handle functionalities to GoogleMaps API version 3
                    *
                    */
                   
                   var map_options = {
                       'zoom': parseInt($('#' + prefix + '_zoom').val()),
                       'scrollwheel': false,
                       'draggableCursor': 'default',
                       'center': new google.maps.LatLng(parseFloat($('#' + prefix + '_lat').val()), parseFloat($('#' + prefix + '_lon').val())),
                       'mapTypeId': $('#' + prefix + '_maptype').val()
                   };
                   
                   var googlemap = new google.maps.Map(document.getElementById(canvas_id), map_options);
                   var googlemarker = null;
                   rafa = googlemap;
                   var fill_fields = function (lat, lng) {
                       $("#" + prefix + "_lat").val(lat);
                       $("#" + prefix + "_lon").val(lng);
                   };

                    // place a marker on the map
                    var load_post_marker = function (lat, lng) {
                        try{
                            lat = parseFloat(lat);
                            lng = parseFloat(lng);
                            if(lat && lng) {
                                if(googlemarker) {
                                    googlemarker.setPosition(new google.maps.LatLng(lat, lng));
                                }else{
                                    fill_fields(lat, lng);
                                    googlemarker = new google.maps.Marker({
                                        map: googlemap,
                                        draggable: true,
                                        position: new google.maps.LatLng(lat, lng)
                                    });
                                    googlemap.panTo(googlemarker.getPosition());
                                }
                            }
                            return googlemarker;
                        } catch(e) {  }
                        return null;
                    };
                    
                    load_post_marker(parseFloat($('#' + prefix + '_lat').val()), parseFloat($('#' + prefix + '_lon').val()));

                    // plot marker on saved location and define map drag event
                    if(load_post_marker($("#" + prefix + "_lat").val(), $("#" + prefix + "_lon").val())) {
                        google.maps.event.addListener(googlemarker, 'drag', function(e) {
                            fill_fields(e.latLng.lat(), e.latLng.lng());
                        });
                    }

                    // define map click event
                    var clicklistener = google.maps.event.addListener(googlemap, 'click', function(event) {
                        place_marker(event.latLng);
                        
                    });

                    google.maps.event.addListener(googlemap, 'zoom_changed', function(event) {
                        $('#' + prefix + '_zoom').val(googlemap.getZoom()); 
                    });
                    
                    google.maps.event.addListener(googlemap, 'maptypeid_changed', function(event) {
                        $('#' + prefix + '_maptype').val(googlemap.getMapTypeId());
                    });
                    
                    // callback for map click event
                    var place_marker = function(location) {
                        
                        if(googlemarker === null) {
                            load_post_marker(location.lat(), location.lng());
                        } else {
                            googlemarker.setPosition(location);
                            fill_fields(location.lat(), location.lng());
                            google.maps.event.addListener(googlemarker, 'drag', function(e) {fill_fields(e.latLng.lat(), e.latLng.lng());});
                        }
                        if(clicklistener){
                            google.maps.event.removeListener(clicklistener);
                        }
                        google.maps.event.addListener(googlemap, 'click', function(e) {
                            googlemarker.setPosition(e.latLng);
                            fill_fields(e.latLng.lat(), e.latLng.lng());
                        });
                    };
                    
                    // activate google service
                    var geocoder = new google.maps.Geocoder();

                    // callback to handle google geolocation result
                    function geocode_callback(results, status) {
                        if (status === google.maps.GeocoderStatus.OK) {
                            var location = results[0].geometry.location;
                            googlemap.setCenter(location);
                            fill_fields(location.lat(), location.lng());

                            if(googlemarker) {
                                
                                googlemarker.setPosition(location);
                                googlemarker.setPosition(location);
                            } else {
                                googlemarker = new google.maps.Marker({
                                    map: googlemap,
                                    draggable: true,
                                    position: location
                                });
                            }
                        }
                        googlemap.setZoom(14);
                    }
                    // the search bar, where user can type an address
                    $("#" + prefix + "_search_address").keypress(function(e){
                        if(e.charCode===13 || e.keyCode===13){ // carriage return
                            geocoder.geocode({'address': $(this).val()}, geocode_callback);
                            return false;
                        }
                    });

                    // the button to place marker on specified coords
                    $("#" + prefix + "_load_coords").click(function(){load_post_marker($("#" + prefix + "_lat").val(), $("#" + prefix + "_lon").val())});

                    $("#" + prefix + "_lat,#" + prefix + "_lon").keypress(function(e) {
                        if(e.charCode===13 || e.keyCode===13){ // carriage return
                            $("#" + prefix + "_load_coords").click();
                            return false;
                        }
                    });

                    // let the user resize map
                    $("#" + prefix + "_canvas").resizable({ handles: 's'});

               });
            });
        })(jQuery);
        </script>
        <p>
            Configure o mada da maneira que vocês deseja que eles apareça.
        </p>
        <fieldset>
            <label for="<?php echo self::meta_key; ?>_search_address"><?php _e('Search address', 'mpv');?>:</label>
            <input type="text" id="<?php echo self::meta_key; ?>_search_address" class="large-field" style="width:100%"/>
        </fieldset><br/>
        
        <fieldset> 
            <input type="hidden" name="<?php echo self::meta_key; ?>[zoom]" id="<?php echo self::meta_key; ?>_zoom" value="<?php echo $location['zoom'];?>"/>
            <input type="hidden" name="<?php echo self::meta_key; ?>[maptype]" id="<?php echo self::meta_key; ?>_maptype" value="<?php echo $location['maptype'];?>"/>
            
            <label for="<?php echo self::meta_key; ?>_lat">Latitude:</label>
            <input type="text" class="medium-field" name="<?php echo self::meta_key; ?>[lat]" id="<?php echo self::meta_key; ?>_lat" value="<?php echo $location['lat'];?>"/>

            <label for="<?php echo self::meta_key; ?>_lon">Longitude:</label>
            <input type="text" class="medium-field" name="<?php echo self::meta_key; ?>[lon]" id="<?php echo self::meta_key; ?>_lon" value="<?php echo $location['lon'];?>"/>
            <input type="button" id="<?php echo self::meta_key; ?>_load_coords" value="Exibir"/>
            
            
            <label style="float:right">
                <input type="checkbox" name="<?php echo self::meta_key; ?>[show-pin]" id="<?php echo self::meta_key; ?>_show_pin" value="1" <?php if($location['show-pin']) echo 'checked="checked"';?>/>
                Mostrar o marcador
            </label>
        </fieldset>
        <div id="<?php echo self::meta_key; ?>_canvas" class="<?php echo self::meta_key; ?>_canvas" style="margin-top:10px; height:400px; width:100%;"></div>

        <?php
    }

    static function savePost($post_id) {
        // verify if this is an auto save routine. 
        // If it is our form has not been submitted, so we dont want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times

        if (!wp_verify_nonce($_POST[__CLASS__.'_noncename'], 'save_'.__CLASS__))
            return;

        $_POST[self::meta_key]['show-pin'] = isset($_POST[self::meta_key]['show-pin']) ? true : false;
        
        // OK, we're authenticated: we need to find and save the data
        if(isset($_POST[self::meta_key])){
            update_post_meta($post_id, self::meta_key, self::filterValue(self::meta_key, $_POST[self::meta_key]));
        }
    }

    static function printMap($post_id = null , $height = '450px', $width = '100%'){
        $post_id = $post_id ? $post_id : get_the_ID();
        if( !$location=get_post_meta($post_id, self::meta_key, true) ) {
            
            $location = array(
                'lat'=>'-23.56367', 
                'lon'=>'-46.65372', 
                'zoom' => 14,
                'maptype' => 'roadmap',
                'show-pin' => false
            );
        }
        $map_id = uniqid();
        ?>
        <div id="<?php echo $map_id?>" class='hl-metamapa' style="width:<?php echo $width; ?>; height:<?php echo $height?>" data-show-pin='<?php echo $location['show-pin']?>' data-lat='<?php echo $location['lat']?>' data-lon='<?php echo $location['lon']?>' data-zoom='<?php echo $location['zoom']?>' data-maptype='<?php echo $location['maptype']?>'></div>
        <?php 
    }
    
}


_SLUG_MetaMapa::init();
