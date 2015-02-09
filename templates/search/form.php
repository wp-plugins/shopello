<div class='shopello_api shopello_search <?php echo $class; ?>'>
	<?php if($label) : ?>
		<label for="shopello_search_query"><?php echo $label; ?></label>
	<?php endif; ?>
	<form method='get' action='<?php echo $target; ?>' role="search" class="s_qs_search_form">

		<input type='text' name='keyword' placeholder="<?php echo $placeholder;?>" id='shopello_search_field' value="<?php echo htmlspecialchars($_GET['s_qs']); ?>" />
		<input type='submit' name='s_sbtn' id='shopello_search_submit' value="<?php echo $search_label;?>"/>
	</form>
</div>
