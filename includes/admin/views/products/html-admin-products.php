<?php
/**
 * Admin View: Products
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$products = new Lengow_Admin_Products();
?>
<h2>Products</h2>
<h2>Products</h2>
<p class="search-box">
    <label class="screen-reader-text" for="search_id-search-input">
        search:</label>
    <input id="search_id-search-input" type="text" name="s" value="" />
    <input id="search-submit" class="button" type="submit" name="" value="search" />
<form method="post">
    <input type="hidden" name="page" value="my_list_test" />
    <?php $products->search_box('search', 'search_id'); ?>
</form>
</p>

<?php Lengow_Admin_Products::my_render_list_page(); ?>
