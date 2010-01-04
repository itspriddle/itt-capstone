<?php require_once '/home/priddle/movierack-config.php'; ?>
<div id="search">
  <h2>Search Movies</h2>
  <form id="search-form" name="search-form" action="/search.php" method="post" style="padding: 10px;">
    <p><label for="search">Search</label>

    <input type="text" id="searchterm" name="searchterm" <?php if( isset( $_POST['searchterm'] ) ) echo "value=\"{$_POST['searchterm']}\""; ?>/></p>
<!--
    <div class="advanced">
      <p>Search for: <br />
      <label><input type="checkbox" name="filter[]" value="titles" <? if ( @in_array( 'titles', $_POST['filter'] ) || !is_array( $_POST['filter'] ) ) echo 'checked="checked" ';?> />Titles</label>
      <label><input type="checkbox" name="filter[]" value="actors" <? if ( @in_array( 'actors', $_POST['filter'] ) ) echo 'checked="checked" '; ?>/>Actors</label>
      <label><input type="checkbox" name="filter[]" value="producers" <? if ( @in_array( 'producers', $_POST['filter'] ) ) echo 'checked="checked" '; ?>/>Producers</label>
      <label><input type="checkbox" name="filter[]" value="writers" <? if ( @in_array( 'writers', $_POST['filter'] ) ) echo 'checked="checked" '; ?>/>Writers</label> </p>

      <p>Genres</p>
      <?=show_filter_genres( $_POST['genres'] ) ?>
    </div>
-->
    <p><input type="submit" name="submit" value="Search" /></p>

  </form>
</div>

