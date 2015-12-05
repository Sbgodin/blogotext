// *** LICENSE ***
// This file is part of BlogoText.
// http://lehollandaisvolant.net/blogotext/
//
// 2006      Frederic Nassar.
// 2010-2015 Timo Van Neerden <timo@neerden.eu>
//
// BlogoText is free software.
// You can redistribute it under the terms of the MIT / X11 Licence.
//
// *** LICENSE ***

"use strict";

/*
	on comment : reply link « @ » quotes le name.
*/

function reply(code) {
	var field = document.querySelector('#form-commentaire textarea');
	field.focus();
	if (field.value !== '') {
		field.value += '\n';
	}
	field.value += code;
	field.scrollTop = 10000;
	field.focus();
}

/*
	cancel button on forms.
*/

function annuler(pagecible) {
	window.location = pagecible;
}


/*
	On login captcha : if the captcha is unreadable, this helps you reload the captcha
	without reloading the whole page (the other fields might been filed)
*/

function new_freecap() {
	var thesrc = document.getElementById("freecap").src;
	thesrc = thesrc.substring(0,thesrc.lastIndexOf(".")+4);
	document.getElementById("freecap").src = thesrc+"?"+Math.round(Math.random()*100000);
}


/*
	On article or comment writing: insert a BBCode Tag or a Unicode char.
*/

function insertTag(startTag, endTag, tag) {
	var field = document.getElementById(tag);
	var scroll = field.scrollTop;
	field.focus();
	var startSelection   = field.value.substring(0, field.selectionStart);
	var currentSelection = field.value.substring(field.selectionStart, field.selectionEnd);
	var endSelection     = field.value.substring(field.selectionEnd);
	if (currentSelection == "") { currentSelection = "TEXT"; }
	field.value = startSelection + startTag + currentSelection + endTag + endSelection;
	field.focus();
	field.setSelectionRange(startSelection.length + startTag.length, startSelection.length + startTag.length + currentSelection.length);
	field.scrollTop = scroll;
}

function insertChar(ch, tag) {
	var field = document.getElementById(tag);
	var scroll = field.scrollTop;
	field.focus();

	var bef_cur = field.value.substring(0, field.selectionStart);
	var aft_cur = field.value.substring(field.selectionEnd);
	field.value = bef_cur + ch + aft_cur;
	field.focus();
	field.setSelectionRange(bef_cur.length + ch.toString.length +1, bef_cur.length + ch.toString.length +1);
	field.scrollTop = scroll;
}


/*
	unfold comment edition bloc.
*/

function unfold(button) {
	var elemOnForground = document.querySelectorAll('.commentbloc.foreground');
	for (var i=0, len=elemOnForground.length ; i<len ; i++) {
		elemOnForground[i].classList.remove('foreground');
	}

	var elemToForground = button.parentNode.parentNode.parentNode.parentNode;
	elemToForground.classList.toggle('foreground');
}

/*
	Used in file upload: converts bytes to kB, MB, GB…
*/
function humanFileSize(bytes) {
	var e = Math.log(bytes)/Math.log(1e3)|0,
	nb = (e, bytes/Math.pow(1e3,e)).toFixed(1),
	unit = (e ? 'KMGTPEZY'[--e] : '') + 'B';
	return nb + ' ' + unit
}



/*
	in page maintenance : switch visibility of forms.
*/

function switch_form(activeForm) {
	var form_export = document.getElementById('form_export');
	var form_import = document.getElementById('form_import');
	var form_optimi = document.getElementById('form_optimi');
	form_export.style.display = form_import.style.display = form_optimi.style.display = 'none';
	document.getElementById(activeForm).style.display = 'block';
}

function switch_export_type(activeForm) {
	var e_json = document.getElementById('e_json');
	var e_html = document.getElementById('e_html');
	var e_zip = document.getElementById('e_zip');
	e_json.style.display = e_html.style.display = e_zip.style.display = 'none';
	document.getElementById(activeForm).style.display = 'block';
}

function hide_forms(blocs) {
	var radios = document.getElementsByName(blocs);
	var e_json = document.getElementById('e_json');
	var e_html = document.getElementById('e_html');
	var e_zip = document.getElementById('e_zip');
	var checked = false;
	for (var i = 0, length = radios.length; i < length; i++) {
		if (!radios[i].checked) {
			var cont = document.getElementById('e_'+radios[i].value);
			while (cont.firstChild) {cont.removeChild(cont.firstChild);}
		}
	}
}






