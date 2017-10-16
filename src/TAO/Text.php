<?php

namespace TAO;

use TAO\Text\ProcessorFactory;
use TAO\Text\ProcessorInterface;

class Text
{
	/**
	 * Обрабатывает текст указанными обработчиками
	 *
	 * @param $text
	 * @param array|string $processors
	 */
	public static function process($text, $processors)
	{
		if (!is_array($processors)) {
			$processors = [$processors];
		}

		foreach ($processors as $processor) {
			if (!($processor instanceof ProcessorInterface)) {
				$processor = ProcessorFactory::processor($processor);
			}
			$text = $processor->process($text);
		}
		return $text;
	}
}