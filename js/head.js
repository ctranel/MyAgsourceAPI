// More here: github.com/Modernizr/Modernizr/issues/issue/21
var cssomPrefixes = 'Webkit Moz O ms Khtml'.split(' '),
	prefixes = ' -webkit- -moz- -o- -ms- -khtml- '.split(' '),
	ns = {'svg': 'http://www.w3.org/2000/svg'};
/**
 * Create our "modernizr" element that we do most feature tests on.
 */
var mod = 'modernizr',
	modElem = document.createElement(mod),
	mStyle = modElem.style;
/**
 * testPropsAll tests a list of DOM properties we want to check against.
 * We specify literally ALL possible (known and/or likely) properties on
 * the element including the non-vendor prefixed one, for forward-
 * compatibility.
 */
function testPropsAll( prop, prefixed ) {
	var ucProp  = prop.charAt(0).toUpperCase() + prop.substr(1),
	props   = (prop + ' ' + cssomPrefixes.join(ucProp + ' ') + ucProp).split(' ');
	return testProps(props, prefixed);
}

/**
 * testProps is a generic CSS / DOM property test; if a browser supports
 * a certain property, it won't return undefined for it.
 * A supported CSS property returns empty string when its not yet set.
 */
function testProps( props, prefixed ) {
	for ( var i in props ) {
		if ( mStyle[ props[i] ] !== undefined ) {
			return prefixed == 'pfx' ? props[i] : true;
		}
	}
	return false;
}

/**
 * setCss applies given styles to the Modernizr DOM node.
 */
function setCss( str ) {
	mStyle.cssText = str;
}

/**
 * setCssAll extrapolates all vendor-specific css strings.
 */
function setCssAll( str1, str2 ) {
	return setCss(prefixes.join(str1 + ';') + ( str2 || '' ));
}

/**
 * contains returns a boolean for if substr is found within str.
 */
function contains( str, substr ) {
	return !!~('' + str).indexOf(substr);
}

