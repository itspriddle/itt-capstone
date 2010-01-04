<?php

/*************************************************************************
 *	Movierack admin 
 *  This is where the magic happens...
 */
 
// Connect to MySQL
require_once "/home/priddle/db_connections/movierack.php";

require_once "/home/priddle/www/inc/imdb/imdb.class.php";

/*************************************************************************
 * User Login
 *
 *	1. Run makemovies.php to fill /movies
 *	2. Check for new movies -> echo # new movies
 *		a) Add new movies to db.movies, (have_details = 'n')
 *	3. Check for movies without IMDBid -> echo # movies w/0 IMDBid
 *		a) Search IMDB for possible matches
 *		b) Insert user's choices into db.movies imdb_id
 *	4. Check for movies without details -> echo # movie w/o details
 *		a) Retrieve details for movies
 *		b) Insert details into db.movie_details
 */ 

function format_sql( $value, $like = NULL ){
/*************************************************************************
 * format vars for insertion into mysql
 * add %'s when needed for LIKE clauses
 */
   // Stripslashes
   if ( get_magic_quotes_gpc() ) {
       $value = stripslashes( $value );
   }

   // Quote if not a number or a numeric string
   if ( !is_numeric( $value ) ) {
       $value = "'". ( $like ? '%' : '' ) . mysql_real_escape_string( $value ) . ( $like ? '%' : '' ) ."'";
   }

   return $value;
}

function make_movies() {
/*************************************************************************
 * retrieve a listing of movies from testing1
 * and then create blank files for each
 * http://movierack.nevercraft.net
 */
	exec( "php /home/priddle/scripts/movierack/get_list.php > /home/priddle/scripts/movierack/full_list.txt" );
	
	$movies = @fopen("/home/priddle/scripts/movierack/full_list.txt", "r");
	if ($movies) {
		while (!feof($movies)) {
			$movie = fgets($movies, 4096);
			if ( !strstr( $movie, "X-Powered-By: PHP/4.4.1" ) && !strstr( $movie, "Content-type: text/html" ) )
				@touch( "/home/priddle/www/movies/" . trim( $movie ) );
		}
		fclose($movies);
		return TRUE;
	} else {
		return FALSE;
	}
}


function get_all_titles( $movie_dir = "/home/priddle/www/movies/", $strip_ext = TRUE ) {
/*************************************************************************
 * return an array of all movie files
 */
	foreach( glob( "$movie_dir*.mp4" ) as $filename ) {
		if ( $strip_ext == TRUE ) {
			$title = explode( ".mp4", $filename );
			$movie = substr( $title[0], strlen( $movie_dir ) );
			$all_movies[] = $movie;
		} else {
			$movie = substr( $filename, strlen( $movie_dir ) );
			$all_movies[] = $movie;
		}
	}

	return $all_movies;
}

function get_new() {
/*************************************************************************
 * return an array of all new (not in the db) titles
 * this does NOT include titles that are listed in the DB,
 * but just dont have IMDB IDs
 */

	foreach( get_all_titles( "/home/priddle/www/movies/", FALSE ) as $title ) {
		$sql = "SELECT * FROM mr_movie_details WHERE filename = '". addslashes( $title ). "'";
		$query = mysql_query( $sql );
		if ( mysql_num_rows( $query ) == 0 )
			$new[] = $title;
	}

	return $new;
}


function get_no_imdbid() {
/*************************************************************************
 * return an array of movie_id's that do not have IMDBids
 * stored in the database yet
 */
	$sql = "SELECT * FROM mr_movie_details WHERE imdb_id = 0";
	$res = mysql_query( $sql );
	
	if ( mysql_num_rows( $res ) > 0 ) {
		while ( $r = mysql_fetch_array( $res ) ) {
			$no_ids[] = $r['movie_id'];
			//$no_ids[]['filename'] = $r['filename'];
		}
		
		return $no_ids;
	} else {
		return NULL;
	}
	
}

