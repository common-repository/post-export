<?php
/**
 * @package Posts_bulk_export
 * @version 1.0
 */
/*
Plugin Name: Post Export
Plugin URI: http://www.nethuesindia.com
Description: Allows admin to bulk export posts.
Author: Nethuesindia
Version: 1.0
Plugin URI: http://www.nethuesindia.com
*/
session_start();
// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }
/**
 * Indicates that a clean exit occured. Handled by set_exception_handler
 */
if (!class_exists('E_Clean_Exit')) {
	class E_Clean_Exit extends RuntimeException
	{
	}
}
$exportListArray									= array();
$exportListArray['post_id']							= "Post Id";
$exportListArray['post_title']						= "Title";
$exportListArray['post_desc']						= "Description";
$exportListArray['post_categories']					= "Categories";
$exportListArray['post_tags']						= "Tags";
$exportListArray['post_url']						= "Url";
$exportListArray['post_status']						= "Status";
$exportListArray['post_date']						= "Date Added";

if (!class_exists('FRS_Custom_Bulk_Action'))
{
	class FRS_Custom_Bulk_Action
	{
		public function __construct()
		{
			if(is_admin())
			{
				global $pagenow;
				global $post_type;
				$postType = $_REQUEST['post_type'];
				if($pagenow == 'edit.php' && $postType != 'page') {
					wp_register_style('export_style.css', plugin_dir_url(__FILE__) . 'css/export_style.css');
					wp_enqueue_style('export_style.css');
					
					wp_register_script('export_style.js', plugin_dir_url(__FILE__) . 'js/export_style.js', array('jquery'));
					wp_enqueue_script('export_style.js');
					
					add_action('admin_footer-edit.php', array(&$this, 'custom_bulk_admin_footer'));
					add_action('load-edit.php',         array(&$this, 'custom_bulk_action'));
				}
			}
		}
		
		/**
		 * Step 1: add the custom Bulk Action to the select menus
		 */
		function custom_bulk_admin_footer()
		{
			global $post_type;
			
			if($post_type == 'post') 
			{
				global $wp_query;
				$_SESSION['custom_pl']['per_page']		= $wp_query->query_vars['posts_per_page'];
				$_SESSION['custom_pl']['query']			= $wp_query->request;
				$_SESSION['custom_pl']['total']			= $wp_query->found_posts;
				$totalPages								= $wp_query->max_num_pages;
				?>
                <script type="text/javascript">
					var totalPages= <?php echo $totalPages;?>;
				</script>
				<div id="bulk-edit-export">
					<form action="" method="post" id="bulkExportForm">
						<input type="hidden" id="isBulkExport" name="isBulkExport" value="1" />
						<div id="firstColumnExport">
							<h4 style="margin:0px 0px 5px 0px;">Bulk Export</h4>
							<div>
								<input type="radio" value="1" id="export_post_options" name="export_options" onchange="changeExportOptions(1);"  checked='checked' /> <label for="export_post_options">Selected Posts</label>&nbsp;&nbsp;&nbsp;
								<input type="radio" value="2" id="export_page_options" name="export_options" onchange="changeExportOptions(2);" /> <label for="export_page_options">All Posts</label>
							</div>
                            <div id="exportPostIDDiv">
							</div>
							<div id="exportPageIDDiv" style="display:none;">
								<?php
								for($i = 1; $i <= $totalPages; $i++)
								{
									?>
									<div>
										<input type="checkbox" id="bulk_export_page_id_<?php echo $i;?>" name="bulk_export_page_id[]" value="<?php echo $i;?>" />
										<label for="bulk_export_page_id_<?php echo $i;?>">Page <?php echo $i;?></label>
									</div>
									<?php
								}
								?>
							</div>
						</div>
                        <div id="secondColumnExport">
							<h4 style="margin:0px 0px 5px 0px;">Select Fields to Export</h4>
							<div>
								<input type="checkbox" value="1" id="export_post_fields_check_all" name="export_post_fields_check_all" onchange="checkAllPostFields(this.checked);" /> <label for="export_post_fields_check_all"><b>Check/Uncheck All</b></label>
							</div>
							<div id="exportActionDiv">
								<table cellpadding="0" cellspacing="0" width="100%">
                                	<tr>
										<td>
											<input type="checkbox" id="post_id" name="post_id" value="1" checked="checked" /> <label for="post_id">ID</label>
										</td>
										<td>
											<input type="checkbox" id="post_title" name="post_title" value="1" /> <label for="post_title">Title</label>
										</td>
									</tr>
									<tr>
										<td>
											<input type="checkbox" id="post_desc" name="post_desc" value="1" /> <label for="post_desc">Description</label>
										</td>
										<td>
											<input type="checkbox" id="post_categories" name="post_categories" value="1" /> <label for="post_categories">Categories</label>
										</td>
									</tr>
									<tr>
										<td>
											<input type="checkbox" id="post_tags" name="post_tags" value="1" /> <label for="post_tags">Tags</label>
										</td>
										<td>
											<input type="checkbox" id="post_url" name="post_url" value="1" /> <label for="post_url">Url</label>
										</td>
									</tr>
									<tr>
										<td>
											<input type="checkbox" id="post_status" name="post_status" value="1" /> <label for="post_status">Status</label>
										</td>
										<td>
											<input type="checkbox" id="post_date" name="post_date" value="1" /> <label for="post_date">Date Added</label>
										</td>
									</tr>
								</table>
							</div>
                            <div class="exposrtbtns">
								<input type="button" id="submitExportButton" name="submitExportButton" value="Export" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" id="cancelExportButton" name="cancelExportButton" value="Cancel" />
							</div>
						</div>
						<br style="clear:both;" />
					</form>
				</div>			
				<?php
	    	}
		}	
		function custom_bulk_action() {
			global $typenow, $wpdb, $exportListArray;
			$post_type 	= $typenow;
			if($post_type == 'post') {
				$isBulkExport	= isset($_POST['isBulkExport'])?(int)$_POST['isBulkExport']:0;
				
				if($isBulkExport == 1) {
					$getPostList	= array();
					$export_type	= isset($_POST['export_type'])?(int)$_POST['export_type']:1;
					$export_options	= isset($_POST['export_options'])?(int)$_POST['export_options']:1;
					if($export_options == 1) {
						$getPostList	= isset($_POST['bulk_export_post_id'])?$_POST['bulk_export_post_id']:array();
					}
					else {
						$postPerPage	= isset($_SESSION['custom_pl']['per_page'])?(int)$_SESSION['custom_pl']['per_page']:25;
						if($postPerPage < 1)
							$postPerPage	= 25;
						$totalRecords	= isset($_SESSION['custom_pl']['total'])?(int)$_SESSION['custom_pl']['total']:0;
						if($totalRecords > 0) {
							$totalPages		= ceil($totalRecords/$postPerPage);
							$masterQuery	= isset($_SESSION['custom_pl']['query'])?trim($_SESSION['custom_pl']['query']):"";
							$breakQuery		= explode("LIMIT", $masterQuery);
							$masterQuery	= trim($breakQuery[0]);
							
							$getPageIDArray	= isset($_POST['bulk_export_page_id'])?$_POST['bulk_export_page_id']:array();
							
							if(count($getPageIDArray) > 0)
							{
								foreach($getPageIDArray as $temp_page_no)
								{
									if($temp_page_no > 0 && $temp_page_no <= $totalPages)
									{
										$startRecord	= ($temp_page_no - 1) * $postPerPage;
										$customQuery	= $masterQuery." LIMIT ".$startRecord.", ".$postPerPage;
										$resultCustom	= $wpdb->get_results($customQuery);
										foreach($resultCustom as $temp_post_obj)
										{
											$getPostList[]	= $temp_post_obj->ID;
										}
									}
								}
							} else {
								for($temp_page_no = 1; $temp_page_no <= $totalPages; $temp_page_no++) {
									if($temp_page_no > 0 && $temp_page_no <= $totalPages) {
										$startRecord	= ($temp_page_no - 1) * $postPerPage;
										$customQuery	= $masterQuery." LIMIT ".$startRecord.", ".$postPerPage;
										$resultCustom	= $wpdb->get_results($customQuery);
										foreach($resultCustom as $temp_post_obj)
										{
											$getPostList[]	= $temp_post_obj->ID;
										}
									}
								}
							}
						}
					}
					if(count($getPostList) > 0) {
						$ctr = 0;
						$xmlData  = array();
						foreach($getPostList as $temp_post_id) {
							if($temp_post_id > 0) {
								
								$postData = get_post( $temp_post_id, ARRAY_A );
								$xmlDataTab  = array();
								foreach($_POST as $exportField=>$exportValue) {
									if(!is_array($exportField) && isset($exportListArray[$exportField]) && $exportListArray[$exportField] != '') {
										if($exportField == "post_id") {
											array_push($xmlDataTab, $exportListArray[$exportField]);
											$xmlData[$ctr][] = $postData['ID'];
										}
										
										if($exportField == "post_title") {
											array_push($xmlDataTab, $exportListArray[$exportField]);
											$xmlData[$ctr][] = stripslashes($postData['post_title']);
										}
										 
										if($exportField == "post_desc") {
											array_push($xmlDataTab, $exportListArray[$exportField]);
											$xmlData[$ctr][] = stripslashes($postData['post_content']);
										}
										 
										if($exportField == "post_categories") {
										 	$tempCat = "";
											$catData = array();
											$catAllData = get_the_category($temp_post_id);
											if(count($catAllData)>0) {
												foreach($catAllData as $catKey => $catData) {
													$tempCat .= stripslashes($catData->name).",";
												}
												$xmlData[$ctr][] = trim($tempCat,",");
											} else {
												$xmlData[$ctr][] = "No Categories";
											}
											array_push($xmlDataTab, $exportListArray[$exportField]);
										}
										if($exportField == "post_tags") {
										 	$tempTag = "";
											if(count($postData['tags_input'])>0) {
												foreach($postData['tags_input'] as $tagKey => $tag) {
													$tempTag .= stripslashes($tag).",";
												}
												$xmlData[$ctr][] = trim($tempTag,",");
											} else {
												$xmlData[$ctr][] = "No tags";
											}
											array_push($xmlDataTab, $exportListArray[$exportField]);
										}
										
										if($exportField == "post_url") {
											array_push($xmlDataTab, $exportListArray[$exportField]);
											$xmlData[$ctr][] = $postData['guid'];
										}										
										if($exportField == "post_status") {
											array_push($xmlDataTab, $exportListArray[$exportField]);
											$xmlData[$ctr][] = $postData['post_status'];
										}
										if($exportField == "post_date") {
											array_push($xmlDataTab, $exportListArray[$exportField]);
											$xmlData[$ctr][] = $postData['post_date'];
										}								 
									}
									
								}
							}
							$ctr++;
						}
						$xmlDataTab = array($xmlDataTab);
						$dataComplete = array_merge($xmlDataTab,$xmlData);
						$file = get_home_path()."wp-content/uploads/posts_data.csv";
						$fp = fopen($file, 'w');
						foreach ($dataComplete as $fields) {
							fputcsv($fp, $fields);
						}
						fclose($fp);
						header("Cache-Control: public");
						header("Content-Description: File Transfer");
						header("Content-Disposition: attachment; filename=posts_data.csv");
						header("Content-Type: application/csv");
						header("Content-Transfer-Encoding: binary");
						
						// Read the file from disk
						readfile($file);
						exit;
					}
					else
					{
						$sendback 		= remove_query_arg( array('option_edited'), wp_get_referer() );
						return false;
					}
					die;
				}
			}
		}
	}
}
new FRS_Custom_Bulk_Action();