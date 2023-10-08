<?php

namespace Rmphp\Content;

use Rmphp\Foundation\Exceptions\AppError;
use Rmphp\Foundation\Exceptions\AppException;
use Rmphp\Foundation\TemplateInterface;

class Content implements TemplateInterface {

	/**
	 * Content constructor.
	 * @param string $template
	 */
	public function __construct(string $template = "") {
		if(!empty($template)) $this->setTemplate($template);
	}

	/**
	 * @param string $template
	 * @param array $resource
	 * @return TemplateInterface
	 */
	public function setTemplate(string $template, array $resource = []) : TemplateInterface {
		foreach ($resource as $resKey => $resVal){
			$this->{$resKey} = $resVal;
		}
		ContentData::$basePath = dirname(__DIR__, 4);
		ContentData::$template = ContentData::$basePath.'/'.$template;
		return $this;
	}

	/**
	 * @param string $subtemplatePath
	 * @return TemplateInterface
	 */
	public function setSubtemplePath(string $subtemplatePath) : TemplateInterface {
		ContentData::$subtemplatePath = ContentData::$basePath.'/'.$subtemplatePath;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSubtemplePath(): string {
		return ContentData::$subtemplatePath;
	}

	/**
	 * @param string $point
	 * @param string $string
	 * @throws AppException
	 */
	public function addValue(string $point, string $string) : void {
		if (empty($point)) throw new AppException("Empty point");
		if (empty(ContentData::$subtemplatePath))throw new AppException("SubtemplatePath is not defined");
		ContentData::$content[$point][] = $string;
	}

	/**
	 * @param string $point
	 * @param string $string
	 * @throws AppException
	 */
	public function setValue(string $point, string $string) : void {
		unset(ContentData::$content[$point]);
		$this->addValue($point, $string);
	}

	/**
	 * @param string $point
	 * @param string $subTempl
	 * @param array $resource
	 * @throws AppException
	 */
	public function addSubtemple(string $point, string $subTempl, array $resource = []) : void {
		if (empty(ContentData::$subtemplatePath))throw new AppException("SubtemplatePath is not defined");
		if (empty($point)) throw new AppException("Empty point");
		if (empty($subTempl) || !file_exists(ContentData::$subtemplatePath."/".$subTempl)) throw new AppException(ContentData::$subtemplatePath."/".$subTempl. " is not found");
		foreach ($resource as $resKey => $resVal){
			$this->{$resKey} = $resVal;
		}
		ob_start(); include ContentData::$subtemplatePath."/".$subTempl; ContentData::$content[$point][] = ob_get_contents(); ob_end_clean();
	}

	/**
	 * @param string $point
	 * @param string $subTempl
	 * @param array $resource
	 * @throws AppException
	 */
	public function setSubtemple(string $point, string $subTempl, array $resource = []) : void {
		unset(ContentData::$content[$point]);
		$this->addSubtemple($point, $subTempl, $resource);
	}

	/**
	 * @param string $incFile
	 * @param array $resource
	 * @return string
	 * @throws AppException
	 */
	public function inc(string $incFile, array $resource = []) : string {
		foreach ($resource as $resKey => $resVal){
			$this->{$resKey} = $resVal;
		}
		if(empty($incFile) || !file_exists(ContentData::$subtemplatePath."/".$incFile)) throw new AppException("Empty inc file");
		ob_start(); include ContentData::$subtemplatePath."/".$incFile; $out = ob_get_contents(); ob_end_clean();
		return $out;
	}

	/**
	 * @param string $point
	 * @return string
	 */
	public function getPoint(string $point) : string {
		if (empty($point) || empty(ContentData::$content[$point])) return "";
		return implode("", ContentData::$content[$point]);
	}

	/**
	 * @return string
	 * @throws AppException
	 */
	public function getResponse(): string {
		if (empty(ContentData::$template) || !file_exists(ContentData::$template)) throw new AppError("Invalid template file");
		ob_start(); include ContentData::$template; $out = ob_get_contents(); ob_end_clean();
		return $out;
	}
}