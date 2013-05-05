<!DOCTYPE html>
<!--
Drawing code is Copyright 2013 Dion Moult <dion@thinkmoult.com>
Webcam authorisation and linking was originally written by Eric Bidelman by Google Inc
Colour analysis algorithms were based off a Microsoft developer test.
The original licenses for Google and Microsoft can be seen below.

Copyright 2011 Google Inc.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

     http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

Author: Eric Bidelman (ericbidelman@chromium.org)

// THIS CODE AND INFORMATION IS PROVIDED "AS IS" WITHOUT WARRANTY OF
// ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO
// THE IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
// PARTICULAR PURPOSE.
//
// Copyright (c) Microsoft Corporation. All rights reserved

-->
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="chrome=1">
<link href="http://fonts.googleapis.com/css?family=Open+Sans:300" rel="stylesheet" type="text/css">
<link href="../common.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="default.js"></script>
<title>Prosthetic</title>
<style>
@-webkit-keyframes glowRed {
  from {
    box-shadow: rgba(255, 0, 0, 0) 0 0 0;
  }
  50% {
    box-shadow: rgba(255, 0, 0, 1) 0 0 15px 1px;
  }
  to {
    box-shadow: rgba(255, 0, 0, 0) 0 0 0;
  }
}
html, body {
  margin: 0;
  padding: 0;
}
body {
  display: -webkit-flex;
  -webkit-align-items: center;
  -webkit-justify-content: center;
  box-sizing: border-box;
}
article {
  text-align: center;
}
#monitor {
  /*-webkit-transform: scaleX(-1);*/
  height: 300px;
  /*-webkit-box-reflect: below 20px -webkit-linear-gradient(top, transparent, transparent 80%, rgba(255,255,255,0.2));*/
}
#live {
  position: absolute;
  z-index: 1;
  color: white;
  font-weight: 600;
  font-family: Arial;
  font-size: 16pt;
  right: 35px;
  top: 20px;
  text-shadow: 1px 1px red;
  letter-spacing: 1px;
}
#live:before {
  content: '';
  border-radius: 50%;
  width: 15px;
  height: 15px;
  background: red;
  position: absolute;
  left: -20px;
  margin-top: 5px;
}
#gallery img {
  position: absolute;
  z-index: -1;
  height: 75px;
}
#gallery img {
  float: left;
  height: 75px;
}
.container {
  padding: 10px 25px 5px 25px;
  border-radius: 4px;
  display: inline-block;
  position: relative;
}
h1 {
  font-weight: 300;
}
.blur {
  -webkit-filter: blur(3px);
}
.brightness {
  -webkit-filter: brightness(5);
}
.contrast {
  -webkit-filter: contrast(8);
}
.hue-rotate {
  -webkit-filter: hue-rotate(90deg);
}
.hue-rotate2 {
  -webkit-filter: hue-rotate(180deg);
}
.hue-rotate3 {
  -webkit-filter: hue-rotate(270deg);
}
.saturate {
  -webkit-filter: saturate(10);
}
.grayscale {
  -webkit-filter: grayscale(1);
}
.sepia {
  -webkit-filter: sepia(1);
}
.invert {
  -webkit-filter: invert(1)
}
</style>
</head>
<body>

<article>
<canvas id="myCanvas" height="800" width="700" style="display: none; border: 2px solid #F00;" ></canvas>
 <section id="app" hidden>
  <div class="container"><video style="display: none;" id="monitor" autoplay onclick="changeFilter(this)" title="Click me to see different filters" class="myImage"></video></div>
 </section>
 <div id="splash">
  <p id="errorMessage"></p>
 </div>
 <div id="gallery"></div>
</article>
<canvas id="photo" style="display:none"></canvas>
<center style="clear: both;">
<canvas id="drawing" height="800" width="700" style="-webkit-transform: scale(-1,1); transform: scale(-1, 1); border: 0px solid #FF0;" ></canvas>
</center>
 <p><button onclick="init(this)">-</button></p>

<table id="colorValues" style="display: none;">
<tr>
<td colspan="6">
Current Pointer Information
</td>
</tr>
<tr>
    <td> X </td>
    <td> Y </td>
    <td class="red"> R </td>
    <td class="green"> G </td>
    <td class="blue"> B </span></td>
    <td> A </td>
</tr>
<tr>
    <td id="x">&nbsp;</td>
    <td id="y">&nbsp;</td>
    <td id="rVal" class="red"> &nbsp;</td>
    <td id="gVal" class="green">&nbsp;</td>
    <td id="bVal"class="blue">&nbsp;</td>
    <td id="aVal">&nbsp; </td>
</tr>
</table>

<div style="display: none;">
<form name="myform" style="display: none;">
<input  name="filter" type="checkbox" value="r">Remove <span class="red"> Red </span> Channel </input><br>
<input  name="filter" type="checkbox" value="g">Remove <span class="green"> Green </span> Channel </input><br>
<input  name="filter" type="checkbox" value="b">Remove <span class="blue"> Blue </span> Channel </input><br>
</form>
<br>
<button type="button" onclick="applyFilter (2)"> Apply Filter </button><br><br>
<button type="button" onclick="applyFilter (1)"> Convert to B&W </button><br><br>