function get_no_details() {
/*************************************************************************
 * get movies with IMDB ids but without details
 */
	//$sql = "SELECT * FROM mr_movie_details WHERE have_details != 'Y' AND imdb_id > 0";
	$sql = "SELECT * FROM mr_movie_details WHERE imdb_id > 0 AND date_updated <=> NULL";
	$res = mysql_query( $sql );
	
	if ( mysql_num_rows( $res ) > 0 ) {
		while ( $r = mysql_fetch_array( $res ) ) {
			$no_details[] = $r['movie_id'];
		}
		return $no_details;
	} else {
		return NULL;
	}
}

function check_genre( $genre ) {
/*************************************************************************
 * check if a genre is in the database
 */
	$sql = "SELECT * FROM mr_genres WHERE genre_name LIKE '%$genre%'";
	$res = mysql_query( $sql );

	if ( mysql_num_rows( $res ) > 0 ) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function add_genre( $genre ) {
/*************************************************************************
 * add a new genre to the database
 */
	$sql = "INSERT INTO mr_genres (genre_name) VALUES ('".
		trim( addslashes( $genre ) ) .
		"')";
	//echo $sql;
	$res = mysql_query( $sql );
	
}

function get_genre_id( $genre_title ) {
/*************************************************************************
 * get the genre ID
 */
	$sql = "SELECT genre_id FROM mr_genres WHERE genre_name = '". trim( addslashes( $genre_title ) ) ."'";
	//echo $sql;
	$res = mysql_query ( $sql );
	while ( $r = mysql_fetch_array( $res ) ) {
		$genre_id = $r['genre_id'];
	}

	return $genre_id;
}

function strip_version( $title ) {
/*************************************************************************
 * strip the version from a title and return just the title
 */
	// Remove (version) from the title
	$new_title = preg_replace( "/\(.*\)/", '', $title );
	return trim( $new_title );
}

function get_version( $title ) {
/*************************************************************************
 * pull the movie version from the filename
 */
	if ( strstr( $title, '(' ) ) {
		$version = explode( "(", $title );
		$the_version = trim( substr( $version[1], 0, strlen( $version[1] ) - 1 ) );
	} else {
		$the_version = 'Theatrical Release';
	}
	return $the_version;
}

function get_version_id( $version ) {
/*************************************************************************
 * get the version id
 */
	$sql =  "SELECT version_id FROM movie_versions ".
			" WHERE version_name = ".
			format_sql( $version ). " LIMIT 1";
	$res = mysql_query( $sql );
	
	if ( $res ) {
		while ( $r = mysql_fetch_array( $res ) ) 
			$verID = $r['version_id']; 
			
		return $verID;
		
	} else
		return NULL;
}

function check_version( $version ) {
/*************************************************************************
 * check if a version is in the database
 */
	$sql = "SELECT * FROM movie_versions WHERE version_name = ".
			format_sql( $version );
	$res = mysql_query( $sql );
	if ( mysql_num_rows( $res ) > 0 ) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function add_new_version( $version ) {
/*************************************************************************
 * add a new movie version into the database
 */
	$sql = "INSERT INTO movie_versions (version_name) VALUES (".
			format_sql( $version ) . 
			")";
	//echo $sql;
	$res = mysql_query( $sql );
	if ( $res ) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function lookup_imdbids( $form_action ) {
/*************************************************************************
 * query the IMDB for possible matches in order to get
 * the IMDB ID for movies that we dont have one for yet
 *
 * returns the html form to send to a processing script
 */
	$sql = "SELECT filename, movie_id FROM mr_movie_details WHERE imdb_id = 0";
	$res = mysql_query( $sql );

	if ( mysql_num_rows( $res ) > 0 ) {

		while ( $r = mysql_fetch_array( $res ) ) {
			$a = explode( '.mp4', $r['filename'] );
			$titles[] = strip_version( $a[0] );
			$files[]  = $r['filename'];
		}

	    $html .= "<form action=\"$form_action\" name=\"imdb_lookup\" id=\"imdb_lookup\" method=\"post\">\n<ol>\n";

	    $c = 1;
	    foreach( $titles as $local_title) {
	        $html .= "  <li>IMDB Search Results for: ". trim($local_title) ."</li>\n";
	        $html .= "  <ol>\n";

    	    $search = new imdbsearch();
	        $search->setsearchname($local_title);
	        $results = $search->results();

			$j = 1;
	        foreach ($results as $res) {
	            $imdb_id    = $res->imdbid();
	            $imdb_title     = $res->title();
    	        $imdb_year  = $res->year();

	            $str  = '    <li>';
	            $str .= '<input type="radio" name="imdb_id_'. $c .'" value="'. $imdb_id .'" />';
	            $str .= '<a href="http://us.imdb.com/title/tt';
	            $str .= $imdb_id;
	            $str .= "\">$imdb_title ($imdb_year)</a></li>\n";
	            $r[] = $str;
	            $str = '';

	        }

	        for ($i = 0; $i < 10; $i++) {
	            $html .= $r[$i];
	        }

	        $html .= '    <li><input type="radio" name="imdb_id_'. $c .'" value="other" />'.
	            'Manually Enter IMDB ID: '.
	            '<input type="text" name="cust_imdb_id_'. $c ."\" value=\"0000000\" onfocus=\"this.value=''\" /></li>\n";

    	    unset($r);



	        $html .= "  </ol>\n".
					 '<input type="hidden" name="filename_'. $c .'" value="'. $files[ $c - 1 ]  ."\" />\n";
			$c++;
	    }
	    $html .= "</ol>\n";

	    $html .= '<input type="hidden" name="total_movies" value="'. ($c - 1) ."\" />\n";
	    $html .= '<p class="submit" style="text-align: left"> <input type="submit" name="submit" value=" Update IMDB IDs " /> </p>'."\n";

	    $html .= "</form>\n";
	
	    return $html;
	}
}

function add_imdbids(  ) {
/*************************************************************************
 * add imdbid's to movies table
 */
	for ( $i = 1; $i <= $_POST['total_movies']; $i++ ) {
		$sql =  "UPDATE mr_movie_details SET imdb_id = '";
		if ( ( $_POST['imdb_id_'. $i] == 'other' ) && ( $_POST['cust_imdb_id_'. $i] > 1 ) ) {
			$sql .= "{$_POST['cust_imdb_id_'. $i]}";
		} else {
			if ( $_POST['imdb_id_'. $i] > 0 ) {
				$sql .= $_POST['imdb_id_'. $i];
			} else {
				$sql .= '0000000';
				$blank = TRUE;
			}
		}
		
		$sql .=	"' WHERE filename = ". format_sql( $_POST['filename_'. $i] );
		//echo $sql;
		$res = mysql_query( $sql );
		
		if ( $res )
			$html['msg'] .= "<div id=\"message\" class=\"updated\"><p>". ( $blank ? "Blank " : '' ) ."IMDB ID added for <b>{$_POST['filename_'. $i]}</b>!</p></div>\n";
		else
			$html['msg'] .= "<div id=\"message\" class=\"updated\"><p>Unable to add IMDB ID for <b>{$_POST['filename_'. $i]}</b>!</p></div>\n";
		
	}
	
	$html['main'] .= "<p>Click <a href=\"{$_SERVER['PHP_SELF']}?step=5\">here</a> to retrieve details for any new movies with an IMDB ID.</p>\n";
	
	return $html;
}


function movieinit() {
/*************************************************************************
 * User Login
 *
 *	1. Run makemovies.php to fill /movies
 *	2. Check for new movies -> echo # new movies
 *	3. Check for movies without IMDBid -> echo # movies w/0 IMDBid
 *	4. Check for movies without details -> echo # movie w/o details
 */ 
	$make = make_movies();
	//if ( !$make ) 
		//die ( "Error! Could not fetch movie list from media server!" );
	
	$new 			= get_new();
	$num_new 		= count( $new );
	
	$no_imdbid		= get_no_imdbid();
	$num_no_imdbid	= count( $no_imdbid );
	
	$no_details		= get_no_details();
	$num_no_details	= count( $no_details );
	
	if ( $num_new > 0 ) {
		$html .= "<p>There are $num_new new movies.  Click ".
				 "<a href=\"{$_SERVER['PHP_SELF']}?step=2\">here</a>".
				 " to add them to the database.</p>\n";
	} else {
		$html .= "<p>There are no new movies.</p>\n";	
	}
	
	
	if ( $num_no_imdbid > 0 ) {
		$html .= "<p>There are $num_no_imdbid movies without IMDB IDs.  Click ".
				 "<a href=\"{$_SERVER['PHP_SELF']}?step=3\">here</a>".
				 " to get IMDB IDs for these titles.<br />\n".
				 "<strong>Note:</strong> This can take several minutes depending on the number ".
				 " of movies to lookup.</p>";
	} else {
		$html .= "<p>There are no movies without IMDB IDs.</p>\n";	
	}
	
	
	if ( $num_no_details > 0 ) {
		$html .= "<p>There are $num_no_details movies with IMDB IDs but without details downloaded.  Click ".
				 "<a href=\"{$_SERVER['PHP_SELF']}?step=5\">here</a>".
				 " to get details for these movies.</p>\n";
	} else {
		$html .= "<p>There are no movies with IMDB IDs but without details downloaded.</p>\n";	
	}
	
	if ( ( $num_new + $num_no_imdbid + $num_no_details ) == 0 )
		$html .= "<p><strong>Go home!  There's nothing to do here!</strong></p>\n";
	
	return $html;

}

function add_new( ) {
	$new = get_new();
	if ( count( $new ) > 0 ) {
		foreach ( $new as $n ) {
			$sql =  "INSERT INTO mr_movie_details (imdb_id, date_added, filename) VALUES ('0000000', NOW(),". format_sql( $n ).')';
			$res = mysql_query( $sql );
			
			if ( $res ) 
				$html['msg'] .= "<div id=\"message\" class=\"updated\"><p>$n</b> added to database! </p></div>\n";
					 //"<p>Added $n to database!</p>\n";
			else 
				$html['msg'] .= "<div id=\"message\" class=\"updated\"><p>Unable to add <b>$n</b> to database!</p></div>\n";
		}
		
		$html['main'] = "<p>Click <a href=\"{$_SERVER['PHP_SELF']}?step=3\">here</a>".
				 " to lookup IMDB IDs for these titles.<br /><strong>Note:</strong> This can take a while if there are several movies to lookup.</p>\n";
	} else {
		$html['main'] = "<p>There are no new movies to add.</p>\n";
	}
	return $html;
}

function retrieve_details() {
/*************************************************************************
 * retrieve movie info from IMDB
 * and update movie_details table
 */

// 11/25 - this shit needs work...
 
	/**
	 * step 1
	 * 
	 * retrieve all movies with an imdb_id but no details
	 */
	ini_set("max_execution_time", "300");
	$sql_all = "SELECT * FROM mr_movie_details WHERE imdb_id > 0 AND date_updated <=> NULL";
	$res_all = mysql_query( $sql_all );

	// End the script if there are no new movies
	if ( mysql_num_rows( $res_all ) > 0 ) {

		/**
		 * step 2
		 *
		 * retrieve all imdb_info and update db
		 */
	
		while ( $r = mysql_fetch_array( $res_all ) ) {
	
			$imdbid   = $r['imdb_id'];
			$movie_id = $r['movie_id'];
			$filename = $r['filename'];
		
			$movie = new imdb( $imdbid );
			$movie->setid( $imdbid );
			
			$title      = $movie->title();
			$year       = $movie->year();
			$runtime    = $movie->runtime();
			$genre      = $movie->genre();
//			$genreID 	= get_genre_id( $genre );
			$all_genres = $movie->genres();
			$mpaar 		= $movie->mpaa ();
			$tagline	= $movie->tagline();
			$directors	= $movie->director();
			$producers	= $movie->producer();
			$writers	= $movie->writing();
			$cast		= $movie->cast();
			//$plot 		= $movie->plot();
			//$short_plot = substr( $plot[0], 0, 255 );
			


			if ( strlen( $plot[0] ) > 255 )
				$short_plot .= ' [...]';
			
			if ( check_genre( $genre ) == FALSE )
				add_genre( $genre );
	
			foreach ( $all_genres as $g ) {
				if ( check_genre( $g ) == FALSE )
					add_genre( $g );
	
				// Update movie_genres
				$mg_sql = "INSERT INTO mr_movie_genres (movie_id, genre_id) VALUES ".
					" ($movie_id, ". get_genre_id( $g ) .")";
	
				$mg_res = mysql_query( $mg_sql );
			}

/*
	
			$theVersion = get_version( substr( $filename, 0, strlen( $filename ) - 4 ) );
			//echo "Version: $theVersion";
			//echo $theVersion;
			if ( !check_version( $theVersion ) ) 
				if ( !add_new_version( $theVersion ) )
					echo "<p>Couln't add $theVersion version to database.</p>\n";
				
			$theVersionID = get_version_id( $theVersion );
*/			
			
// -- THIS NEEDS TO BE EDITED..

			$md_sql =	'UPDATE mr_movie_details SET '.
						' date_updated = NOW(), '.
						' title = '. format_sql( $title ). ', '.
						' year = '. format_sql( $year ). ', '.
						' runtime = '. format_sql( $runtime ). ', '.
						' mpaa_rating = '. format_sql( $mpaar['USA'] ). ', '.
						' tagline = '. format_sql( trim( $tagline ) ) . ' '.
						" WHERE movie_id = $movie_id LIMIT 1";


			$producersSQL = "INSERT INTO mr_movie_producers (movie_id, producer_id, producer_role) VALUES ";
			for ( $i = 0; $i < count( $producers ); $i++ ) {
				$checkSQL = "SELECT * FROM mr_producers WHERE producer_name LIKE ". format_sql( trim( $producers[$i]['name'] ), TRUE );
				$checkRES = mysql_query( $checkSQL );
				if ( mysql_num_rows( $checkRES ) == 0 )  {
					mysql_query( "INSERT INTO mr_producers (producer_name) VALUES (". format_sql( trim( $producers[$i]['name'] ) ) .")" );
				} else {
					while ( $r = mysql_fetch_array( $checkRES ) )  {
						$producer_id = $r['producer_id'];
					}
				}
				$producersSQL .= "( $movie_id, $producer_id, ". format_sql( trim( $producers[$i]['role'] ) ) ."), ";
			}
			$producersSQL = substr( $producersSQL, 0, -2 );
			//echo $producersSQL;
			@mysql_query( $producersSQL );// or die("Couldn't add producers! ");

            $writersSQL = "INSERT INTO mr_movie_writers (movie_id, writer_id) VALUES ";
            for ( $i = 0; $i < count( $writers ); $i++ ) {
                $checkSQL = "SELECT * FROM mr_writers WHERE writer_name LIKE ". format_sql( trim( $writers[$i]['name'] ), TRUE );
                $checkRES = mysql_query( $checkSQL );
                if ( mysql_num_rows( $checkRES ) == 0 ) {
                    mysql_query( "INSERT INTO mr_writers (writer_name) VALUES (". format_sql( trim( $writers[$i]['name'] ) ) .")" );
                } else {
                    while ( $r = mysql_fetch_array( $checkRES ) )  {
                        $writer_id = $r['writer_id'];
                    }
                }
                $writersSQL .= "( $movie_id, $writer_id ), ";
            }
            $writersSQL = substr( $writersSQL, 0, -2 );
            //echo $writersSQL;
			@mysql_query( $writersSQL );// or die("Couldn't add writers!");

            $directorsSQL = "INSERT INTO mr_movie_directors (movie_id, director_id) VALUES ";
            for ( $i = 0; $i < count( $directors ); $i++ ) {
                $checkSQL = "SELECT * FROM mr_directors WHERE director_name LIKE ". format_sql( trim( $directors[$i]['name'] ), TRUE );
                $checkRES = mysql_query( $checkSQL );
                if ( mysql_num_rows( $checkRES ) == 0 ) {
                    mysql_query( "INSERT INTO mr_directors (director_name) VALUES (". format_sql( trim( $directors[$i]['name'] ) ) .")" );
                } else {
                    while ( $r = mysql_fetch_array( $checkRES ) )  {
                        $director_id = $r['director_id'];
                    }
                }
                $directorsSQL .= "( $movie_id, $director_id ), ";
            }
            $directorsSQL = substr( $directorsSQL, 0, -2 );
            //echo $directorsSQL;
			@mysql_query( $directorsSQL );// or die("Couldn't add directors!");


            $castSQL = "INSERT INTO mr_movie_actors (movie_id, actor_id, actor_role) VALUES ";
            for ( $i = 0; $i < count( $cast ); $i++ ) { 
                $checkSQL = "SELECT * FROM mr_actors WHERE actor_name LIKE ". format_sql( trim( $cast[$i]['name'] ), TRUE );
                $checkRES = mysql_query( $checkSQL );
                if ( mysql_num_rows( $checkRES ) == 0 ) {
                    mysql_query( "INSERT INTO mr_actors (actor_name) VALUES (". format_sql( trim( $cast[$i]['name'] ) ) .")" );
                } else {
                    while ( $r = mysql_fetch_array( $checkRES ) )  {
                        $actor_id = $r['actor_id'];
                    }
                }
                $castSQL .= "( $movie_id, $actor_id, ". format_sql( trim( $cast[$i]['role'] ) ) ."), ";
            }
            $castSQL = substr( $castSQL, 0, -2 );
            //echo $castSQL;
			@mysql_query( $castSQL );// or die("Couldn't add cast!");


// directors, writers, cast

	
			$ratingSQL = "INSERT INTO mr_movie_ratings (movie_id) VALUES ($movie_id)";
			$ratingRes = mysql_query( $ratingSQL );
	
			$m_sql = "UPDATE movies SET ".
				" have_details = 'Y', ".
				" version_id = $theVersionID ".
				" WHERE movie_id = $movie_id";
	
			//echo $m_sql;
			
			$md_res = mysql_query( $md_sql );
//			$m_res  = mysql_query( $m_sql );
	
			$html['msg'] .= "<div id=\"message\" class=\"updated\"><p>Info for <b>$title</b> added to database!</p></div>\n";
	
		}
		
		$html['main'] .= "<p>Go <a href=\"{$_SERVER['PHP_SELF']}\">Home</a>.</p>\n";
	} else {
		$html['main'] .= "<p>There are no movies with IMDB IDs but without details downloaded.</p>\n";
	}
	
	return $html;
}


function get_movie_genres( $mid ) {
    $sql = "SELECT genre_id FROM mr_movie_genres WHERE movie_id = $mid";
    $res = mysql_query( $sql );
    if ( $res ) {
        while ( $r = mysql_fetch_array( $res ) ) {
            $genreIDs[] = $r['genre_id'];
        }

        foreach ( $genreIDs as $g ) {
            $sql2 = "SELECT genre_name FROM mr_genres WHERE genre_id = $g";
            $res2 = mysql_query( $sql2 );

            if ( $res2 ) {
                while ( $r = mysql_fetch_array( $res2 ) ) {
                    $movieGenres[] = $r['genre_name'];
                }
            }
        }
        return $movieGenres;
    }
}
function get_mpaa_rating( $imdbid ) {
        $movie = new imdb( $imdbid );
        $movieid = $imdbid;
        $movie->setid( $movieid );

        $mpaar = $movie->mpaa ();


        return $mpaar['USA'];
}


function show_update_genres( $genres = array() ) {
    $sql = "SELECT * FROM mr_genres ORDER BY genre_name";
    $res = mysql_query( $sql );

    while ( $r = mysql_fetch_array( $res ) ) {
        $data[] = "      <label><input type=\"checkbox\" name=\"genres[]\" value=\"{$r['genre_id']}\" ".
		( @in_array( $r['genre_name'], $genres ) ? ' checked="checked"' : '' ).
		" /> {$r['genre_name']}</label>\n";
    }

    $html = "      <table id=\"upd-genres\">\n";

    for ( $i = 0; $i < ceil( count( $data ) / 5 ); $i++ ) {
        $html .= "        <tr>\n";
        for ( $j = 0; $j < 5; $j++ ) {
            $html .= "          <td>{$data[ ( $i * 5 ) + $j ]}</td>\n";
        }

        $html .= "        </tr>\n";
    }
    $html .= "      </table>\n";
    return $html;
}

function show_update_mpaa( $default = NULL ) {
    $html = "      <select name=\"mpaa_rating\" id=\"mpaa_rating\" />\n";

    $sql = "SELECT * FROM mpaa_ratings ORDER BY rating";
    $res = mysql_query( $sql );
    while ( $r = mysql_fetch_array( $res ) )
        $html .= "        <option value=\"{$r['mpaa_rating_id']}\"".( $default == $r['rating'] ? ' selected="selected"' : ''  ).">{$r['rating']}</option>\n";

    $html .= "      </select>";
    return $html;
}
function editmovielist_old() {
    $sqlMovies = "SELECT * FROM movies ORDER BY filename";
    $resMovies = mysql_query( $sqlMovies );
    $html = "<ol>\n";
    while ( $r = mysql_fetch_array( $resMovies ) ) {
        $movie = substr( $r['filename'], 0, ( strlen( $r['filename'] ) - 4 ) );
        $html .= "  <li>[<a href=\"movies.php?mid={$r['movie_id']}\">Edit</a>] $movie</li>\n";
    }
    $html .= "</ol>\n";
    return $html;
}


function editmovielist( $start = NULL, $perpage = NULL ) {
    $sqlMovies = "SELECT * FROM mr_movie_details WHERE date_updated IS NOT NULL ORDER BY filename";
    $resMovies = mysql_query( $sqlMovies );
	$c = 0;
    while ( $r = mysql_fetch_array( $resMovies ) ) {
        $movie = substr( $r['filename'], 0, ( strlen( $r['filename'] ) - 4 ) );
		$movies[$c]['title'] = $movie;
		$movies[$c]['movie_id'] = $r['movie_id'];
		$c++;
    }

	if ( $start == null ) $start = 0;
	if ( $perpage == null ) $perpage = 15;

//	$html = "<ul style=\"list-style: none\">\n";
	$html = "<ol start=\"". ( $start + 1 ) ."\">\n";
	for ( $i = $start; $i < ( $start + $perpage ); $i++ ) {
		$html .= "  <li>[<a href=\"movies.php?mid={$movies[$i]['movie_id']}\">Edit</a>] {$movies[$i]['title']}</li>\n";
		if ( $i == ( mysql_num_rows( $resMovies ) - 1 ) )
			break;
	}

    $html .= "</ul>\n";

	if ( ( $start - $perpage ) >= 0 )
		$prev = "<a href=\"{$_SERVER['PHP_SELF']}?start=". ( $start - $perpage ) ."&amp;perpage=$perpage\">Previous Page</a>";

	if ( ( $start + $perpage ) < mysql_num_rows( $resMovies ) )
		$next = "<a href=\"{$_SERVER['PHP_SELF']}?start=". ( $start + $perpage ) ."&amp;perpage=$perpage\">Next Page</a>";

	if ( $prev != '' || $next != '' )
		$html .= "<p>";
	
	if ( $prev != '' )
		$html .= $prev;
	if ( $prev != '' && $next != '' )
		$html .= " | ";
	if ( $next != '' )
		$html .= $next;

    if ( $prev != '' || $next != '' )
        $html .= "</p>";


    return $html;
}
