<?php

namespace OpenEstate\PhpExport;

/**
 * Extended configuration for integration into the website.
 */
class WrapperConfig extends MyConfig {
	public function __construct( $basePath, $baseUrl = '.' ) {
		parent::__construct( $basePath, $baseUrl );

		// set configured charset
		$charset = \trim( \get_option( 'openestate_wrapper_charset' ) );
		if ( Utils::isNotBlankString( $charset ) ) {
			$this->charset = $charset;
		}

		// enable / disable favorites
		$this->favorites = \trim( \get_option( 'openestate_wrapper_favorites' ) ) === '1';

		// enable / disable language selection
		$this->allowLanguageSelection = \trim( \get_option( 'openestate_wrapper_languages' ) ) === '1';
	}

	public function getActionUrl( $parameters = null ) {
		if ( $parameters == null ) {
			$parameters = array();
		}

		$parameters['wrap'] = 'action';
		foreach ( $_REQUEST as $key => $value ) {
			if ( ! isset( $parameters[ $key ] ) && $key != 'update' ) {
				$parameters[ $key ] = $value;
			}
		}

		$baseUrl = \explode( '?', $_SERVER['REQUEST_URI'] );

		return $baseUrl[0] . Utils::getUrlParameters( $parameters );
	}

	public function getExposeUrl( $parameters = null ) {
		if ( $parameters == null ) {
			$parameters = array();
		}

		$parameters['wrap'] = 'expose';
		foreach ( $_REQUEST as $key => $value ) {
			if ( ! isset( $parameters[ $key ] ) && $key != 'update' ) {
				$parameters[ $key ] = $value;
			}
		}

		$baseUrl = \explode( '?', $_SERVER['REQUEST_URI'] );

		return $baseUrl[0] . Utils::getUrlParameters( $parameters );
	}

	public function getFavoriteUrl( $parameters = null ) {
		if ( $parameters == null ) {
			$parameters = array();
		}

		$parameters['wrap'] = 'fav';
		foreach ( $_REQUEST as $key => $value ) {
			if ( ! isset( $parameters[ $key ] ) && $key != 'update' ) {
				$parameters[ $key ] = $value;
			}
		}

		$baseUrl = \explode( '?', $_SERVER['REQUEST_URI'] );

		return $baseUrl[0] . Utils::getUrlParameters( $parameters );
	}

	public function getListingUrl( $parameters = null ) {
		if ( $parameters == null ) {
			$parameters = array();
		}

		$parameters['wrap'] = 'index';
		foreach ( $_REQUEST as $key => $value ) {
			if ( ! isset( $parameters[ $key ] ) && $key != 'update' ) {
				$parameters[ $key ] = $value;
			}
		}

		$baseUrl = \explode( '?', $_SERVER['REQUEST_URI'] );

		return $baseUrl[0] . Utils::getUrlParameters( $parameters );
	}

	public function setupEnvironment( Environment $env ) {
		parent::setupEnvironment( $env );
		Environment::$parameterPrefix = 'wrap';
	}

	public function setupExposeHtml( View\ExposeHtml $view ) {
		parent::setupExposeHtml( $view );
		$view->setBodyOnly( true );
	}

	public function setupFavoriteHtml( View\FavoriteHtml $view ) {
		parent::setupFavoriteHtml( $view );
		$view->setBodyOnly( true );

		// disable ordering
		if (\trim( \get_option( 'openestate_wrapper_ordering' ) ) !== '1')
			$view->orders = array();
	}

	public function setupListingHtml( View\ListingHtml $view ) {
		parent::setupListingHtml( $view );
		$view->setBodyOnly( true );

		// disable ordering
		if (\trim( \get_option( 'openestate_wrapper_ordering' ) ) !== '1')
			$view->orders = array();

		// disable filtering
		if (\trim( \get_option( 'openestate_wrapper_filtering' ) ) !== '1')
			$view->filters = array();
	}

	public function setupTheme( Theme\AbstractTheme $theme ) {
		parent::setupTheme( $theme );

		// register disabled components
		$disabledComponents = \explode( ',', \trim( \get_option( 'openestate_wrapper_disabledComponents' ) ) );
		foreach ( $disabledComponents as $componentId ) {
			$theme->setComponentEnabled( $componentId, false );
		}
	}
}
