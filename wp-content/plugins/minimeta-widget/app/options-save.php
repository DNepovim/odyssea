<?php
// don't load directly 
if ( !defined('ABSPATH') ) 
	die('-1');

// Form Processing
$mmconfigid=$_REQUEST['mmconfigid'];

// Copy  Config
if ($_GET['action']=='copy' and !empty($mmconfigid)) {
	check_admin_referer('mmconfig_'.$mmconfigid);
	$options_widgets = get_option('minimeta_widget_options');
	
	//generate New number
	$newnumber=wp_create_nonce(mt_rand(10, 30));
	while (is_array($options_widgets[$newnumber])) {
		$newnumber=wp_create_nonce(mt_rand(10, 30));
	}
	//make config copy
	$options_widgets[$newnumber]=$options_widgets[$mmconfigid];
	$options_widgets[$newnumber]['optionname']=htmlentities(stripslashes(__('Copy of', 'MiniMetaWidget').' '.$options_widgets[$mmconfigid]['optionname']));
	
	if (update_option('minimeta_widget_options', $options_widgets)) {
		$minimeta_options_text = '<font color="green">'.sprintf(__('Copy of "%s" Config Created', 'MiniMetaWidget'),$options_widgets[$mmconfigid]['optionname']).'</font>';
	} else {
		$minimeta_options_text = '<font color="red">'.sprintf(__('Copy of "%s" Config NOT Created', 'MiniMetaWidget'),$options_widgets[$mmconfigid]['optionname']).'</font>';
	}
	
	$mmconfigid=$newnumber;
}

//Delete Config
if ($_GET['action']=='delete' and !empty($mmconfigid)) {
	check_admin_referer('delete-mmconfig_'.$mmconfigid);
	$options_widgets = get_option('minimeta_widget_options');
	
	$name=$options_widgets[$mmconfigid]['optionname']; 
	unset($options_widgets[$mmconfigid]);
	
	$i=0; //test if config exists
	foreach ($options_widgets as $optionid) {
		$i++;
	}
	
	if ($i==0) { //only Update if a config exists
		if (delete_option('minimeta_widget_options')) {
			add_option('minimeta_widget_options');
			$minimeta_options_text = '<font color="green">'.sprintf(__('Config "%s" Deleted', 'MiniMetaWidget'),$name).'</font>';
			$mmconfigid="";
		} else {
			$minimeta_options_text = '<font color="red">'.sprintf(__('Config "%s" NOT Deleted', 'MiniMetaWidget'),$name).'</font>';
		}
	} else {
		if (update_option('minimeta_widget_options', $options_widgets)) {
			$minimeta_options_text = '<font color="green">'.sprintf(__('Config "%s" Deleted', 'MiniMetaWidget'),$name).'</font>';
			$mmconfigid="";
		} else {
			$minimeta_options_text = '<font color="red">'.sprintf(__('Config "%s" NOT Deleted', 'MiniMetaWidget'),$name).'</font>';
		}
	}
}

