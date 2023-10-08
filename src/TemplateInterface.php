<?php
/**
 * Created by PhpStorm.
 * User: Zuev Yuri
 * Date: 08.10.2023
 * Time: 18:52
 */

namespace Rmphp\Content;
use \Rmphp\Foundation\TemplateInterface as BaseTemplateInterface;

interface TemplateInterface extends BaseTemplateInterface{
	public function setTemplate(string $template, array $resource = []): TemplateInterface;
	public function setSubtemplePath(string $subtemplatePath) : TemplateInterface;
	public function getSubtemplePath(): string;
	public function setValue(string $point, string $string) : void;
	public function addValue(string $point, string $string) : void;
	public function setSubtemple(string $point, string $subTempl, array $resource = []) : void;
	public function addSubtemple(string $point, string $subTempl, array $resource = []) : void;
	public function inc(string $incFile) : string;
	public function getPoint(string $point) : string;
}