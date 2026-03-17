<?php



//Delete link button (called by ajax)
function aalDeleteLink(){
	
			if ( ! current_user_can( 'publish_pages' ) ) {
				wp_die();
			}
	
	
	
		  if ( ! wp_verify_nonce( $_POST['aal_nonce'], 'aal-ajax-nonce' ) ) {
         die ( 'no privileges');
     }
    
            if(isset($_POST['id'])){
                global $wpdb;
                $table_name = $wpdb->prefix . "automated_links";
                
                //Security check and input sanitize
		$id = intval(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS)); // $_GET['id'];
		
		//Add to database and redirect to the plugin default page
		$wpdb->query("DELETE FROM ". $table_name ." WHERE id = '". $id ."' LIMIT 1");
                
                die();
            }
}


//Update link button (called by ajax)
function aalUpdateLink(){


		if ( ! current_user_can( 'publish_pages' ) ) {
				wp_die();
			}
	
	
	  if ( ! wp_verify_nonce( $_POST['aal_nonce'], 'aal-ajax-nonce' ) ) {
         die ( 'no privileges');
     }
    
            if(isset($_POST['id'])){
                global $wpdb;
                $table_name = $wpdb->prefix . "automated_links";
                
                //Security check and input sanitize
		$id = intval(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS)); // $_GET['id'];
		$link = filter_input(INPUT_POST, 'aal_link', FILTER_SANITIZE_SPECIAL_CHARS); // $_POST['link'];
		$keywords = filter_input(INPUT_POST, 'aal_keywords', FILTER_SANITIZE_SPECIAL_CHARS); // $_POST['keywords'];
		$title = filter_input(INPUT_POST, 'aal_title', FILTER_SANITIZE_SPECIAL_CHARS); // $_POST['title'];
		$samelinkmeta = filter_input(INPUT_POST, 'aal_samelinkmeta', FILTER_SANITIZE_SPECIAL_CHARS); // $_POST['title'];
		$disclosureoff = filter_input(INPUT_POST, 'aal_disclosureoff', FILTER_SANITIZE_SPECIAL_CHARS); // $_POST['title'];
		$disabled = filter_input(INPUT_POST, 'aal_disabled', FILTER_SANITIZE_SPECIAL_CHARS); // $_POST['title'];
		
		$stats = '';
		if($disabled) { $stats = 'disabled'; }
		
		$meta = new StdClass();
		$meta->title = $title;
		$meta->samelink = $samelinkmeta;
		$meta->disclosureoff = $disclosureoff;
		
		$linkval = array( 'link' => $link, 'keywords' => $keywords, 'meta' => json_encode($meta), 'stats' => $stats );
		print_r($linkval);
		
		$check = $wpdb->get_results( "SELECT * FROM ". $table_name ." WHERE id = '". $id ."' " );		
		
		// Add to database 
		if($check) { 
				$wpdb->update( $table_name, $linkval , array( 'id' => $id ));
				//$aal_delete_id=$check[0]->id;
			}
			else {
				echo 'something went wrong';
			}
		

                
                die();
            }
}



//Add link form (called by ajax)
function aalAddLink(){
	
			if ( ! current_user_can( 'publish_pages' ) ) {
				wp_die();
			}
	
		
	  if ( ! wp_verify_nonce( $_POST['aal_nonce'], 'aal-ajax-nonce' ) ) {
         die ( 'no privileges');
     }
    
            	global $wpdb;
                $table_name = $wpdb->prefix . "automated_links";



     	
		// Security check and sanitize	
		$aal_link = filter_input(INPUT_POST, 'aal_link', FILTER_SANITIZE_SPECIAL_CHARS); // $_POST['link'];
		$aal_keywords = filter_input(INPUT_POST, 'aal_keywords', FILTER_SANITIZE_SPECIAL_CHARS); // $_POST['keywords'];
		$aal_title = filter_input(INPUT_POST, 'aal_title', FILTER_SANITIZE_SPECIAL_CHARS); // $_POST['title'];
		
		$aal_link = aal_add_http($aal_link);		
		
		$meta = new StdClass();
		$meta->title = $aal_title;
		$jmeta = json_encode($meta);
		
		$check = $wpdb->get_results( "SELECT * FROM ". $table_name ." WHERE link = '". $aal_link ."' " );		
		
		// Add to database 
		if($check) { 
				$wpdb->update( $table_name, array( 'keywords' => $check[0]->keywords .','. $aal_keywords), array( 'link' => $aal_link ) );
				$aal_delete_id=$check[0]->id;
			}
		else {
			$rows_affected = $wpdb->insert( $table_name, array( 'link' => $aal_link, 'keywords' => $aal_keywords, 'meta' => $jmeta ) );
			$aal_delete_id=$wpdb->insert_id;
		} 
		
        
                
                
                $aal_json=array( 'aal_delete_id' => $aal_delete_id, 'aal_new_url' => $aal_link );
                
                echo json_encode($aal_json);
                
                die();
}


