// Display LinkedIn-style list of "contacts" (e.g., coauthors) 

function display_coauthors(s)
{	
	// The parentheses are vital, see 
	http://www.bennadel.com/blog/99-JSON-Minor-But-VERY-Important-Detail-Creating-Syntax-Error-Invalid-Label.htm
	var obj = eval("(" + s + ")");
	var letters = new Array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

	var list_html = '';

	list_html += '<ol style="list-style-type:none;">';

	var pos = 0;

	// Get count of A
	var numA = 0;
	if (obj.coauthors.length > 0)
	{
		var i = 0;
		while (obj.coauthors[i].lastname.charAt(0).toUpperCase() == 'A')
		{
			i++;
			numA++;
		}
	}
	
	if (numA > 0)
	{
		list_html += '<li>';
		list_html += '<h3><a name="A">A</a></h3>';
		list_html += '<ul style="list-style-type:none;border-bottom:1px dotted rgb(190,190,190);padding-bottom:2px">';
	}

	var count = new Array();
	for (i=0;i<26;i++)
	{		
		count[i] = 0;
	}
	
	for(i=0;i<obj.coauthors.length;i++)
	{
		var firstLetter = obj.coauthors[i].lastname.charAt(0);
		firstLetter = firstLetter.toUpperCase();

		if (firstLetter != letters[pos])
		{
			list_html += '</ul>';
			list_html += '</li>';
		}
		
		//pos = 0;
		while (firstLetter != letters[pos])
		{
			pos++;
			
			if (firstLetter == letters[pos])
			{
				list_html += '<li>';
				list_html += '<h3><a name="' + letters[pos] + '">' + letters[pos] + '</a></h3>';
				list_html += '<ul style="list-style-type:none;border-bottom:1px dotted rgb(190,190,190);padding-bottom:2px">';
			}
		}
//		list_html += '<li><a href="display_author.php?id=' + obj[i].id + '">' + obj[i].lastname + ',&nbsp;' + obj[i].forename  + '</a><span style="float:right;margin-right:20px;">' + obj[i].count + '</span></li>';
		list_html += '<li><a href="' + gWebRoot + 'author/' + obj.coauthors[i].id + '">' + obj.coauthors[i].lastname + ',&nbsp;' + obj.coauthors[i].forename  + '</a><span style="float:right;margin-right:20px;">' + obj.coauthors[i].count + '</span></li>';

		count[obj.coauthors[i].lastname.charCodeAt(0) - 65]++;
	}
	list_html += '</ol>';


	// Index sidebar
	var index_html = '';
	for (i=0;i<26;i++)
	{		
		if( count[i] > 0)
		{
			
			// Safari needs the URL path for the # to work, otherwise we get bounced to the site root
			index_html += '<span style="display:block;font-size:12px;"><a href="' + gWebRoot + 'author/' + obj.author_id + '#' + letters[i] + '">' + letters[i] + '</a></span>';
		}
		else
		{
			index_html += '<span style="display:block;color:rgb(192,192,192);font-size:12px;">' + letters[i] + '</span>';
		}

	}
		
	document.getElementById('contact_index').innerHTML = index_html;
	document.getElementById('contact_list').innerHTML = list_html;
}
