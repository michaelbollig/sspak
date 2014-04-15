<?php

/**
 * Argument parser for SSPak
 */
class Args {
	protected $namedArgs = array();
	protected $unnamedArgs = array();
	protected $action = null;

	function __construct($args) {
		array_shift($args);

		foreach($args as $arg) {
			if(preg_match('/^--([^=]+)=(.*)$/', $arg, $matches)) {
				$this->namedArgs[$matches[1]] = $matches[2];
			} else if(preg_match('/^--([^=]+)$/', $arg, $matches)) {
				$this->namedArgs[$matches[1]] = true;
			} else {
				$this->unnamedArgs[] = $arg;
			}
		}

		$this->action = array_shift($this->unnamedArgs);
	}

	function unshiftUnnamed($arg) {
		array_unshift($this->unnamedArgs, $arg);
	}

	function getNamedArgs() {
		return $this->namedArgs;
	}

	function getUnnamedArgs() {
		return $this->unnamedArgs;
	}

	function getAction() {
		return $this->action;
	}

	/**
	 * Return the unnamed arg of the given index (0 = first)
	 */
	function unnamed($idx) {
		return isset($this->unnamedArgs[$idx]) ? $this->unnamedArgs[$idx] : null;
	}

	/**
	 * Return the sudo argument, preferring a more specific one with the given optional prefix
	 */
	function sudo($optionalPrefix) {
		if(!empty($this->namedArgs[$optionalPrefix . '-sudo'])) return $this->namedArgs[$optionalPrefix . '-sudo'];
		else if(!empty($this->namedArgs['sudo'])) return $this->namedArgs['sudo'];
		else return null;
	}

	/**
	 * Return the pak-parks arguments, as a map of part => boolean
	 */
	function pakParts() {
		// Look up which parts of the sspak are going to be saved
		$pakParks = array();
		foreach(array('assets','db','git-remote','svn') as $part) {
			$pakParts[$part] = !empty($this->namedArgs[$part]);
		}

		// Default to db and assets
		if(!array_filter($pakParts)) $pakParts = array('db' => true, 'assets' => true, 'git-remote' => true, 'svn' => true);
		return $pakParts;
	}

	function requireUnnamed($items) {
		if(sizeof($this->unnamedArgs) < sizeof($items)) {
			echo "Usage: {$_SERVER['argv'][0]} " . $this->action . " (";
			echo implode(") (", $items);
			echo ")\n";
			throw new Exception('Arguments missing.');
		}
	}
}
