'use strict';

function nalert(message) 
{
	var normalizeStyle = "margin:0px;"+
						  "padding:0px;"+
						  "overflow:hidden;"+
						  "box-sizing:border-box;"+
						  "color:#444;";

	var createPopup = function()
	{

		var nalertStyle   = normalizeStyle;
			nalertStyle  += "position:absolute;"+
						    "top:50%;"+
						    "left:50%;"+
						    "transform:translateX(-50%) translateY(-50%);"+
						    "-webkit-transform:translateX(-50%) translateY(-50%);"+
						    "width:250px;"+
						    "height:120px;"+
						    "z-index:10100;"+
						    "background:#FFF;"+
						    "padding-top:10px;"+
						    "border-radius:20px;"+
						    "box-shadow:0px 3px 10px rgba(0,0,0,0.3)";

		var messageStyle  = normalizeStyle;
			messageStyle += "display:flex;"+
							"display:-webkit-flex;"+
							"height:70px;"+
							"align-items:center;"+
							"-webkit-align-items:center;"+
							"padding:0px 10px;"+
							"justify-content:center;"+
							"text-align:center";


		var btnStyle      = normalizeStyle;
			btnStyle     += "position:absolute;"+
						    "display:block;"+
						    "right:0px;"+
						    "bottom:0px;"+
						    "left:0px;"+
						    "padding:10px;"+
						    "text-align:center;"+
						    "border-top:1px solid #eaeaea;"+
						    "font-weight:bold;";



		var template = '<div class="nalert" style="'+nalertStyle+'">'+
							'<p class="nalert--message" style="'+messageStyle+'">'+
							'<span>'+message+'</span>'+
							'</p>'+
							'<a class="nalert--btn js-nalert-btn-ok" style="'+btnStyle+'" title="Ok">Ok</a>'+
						'</div>';


		var nalert = document.createElement('div');
			nalert.innerHTML = template;

		return nalert;
	};


	var createOverlay = function()
	{	
		var overlayStyle  = normalizeStyle;
			overlayStyle += "position:absolute;"+
							"top:0px;"+
							"right:0px;"+
							"bottom:0px;"+
							"left:0px;"+
							"background:rgba(0,0,0,0.4);"+
							"width:100%;"+
							"height:100%;"+
							"z-index:10000;";

		var overlay = document.createElement('div');
			overlay.style = overlayStyle;

		return overlay;
	};



	var overlay = createOverlay();
	var popup   = createPopup();
	var body 	 = document.querySelector('body');
		
	// append the popup
	var wrapper = document.createElement('div');
		wrapper.setAttribute('id', 'nalert');
		wrapper.appendChild(overlay);
		wrapper.appendChild(popup);

	body.insertBefore(wrapper, body.firstChild);

	var closePopup = function(e)
	{
		// prevent default
		e.preventDefault();

		// remove the element
		document.querySelector('body').removeChild(wrapper);

		return false;
	};


	// set the close btn
	var closeBtn = document.querySelector('.js-nalert-btn-ok');
		closeBtn.addEventListener('mouseup', closePopup);
		closeBtn.addEventListener('touchend', closePopup);
};

