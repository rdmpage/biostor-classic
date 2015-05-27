//--------------------------------------------------------------------------------------------------
function showPage(PageID)
{
	$('#page').html('<div style="background-color:white;text-align:center;">Click on image to hide</div><img src="http://biostor.org/bhl_image.php?PageID=' + PageID + '" width="400"></img>');
	$('#page').css('z-index', 10);
	$('#page').show();

}

//--------------------------------------------------------------------------------------------------
function hidePage()
{
	$('#page').hide();
}


//--------------------------------------------------------------------------------------------------
// Toggle a DOI actions based on whether we have a DOI in the form or not
function doi_enable()
{
	var value = $('#doi').val();
	$('#doi_lookup').attr('disabled', (value == ''));
	$('#doi_link').attr('disabled', (value == ''));
}

//--------------------------------------------------------------------------------------------------
// Toggle Handle actions based on whether we have a Handle in the form or not
function hdl_enable()
{
	var value = $('#hdl').val();
	$('#hdl_link').attr('disabled', (value == ''));
}


//--------------------------------------------------------------------------------------------------
// Toggle Handle actions based on whether we have a Handle in the form or not
function pmid_enable()
{
	var value = $('#pmid').val();
	$('#pmid_link').attr('disabled', (value == ''));
}

//--------------------------------------------------------------------------------------------------
// Toggle URL actions based on whether we have a URL in the form or not
function url_enable()
{
	var value = $('#url').val();
	$('#url_link').attr('disabled', (value == ''));
}

//--------------------------------------------------------------------------------------------------
// Toggle PDF actions based on whether we have a PDF in the form or not
function pdf_enable()
{
	var value = $('#pdf').val();
	$('#pdf_link').attr('disabled', (value == ''));
}

//--------------------------------------------------------------------------------------------------
// Toggle PDF actions based on whether we have a PDF in the form or not
function biostor_enable()
{
	var value = $('#biostor').val();
	$('#biostor_link').attr('disabled', (value == ''));
}

