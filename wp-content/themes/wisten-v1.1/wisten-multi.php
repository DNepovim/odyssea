<?php
/**
 * Template name: Multi Section
 * The Template for displaying all single posts
 *
 * @package WordPress
 * @subpackage Wisten
 * @since Wisten 1.0
 */

get_header(); 
FastWP_UI::build_multi_page(true); 
get_sidebar(); 
get_footer(); 