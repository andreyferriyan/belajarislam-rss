<?php
/*
 * Belajar Islam RSS Dashboard
 */
require_once( plugin_dir_path( __FILE__ ) . '/belajarislam-rss.php' );

$an = new AN_Belajarislam_Rss();

an_belajarislam_rss_form($id,$maxList);
an_belajarislam_rss_form_handler();