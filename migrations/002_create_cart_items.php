<?php

namespace Fuel\Migrations;

class Create_cart_items
{
	public function up()
	{
		\DBUtil::create_table('cart_items', array(
			'id'         => array('constraint' => 11, 'type' => 'int', 'auto_increment' => true, 'unsigned' => true),
			'cart_id'    => array('constraint' => 11, 'type' => 'int', 'unsigned' => true),
			'identifier' => array('constraint' => 32, 'type' => 'varchar'),
			'item_id'    => array('constraint' => 11, 'type' => 'int', 'unsigned' => true),
			'name'       => array('constraint' => 255, 'type' => 'varchar'),
			'price'      => array('constraint' => '15, 4', 'type' => 'decimal'),
			'quantity'   => array('constraint' => 11, 'type' => 'int'),
			'option'     => array('type' => 'text', 'null' => true),
		), array('id'));
	}

	public function down()
	{
		\DBUtil::drop_table('cart_items');
	}
}
