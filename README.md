# Image Compositions

Combine multiple images in your media library into a single image.

## Installation and Setup

After activating Image Compositions on the Plugins page, you can access the tool by navigating to the *Image Compositions* page in the *Media* submenu.

## Creating an Image Composition

The canvas for your image composition is determined by two fields:

##### Layout
The default composition layouts are arranged as buttons at the top of the tool's page. Layouts specify the size and position of each workspace, each of which will contain a single image. Workspaces are separated by dividers.

##### Aspect Ratio
Next to the layout selectors, you will see a dropdown menu of aspect ratios. Changing the selection will stretch or shrink the canvas accordingly, while maintaining the workspaces defined by the layout.

When you've decided on a layout and an aspect ratio, you may begin your image selection.

### Adding an Image

To place an image, hover over one of the workspaces. You'll see a small *Add* button appear in the upper-left corner of the workspace. Clicking this button will display the standard WordPress media selector. When you've decided on an image, click the *Add Image* button in the bottom-right corner of the media modal.

You'll be returned to the canvas with the newly selected image positioned in the appropriate workspace. You have two options for manipulating the image within its workspace:

##### Dragging and Dropping
Once you've added an image to the workspace, you'll notice that your cursor has changed to the "move" type. You'll be able to drag and drop the image within the confines of the workspace so long as the image continues to cover the entire area.

##### Zooming
In the upper-left corner of the workspace, just below the *Add* button, you'll see two new buttons for zooming in and out. Much like the drag-and-drop, you'll only be able to zoom out for as long as the image covers the entire workspace.

Repeat the image selection for each workspace until your canvas is filled.

### Image Title (optional)

You may opt to title the image in the text field below the canvas. If no title is specified, a title will be automatically generated according to the UNIX timestamp.

### Saving

When the image is saved, you'll be redirected to the attachment page for the newly created image. As part of the attachment creation, there will be an attachment metadata field called `mdt_image_composition_source_id` added for each image in the composition, with the value being the attachment ID for that image.

## Configuration

There are a couple actions provided to help customize the Image Compositions tool. You can configure the canvas options by the using the `mdt_image_composition_configuration` WordPress filter, which passes an array that is localized for the tool's JavaScript file. Here are the current filterable options:

##### dividerWidth
The thickness of the divider placed between workspaces. "0" is an acceptable value for this property if you don't wish to have a visible divider.

##### dividerColor
Accepts a hex value for the color of the workspace divider.

##### layouts
Defines the available layout selections. To define a new layout, you must add a new key-value pair, where the key is a name to call the layout (i.e. "equalEighthts") and the value is an array with two properties: `zones` and `divides`.

The `zones` property is an array of workspaces, each of which contains four properties: `width` (the width of the workspace, expressed as a percentage within the overall canvas width), `height` (the workspace height), `left` (the position of the workspace from the leftmost edge of the canvas, expressed as a percentage) and `top` (the position of the workspace from the top).

The `divides` property is an array of divider placements, each of which contains up to four properties: `type` (either "vertical" or "horizontal"), `width` (the width of a horizontal divider, expressed as a percentage), `height` (the height of a vertical divider, expressed as a percentage) and `left` (the position of the divider from the leftmost edge of the canvas, expressed as a percentage).

##### aspectRatios
Defines an array of the selections available in the aspect ratio dropdown. New aspect ratios can be expressed as two numbers separated by a colon, i.e. `2:1`.

##### compositionWidth
The width of the overall composition, in pixels. The default composition width is 1000 pixels.

Here is an example of the filter in action, changing each of the aforementioned properties to customize the canvas:

```
add_filter( 'mdt_image_composition_configuration', function( $configuration ) {

	// Change the divider to 1px red
	$configuration['dividerColor'] = '#ff0000';
	$configuration['dividerWidth'] = 1;

	// Add a new layout called "equalEighths"
	$configuration['layouts']['equalEighths'] = [
		'zones' => [
			[
				'width'  => 25,
				'height' => 50,
				'left'   => 0,
				'top'    => 0
			],
			[
				'width'  => 25,
				'height' => 50,
				'left'   => 25,
				'top'    => 0
			],
			[
				'width'  => 25,
				'height' => 50,
				'left'   => 50,
				'top'    => 0
			],
			[
				'width'  => 25,
				'height' => 50,
				'left'   => 75,
				'top'    => 0
			],
			[
				'width'  => 25,
				'height' => 50,
				'left'   => 0,
				'top'    => 50
			],
			[
				'width'  => 25,
				'height' => 50,
				'left'   => 25,
				'top'    => 50
			],
			[
				'width'  => 25,
				'height' => 50,
				'left'   => 50,
				'top'    => 50
			],
			[
				'width'  => 25,
				'height' => 50,
				'left'   => 75,
				'top'    => 50
			]
		],
		'divides' => [
			[
				'type'   => 'vertical',
				'width'  => 25,
				'height' => 100,
			],
			[
				'type'   => 'vertical',
				'width'  => 50,
				'height' => 100,
			],
			[
				'type'   => 'vertical',
				'width'  => 75,
				'height' => 100,
			],
			[
				'type'   => 'horizontal',
				'width'  => 100,
				'height' => 50,
			]
		]
	];

	// Add a new 2:1 aspect ratio
	$configuration['aspectRatios'][] = '2:1';

	// Change the default composition width to 1100
	$configuration['compositionWidth'] = 1100;
	
	return $configuration;
} );
```

Finally, there is an action run called `mdt_image_composition_uploaded` that is fired immediately after a new composition is successfully uploaded. The new attachment ID is the only argument passed.