//The *old* flexbox
	//www.w3.org/TR/2009/WD-css3-flexbox-20090723/
	head.feature('flexbox-legacy', function() {
		return testPropsAll('boxDirection');
	});

	//dev.w3.org/csswg/css3-flexbox
	head.feature('flexbox', function() {
		return testPropsAll('flexOrder');
	});

	//On the S60 and BB Storm, getContext exists, but always returns undefined
	//so we actually have to call getContext() to verify
	//github.com/Modernizr/Modernizr/issues/issue/97/
	head.feature('canvas', function() {
		var elem = document.createElement('canvas');
		return !!(elem.getContext && elem.getContext('2d'));
	});

	head.feature('canvastext', function() {
		var elem = document.createElement('canvas');
		if (!!(elem.getContext && elem.getContext('2d'))) {
			return !! typeof(elem.getContext('2d').fillText) === 'function';
		}
		else return false;
	});

	//this test initiates a new webgl context.
	//webk.it/70117 is tracking a legit feature detect proposal
	head.feature('webgl', function() {
		try {
			var canvas = document.createElement('canvas'),
				ret;
			ret = !!(window.WebGLRenderingContext && (canvas.getContext('experimental-webgl') || canvas.getContext('webgl')));
			canvas = undefined;
		} catch (e){
			ret = false;
		}
		return ret;
	});

	/*
	* geolocation tests for the new Geolocation API specification.
	*  This test is a standards compliant-only test; for more complete
	*  testing, including a Google Gears fallback, please see:
	*  code.google.com/p/geo-location-javascript/
	* or view a fallback solution using google's geo API:
	*  gist.github.com/366184
	*/
	head.feature('geolocation', function() {
		return !!navigator.geolocation;
	});

	//Per 1.6:
	//This used to be Modernizr.crosswindowmessaging but the longer
	//name has been deprecated in favor of a shorter and property-matching one.
	//The old API is still available in 1.6, but as of 2.0 will throw a warning,
	//and in the first release thereafter disappear entirely.
	head.feature('postmessage', function() {
		return !!window.postMessage;
	});

	//Chrome incognito mode used to throw an exception when using openDatabase
	//It doesn't anymore.
	head.feature('websqldatabase', function() {
		return !!window.openDatabase;
	});

	//Vendors had inconsistent prefixing with the experimental Indexed DB:
	//- Webkit's implementation is accessible through webkitIndexedDB
	//- Firefox shipped moz_indexedDB before FF4b9, but since then has been mozIndexedDB
	//For speed, we don't test the legacy (and beta-only) indexedDB
	head.feature('indexedDB', function() {
		for ( var i = -1, len = cssomPrefixes.length; ++i < len; ){
			if ( window[cssomPrefixes[i].toLowerCase() + 'IndexedDB'] ){
				return true;
			}
		}
		return !!window.indexedDB;
	});

	//Per 1.6:
	//This used to be Modernizr.historymanagement but the longer
	//name has been deprecated in favor of a shorter and property-matching one.
	//The old API is still available in 1.6, but as of 2.0 will throw a warning,
	//and in the first release thereafter disappear entirely.
	head.feature('history', function() {
		return !!(window.history && history.pushState);
	});

	head.feature('draganddrop', function() {
		var div = document.createElement('div');
		return ('draggable' in div) || ('ondragstart' in div && 'ondrop' in div);
	});

	//FIXME: Once FF10 is sunsetted, we can drop prefixed MozWebSocket
	//bugzil.la/695635
	head.feature('websockets', function() {
		for ( var i = -1, len = cssomPrefixes.length; ++i < len; ){
			if ( window[cssomPrefixes[i] + 'WebSocket'] ){
				return true;
			}
		}
		return 'WebSocket' in window;
	});

	head.feature('hsla', function() {
		// Same as rgba(), in fact, browsers re-map hsla() to rgba() internally,
		//   except IE9 who retains it as hsla
		setCss('background-color:hsla(120,40%,100%,.5)');
		return contains(mStyle.backgroundColor, 'rgba') || contains(mStyle.backgroundColor, 'hsla');
	});

	head.feature('backgroundsize', function() {
		return testPropsAll('backgroundSize');
	});

	head.feature('opacity', function() {
		// Browsers that actually have CSS Opacity implemented have done so
		//  according to spec, which means their return values are within the
		//  range of [0.0,1.0] - including the leading zero.
		setCssAll('opacity:.55');
		
		// The non-literal . in this regex is intentional:
		//   German Chrome returns this value as 0,55
		// github.com/Modernizr/Modernizr/issues/#issue/59/comment/516632
		return /^0.55$/.test(mStyle.opacity);
	});

	//Note, Android < 4 will pass this test, but can only animate
	//a single property at a time
	//daneden.me/2011/12/putting-up-with-androids-bullshit/
	head.feature('cssanimations', function() {
		return testPropsAll('animationName');
	});

	head.feature('csscolumns', function() {
		return testPropsAll('columnCount');
	});

	head.feature('cssgradients', function() {
		/**
		 * For CSS Gradients syntax, please see:
		 * webkit.org/blog/175/introducing-css-gradients/
		 * developer.mozilla.org/en/CSS/-moz-linear-gradient
		 * developer.mozilla.org/en/CSS/-moz-radial-gradient
		 * dev.w3.org/csswg/css3-images/#gradients-
		 */
		var str1 = 'background-image:',
			str2 = 'gradient(linear,left top,right bottom,from(#9f9),to(white));',
			str3 = 'linear-gradient(left top,#9f9, white);';
		
		setCss(
			// legacy webkit syntax (FIXME: remove when syntax not in use anymore)
			(str1 + '-webkit- '.split(' ').join(str2 + str1)
			// standard syntax
			// trailing 'background-image:'
			+ prefixes.join(str3 + str1)).slice(0, -str1.length)
		);
		
		return contains(mStyle.backgroundImage, 'gradient');
	});

	head.feature('csstransforms3d', function() {
		var ret = !!testProps(['perspectiveProperty', 'WebkitPerspective', 'MozPerspective', 'OPerspective', 'msPerspective']);
		// Webkit's 3D transforms are passed off to the browser's own graphics renderer.
		//   It works fine in Safari on Leopard and Snow Leopard, but not in Chrome in
		//   some conditions. As a result, Webkit typically recognizes the syntax but
		//   will sometimes throw a false positive, thus we must do a more thorough check:
		docElement = doc.documentElement
		if ( ret && 'webkitPerspective' in docElement.style ) {
			// Webkit allows this media query to succeed only if the feature is enabled.
			// `@media (transform-3d),(-o-transform-3d),(-moz-transform-3d),(-ms-transform-3d),(-webkit-transform-3d),(modernizr){ ... }`
			ret = (hash['csstransforms3d'] && hash['csstransforms3d'].offsetLeft) === 9;
		}
		return ret;
	});

	//CSS generated content detection
//	head.feature('generatedcontent', function() {
//		return (hash['generatedcontent'] && hash['generatedcontent'].offsetHeight) >= 1;
//	});

	//These tests evaluate support of the video/audio elements, as well as
	//testing what types of content they support.
	//
	//We're using the Boolean constructor here, so that we can extend the value
	//e.g.  Modernizr.video // true
	//Modernizr.video.ogg // 'probably'
	//
	//Codec values from : github.com/NielsLeenheer/html5test/blob/9106a8/index.html#L845
