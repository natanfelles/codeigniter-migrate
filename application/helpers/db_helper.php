<?php
/**
 * @author   Natan Felles <natanfelles@gmail.com>
 */
defined('BASEPATH') OR exit('No direct script access allowed');
if ( ! function_exists('add_foreign_key'))
{
	/**
	 * @param string $table
	 * @param string $foreign_key
	 * @param string $references
	 * @param string $on_delete
	 * @param string $on_update
	 *
	 * @return string SQL command
	 */
	function add_foreign_key($table, $foreign_key, $references, $on_delete = 'RESTRICT', $on_update = 'RESTRICT')
	{
		$constraint = "{$table}_{$foreign_key}_fk";
		$sql = "ALTER TABLE {$table} ADD CONSTRAINT {$constraint} FOREIGN KEY ({$foreign_key}) REFERENCES {$references} ON DELETE {$on_delete} ON UPDATE {$on_update}";
		return $sql;
	}
}
if ( ! function_exists('drop_foreign_key'))
{
	/**
	 * @param string $table
	 * @param string $foreign_key
	 *
	 * @return string SQL command
	 */
	function drop_foreign_key($table, $foreign_key)
	{
		$constraint = "{$table}_{$foreign_key}_fk";
		$sql = "ALTER TABLE {$table} DROP FOREIGN KEY {$constraint}";
		return $sql;
	}
}