//--------------------------------------------------------------------------------------------------
// Send user to reference identified by DOI
function doi_go()
{
	var value = $('#doi').val();
	if (value != '')
	{
		if (value.match(/^10\.\d+\//))
		{
			window.open('http://dx.doi.org/' + value);
		}
		else
		{
			alert('"' + value + '" is not a DOI');
		}
	}
}

//--------------------------------------------------------------------------------------------------
// Send user to reference identified by Handle
function handle_go()
{
	var value = $('#hdl').val();
	if (value != '')
	{
		if (value.match(/\d+\//))
		{
			window.open('http://hdl.handle.net/' + value);
		}
		else
		{
			alert('"' + value + '" is not a Handle');
		}
	}
}

//--------------------------------------------------------------------------------------------------
// Send user to reference identified by PMID
function pmid_go()
{
	var value = $('#pmid').val();
	if (value != '')
	{
		if (value.match(/^\d+$/))
		{
			window.open('http://www.ncbi.nlm.nih.gov/pubmed/' + value);
		}
		else
		{
			alert('"' + value + '" is not a PMID (must be a number)');
		}
	}
}

//--------------------------------------------------------------------------------------------------
// Send user to reference identified by BioStor id
function biostor_go()
{
	var value = $('#biostor').val();
	if (value != '')
	{
		if (value.match(/^\d+$/))
		{
			window.open('http://biostor.org/reference/' + value);
		}
		else
		{
			alert('"' + value + '" must be a number');
		}
	}
}

//--------------------------------------------------------------------------------------------------
// Send user to reference identified by URL
function url_go()
{
	var value = $('#url').val();
	if (value != '')
	{
		if (!value.match(/^http:\/\//))
		{
			value = 'http://' + value;
			$('#url').val(value);
		}
		window.open(value);
	}
}

//--------------------------------------------------------------------------------------------------
// Send user to PDF
function pdf_go()
{
	var value = $('#pdf').val();
	if (value != '')
	{
		if (!value.match(/^http:\/\//))
		{
			value = 'http://' + value;
			$('#pdf').val(value);
		}
		window.open(value);
	}
}


//--------------------------------------------------------------------------------------------------
function validateIssn()
{
	var ok = false;
	var value = $('#issn').val();
	
	if (value == '') return ok;
	
	var issnPattern = /^[0-9]{4}\-[0-9]{3}([0-9]|X)$/;
	if (issnPattern.test(value))
	{
		// ISSN is OK
		ok = true;
	}
	else
	{
		issnPattern = /^[0-9]{4}[0-9]{3}([0-9]|X)$/;
		if (issnPattern.test(value))
		{
			// Missing '-', so reformat to NNNN-NNNX
			issn = value.substring(0, 4);
			issn += '-';
			issn += value.substring(4);
			
			// Update form
			$('#issn').val(issn);
			ok = true;
		}
	}
	return ok;
}


//--------------------------------------------------------------------------------------------------
/*
function formToObject()
{
	var Reference = {
		identifiers []: ,
		authors : [],
		type: 'Unknown'
	};
	
	// Iterate over form elements 
	$(':input', '#openurl').each(function() {
		if (this.value != '')
		{
			switch (this.name)
			{	
			
				case 'rft_val_fmt':
					switch (this.value)
					{
						case 'info:ofi/fmt:kev:mtx:journal':
							Reference.type='Journal Article';
							break;
							
						default:
							break;
					}
					break;
			
				case 'authors':
					var authors = jQuery.trim(this.value);
					authors = authors.split("\n");
					for (var i in authors)
					{
						Reference.authors.push(authors[i]);
					}
					break;
					
				case 'publication_outlet':
				case 'volume':
				case 'series':
				case 'issue':
				case 'spage':
				case 'epage':
				case 'year':
				case 'url':
					Reference[this.name] = this.value;
					break;
					
				case 'doi':
					Reference.identifiers['doi'] = this.value;
					break;
				case 'hdl':
				case 'issn':
				case 'pmid':
					break;

				default:
					break;
			}
		}
	});
  
  	alert(Reference.identifiers.doi);
	

	return Reference;
}
*/

//--------------------------------------------------------------------------------------------------
function formToOpenurl()
{
	var openurl_keys=[];
	openurl_keys['rft_val_fmt'] = 'rft_val_fmt';
	openurl_keys['jtitle'] = 'rft.atitle';
	openurl_keys['title'] = 'rft.btitle';
	openurl_keys['authors'] = 'rft.au';
	openurl_keys['publication_outlet'] = 'rft.title';
	openurl_keys['volume'] = 'rft.volume';
	openurl_keys['issue'] = 'rft.issue';
	openurl_keys['spage'] = 'rft.spage';
	openurl_keys['epage'] = 'rft.epage';
	openurl_keys['year'] = 'rft.date';	
	
	var parameters=[];
	parameters.push('url_ver=Z39.88-2004');
	var delimiter = '&';
	
	// Iterate over form elements to get list of parameters for OpenURL
	$(':input', '#openurl').each(function() {
		if (this.value != '')
		{
			switch (this.name)
			{
				case 'title':
					// what kind of reference?
					switch ($('#rft_val_fmt').val())
					{
						case 'info:ofi/fmt:kev:mtx:journal':
							parameters.push(openurl_keys['jtitle'] + '=' + encodeURIComponent(this.value));
							break;
							
						default:
							parameters.push(openurl_keys[this.name] + '=' + encodeURIComponent(this.value));
							break;
					}
					break;
					
				case 'authors':
					var authorstring = this.value.replace(/^\s+|\s+$/g, '');
					var authors = authorstring.split("\n");
					for (var i in authors)
					{
						parameters.push(openurl_keys[this.name] + '=' + encodeURIComponent(authors[i]));
					}
					break;
					
				default:
					if (this.name in openurl_keys)
					{
						parameters.push(openurl_keys[this.name] + '=' + encodeURIComponent(this.value));
					}
					break;
			}
		}
	});
  
	var openurl = parameters.join(delimiter);

	return openurl;
}

//--------------------------------------------------------------------------------------------------
function canMakeOpenurl()
{
	var ok = true;
	
	switch ($('#rft_val_fmt').val())
	{
		case 'info:ofi/fmt:kev:mtx:journal':
			var publication_outlet 	= $('#publication_outlet').val();
			var volume 				= $('#volume').val();
			var spage 				= $('#spage').val();
			var issn 				= $('#issn').val();
			
			if (issn != '')
			{
				ok = validateIssn();
				if (!ok)
				{
					alert('"' + issn + '" is not a valid ISSN');
				}
			}
			if (ok)
			{
				ok = ((publication_outlet != '') || (issn != ''))
					&& (volume != '')
					&& (spage != '');
				if (!ok)
				{
					alert ("Not enough information for OpenURL");
				}

			}
			
			break;
			
		default:
			break;
	}
	
	return ok;
	
}

//--------------------------------------------------------------------------------------------------
function findFromMetadata()
{
	if (!canMakeOpenurl())
	{
		return;
	}
	
	var openurl = formToOpenurl();
	
	$('#bioguid_progress').show();
	$('#bioguid_progress').attr('src', 'images/ajax-loader.gif');
	
	// Ajax JSONP call to look up reference in bioGUID (note callback parameter)
	jQuery.getJSON("http://bioguid.info/openurl?" + openurl + "&display=json&callback=?", 
	function(data) 
	{
		if (data.status == 'ok')
		{
			$('#bioguid_progress').attr('src', 'images/accept.png');
		
			// We found reference, so update form details
			if (data.doi)
			{
				$('#doi').val(data.doi);
				doi_enable();
			}
			else { $('#doi').val(''); }

			// We found reference, so update form details
			if (data.issn)
			{
				$('#issn').val(data.issn);
			}
			else { $('#issn').val(''); }
			
			// We found reference, so update form details
			if (data.pmid)
			{
				$('#pmid').val(data.pmid);
			}
			else { $('#pmid').val(''); }
			
			// We found reference, so update form details
			if (data.hdl)
			{
				$('#hdl').val(data.hdl);
			}
			else { $('#hdl').val(''); }
			
			
			// We found reference, so update form details
			if (data.url)
			{
				$('#url').val(data.url);
			}
			else { $('#url').val(''); }			
			
			
			$('#bioguid_progress').fadeOut(1000);
		}
		else
		{
			// Let user know we failed to find this reference
			$('#bioguid_progress').attr('src', 'images/exclamation.png');
			$('#bioguid_progress').fadeOut(1000);
		}
	});	
	
}

//--------------------------------------------------------------------------------------------------
// Get metadata for DOI
function findFromDOI()
{
	$('#hits').html('');

	var doi = $('#doi').val();

	$('#bioguid_progress').show();
	$('#bioguid_progress').attr('src', 'images/ajax-loader.gif');
	
	// Ajax JSONP call to look up reference in bioGUID (note callback parameter)
	jQuery.getJSON("http://bioguid.info/openurl?id=doi:" + doi + "&display=json&callback=?", 
	function(data) 
	{
		if (data.status == 'ok')
		{
			$('#bioguid_progress').attr('src', 'images/accept.png');
			
			// Clear some fields
			$('#url').val('');
			$('#pmid').val('');
			$('#hdl').val('');
			
			// Fill with new data
			
			var name;
			for (name in data)
			{
				//alert(name + '=' + data[name]);
				
				switch (name)
				{
					case 'atitle':
						$('#title').val(data[name]);
						break;
						
					case 'title':
						$('#publication_outlet').val(data[name]);
						break;
						
					case 'authors':
						var value = '';
						var n = data[name].length;
						for (var i=0;i<n;i++)
						{
							value += data[name][i].forename + ' ' + data[name][i].lastname + "\n";
						}
						$('#authors').val(value);
						break;
					
					case 'volume':
					case 'issue':
					case 'issn':
					case 'spage':
					case 'epage':
					case 'year':
					case 'pmid':
					case 'hdl':
					case 'biostor':
						$('#' + name).val(data[name]);
						break;

					case 'url':
						var url = data[name];
						if (url.match(/^http:\/\/dx.doi.org\//))
						{
							$('#' + name).val('');
						}
						else
						{
							$('#' + name).val(url);
						}
						break;
						
					default:
						break;
				}
			}
			
			$('#bioguid_progress').fadeOut(1000);
		}
		else
		{
			// Let user know we failed to find this reference
			$('#bioguid_progress').attr('src', 'images/exclamation.png');
		}
	});	
	
}

//--------------------------------------------------------------------------------------------------
function save()
{
	$.post("posthook.php", $("#openurl").serialize(),
	function(data){
     alert(data);
   });
}


//--------------------------------------------------------------------------------------------------
function findInBiostor()
{
	$('#hits').html('');
	
	if (!canMakeOpenurl())
	{
		return;
	}
	
	var openurl = formToOpenurl();
	
	$('#bioguid_progress').show();
	$('#bioguid_progress').attr('src', 'images/ajax-loader.gif');
	
	// Ajax JSONP call to look up reference in bioGUID (note callback parameter)
	jQuery.getJSON("http://biostor.org/openurl?" + openurl + "&format=json&callback=?", 
	function(data) 
	{
		var found = false;
		
		// Did we get one hit (i.e., a reference that already exists in BioStor?
		// Reference exists in BioStor
		if (data.reference_id)
		{
			found = true;
			
			$('#bioguid_progress').attr('src', 'images/accept.png');	

			// Update URL field in form
			$('#biostor').val(data.reference_id);
			biostor_enable();			
			
			$('#bioguid_progress').fadeOut(1000);			
		}
		else { $('#biostor').val(''); }	
		
		if (!found)
		{
			// We don't have a single hit, but do we have multiple hits?
			// De have possible matches in BioStor?
			var html = '';
			
			if (data.length > 0)
			{
				found = true;
				
				html += '<a href="http://biostor.org/openurl?' + openurl + '" target="_blank">Search in BioStor</a>';
			
				html += '<ol>';
				for (var i =0; i < data.length; i++)
				{
					html += '<li>';
					html += '<div style="height:100px;padding:10px;position:relative;">';
					html += '<div style="float:left">';
					html += '<img height="100" src="http://biostor.org/bhl_image.php?PageID=' + data[i].PageID + '&thumbnail" onclick="showPage(\'' + data[i].PageID + '\');" />';
					html += '</div>';	
					html += '<div style="position:absolute;top:0px;left:100px;">';	
					html += data[i].snippet;
					
					//html += '<button onclick="alert(\'Add to BioStor and update URL field\');">Accept</button>';
					
					//html += '<a href="http://biostor.org/openurl.php?' + openurl + '" target="_new">BioStor</a><span onclick="Reference.acceptBiostor();">xxx</span>';
					
					html += '</div>';	
					html += '</div>';	
					html += '</li>';	
				}
				html += '</ol>';
			}
			else
			{
				html = '[Not found]';
				$('#bioguid_progress').attr('src', 'images/exclamation.png');
			}
			$('#hits').html(html);
		}
		
		$('#bioguid_progress').fadeOut(1000);
	});	
	
	$('#bioguid_progress').fadeOut(1000);
	
	
	
	
}