//	                  thx to NielsLeenheer and zcorpan
	//Note: in some older browsers, "no" was a return value instead of empty string.
	//It was live in FF3.5.0 and 3.5.1, but fixed in 3.5.2
	//It was also live in Safari 4.0.0 - 4.0.4, but fixed in 4.0.5
	head.feature('video', function() {
		var elem = document.createElement('video'),
			bool = false;
		// IE9 Running on Windows Server SKU can cause an exception to be thrown, bug #224
		try {
			if ( bool = !!elem.canPlayType ) {
				bool      = new Boolean(bool);
				bool.ogg  = elem.canPlayType('video/ogg; codecs="theora"')      .replace(/^no$/,'');
				bool.h264 = elem.canPlayType('video/mp4; codecs="avc1.42E01E"') .replace(/^no$/,'');
				bool.webm = elem.canPlayType('video/webm; codecs="vp8, vorbis"').replace(/^no$/,'');
			}
		} catch(e) { }
		return bool;
	});

	head.feature('audio', function() {
		var elem = document.createElement('audio'),
			bool = false;
		try {
			if ( bool = !!elem.canPlayType ) {
				bool      = new Boolean(bool);
				bool.ogg  = elem.canPlayType('audio/ogg; codecs="vorbis"').replace(/^no$/,'');
				bool.mp3  = elem.canPlayType('audio/mpeg;')               .replace(/^no$/,'');
				// Mimetypes accepted:
				//   developer.mozilla.org/En/Media_formats_supported_by_the_audio_and_video_elements
				//   bit.ly/iphoneoscodecs
				bool.wav  = elem.canPlayType('audio/wav; codecs="1"')     .replace(/^no$/,'');
				bool.m4a  = ( elem.canPlayType('audio/x-m4a;')            ||
						elem.canPlayType('audio/aac;'))             .replace(/^no$/,'');
			}
		} catch(e) { }
		return bool;
	});

	//In FF4, if disabled, window.localStorage should === null.
	//Normally, we could not test that directly and need to do a
	//`('localStorage' in window) && ` test first because otherwise Firefox will
	//throw bugzil.la/365772 if cookies are disabled

	//Also in iOS5 Private Browsing mode, attepting to use localStorage.setItem
	//will throw the exception:
	//QUOTA_EXCEEDED_ERRROR DOM Exception 22.
	//Peculiarly, getItem and removeItem calls do not throw.

	//Because we are forced to try/catch this, we'll go aggressive.
	//Just FWIW: IE8 Compat mode supports these features completely:
	//www.quirksmode.org/dom/html5.html
	//But IE8 doesn't support either with local files
	head.feature('localstorage', function() {
		try {
			localStorage.setItem(mod, mod);
			localStorage.removeItem(mod);
			return true;
		} catch(e) {
			return false;
		}
	});

	head.feature('sessionstorage', function() {
		try {
			sessionStorage.setItem(mod, mod);
			sessionStorage.removeItem(mod);
			return true;
		} catch(e) {
			return false;
		}
	});

	head.feature('webworkers', function() {
		return !!window.Worker;
	});

	head.feature('applicationcache', function() {
		return !!window.applicationCache;
	});

	//Thanks to Erik Dahlstrom
	head.feature('svg', function() {
		return !!document.createElementNS && !!document.createElementNS(ns.svg, 'svg').createSVGRect;
	});

	//specifically for SVG inline in HTML, not within XHTML
	//test page: paulirish.com/demo/inline-svg
	head.feature('inlinesvg', function() {
		var div = document.createElement('div');
		div.innerHTML = '<svg/>';
		return (div.firstChild && div.firstChild.namespaceURI) == ns.svg;
	});

	//SVG SMIL animation
	head.feature('smil', function() {
		return !!document.createElementNS && /SVGAnimate/.test(toString.call(document.createElementNS(ns.svg, 'animate')));
	});

	//This test is only for clip paths in SVG proper, not clip paths on HTML content
	//demo: srufaculty.sru.edu/david.dailey/svg/newstuff/clipPath4.svg
	//However read the comments to dig into applying SVG clippaths to HTML content here:
	//github.com/Modernizr/Modernizr/issues/213#issuecomment-1149491
	head.feature('svgclippaths', function() {
		return !!document.createElementNS && /SVGClipPath/.test(toString.call(document.createElementNS(ns.svg, 'clipPath')));
	});
	
	
	/* TEST THAT DO NOT CURRENTLY WORK IN THIS CONTEXT */
	/* The Modernizr.touch test only indicates if the browser supports
	*  touch events, which does not necessarily reflect a touchscreen
	*  device, as evidenced by tablets running Windows 7 or, alas,
	*  the Palm Pre / WebOS (touch) phones.
	* Additionally, Chrome (desktop) used to lie about its support on this,
	*  but that has since been rectified: crbug.com/36415
	* We also test for Firefox 4 Multitouch Support.
	* For more info, see: modernizr.github.com/Modernizr/touch.html
	head.feature('touch', function() {
		return ('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch || (hash['touch'] && hash['touch'].offsetTop) === 9;
	});
	*/

	//documentMode logic from YUI to filter out IE8 Compat Mode
	//which false positives.
//	head.feature('hashchange', function() {
//		return isEventSupported('hashchange', window) && (document.documentMode === undefined || document.documentMode > 7);
//	});

