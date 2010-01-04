<?php
/**
 * admin.class.php (06/08/07)
 * The movierack.net admin class
 **/


/***
	06142007-0136 -- make sure to copy list.php to list_movies.php on testing1 
***/


define('HOMEDIR', '/home/priddle/');
define('INCLDIR', HOMEDIR . 'www/cp/inc/');

require_once HOMEDIR .'db_connections/movierack.php';
require_once INCLDIR .'imdb/imdb.class.php';
require_once INCLDIR .'pagination.php';
require_once INCLDIR .'functions.php';


class Admin {

	var $MediaPath = array('m1');
	var $PageHTML;
	var $Splash;
	var $Episodes = array();
	
	function Admin( $page = NULL ) { 
		if ( !isset($page ) ) {
			$this->Page('index');
		} else {
			$this->Page($page);
		}
	}
	
	function movie_init() {
		$this->MakeMovies();		// make movie dummy files
		$this->AddNewMovies(FALSE);
	}
	
	function show_init() {
		$this->MakeShows();
	}

	function MakeMovies() {
	// makes dummy files for each movie
		foreach ( $this->MediaPath as $mp ) {
			exec( HOMEDIR ."scripts/movierack/getMovies $mp > ". HOMEDIR ."scripts/movierack/movies/$mp.txt" );
			$movies = @fopen( HOMEDIR ."scripts/movierack/movies/$mp.txt", "r" );
			if ( $movies ) {
				while ( !feof( $movies ) ) {
					$movie = fgets( $movies, 4096 );
					if ( !is_dir( HOMEDIR .".mr_skel/movies/$mp" ) ) {
						mkdir( HOMEDIR .".mr_skel/movies/$mp" );
					}
					@touch( HOMEDIR .".mr_skel/movies/$mp/". trim( $movie ) );
				}
				fclose( $movies );
				exec( "chmod -R 777 ". HOMEDIR .".mr_skel/movies/$mp" );
				return TRUE;
			} else {
				return FALSE;
			}		
		}
	}

	function MakeShows() {
		foreach ( $this->MediaPath as $mp ) { 
			exec( HOMEDIR ."scripts/movierack/getShows $mp > ". HOMEDIR ."scripts/movierack/shows/$mp.txt" );
			$shows = @fopen(HOMEDIR ."scripts/movierack/shows/$mp.txt", "r");
			if ( $shows ) {
				if ( !is_dir( HOMEDIR .".mr_skel/shows/$mp" ) ) {
					mkdir( HOMEDIR .".mr_skel/shows/$mp", 0777 );
				}
				while ( !feof( $shows ) ) {
					$line = fgets( $shows, 4096 );
					if ( strlen( $line ) < 1 ) continue;
					if ( substr( $line, 0, 3 ) == '###' ) {
						$show = trim( substr( $line, 4 ) ); 

						if ( strlen( $show ) > 0 ) {
							$this->AddShow( $show );
						}

						if ( !is_dir( HOMEDIR .".mr_skel/shows/$mp/$show" ) ) {
							mkdir( HOMEDIR .".mr_skel/shows/$mp/$show", 0777 );
						}
					} else {
						$episode = trim( $line );
						@touch( HOMEDIR .".mr_skel/shows/$mp/$show/$episode" );

						/* -- Not using this right now
						$ep = $this->FormatEpisode( $episode );
						if ( !is_numeric( $ep['season'] ) || !isset( $ep['season'] ) ) $ep['season'] = NULL;
						if ( !is_numeric( $ep['number'] ) || !isset( $ep['number'] ) ) $ep['number'] = NULL;
						$this->AddShow( $ep['show'], $episode, $ep['title'], $ep['number'], $ep['season'] );
						*/
					}
				}
				//unset( $ep );
				unset( $show );
				unset( $episode );
				fclose( $shows );
				exec( "chmod -R 777 ". HOMEDIR .".mr_skel/shows/$mp" );
			} else {
				return FALSE;
			}		
		}
	}

	function GetShowID( $show ) {
		$sql = "SELECT show_id FROM mr_shows WHERE title = ". formatSQL( $show ) ." LIMIT 1";
		$res = mysql_query( $sql );
		$r = mysql_fetch_array( $res );
		return $r[0];
	}

	function FormatEpisode( $filename ) {
		// Sealab 2021 - 101 - I, Robot.mp4
		if ( !strpos( $filename, " - " ) ) { 
				$episode['title'] = substr( $filename, 0, -4 );
				return $episode;
		}
		$episode = array();
		$raw = explode( " - ", $filename );
		$episode['show'] = $raw[0];
		if ( !strstr( $raw[1], '.mp4' ) ) {
			$episode['season'] = substr( $raw[1], 0, 1 );
			$episode['number'] = substr( $raw[1], 1 );
			$episode['title'] = substr( $raw[2], 0, -4 );
		} else {
			$episode['title'] = substr( $raw[1], 0, -4 );
		}
		return $episode;
	}

