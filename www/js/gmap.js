

function createMarker(point, title, label) 
{ 
	var marker = new GMarker(point, {title:title}); 
	GEvent.addListener(marker, 'click', 
		function() 
		{ 
			marker.openInfoWindowHtml(label, {maxWidth:350}); 
		}); 
	return marker; 
}    

