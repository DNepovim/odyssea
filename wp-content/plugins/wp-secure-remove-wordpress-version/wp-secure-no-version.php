<?php
/*
Plugin Name: WP-Secure Remove Wordpress Version
Plugin URI: http://www.wp-secure.net
Description: Removes the Wordpress version from the header of your HTML code
Version: 1.0a
Author: Stephen D. Sandecki
Author URI: http://www.wp-secure.net
*/

/*  Copyright 2009  Stephen D. Sandecki "wp-secure.net"  (email : stephen@wp-secure.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

remove_action('wp_head', 'wp_generator');

?>