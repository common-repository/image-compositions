/*global jQuery, document */

var mdt = mdt || {};

(function ($) {

    'use strict';

    mdt.ImageCompositions = (function() {

        // Define the composition area and create array of canvases.
        var $composition  = $('.mdt-image-composition'),
            $navigation   = $('.mdt-image-composition-nav'),
            $aspectRatio  = $('<select>').addClass('mdt-image-composition-aspect-ratio'),

            $canvas       = $('.mdt-image-compositions-canvas'),
            canvas        = $canvas[0],
            context       = canvas.getContext('2d'),

            $data         = $('#mdt-image-composition-data'),
            $activeImages = $('#mdt-image-composition-image-ids'),

            workspaces    = [],
            layoutClasses = [],
            activeImages  = [],
            activeLayout  = null,

            // These variables can all be manipulated by the PHP filter.
            aspectRatios = mdtImageCompositionsConfiguration.aspectRatios,
            dividerWidth = parseInt( mdtImageCompositionsConfiguration.dividerWidth ),
            dividerColor = mdtImageCompositionsConfiguration.dividerColor,
            zoomInRate   = 1 + ( mdtImageCompositionsConfiguration.zoomRate / 100 ),
            zoomOutRate  = 1 / zoomInRate,
            layouts      = mdtImageCompositionsConfiguration.layouts;

        /**
         * Create all the workspaces.
         */
        function createWorkspace() {

            // Build out the workspace markup.
            var $add         = $('<a>').addClass('mdt-image-compositions-add').text('Add'),
                $zoomIn      = $('<a>').addClass('mdt-image-compositions-zoom-in').text('+'),
                $zoomOut     = $('<a>').addClass('mdt-image-compositions-zoom-out').text('-'),
                $controls    = $('<div>').addClass('mdt-image-compositions-controls').append( $add, $zoomIn, $zoomOut ),
                $el          = $('<div>').addClass('mdt-image-compositions-workspace').append( $controls ),
                $boundingBox = $('<div>').addClass('mdt-image-composition-bounding-box'),
                image        = new Image,
                $image       = $(image),
                zoom         = 1,
                frame        = null,
                attachment   = null;

            // Set image dimensions prior to placement.
            function setImage() {

                if(attachment){

                    zoom = 1;

                    // Determine ratio of image and size
                    var ratioX = $el.width() / attachment.width,
                        ratioY = $el.height() / attachment.height;

                    if(ratioX > ratioY) {
                        image.width  = $el.width();
                        image.height = attachment.height * ratioX;
                        zoom        *= ratioX;
                    } else if(ratioY > ratioX){
                        image.width  = attachment.width * ratioY;
                        image.height = $el.height();
                        zoom        *= ratioY;
                    } else if(ratioY === ratioX) {
                        image.width  = attachment.width * ratioX;
                        image.height = attachment.height * ratioY;
                        zoom        *= ratioY;
                    }
                }
            }

            /**
             * Set a bounding box for image to pan within.
             *
             * @param int placementX
             * @param int placementY
             */
            function setBoundingBox(placementX = null, placementY = null) {

                if (image) {

                    var boundX = image.width - $el.width(),
                        boundY = image.height - $el.height();

                    // Set dimensions and position of the bounding box
                    $boundingBox.css({
                        'width': image.width + boundX,
                        'height': image.height + boundY,
                        'left': -boundX,
                        'top': -boundY
                    });

                    // Make sure any custom image placements don't move image off workspace
                    if (!placementX || !placementY || placementX > boundX || placementY > boundY){
                        placementX = boundX;
                        placementY = boundY;
                    }

                    $image.css({
                        'left': placementX,
                        'top': placementY
                    })
                }
            }

            /**
             * Draw an image to the canvas.
             */
            function drawImage() {
                    
                // Determine cropping coordinates
                var cropX = Math.abs( parseInt( $boundingBox.css('left') ) + parseInt( $image.css('left') ) ),
                    cropY = Math.abs( parseInt( $boundingBox.css('top') ) + parseInt( $image.css('top') ) );

                // Disable image smoothing (can cause blurriness in scaled down images).
                context.imageSmoothingEnabled = false;

                // Draw the image
                context.drawImage(
                    image, // Image object
                    cropX / zoom, // X coordinate of source image to extract
                    cropY / zoom, // Y coordinate of source image to extract
                    $el.width() / zoom, // Width of source image to extract
                    $el.height() / zoom, // Height of source image to extract
                    parseFloat( $el.css('left') ), // X coordinate of destination canvas to project onto
                    parseFloat( $el.css('top') ),  // Y coordinate of destination canvas to project onto
                    $el.width(), // Width of destination canvas to project onto
                    $el.height() // Height of destination canvas to project onto
                );

                // Re-draw the grid and save the image to the canvas
                drawGrid();
                saveImage();
            }

            /**
             * Save canvas data to hidden field.
             */
            function saveImage() {
                $data.val( canvas.toDataURL() );
            }

            // Bind media modal to selection link.
            $add.on( 'click', function(event) {

                event.preventDefault();

                // If the media frame already exists, reopen it
                if (frame) {
                    frame.open();
                    return;
                }

                // Define the media frame
                frame = wp.media({
                    title   : 'Select or Upload Image.',
                    button  : {
                        text: 'Add image'
                    },
                    multiple: false
                });

                // Port selected image to canvas on selection
                frame.on( 'select', function() {
                    
                    // Get the attachment JSON and set the image source.
                    attachment = frame.state().get('selection').first().toJSON();

                    if(attachment.mime === 'image/gif'){
                        alert('GIFs cannot be added to image composites.');
                        attachment = null;
                        return false;
                    }

                    // Use full-size image.
                    image.src = attachment.url;

                    // Ensure image src matches window protocol.
                    var protocol = window.location.protocol,
                        url      = new URL( image.src );

                    image.src = protocol + '//' + url.hostname + url.pathname;

                    $image.css({
                        height: '',
                        width: ''
                    })

                    // Start drawing after image has loaded
                    image.onload = function(){

                        // Set the image and its bounding box
                        setImage();
                        setBoundingBox();

                        // Create the bounding box
                        $boundingBox.append(image);

                        $el.addClass( 'mdt-image-composition-has-image' );
                        $el.append( $boundingBox );

                        // Set image as draggable
                        $image.draggable({
                            'containment': 'parent',
                            'stop': function() {
                                drawImage();
                            }
                        });

                        // Draw and save image
                        drawImage();
                        saveImage();

                        // Save image ID to field
                        activeImages.push(attachment.id);
                        saveImageIds();
                    };
                });

                frame.open();
            });

            // Zoom in.
            $zoomIn.on( 'click', function(e) {

                e.preventDefault();

                // Increase zoom rate
                zoom *= zoomInRate;

                // Determine current positions
                var posX = parseInt( $image.css('left') ),
                    posY = parseInt( $image.css('top') );

                // Set new image dimensions
                $image.css({
                    'width': $image.width() * zoomInRate,
                    'height': $image.height() * zoomInRate,
                });

                // Reset the bounding box with specific image placement and draw
                setBoundingBox(posX * zoomInRate, posY * zoomInRate);
                drawImage();
            });

            // Zoom out.
            $zoomOut.on( 'click', function(e) {

                e.preventDefault();

                // Ensure minimum width and height to cover workspace
                if ( $image.width() * zoomOutRate < $el.width() || $image.height() * zoomOutRate < $el.height() ) {
                    return;
                }

                // Decrease zoom rate
                zoom *= zoomOutRate;

                // Determine current positions
                var posX = parseInt( $image.css('left') ),
                    posY = parseInt( $image.css('top') );

                // Set new image dimensions
                $image.css({
                    'width': $image.width() * zoomOutRate,
                    'height': $image.height() * zoomOutRate,
                });

                // Reset the bounding box with specific image placement and draw
                setBoundingBox(posX * zoomOutRate, posY * zoomOutRate);
                drawImage();
            });

            return {

                $el: $el,

                // Get the attachment object
                getAttachment: function() {
                    return attachment;
                },

                // Redraw the canvas
                redraw: function() {
                    setBoundingBox();
                    drawImage();
                }
            };
        }

        // Set a layout
        function setLayout(layout) {

            // Clear the canvas
            context.clearRect(0, 0, $composition.width(), $composition.height());

            var $workspaces = $('.mdt-image-compositions-workspace');

            // Hide all workspaces
            $workspaces.hide();

            // Reset the active images array
            activeImages = [];

            // Resize the canvases and show them
            $.each(layouts[activeLayout].zones, function(key, zone){

                workspaces[ key ].$el.css({
                    'width': zone.width + '%',
                    'height': zone.height + '%',
                    'top': zone.top + '%',
                    'left': zone.left + '%',
                }).show();

                workspaces[ key ].redraw();

                var attachmentData = workspaces[ key ].getAttachment();
                if(attachmentData){
                    activeImages.push(attachmentData.id);
                }
            });

            // Serialize image IDs into hidden field
            saveImageIds();

            // Set the CSS class
            $composition.removeClass(layoutClasses.join(' ')).addClass( 'mdt-image-composition-' + activeLayout);

            // Set active nav
            $('[data-mdt-image-composition-layout]').removeClass('selected');
            $('[data-mdt-image-composition-layout=' + activeLayout + ']').addClass('selected');

            // Draw the grid
            drawGrid();
        }

        /**
         * Draw the grid overlay.
         */
        function drawGrid() {

            // Remove all existing dividers.
            $('.mdt-image-composition-divide').remove();

            if(activeLayout && layouts[activeLayout].divides) {

                var x, y, width;

                $.each(layouts[activeLayout].divides, function(key, divide) {

                    // Default divider styles.
                    var $divide = $('<div />').addClass('mdt-image-composition-divide'),
                        styles  = {
                            background: dividerColor,
                            position: 'absolute',
                            zIndex: 100
                        };

                    if(divide.type === 'vertical') {

                        // Determine x-coordinate to start divider.
                        x = $composition.width() * ( divide.width / 100 ) - (dividerWidth / 2 );

                        if ( dividerWidth === 1 ) {

                            // Use lineTo method to draw 1px lines on canvas.
                            context.beginPath();
                            context.moveTo( x + 1, 0 );
                            context.lineTo( x + 1, $composition.height() );
                            context.stroke();
                        } else {

                            // Otherwise, draw a rectangle.
                            context.fillRect(x, 0, dividerWidth, $composition.height());
                        }

                        // Build styles for CSS dividers.
                        styles = { ...styles, ...{
                            height: '100%',
                            left: 'calc(' + divide.width + '% - ' + ( dividerWidth / 2 ) + 'px)',
                            top: 0,
                            width: dividerWidth + 'px',
                        } }
                    } else if (divide.type === 'horizontal') {

                        // Determine y-coordinate to start divider.
                        y = $composition.height() * ( divide.height / 100 ) - (dividerWidth / 2 );

                        if ( divide.width === 100) {
                            x = 0;
                            width = $composition.width();
                        } else {
                            x += dividerWidth / 2;
                            width = $composition.width() * ( divide.width / 100 );
                        }

                        if ( dividerWidth === 1 ) {

                            // Use lineTo method to draw 1px lines on canvas.
                            context.beginPath();
                            context.moveTo( x, y + 1 );
                            context.lineTo( x + width, y + 1 );
                            context.stroke();
                        } else {

                            // Otherwise, draw a rectangle.
                            context.fillRect(x, y, width, dividerWidth);
                        }

                        // Build styles for CSS dividers.
                        styles = { ...styles, ...{
                            height: dividerWidth + 'px',
                            left: (typeof(divide.left) !== 'undefined') ? divide.left + '%' : 0,
                            top: 'calc(' + divide.height + '% - ' + ( dividerWidth / 2 ) + 'px)',
                            width: (divide.width !== 100) ? divide.width + '%' : '100%',
                        } }
                    }

                    // Add CSS styles and append divider.
                    $composition.append( $divide.css( styles ) );
                });
            }
        }

        /**
         * Save image IDs to hidden field.
         */
        function saveImageIds() {
            $activeImages.val(JSON.stringify(activeImages));
        }

        // Set global fill style for white dividers.
        context.strokeStyle = dividerColor;
        context.fillStyle   = dividerColor;

        // Keep track of the maximum number of workspaces we need across all layouts.
        var maxWorkspaces = 0;

         // Create the layout nav menu.
        $.each(layouts, function(name, layout) {

            // Create the list item.
            var $li = $('<li>').attr('data-mdt-image-composition-layout', name);

            // Create blocks representing the zone.
            $.each( layout.zones, function(key, zone){
                $li.append(
                    $('<div>').css({
                        'width': zone.width + '%',
                        'height': zone.height + '%',
                        'top': zone.top + '%',
                        'left': zone.left + '%',
                    })
                 );
            });

            layoutClasses.push('mdt-image-composition-' + name);

            // Add the list item.
            $navigation.append( $li );

            // See if we're accounting for all the workspaces.
            if ( layout.zones.length > maxWorkspaces ) {
                maxWorkspaces = layout.zones.length;
            }
        });

        // Create the maximum number of workspaces.
        for ( var i = 0; i < maxWorkspaces; i++ ) {
            var workspace = createWorkspace();
            workspaces.push( workspace );
            $composition.append( workspace.$el );  
        }

        // Add aspect ratio selection
        $('<li>').append(
            $aspectRatio.append(
                $.map(aspectRatios, function(a) {
                    return $('<option>').text(a).val(a.replace(':','-'));
                })
            )
        ).appendTo( $navigation );

        // Load the templates with nav selection
        $('[data-mdt-image-composition-layout]').on('click', function(e) {
            e.preventDefault();
            activeLayout = $(this).data('mdt-image-composition-layout');
            setLayout( activeLayout );
        });

        $aspectRatio.on('change', function() {
            $composition.removeClass().addClass('mdt-image-composition mdt-image-composition-' + $aspectRatio.val());
            context.canvas.height = parseInt( $composition.height() );
            context.strokeStyle   = dividerColor;
            context.fillStyle     = dividerColor;
            setLayout( activeLayout );
            drawGrid();
        });

        // Set default layout
        activeLayout = 'equalHalf';
        setLayout( activeLayout );

    })();

})(jQuery);