	function CheckEpisode( $filename ) {
		$sql = "SELECT * FROM mr_show_episodes WHERE filename = ". formatSQL( $filename ) ." LIMIT 1";
		$res = mysql_query( $sql );
		if ( @mysql_num_rows( $res ) == 1 ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function AddShow( $show ) {
		$sql = "INSERT IGNORE INTO mr_shows (title) VALUES (". formatSQL( $show ) .")";
		$res = mysql_query( $sql );
		if ( mysql_affected_rows() > 0 ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}



	function __AddShow( $show, $filename = NULL, $episode = NULL, $episodeNum = NULL, $season = NULL ) {
		if ( isset( $filename ) && $this->CheckEpisode( $filename ) ) exit;
		if ( isset( $episode ) ) {
			$sql = "INSERT INTO mr_show_episodes (".
					" show_id, ".
					" filename, ".
					" date_added, ". 
					( isset( $episodeNum ) 	? "episode_number, " : '' ) . 
					( isset( $season ) 		? "season, " : '' ) .
					" episode_name ".
					") VALUES (".
					$this->GetShowID( $show ). ", ".
					formatSQL( $filename ). ", ".
					"now(), ". 
					( isset( $episodeNum ) ? formatSQL( $episodeNum ). ", " : '' ).
					( isset( $season ) ? formatSQL( $season ). ", " : '' ).
					"'". trim( addslashes( $episode ) ). 
					"') ";
			$res = mysql_query( $sql );
			if ( mysql_affected_rows() > 0 ) {
				return true;
			} else {
				return false;
			}
		} else {
			$sql = "INSERT IGNORE INTO mr_shows (title) VALUES (". formatSQL( $show ) .")";
			$res = mysql_query( $sql );
			if ( mysql_affected_rows() > 0 ) {
				return true;
			} else {
				return false;
			}
		}
	}

	function GetFiles( $get = 'movies', $strip_ext = FALSE ) {
		foreach ($this->MediaPath as $mp) {
			$dir = HOMEDIR .".mr_skel/". ( $get == 'episodes' ? 'shows' : $get ) ."/$mp/";
			if ( $get == 'movies' ) {
				$i = 0;
				foreach ( glob( "$dir*.mp4" ) as $filename ) {
					if ( $strip_ext == TRUE ) {
						$t 		= explode( ".mp4", $filename );
						$title	= substr( $t[0], strlen( $dir ) );
					} else {
						$title 	= substr( $filename, strlen( $dir ) );
					}
					$all[$i]['title'] 	= $title;
					$all[$i]['media_path'] = $mp;
					$i++;
				}
			} elseif ( $get == 'episodes' ) {
				$i = 0;
				foreach( $this->GetEpisodeFiles( $dir ) as $ep_filename ) {
					if ( $strip_ext == TRUE ) {
						$t		= explode( ".mp4", $ep_filename );
						$title	= $t[0];
					} else {
						$title 	= $ep_filename;
					}	
					$all[$i]['title'] = $title;
					$all[$i]['media_path'] = $mp;
					$i++;
				}
			}
		}
		return $all;	
	}

	function GetTitlesFromFiles( $get = 'movies' ) {
		return $this->GetFiles( $get, FALSE );
	}
	
	function GetEpisodeFiles( $base ) {
		$episodes = array();
		foreach( glob( "$base/*" ) as $filename ) { 
			$file = substr( $filename, ( strlen( $base ) + 1 ) );
			//if ( substr( $file, -3 ) == 'php' || $file == 'lost+found' || $file == '_scripts' ) continue;
			if ( is_dir( $filename ) ) {
				$res = $this->GetEpisodeFiles( $filename );
				$episodes = array_merge( $episodes, $res );
			} else {
				array_push( $episodes, $file );
			}
		}
		return $episodes;
	}

	function GetNewMovies() {
		$i = 0;
		foreach ($this->GetFiles() as $title) {
			$sql = "SELECT * FROM mr_movie_details WHERE filename = ". formatSQL( $title['title'] );
			$res = mysql_query($sql);
			if (mysql_num_rows($res) == 0 ) {
				$new[$i]['title'] = $title['title'];
				$new[$i]['media_path'] = $title['media_path'];
			}
			$i++;
		}
		return $new;
	}

	function GetNewShows() {
		$sql = "SELECT title FROM mr_shows WHERE date_updated <=> NULL";
		$res = mysql_query( $sql );
		while ( $r = mysql_fetch_array( $res ) ) {
			$new[] = $r['title'];
		}
		return $new;
	}
	
	function GetNewEpisodes() {
		$sql = "SELECT title FROM mr_episodes WHERE date_updated <=> NULL";
		$res = mysql_query( $sql );
		while ( $r = mysql_fetch_array( $res ) ) {
			$new[] = $r['title'];
		}
		return $new;
	}
	
	function __GetNewEpisodes() {
		$episodes = $this->GetFiles('episodes');
		for( $i = 0; $i < count( $episodes ); $i++ ) {
			$sql = "SELECT * FROM mr_show_episodes WHERE filename = ". formatSQL( $episodes[$i]['title'] ) ." AND date_updated <=> NULL"; 
			$res = mysql_query($sql);
			if ( @mysql_num_rows( $res ) == 1 ) {
				$new[] = $episodes[$i]['title'];
			}
		}
		return $new;
	}

	function CheckNoIMDBID() {
		$sql = "SELECT * FROM mr_movie_details WHERE imdb_id = 0";
		$res = mysql_query( $sql );
		if ( mysql_num_rows( $res ) > 0 ) {
			while ( $r = mysql_fetch_array( $res ) ) {
				$no_ids[] = $r['movie_id'];
			}
			return $no_ids;
		} else {
			return NULL;
		}
	}
	
	function CheckNoDetails() {
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
	
	function CheckGenre($genre) {
		$sql = "SELECT * FROM mr_genres WHERE genre_name LIKE ". formatSQL($genre, TRUE) ."";
		$res = mysql_query( $sql );
		if ( mysql_num_rows( $res ) > 0 ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function AddGenre($genre) {
		$sql = "INSERT INTO mr_genres (genre_name) VALUES ('". trim(addslashes($genre))."')";
		if (mysql_query($sql)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function GetGenreID($genre) {
		$sql = "SELECT genre_id FROM mr_genres WHERE genre_name = '". trim(addslashes($genre)) ."' LIMIT 1";
		$res = mysql_query($sql);
		while ($r = mysql_fetch_array($res)) {
			$genre_id = $r['genre_id'];
		}
		return $genre_id;	
	}
	
	function GetMovieGenres( $mid ) {
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
	
	function GetMovieCount() {
		return mysql_num_rows( mysql_query( "SELECT * FROM mr_movie_details WHERE date_added IS NOT NULL" ) );
	}

	function UpdateGenresForm( $genres = array() ) {
	    $sql = "SELECT * FROM mr_genres ORDER BY genre_name";
	    $res = mysql_query( $sql );

	    while ( $r = mysql_fetch_array( $res ) ) {
	        $data[] = "<label><input type=\"checkbox\" name=\"genres[]\" value=\"{$r['genre_id']}\"".
			( @in_array( $r['genre_name'], $genres ) ? ' checked="checked"' : '' ).
			" />{$r['genre_name']}</label>\n";
	    }

	    $html = "\t\t\t\t<table id=\"upd-genres\">\n";

	    for ( $i = 0; $i < ceil( count( $data ) / 5 ); $i++ ) {
	        $html .= "\t\t\t\t\t<tr>\n";
	        for ( $j = 0; $j < 5; $j++ ) {
	            if ( isset( $data[ ( $i * 5 ) + $j ] ) ) {
					$html .= "\t\t\t\t\t\t<td>". trim($data[ ( $i * 5 ) + $j ] ) ."</td>\n";
				} else {
					$html .= "\t\t\t\t\t\t<td>&nbsp;</td>\n";
				}
	        }

	        $html .= "\t\t\t\t\t</tr>\n";
	    }
	    $html .= "\t\t\t\t</table>\n";
	    return $html;
	}

	function GetVersion( $movie ) {
		if( strstr( $movie, '(' ) ) {
			$version = explode( "(", $movie );
			$theVersion = trim( substr( $version[1], 0, strlen( $version[1]) - 1 ) );
		} else {
			$theVersion = 'Theatrical Release';
		}
		return $theVersion;
	}
	
	function StripVersion( $movie ) {
		return trim( preg_replace( "/\(.*\)/", '', $movie ) );
	}
	
	function AddHTML( $html ) {
		$this->PageHTML .= $html;
	}
	
	function GeneratePage() {
		echo $this->PageHTML;
	}
	
	function LookupIMDBIDS() {
		$sql = "SELECT filename, movie_id FROM mr_movie_details WHERE imdb_id = 0";
		$res = mysql_query($sql);
			if (mysql_num_rows($res) > 0) {
			while ($r = mysql_fetch_array($res)) {
				$title_a = explode( '.mp4', $r['filename'] );
				$titles[] = $this->StripVersion( $title_a[0] );
				$files[]  = $r['filename'];
			}	
	
			$html  = "\t<form action=\"/movies/add/lookup/imdbids\" name=\"imdb_lookup\" id=\"imdb_lookup\" method=\"post\">\n";
			$html .= "\t\t<ol>\n";
	
			$c = 1;
			
			foreach ($titles as $t) {
				$html .= "\t\t\t<li>IMDB Search Results for: ". trim($t) ."</li>\n";
				$html .= "\t\t\t<ol>\n";
	
				$search = new imdbsearch();
				$search->setsearchname($t);
				$search_res = $search->results();
				
				foreach ($search_res as $sr) {
					$imdb_id 	= $sr->imdbid();
					$imdb_title = $sr->title();
					$imdb_year 	= $sr->year();
	
					$str  = "\t\t\t\t<li><input type=\"radio\" name=\"imdb_id_$c\" value=\"$imdb_id\" />";
					$str .= "<a href=\"http://us.imdb.com/title/tt$imdb_id\">$imdb_title ($imdb_year)</a></li>\n";
					$h[]  = $str;
						//$str  = '';
				}
	
	
				for ($i = 0; $i < 10; $i++) {
					$html .= $h[$i];
				}
	
				unset($h);
				$html .= "\t\t\t\t<li><input type=\"radio\" name=\"imdb_id_$c\" value=\"other\" />Manually Enter IMDB ID: ";
				$html .= "<input type=\"text\" name=\"cust_imdb_id_$c\" value=\"0000000\" onfocus=\"this.value=''\" /></li>\n";
				$html .= "\t\t\t</ol>\n";
				$html .= "\t\t\t<input type=\"hidden\" name=\"filename_$c\" value=\"". $files[$c - 1] ."\" />\n";
				$c++;
			}
			$html .= "\t\t</ol>\n";
			$html .= "\t\t<input type=\"hidden\" name=\"total_movies\" value=\"". ($c - 1) ."\" />\n";
			$html .= "\t\t<p class=\"submit\" style=\"text-align: left\"> <input type=\"submit\" name=\"submit\" value=\" Update IMDB IDs \" /> </p>\n";
			$html .= "\t</form>\n";
	
		}
	
		$this->AddHTML($html);
	}
	
	function AddIMDBIDS() { 
		for ($i = 1; $i <= $_POST['total_movies']; $i++) {
			$sql = "UPDATE mr_movie_details SET imdb_id = '";
			if ($_POST['imdb_id_'. $i] == 'other' && $_POST['cust_imdb_id_'. $i] > 1) {
				$sql .= $_POST['cust_imdb_id_'. $i];
			} else {
				if ($_POST['imdb_id_'. $i] > 0) {
					$sql .= $_POST['imdb_id_'. $i];
				} else {
					$sql .= '0000000';
					$blank = TRUE;
				}
			}
	
			$sql .= "' WHERE filename = ". formatSQL($_POST['filename_'. $i]);
			
			if (mysql_query($sql)) {
				$this->SetSplash("Added IMDB ID for <strong>{$_POST['filename_'. $i]}</strong>");
			} else {
				$this->SetSplash("Couldn't add IMDB ID for <strong>{$_POST['filename_'. $i]}</strong>");
			}
	
		}
	}
	
	function AddNewMovies( $output = TRUE ) {
		$new = $this->GetNewMovies(); echo $new;
		if (count($new) == 0) {
			return;
		}
		foreach ($new as $n) {
			$sql =  "INSERT INTO mr_movie_details (imdb_id, date_added, filename, media_path) VALUES ".
					"('0000000', NOW(),". formatSQL($n['title']).", ". formatSQL($n['media_path']). ")";
			if ( $output == TRUE ) {
				if (mysql_query($sql)) {
					$this->SetSplash("Added <strong>{$n['title']}</strong> to database!");
				} else {
					$this->SetSplash("Couldn't add <strong>{$n['title']}</strong> to database!");
				}
			} 
		}
		if ( $output == TRUE ) {
			$html  = "<p>Click <a href=\"/movies/add/lookup/imdbids\">here</a>".
				 " to lookup IMDB IDs for these titles.<br /><strong>Note:</strong> This can take a while if there are several movies to lookup.</p>\n";
	
			$this->AddHTML($html);
		}
				
	}

	function AddNewShows() {
		$new = $this->GetNewShows();
		if ( count( $new ) == 0 ) {
			exit;
		}
		foreach ( $new as $n ) {
			$sql;
		}
	}
	
	function AddDetails() {
		
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
				$media_path = $r['media_path'];
			
				$movie = new imdb( $imdbid );
				$movie->setid( $imdbid );
				
				$title      = $movie->title();
				$year       = $movie->year();
				$runtime    = $movie->runtime();
				$genre      = $movie->genre();
				$all_genres = $movie->genres();
				$mpaar 		= $movie->mpaa ();
				$tagline	= $movie->tagline();
				$directors	= $movie->director();
				$producers	= $movie->producer();
				$writers	= $movie->writing();
				$cast		= $movie->cast();
				//$plot 		= $movie->plot();
				//$short_plot = substr( $plot[0], 0, 255 );
				
	
	
				//if ( strlen( $plot[0] ) > 255 )
				//	$short_plot .= ' [...]';
				
				if ( $this->CheckGenre( $genre ) == FALSE )
					$this->AddGenre( $genre );
		
				foreach ( $all_genres as $g ) {
					if ( $this->CheckGenre( $g ) == FALSE )
						$this->AddGenre( $g );
		
					// Update movie_genres
					$mg_sql = "INSERT INTO mr_movie_genres (movie_id, genre_id) VALUES ".
						" ($movie_id, ". $this->GetGenreID( $g ) .")";
					echo $mg_sql;
					$mg_res = mysql_query( $mg_sql );
				}
	
				
	// -- THIS NEEDS TO BE EDITED..
	
				$md_sql =	'UPDATE mr_movie_details SET '.
							' date_updated = NOW(), '.
							' title = '. formatSQL( $title ). ', '.
							' year = '. formatSQL( $year ). ', '.
							' runtime = '. formatSQL( $runtime ). ', '.
							' mpaa_rating = '. formatSQL( $mpaar['USA'] ). ', '.
							' tagline = '. formatSQL( trim( $tagline ) ) . ' '.
							" WHERE movie_id = $movie_id LIMIT 1";
	
	
				$producersSQL = "INSERT INTO mr_movie_producers (movie_id, producer_id, producer_role) VALUES ";
				for ( $i = 0; $i < count( $producers ); $i++ ) {
					$checkSQL = "SELECT * FROM mr_producers WHERE producer_name LIKE ". formatSQL( trim( $producers[$i]['name'] ), TRUE );
					$checkRES = mysql_query( $checkSQL );
					if ( mysql_num_rows( $checkRES ) == 0 )  {
						mysql_query( "INSERT INTO mr_producers (producer_name) VALUES (". formatSQL( trim( $producers[$i]['name'] ) ) .")" );
					} else {
						while ( $r = mysql_fetch_array( $checkRES ) )  {
							$producer_id = $r['producer_id'];
						}
					}
					$producersSQL .= "( $movie_id, $producer_id, ". formatSQL( trim( $producers[$i]['role'] ) ) ."), ";
				}
				$producersSQL = substr( $producersSQL, 0, -2 );
				//echo $producersSQL;
				@mysql_query( $producersSQL );// or die("Couldn't add producers! ");
	
	            $writersSQL = "INSERT INTO mr_movie_writers (movie_id, writer_id) VALUES ";
	            for ( $i = 0; $i < count( $writers ); $i++ ) {
	                $checkSQL = "SELECT * FROM mr_writers WHERE writer_name LIKE ". formatSQL( trim( $writers[$i]['name'] ), TRUE );
	                $checkRES = mysql_query( $checkSQL );
	                if ( mysql_num_rows( $checkRES ) == 0 ) {
	                    mysql_query( "INSERT INTO mr_writers (writer_name) VALUES (". formatSQL( trim( $writers[$i]['name'] ) ) .")" );
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
	                $checkSQL = "SELECT * FROM mr_directors WHERE director_name LIKE ". formatSQL( trim( $directors[$i]['name'] ), TRUE );
	                $checkRES = mysql_query( $checkSQL );
	                if ( mysql_num_rows( $checkRES ) == 0 ) {
	                    mysql_query( "INSERT INTO mr_directors (director_name) VALUES (". formatSQL( trim( $directors[$i]['name'] ) ) .")" );
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
	                $checkSQL = "SELECT * FROM mr_actors WHERE actor_name LIKE ". formatSQL( trim( $cast[$i]['name'] ), TRUE );
	                $checkRES = mysql_query( $checkSQL );
	                if ( mysql_num_rows( $checkRES ) == 0 ) {
	                    mysql_query( "INSERT INTO mr_actors (actor_name) VALUES (". formatSQL( trim( $cast[$i]['name'] ) ) .")" );
	                } else {
	                    while ( $r = mysql_fetch_array( $checkRES ) )  {
	                        $actor_id = $r['actor_id'];
	                    }
	                }
	                $castSQL .= "( $movie_id, $actor_id, ". formatSQL( trim( $cast[$i]['role'] ) ) ."), ";
	            }
	            $castSQL = substr( $castSQL, 0, -2 );
	            //echo $castSQL;
				@mysql_query( $castSQL );// or die("Couldn't add cast!");
	
	
	// directors, writers, cast
	
		
				$ratingSQL = "INSERT INTO mr_movie_ratings (movie_id) VALUES ($movie_id)";
				$ratingRes = mysql_query( $ratingSQL );
				
				$md_res = mysql_query( $md_sql );
		
				$this->SetSplash("Info for <strong><a href=\"/movies/edit/$movie_id\">$title</a></strong> added to database!");
		
			}
			
			//$this->AddHTML("<p><a href=\"/movies/edit/$movie_id\">View Movie Details</a></p>\n");
		} else {
			$this->AddHTML("<p>There are no movies with IMDB IDs but without details downloaded.</p>\n");
		}
	}
	
	function GetMPAA($imdb_id) {
        $movie = new imdb( $imdb_id );
        $movie->setid( $imdb_id );
        $mpaar = $movie->mpaa();
        return $mpaar['USA'];	
	}
	
	function EditMovie($movie_id) {
		
		if ( isset( $_POST['submit'] ) ) { // if they clicked submit update the db 
	
			$sqlUpdate = "UPDATE mr_movie_details ".
						 " SET title = ". formatSQL( $_POST['title'] ) .", ".
						 " imdb_id = '". formatSQL( $_POST['imdb_id'] ) ."', ".
						 " year = ". formatSQL( $_POST['year'] ) .", ". 
						 " runtime = ". formatSQL( $_POST['runtime'] ) .", ".
						 " mpaa_rating = ". formatSQL( $_POST['mpaa_rating'] ) .", ".
						 " tagline = ". formatSQL( $_POST['tagline'] ) .", ".
						 " date_updated = now() ".
						 " WHERE movie_id = {$_POST['movie_id']}".
						 " LIMIT 1";
		
		
			$sqlUpdateG1 = "DELETE FROM mr_movie_genres WHERE movie_id = {$_POST['movie_id']}";
			$sqlUpdateG2 = "INSERT INTO mr_movie_genres (movie_id, genre_id) VALUES ";
		
			foreach ( $_POST['genres'] as $g )
				$sqlUpdateG2 .= "({$_POST['movie_id']}, $g),";
		
			$sqlUpdateG2 = substr( $sqlUpdateG2, 0, ( strlen( $sqlUpdateG2 ) - 1 ) );
		
			$res1 = mysql_query( $sqlUpdate )	 	or die ( "Coudn't update {$_POST['title']}!  MySql error: ". mysql_error() );
			$res3 = mysql_query( $sqlUpdateG1 ) 	or die ( "Coudn't remove existing genres!  MySql error: ". mysql_error() );
			$res4 = mysql_query( $sqlUpdateG2 ) 	or die ( "Coudn't add genres!  MySql error: ". mysql_error() );
		
			$this->SetSplash( "Updated ". stripslashes( $_POST['title'] ) );
			//$this->PageSplash();
		} 
		$sqlMovie = "SELECT m.movie_id AS mid, imdb_id, ".
		            " filename, ".
		            " name AS title, ".
		            " version_name AS version, ".
		            " year, ".
		            " runtime, ".
					" rating, ".
		            " summary, ".
		            " genre_name AS genre " .
		            " FROM movies AS m, ".
					" mpaa_ratings AS mr, ".
		            " movie_details AS md, ".
		            " genres AS g, ".
		            " movie_versions AS mv ".
		            " WHERE m.movie_id = md.movie_id ".
		            " AND md.genre_id = g.genre_id ".
		            " AND m.version_id = mv.version_id ".
		            " AND md.mpaa_rating = mr.mpaa_rating_id ".
		            " AND m.have_details = 'Y' ".
		            " AND m.movie_id = '$movie_id' ";
		$sqlMovie = "SELECT * FROM mr_movie_details WHERE movie_id = $movie_id LIMIT 1";
		$resMovie = mysql_query( $sqlMovie ) or die("No movie found with that ID.");
		
		while ( $r = mysql_fetch_array( $resMovie ) ) {
			$movie_id 	 = $r['movie_id'];
			$imdb_id 	 = $r['imdb_id'];
			$filename 	 = $r['filename'];
			$title 		 = $r['title'];
			$version 	 = $r['version'];
			$year 		 = $r['year'];
			$runtime 	 = $r['runtime'];
			$mpaa_rating = $r['mpaa_rating'];
			$tagline 	 = $r['tagline'];
		}

		$movie_form = <<<EOT
	<form id="movie_details" name="movie_details" method="post" action="/movies/edit/$movie_id">
		<fieldset>
			<legend>Title</legend>
			<input type="text" name="title" id="mtitle" maxlength="200" value="$title" />
		</fieldset>
		<fieldset>
			<legend>IMDB ID</legend>
			<input type="text" name="imdb_id" id="imdb_id" maxlength="7" value="$imdb_id" />
		</fieldset>
		<fieldset>
			<legend>Year Released</legend>
			<select name="year" id="year">

EOT;
		for ( $i = date('Y'); $i >= 1900; $i-- ) 
			$movie_form .= "\t\t\t\t<option value=\"$i\"".( $i == $year ? ' selected="selected"' : '' ).">$i</option>\n"; 

		$movie_form .= <<<EOT
			</select>
		</fieldset>
		<fieldset>
			<legend>Runtime (Minutes)</legend>
			<input type="text" name="runtime" id="runtime" maxlength="3" value="$runtime" />
		</fieldset>
		<fieldset>
			<legend>MPAA Rating</legend>
			<select name="mpaa_rating">

EOT;
		$i = 1;
		foreach( array( 'G', 'PG', 'PG-13', 'R', 'NC-17', 'Unrated' ) as $mpaa ) {
			$movie_form .= "\t\t\t\t<option value=\"$i\"". ( $mpaa == $mpaa_rating ? ' selected="selected"' : '' ) .">$mpaa </option>\n";
			$i++;
		}
		$movie_form .= <<<EOT
			</select>
		</fieldset>
		<fieldset>
			<legend>Genres</legend>
			<div id="update-genre">

EOT;
		$movie_form .= $this->UpdateGenresForm( $this->GetMovieGenres( $movie_id ) );

		$movie_form .= <<<EOT
			</div>
		</fieldset>
		<fieldset>
			<legend>Summary</legend>
			<textarea name="tagline" id="tagline" rows="6" cols="50">$tagline</textarea>
		</fieldset>
		<p class="submit" style="text-align: left">
			<input type="hidden" name="movie_id" value="$movie_id" />
			<input type="submit" name="submit" value="Update Database" onclick="return validate()" />
			<input type="button" name="delete" id="delete" value="Delete" onclick="javascript:window.location='http://cp.movierack.net/movies/delete/$movie_id'" />
			<input type="button" name="goback" id="goback" value="Go Back" onclick="javascript:window.location='http://cp.movierack.net/movies'" />
		</p>
	</form>

EOT;
		$this->PageHTML = $movie_form;
	}

	function DeleteMovie( $movie_id = NULL ) {
	// delete from mysql
			
		if ( isset( $_POST['submit'] ) ) {
			if ( $_POST['verify'] == 'Yes' ) {
				$sql = array(	"DELETE FROM mr_movie_details WHERE movie_id = $movie_id LIMIT 1", 
								"DELETE FROM mr_movie_ratings WHERE movie_id = $movie_id LIMIT 1",
								"DELETE FROM mr_movie_genres WHERE movie_id = $movie_id ",
								"DELETE FROM mr_movie_actors WHERE movie_id = $movie_id ",
								"DELETE FROM mr_movie_directors WHERE movie_id = $movie_id ",
								"DELETE FROM mr_movie_producers WHERE movie_id = $movie_id ",
								"DELETE FROM mr_movie_writers WHERE movie_id = $movie_id "
							);
				foreach ( $sql as $s ) {
					if ( ! mysql_query( $s ) ) { 
						$this->SetSplash("Couldn't <strong>$s</strong>.");
					} else {
						$this->SetSplash("Movie Deleted");
						break;
					}
				}
			} else { // didnt click yes

			}
		}
		
		$info = $this->GetMovieInfo( $movie_id );
		if ( isset( $_POST['submit'] ) && $_POST['verify'] != 'Yes' ) {
			$extra = "<p><strong>You must click yes to delete this movie!</strong></p>";
		}
		$del_form = <<<EOT
<form name="delete" id="delete" method="post" action="/movies/delete/$movie_id">
	<p>Are you sure you want to delete: <strong>{$info['title']}</strong>?</p>
	<p><em>Note: You must delete the movie from the media server, or move it to _dump</em></p>
	$extra
	<p>
		<label>Yes <input name="verify" type="checkbox" value="Yes" /></label>
	</p>
	<p>
		<input type="submit" name="submit" value="Delete" />
		<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1)" />
	</p>

</form>
EOT;
		if ( isset( $_POST['submit'] ) && $_POST['verify'] != 'Yes' ) {
			$this->AddHTML( $del_form );
		} elseif ( isset( $_POST['submit'] ) && $_POST['verify'] == 'Yes' ) {

		} else {
			$this->AddHTML( $del_form );
		}
	}
	
	function GetMovieInfo($movie_id) {
		$sql = "SELECT * FROM mr_movie_details WHERE movie_id = $movie_id LIMIT 1"; 
		$res = mysql_query( $sql );
		while ( $r = mysql_fetch_array( $res ) ) {
			$info['movie_id'] 	 = $r['movie_id'];
			$info['imdb_id'] 	 = $r['imdb_id'];
			$info['filename'] 	 = $r['filename'];
			$info['title'] 		 = $r['title'];
			$info['version'] 	 = $r['version'];
			$info['year'] 		 = $r['year'];
			$info['runtime'] 	 = $r['runtime'];
			$info['mpaa_rating'] = $r['mpaa_rating'];
			$info['tagline'] 	 = $r['tagline'];	
		}

		return $info;
	}
	
	function ShowServerStats() {
		$mTotal = $this->GetMovieCount();
		$mNew = count( $this->GetNewMovies() );
		$mIMDB = count( $this->CheckNoIMDBID() );
		$mDetails = count( $this->CheckNoDetails() );

		$emNew = "There ". ( $mNew == 1 ? 'is ' : 'are ' ). " $mNew new ". ( $mNew == 1? 'movie' : 'movies' ) .".";
		$emIMDB = "There ". ( $mIMDB == 1 ? 'is ' : 'are ' ). " $mIMDB ". ( $mIMDB == 1? 'movie' : 'movies' ) ." without IMDB IDs.";
		$emDetails = "There ". ( $mDetails == 1 ? 'is ' : 'are ' ). " $mDetails ". ( $mDetails == 1? 'movie' : 'movies' ) ." without details.";

		$html = <<<EOT
<div id="server-overview">
	<div class="stats-left">
		<div class="content-box">
			<h3>Movie Statistics</h3>
			<p>There are $mTotal movies total.</p>
			<p>$emNew</p>
			<p>$emIMDB</p>
			<p>$emDetails</p>
		</div>
		<div class="content-box">
			<h3>Show Statistics</h3>
		</div>
	</div>
	<div class="stats-right">
		<div class="content-box">
			<h3>Media Server Statistics </h3>

EOT;
		echo $html;
		include "http://media.movierack.net/mr_drive_stats.php";
		$html2 = <<<EOT
		</div>
	</div>
	<br class="clear" />
</div>

EOT;

		echo $html2;
	}
	function PageHeader($page_title = NULL, $h2 = FALSE) {
		include INCLDIR .'page_header.php';
	}
	
	function SetSplash($splash) {
		$this->Splash .= "\t<div class=\"splash\">$splash</div>\n";
	}
	
	function PageSplash() {
		echo $this->Splash;
	}
	
	function PageFooter() {
		include INCLDIR .'page_footer.php';
	}
	
	function GetContent($page) {
	// get content from mysql db
	}
	
	
	function Page($page) {
	// index
	// movies
	// reviews
	// news
	// users
	// profile
		switch($page) {
			case 'index':
				$this->PageHeader();
				//if ( !$this->MakeMovies() ) echo "boo"; else echo "yay";
				$this->movie_init();
				//$this->GetContent('main');
				$this->ShowServerStats();
				echo "<p>Hi there, {$_SERVER['REMOTE_USER']}.</p>\n".
					"<p>Lets get started: </p>\n".
					"<ul>\n".
					"\t<li><a href=\"/movies/manage\">Manage Movies</a></li>\n".
					"\t<li><a href=\"/shows/manage\">Manage Shows</a></li>\n".
					"\t<li><a href=\"/news/manage\">Manage News</a></li>\n".
					"\t<li><a href=\"/reviews/manage\">Manage Reviews</a></li>\n".
					"\t</ul>".
					"<br class=\"clear\" />\n";
				$this->PageSplash();
				$this->PageFooter();
			break;
	
			case 'movies':
				$this->PageHeader( ( isset( $_GET['action'] ) ? ucfirst( $_GET['action'] ) : 'Manage' ) .' Movie'. (  $_GET['action'] == 'add' || $_GET['action'] == 'manage'  ? 's' : '' ) , TRUE);
				$this->ManageMovies( $_GET['action'] );
				$this->PageFooter();
			break;

			case 'shows':
				$this->PageHeader( ( isset( $_GET['action'] ) ? ucfirst( $_GET['action'] ) : 'Manage' ) .' Show'. (  $_GET['action'] == 'add' || $_GET['action'] == 'manage'  ? 's' : '' ) , TRUE);
				$this->ManageShows( $_GET['action'] );
				$this->PageFooter();
			break;

			case 'users':
				$this->PageHeader( ( isset( $_GET['action'] ) ? ucfirst( $_GET['action'] ) : 'Manage' ) .' User'. (  $_GET['action'] == 'add' || $_GET['action'] == 'manage'  ? 's' : '' ) , TRUE);
				$this->ManageUsers( $_GET['action'] );
				$this->PageFooter();
			break;
		}
	}
	
	function ManageMovies($action = NULL) {
	// browse
	// add
	// edit
	// delete		

		switch($action) {
			case 'browse':
			case NULL:
			default:
				$this->BrowseMovies($_GET['start']);
				echo $this->PageHTML;
			break;

			case 'add':
				switch($_GET['lookup']) {
					case 'imdbids' : 
						if ( isset( $_POST['submit'] ) ) {
							$this->AddIMDBIDS();
						} else {
							$this->LookupIMDBIDS();
						}
					break;

					case 'details' : 
						$this->AddDetails();
					break;

					case NULL : 
						$this->movie_init();

						if ( count( $this->GetNewMovies() ) > 0 ) {
							$this->AddNewMovies();
						} else {
							$this->AddHTML("<p>There are no new movies.</p>\n");
						}

						if ( count( $this->CheckNoIMDBID() ) > 0 ) {
							$this->AddHTML("<p>There are ". count( $this->CheckNoIMDBID() ) ." movies without IMDB IDs.</p>\n");
							$this->AddHTML("<p>Click <a href=\"/movies/add/lookup/imdbids\">here</a> to look up IDs.</p>\n");
						} else {
							$this->AddHTML("<p>There are no movies without IMDB IDs.</p>\n");
						}

						if ( count( $this->CheckNoDetails() ) > 0 ) {
							$this->AddHTML("<p>There are ". count( $this->CheckNoDetails() ) ." movies without detailss.</p>\n");
							$this->AddHTML("<p>Click <a href=\"/movies/add/lookup/details\">here</a> to look up details.</p>\n");
						} else {
							$this->AddHTML("<p>There are no movies without details.</p>\n");
						}


					break;
				}
				$this->PageSplash();
				echo $this->PageHTML;
				
			break;

			case 'edit':
				if ( isset( $_GET['movie_id'] ) ) {
					$this->EditMovie($_GET['movie_id']);
					$this->PageSplash();
					echo $this->PageHTML;
				} else {
					$this->BrowseMovies($_GET['start']);
					echo $this->PageHTML;
				}
			break;

			case 'delete':
				$this->DeleteMovie($_GET['movie_id']);
				$this->PageSplash();
				echo $this->PageHTML;
			break;
		}
	}

	function BrowseMovies($start = NULL, $perpage = 25) {
    	$sql = "SELECT * FROM mr_movie_details WHERE date_updated IS NOT NULL ORDER BY filename";
		$paginate = new pagination($sql, $perpage, (isset($start) ? $start : 0));
		$pagination = $paginate->output();
		$res = mysql_query($pagination[0]);
		$c = 0;
		while ($r = mysql_fetch_array($res)) {
        	//$movie = substr( $r['filename'], 0, ( strlen( $r['filename'] ) - 4 ) );
        	$movie = substr( $r['filename'], 0, -4 );
			$movies[$c]['title'] = $movie;
			$movies[$c]['movie_id'] = $r['movie_id'];
			$c++;
		}

		$html = "<ol start=\"". ( $start > 1 ? ( ( ( $start - 1 ) * $perpage ) + 1 ) : '1' ) ."\">\n";
		for ( $i = 0; $i <= count( $movies ); $i++ ) {
			$html .= "\t<li>[<a href=\"/movies/edit/{$movies[$i]['movie_id']}\">Edit</a>] {$movies[$i]['title']}</li>\n";
			if ( $i == ( mysql_num_rows( $res ) - 1 ) ) {
				break;
			}
		}

	    $html .= "</ol>\n";
		$html .= $pagination[1];

		$this->AddHTML($html);
	}

	function BrowseShows( $start = NULL, $perpage = 25 ) {
		$sql = "SELECT * FROM mr_shows WHERE date_updated IS NOT NULL ORDER BY title";
		$paginate = new pagination( $sql, $perpage, ( isset( $start ) ? $start : 0 ) );
		$pagination = $paginate->output();
		$res = mysql_query( $pagination[0] );
		$c = 0;
		while ( $r = mysql_query( $res ) ) {
			$shows[$c]['title'] = $r['title'];
			$shows[$c]['show_id'] = $r['show_id'];
			$c++;
		}

		$html = "<ol start=\"". ( $start > 1 ? ( ( ( $start - 1 ) * $perpage ) + 1 ) : '1' ) ."\">\n";
		for ( $i = 0; $i <= count( $shows ); $i++ ) {
			$html .= "\t<li>[<a href=\"/shows/edit/{$shows[$i]['show_id']}\">Edit</a>] {$shows[$i]['title']}</li>\n";
			if ( $i == ( mysql_num_rows( $res ) - 1 ) ) {
				break;
			}
		}
		$html .= "</ol>\n";
		$html .= $pagination[1];
		$this->AddHTML( $html );
	}

	function EditShow( $show_id ) {

	}

	function ManageUsers($action = NULL) {

	}

	function AddUser() {
		/* --> ~/www/admin/wp-includes/registration_functions.php
		ID
		user_login
		user_pass
		user_nicename
		user_email
		user_url
		user_registered
		user_activation_key
		user_status
		display_name
		*/
	
	}

	function EmailUser( $user_id, $data = array() ) {
		
	}

	function DeleteUser($user_id) {

	}

	function EditUser($user_id) {
		$sql = "SELECT * FROM mr_users WHERE ID = $user_id LIMIT 1";
		$res = mysql_query( $sql );
		while ( $r = mysql_fetch_array( $res ) ) {
			$user_email = $r['user_email'];
		}
	}

	function ManageShows( $action = null ) {
		switch( $action ) {
			case 'browse':
			case NULL:
			default:
				$this->BrowseShows( $_GET['start'] );
				echo $this->PageHTML;
			break;

			case 'edit':
				if ( isset( $_GET['show_id'] ) ) {
					$this->EditShow( $_GET['show_id'] );
				} else {
					$this->BrowseShows( $_GET['start'] );
					echo $this->PageHTML;
				}
			break;

			case 'delete':
					$this->DeleteShow( $_GET['show_id'] );
					$this->PageSplash();
					echo $this->PageHTML;
			break;


			case 'add':
				
				$this->show_init();

				$newShows = $this->GetNewShows();
				//$newEpisodes = $this->GetNewEpisodes();

				if ( count( $newShows ) > 0 ) {
					$this->AddHTML( "<p>There are ". count( $newShows ) ." new shows.</p>\n" );
					$this->AddHTML( "<p>Click <a href=\"/shows/add\">here</a> to add them to the database.</p>\n" );
				} else {
					$this->AddHTML( "<p>There are no new shows.</p>\n" );
				}
	
				/*
				if ( count( $newEpisodes() ) > 0 ) {
					$this->AddHTML( "<p>There are ". count( $newEpisodes ) ." new episodes.</p>\n" );
					$this->AddHTML( "<p>Click <a href=\"/shows/add/episodes\">here</a> to add them to the database.</p>\n" );
				} else {
					$this->AddHTML( "<p>There are no new episodes.</p>\n" );
				}
				*/
				echo $this->PageHTML;
			break;
		
		}
	}
} // End Class

