<div id="content-middle">
    <div id="welcome">
	    <p><strong>Welcome to Movierack!</strong></p>
	    <p><strong>Movierack Beta Launch</strong> Movierack celebrates its beta launch on 
	        Feb 27th, 2007!</p>
	    <p>Sign up for our beta program before
	        June 1st, and get $10 in free downloads!</p>
	    </div>
    	<div style="width: 500px; clear: both; position: relative; top: 22px; border-top: 1px solid #3E3C3D; ">
		    <div id="home-new" style="float: left; height: 400px">
				<h2><span>New movies</span></h2>
				<div id="home-new-content" class="content" style="height: 400px; padding: 10px;"><?php echo new_movie_feed(); ?> 
				</div>
			</div>
		    <div id="home-feed" style="float: right; height: 400px">
				<h2><span>Newsfeed</span></h2>
				<div id="home-feed-content" class="content" style="padding: 10px;"><?php echo get_news_feed( 'all', 2 ); ?>
				</div>
			</div>
	    	<div style="clear: both;">&nbsp;</div>
		</div>
</div>
