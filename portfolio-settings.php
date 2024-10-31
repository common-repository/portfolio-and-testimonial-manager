<?php
function portfolio_settings() {
    global $wpdb; ?>
	<?php screen_icon(); ?>
	<div class="wrap">
        <?php pf_settings(); ?>
    </div><?php
} 

function PFSettingsValidate() {
    $err = array();
    if($_POST['txtSliderImageWidth']=="") {
        $err['SliderImageWidth'] = "Please enter the slider image width";
    }
    
    if($_POST['txtSliderImageHeight']=="") {
        $err['SliderImageHeight'] = "Please enter the slider image height";
    }
    
    if($_POST['txtPageImageWidth']=="") {
        $err['PageImageWidth'] = "Please enter the page image width";
    }
    
    if($_POST['txtPageImageHeight']=="") {
        $err['PageImageHeight'] = "Please enter the page image height";
    }
    
    if($_POST['txtPortfolioImageWidth']=="") {
        $err['PortfolioImageWidth'] = "Please enter the portfolio image width";
    }
    
    if($_POST['txtPortfolioImageHeight']=="") {
        $err['PortfolioImageHeight'] = "Please enter the portfolio image height";
    }
    
    return $err;
}

function pf_settings() {
    global $wpdb; ?>
    <div class="wrap nosubsub">
        <?php screen_icon(); ?>
        <div class="icon32 icon32-posts-post" id="icon-edit"><br /></div>
        <h2>Portfolio Settings</h2><?php
        $table_name = $wpdb->prefix."portfolio_settings";
        $action ="Save";
        
        if($_POST['submit'] == "Save Changes" && $_POST['hidSettingsId']=="") {
            $wpdb->query("INSERT INTO $table_name (slider_image_width, slider_image_height, page_image_width, page_image_height, portfolio_image_width, portfolio_image_height) VALUES ('".$_POST['txtSliderImageWidth']."', '".$_POST['txtSliderImageHeight']."', '".$_POST['txtPageImageWidth']."', '".$_POST['txtPageImageHeight']."', '".$_POST['txtPortfolioImageWidth']."', '".$_POST['txtPortfolioImageHeight']."')");
        }

        if($_POST['submit'] == "Update Changes" && $_POST['hidSettingsId']!="") {
            $FeeMsg = '';
            if(!PFSettingsValidate()) {
                $wpdb->query("UPDATE $table_name SET slider_image_width = '".$_POST['txtSliderImageWidth']."', slider_image_height = '".$_POST['txtSliderImageHeight']."', page_image_width = '".$_POST['txtPageImageWidth']."', page_image_height = '".$_POST['txtPageImageHeight']."', portfolio_image_width = '".$_POST['txtPortfolioImageWidth']."', portfolio_image_height = '".$_POST['txtPortfolioImageHeight']."' WHERE pf_settings_id = '".$_POST['hidSettingsId']."'");
                $FeeMsg = "Your settings was updated successfully.";
            } else {
                $FeeMsg = "Please fill in all the fields that are marked as mandatory (*)";
            }
            $_GET[id] = $_POST['hidSettingsId'];
            $slider_image_width = $_POST['txtSliderImageWidth'];
            $slider_image_height = $_POST['txtSliderImageHeight'];

            $page_image_width = $_POST['txtPageImageWidth'];
            $page_image_height = $_POST['txtPageImageHeight'];
            
            $portfolio_image_width = $_POST['txtPortfolioImageWidth'];
            $portfolio_image_height = $_POST['txtPortfolioImageHeight'];
            
            $action ="Update";
        } 
        
        
    	$query="SELECT * FROM $table_name";
    	$rows = $wpdb->get_results($query);
    	if(count($rows) > 0) {
    		$action ="Update";
    		foreach ($rows as $row) {
    			$pf_settings_id = $row->pf_settings_id;
    			
                $slider_image_width = $row->slider_image_width;
                $slider_image_height = $row->slider_image_height;
    
                $page_image_width = $row->page_image_width;
                $page_image_height = $row->page_image_height;
                
                $portfolio_image_width = $row->portfolio_image_width;
                $portfolio_image_height = $row->portfolio_image_height;
    		}
        } ?>

        <form name="thisForm" id="thisForm" method="post" action="">
        <input type="hidden" name="hidSettingsId" id="hidSettingsId" value="<?php echo ($_GET[id]) ? $_GET[id] : $pf_settings_id;?>" /><?php
        if($FeeMsg!="") { ?>
        	<div class="updated settings-error" id="setting-error-settings_updated"><p><strong><?php echo $FeeMsg; ?></strong></p></div><?php
        }?>	

        <table class="form-table psettings">
        	<tr valign="top">
        		<td width="270">Image size for Testimonial *</td>
        		<td>Width <input name="txtSliderImageWidth" type="text" id="txtSliderImageWidth" value="<?php echo $slider_image_width; ?>" lass="regular-text" maxlength="10" size="6" />px  ~  Height <input name="txtSliderImageHeight" type="text" id="txtSliderImageHeight" value="<?php echo $slider_image_height; ?>" lass="regular-text" maxlength="10" size="6" />px</td>
        	</tr>
            
            <tr valign="top">
        		<td>Image size for Portfolio listing *</td>
        		<td>Width <input name="txtPageImageWidth" type="text" id="txtPageImageWidth" value="<?php echo $page_image_width; ?>" lass="regular-text" maxlength="10" size="6" />px  ~  Height <input name="txtPageImageHeight" type="text" id="txtPageImageHeight" value="<?php echo $page_image_height; ?>" lass="regular-text" maxlength="10" size="6" />px</td>
        	</tr>
            
            <tr valign="top">
        		<td>Image size for Portfolio detail page *</td>
        		<td>Width <input name="txtPortfolioImageWidth" type="text" id="txtPortfolioImageWidth" value="<?php echo $portfolio_image_width; ?>" lass="regular-text" maxlength="10" size="6" />px  ~  Height <input name="txtPortfolioImageHeight" type="text" id="txtPortfolioImageHeight" value="<?php echo $portfolio_image_height; ?>" lass="regular-text" maxlength="10" size="6" />px</td>
        	</tr>
            
            <tr valign="top">
            	<td colspan="2"><p class="submit"><input type="submit" value="<?php echo $action?> Changes" class="button-primary" id="submit" name="submit"></p></td>
            </tr>    
        	
        </table>
        </form>
    </div><?php
} ?>