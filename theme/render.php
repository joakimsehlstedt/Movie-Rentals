<?php
/**
 * Render content to theme.
 *
 */

// Extract variables from the Ami array for easier access in the template files.
extract($ami);

// Include the template functions.
include(__DIR__ . '/functions.php');

// Include the template file.
include(__DIR__ . '/index.tpl.php');
