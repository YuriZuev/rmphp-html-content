<?php

namespace Rmphp\Content;

use Rmphp\Foundation\Exceptions\AppError;
use Rmphp\Foundation\Exceptions\AppException;
use Rmphp\Foundation\TemplateInterface;

class Content implements TemplateInterface {

	private array $content = [];
	private ContentData $data;
	private ContentData $dataGlobal;
	private string $basePath = "";
	private string $template;
	private string $subtemplatePath;

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
		if(empty($this->data)) $this->data = new ContentData();
		foreach ($resource as $resKey => $resVal){
			$this->data->{$resKey} = $resVal;
		}
		$this->basePath = dirname(__DIR__, 4);
		$this->template = $this->basePath.'/'.$template;
		return $this;
	}

	/**
	 * @param string $subtemplatePath
	 * @return TemplateInterface
	 */
	public function setSubtemplePath(string $subtemplatePath) : TemplateInterface {
		$this->subtemplatePath = $this->basePath.'/'.$subtemplatePath;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSubtemplePath(): string {
		return $this->subtemplatePath;
	}


	/**
	 * @param string $point
	 * @param string $string
	 * @throws AppException
	 */
	public function addValue(string $point, string $string) : void {
		if (empty($point)) throw new AppException("Empty point");
		if (empty($this->subtemplatePath))throw new AppException("SubtemplatePath is not defined");
		$this->content[$point][] = $string;
	}

	/**
	 * @param string $point
	 * @param string $string
	 * @throws AppException
	 */
	public function setValue(string $point, string $string) : void {
		unset($this->content[$point]);
		$this->addValue($point, $string);
	}

	/**
	 * @param string $point
	 * @param string $subTempl
	 * @param array $resource
	 * @throws AppException
	 */
	public function addSubtemple(string $point, string $subTempl, array $resource = []) : void {
		if (empty($this->subtemplatePath))throw new AppException("SubtemplatePath is not defined");
		if (empty($point)) throw new AppException("Empty point");
		if (empty($subTempl) || !file_exists($this->subtemplatePath."/".$subTempl)) throw new AppException($this->subtemplatePath."/".$subTempl. " is not found");
		if(empty($this->data)) $this->data = new ContentData();
		foreach ($resource as $resKey => $resVal){
			$this->data->{$resKey} = $resVal;
		}
		ob_start(); include $this->subtemplatePath."/".$subTempl; $this->content[$point][] = ob_get_contents(); ob_end_clean();
	}

	/**
	 * @param string $point
	 * @param string $subTempl
	 * @param array $resource
	 * @throws AppException
	 */
	public function setSubtemple(string $point, string $subTempl, array $resource = []) : void {
		unset($this->content[$point]);
		$this->addSubtemple($point, $subTempl, $resource);
	}

	/**
	 * @param array $globals
	 */
	public function setGlobals(array $globals = []) : void {
		$this->dataGlobal = new ContentData();
		foreach ($globals as $resKey => $resVal){
			$this->dataGlobal->{$resKey} = $resVal;
		}
	}

	/**
	 * @param string $incFile
	 * @param array $resource
	 * @return string
	 * @throws AppException
	 */
	public function inc(string $incFile, array $resource = []) : string {
		if(empty($this->data)) $this->data = new ContentData();
		foreach ($resource as $resKey => $resVal){
			$this->data->{$resKey} = $resVal;
		}
		if(empty($incFile) || !file_exists($this->subtemplatePath."/".$incFile)) throw new AppException("Empty inc file");
		ob_start(); include $this->subtemplatePath."/".$incFile; $out = ob_get_contents(); ob_end_clean();
		return $out;
	}

	/**
	 * @param string $point
	 * @return string
	 */
	public function getPoint(string $point) : string {
		if (empty($point) || empty($this->content[$point])) return "";
		return implode("", $this->content[$point]);
	}

	/**
	 * @return string
	 * @throws AppException
	 */
	public function getResponse(): string {
		if (empty($this->template) || !file_exists($this->template)) throw new AppError("Invalid template file");
		ob_start(); include $this->template; $out = ob_get_contents(); ob_end_clean();
		return $out;
	}
}