<?php

namespace TAO\Text;

use TAO\Text\Exception\UndefinedProcessor;

class ProcessorFactory
{
	/**
	 * Возвращает объект обработчика текста по его мнемокоду
	 *
	 * @param string $processor_code
	 * @return ProcessorInterface
	 * @throws UndefinedProcessor
	 */
	public static function processor($processor_code)
	{
		$processors = config('tao.text.processors', []);
		if (isset($processors[$processor_code])) {
			$processor = app()->make($processors[$processor_code]);
		}
		if (!$processor) {
			throw new UndefinedProcessor($processor_code);
		}
		return $processor;
	}
}