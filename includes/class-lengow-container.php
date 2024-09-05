<?php

/**
 * Lengow Container
 *
 * Copyright 2024 Lengow SAS
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
 * @copyright   2024 Lengow SAS
 */

declare(strict_types=1);

class Lengow_Container
{
	/**
	 * @var Lengow_Container
	 */
	private static Lengow_Container $container;

	/**
	 * @var array
	 */
	protected array $dependencies = array();

	/**
	 * @var array
	 */
	protected array $instances = array();

	/**
	 * @return Lengow_Container
	 */
	public static function instance(): Lengow_Container {
		return self::$container ?? self::$container = new Lengow_Container();
	}

	private function __construct() {
	}

	/**
	 * @param string $name
	 * @param mixed $concrete
	 * @param bool $unset_instance
	 *
	 * @return $this
	 */
	public function bind( string $name, $concrete, bool $unset_instance = false ): self {
		$this->dependencies[ $name ] = $concrete;
		if ( $unset_instance && isset( $this->instances[ $name ] ) ) {
			unset( $this->instances[ $name ] );
		}

		return $this;
	}

	/**
	 * Create a new instance for the given dependency.
	 *
	 * @param string $name
	 * @return mixed
	 * @throws RuntimeException if $name does not exist in the dependencies
	 */
	public function make( string $name ) {
		if ( isset( $this->dependencies[ $name ] ) ) {
			return $this->instances[ $name ] = $this->resolve( $this->dependencies[ $name ] );
		}

		// case if this is a concrete class name
		try {
			return $this->instances[ $name ] = $this->resolve( $name );
		} catch ( RuntimeException $e ) {
			throw new RuntimeException( "Dependency {$name} not found.", 0, $e );
		}
	}

	/**
	 * Get or create an instance for the given dependency.
	 * If the dependency name starts with a $ (dollar) it will be returned as is.
	 *
	 * @param string $name
	 * @return mixed
	 * @throws RuntimeException if $name does not exist in the dependencies
	 */
	public function get( string $name ) {
		if ( '$' === substr( $name, 0, 1 ) ) {
			if ( ! isset( $this->dependencies[ $name ] ) ) {
				throw new RuntimeException( "Dependency {$name} not found." );
			}

			return $this->dependencies[ $name ];
		}

		return $this->instances[ $name ] ?? $this->instances[ $name ] = $this->make( $name );
	}

	/**
	 * Resolve the given dependency with constructor arguments.
	 *
	 * @param string|callable $concrete class or factory
	 * @return mixed
	 */
	protected function resolve( $concrete ) {
		if ( is_callable( $concrete ) ) {
			return $concrete( $this );
		} else {
			try {
				$reflector = new ReflectionClass( $concrete );
			} catch ( ReflectionException $e ) {
				throw new RuntimeException( $e->getMessage(), $e->getCode(), $e );
			}

			$constructor = $reflector->getConstructor();

			if ( ! $constructor ) {
				return new $concrete;
			}

			$parameters = $constructor->getParameters();
			$dependencies = [];
			foreach ( $parameters as $parameter ) {
				$type = $parameter->getType();
				if ( ! $type || $type->isBuiltin() ) {
					try {
						$dependencies[] = $this->get( '$' . $parameter->getName() );
					} catch ( RuntimeException $e ) {
						if ( $type->allowsNull() ) {
							// should we allow this ?
							$dependencies[] = null;
							continue;
						}

						throw new RuntimeException(
							"Cannot resolve dependency {$parameter->getName()} for class {$reflector->getName()}",
							$e->getCode(),
							$e
						);
					}
				} else {
					$dependencies[] = $this->get( $type->getName() );
				}
			}

			return new $concrete( ...$dependencies );
		}
	}
}
