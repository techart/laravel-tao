<?php

namespace TAO\Text\Exception;

use Throwable;

class UndefinedProcessor extends \TAO\Exception
{
	public function __construct($processor_code = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct('Undefined processor for code "' . $processor_code . '"', $code, $previous);
	}

}