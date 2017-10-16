<?php

namespace TAO\Text\Processor\Parser;

use TAO\Text\ProcessorInterface;

class Markdown implements ProcessorInterface
{
	protected $parser;

	public function process($text)
	{
		return $this->parser()->parse($text);
	}

	protected function parser()
	{
		if (!$this->parser) {
			$this->parser = new \Parsedown();
		}
        return $this->parser;
	}
}