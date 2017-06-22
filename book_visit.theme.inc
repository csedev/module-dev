<?php

/**
 * @file
 * Preprocessors and theme functions of Booking module.
 */
 
/**
 * Prepares variables for booking templates.
 *
 * Default template: booking.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An array of elements to display in view mode.
 */
function template_preprocess_booking(&$variables) {
	$content = $variables['content'];

	$variables['title'] = $content->first_name . ' ' . $content->last_name;
}