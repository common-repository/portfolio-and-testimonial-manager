<?php
function view_testimonials($atts) {
    global $wpdb; 
    $args = array( 'post_type' => 'portfolio' );
    $loop = new WP_Query( $args );
    $portfolio_count = $loop->post_count;
    if($portfolio_count > 0) { ?>
        <div id="testimonial-slider" class="testimonial-slider">
            <a title="" href="#" class="prev"></a>
            <div class="slides"><?php
            while ( $loop->have_posts() ) : $loop->the_post();
        
                $meta = get_post_meta( get_the_ID() );
        
                $project_url = get_post_meta( get_the_ID(), '_projecturl', true );
                $testimonial = get_post_meta( get_the_ID(), '_testimonial', true );
                $client_name = get_post_meta( get_the_ID(), '_clientname', true );
                
                $mylink = $wpdb->get_row("SELECT attached_image_id FROM ".$wpdb->prefix."portfolio_images WHERE portfolio_id = ". get_the_ID() );
                $attached_image_id = $mylink->attached_image_id;
                
                $img_arr = wp_get_attachment_image_src( $attached_image_id, 'testimonial_image' ); 
                
                $category_name = '';
                $category_name_arr = array();
                $category_term_arr = get_the_terms(get_the_ID(), 'portfolio-category');
                if(is_array($category_term_arr)) {
                    foreach($category_term_arr as $term)
                        $category_name_arr[]= $term->slug;
                }
                
                if(count($category_name_arr)>0) {
                    $category_name = implode(', ', $category_name_arr);
                }
                ?>
				<div>
                    <div class="testimonial-col1">
                        <?php
                        if(isset($img_arr) && $img_arr[0]!="") { ?>
                    	   <div class="testimonials-carousel-thumbnail"><img alt="" src="<?php echo $img_arr[0]; ?>"></div><?php
                        } ?>
						<?php
                        if($project_url) { ?>
                            <a href="<?php echo str_replace('"',"&#34;",stripslashes($project_url)); ?>" target="_blank" class="visit-site">Visit Site</a><?php
                        } ?>
                    </div>
                    <div class="testimonial-col2">
                    	<span class="testimonial-title"><?php the_title(); ?></span>
                        <span class="portfolio-label"><?php echo $category_name; ?></span>
        				<div class="testi-content"><?php echo stripslashes($testimonial); ?></div>
                        <span class="portfolio-label">- <?php echo str_replace('"',"&#34;",stripslashes($client_name)); ?></span>
                    </div>
                </div>
                <?php
            endwhile;  ?>
        </div>
        
    <a title="" href="#" class="next"></a>
    <script>
		jQuery("document").ready(function(){
    jQuery("div#testimonial-slider").jContent({orientation: 'horizontal', 
        easing: "easeOutCirc", 
        duration: 500,
        circle: true});
 });
	</script>
</div><?php
    } else {?>
        <div>There is no testimonial available at the moment. Please check back later.</div><?php
    }
} 

