<?php require_once '/home/priddle/movierack-config.php'; ?>
<?php $page_title = "Search Results for: {$_POST['searchterm']}"; ?>
<?php include_once INCLDIR .'page-header.php'; ?>
<div id="search-results">
<h2>Search Results for: <?=$_POST['searchterm'] ?></h2>
<div class="search">
  <form id="search-form" name="search-form" action="/search.php" method="post">
    <label for="search">Search</label>

    <input type="text" id="searchterm" name="searchterm" <?php if( isset( $_POST['searchterm'] ) ) echo "value=\"{$_POST['searchterm']}\""; ?>/>

	<a href="#" onclick="javascript:show_advanced_search()">Advanced Search Options</a>
    <div id="advanced-search">
      <p>Search for: <br />
      <label><input type="checkbox" name="filter[]" value="titles" <? if ( @in_array( 'titles', $_POST['filter'] ) || !is_array( $_POST['filter'] ) ) echo 'checked="checked" ';?> />Titles</label>
      <label><input type="checkbox" name="filter[]" value="actors" <? if ( @in_array( 'actors', $_POST['filter'] ) ) echo 'checked="checked" '; ?>/>Actors</label>
      <label><input type="checkbox" name="filter[]" value="producers" <? if ( @in_array( 'producers', $_POST['filter'] ) ) echo 'checked="checked" '; ?>/>Producers</label>
      <label><input type="checkbox" name="filter[]" value="writers" <? if ( @in_array( 'writers', $_POST['filter'] ) ) echo 'checked="checked" '; ?>/>Writers</label> </p>
      <p>Genres</p>
      <?=show_filter_genres( $_POST['genres'] ) ?>
    </div>
    <input type="submit" name="submit" value="Search" />

  </form>
</div>
<div class="results">
<?php 

if ( $_POST['searchterm'] != '' ) { 
	$search_term = $_POST['searchterm'];


	$genres = $_POST['genres'];
	if ( count( $genres ) == 0 ) $genres = 'ALL';	
	$sql = 	"SELECT DISTINCT(movie_id) FROM mr_movie_genres ";

	if ( $genres != 'ALL' ) {
		$sql .= " WHERE ";

		foreach( $genres as $g )
			$sql .= "genre_id = $g OR ";
		$sql = substr( $sql, 0, ( strlen( $sql ) - 3 ) );
	}

	//echo $sql;

	$res = mysql_query( $sql );
	if ( mysql_num_rows( $res ) > 0 ) {
		while ($r = mysql_fetch_array( $res ) )
			$results[] = $r['movie_id'];

		$filters = $_POST['filter'];
		if ( count( $_POST['filter'] ) == 0 )
			$filters[0] = 'titles';

		foreach( $filters as $f ) {
			switch( $f ) {
				case 'titles':
					$sqlf = "SELECT DISTINCT md.movie_id, imdb_id, title FROM mr_movie_details AS md ".
				            " WHERE md.title LIKE ".
							format_sql( $search_term, TRUE ).
							" AND ( ";
					$f = "Movie Titles";
					break;
				case 'actors':
					$sqlf = "SELECT DISTINCT md.movie_id, imdb_id, title FROM mr_movie_details AS md, mr_actors AS a, mr_movie_actors AS ma ".
							" WHERE ma.movie_id = md.movie_id AND ma.actor_id = a.actor_id AND a.actor_name LIKE ".
							format_sql( $search_term, TRUE ).
							" AND ( ";
					break;
				case 'producers':
					$sqlf = "SELECT DISTINCT md.movie_id, imdb_id, title FROM mr_movie_details AS md, mr_producers AS p, mr_movie_producers AS mp ".
							" WHERE mp.movie_id = md.movie_id AND mp.producer_id = p.producer_id AND p.producer_name LIKE ".
							format_sql( $search_term, TRUE ).
							" AND ( ";
					break;
				case "writers":
					$sqlf = "SELECT DISTINCT md.movie_id, imdb_id, title FROM mr_movie_details AS md, mr_writers AS w, mr_movie_writers AS mw ".
							" WHERE mw.movie_id = md.movie_id AND mw.writer_id = w.writer_id AND w.writer_name LIKE ".
							format_sql( $search_term, TRUE ).
							" AND ( ";
					break;
			}
			foreach( $results as $rs )
				$sqlf .= "md.movie_id = $rs OR ";
			$sqlf = substr( $sqlf, 0, ( strlen( $sqlf ) - 3 ) ) .' )';
			//echo "sqlf: $sqlf |||";

			$resf = mysql_query( $sqlf. " ORDER BY title" );
			$total_res = @mysql_num_rows( $resf );
			if ( $total_res > 0 ) {
				unset( $movies );

		        $html = "<h3>$total_res results found in ". ucfirst( $f ) ." for $search_term</h3>\n";
		        $html .= "<table id=\"movietbl\">\n";
				$c = 0;
		        while( $r = mysql_fetch_array( $resf ) ) {
		            $movies[$c]['movie_id'] = $r['movie_id'];
		            $movies[$c]['imdb_id']  = $r['imdb_id'];
		            $movies[$c]['title']    = $r['title'];
					$c++;
		        }
				$table_heading = "$total_res results found in <strong>". ucfirst( $f ) ."</strong> for <strong>\"$search_term\"</strong>";
				echo print_movies( $movies, $table_heading );

			} else {// no results
			}
		}
	} else {// no results 

	}
	
}



?>
</div>
</div>
<?php include_once INCLDIR .'page-footer.php'; ?>