/**************************************************************************************************************************************
	LINKS AND ARTICLE FORMS : TAGS HANDLING
**************************************************************************************************************************************/

/* add tags ont links and articles, with HTML5/Datalist autocompletion support */
function insertCatTag(inputId, tag) {
	var field = document.getElementById(inputId);
	if (field.value !== '') {
		field.value += ', ';
	}
	field.value += tag;
}

/* Adds a tag to the list when we hit "enter" */
/* detects keyhit */
function chkHit(e) {
	var unicode = (e.keyCode) ? e.keyCode : e.charCode;
	if (unicode == 13) {
		moveTag;
		return false;
	}
	return true;
}

/* validates the tag and move it to the list */
function moveTag() {
	var iField = document.getElementById('type_tags');
	var oField = document.getElementById('selected');
	var fField = document.getElementById('categories');

	// if something in the input field : enter == add word to list of tags.
	if (iField.value.length != 0) {
		oField.innerHTML += '<li class="tag"><span>'+iField.value+'</span><a href="javascript:void(0)" onclick="removeTag(this.parentNode)">×</a></li>';
		iField.value = '';
		return false;
	}
	// else : real submit : seek in the list of tags, extract the tags and submit these.
	else {
		var liste = oField.getElementsByTagName('li');
		var len = liste.length;
		var iTag = '';
		for (var i = 0 ; i<len ; i++) { iTag += liste[i].getElementsByTagName('span')[0].innerHTML+", "; }
		fField.value = iTag.substr(0, iTag.length-2);
		return true;
	}
}

/* remove a tag from the list */
function removeTag(tag) {
	tag.parentNode.removeChild(tag);
	return false;
}


/**************************************************************************************************************************************
	FILE UPLOADING : DRAG-N-DROP
**************************************************************************************************************************************/

/* Drag and drop event handlers */
function handleDragEnd(e) {
	document.getElementById('dragndrop-area').classList.remove('fullpagedrag');
}

function handleDragLeave(e) {
	if ('WebkitAppearance' in document.documentElement.style) { // Chromium old bug #131325 since 2013.
		if (e.pageX > 0 && e.pageY > 0) {
			return false;
		}
	}
	document.getElementById('dragndrop-area').classList.remove('fullpagedrag');
}

function handleDragOver(e) {
	if (document.getElementById('dragndrop-area').classList.contains('fullpagedrag')) return false;

	var isFiles = false;
	// detects if drag content is actually files (it might be text, url… but only files are relevant here)
	if (e.dataTransfer.types.contains) {
		var isFiles = e.dataTransfer.types.contains("application/x-moz-file");
	}
	else if (e.dataTransfer.types) {
		var isFiles = (e.dataTransfer.types == 'Files') ? true : false;
	}

	if (isFiles) {
		document.getElementById('dragndrop-area').classList.add('fullpagedrag');
	} else {
		document.getElementById('dragndrop-area').classList.remove('fullpagedrag');
	}
}



/* switches between the FILE upload, URL upload and Drag'n'Drop */
function switchUploadForm(where) {
	var link = document.getElementById('click-change-form');
	var input = document.getElementById('fichier');

	if (input.type == "file") {
		link.innerHTML = link.dataset.langFile;
		input.placeholder = "http://example.com/image.png";
		input.type = "url";
		input.focus();
	}
	else {
		link.innerHTML = link.dataset.langUrl;
		input.type = "file";
		input.placeholder = null;
	}
	return false;
}

/* Onclick tag button, shows the images in that folder and build the wall from all JSON data. */

function folder_sort(folder, button) {

	var newlist = new Array();
	for(var k in imgs.list) {
		if (imgs.list[k].dossier.search(folder) != -1) {
			newlist.push(imgs.list[k]);
		}
	}
	// reattributes the new list (it’s a global)
	curr_img = newlist;
	curr_max = curr_img.length-1;

	// recreates the images wall with the new list
	image_vignettes();

	// styles on buttons
	var buttons = document.getElementById('list-albums').childNodes;
	for (var i = 0, nbbut = buttons.length ; i < nbbut ; i++) {
		if (buttons[i].nodeName=="BUTTON") buttons[i].className = '';
	}
	document.getElementById(button).className = 'current';
}

