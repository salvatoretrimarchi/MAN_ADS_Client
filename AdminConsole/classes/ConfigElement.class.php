<?php
/**
 * Copyright (C) 2009-2012 Ulteo SAS
 * http://www.ulteo.com
 * Author Laurent CLOUET <laurent@ulteo.com> 2009
 * Author Julien LANGLOIS <julien@ulteo.com> 2012
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2
 * of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 **/

abstract class ConfigElement{
	public $id;
	public $label;
	public $description;
	public $description_detailed;
	public $content;
	public $content_default;
	public $content_available;
	public $formSeparator='';
	public $path=array();
	protected $prefix = null;

	abstract public function toHTML($readonly=false);

	public function __construct($id_, $label_, $description_, $description_detailed_, $content_, $content_default_) {
		$this->id = $id_;
		$this->label = $label_;
		$this->description = $description_;
		$this->description_detailed = $description_detailed_;
		$this->content = $content_;
		$this->content_default = $content_default_;
// 		$this->content_available = $content_available_;
// 		$this->type = $type_;
	}
	public function __toString(){
		$str =  '<strong>'.get_class($this)."</strong>( '".$this->id."','".$this->label."','";
		$str .=  '<strong>';
		if (is_array($this->content)) {
			$str .= 'array(';
			foreach($this->content as $k => $v)
				$str .= '\''.$k.'\' => \''.$v.'\' , ';
			$str .= ') ';
		}
		else
			$str .= $this->content;
		$str .=  '</strong>';
		$str .=  "','";
		if (is_array($this->content_available)) {
			$str .= 'array(';
			foreach($this->content_available as $k => $v)
				$str .= '\''.$k.'\' => \''.$v.'\' , ';
			$str .= ') ';
		}
		else
			$str .= $this->content_available;
		$str .=  "','".$this->description."','".$this->description_detailed."'";
		$str .= ')';
		return $str;
	}
	public function reset() {
		if (is_string($this->content)) {
			$this->content = '';
		}
		else if (is_array($this->content)){
			$this->content = array();
		}
		else{
			// TODO
			$this->content = '';
		}
	}

	public function setPath($path_) {
		$this->path = $path_;
	}

	public function setFormSeparator($sep_) {
		$this->formSeparator = $sep_;
	}

	public function setContentAvailable($content_available_) {
		$this->content_available = $content_available_;
	}

	protected function htmlID() {
		$ret = implode($this->formSeparator, $this->path);
		if (! is_null($this->prefix)) {
			$ret = implode($this->formSeparator, array($this->prefix, $ret));
		}
		
		return $ret;
	}
	
	protected function get_input_name() {
		return implode($this->formSeparator, $this->path);
	}
	
	public function setPrefixID($prefix_) {
		$this->prefix = $prefix_;
	}
}
