// BHL viewer

// Current page id
var gPageID = 0;

  
//--------------------------------------------------------------------------------------------------
// Called when user clicks on thumbnail. We display the image for the corresponding
// page.
function show_page(page_image_url, page_image_thumbnail_url, PageID) 
{
	if (gPageID != PageID)
	{
		// Display thumbnail so use sees preview while we load image
		$('page_image').src = page_image_thumbnail_url; 
	
		// Toggle selection in thumbnail list
		// Note that padding and borders must sum to same figure, and this matches
		// the default CSS in viewer.css
		$('thumbnail_image_' + PageID).setStyle({ border:'3px solid rgb(56,117,215)'});
		$('thumbnail_image_' + PageID).setStyle({ padding:'0px'});
		if (gPageID != 0)
		{
			$('thumbnail_image_' + gPageID).setStyle({ border:'1px solid rgb(146,146,146)'});
			$('thumbnail_image_' + gPageID).setStyle({ padding:'2px'});
		}
		gPageID = PageID;
			
		// Display page image
		$('page_image').src = page_image_url; 
				
		// Ensure thumbnail is visible
		//window.location = '#'; // original idea, but scrolls whole browser window position as well
		
		// Get position of element enclosing the thumbnail list (w.r.t. browser window)
		page_top = Position.page($('page'))[1];
		
		// Get coordinates of thumbnail (relative to thumbnail_list)
		thumbnail_top = $('thumbnail_image_' + gPageID).offsetTop;
		thumbnail_bottom = thumbnail_top + $('thumbnail_image_' + gPageID).getHeight();
		
		
		// Dimensions of list of thumbnails
		list_height = $('thumbnail_list').scrollHeight; // height of complete thumbnail_list (much of which may not be visible)
		view_height = $('thumbnail_list').getHeight(); // height of visible part thumbnail_list element (by default 600px)

		// What part of the thumbnail list is visible in the web browser window?
		// Get start and end of 600px view port on thumbnail_list
		list_top 			= Position.page($('thumbnail_list'))[1]; 	// where is top of thumbnail_list element w.r.t. browser window?
		list_top 			-= page_top; 								// offset by position of enclosing element on browser page
		list_visible_start 	= Math.abs(list_top);
		list_visible_end 	= list_visible_start + view_height;
		
		// Is any part of thumbnail in list view port?
		thumb_is_visible = 
			((thumbnail_top > list_visible_start) && (thumbnail_top < list_visible_end))
			||
			((thumbnail_bottom > list_visible_start) && (thumbnail_bottom < list_visible_end));
		
		// http://www.daniweb.com/forums/thread64541.html
		if (!thumb_is_visible)
		{
			$('thumbnail_list').scrollTop = $('thumbnail_image_' + gPageID).offsetTop;
		}
	}
}