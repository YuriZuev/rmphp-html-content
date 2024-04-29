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
	 * @param array $aliases
	 * @return TemplateInterface
	 */
	public function setSubtemplatePathAlias(array $aliases = []) : TemplateInterface {
		foreach($aliases as $alias => $subtemplatePath){
			ContentData::$subtemplatePathAlias[$alias] = ContentData::$basePath.rtrim($subtemplatePath, '/');
		}
		return $this;
	}

	/**
	 * @return array
	 */
	public function getSubtemplatePathAlias() : array {
		return ContentData::$subtemplatePathAlias;
	}

	/**
	 * @param string $subtemplate
	 * @return string
	 */
	public function getFullSubtemplatePath(string $subtemplate) : string {
		if (preg_match("'^[%@#](\w+?)(/.+)'", $subtemplate, $match)){
			if(empty(ContentData::$subtemplatePathAlias)) throw new AppError("SubtemplatePathAliases is not defined");
			if(empty(ContentData::$subtemplatePathAlias[$match[1]])) throw new AppError("Aliase '$match[1]' is not defined");
			if(!file_exists(ContentData::$subtemplatePathAlias[$match[1]].$match[2])) throw new AppError("Subtemplate ".ContentData::$subtemplatePathAlias[$match[1]].$match[2]. " is not found");
			return ContentData::$subtemplatePathAlias[$match[1]].$match[2];
		} else {
			if (empty(ContentData::$subtemplatePath)) throw new AppError("SubtemplatePath is not defined");
			if (!file_exists(ContentData::$subtemplatePath.$subtemplate)) throw new AppError("Subtemplate ".ContentData::$subtemplatePath.$subtemplate. " is not found");
			return ContentData::$subtemplatePath.$subtemplate;
		}
	}


	/**
	 * @inheritDoc
	 */
	public function addValue(string $point, string $string) : void {
		if (empty($point)) throw new AppError("Empty point");
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
		if (empty($point)) throw new AppError("Empty point");
		if (empty($subtemplate)) throw new AppError("Subtemplate is empty");
		$inc = $this->getFullSubtemplatePath($subtemplate);
		foreach ($resource as $resKey => $resVal){
			$this->{$resKey} = $resVal;
		}
		ob_start(); include $inc; ContentData::$content[$point][] = ob_get_contents(); ob_end_clean();
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
		$inc = $this->getFullSubtemplatePath($incFile);
		foreach ($resource as $resKey => $resVal){
			$this->{$resKey} = $resVal;
		}
		ob_start(); include $inc; $out = ob_get_contents(); ob_end_clean();
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