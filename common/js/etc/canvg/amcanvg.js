/*
   * Export.js - AmCharts to PNG
   * Benjamin Maertz (tetra1337@gmail.com)
   *
   * Requires:    rgbcolor.js - http://www.phpied.com/rgb-color-parser-in-javascript/
   *                 canvg.js - http://code.google.com/p/canvg/
   *                amcharts.js - http://www.amcharts.com/download
   */
  
  // Lookup for required libs
  if ( typeof(AmCharts) === 'undefined' || typeof(canvg) === 'undefined' || typeof(RGBColor) === 'undefined' ) {
      throw('Woup smth is wrong you might review that http://www.amcharts.com/forum/viewtopic.php?id=11001');
  }
   
  // Define custom util
  AmCharts.getExport = function(anything) {
      /*
      ** PRIVATE FUNCTIONS
      */
   
      // Word around until somebody found out how to cover that
      function removeImages(svg) {
          var startStr    = '<image';
          var stopStr        = '</image>';
          var start        = svg.indexOf(startStr);
          var stop        = svg.indexOf(stopStr);
          var match        = '';
   
          // Recursion
          if ( start != -1 && stop != -1 ) {
              svg = removeImages(svg.slice(0,start) + svg.slice(stop + stopStr.length,svg.length));
          }
          return svg;
      };
   
      // Senseless function to handle any input
      function gatherAnything(anything,inside) {
          switch(toString.call(anything)) {
              case '[object String]':
                  if ( document.getElementById(anything) ) {
                      anything = inside?document.getElementById(anything):new Array(document.getElementById(anything));
                  }
                  break;
              case '[object Array]':
                  for ( var i=0;i<anything.length;i++) {
                      anything[i] = gatherAnything(anything[i],true);
                  }
                  break;
   
              case '[object XULElement]':
                  anything = inside?anything:new Array(anything);
                  break;
   
              case '[object HTMLDivElement]':
                  anything = inside?anything:new Array(anything);
                  break;
   
              default:
                  anything = new Array();
                  for ( var i=0;i<AmCharts.charts.length;i++) {
                      anything.push(AmCharts.charts[i].div);
                  }
                  break;
          }
          return anything;
      }
   
      /*
      ** varibales VARIABLES!!!
      */
      var chartContainer    = gatherAnything(anything);
      var chartImages        = [];
      var canvgOptions    = {
          ignoreAnimation    :    true,
          ignoreMouse        :    true,
          ignoreClear        :    true,
          ignoreDimensions:    true,
          offsetX            :    0,
          offsetY            :    0
      };
   
      /*
      ** Loop, generate, offer
      */
   
      // Loop through given container
      for(var i1=0;i1<chartContainer.length;i1++) {
          var canvas        = document.createElement('canvas');
          var context        = canvas.getContext('2d');
          var svgs        = chartContainer[i1].getElementsByTagName('svg');
          var image        = new Image();
          var heightCounter = 0;
   
          // Set dimensions, background color to the canvas
          canvas.width    = chartContainer[i1].offsetWidth;
          canvas.height    = chartContainer[i1].offsetHeight;
          context.fillStyle = '#FFFFFF';
          context.fillRect(0,0,canvas.width,canvas.height);
   
          // Loop through all svgs within the container
          for(var i2=0;i2<svgs.length;i2++) {
              var wrapper        = svgs[i2].parentNode;
              var clone        = svgs[i2].cloneNode(true);
              var cloneDiv    = document.createElement('div');
              var offsets        = {
                  x:    wrapper.style.left.slice(0,-2) || 0,
                  y:    wrapper.style.top.slice(0,-2) || 0,
                  height:    wrapper.offsetHeight,
                  width:    wrapper.offsetWidth
              };
   
              // Remove the style and append the clone to the div to receive the full SVG data
              clone.setAttribute('style','');
              cloneDiv.appendChild(clone);
              innerHTML = removeImages(cloneDiv.innerHTML); // without imagery
   
              // Apply parent offset
              if ( offsets.y == 0 ) {
                  offsets.y = heightCounter;
                  heightCounter += offsets.height;
              }
   
              canvgOptions.offsetX = offsets.x;
              canvgOptions.offsetY = offsets.y;
   
              // Some magic beyond the scenes...
              canvg(canvas,innerHTML,canvgOptions);
          }
   
          // Get the final data URL and throw throwat image to the array
          image.src = canvas.toDataURL();
          chartImages.push(image);
      }
   
      // Return DAT IMAGESS!!!!
      return chartImages
  }