function view_portfolios($atts) {
    global $wpdb; 
    global $wp_query;
    $curr_page_id = get_the_ID();

    $id = isset( $wp_query->query_vars['portfolio_id'] ) ? absint( $wp_query->query_vars['portfolio_id'] ) : '';
    if($id == '') { ?>    
        <div id="container"><?php
            $i=1;
            $args = array( 'post_type' => 'portfolio' );
            $loop = new WP_Query( $args );
            $portfolio_count = $loop->post_count;
            if($portfolio_count > 0) { ?>
                <div class="filter-options">
                	<a data-group="all" class="active">All</a><?php
                    $taxonomies = get_terms('portfolio-category', 'hide_empty=1'); 
                    if($taxonomies) {
                        foreach ($taxonomies  as $taxonomy ) {
                            $category_name = str_replace('"',"&#34;",stripslashes($taxonomy->name));
                            $category_slug = str_replace('"',"&#34;",stripslashes($taxonomy->slug)); ?>
                            <a data-group="<?php echo strip_tags($category_slug); ?>"><?php echo $category_name; ?></a><?php
                        }
                    } ?>
                </div>
                <div class="clear"></div>
                <div id="grid" class="portfolioContainer"><?php
                
                
                $theSettings = $wpdb->get_row('SELECT page_image_width, page_image_height FROM '.$wpdb->prefix.'portfolio_settings');
                
                $portfolio_image_width = $theSettings->page_image_width;
                $portfolio_image_height = $theSettings->page_image_height;
    
                while ( $loop->have_posts() ) : $loop->the_post();
                    $pf_portfolio_id = get_the_ID();
                    $meta = get_post_meta( get_the_ID() );
                    $project_url = get_post_meta( get_the_ID(), '_projecturl', true );
                    $testimonial = get_post_meta( get_the_ID(), '_testimonial', true );
                    $client_name = get_post_meta( get_the_ID(), '_clientname', true );
        
                    $mylink = $wpdb->get_row("SELECT attached_image_id FROM ".$wpdb->prefix."portfolio_images WHERE portfolio_id = ". get_the_ID() );
                    $attached_image_id = $mylink->attached_image_id;
                    $img_arr = wp_get_attachment_image_src( $attached_image_id, 'portfolio_image' );
                    $terms = get_the_terms( $pf_portfolio_id, 'portfolio-category' ); 
                    $category_name = '';
                    $category_name_arr = array();
                    $category_term_arr = get_the_terms($pf_portfolio_id, 'portfolio-category');
                    if(is_array($category_term_arr)) {
                        foreach($category_term_arr as $term)
                            $category_name_arr[]= $term->slug;
                    }
                    
                    if(count($category_name_arr)>0) {
                        $category_name = implode('", "', $category_name_arr);
                    }

                   	echo "<div class=\"picture-item\" data-groups='[\"".strip_tags($category_name)."\"]'>"; ?>
                        <a href="<?php echo add_query_arg( 'portfolio_id', $pf_portfolio_id, get_permalink($curr_page_id) ); ?>" ><?php
                            if(isset($img_arr) && $img_arr[0]!="") { ?>
                                <img  src="<?php echo $img_arr[0]; ?>" border="0" /><?php
                            } else {?>
                            	<div style="width:<?php echo $portfolio_image_width; ?>px; height:<?php echo $portfolio_image_height; ?>px; margin-bottom:12px; text-align:center;"><img src="<?php echo plugins_url();?>/portfolio-and-testimonial-manager/img/no-image.jpg" border="0" /></div><?php
                            } ?>
                            <h3><?php the_title(); ?></h3>  
                        </a>
                        <a href="<?php echo add_query_arg( 'portfolio_id', $pf_portfolio_id, get_permalink($curr_page_id) ); ?>" class="mask"></a>
                    </div><?php
                    $i = $i + 1;
                endwhile;?>
                </div><?php
            } else { ?>
                <div>There is no portfolio available at the moment. Please check back later.</div><?php
            }?>
        </div>
        
        <?php
    } else {
        $post_in_arr = array($id);
        $args = array( 'post_type' => 'portfolio', 'post__in' => $post_in_arr );
        $loop = new WP_Query( $args );
        $portfolio_count = $loop->post_count;
        if($portfolio_count > 0) {
            while ( $loop->have_posts() ) : $loop->the_post();
                $pf_portfolio_id = get_the_ID();
                $meta = get_post_meta( get_the_ID() );
                $project_url = get_post_meta( get_the_ID(), '_projecturl', true );
                $testimonial = get_post_meta( get_the_ID(), '_testimonial', true );
                $client_name = get_post_meta( get_the_ID(), '_clientname', true );
                
                $mylink = $wpdb->get_row("SELECT attached_image_id FROM ".$wpdb->prefix."portfolio_images WHERE portfolio_id = ". get_the_ID() );
                $attached_image_id = $mylink->attached_image_id;
                
                $portfolio_big_img_arr = wp_get_attachment_image_src( $attached_image_id, 'portfolio_big_image' );
                
                $category_name = get_the_term_list( $pf_portfolio_id, 'portfolio-category', '', ', ', '' ); ?>

                <div class="portfolio-image"><?php
                if(isset($portfolio_big_img_arr) && $portfolio_big_img_arr[0]!="") { ?>
                    <img src="<?php echo $portfolio_big_img_arr[0]?>" alt="" border="0"><?php
                } ?>
                </div>
      
                <div class="portfolio-content">
                    <h2><?php echo the_title(); ?></h2>
                    <a title="back to portfolio list" href="<?php echo get_permalink($curr_page_id); ?>" class="portfolio-all"></a>
                    <?php
                        if($client_name != "") { ?>
                    <span class="portfolio-label">Client: </span><?php echo $client_name; ?><br />
                    <?php  }
                        if($category_name != "") { ?>
                        <span class="portfolio-label">Technology: </span> <?php echo strip_tags($category_name); ?>
                    <?php  } ?><br />
					<div class="portfolio-col1">
					<?php
                      if(the_content) { ?> 
                        <div class="portfolio-desc"><?php the_content(); ?></div><?php
                      } 
					  if($project_url != "") {
					?>
					  <a href="<?php echo str_replace('"',"&#34;",stripslashes($project_url)); ?>" target="_blank">Visit this website</a>
                    <?php } ?>  
                    </div>  
					<?php if($testimonial!="") { ?>
                        <div class="testimonial-txt">
						<?php echo str_replace('"',"&#34;",stripslashes($testimonial)); ?><br /><?php
                        echo "<strong>- ".str_replace('"',"&#34;",stripslashes($client_name))."</strong><br /></div>";
                    } ?>
				<div class="clear"></div>
                <hr /></div><?php
            endwhile;
        }
        		
        if($category_name!="") {
            $i=1;
            $post_not_in_arr = array($id);    
            $args = array( 'post_type' => 'portfolio', 'post__not_in' => $post_not_in_arr );
            $loop = new WP_Query( $args );
            $portfolio_count = $loop->post_count;
            if($portfolio_count > 0) { ?>
                <h3>Similar Work</h3>
                <div class="portfolioContainer"><?php
                while ( $loop->have_posts() ) : $loop->the_post();
                    $pf_portfolio_id = get_the_ID();
                    $meta = get_post_meta( get_the_ID() );
                    $project_url = get_post_meta( get_the_ID(), '_projecturl', true );
                    $testimonial = get_post_meta( get_the_ID(), '_testimonial', true );
                    $client_name = get_post_meta( get_the_ID(), '_clientname', true );
                    $mylink = $wpdb->get_row("SELECT attached_image_id FROM ".$wpdb->prefix."portfolio_images WHERE portfolio_id = ". get_the_ID() );
                    $attached_image_id = $mylink->attached_image_id;
                    $small_img_arr = wp_get_attachment_image_src( $attached_image_id, 'testimonial_image' );
					
                    $category_name = get_the_term_list( $pf_portfolio_id, 'portfolio-category', '', ', ', '' ); ?>
                    <div class="picture-item">
                        <a href="<?php echo add_query_arg( 'portfolio_id', $pf_portfolio_id, get_permalink($curr_page_id) ); ?>" ><?php
                            if(isset($small_img_arr) && $small_img_arr[0]!="") { ?>
                                <img  src="<?php echo $small_img_arr[0]; ?>" border="0" /><?php
                            } ?>  
                        <h3><?php the_title(); ?></h3></a>
                        <a href="<?php echo add_query_arg( 'portfolio_id', $pf_portfolio_id, get_permalink($curr_page_id) ); ?>" class="mask"></a>
                    </div><?php
                    $i = $i + 1;
                endwhile; ?>
                    </div><?php
            }
        }
    }    
}?>