// Update Options
if(!empty($_POST['Submit']) and !empty($mmconfigid)) {
	check_admin_referer('MiniMeta-options');
	$options_widgets = get_option('minimeta_widget_options');
	
	//write every options tab to optiones
	if (is_array($_POST['widget-options'][$mmconfigid])) {
		if (is_array($options_widgets[$mmconfigid])) //Clean old values
			unset($options_widgets[$mmconfigid]);
	    $options_widgets[$mmconfigid]['optionname'] = htmlentities(stripslashes($_POST['widget-options'][$mmconfigid]['optionname']));
		//Save Ordering
		$options_widgets[$mmconfigid]['order']['in']=str_replace("&in[]=",",",substr($_POST['widget-options'][$mmconfigid]['order']['in'],5));
		$options_widgets[$mmconfigid]['order']['out']=str_replace("&out[]=",",",substr($_POST['widget-options'][$mmconfigid]['order']['out'],6));
		//Save general options
		$options_widgets[$mmconfigid]['general']['style']['ul'] = $_POST['widget-options'][$mmconfigid]['general']['style']['ul'];
		$options_widgets[$mmconfigid]['general']['style']['li'] = $_POST['widget-options'][$mmconfigid]['general']['style']['li'];
		$options_widgets[$mmconfigid]['general']['class']['ul'] = $_POST['widget-options'][$mmconfigid]['general']['class']['ul'];
		$options_widgets[$mmconfigid]['general']['class']['li'] = $_POST['widget-options'][$mmconfigid]['general']['class']['li'];
		$options_widgets[$mmconfigid]['general']['php']['title'] = $_POST['widget-options'][$mmconfigid]['general']['php']['title'];
		$options_widgets[$mmconfigid]['general']['php']['before_title'] = $_POST['widget-options'][$mmconfigid]['general']['php']['before_title'];
		$options_widgets[$mmconfigid]['general']['php']['after_title'] = $_POST['widget-options'][$mmconfigid]['general']['php']['after_title'];
		$options_widgets[$mmconfigid]['general']['php']['before_widget'] = $_POST['widget-options'][$mmconfigid]['general']['php']['before_widget'];
		$options_widgets[$mmconfigid]['general']['php']['after_widget'] = $_POST['widget-options'][$mmconfigid]['general']['php']['after_widget'];
		$options_widgets[$mmconfigid]['general']['pagesnot']['notselected'] = isset($_POST['widget-options'][$mmconfigid]['general']['pagesnot']['notselected']);
		$options_widgets[$mmconfigid]['general']['pagesnot']['out']['home'] = isset($_POST['widget-options'][$mmconfigid]['general']['pagesnot']['out']['home']);
		$options_widgets[$mmconfigid]['general']['pagesnot']['out']['singlepost'] = isset($_POST['widget-options'][$mmconfigid]['general']['pagesnot']['out']['singlepost']);
		$options_widgets[$mmconfigid]['general']['pagesnot']['out']['search'] = isset($_POST['widget-options'][$mmconfigid]['general']['pagesnot']['out']['search']);
		$options_widgets[$mmconfigid]['general']['pagesnot']['out']['errorpages'] = isset($_POST['widget-options'][$mmconfigid]['general']['pagesnot']['out']['errorpages']);
		$options_widgets[$mmconfigid]['general']['pagesnot']['in']['home'] = isset($_POST['widget-options'][$mmconfigid]['general']['pagesnot']['in']['home']);
		$options_widgets[$mmconfigid]['general']['pagesnot']['in']['singlepost'] = isset($_POST['widget-options'][$mmconfigid]['general']['pagesnot']['in']['singlepost']);
		$options_widgets[$mmconfigid]['general']['pagesnot']['in']['search'] = isset($_POST['widget-options'][$mmconfigid]['general']['pagesnot']['in']['search']);
		$options_widgets[$mmconfigid]['general']['pagesnot']['in']['errorpages'] = isset($_POST['widget-options'][$mmconfigid]['general']['pagesnot']['in']['errorpages']);
		$options_widgets[$mmconfigid]['general']['pagesnot']['in']['pages'] = $_POST['widget-options'][$mmconfigid]['general']['pagesnot']['in']['pages'];
		$options_widgets[$mmconfigid]['general']['pagesnot']['out']['pages'] = $_POST['widget-options'][$mmconfigid]['general']['pagesnot']['out']['pages'];
		//Save option for in and out and sort
		$ordering=0;
		$sort=split(",",$options_widgets[$mmconfigid]['order']['in']);
		for ($i=0; $i<sizeof($sort);$i++) {
			if(isset($_POST['widget-options'][$mmconfigid]['in'][$sort[$i]]['active'])) {
				$options_widgets[$mmconfigid]['in'][$ordering]['part']=$_POST['widget-options'][$mmconfigid]['in'][$sort[$i]]['part'];
				if (is_array($_POST['widget-options'][$mmconfigid]['in'][$sort[$i]]['args'])) {
					foreach($_POST['widget-options'][$mmconfigid]['in'][$sort[$i]]['args'] as $argkey => $argvalue) {
						$options_widgets[$mmconfigid]['in'][$ordering]['args'][$argkey]=$argvalue;
						if ($argvalue=='1')
							$options_widgets[$mmconfigid]['in'][$ordering]['args'][$argkey]=true;
					}
				}
				$ordering++;
			}
		}
		$ordering=0;
		$sort=split(",",$options_widgets[$mmconfigid]['order']['out']);
		for ($i=0; $i<sizeof($sort);$i++) {
			if(isset($_POST['widget-options'][$mmconfigid]['out'][$sort[$i]]['active'])) {
				$options_widgets[$mmconfigid]['out'][$ordering]['part']=$_POST['widget-options'][$mmconfigid]['out'][$sort[$i]]['part'];
				if (is_array($_POST['widget-options'][$mmconfigid]['out'][$sort[$i]]['args'])) {
					foreach($_POST['widget-options'][$mmconfigid]['out'][$sort[$i]]['args'] as $argkey => $argvalue) {
						$options_widgets[$mmconfigid]['out'][$ordering]['args'][$argkey]=$argvalue;
						if ($argvalue=='1')
							$options_widgets[$mmconfigid]['out'][$ordering]['args'][$argkey]=true;
					}
				}
				$ordering++;
			}
		}
	}
	
	if (update_option('minimeta_widget_options', $options_widgets)) {
		$minimeta_options_text = '<font color="green">'.sprintf(__('Config "%s" Updated', 'MiniMetaWidget'),$options_widgets[$mmconfigid]['optionname']).'</font>';

	} else {
		$minimeta_options_text = '<font color="red">'.sprintf(__('Config "%s" NOT Updated', 'MiniMetaWidget'),$options_widgets[$mmconfigid]['optionname']).'</font>';
	}
}
?>