<?php
/*
* SiteSense
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@sitesense.org so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade SiteSense to newer
* versions in the future. If you wish to customize SiteSense for your
* needs please refer to http://www.sitesense.org for more information.
*
* @author     Full Ambit Media, LLC <pr@fullambit.com>
* @copyright  Copyright (c) 2011 Full Ambit Media, LLC (http://www.fullambit.com)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
common_include('libraries/forms.php');
function admin_dynamicURLsBuild($data,$db) {
    if(!checkPermission('edit','dynamicURLs',$data)) {
        $data->output['abort'] = true;
        $data->output['abortMessage'] = '<h2>Insufficient User Permissions</h2>You do not have the permissions to access this area.';
        return;
    }
	$remapId = (int)$data->action[3];
	$check = $db->prepare('getUrlRemapById','admin_dynamicURLs');
	$check->execute(array(
		':id' => $remapId
	));
	// Check To Make Sure It Exists
	if(($data->output['urlremap'] = $check->fetch()) === FALSE){
		$data->output['abort'] = true;
		$data->output['abortMessage'] = '<h2>ID does not exist in database</h2>';
		return;
	}
    if(!$data->output['urlremap']['regex']) {
        // Standard Mode
        $data->output['urlremap']['match']=str_replace('^','',$data->output['urlremap']['match']);
        $data->output['urlremap']['match']=str_replace('(/.*)?$','',$data->output['urlremap']['match']);
        $data->output['urlremap']['replace']=str_replace('\1','',$data->output['urlremap']['replace']);
    }
	// Create The Form
	$form = $data->output['remapForm'] = new formHandler('addEdit',$data,true);
	$form->caption = 'Editing URL Remap';
	
	if ((!empty($_POST['fromForm'])) && ($_POST['fromForm']==$form->fromForm)) {
		// Populate The Send Array
		$form->populateFromPostData();
		if ($form->validateFromPost()) {
			if(!$data->output['urlremap']['regex']) {
                // Remove
                $form->sendArray[':match']=str_replace('^','',$form->sendArray[':match']);
                $form->sendArray[':match']=str_replace('(/.*)?$','',$form->sendArray[':match']);
                $form->sendArray[':replace']=str_replace('\1','',$form->sendArray[':replace']);
                // Trim Forward Slashes + Whitespace from Beginning and End
                $form->sendArray[':match']=trim($form->sendArray[':match']);
                $form->sendArray[':replace']=trim($form->sendArray[':replace']);
                $form->sendArray[':match']=trim($form->sendArray[':match'],'/');
                $form->sendArray[':replace']=trim($form->sendArray[':replace'],'/');
                // Add Regex
                $form->sendArray[':match']='^'.$form->sendArray[':match'].'(/.*)?$';
                $form->sendArray[':replace']=$form->sendArray[':replace'].'\1';
            }
            $statement = $db->prepare('editUrlRemap','admin_dynamicURLs');
            $form->sendArray[':id'] = $remapId;
            $result = $statement->execute($form->sendArray) ;
			
			if($result == FALSE) {
                $data->output['remapForm']->fields['match']['error']=true;
                $data->output['remapForm']->fields['match']['errorList'][]='<h2>URL Routing Conflict:</h2> Duplicate Regular Expression Exists';
                return;
			}
			
			if (empty($data->output['secondSidebar'])) {
				$data->output['savedOkMessage']='
					<h2>Remap Saved Successfully</h2>
					<div class="panel buttonList">
						<a href="'.$data->linkRoot.'admin/dynamic-urls/add">
							Add New URL Remap
						</a>
						<a href="'.$data->linkRoot.'admin/dynamic-urls/list/">
							Return to URL Remap List
						</a>
					</div>';
			}
		} else {
			/*
				invalid data, so we want to show the form again
			*/
			$data->output['secondSidebar']='
				<h2>Error in Data</h2>
				<p>
					There were one or more errors. Please correct the fields with the red X next to them and try again.
				</p>';
		}
	}
}
function admin_dynamicURLsShow($data) {
	if (isset($data->output['savedOkMessage'])) {
		echo $data->output['savedOkMessage'];
	} else {
		theme_buildForm($data->output['remapForm']);
	}
}
?>