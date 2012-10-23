
  if (document.domain.indexOf('sbhs.net.au') >= 0) {
    document.domain = 'sbhs.net.au';
  }
  else if (document.domain.indexOf('sydneyboyshigh.com') >= 0) {
    document.domain = 'sydneyboyshigh.com'
  }

  // when sent back to the calendar view
  if (window.location.href.indexOf('calendar/view.php') > -1) {
    if (window.opener) {
      if (window.opener.document.getElementById('moodle-embed-signature')) {
        if (window.opener.document.getElementById('moodle-embed-button')) {
          window.opener.document.getElementById('moodle-embed-button').click();
        }
        window.close();
      }
      //else
      //  alert("No Embed signature");
    }
    //else
    //  alert("No Window Opener");
  }


function calendarCreateSubmission() {
  //alert('submission')
  //return false;
}

// re-written course jump handler
function calendarSubjectChange() {
  u=window.location.href;
  u=u.replace(/&course=\d+/gi, '');
  jmp=document.getElementById('cal_course_flt').jump.options[document.getElementById('cal_course_flt').jump.selectedIndex].value;
  res=jmp.match(/&id=(\d+)/);
  if ((res.length > 0) && (parseInt(res[1]) > 1)) {
    u += '&course=' + res[1];
  }
  window.location.href = u;
}

function getElementsByClass(oElm, strTagName, oClassNames){
	var arrElements = (strTagName == "*" && oElm.all)? oElm.all : oElm.getElementsByTagName(strTagName);
	var arrReturnElements = new Array();
	var arrRegExpClassNames = new Array();
	if(typeof oClassNames == "object"){
		for(var i=0; i<oClassNames.length; i++){
			arrRegExpClassNames.push(new RegExp("(^|\\s)" + oClassNames[i].replace(/\-/g, "\\-") + "(\\s|$)"));
		}
	}
	else{
		arrRegExpClassNames.push(new RegExp("(^|\\s)" + oClassNames.replace(/\-/g, "\\-") + "(\\s|$)"));
	}
	var oElement;
	var bMatchesAll;
	for(var j=0; j<arrElements.length; j++){
		oElement = arrElements[j];
		bMatchesAll = true;
		for(var k=0; k<arrRegExpClassNames.length; k++){
			if(!arrRegExpClassNames[k].test(oElement.className)){
				bMatchesAll = false;
				break;
			}
		}
		if(bMatchesAll){
			arrReturnElements.push(oElement);
		}
	}
	return (arrReturnElements)
}



//DomReady.ready(function() {
YUI().use('node', function (Y) {
Y.on('domready', function() {
  // when adding an event
  if ((window.location.href.indexOf('calendar/event.php') > -1) ||
      (window.location.href.indexOf('calendar/view.php') > -1)) {
    // add the additional query field to anchors
    var e = document.getElementsByTagName('a');
    for (var i=0; i < e.length; i++) {

      // rewriting links into Moodle
      if ((e[i].href.indexOf('calendar/event.php?action=edit') > -1) || (e[i].href.indexOf('calendar/event.php?action=delete') > -1)) {
        e[i].target = '_top';
      }
      else {
        // updating links
        var app = '?';
        if (e[i].href.indexOf('?') > -1) {
          app = '&';
        }
        app += 'shstheme=embedded';
        if (e[i].href.lastIndexOf('#') == -1) {
          e[i].href += app;
        }
        else {
          u = e[i].href;
          e[i].href = u.substr(0, u.lastIndexOf('#')) + app + u.substr(u.lastIndexOf('#'));
        }
      }
    }

    // generate a hidden field
    var e = document.createElement('input');
    e.setAttribute('type', 'hidden');
    e.setAttribute('name', 'shstheme');
    e.setAttribute('value', 'embedded');

    // add it to the DOM for every form on the page
    var f = document.getElementsByTagName('form');
    for (var i=0; i < f.length; i++) {
      f[i].appendChild(e);
    }

    // capture form submission
    if (document.getElementById('eventform')) {
      document.getElementById('eventform').onsubmit = calendarCreateSubmission;
    }

    // override subject selection <select> change
    if (document.getElementById('cal_course_flt_jump')) {
      document.getElementById('cal_course_flt_jump').onchange = calendarSubjectChange;
    }

  }

});
});
