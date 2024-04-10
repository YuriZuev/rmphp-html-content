<?php

namespace Rmphp\Content;

use Rmphp\Foundation\Exceptions\AppError;
use Rmphp\Foundation\TemplateInterface;

class Content implements TemplateInterface {

	/**
	 * Content constructor.
	 * @param string $template
	 */
	public function __construct(string $template = "") {
		if(!empty($template)) $this->setTemplate($template);
	}

	/** @inheritDoc */
	public function setTemplate(string $template, array $resource = []) : TemplateInterface {
		foreach ($resource as $resKey => $resVal){
			$this->{$resKey} = $resVal;
		}
		ContentData::$basePath = dirname(__DIR__, 4);
		ContentData::$template = ContentData::$basePath.$template;
		return $this;
	}

	/** @inheritDoc */
	public function setSubtemplatePath(string $subtemplatePath = "") : TemplateInterface {
		ContentData::$subtemplatePath = ContentData::$basePath.rtrim($subtemplatePath, '/');
		return $this;
	}

	/** @inheritDoc */
	public function getSubtemplatePath(): string {
		return ContentData::$subtemplatePath;
	}

	/**
	 * @inheritDoc
	 */
	public function addValue(string $point, string $string) : void {
		if (empty($point)) throw new AppError("Empty point");
		if (empty(ContentData::$subtemplatePath))throw new AppError("SubtemplatePath is not defined");
		ContentData::$content[$point][] = $string;
	}

	/**
	 * @inheritDoc
	 */
	public function setValue(string $point, string $string) : void {
		unset(ContentData::$content[$point]);
		$this->addValue($point, $string);
	}

	/**
	 * @inheritDoc
	 */
	public function addSubtemplate(string $point, string $subtemplate, array $resource = []) : void {
		if (empty(ContentData::$subtemplatePath))throw new AppError("SubtemplatePath is not defined");
		if (empty($point)) throw new AppError("Empty point");
		if (empty($subtemplate) || !file_exists(ContentData::$subtemplatePath.$subtemplate)) throw new AppError(ContentData::$subtemplatePath.$subtemplate. " is not found");
		foreach ($resource as $resKey => $resVal){
			$this->{$resKey} = $resVal;
		}
		ob_start(); include ContentData::$subtemplatePath.$subtemplate; ContentData::$content[$point][] = ob_get_contents(); ob_end_clean();
	}

	/**
	 * @inheritDoc
	 */
	public function setSubtemplate(string $point, string $subtemplate, array $resource = []) : void {
		unset(ContentData::$content[$point]);
		$this->addSubtemplate($point, $subtemplate, $resource);
	}

	/**
	 * @inheritDoc
	 */
	public function inc(string $incFile, array $resource = []) : string {
		foreach ($resource as $resKey => $resVal){
			$this->{$resKey} = $resVal;
		}
		if(empty($incFile) || !file_exists(ContentData::$subtemplatePath.$incFile)) throw new AppError("Empty inc file ".$incFile);
		ob_start(); include ContentData::$subtemplatePath.$incFile; $out = ob_get_contents(); ob_end_clean();
		return $out;
	}

	/** @inheritDoc */
	public function getPoint(string $point) : string {
		if (empty($point) || empty(ContentData::$content[$point])) return "";
		return implode("", ContentData::$content[$point]);
	}

	/** @inheritDoc */
	public function getResponse(): string {
		if (empty(ContentData::$template) || !file_exists(ContentData::$template)) throw new AppError("Invalid template file");
		ob_start(); include ContentData::$template; $out = ob_get_contents(); ob_end_clean();
		return $out;
	}
}