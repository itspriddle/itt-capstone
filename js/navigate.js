var req;
var resDiv

function mr_movie_feed(start) {
        var url = "/inc/new-movies-home.php?start=" + start;
        if(window.XMLHttpRequest) {
                req = new XMLHttpRequest();
        } else if(window.ActiveXObject) {
                req = new ActiveXObject("Microsoft.XMLHTTP");
        }
		resDiv = 'home-new-content';
        req.open("GET", url, true);
        req.onreadystatechange = callback;
        req.send(null);
}

function mr_news_feed(start) {
        var url = "/inc/new-movies-home.php?start=" + start;
        if(window.XMLHttpRequest) {
                req = new XMLHttpRequest();
        } else if(window.ActiveXObject) {
                req = new ActiveXObject("Microsoft.XMLHTTP");
        }
        resDiv = 'home-feed-content';
        req.open("GET", url, true);
        req.onreadystatechange = callback;
        req.send(null);

}

function callback() {
        if(req.readyState == 4) {
                if(req.status == 200) {
                        response = req.responseText;
                        document.getElementById(resDiv).innerHTML = response;
                } else {
                        alert("There was a problem retrieving the data:\n" + req.statusText);
                }
        }
}