Note: once channel is removed, you have to<br> reload image to get it back.
<br>
<br>
<br>
<span> Left Mouse Click </span> - remove color<br>
<span> Right Mouse Click </span>- color to alpha
<br><br>
<button type="button" onclick="loadImage()"> Reload Image </button><br>
</div>
<form>




<script>
navigator.getUserMedia = navigator.webkitGetUserMedia || navigator.getUserMedia;
window.URL = window.URL || window.webkitURL;

var app = document.getElementById('app');
var video = document.getElementById('monitor');
var mycan = document.getElementById('myCanvas');
var drawing = document.getElementById('drawing');
var canvas = document.getElementById('photo');
var effect = document.getElementById('effect');
var gallery = document.getElementById('gallery');
var ctx = canvas.getContext('2d');
var myctx = mycan.getContext('2d');
var drawctx = drawing.getContext('2d');
var intervalId = null;
var idx = 0;
var filters = [
  'grayscale',
  'sepia',
  'blur',
  'brightness',
  'contrast',
  'hue-rotate', 'hue-rotate2', 'hue-rotate3',
  'saturate',
  'invert',
  ''
];

function rgb2hsv() {
    var rr, gg, bb,
        r = arguments[0] / 255,
        g = arguments[1] / 255,
        b = arguments[2] / 255,
        h, s,
        v = Math.max(r, g, b),
        diff = v - Math.min(r, g, b),
        diffc = function(c) {
            return (v-c) / 6 / diff + 1 / 2;
        };

    if (diff == 0) {
        h = s = 0;
    } else {
        s = diff / v;
        rr = diffc(r);
        gg = diffc(g);
        bb = diffc(b);

        if (r === v) {
            h = bb - gg;
        } else if (g === v) {
            h = (1 / 3) + rr - bb;
        } else if (b === v) {
            h = (2 / 3) + gg - rr;
        }
        if (h < 0) {
            h += 1;
        } else if (h > 1) {
            h -= 1;
        }
    }
    return {
        h: Math.round(h * 360),
        s: Math.round(s * 100),
        v: Math.round(v * 100)
    };
}

function changeFilter(el) {
  el.className = '';
  var effect = filters[idx++ % filters.length];
  if (effect) {
    el.classList.add(effect);
  }
}

function gotStream(stream) {
  if (window.URL) {
    video.src = window.URL.createObjectURL(stream);
  } else {
    video.src = stream; // Opera.
  }

  video.onerror = function(e) {
    stream.stop();
  };

  stream.onended = noStream;

  video.onloadedmetadata = function(e) { // Not firing in Chrome. See crbug.com/110938.
    document.getElementById('splash').hidden = true;
    document.getElementById('app').hidden = false;
  };

  // Since video.onloadedmetadata isn't firing for getUserMedia video, we have
  // to fake it.
  setTimeout(function() {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    document.getElementById('splash').hidden = true;
    document.getElementById('app').hidden = false;
  }, 50);
}

function noStream(e) {
  var msg = 'No camera available.';
  if (e.code == 1) {
    msg = 'User denied access to use camera.';
  }
  document.getElementById('errorMessage').textContent = msg;
}

function capture() {
  if (intervalId) {
    clearInterval(intervalId);
    intervalId = null;
    return;
  }

  intervalId = setInterval(function() {
    ctx.drawImage(video, 0, 0);
    var img = document.createElement('img');
    img.src = canvas.toDataURL('image/webp');

    var angle = Math.floor(Math.random() * 36);
    var sign = Math.floor(Math.random() * 2) ? 1 : -1;
    img.style.webkitTransform = 'rotateZ(' + (sign * angle) + 'deg)';

    var maxLeft = document.body.clientWidth;
    var maxTop = document.body.clientHeight;

    img.style.top = Math.floor(Math.random() * maxTop) + 'px';
    img.style.left = Math.floor(Math.random() * maxLeft) + 'px';

    gallery.appendChild(img);
  }, 150);
}

