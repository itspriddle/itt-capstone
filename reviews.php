<?php require_once '/home/priddle/movierack-config.php'; ?>
<?php $page_title = 'Movie Reviews'; ?>
<?php include_once INCLDIR .'page-header.php'; ?>
<div id="reviews">
<h2>Movie Reviews</h2>
  <div style="padding: 10px">
  <?php echo get_news_feed( 'reviews' ); ?>
  </div>
</div>
<?php include_once INCLDIR .'page-footer.php'; ?>
