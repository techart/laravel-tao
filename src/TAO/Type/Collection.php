<?php
namespace TAO\Type;

class Collection
{
	/**
	 * @param $array
	 * @return bool
	 */
	public static function isIndexed($array)
	{
		return array_keys($array) === range(0, count($array) - 1) || empty($array);
	}
}