//Keyword suggestion called by AJAX
function aalKWSuggestionAjax(){
	
	
			if ( ! current_user_can( 'publish_pages' ) ) {
				wp_die();
			}
	
		
	  if ( ! wp_verify_nonce( $_POST['aal_kw_nonce'], 'aal-ajax-kw-nonce' ) ) {
         die ( 'no privileges');
     }
    
            	global $wpdb;
                $table_name = $wpdb->prefix . "automated_links";
                
                
                



     					
						//do the work here     
						
		$myrows = $wpdb->get_results( "SELECT id,link,keywords,meta,stats FROM ". $table_name );			
						
		$alllinks = array();
		foreach($myrows as $row) { 
			$keys = explode(',',$row->keywords);
			foreach($keys as $key) {
			
				$alllinks[] = trim($key);	
				
			}
		}
		
		//print_r($alllinks);
    
        //Search trough your post to generate reccomend most used keywords
        $wholestring = '';
        $searchposts  = get_posts(array('numberposts' => 15,  'post_type'  => array('post','page'), 'post_status'      => 'publish'));
        foreach($searchposts as $spost) {
        	if (strlen($spost->post_content) > 2000)
 				  $spost->post_content = substr($spost->post_content, 0, 2000);
                $wholestring .=  ' '. $spost->post_content;
        }



        $wholestring = strip_tags($wholestring);
        //Remove text inside brackets
        $wholestring = preg_replace("/\[[^)]+\]/","",$wholestring);
        
        $sugint = get_option('aal_sugint');
		if($sugint=='true') { 
				$wholestring = preg_replace("~[\\\\/\.\?\-\+\(\)\{\}\[\]\'\"\:\<\>\=\$\*\^\|;,&%#@\!`]~", " ", $wholestring );
			} else {
        	$wholestring = preg_replace("/[^A-Za-z0-9]/", " ", $wholestring );
        }
       
        
        
        $wholestring = aal_removecommonwords($wholestring);
			
	       
        
       //$wholestring = sanitize_text_field($wholestring);
        

        //Replace common words
			
        
        

        //Turning the string into an array
        $karray = explode(" ",strtolower($wholestring));
        
	   
	   
//Logic for two-keywords

		$twokeywords = array();

		for ($i = 0; $i < count($karray) - 1; $i++) {
			
	   		 $word1 = $karray[$i];
   			 $word2 = $karray[$i+1];

    
  				 $bigram = trim(trim($word1) . ' ' . trim($word2));  
	   
	   		if (!empty($bigram) && strlen($bigram) >= 6 && !in_array($bigram, $alllinks) && (strpos($bigram, 'nbsp') === false) && (aal_removecommonwords($word1) || aal_removecommonwords($word2) ) && trim($word1) && trim($word2) )  {
	   				$twokeywords[] = $bigram;	
						   		
	   		}
        				
	   
	   }
	   
	   $twokeywords = array_count_values($twokeywords);
	   arsort($twokeywords);
	   $twokeywords = array_slice($twokeywords, 0, 100);
	  // print_r($twokeywords);
	   
	   
        
        //remove numbers and short keys
		foreach($karray as $id => $key) {
			if(is_numeric($key) || strlen($key)<6) {	
				unset($karray[$id]);
			}
		}
		
		$karray = array_values($karray);
		

        //Coountin how many times each keyword appear
        $final=array(); $times=array();
        foreach($karray as $kws) {

                if(!in_array($kws,$final)) {
                	 if(!in_array($kws,$alllinks)) { 
                        $final[] = $kws;
                        $times[]=1;
                	}
                }
                else{
                        foreach($final as $in => $test) {
                                if($test==$kws) $times[$in]++;
                        }
                }

        }	
        
        
        //Combining one words with two words.
        foreach ($twokeywords as $keyword => $count) {
   			 $final[] = $keyword;
   			 $times[] = $count;
			}

        //Sorting the array
        $length = count($final);
        $sw=1;
        while($sw!=0) {
                $sw=0;
                for($i=0;$i<$length-1;$i++) {
                        if($times[$i]<$times[$i+1]) {
                                $aux = $final[$i];
                                $final[$i] = $final[$i+1];
                                $final[$i+1] = $aux;
                                $aux = $times[$i];
                                $times[$i] = $times[$i+1];
                                $times[$i+1] = $aux;
                                $sw=1;

                        }
                }
        }
		$extended = array_slice($final, 0, 150);
        //Taking only the most used 20 keywords and displaying them
        $final = array_slice($final, 0, 19);
        
        
	/*	$gensw = array();       
		foreach($extended as $eid => $extitem) {
			$genitem = new StdClass;
			$genitem->keyword = $extitem;
			$genitem->times = $times[$eid];
			$gensw[] = $genitem;

		}      */   
        
        
       /*  foreach($final as $fin) {
                if($fin!='' && $fin!=' ' && $fin!= '   ') {
                        echo '<a href="javascript:;" onclick="document.getElementById(\'aal_formkeywords\').value=\''. $fin .'\'">'. $fin .'</a>&nbsp;';
                }

        } */						
											
											
				$returned = '';
				
				foreach($extended as $in => $fin) {
                if($fin!='' && $fin!=' ' && $fin!= '   ') {
                        $returned .= '<div class="aal_sugbox">'. $fin .' ('. $times[$in] .') &nbsp;&nbsp;&nbsp;<span><a class="aal_sugkey" href="javascript:;"  title="'. $fin .'">Add >> </a></span></div>';
                }
                
             }  			
			
     					
     					
                
                echo $returned;
                
                die();
}





?>