/* Same as folder_sort(), but for filetypes (.doc, .xls, etc.) */

function type_sort(type, button) {
	// finds the matching files
	var wall = document.getElementsByClassName('file_bloc');
	for (var i=0, sz = wall.length; i<sz; i++) {
		var file = wall[i];
		if ((file.getAttribute('data-type') != null) && file.getAttribute('data-type').search(type) != -1) {
			file.style.display = 'inline-block';
		} else {
			file.style.display = 'none';
		}
	}
	var buttons = document.getElementById('list-types').childNodes;
	for (var i = 0, nbbut = buttons.length ; i < nbbut ; i++) {
		if (buttons[i].nodeName=="BUTTON") buttons[i].className = '';
	}
	document.getElementById(button).className = 'current';
}


/* for slideshow : detects the → and ← keypress to change image. */
function checkKey(e) {
	if (!document.getElementById('slider')) return true;
	if (document.getElementById('slider').style.display != 'block') return true;
	e = e || window.event;
	var evt = document.createEvent("MouseEvents"); // créer un évennement souris
	evt.initMouseEvent("click", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
	if (e.keyCode == '37') {
		// left
		var button = document.getElementById('slider-prev');
		button.dispatchEvent(evt);
	}
	else if (e.keyCode == '39') {
		// right
		var button = document.getElementById('slider-next');
		//e.preventDefault(); // ???
		button.dispatchEvent(evt);
	}
	return true;
}


/*	Images slideshow */
function slideshow(action, image) {
	if (action == 'close') {
		document.getElementById('slider').style.display = 'none';
		window.removeEventListener('keydown', checkKey);
		return false;
	}

	window.addEventListener('keydown', checkKey);

	var ElemImg = document.getElementById('slider-img');

	switch (action) {
		case 'start':
			document.getElementById('slider').style.display = 'block';
			counter = parseInt(image);
			break;

		case 'first':
			counter = 0;
			break;

		case 'prev':
			counter = Math.max(counter-1, 0);
			break;

		case 'next':
			counter = Math.min(++counter, curr_max)
			break;

		case 'last':
			counter = curr_max;
			break;
	}

	var newImg = new Image();
	newImg.onload = function() {
		var im = curr_img[counter];
		ElemImg.height = im.height;
		ElemImg.width = im.width;
		// description
		document.getElementById('infos-content').appendChild(document.createTextNode(im.desc));
		// details
		var idet = document.getElementById('infos-details');
		while (idet.firstChild) {idet.removeChild(idet.firstChild);}
		// details :: name + size + weight
		var idetnam = document.createElement('dl');
		var idetnamDl = idetnam.appendChild(document.createElement('dt'));
			// name
			idetnamDl.appendChild(document.createElement('div').appendChild(document.createTextNode(im.filename[1])).parentNode);
			// size
			var idetnamDiv2 = idetnamDl.appendChild(document.createElement('div'));
			idetnamDiv2.appendChild(document.createElement('span').appendChild(document.createTextNode(im.width+' × '+im.height)).parentNode);
			// weight
			idetnamDiv2.appendChild(document.createElement('span').appendChild(document.createTextNode(humanFileSize(im.weight))).parentNode);

		// details :: Date
		var idetnamDl2 = idetnam.appendChild(document.createElement('dt'));
			// Date
			idetnamDl2.appendChild(document.createElement('div').appendChild(document.createTextNode(im.date[0])).parentNode);
			// Day + hour
			var idetnamDiv2 = idetnamDl2.appendChild(document.createElement('div'));
			idetnamDiv2.appendChild(document.createElement('span').appendChild(document.createTextNode(im.date[1])).parentNode);

		idet.appendChild(idetnam);
		ElemImg.src = newImg.src;
		ElemImg.classList.remove('loading');
	};

	newImg.onerror = function() {
		ElemImg.src = '';
		ElemImg.alt = 'Error Loading File';
		ElemUlLi[0].innerHTML = ElemUlLi[1].innerHTML = ElemUlLi[2].innerHTML = 'Error Loading File';
		document.getElementById('slider-img-a').href = '#';
		ElemImg.style.marginTop = '0';
	};
	ElemImg.src = '';
	newImg.src = curr_img[counter].filename[0];
	assingButtons(curr_img[counter]);
}

/* Assigne the events on the buttons from the slideshow */
function assingButtons(file) {
	// dl button
	var dl = document.getElementById('slider-nav-dl');
	dl.href = file.filename[0];
	dl.setAttribute('download', '');

	// share button
	document.getElementById('slider-nav-share').href = 'links.php?url='+file.filename[0];

	// infos button
	document.getElementById('slider-nav-infos').onclick = function(){ document.getElementById('slider-main-content').classList.toggle('infos-on'); };

	// edit button
	document.getElementById('slider-nav-edit').href = '?file_id='+file.id;

	// suppr button
	document.getElementById('slider-nav-suppr').dataset.id = file.id;
	document.getElementById('slider-nav-suppr').onclick = currImageDelUpdate;
	function currImageDelUpdate(event) {
		request_delete_form(event.target.dataset.id);
		this.removeEventListener('click', currImageDelUpdate);
	};
}


/* JS AJAX for remove a file in the list directly, w/o reloading the whole page */

// create and send form
function request_delete_form(id) {
	// prepare XMLHttpRequest
	document.getElementById('slider-img').src = 'style/loading.gif';
	document.getElementById('slider-img').classList.add('loading');

	var xhr = new XMLHttpRequest();
	xhr.open('POST', '_rmfichier.ajax.php');
	xhr.onload = function() {
		if (this.responseText == 'success') {
			// remove tile of the deleted image
			document.getElementById('bloc_'.concat(id)).parentNode.removeChild(document.getElementById('bloc_'.concat(id)));
			// remove image from index
			var globalFlagRem = false, currentFlagRem = false;
			for (var i = 0, len = curr_img.length ; i < len ; i++) {
				if (id == imgs.list[i].id) {
					imgs.list.splice(i , 1);
					globalFlagRem = true;
				}
				if (id == curr_img[i].id) {
					curr_img.splice(i , 1);
					currentFlagRem = true;
					curr_max--;
				}
				// if both lists have been updated, break to avoid useless loops.
				if (globalFlagRem && currentFlagRem) break;
			}
			// rebuilt image wall
			image_vignettes();
			// go prev image in slideshow
			slideshow('prev', counter);
		} else {
			alert(this.responseText+' '+id);
		}
	};

	// prepare and send FormData
	var formData = new FormData();  
	formData.append('supprimer', '1');
	formData.append('file_id', id);
	xhr.send(formData);
}



/* This builts the wall with image-blocks. The data is gathered from Json data. */
function image_vignettes() {
	// empties the existing wall (using while() and removeChild is actually much faster than “innerHTML = ""”
	if (!document.getElementById('image-wall')) { return };
	var wall = document.getElementById('image-wall');
	while (wall.firstChild) {wall.removeChild(wall.firstChild);}
	var loadedFlag = 0;
	// populates the wall with images in $curr_img (sorted by folder_sort())
	for (var i = 0, len = curr_img.length ; i < len ; i++) {
		loadedFlag++;
		var img = curr_img[i];
		var div = document.createElement('div');
		div.classList.add('image_bloc');
		div.id = 'bloc_'+img.id;

		var spanBottom = document.createElement('span');
		    spanBottom.classList.add('spanbottom');

		var spanSlide = document.createElement('span');
		    spanSlide.dataset.i = i;
		    spanSlide.addEventListener('click', function(event){slideshow('start', event.target.dataset.i);});
		    spanBottom.appendChild(spanSlide);

		div.appendChild(spanBottom);

		var newImg = new Image();

		newImg.onload = function() {
			newImg.id = img.id;
			newImg.alt = img.filename[1];
			loadedFlag--;
			if (loadedFlag == 0) tileImages();
		}
		div.appendChild(newImg);
		wall.appendChild(div);
		newImg.src = img.filename[2];
	}
}


/* Used to tile images for a nicer fit, using a personnal implementation of the partition problem
where the partition are resizeable (images are resizeable — but distorted) */
function tileImages() {
	var blocs = document.querySelectorAll('#image-wall .image_bloc');
	var container = document.querySelector('#image-wall');
	if (window.getComputedStyle(container).display != 'flex') return;

	var containerW = parseInt(container.clientWidth) - parseInt(getComputedStyle(container).paddingLeft) - parseInt(getComputedStyle(container).paddingRight) -1 ;

	for (var i=0, len=blocs.length, summedBlocWidth=0, fstImOfRow=0, fixedSummedBlocWidth=0 ; i<len ; i++) {
		var currBloc = blocs[i];
		var cW = parseInt(getComputedStyle(currBloc).width),
		    cML = parseInt(getComputedStyle(currBloc).marginLeft),
		    cMR = parseInt(getComputedStyle(currBloc).marginRight),
		    cPL = parseInt(getComputedStyle(currBloc).paddingLeft),
		    cPR = parseInt(getComputedStyle(currBloc).paddingRight),
		    cBL = parseInt(getComputedStyle(currBloc).borderLeftWidth),
		    cBR = parseInt(getComputedStyle(currBloc).borderRightWidth);
		var currBlocW = cW + cML + cMR + cBL + cBR + cPL + cPR;

		// if we have fallen on a new row
		if (summedBlocWidth + currBlocW >= containerW) {
				var whiteSpace = containerW - summedBlocWidth;
				var delta = ( whiteSpace)/(i-fstImOfRow);
				for (var j=fstImOfRow ; j<i ; j++) {
					var jW = parseInt(getComputedStyle(blocs[j]).width),
						 jML = parseInt(getComputedStyle(blocs[j]).marginLeft),
						 jMR = parseInt(getComputedStyle(blocs[j]).marginRight),
						 jPL = parseInt(getComputedStyle(blocs[j]).paddingLeft),
						 jPR = parseInt(getComputedStyle(blocs[j]).paddingRight),
						 jBL = parseInt(getComputedStyle(blocs[j]).borderLeftWidth),
						 jBR = parseInt(getComputedStyle(blocs[j]).borderRightWidth);
					var jiW = parseInt(getComputedStyle(blocs[j].querySelector('img')).width),
						 jiML = parseInt(getComputedStyle(blocs[j].querySelector('img')).marginLeft),
						 jiMR = parseInt(getComputedStyle(blocs[j].querySelector('img')).marginRight),
						 jiPL = parseInt(getComputedStyle(blocs[j].querySelector('img')).paddingLeft),
						 jiPR = parseInt(getComputedStyle(blocs[j].querySelector('img')).paddingRight),
						 jiBL = parseInt(getComputedStyle(blocs[j].querySelector('img')).borderLeftWidth),
						 jiBR = parseInt(getComputedStyle(blocs[j].querySelector('img')).borderRightWidth);

					// the last image of the row gets the few remaining pixels.
					if (j == i-1) {
						//delta = ;
					}
					var w = jW - jPL - jPR;
					// if the bloc is bigger than the image (i.e. on purpose, if bloc haz min-width), give the delta to the bloc, not the image in the bloc
					if ( parseInt(blocs[j].querySelector('img').clientWidth) < w-3 ) {
						blocs[j].style.width = (jW + delta) + 'px';
						fixedSummedBlocWidth += getComputedStyle(blocs[j]).width + jPL+jPR+jBL+jBR+jMR+jML;
					} else {
						blocs[j].querySelector('img').style.width = (jiW + delta) + 'px';
						fixedSummedBlocWidth += getComputedStyle(blocs[j].querySelector('img')).width + jiPL+jiPR+jiBL+jiBR+jiMR+jiML;
					}
				}
				fstImOfRow = i;
			summedBlocWidth = 0;
			fixedSummedBlocWidth = 0;
		}
		summedBlocWidth += currBlocW;
	}
}



// process bunch of files
function handleDrop(event) {
	var result = document.getElementById('result');
	document.getElementById('dragndrop-area').classList.remove('fullpagedrag');
	if (nbDraged === false) { nbDone = 0; }
	// detects if drag contains files.
	if (event.dataTransfer.types.contains) {
		var isFiles = event.dataTransfer.types.contains("application/x-moz-file");
	}
	else if (event.dataTransfer.types) {
		var isFiles = (event.dataTransfer.types == 'Files') ? true : false;
	}

	if (!isFiles) { event.preventDefault(); return false; }

	var filelist = event.dataTransfer.files;
	if (!filelist || !filelist.length) { event.preventDefault(); return false; }

	for (var i = 0, nbFiles = filelist.length ; i < nbFiles && i < 500; i++) { // limit is for not having an infinite loop
		var rand = 'i_'+Math.random()
		filelist[i].locId = rand;
		list.push(filelist[i]);
		var div = document.createElement('div');
		var fname = document.createElement('span');
		    fname.classList.add('filename');
		    fname.textContent = escape(filelist[i].name);
		var flink = document.createElement('a');
		    flink.classList.add('filelink');
		var fsize = document.createElement('span');
		    fsize.classList.add('filesize');
		    fsize.textContent = '('+humanFileSize(filelist[i].size)+')';
			
		var fstat = document.createElement('span');
		    fstat.classList.add('uploadstatus');
		    fstat.textContent = 'Ready';

		div.appendChild(fname);
		div.appendChild(flink);
		div.appendChild(fsize);
		div.appendChild(fstat);
		div.classList.add('pending');
		div.classList.add('fileinfostatus');
		div.id = rand;

		result.appendChild(div);
	}
	nbDraged = list.length;
	// deactivate the "required" attribute of file (since no longer needed)
	document.getElementById('fichier').required = false;
	event.preventDefault();
}

// OnSubmit for files dragNdrop.
function submitdnd(event) {
	// files have been dragged (means also that this is not a regulat file submission)
	if (nbDraged != 0) {
		// proceed to upload
		uploadNext();
		event.preventDefault();
	}
}

// upload file
function uploadFile(file) {
	// prepare XMLHttpRequest
	var xhr = new XMLHttpRequest();
	xhr.open('POST', '_dragndrop.ajax.php');

	xhr.onload = function() {
		var respdiv = document.getElementById(file.locId);
		// need "try/catch/finally" because of "JSON.parse", that might return errors (but should not, since backend is clean)
		try {
			var resp = JSON.parse(this.responseText);
			respdiv.classList.remove('pending');

			if (resp !== null) {
				// renew token
				document.getElementById('token').value = resp.token;

				respdiv.querySelector('.uploadstatus').innerHTML = resp.status;

				if (resp.status == 'success') {
					respdiv.classList.add('success');
					respdiv.querySelector('.filelink').href = resp.url;
					respdiv.querySelector('.uploadstatus').innerHTML = 'Uploaded';
					// replace file name with a link
					respdiv.querySelector('.filelink').innerHTML = respdiv.querySelector('.filename').innerHTML;
					respdiv.removeChild(respdiv.querySelector('.filename'));
				}
				else {
					respdiv.classList.add('failure');
					respdiv.querySelector('.uploadstatus').innerHTML = 'Upload failed';
				}

				nbDone++;
				document.getElementById('count').innerHTML = +nbDone+'/'+nbDraged;
			} else {
				respdiv.classList.add('failure');
				respdiv.querySelector('.uploadstatus').innerHTML = 'PHP or Session error';
			}

		} catch(e) {
			console.log(e);
		} finally {
			uploadNext();
		}

	};

	xhr.onerror = function() {
		uploadNext();
	};

	// prepare and send FormData
	var formData = new FormData();
	formData.append('token', document.getElementById('token').value);

	formData.append('fichier', file);
	formData.append('statut', ((document.getElementById('statut').checked === false) ? '' : 'on'));

	formData.append('description', document.getElementById('description').value);
	formData.append('nom_entree', document.getElementById('nom_entree').value);
	formData.append('dossier', document.getElementById('dossier').value);
	xhr.send(formData);
}



/**************************************************************************************************************************************
	TOUCH EVENTS HANDLING (various pages)
**************************************************************************************************************************************/
function handleTouchEnd() {
	doTouchBreak = null;
}

function handleTouchStart(evt) {
	xDown = evt.touches[0].clientX;
	yDown = evt.touches[0].clientY;
}


/* Swipe on slideshow to change images */
function swipeSlideshow(evt) {
	if ( !xDown || !yDown || doTouchBreak || document.getElementById('slider').style.display != 'block' ) { return; }
	var xUp = evt.touches[0].clientX;
	var xDiff = xDown - xUp;

	if (Math.abs(xDiff) > minDelta) {
		var newEvent = document.createEvent("MouseEvents");
		newEvent.initMouseEvent("click", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);

		if ( xDiff > minDelta ) {
			/* left swipe */
			var button = document.getElementById('slider-next');
			evt.preventDefault();
			button.dispatchEvent(newEvent);
			doTouchBreak = true;
		} else if ( xDiff < -minDelta) {
			/* right swipe */
			var button = document.getElementById('slider-prev');
			evt.preventDefault();
			button.dispatchEvent(newEvent);
			doTouchBreak = true;
		}

	}
	if (doTouchBreak) {
		xDown = null;
		yDown = null;
	}
}
