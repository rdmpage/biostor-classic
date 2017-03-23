//----------------------------------------------------------------------------------------
function is_name(str) {
	var is_name = false;
	
	if (str.match(/[A-Z][a-zA-Z]+,\s*([A-Z]\.\s*)+/)) {
		is_name = true;
	}
	
	return is_name;
}

//----------------------------------------------------------------------------------------
function is_end_of_citation(str, mean_line_length) {
	var is_end = false;
	
	// page range
	if (str.match(/[­|-|—|–|-](\d+)\.?\s*$/)) {
		is_end = true;
	}
	
	// DOI
	//  - doi: 10.1080/00222938809460919
	if (str.match(/(\s+[-|-]\s+)?doi:\s*10\.\d+\/(.*)$/)) {
		is_end = true;
	}

	if (str.match(/pp\.(\s+\w+\.)?$/)) {
		is_end = true;
	}

	// F C Thompson-style references
	if (str.match(/([0-9]{2}|\?\?)\]$/)) {
		is_end = true;
	}

	if (str.match(/\.\]$/)) {
		is_end = true;
	}

	if (str.match(/\.$/)) {
		if (str.length < mean_line_length) {
			if (!is_name(str)) {
				is_end = true;
			}
		}
	}
	
	return is_end;
}



//----------------------------------------------------------------------------------------
function extract_citations (pages) {
	var citations = [];
	
	var sum_line_length = 0;
	var num_lines = 0;
	
	for (var i in pages) {
		var lines = pages[i].split(/\n/);
		num_lines += lines.length;
		
		for (var j in lines) {
			sum_line_length += lines[j].length;
		}
	}
	var mean_line_length = sum_line_length / num_lines;
	
	
	var STATE_START = 0;
	var STATE_IN_REFERENCES = 1;
	var STATE_OUT_REFERENCES = 2;
	var STATE_START_CITATION = 3;
	var STATE_END_CITATION = 4;

	var state = STATE_START;

	var citation = '';
	
	for (var i in pages) {
		lines = pages[i].split(/\n/);
						
		var n = lines.length;
		var line_number = 0;

		// Handle hyphenation
		var hyphens = [];
		hyphens[0] = 0;

		// Skip running head
		line_number++;
	
		// Hyphen
		var last_line_had_hyphen = false;

		while ((state != STATE_OUT_REFERENCES) && (line_number < n)) {
			line = lines[line_number];	
			line = line.replace(/^\s+/, '');
			line = line.replace(/\s+$/, '');
			
			//document.write(line);
			
			// Trim and flag hyphenation
			if (line.match(/[A-Za-z][-|­]\s*$/)) {
				line = line.replace(/[-|­]\s*$/, '');
				hyphens[line_number] = 1;
			} else {
				hyphens[line_number] = 0;
			}
									
			switch (state) {
				case STATE_START:
					// Look for references
					if (line.match(/^\s*(REFERENCES|LITERATURE CITED|ZOOTAXA References)$/i)) {
						// Ignore table of contents
						if (line.match(/\.?\s*[0-9]$/)) {
						} else {
							state = STATE_IN_REFERENCES;
						}
					}
					break;
				
				case STATE_IN_REFERENCES:
					if (line.match(/^([A-Z]|\.\s*[0-9]{4})/))
					{
						if (line.match(/^((Note[s]? added in proof)|(Appendix)|(Buchbesprechungen)|(Figure)|(Index))/i)) {
							state = STATE_OUT_REFERENCES;
						} else {
							state = STATE_START_CITATION;
							citation = line;
							if (is_end_of_citation(line, mean_line_length)) {
								citations.push(citation);
								state = STATE_IN_REFERENCES;
							}
						}
					}
					break;
				
				case STATE_START_CITATION:
					if (hyphens[line_number - 1] == 0) {
						citation += ' ';
					}
					citation += line;
					if (is_end_of_citation(line, mean_line_length))
					{
						citations.push(citation);
						
						state = STATE_IN_REFERENCES;
					}
					break;
				
				default:
					break;
				
			}
			line_number++;

		}

	}		
	
	return citations;
}
	

function(doc) {
  if (doc.text) {

    // only do this for some journals
    var issn = '';
    if (doc.journal) {
      if (doc.journal.identifier) {
        for (var i in doc.journal.identifier) {
          if (doc.journal.identifier[i].type == 'issn') {
            issn = doc.journal.identifier[i].id;
          }
        }
      }
    }

   switch (issn) {
     case '0035-418X':
       var citations = extract_citations(doc.text);

       for (var i in citations) {
          emit(doc._id, [i,citations[i]]);
       } 
       break;

     default:
       break;
    }
  }
}