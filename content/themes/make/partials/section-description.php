<?php
/**
 * @package Make
 */

if ( is_category() || is_tag() || is_tax() ) :
	if ( '' !== term_description() ) : ?>
	<div class="section-description">
		<?php echo wpautop( term_description() ); ?>
	</div>
	<?php endif;

elseif ( is_author() ) :
	if ( '' !== get_the_author_meta( 'description' ) ) : ?>
	<div class="section-description">
		<?php echo wpautop( get_the_author_meta( 'description' ) ); ?>
	</div>
	<?php endif;

endif;