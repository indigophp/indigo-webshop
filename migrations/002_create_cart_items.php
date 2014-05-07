<?php

namespace Fuel\Migrations;

class Create_cart_items
{
	public function up()
	{
		\DBUtil::create_table('cart_items', array(
			'id' => array('constraint' => 11, 'type' => 'int', 'auto_increment' => true, 'unsigned' => true),
			'cart_id' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true),
			'product_id' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true),
			'quantity' => array('constraint' => 11, 'type' => 'int'),
		), array('id'));
	}

	public function down()
	{
		\DBUtil::drop_table('cart_items');
	}
}