function init(el) {
  if (!navigator.getUserMedia) {
    document.getElementById('errorMessage').innerHTML = 'Sorry. <code>navigator.getUserMedia()</code> is not available.';
    return;
  }
  el.onclick = capture;
  el.textContent = '';
  el.style.visibility = 'hidden';
  navigator.getUserMedia({video: true}, gotStream, noStream);

  var previousimg = 'null';

    video.addEventListener('play', function () {
        var $this = this; //cache
        (function loop() {
            if (!$this.paused && !$this.ended) {

                myctx.drawImage($this, 0, 0);
                WIDTH = mycan.width;
                HEIGHT = mycan.height;
                imgData = myctx.getImageData(0,0,WIDTH, HEIGHT);
                drawimgData = drawctx.getImageData(0,0,WIDTH, HEIGHT);

    var pixels = imgData.data;
    var drawpixels = drawimgData.data;
    //if (previousimg != 'null') {
        //var prevpixels = previousimg.data;
    //}
     rFilt = document.myform.filter[0].checked;
     gFilt = document.myform.filter[1].checked;
     bFilt = document.myform.filter[2].checked;
    var colorOffset  = {red: 0, green: 1, blue: 2, alpha: 3};

    // Loop through the pixels, turning them grayscale
    for (var i = 0; i < pixels.length; i += 4) {
        var r = pixels[i];
        var g = pixels[i + 1];
        var b = pixels[i + 2];
        //if (previousimg != 'null')
        //{
        //var prevr = prevpixels[i];
        //var prevg = prevpixels[i + 1];
        //var prevb = prevpixels[i + 2];
        //}

        // convert to B&W
        //var brightness = (.3 * r + .55 * g + .11 * b) ;
        // http://en.wikipedia.org/wiki/Luma_(video)
        var brightness = 0.2126 * r + 0.7152 * g + 0.0722 * b;
        //if (prevg < 200)
        //{
            //pixels[i + colorOffset.red] = prevpixels[i + colorOffset.red];
            //pixels[i + colorOffset.green] = prevpixels[i + colorOffset.green];
            //pixels[i + colorOffset.blue] = prevpixels[i + colorOffset.blue];
            ////pixels[i + colorOffset.alpha] = 1;
        //}
        //if (r > 50 && b > 50 && g < 200) {
            //pixels[i + colorOffset.red] = 0;
            //pixels[i + colorOffset.green] = 0;
            //pixels[i + colorOffset.blue] = 0;
            ////pixels[i + colorOffset.alpha] = 0;
        //}

        // Light blue bottle cap
        var checkr = 30;
        var checkg = 57;
        var checkb = 115;

        if (
            ( r > (checkr-25) && r < (checkr+25) ) &&
            ( g > (checkg-25) && g < (checkg+25) ) &&
            //( b > (checkb-25) && b < (checkb+25) )
            ( b > (checkb-25) )
        ) {
            drawpixels[i + colorOffset.red] = 250;
            drawpixels[i + colorOffset.green] = 100;
            drawpixels[i + colorOffset.blue] = 100;
            drawpixels[i + colorOffset.alpha] = 255;
        }

        // On occasion, HSV is more accurate than RGB, but the added conversion
        // will lag th system.
        //hsv = rgb2hsv(r, g, b);
        //var my_rr, my_gg, my_bb,
            //my_r = r / 255,
            //my_g = g / 255,
            //my_b = b / 255,
            //my_h, my_s,
            //my_v = Math.max(my_r, my_g, my_b),
            //my_diff = my_v - Math.min(my_r, my_g, my_b),
            //my_diffc = function(my_c) {
                //return (my_v-my_c) / 6 / my_diff + 1 / 2;
            //};

        //if (my_diff == 0) {
            //my_h = my_s = 0;
        //} else {
            //my_s = my_diff / my_v;
            //my_rr = my_diffc(my_r);
            //my_gg = my_diffc(my_g);
            //my_bb = my_diffc(my_b);

            //if (my_r === my_v) {
                //my_h = my_bb - my_gg;
            //} else if (my_g === my_v) {
                //my_h = (1 / 3) + my_rr - my_bb;
            //} else if (b === v) {
                //my_h = (2 / 3) + my_gg - my_rr;
            //}
            //if (my_h < 0) {
                //my_h += 1;
            //} else if (my_h > 1) {
                //my_h -= 1;
            //}
        //}
        //var h = Math.round(my_h * 360);
        //var s = Math.round(my_s * 100);
        //var v = Math.round(my_v * 100);

        //// Light blue bottle cap
        //var checkh = 198;
        //var checks = 111;
        //var checkv = 115;

        //if (
            //( h > (checkh-155) && h < (checkh+155) ) &&
            //( s > (checks-55) && s < (checks+55) ) &&
            //( v > (checkv-55) && v < (checkv+55) )
        //) {
            //drawpixels[i + colorOffset.red] = 250;
            //drawpixels[i + colorOffset.green] = 100;
            //drawpixels[i + colorOffset.blue] = 100;
            //drawpixels[i + colorOffset.alpha] = 255;
        //}
    }
    myctx.putImageData(imgData,0,0);
    drawctx.putImageData(drawimgData,0,0);

                //setTimeout(loop, 1000 / 30); // drawing at 30fps
                setTimeout(loop, 1000 / 600); // drawing at 30fps
            }
        })();
    }, 0);
}

window.addEventListener('keydown', function(e) {
  if (e.keyCode == 27) { // ESC
    document.querySelector('details').open = false;
  }
}, false);
</script>
<script>
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-22014378-1']);
_gaq.push(['_trackPageview']);

(function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>
<!--[if IE]>
<script src="http://ajax.googleapis.com/ajax/libs/chrome-frame/1/CFInstall.min.js"></script>
<script>CFInstall.check({mode: 'overlay'});</script>
<![endif]-->
</body>
</html>
