<?php $first_name='troy'; $last_name='Vitullo'; $email='troy@troyvit.com'; ?>
<html><head><title>test</title></head>
<body>
<?php
print_r($_REQUEST);
?>
<form method=post action="https://secure.velocitypaymentsgateway.com/cart/cart.php">
<input type=hidden name="customer_receipt" value="true" />
<input type=hidden name="action" value="process_cart" />
<input type=hidden name="username" value="demo" />
<input type=hidden name="url_finish" value="http://www.infantaquatics.com/ns/utilities/cart_example.php" />
<input type=hidden name="url_continue" value="http://www.infantaquatics.com/ns/utilities/cart_example.php" />
<input type=hidden name="product_description_1458" value="Product #1" />
<input type=hidden name="product_sku_1458" value="ITEM1" />
<input type=hidden name="product_amount_1458" value="7.95" />
<input type=hidden name="product_quantity_1458" size="3" value="1" />
<input type=hidden name="product_description_1522" value="Product #1" />
<input type=hidden name="product_sku_1522" value="ITEM2" />
<input type=hidden name="product_amount_1522" value="7.95" />
<input type=hidden name="product_quantity_1522" size="3" value="1" />
<input type="text" name="first_name" value="<?php echo $first_name; ?>" />
<input type="text" name="last_name" value="<?php echo $last_name; ?>" />
<input type="text" name="email" value="<?php echo $email; ?>" />
<input type=submit value="Add To Cart" />
</form>
</body>
</html>
