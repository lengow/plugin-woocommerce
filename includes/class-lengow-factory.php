<?php

/**
 * Lengow Factory
 *
 * Copyright 2017 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl-3.0
 *
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  webservice
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2017 Lengow SAS
 */

declare(strict_types=1);

class Lengow_Factory {


	/**
	 * @var Lengow_Factory
	 */
	private static Lengow_Factory $factory;

	/**
	 * @var array
	 */
	protected array $dependencies = array();

	/**
	 * @var array
	 */
	protected array $instances = array();

	/**
	 * @return Lengow_Factory
	 */
	public static function instance(): Lengow_Factory {
		return self::$factory ?? self::$factory = new Lengow_Factory();
	}

	private function __construct() {
	}

	/**
	 * @param string $name
	 * @param mixed  $concrete
	 * @return $this
	 */
	public function bind( string $name, $concrete ): self {
		$this->dependencies[ $name ] = $concrete;
		return $this;
	}

	/**
	 * Make a new instance for the given dependency.
	 *
	 * @param string $name
	 * @return mixed
	 * @throws Exception if $name does not exist in the dependencies
	 */
	public function make( string $name ) {
		if ( isset( $this->dependencies[ $name ] ) ) {
			return $this->resolve( $this->dependencies[ $name ] );
		}

		throw new Exception( "Dependency {$name} not found." );
	}

	/**
	 * Get or create an instance for
	 *
	 * @param string $name
	 * @return mixed
	 * @throws Exception if $name does not exist in the dependencies
	 */
	public function get( string $name ) {
		return $this->instances[ $name ] ?? $this->instances[ $name ] = $this->make( $name );
	}

	/**
	 * @param mixed $concrete
	 * @return mixed
	 */
	private function resolve( $concrete ) {
		if ( is_callable( $concrete ) ) {
			return $concrete( $this );
		} else {
			return new $concrete();
		}
	}
}
