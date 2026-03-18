<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

require( __DIR__ . '/htmlparser.php' );
require( __DIR__ . '/content_img.php' );
require( __DIR__ . '/content_js.php' );
require( __DIR__ . '/content_css.php' );
require( __DIR__ . '/content_frm.php' );

spl_autoload_register(
	function( $class )
	{
		if( strpos( $class, 'seraph_accel\\DomElementEx' ) === 0 || strpos( $class, 'seraph_accel\\ContSkeletonHash_MatchAll' ) === 0 || strpos( $class, 'seraph_accel\\LazyCont_XpathExtFunc_FollowingSiblingUpToParent_Iterator' ) === 0 )
			@include_once( __DIR__ . '/content_ex.php' );
		if( strpos( $class, 'seraph_accel\\CssToXPathNormalizedAttributeMatchingExtension' ) === 0 || strpos( $class, 'seraph_accel\\CssToXPathHtmlExtension' ) === 0 || strpos( $class, 'seraph_accel\\CssSelFs' ) === 0 )
			@include_once( __DIR__ . '/content_css_ex.php' );
		if( strpos( $class, 'seraph_accel\\Symfony\\Component\\CssSelector\\' ) === 0 )
			@include_once( __DIR__ . '/Cmn/Ext/PHP/symfony-css-selector/' . str_replace( '\\', '/', substr( $class, 43 ) ) . '.php' );

		if( strpos( $class, 'seraph_accel\\tubalmartin\\CssMin' ) === 0 )
			@include_once( __DIR__ . '/Cmn/Ext/PHP/YUI-CSS-compressor-PHP-port/' . str_replace( '\\', '/', substr( $class, 32 ) ) . '.php' );
		if( strpos( $class, 'seraph_accel\\Sabberworm\\CSS' ) === 0 )
			@include_once( __DIR__ . '/Cmn/Ext/PHP/php-css-parser/' . str_replace( '\\', '/', substr( $class, 28 ) ) . '.php' );

		if( strpos( $class, 'seraph_accel\\JSMin\\' ) === 0 )
			@include_once( __DIR__ . '/Cmn/Ext/PHP/jsmin-php/' . str_replace( '\\', '/', substr( $class, 19 ) ) . '.php' );
		if( strpos( $class, 'seraph_accel\\JShrink\\' ) === 0 )
			@include_once( __DIR__ . '/Cmn/Ext/PHP/JShrink/' . str_replace( '\\', '/', substr( $class, 21 ) ) . '.php' );
	}
);

function ContentDisableIndexingEx( $buffer )
{
	$posHead = Ui::TagBeginGetPos( array( 'head', 'HEAD' ), $buffer );
	if( $posHead )
		$buffer = substr( $buffer, 0, $posHead[ 1 ] ) . Ui::TagOpen( 'meta', array( 'name' => 'robots', 'content' => 'noindex' ) ) . substr( $buffer, $posHead[ 1 ] );
	return( $buffer );
}

function ContentDisableIndexing()
{
	ob_start( 'seraph_accel\\ContentDisableIndexingEx' );
}

function InitContentProcessor( $sett )
{

	add_action( 'init', 'seraph_accel\\_InitContentProcessor', 0 );
}

function _ContentProcessor_TmpCont_SettImg_Adjust( &$settImg )
{

	Gen::SetArrField( $settImg, array( 'inlSml' ), false );
	Gen::SetArrField( $settImg, array( 'deinlLrg' ), false );

}

function _InitContentProcessor()
{
	global $seraph_accel_g_prepPrms;
	global $seraph_accel_g_prepCont;
	global $seraph_accel_g_simpCacheMode;

	$siteId = GetSiteId();
	$sett = Plugin::SettGet();
	$settContPr = Gen::GetArrField( $sett, array( 'contPr' ), array() );
	$tmCur = Gen::GetCurRequestTime();

	$seraph_accel_g_prepCont = false;

	$prepContSpecStage = 'full';
	if( is_string( $seraph_accel_g_simpCacheMode ) )
	{
		if( Gen::StrStartsWith( $seraph_accel_g_simpCacheMode, 'fragments' ) )
			$prepContSpecStage = 'fragments';
		else if( Gen::StrStartsWith( $seraph_accel_g_simpCacheMode, 'data:' ) )
			return;
	}

	$settContPrOverride = GetContentProcessorForce( $sett );

	if( !$settContPrOverride )
	{
		if( $cacheSkipData = GetContCacheEarlySkipData( $pathOrig, $path, $pathIsDir, $args ) )
		{
			if( $cacheSkipData[ 0 ] == 'revalidating-begin' )
				$prepContSpecStage = 'tmp';
			else if( $seraph_accel_g_prepPrms !== null && ($seraph_accel_g_prepPrms[ 'selfTest' ]??null) )
			{
			}
			else if( !Gen::StrStartsWith( ( string )$seraph_accel_g_simpCacheMode, 'fragments' ) )
			{

				BatCache_DontProcessCurRequest();

				return;
			}

			unset( $cacheSkipData );
		}

		if( $seraph_accel_g_prepPrms !== null && ($seraph_accel_g_prepPrms[ 'tmp' ]??null) )
			$prepContSpecStage = 'tmpLong';

		$settCache = Gen::GetArrField( $sett, array( 'cache' ), array() );

		if( ($settCache[ 'enable' ]??null) && !function_exists( 'seraph_accel_siteSettInlineDetach' ) )
			return;

		if( ContProcGetExclStatus( $siteId, $settCache, $path, $pathOrig, $pathIsDir, $_GET, $varsOut, false, !($settCache[ 'enable' ]??null) ) )
			return;

		unset( $varsOut );

		if( Gen::GetArrField( $settContPr, array( 'enable' ), false ) && lfjikztqjqji( $siteId, $tmCur, false ) )
			return;

	}
	else
	{
		add_action( 'wp_loaded',
			function()
			{
				if( ($_REQUEST[ 'd' ]??null) == 'phpinfo' )
				{
					phpinfo();
					exit;
				}

				if( ($_REQUEST[ 'd' ]??null) == 'opcache_reset' )
				{

					echo( '<pre>PluginRe::OpCacheReset(), dir \'' . ( string )PluginRe::GetOpCacheDir() . '\': ' . sprintf( '0x%08X', PluginRe::OpCacheReset() ) . '</pre>' );

					if( function_exists( 'opcache_reset' ) )
						echo( '<pre>opcache_reset(): ' . ( @opcache_reset() ? 'OK' : 'FALSE' ) . '</pre>' );
					else
						echo( '<pre>opcache_reset(): doesnt exist</pre>' );

					exit;
				}

				if( ($_REQUEST[ 'd' ]??null) == 'delcache' )
				{
					$hr = Gen::DelDir( GetCacheDir(), false );
					echo( '<pre>res: ' . sprintf( '0x%08X', $hr ) . '</pre>' );
					exit;
				}

				if( ($_REQUEST[ 'd' ]??null) == 'info' )
				{
					$aTestRes = array();
					$aTestRes[ 'roots' ][ 'siteRootUrl' ] = Wp::GetSiteRootUrl();
					$aTestRes[ 'roots' ][ 'siteRootUrl-base' ] = Wp::GetSiteRootUrl( '', 'base' );
					$aTestRes[ 'roots' ][ 'siteWpRoot1' ] = Wp::GetSiteWpRootUrl( '', null, true );
					$aTestRes[ 'roots' ][ 'siteWpRoot2' ] = Wp::GetSiteWpRootUrl();

					$aTestRes[ 're' ][ 'launcher' ] = PluginRe::_GetPhpLauncher();
					$aTestRes[ 're' ][ 'phpExtensionDir' ] = ini_get( 'extension_dir' );

					$aTestRes[ 'defines' ][ 'SERAPH_ACCEL_CACHE_DIR' ] = Gen::Constant( 'SERAPH_ACCEL_CACHE_DIR' );
					$aTestRes[ 'defines' ][ 'SERAPH_ACCEL_SALT' ] = Gen::Constant( 'SERAPH_ACCEL_SALT' );
					$aTestRes[ 'defines' ][ 'SERAPH_SECRET_KEY' ] = Gen::Constant( 'SERAPH_SECRET_KEY' );
					$aTestRes[ 'defines' ][ 'SERAPH_ACCEL_SITEROOT_DIR' ] = Gen::Constant( 'SERAPH_ACCEL_SITEROOT_DIR' );
					$aTestRes[ 'defines' ][ 'SERAPH_ACCEL_ALT_ROOTS' ] = Gen::Constant( 'SERAPH_ACCEL_ALT_ROOTS' );
					$aTestRes[ 'defines' ][ 'NONCE_SALT' ] = Gen::Constant( 'NONCE_SALT' );
					$aTestRes[ 'defines' ][ 'WP_CACHE' ] = Gen::Constant( 'WP_CACHE' );

					$aTestRes[ 'ABSPATH' ][ 'path' ] = ABSPATH;
					$aTestRes[ 'ABSPATH' ][ 'isWritabble' ] = @is_writable( $aTestRes[ 'ABSPATH' ][ 'path' ] );

					$aTestRes[ 'WP_CONTENT_DIR' ][ 'path' ] = WP_CONTENT_DIR;
					$aTestRes[ 'WP_CONTENT_DIR' ][ 'isWritabble' ] = @is_writable( $aTestRes[ 'WP_CONTENT_DIR' ][ 'path' ] );

					$aTestRes[ 'WP_TEMP_DIR' ][ 'path' ] = Gen::Constant( 'WP_TEMP_DIR' );
					$aTestRes[ 'WP_TEMP_DIR' ][ 'isWritabble' ] = $aTestRes[ 'WP_TEMP_DIR' ][ 'path' ] ? @is_writable( $aTestRes[ 'WP_TEMP_DIR' ][ 'path' ] ) : false;

					$aTestRes[ 'temp_dir' ][ 'path' ] = Gen::GetTempDir();
					$aTestRes[ 'temp_dir' ][ 'isWritabble' ] = @is_writable( $aTestRes[ 'temp_dir' ][ 'path' ] );

					$aTestRes[ 'wp-config' ][ 'file' ] = Wp::GetConfigFilePath();
					$aTestRes[ 'wp-config' ][ 'isWritabble' ] = @is_writable( $aTestRes[ 'wp-config' ][ 'file' ] );

					$aTestRes[ 'advanced-cache' ][ 'file' ] = WP_CONTENT_DIR . '/advanced-cache.php';
					$aTestRes[ 'advanced-cache' ][ 'isWritabble' ] = @is_writable( $aTestRes[ 'advanced-cache' ][ 'file' ] );

					$aTestRes[ 'ctx' ] = GetContentProcessCtx( $_SERVER, Plugin::SettGet() );

					$aContBlock = array(
						'General'							=> Ui::Tag( 'pre', htmlentities( str_replace( '\\/', '/', json_encode( $aTestRes, JSON_PRETTY_PRINT ) ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 ) ),
						'Post types'						=> Ui::Tag( 'pre', htmlentities( str_replace( '\\/', '/', json_encode( get_post_types( array(), 'objects' ), JSON_PRETTY_PRINT ) ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 ) ),
						'Taxonomies'						=> Ui::Tag( 'pre', htmlentities( str_replace( '\\/', '/', json_encode( get_taxonomies( array(), 'objects' ), JSON_PRETTY_PRINT ) ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 ) ),
						'Content of \'wp-config.php\''		=> Ui::Tag( 'pre', htmlentities( ( string )Gen::FileGetContents( $aTestRes[ 'wp-config' ][ 'file' ] ) ) ),
						'Content of \'advanced-cache.php\''	=> Ui::Tag( 'pre', htmlentities( ( string )Gen::FileGetContents( $aTestRes[ 'advanced-cache' ][ 'file' ] ) ) ),

					);

					$fnName2Slug = function( $v )
					{
						return( preg_replace( '@\W@', '_', strtolower( $v ) ) );
					};

					echo( Ui::Tag( 'style', 'pre{tab-size:4;}h2{margin-top:2em;}' ) );

					echo( Ui::Tag( 'h1', 'Info' ) );
					foreach( $aContBlock as $contBlockName => $contBlockCont )
						echo( Ui::Link( $contBlockName, '#' . $fnName2Slug( $contBlockName ) ) . '<br>' );

					foreach( $aContBlock as $contBlockName => $contBlockCont )
						echo( Ui::Tag( 'h2', $contBlockName, array( 'id' => $fnName2Slug( $contBlockName ) ) ) . $contBlockCont );

					exit;
				}
			}
		, 99999 );

		ApplyContentProcessorForceSett( $sett, $settContPrOverride );
		Plugin::SettSet( $sett, true );
	}

	if( $seraph_accel_g_prepPrms !== null )
		Wp::RemoveFilters( 'init', 'wp_cron' );

	$seraph_accel_g_prepCont = $prepContSpecStage;

	{
		if( defined( 'EZOIC__PLUGIN_NAME' ) )
		{
			Wp::RemoveFilters( 'shutdown', array( 'Ezoic_Namespace\\Ezoic_Integration_Public', 'ez_buffer_end' ) );
			Wp::RemoveFilters( 'shutdown', array( 'Ezoic_Namespace\\Ezoic_Wp_Integration', 'ez_buffer_end' ) );

			add_filter( 'seraph_accel_content_pre',
				function( $buffer )
				{
					if( !Gen::DoesFuncExist( '\\Ezoic_Namespace\\Ezoic_Integration_WP_Request::get_content_response_from_ezoic' ) || !Gen::DoesFuncExist( '\\Ezoic_Namespace\\Ezoic_Integration_WP_Response::handle_ezoic_response' ) )
						return( $buffer );

					$ez_request = new \Ezoic_Namespace\Ezoic_Integration_WP_Request();
					$ez_response = new \Ezoic_Namespace\Ezoic_Integration_WP_Response();
					return( $ez_response -> handle_ezoic_response( $buffer, $ez_request -> get_content_response_from_ezoic( $buffer ) ) );
				}
			);
		}

		if( defined( 'HMWP_VERSION' ) )
		{
			$model = Gen::GetArrField( Wp::GetFilters( 'plugins_url', array( 'HMWP_Models_Rewrite', 'plugin_url' ) ), array( 0, 'f', 0 ) );
			if( $model && Gen::DoesFuncExist( 'HMWP_Models_Rewrite::find_replace' ) )
			{
				$ctx = new AnyObj();
				$ctx -> model = $model;
				$ctx -> cbAdjustSepCont =
					function( $ctx, $content, $isFile = true )
					{
						if( !$isFile )
							return( $content );

						$aFlt = Wp::RemoveFilters( 'hmwp_process_find_replace' );
						$content = $ctx -> model -> find_replace( $content );
						Wp::AddFilters( $aFlt );
						return( $content );
					}
				;

				add_filter( 'hmwp_process_buffer', '__return_false', 99999 );
				add_filter( 'hmwp_process_find_replace', '__return_false', 99999 );
				add_filter( 'seraph_accel_content', array( $ctx, 'cbAdjustSepCont' ) );
				add_filter( 'seraph_accel_css_content', array( $ctx, 'cbAdjustSepCont' ), 10, 2 );
				add_filter( 'seraph_accel_js_content', array( $ctx, 'cbAdjustSepCont' ), 10, 2 );
				add_filter( 'seraph_accel_html_content', array( $ctx, 'cbAdjustSepCont' ), 10, 2 );

			}
			unset( $model, $ctx );
		}

		if( Gen::DoesFuncExist( '\\WPH::proces_html_buffer' ) )
		{
			add_filter( 'wph/components/css_combine_code',     function( $ignore ) { return false; }, 99999 );
			add_filter( 'wph/components/js_combine_code',     function( $ignore ) { return false; }, 99999 );

			add_filter( 'wp-hide/ignore_ob_start_callback',     function( $ignore ) { return true; }, 99999 );

			$ctx = new AnyObj();
			$ctx -> cbAdjustSepCont =
				function( $ctx, $content, $isFile = true )
				{
					global $wph;

					if( !$isFile || !$wph || ($wph -> ob_callback_late??null) )
						return( $content );

					$content = $wph -> proces_html_buffer( $content );
					return( $content );
				}
			;

			add_filter( 'seraph_accel_content', array( $ctx, 'cbAdjustSepCont' ) );
			add_filter( 'seraph_accel_css_content', array( $ctx, 'cbAdjustSepCont' ), 10, 2 );
			add_filter( 'seraph_accel_js_content', array( $ctx, 'cbAdjustSepCont' ), 10, 2 );
			add_filter( 'seraph_accel_html_content', array( $ctx, 'cbAdjustSepCont' ), 10, 2 );

			unset( $ctx );
		}

		if( class_exists( 'WebpConverter\\WebpConverter' ) )
		{
			add_action( 'init', function() { Wp::RemoveFilters( 'init', array( 'WebpConverter\\Loader\\HtaccessBypassingLoader', 'start_buffering' ) ); }, 1 );

		}

		if( defined( 'WPSHIELD_CPP_PATH' ) )
		{
			Wp::RemoveFilters( 'init', array( 'WPShield\\Plugin\\ContentProtectorPro\\ContentProtectorSetup', 'buffer_start' ) );
			Wp::RemoveFilters( 'shutdown', array( 'WPShield\\Plugin\\ContentProtectorPro\\ContentProtectorSetup', 'buffer_end' ) );
			add_filter( 'seraph_accel_content', function( $content ) { return( apply_filters( 'wpshield/content-protector-pro/buffer/end/content', $content ) ); } );
		}

		if( function_exists( 'jet_elements' ) )
			Wp::RemoveFilters( 'init', array( 'Jet_Elements_Download_Handler', 'process_download' ) );
	}

	{

		if( Gen::GetArrField( $settContPr, array( 'img', 'lazy', 'load' ), false, '/' ) )
			add_filter( 'wp_lazy_loading_enabled', function( $default, $tag_name ) { return( ( $tag_name == 'img' || $tag_name == 'picture' ) ? false : $default ); }, 10, 2 );
		if( Gen::GetArrField( $settContPr, array( 'frm', 'lazy', 'enable' ), false, '/' ) )
			add_filter( 'wp_lazy_loading_enabled', function( $default, $tag_name ) { return( ( $tag_name == 'iframe' ) ? false : $default ); }, 10, 2 );
	}

	$settImg = Gen::GetArrField( $settContPr, array( 'img' ), array() );
	$settCdn = Gen::GetArrField( $settContPr, array( 'cdn' ), array() );

	if( Gen::GetArrField( $settImg, array( 'sysFlt' ), false ) && ( Gen::GetArrField( $settImg, array( 'srcAddLm' ), false ) || Gen::GetArrField( $settCdn, array( 'enable' ), false ) ) )
	{

		add_filter( 'wp_get_attachment_url',
			function( $url, $post_id )
			{
				if( !$url )
					return( $url );

				$sett = Plugin::SettGet();
				$settCache = Gen::GetArrField( $sett, array( 'cache' ), array() );
				$settContPr = Gen::GetArrField( $sett, array( 'contPr' ), array() );
				$settImg = Gen::GetArrField( $settContPr, array( 'img' ), array() );
				$settCdn = Gen::GetArrField( $settContPr, array( 'cdn' ), array() );

				_ContentProcessor_TmpCont_SettImg_Adjust( $settImg );

				$ctxProcess = &GetContentProcessCtx( $_SERVER, $sett );

				$url = new ImgSrc( $ctxProcess, $url );
				Images_ProcessSrc( $ctxProcess, $url, $settCache, $settImg, $settCdn );
				return( $url -> src );
			}
		, 9999, 2 );

	    add_filter( 'wp_get_attachment_image_src',
	        function( $image, $attachment_id, $size, $icon )
	        {
	            if( !is_array( $image ) )
					return( $image );

				$src = ($image[ 0 ]??null);
	            if( !$src )
					return( $image );

				$sett = Plugin::SettGet();
				$settCache = Gen::GetArrField( $sett, array( 'cache' ), array() );
				$settContPr = Gen::GetArrField( $sett, array( 'contPr' ), array() );
				$settImg = Gen::GetArrField( $settContPr, array( 'img' ), array() );
				$settCdn = Gen::GetArrField( $settContPr, array( 'cdn' ), array() );

				_ContentProcessor_TmpCont_SettImg_Adjust( $settImg );

				$ctxProcess = &GetContentProcessCtx( $_SERVER, $sett );

				$src = new ImgSrc( $ctxProcess, $src );
	            if( Images_ProcessSrc( $ctxProcess, $src, $settCache, $settImg, $settCdn ) )
	                $image[ 0 ] = $src -> src;

	            return( $image );
	        }
	    , 9999, 4 );

	    add_filter( 'wp_calculate_image_srcset',
	        function( $sources, $size_array, $image_src, $image_meta, $attachment_id )
	        {
	            if( !is_array( $sources ) )
	                return( $sources );

				$sett = Plugin::SettGet();
				$settCache = Gen::GetArrField( $sett, array( 'cache' ), array() );
				$settContPr = Gen::GetArrField( $sett, array( 'contPr' ), array() );
				$settImg = Gen::GetArrField( $settContPr, array( 'img' ), array() );
				$settCdn = Gen::GetArrField( $settContPr, array( 'cdn' ), array() );

				_ContentProcessor_TmpCont_SettImg_Adjust( $settImg );

				$ctxProcess = &GetContentProcessCtx( $_SERVER, $sett );

	            foreach( $sources as &$source )
	            {
	                if( !is_array( $source ) )
	                    continue;

					$src = ($source[ 'url' ]??null);
	                if( !$src )
	                    continue;

					$src = new ImgSrc( $ctxProcess, $src );
	                if( Images_ProcessSrc( $ctxProcess, $src, $settCache, $settImg, $settCdn ) )
	                    $source[ 'url' ] = $src -> src;
	            }

	            return( $sources );
	        }
	    , 9999, 5 );
	}

	if( ($settContPr[ 'enable' ]??null) && !Gen::GetArrField( $sett, array( 'emojiIcons' ), true, '/' ) )
		add_action( 'wp_loaded',
			function()
			{
				remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
				remove_action( 'wp_print_styles', 'print_emoji_styles' );
				remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
				remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
				remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
				add_filter( 'emoji_svg_url', '__return_false' );

			}
	);

	if( Gen::GetArrField( $settContPr, array( 'rc', 'thmFltsm' ), false ) && function_exists( 'flatsome_text_box'  ) )
	{
		add_action( 'wp_loaded',
			function()
			{
				$dir = dirname( ( string )Gen::GetFuncFile( 'flatsome_text_box' ) );
				if( !$dir )
					return;

				global $shortcode_tags;

				static $g_aTag2IdPrefix = array(
					'accordion-item' => 'accordion-',
					'blog_posts' => 'row-',
					'gap' => 'gap-',
					'map' => 'map-',
					'page_header' => 'page-header-',
					'ux_product_categories' => 'cats-',
					'ux_product_categories_grid' => 'cats-',
					'ux_product_flip' => 'product-flip-',
					'col' => 'col-',
					'col_inner' => 'col-',
					'col_inner_1' => 'col-',
					'col_inner_2' => 'col-',
					'row' => 'row-',
					'row_inner' => 'row-',
					'row_inner_1' => 'row-',
					'row_inner_2' => 'row-',
					'background' => 'section_',
					'section' => 'section_',
					'section_inner' => 'section_',
					'tabgroup' => 'panel-',
					'tabgroup_vertical' => 'panel-',
					'text_box' => 'text-box-',
					'title' => 'title-',
					'ux_banner' => 'banner-',
					'ux_banner_grid' => 'banner-grid-',
					'ux_gallery' => 'gallery-',
					'ux_image' => 'image_',
					'ux_instagram_feed' => 'instagram-',
					'ux_pages' => 'pages-',
					'ux_bestseller_products' => 'product-grid-',
					'ux_featured_products' => 'product-grid-',
					'ux_sale_products' => 'product-grid-',
					'ux_latest_products' => 'product-grid-',
					'ux_custom_products' => 'product-grid-',
					'product_lookbook' => 'product-grid-',
					'products_pinterest_style' => 'product-grid-',
					'ux_products' => 'product-grid-',
					'ux_slider' => 'slider-',
					'ux_text' => 'text-',
					'ux_countdown' => 'timer-',

				);

				$data = new AnyObj();
				$data -> idxs = array();

				foreach( $shortcode_tags as $tag => $cb )
				{
					if( !is_string( $cb ) )
						continue;

					if( !Gen::StrStartsWith( ( string )Gen::GetFuncFile( $cb ), $dir ) )
						continue;

					$hook = new AnyObj();

					$hook -> idPrefix = ($g_aTag2IdPrefix[ $tag ]??null);
					if( !$hook -> idPrefix )
						continue;

					$hook -> data = $data;
					$hook -> cbPrev = $cb;
					$hook -> cb =
						function( $hook, $attrs, $content, $tag )
						{
							$content = call_user_func( $hook -> cbPrev, $attrs, $content, $tag );

							if( preg_match( '@\\sid\\s*=\\s*["\'](' . $hook -> idPrefix . ')(\\d+)@', $content, $m ) )
							{
								$idx = &$hook -> data -> idxs[ $m[ 1 ] ];

								$id = $m[ 1 ] . 'a' . ( ++$idx );
								$content = str_replace( $m[ 1 ] . $m[ 2 ], $id, $content );

							}

							return( $content );
						}
					;

					$shortcode_tags[ $tag ] = array( $hook, 'cb' );
				}
			}
		);
	}
}

function OnEarlyContentComplete( $buffer, $tmpUpdate = false )
{

	global $seraph_accel_g_prepCont;
	global $seraph_accel_g_prepPrms;
	global $seraph_accel_g_lazyInvTmp;
	global $seraph_accel_g_contProcGetSkipStatus;
	global $seraph_accel_g_simpCacheMode;

	if( $seraph_accel_g_prepCont === null && ( defined( 'LINGUISE_SCRIPT_TRANSLATION' )  ) )
		_InitContentProcessor();

	if( !$seraph_accel_g_prepCont )
	{
		if( $seraph_accel_g_prepCont === null && $seraph_accel_g_prepPrms !== null )
		{
			$seraph_accel_g_contProcGetSkipStatus = null;
			ContProcGetSkipStatus( $buffer );
			if( ( !$seraph_accel_g_contProcGetSkipStatus || $seraph_accel_g_contProcGetSkipStatus == 'noHdrOrBody' ) && !( is_string( $seraph_accel_g_simpCacheMode ) && Gen::StrStartsWith( ( string )$seraph_accel_g_simpCacheMode, 'data:' ) ) )
				$seraph_accel_g_contProcGetSkipStatus = 'err:contTermEarly:' . rawurlencode( Gen::GetCallStack( DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS ) );
		}

		return( apply_filters( 'seraph_accel_html_content', $buffer, true ) );
	}

	if( !IsHtml( $buffer ) )
	{

		return( $buffer );
	}

	$skipStatus = ContProcGetSkipStatus( $buffer );
	if( $skipStatus )
		return( apply_filters( 'seraph_accel_html_content', $buffer, true ) );

	$sett = Plugin::SettGet();
	if( is_multisite() )
	{
		$settCacheGlobal = Gen::GetArrField( Plugin::SettGetGlobal(), array( 'cache' ), array() );
		foreach( array( array( 'cache', 'procWorkInt' ), array( 'cache', 'procPauseInt' ) ) as $fldPath )
			Gen::SetArrField( $sett, $fldPath, Gen::GetArrField( $settCacheGlobal, $fldPath ) );
		unset( $fldPath, $settCacheGlobal );
	}

	$settCache = Gen::GetArrField( $sett, array( 'cache' ), array() );
	$settContPr = Gen::GetArrField( $sett, array( 'contPr' ), array() );

	$buffer = apply_filters( 'seraph_accel_content_pre', ContentPreProcess( $buffer, Gen::GetArrField( $settContPr, array( 'rc' ), array() ) ) );

	global $seraph_accel_g_dataPath;
	global $seraph_accel_g_prepOrigContHashPrev;
	global $seraph_accel_g_prepOrigContHash;
	global $seraph_accel_g_prepOrigCont;
	global $seraph_accel_g_bPrepContTmpToMain;
	global $seraph_accel_g_cacheObjChildren;
	global $seraph_accel_g_cacheObjSubs;
	global $seraph_accel_g_ctxProcess;

	{
		$dataForChecksum = $buffer;
		foreach( GetCurHdrsToStoreInCache( $settCache ) as $hdr )
			$dataForChecksum .= $hdr;
		$dataForChecksum .= @json_encode( $settContPr ) . @json_encode( $settCache );

		$seraph_accel_g_prepOrigContHash = md5( $dataForChecksum, true );
		if( $seraph_accel_g_prepOrigContHash === $seraph_accel_g_prepOrigContHashPrev && !( $seraph_accel_g_prepPrms !== null && isset( $seraph_accel_g_prepPrms[ 'lrn' ] ) ) )
		{
			$seraph_accel_g_contProcGetSkipStatus = 'notChanged';
			return( $buffer );
		}

		unset( $dataForChecksum );
	}

	if( Gen::StrStartsWith( $seraph_accel_g_prepCont, 'tmp' ) )
	{
		if( $seraph_accel_g_lazyInvTmp && Gen::GetArrField( $settCache, array( 'fastTmpOpt' ), false ) )
		{
			$seraph_accel_g_prepOrigCont = $buffer;

			$tmProc = microtime( true );
			$buffer = _EarlyContentComplete( $buffer, 1 | ( $seraph_accel_g_prepCont == 'tmpLong' ? 2 : 0 ), $sett, $settCache, $settContPr, $skipStatus );
			$tmProc = microtime( true ) - $tmProc;

			$seraph_accel_g_ctxProcess[ 'stat' ][ 'Total-Duration' ] = LocId::Pack( 'TimeDurSec_%1$s', null, array( sprintf( '%.1F', $tmProc ) ) );
			if( !$seraph_accel_g_ctxProcess[ 'modeReq' ] )
				$seraph_accel_g_bPrepContTmpToMain = true;

			if( !$skipStatus )
				$skipStatus = 'ok';

			if( ($sett[ 'hdrTrace' ]??null) )
			{
				if( !headers_sent() )
					header( 'X-Seraph-Accel-Content: 2.28.15; status=' . $skipStatus . ', procTime=' . $tmProc . 's' );

			}
		}

		return( apply_filters( 'seraph_accel_content', $buffer ) );
	}

	$bPrepContTmpToMain = false;
	$skipStatus = null;
	$tmProc = 0;

	if( $tmpUpdate && $seraph_accel_g_prepCont != 'fragments' )
	{
		if( $seraph_accel_g_prepPrms !== null && $seraph_accel_g_lazyInvTmp && !Gen::GetArrField( $settCache, array( 'fastTmpOpt' ), false )  )
		{
			$bufferTmp = $buffer;
			$ctxProcessCur = $seraph_accel_g_ctxProcess;
			if( Gen::GetArrField( $settCache, array( 'fastTmpOpt' ), false ) )
			{
				$seraph_accel_g_prepOrigCont = $bufferTmp;

				$prepPrmsCur = $seraph_accel_g_prepPrms;

				$tmProc = microtime( true );
				$bufferTmp = _EarlyContentComplete( $bufferTmp, 1 | 2, $sett, $settCache, $settContPr, $skipStatus );
				$tmProc = microtime( true ) - $tmProc;

				$seraph_accel_g_ctxProcess[ 'stat' ][ 'Total-Duration' ] = LocId::Pack( 'TimeDurSec_%1$s', null, array( sprintf( '%.1F', $tmProc ) ) );

				if( !$seraph_accel_g_ctxProcess[ 'modeReq' ] )
					$bPrepContTmpToMain = true;

				$seraph_accel_g_prepPrms = $prepPrmsCur;

				unset( $prepPrmsCur );
			}

			$bufferTmp = apply_filters( 'seraph_accel_content', $bufferTmp );

			if( $skipStatus )
				return( $bufferTmp );

			if( !$bPrepContTmpToMain )
			{
				$lock = new Lock( 'dl', GetCacheDir() );
				CacheDscUpdate( $lock, $settCache, $bufferTmp, $seraph_accel_g_cacheObjChildren, $seraph_accel_g_cacheObjSubs, $seraph_accel_g_dataPath, 'u', $seraph_accel_g_prepOrigCont, $seraph_accel_g_prepOrigContHash );
				unset( $lock );

				CacheAdditional_UpdateCurUrl( $settCache, true );

				$seraph_accel_g_cacheObjChildren = $seraph_accel_g_cacheObjSubs = null;
				$seraph_accel_g_ctxProcess = $ctxProcessCur;
			}
			else
			{
				$buffer = $bufferTmp;
			}

			unset( $bufferTmp, $ctxProcessCur );
		}
	}

	if( !$bPrepContTmpToMain )
	{
		$tmProc = microtime( true );
		$buffer = _EarlyContentComplete( $buffer, $seraph_accel_g_prepCont == 'fragments' ? 256 : ( 1 | 2 | 4 ), $sett, $settCache, $settContPr, $skipStatus );
		$tmProc = microtime( true ) - $tmProc;

		$seraph_accel_g_ctxProcess[ 'stat' ][ 'Total-Duration' ] = LocId::Pack( 'TimeDurSec_%1$s', null, array( sprintf( '%.1F', $tmProc ) ) );

		$buffer = apply_filters( 'seraph_accel_content', $buffer );
	}

	if( !$skipStatus )
		$skipStatus = 'ok';

	if( ($sett[ 'hdrTrace' ]??null) )
	{
		if( !headers_sent() )
			header( 'X-Seraph-Accel-Content: 2.28.15; status=' . $skipStatus . ', procTime=' . $tmProc . 's' );

	}

	return( $buffer );
}

function _EarlyContentComplete( $buffer, $mode, $sett, $settCache, $settContPr, &$skipStatus = null )
{
	global $seraph_accel_g_contProcGetSkipStatus;

	{
		$memLim = Gen::GetArrField( $settCache, array( 'procMemLim' ), 0 );

		$memLimCur = wp_convert_hr_to_bytes( @ini_get( 'memory_limit' ) ) / 1024 / 1024;

		if( $memLimCur < $memLim )
		{

			@ini_set( 'memory_limit', ( string )$memLim . 'M' );

		}

		unset( $memLim );
		unset( $memLimCur );
	}

	$ctxProcess = &GetContentProcessCtx( $_SERVER, $sett );
	$ctxProcess[ 'mode' ] = $mode;

	if( ($settCache[ 'enable' ]??null) && Gen::GetArrField( $settCache, array( 'chunks', 'enable' ), false ) )
		$ctxProcess[ 'chunksEnabled' ] = true;

	$skipStatus = null;

	$errorReportingPrevLevel = @error_reporting( E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR );
	$encPrev = ContentParseStrIntEncodingCorrect();
	$buffer = ContentProcess( $ctxProcess, $sett, $settCache, $settContPr, $buffer, $skipStatus );
	ContentParseStrIntEncodingRestore( $encPrev );
	@error_reporting( $errorReportingPrevLevel );

	if( !( $ctxProcess[ 'mode' ] & 2 ) )
		$ctxProcess[ 'modeReq' ] |= 2;

	if( $skipStatus )
	{
		$seraph_accel_g_contProcGetSkipStatus = $skipStatus;
		if( Gen::LastErrDsc_Is() )
			$seraph_accel_g_contProcGetSkipStatus .= ':' . rawurlencode( Gen::LastErrDsc_Get() );
	}

	return( $buffer );
}

function ContentProcess_IsItemInFragments( $ctxProcess, $item, $cbCmp = null )
{
	if( !( $ctxProcess[ 'mode' ] & 256 ) )
		return( true );

	foreach( $ctxProcess[ 'fragments' ] as $itemFragment )
		if( HtmlNd::DoesContain( $itemFragment, $item ) )
			return( $cbCmp ? @call_user_func( $cbCmp, $itemFragment, $item ) : true );

	return( false );
}

function ContentProcess_GetCurRelatedUri( $ctxProcess, $args )
{
	$requestPath = ParseContCachePathArgs( $ctxProcess[ 'serverArgs' ], $requestArgs );
	return( Net::UrlAddArgsEx( $ctxProcess[ 'ndHeadBase' ] ? $requestPath : '', array_merge( $requestArgs, $args ) ) );
}

function ContentProcess_GetGetPartUri( $ctxProcess, $id )
{
	return( ContentProcess_GetCurRelatedUri( $ctxProcess, array( 'seraph_accel_gp' => ( string )Gen::GetCurRequestTime( $ctxProcess[ 'serverArgs' ] ) . '_' . str_replace( '.', '_', $id ) ) ) );
}

function ContentPreProcess( $buffer, $settRc, $test = false )
{

	if( Gen::GetArrField( $settRc, array( 'gglTrn' ), false ) && ( $test || Gen::DoesFuncExist( 'GTranslate::activate'  ) ) )
	{
		$aIds = array();
		$offs = 0;
		while( preg_match( '@id=([\'"])gt-wrapper-(\\d+)(?1)@S', $buffer, $m, PREG_OFFSET_CAPTURE, $offs ) )
		{
			$offs = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
			$aIds[] = $m[ 2 ][ 0 ];
		}

		$unique_id_base = substr( Wp::GetSiteId(), 0, 8 );

		foreach( $aIds as $i => $id )
		{
			$unique_id = $unique_id_base . '-' . ( $i + 1 );
			$buffer = preg_replace( '@gt-wrapper-' . $id . '@', 'gt-wrapper-' . $unique_id, $buffer );
			$buffer = preg_replace( '@gt_widget_script_' . $id . '@', 'gt_widget_script_' . $unique_id, $buffer );
			$buffer = preg_replace( '@data-gt-widget-id=([\'"])' . $id . '(?1)@', 'data-gt-widget-id=${1}' . $unique_id . '${1}', $buffer );
			$buffer = preg_replace( '@\\.gtranslateSettings\\[\\\'' . $id . '@', '.gtranslateSettings[\'' . $unique_id, $buffer );
		}
	}

	if( Gen::GetArrField( $settRc, array( 'aksmtAs' ), false ) && ( $test || defined( 'AKISMET_VERSION' ) ) )
	{
		$buffer = preg_replace_callback( '@id="ak_js_\\d+"\\s+name="ak_js"\\s+value="(\\d+)@i',
			function( $m )
			{
				return( substr( $m[ 0 ], 0, -strlen( $m[ 1 ] ) ) . '0' );
			}
		, $buffer );
	}

	if( Gen::GetArrField( $settRc, array( 'advWooSrch' ), false ) )
	{
		$aIds = array();
		$offs = 0;
		while( preg_match( '@class=([\'"])aws-search-label(?1)\\sfor=(?1)([^\'"]+)(?1)@S', $buffer, $m, PREG_OFFSET_CAPTURE, $offs ) )
		{
			$offs = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
			$aIds[] = $m[ 2 ][ 0 ];
		}

		foreach( $aIds as $i => $id )
			$buffer = preg_replace( '@([\'"])' . $id . '(?1)@', '${1}aws-search-' . ( $i + 1 ) . '${1}', $buffer );
	}

	if( Gen::GetArrField( $settRc, array( 'jetMblMnu' ), false ) )
	{
		$aIds = array();
		$offs = 0;
		while( preg_match( '@id=([\'"])jet-mobile-menu-([\\da-fA-F]+)(?1)@S', $buffer, $m, PREG_OFFSET_CAPTURE, $offs ) )
		{
			$offs = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
			$aIds[] = $m[ 2 ][ 0 ];
		}

		foreach( $aIds as $i => $id )
			$buffer = str_replace( array( 'jet-mobile-menu-' . $id, 'menuUniqId&quot;:&quot;' . $id, 'menuUniqId":"' . $id, 'jetMenuMobileWidgetRenderData' . $id ), array( 'jet-mobile-menu-' . ( $i + 1 ), 'menuUniqId&quot;:&quot;' . ( $i + 1 ), 'menuUniqId":"' . ( $i + 1 ), 'jetMenuMobileWidgetRenderData' . ( $i + 1 ) ), $buffer );
	}

	if( Gen::GetArrField( $settRc, array( 'wpelLnk' ), false ) )
	{
		$aIds = array();
		$offs = 0;
		while( preg_match( '@<a[^>]+class=([\'"])(u[A-Fa-f0-9]{32})(?1)\\s+data-wpel-link=[^>]+>@S', $buffer, $m, PREG_OFFSET_CAPTURE, $offs ) )
		{
			$offs = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
			$aIds[] = $m[ 2 ][ 0 ];
		}

		foreach( $aIds as $i => $id )
			$buffer = str_replace( array( $id ), array( 'wpel-link-u-' . ( $i + 1 ) ), $buffer );
	}

	if( Gen::GetArrField( $settRc, array( 'cfTrnstl' ), false ) )
	{
		$aIds = array();
		$offs = 0;
		while( preg_match( '@\\sid=([\'"])cf-turnstile-([\\w\\-]+)-(\\d+)(?1)@S', $buffer, $m, PREG_OFFSET_CAPTURE, $offs ) )
		{
			$offs = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
			$aIds[] = array( 'prefix' => $m[ 2 ][ 0 ], 'random' => $m[ 3 ][ 0 ] );
		}

		foreach( $aIds as $i => $aId )
			$buffer = str_replace( array( $aId[ 'prefix' ] . '-' . $aId[ 'random' ] ), array( $aId[ 'prefix' ] . '-' . ( $i + 1 ) ), $buffer );
	}

	if( Gen::GetArrField( $settRc, array( 'tagGrpsShfflBx' ), false ) && @preg_match( '@<script\\s[^>]+plugins/tag-groups[^>]+js/shuffle-box@', $buffer ) )
	{
		$aIds = array();
		$offs = 0;
		while( preg_match( '@\\sid=([\'"])tag-groups-shuffle-box-([a-zA-Z\\d]+)(?1)@S', $buffer, $m, PREG_OFFSET_CAPTURE, $offs ) )
		{
			$offs = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
			$aIds[] = $m[ 2 ][ 0 ];
		}

		foreach( $aIds as $i => $id )
			$buffer = str_replace( array( 'tag-groups-shuffle-box-' . $id ), array( 'tag-groups-shuffle-box-' . ( $i + 1 ) ), $buffer );
	}

	if( Gen::GetArrField( $settRc, array( 'g5Ere' ), false ) && @preg_match( '@<script\\s[^>]+/plugins/g5-ere/@', $buffer ) )
	{
		$aIds = array();
		$offs = 0;
		while( preg_match( '@\\sdata-prefix=([\'"])(g5ere_[a-zA-Z_]+-)([a-fA-f\\d]+)(?1)@S', $buffer, $m, PREG_OFFSET_CAPTURE, $offs ) )
		{
			$offs = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
			$aIds[] = array( 'prefix' => $m[ 2 ][ 0 ], 'random' => $m[ 3 ][ 0 ] );
		}

		foreach( $aIds as $i => $aId )
			$buffer = str_replace( array( $aId[ 'prefix' ] . $aId[ 'random' ] ), array( $aId[ 'prefix' ] . ( $i + 1 ) ), $buffer );
	}

	if( Gen::GetArrField( $settRc, array( 'elmntrTrx' ), false ) && @preg_match( '@<style\\s[^>]+>([^<]*\\.trx_addons_inline_\\d+[^<]*)</style>@', $buffer, $m ) )
	{
		$cont = $m[ 1 ];

		$aIds = array();
		$offs = 0;
		while( preg_match( '@\\.(trx_addons_inline_)(\\d+)@S', $cont, $m, PREG_OFFSET_CAPTURE, $offs ) )
		{
			$offs = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
			$aIds[] = array( 'prefix' => $m[ 1 ][ 0 ], 'random' => $m[ 2 ][ 0 ] );
		}

		unset( $cont );

		foreach( $aIds as $i => $aId )
			$buffer = str_replace( array( $aId[ 'prefix' ] . $aId[ 'random' ] ), array( $aId[ 'prefix' ] . ( $i + 1 ) ), $buffer );
	}

	if( Gen::GetArrField( $settRc, array( 'thmXStr' ), false ) && @preg_match( '@<style\\s[^>]*id=[\'"]xstore-inline-css-inline-css[\'"][^>]*>([^<]*)</style>@', $buffer, $m ) )
	{
		$cont = $m[ 1 ];

		$aIds = array();

		$offs = 0;
		while( preg_match( '@\\sid=([\'"])((?:filter|path|banner)-)(\\d+)(?1)@S', $buffer, $m, PREG_OFFSET_CAPTURE, $offs ) )
		{
			$offs = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );

			$class = $m[ 2 ][ 0 ];
			if( !isset( $aIds[ $class ][ 't' ] ) )
				$aIds[ $class ][ 't' ] = array( 's' => '@(\\W' . preg_quote( $class ) . '){RID}(\\W)@', 'r' => '${1}thmxstr-{NID}${2}' );
			$aIds[ $class ][ 'i' ][] = $m[ 3 ][ 0 ];
		}

		$offs = 0;
		while( preg_match( '@<\\w+\\s[^>]*class=[\'"][^\'"]*\\s((?:slider|menu-list)-)(\\d+)@S', $buffer, $m, PREG_OFFSET_CAPTURE, $offs ) )
		{
			$offs = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );

			$class = $m[ 1 ][ 0 ];
			if( !isset( $aIds[ $class ][ 't' ] ) )
				$aIds[ $class ][ 't' ] = array( 's' => '@(\\W' . preg_quote( $class ) . '){RID}(\\W)@', 'r' => '${1}thmxstr-{NID}${2}' );
			$aIds[ $class ][ 'i' ][] = $m[ 2 ][ 0 ];
		}

		$offs = 0;
		while( preg_match( '@<\\w+\\s[^>]*class=[\'"][^\'"]*\\s((?:menu-item)-)(\\d+)@S', $buffer, $m, PREG_OFFSET_CAPTURE, $offs ) )
		{
			$offs = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );

			if( !preg_match( '@\\.' . preg_quote( $m[ 1 ][ 0 ] . $m[ 2 ][ 0 ] ) . '@', $cont ) )
				continue;

			$class = $m[ 1 ][ 0 ];
			if( !isset( $aIds[ $class ][ 't' ] ) )
				$aIds[ $class ][ 't' ] = array( 's' => '@(\\W' . preg_quote( $class ) . '){RID}(\\W)@', 'r' => '${1}thmxstr-{NID}${2}' );
			$aIds[ $class ][ 'i' ][] = $m[ 2 ][ 0 ];
		}

		$offs = 0;
		while( preg_match( '@<\\w+\\s[^>]*class=[\'"][^\'"]*\\s(slider-item-)(\\d+)@S', $buffer, $m, PREG_OFFSET_CAPTURE, $offs ) )
		{
			$offs = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );

			if( !preg_match( '@\\.' . preg_quote( $m[ 1 ][ 0 ] . $m[ 2 ][ 0 ] ) . '@', $cont ) )
				continue;

			$class = $m[ 1 ][ 0 ];
			if( !isset( $aIds[ $class ][ 't' ] ) )
				$aIds[ $class ][ 't' ] = array( 's' => array( '@(\\W' . preg_quote( $class ) . '){RID}(\\W)@', '@(\\Wdata-slide-id=[\'"]){RID}([\'"]\\W)@' ), 'r' => array( '${1}thmxstr-{NID}${2}', '${1}{NID}${2}' ) );
			$aIds[ $class ][ 'i' ][] = $m[ 2 ][ 0 ];
		}

		unset( $cont );

		foreach( $aIds as $aId )
			foreach( $aId[ 'i' ] as $i => $id )
				$buffer = preg_replace( str_replace( '{RID}', preg_quote( $id ), $aId[ 't' ][ 's' ] ), str_replace( '{NID}', ( string )( $i + 1 ), $aId[ 't' ][ 'r' ] ), $buffer );
	}

	if( Gen::GetArrField( $settRc, array( 'wooPrdQnt' ), false ) && @preg_match( '@<body\\s[^>]*class=[\'"][^\'"]*woocommerce@', $buffer, $m ) )
	{
		$aIds = array();

		$offs = 0;
		while( preg_match( '@<input\\s+type="number"[^>]*\\sid=([\'"])(quantity_)([\\da-f\\.]+)(?1)@S', $buffer, $m, PREG_OFFSET_CAPTURE, $offs ) )
		{
			$offs = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );

			$class = $m[ 2 ][ 0 ];
			if( !isset( $aIds[ $class ][ 't' ] ) )
				$aIds[ $class ][ 't' ] = array( 's' => '@(\\W' . preg_quote( $class ) . '){RID}(\\W)@', 'r' => '${1}wooprd-{NID}${2}' );
			$aIds[ $class ][ 'i' ][] = $m[ 3 ][ 0 ];
		}

		foreach( $aIds as $aId )
			foreach( $aId[ 'i' ] as $i => $id )
				$buffer = preg_replace( str_replace( '{RID}', preg_quote( $id ), $aId[ 't' ][ 's' ] ), str_replace( '{NID}', ( string )( $i + 1 ), $aId[ 't' ][ 'r' ] ), $buffer );
	}

	if( Gen::GetArrField( $settRc, array( 'asClnTlk' ), false ) && @preg_match( '@<script\\s[^>]+id=["\']apbct-public-bundle@', $buffer, $m ) )
	{
		$aIds = array();
		$offs = 0;
		while( preg_match( '@<input\\s[^>]+id=["\'](ct_checkjs_[a-z0-9]+_)(\\w+)@S', $buffer, $m, PREG_OFFSET_CAPTURE, $offs ) )
		{
			$offs = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
			$aIds[ $m[ 1 ][ 0 ] ][] = array( 'random' => $m[ 2 ][ 0 ] );
		}

		foreach( $aIds as $prefix => $aIdsE )
			foreach( $aIdsE as $i => $aId )
				$buffer = str_replace( array( $prefix . $aId[ 'random' ] ), array( $prefix . ( $i + 1 ) ), $buffer );
	}

	if( Gen::GetArrField( $settRc, array( 'chbsBkngFrm' ), false ) )
	{
		$aIds = array();
		$offs = 0;
		while( preg_match( '@\\Wid=["\'](chbs_booking_form_)(\\w+)@', $buffer, $m, PREG_OFFSET_CAPTURE, $offs ) )
		{
			$offs = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
			$aIds[ $m[ 1 ][ 0 ] ][] = array( 'random' => $m[ 2 ][ 0 ] );
		}

		foreach( $aIds as $prefix => $aIdsE )
			foreach( $aIdsE as $i => $aId )
				$buffer = str_replace( array( $prefix . $aId[ 'random' ] ), array( $prefix . ( $i + 1 ) ), $buffer );
	}

	return( $buffer );
}

function ContentProcess( &$ctxProcess, $sett, $settCache, $settContPr, $buffer, &$skipStatus )
{
	Gen::SetTimeLimit( Gen::GetArrField( $settCache, array( 'procTmLim' ), 570 ) );
	Gen::GarbageCollectorEnable( false );

	global $seraph_accel_g_prepPrms;
	global $seraph_accel_g_ctxCache;
	global $seraph_accel_g_prepContIsUserCtx;
	global $seraph_accel_g_prepLearnId;

	if( GetContentProcessorForce( $sett ) !== null && ($_REQUEST[ 'd' ]??null) == 'orig' )
		return( $buffer );

	$ctxProcess[ '_stat' ][ 'imgRead' ][ 'v' ] = 0;
	$ctxProcess[ '_stat' ][ 'imgReadCount' ][ 'v' ] = 0;
	$ctxProcess[ '_stat' ][ 'ai' ][ 'v' ] = null;
	foreach( array( 'webp','avif' ) as $comprType )
		$ctxProcess[ '_stat' ][ 'compr' ][ $comprType ][ 'v' ] = null;

	$stage = 'parse'; if( $seraph_accel_g_prepPrms && !( $resUpd = ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array( 'stage' => $stage ) ) ) ) { $skipStatus = ( $resUpd === null ) ? 'aborted' : 'err:internal'; return( $buffer ); }

	{
		$tmStat = microtime( true );

	}

	$bufferNoOpt = $buffer;
	ContentProcess_Replace( $ctxProcess, $settCache, $settContPr, $buffer );
	if( ContentProcess_IsAborted( $ctxProcess, $settCache ) ) { $skipStatus = 'aborted'; return( $buffer ); }

	$norm = Gen::GetArrField( $settContPr, array( 'normalize' ), 0 );
	$doc = GetHtmlDoc( $buffer, $norm, Gen::GetArrField( $settContPr, array( 'min' ), false ), Gen::GetArrField( $settContPr, array( 'cln', 'cmts' ), false ) ? Gen::GetArrField( $settContPr, array( 'cln', 'cmtsExcl' ), array() ) : true );

	if( GetContentProcessorForce( $sett ) !== null )
	{
		if( ($_REQUEST[ 'd' ]??null) == 'origrpl' )
			return( $buffer );
		if( ($_REQUEST[ 'd' ]??null) == 'origparsed' )
			return( HtmlDocDeParse( $doc, $norm ) );
		if( ($_REQUEST[ 'd' ]??null) == 'origparsedstruct' )
			return( '<pre>' . htmlentities( @json_encode( HtmlNd::Dump( $doc ), JSON_PRETTY_PRINT ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 ) . '</pre>' );
	}

	{
		$tmStat = microtime( true ) - $tmStat;

		$ctxProcess[ 'stat' ][ ucfirst( $stage ) . '-Duration' ] = LocId::Pack( 'TimeDurSec_%1$s', null, array( sprintf( '%.1F', $tmStat ) ) );
	}

	if( !$doc )
	{
		$skipStatus = 'err:' . $stage;
		return( $buffer );
	}

	if( ContentProcess_IsAborted( $ctxProcess, $settCache ) ) { $skipStatus = 'aborted'; return( $buffer ); }

	$ctxProcess[ 'ndHtml' ] = HtmlNd::FindByTag( $doc, 'html', false );
	$ctxProcess[ 'ndHead' ] = HtmlNd::FindByTag( $ctxProcess[ 'ndHtml' ], 'head', false );
	$ctxProcess[ 'ndHeadBase' ] = HtmlNd::FindByTag( $ctxProcess[ 'ndHead' ], 'base', false );
	$ctxProcess[ 'ndBody' ] = HtmlNd::FindByTag( $ctxProcess[ 'ndHtml' ], 'body', false );

	if( !$ctxProcess[ 'ndHead' ] || !$ctxProcess[ 'ndBody' ] )
	{
		$skipStatus = 'err:noHdrOrBody';
		return( $buffer );
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) && ($ctxProcess[ 'debug' ]??null) )
	{
		{
			$item = $doc -> createElement( 'script' );
			$item -> setAttribute( 'seraph-accel-crit', '1' );
			$item -> nodeValue = htmlspecialchars( '
				(function()
				{
					new PerformanceObserver(
						function( entryList )
						{
							for( const entry of entryList.getEntries() )
							{
								console.log( "LCP candidate: ", entry.startTime, entry );
							}
						}
					).observe( { type: "largest-contentful-paint", buffered: true } );
				})();
			' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $item );
		}

		$item = $doc -> createElement( 'script' );
		$item -> setAttribute( 'type', 'text/javascript' );
		$item -> setAttribute( 'id', 'seraph-accel-testLoad' );
		$item -> nodeValue = htmlspecialchars( '
			(function()
			{
				var callsCheck = {};

				function cr( k, fromFunc )
				{
					console.log( "seraph_accel: \\"" + k + "\\" just triggered" + ( fromFunc ? " from \\"" + fromFunc + "\\"" : "" ) );
					if( !callsCheck[ k ] )
						callsCheck[ k ] = { n: 0 };
					return( callsCheck[ k ] );
				}

				document.addEventListener( "DOMContentLoaded",
					function( e )
					{
						cr( "document.DOMContentLoaded" ).n++;
					}
				);
				window.addEventListener( "DOMContentLoaded",
					function( e )
					{
						cr( "window.DOMContentLoaded" ).n++;
					}
				);

				window.addEventListener( "load",
					function( e )
					{
						cr( "window.load" ).n++;
					}
				);

				window.onload =
					function( e )
					{
						cr( "window.onload", arguments.callee.caller.name ).n++;
					}
				;

				jQuery(
					function()
					{
						cr( "jQuery( func... )" ).n++;
					}
				);

				if( parseInt( jQuery.fn.jquery.split( "." )[ 0 ], 10 ) < 3 )
					jQuery( window ).load(
						function( e )
						{
							var o = cr( "jQuery( window ).load()" );

							o.n++;
							if( cr( "jQuery( func... )" ).n < 1 )
								o.err = "too early";
						}
					);
				else
					cr( "jQuery( window ).load()" ).n++;

				var JQCheck = 0;
				jQuery( document ).ready(
					function( $ )
					{
						var o = cr( "jQuery( document ).ready()" );
						o.n++;
						if( !JQCheck )
							o.err = "not async";
					}
				);
				JQCheck = 1;

				jQuery( document ).on( "ready",
					function( $ )
					{
						var o = cr( "jQuery( document ).on( \\"ready\\" )" );
						o.n++;
					}
				);

				setTimeout(
					function()
					{
						var ak =
						[
							"document.DOMContentLoaded",
							"window.DOMContentLoaded",
							"window.load",
							"window.onload",
							"jQuery( func... )",
							"jQuery( window ).load()",
							"jQuery( document ).ready()",
							"jQuery( document ).on( \\"ready\\" )",
						];

						for( var k in ak )
						{
							cr( ak[ k ] );
						}

						for( var k in callsCheck )
						{
							var o = callsCheck[ k ];
							console.log( "seraph_accel: \\"" + k + "\\": " + ( ( o.n == 1 && !o.err ) ? "OK" : ( "ERROR: fired " + o.n + " times" + ( o.err ? ( ", " + o.err ) : "" ) ) ) );
						}
					}
				, 5 * 1000 );
			})();
		' );
		$ctxProcess[ 'ndBody' ] -> appendChild( $item );
	}

	if( $ctxProcess[ 'mode' ] & 1 )
	{
		$xpath = null;
		foreach( Gen::GetArrField( $settCache, array( 'exclConts' ), array() ) as $pattern )
		{
			if( !$xpath )
				$xpath = new \DOMXPath( $doc );

			if( !HtmlNd::FirstOfChildren( @$xpath -> query( $pattern, $doc ) ) )
				continue;

			$skipStatus = 'exclConts:' . $pattern;
			return( $buffer );
		}
		unset( $xpath );
	}

	{
		$xpath = null;
		foreach( Gen::GetArrField( $settContPr, array( 'cln', 'items' ), array() ) as $pattern )
		{
			if( !$xpath )
				$xpath = new \DOMXPath( $doc );

			foreach( HtmlNd::ChildrenAsArr( @$xpath -> query( $pattern, $doc ) ) as $item )
			{
				if( is_a( $item, 'DOMElement' ) )
					$item -> parentNode -> removeChild( $item );
				else if( is_a( $item, 'DOMAttr' ) && $item -> ownerElement )
					$item -> ownerElement -> removeAttributeNode( $item );
			}
		}
		unset( $xpath );
	}

	$ctxProcess[ 'isAMP' ] = $ctxProcess[ 'ndHtml' ] -> hasAttribute( 'amp' );
	$ctxProcess[ 'isRtl' ] = $ctxProcess[ 'ndHtml' ] -> getAttribute( 'dir' ) === 'rtl';

	$ctxProcess[ 'lazyVidCurId' ] = 0;

	$viewId = 'cmn';
	if( $viewsDeviceGrp = GetCacheViewDeviceGrp( $settCache, $ctxProcess[ 'userAgent' ] ) )
		$viewId = ($viewsDeviceGrp[ 'id' ]??null);

	$contGrpRes = ( GetContentProcessorForce( $sett ) !== null  ) ? array() : ContGrpsGet( $contGrpResPagePath, $ctxProcess, Gen::GetArrField( $settContPr, array( 'grps' ), array() ), $doc, $viewId, $grpVariationDataId );

	if( $seraph_accel_g_prepPrms !== null && isset( $seraph_accel_g_prepPrms[ 'lrn' ] ) && !isset( $contGrpRes[ 2 ] ) )
	{
		$skipStatus = 'grpLrnOff';
		return( $buffer );
	}

	if( isset( $contGrpRes[ 1 ] ) )
	{
		$contGrp = $contGrpRes[ 1 ][ 0 ];

		if( !Gen::GetArrField( $contGrp, array( 'contPr', 'enable' ), false ) )
		{

			return( $bufferNoOpt );
		}

		if( Gen::GetArrField( $contGrp, array( 'contPr', 'cssOvr' ), false ) )
			Gen::ArrSet( $settContPr[ 'css' ], Gen::GetArrField( $contGrp, array( 'contPr', 'css' ), array() ) );

		foreach( array( array( 'nonCrit', 'inl' ), array( 'nonCrit', 'int' ), array( 'nonCrit', 'ext' ), array( 'nonCrit', 'excl' ), array( 'nonCrit', 'items' ) ) as $fldId )
		{
			if( Gen::GetArrField( $contGrp, array( 'contPr', 'jsNonCritScopeOvr' ), false ) )
				Gen::SetArrField( $settContPr[ 'js' ], $fldId, Gen::GetArrField( $contGrp, array_merge( array( 'contPr', 'js' ), $fldId ) ) );
			Gen::UnsetArrField( $contGrp, array_merge( array( 'contPr', 'js' ), $fldId ) );
		}

		if( Gen::GetArrField( $contGrp, array( 'contPr', 'jsOvr' ), false ) )
			Gen::ArrSet( $settContPr[ 'js' ], Gen::GetArrField( $contGrp, array( 'contPr', 'js' ), array() ) );
	}

	unset( $bufferNoOpt );

	if( $seraph_accel_g_prepContIsUserCtx )
	{
		if( !Gen::GetArrField( $settCache, array( 'ctxContPr' ), true ) )
			return( $buffer );

		Gen::SetArrField( $settContPr, array( 'css', 'nonCrit', 'auto' ), false );
		Gen::SetArrField( $settContPr, array( 'js', 'optLoad' ), false );
	}

	$settCss = Gen::GetArrField( $settContPr, array( 'css' ), array() );
	$settJs = Gen::GetArrField( $settContPr, array( 'js' ), array() );
	$settCdn = Gen::GetArrField( $settContPr, array( 'cdn' ), array() );
	$settImg = Gen::GetArrField( $settContPr, array( 'img' ), array() );
	$settFrm = Gen::GetArrField( $settContPr, array( 'frm' ), array() );
	$settCp = Gen::GetArrField( $settContPr, array( 'cp' ), array() );

	$jsNotCritsDelayTimeout = ( Gen::GetArrField( $settJs, array( 'optLoad' ), false ) && Gen::GetArrField( $settJs, array( 'nonCrit', 'timeout', 'enable' ), false ) ) ? Gen::GetArrField( $settJs, array( 'nonCrit', 'timeout', 'v' ), 0 ) : null;

	$aFreshItemClassApply = array();

	$ctxProcess[ 'isJsDelayed' ] = false;
	if(

		!($ctxProcess[ 'compatView' ]??null) && !$ctxProcess[ 'isAMP' ] && $jsNotCritsDelayTimeout )
	{
		$ctxProcess[ 'isJsDelayed' ] = true;
	}

	if( $ctxProcess[ 'isJsDelayed' ] )
	{
		$aBodyClasses = array( 'seraph-accel-js-lzl-ing', 'seraph-accel-js-lzl-ing-ani' );
		if( ($settCache[ 'views' ]??null) )
			$aBodyClasses[] = 'seraph-accel-view-' . $viewId;

		HtmlNd::AddRemoveAttrClass( $ctxProcess[ 'ndBody' ], $aBodyClasses );
		unset( $aBodyClasses );
	}

	if( Gen::LastErrDsc_Is() )
	{
		$skipStatus = 'err:prepare';
		return( $buffer );
	}

	$ctxProcess[ 'fragments' ] = array();
	if( $aItemSelector = Gen::GetArrField( $settContPr, array( 'fresh', 'items' ), array() ) )
	{
		$xpath = new \DOMXPath( $doc );

		foreach( $aItemSelector as $sel )
		{
			$bShowAlways = false;
			if( Gen::StrStartsWith( $sel, 'sa:' ) )
			{
				$sel = substr( $sel, 3 );
				$bShowAlways = true;
			}

			$res = $xpath -> query( $sel );
			if( $res && $res -> length )
			{
				$sel = md5( $sel );
				$aFreshItemClassApply[] = $sel;

				foreach( $res as $item )
				{
					$item -> setAttribute( 'data-lzl-fr', $sel );
					if( $bShowAlways )
						HtmlNd::AddRemoveAttrClass( $item, array( 'lzl-fr-sa' ) );
					$ctxProcess[ 'fragments' ][] = $item;
				}
			}
		}

		unset( $xpath );

		unset( $aItemSelector );
	}

	$stage = 'contParts'; if( $seraph_accel_g_prepPrms && !( $resUpd = ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array( 'stage' => $stage ) ) ) ) { $skipStatus = ( $resUpd === null ) ? 'aborted' : 'err:internal'; return( $buffer ); }

	{
		$tmStat = microtime( true );

	}

	if( !ContParts_Process( $ctxProcess, $doc, $settCache, $settCp, $settImg, $settFrm, $settCdn, $jsNotCritsDelayTimeout ) )
	{
		$skipStatus = 'err:' . $stage;
		return( $buffer );
	}

	{
		$tmStat = microtime( true ) - $tmStat;

		$ctxProcess[ 'stat' ][ ucfirst( $stage ) . '-Duration' ] = LocId::Pack( 'TimeDurSec_%1$s', null, array( sprintf( '%.1F', $tmStat ) ) );
	}

	if( $ctxProcess[ 'mode' ] & 1 )
	{
		$settHash = GetContProcSettHash( $settContPr );
		$aSkeletonAggr = null;

		if( GetContentProcessorForce( $sett ) && ($_REQUEST[ 'd' ]??null) == 'learn' )
		{
			$out = Ui::Tag( 'style', 'pre{tab-size:4;}' ) . Ui::Tag( 'h1', 'Self learning information' );

			$contGrpResTmp = ContGrpsGet( $contGrpResPagePath, $ctxProcess, Gen::GetArrField( $settContPr, array( 'grps' ), array() ), $doc, $viewId, $grpVariationDataIdTmp );
			if( isset( $contGrpResTmp[ 2 ] ) )
			{
				$ctxProcessNull = null;

				$contGrpTmp = $contGrpResTmp[ 2 ][ 0 ];

				$sklCssSelExcl = ($contGrpTmp[ 'sklSrch' ]??null) ? ($contGrpTmp[ 'sklCssSelExcl' ]??null) : null;
				$contSkeletonHash = ($contGrpTmp[ 'sklSrch' ]??null) === 'a' ? $grpVariationDataIdTmp : GetContSkeleton( $ctxProcessNull, $ctxProcess[ 'ndBody' ], Gen::GetArrField( $contGrpTmp, array( 'sklExcl' ), array() ), $sklCssSelExcl );

				$out .= Ui::Tag( 'h2', 'ID' ) . Ui::Tag( 'p', $contGrpResTmp[ 2 ][ 1 ] . '/' . $contSkeletonHash );
				$out .= Ui::Tag( 'h2', 'Tree' ) . Ui::Tag( 'pre', GetContSkeleton( $ctxProcessNull, $ctxProcess[ 'ndBody' ], Gen::GetArrField( $contGrpTmp, array( 'sklExcl' ), array() ), $sklCssSelExcl, 'text', ($contGrpTmp[ 'sklSrch' ]??null) === 'a' ) );
			}
			else
				$out .= 'Not defined';

			return( $out );
		}

		if( ( $seraph_accel_g_prepPrms !== null  ) && isset( $contGrpRes[ 2 ] ) )
		{

			$contGrp = $contGrpRes[ 2 ][ 0 ];

			if( ($contGrp[ 'sklSrch' ]??null) )
			{
				$ctxProcess[ 'docSkeleton' ] = new \DOMDocument();
				$ctxProcess[ 'docSkeleton' ] -> registerNodeClass( 'DOMElement', 'seraph_accel\\DomElementEx' );
				$ctxProcess[ 'sklCssSelExcl' ] = ($contGrp[ 'sklCssSelExcl' ]??null);
			}

			if( ($contGrp[ 'sklSrch' ]??null) === 'a' )
			{
				$contSkeletonHash = $grpVariationDataId;
				$aSkeletonAggr = GetContSkeleton( $ctxProcess, $ctxProcess[ 'ndBody' ], Gen::GetArrField( $contGrp, array( 'sklExcl' ), array() ), ($ctxProcess[ 'sklCssSelExcl' ]??null), 'tree', true );

			}
			else
				$contSkeletonHash = GetContSkeleton( $ctxProcess, $ctxProcess[ 'ndBody' ], Gen::GetArrField( $contGrp, array( 'sklExcl' ), array() ), ($ctxProcess[ 'sklCssSelExcl' ]??null), 'hash', false, ($ctxProcess[ 'docSkeleton' ]??null) );

			$ctxProcess[ 'lrnFile' ] = ( $seraph_accel_g_ctxCache ? $seraph_accel_g_ctxCache -> viewPath : ( '' ) ) . '/l/' . $contGrpRes[ 2 ][ 1 ] . '/' . $contSkeletonHash . '.dat.gz';
			$ctxProcess[ 'lrnDataPath' ] = Gen::GetFileDir( $ctxProcess[ 'dataPath' ] ) . '/l';
			$seraph_accel_g_prepLearnId = $contGrpRes[ 2 ][ 1 ] . '/' . hex2bin( $contSkeletonHash );

			$bProcessLearning = false;
			if( isset( $seraph_accel_g_prepPrms[ 'lrn' ] )  )
			{

				$bProcessLearning = true;
			}
			else if( Learn_Init( $ctxProcess, $settHash, $aSkeletonAggr ) )
			{

				if( $aSkeletonAggr !== null )
				{
					$aSkeletonAggr = ( array )($ctxProcess[ 'lrnDsc' ][ 's' ]??null);
					GetContSkeleton_GenNodesFromAgg( ($ctxProcess[ 'docSkeleton' ]??null), $aSkeletonAggr );
				}
			}
			else
			{

				$lrnId = substr( $ctxProcess[ 'lrnFile' ], strlen( GetCacheDir() ) );
				if( $ctxProcess[ 'mode' ] & 4 )
				{
					$tmLearnStart = Learn_IsStarted( $ctxProcess );
					if( $tmLearnStart === false )
					{
					}
					else if( ( time() - $tmLearnStart > 60 ) && !Queue_IsPriorFirst( $ctxProcess[ 'siteId' ], -480 ) )
					{

						if( $aSkeletonAggr === null )
							Learn_Clear( $ctxProcess[ 'lrnFile' ] );
					}
					else
					{
						$skipStatus = 'lrnNeed';
						return( $buffer );
					}

					if( !Learn_Start( $ctxProcess ) )
						$skipStatus = 'err:writeLrnPending';
					else
						$skipStatus = 'lrnNeed:' . $lrnId;

					return( $buffer );
				}

				$ctxProcess[ 'modeReq' ] |= 4;

				$bProcessLearning = true;
				if( $seraph_accel_g_prepPrms )
					$seraph_accel_g_prepPrms[ 'lrn' ] = $lrnId;
			}

			if( $bProcessLearning )
			{

					$ctxProcess[ 'lrn' ] = $seraph_accel_g_prepPrms[ 'lrn' ];
				$ctxProcess[ 'lrnDsc' ] = Learn_ReadDsc( $ctxProcess[ 'lrnFile' ] );

				if( $aSkeletonAggr !== null )
				{
					$aSkeletonAggr = array_merge_recursive( Gen::GetArrField( $ctxProcess[ 'lrnDsc' ], array( 's' ), array() ), $aSkeletonAggr );
					GetContSkeleton_GenNodesFromAgg( ($ctxProcess[ 'docSkeleton' ]??null), $aSkeletonAggr );

					$ctxProcess[ 'lrnDsc' ][ 's' ] = $aSkeletonAggr;
				}
			}

		}

		unset( $contGrpRes );
	}

	if( ( $ctxProcess[ 'mode' ] & 4 ) && Gen::GetArrField( $sett, array( 'test', 'optDelay' ), false ) )
	{

		$timeout = Gen::GetArrField( $sett, array( 'test', 'optDelayTimeout' ), 0 ) / 1000;
		while( $timeout )
		{
			if( ContentProcess_IsAborted() ) { $skipStatus = 'aborted'; return( $buffer ); }

			sleep( 5 );
			$timeout = ( $timeout < 5 ) ? 0 : ( $timeout - 5 );
		}
	}

	if( $ctxProcess[ 'mode' ] & 1 )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.lzl-fr-ing@' ] = true;

		foreach( $ctxProcess[ 'fragments' ] as $item )
		{
			HtmlNd::AddRemoveAttrClass( $item, array( 'lzl-fr-ing' ) );
			if( ($ctxProcess[ 'chunksEnabled' ]??null) )
				ContentMarkSeparate( $item, false );
		}
	}

	$stage = 'images'; if( $seraph_accel_g_prepPrms && !( $resUpd = ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array( 'stage' => $stage ) ) ) ) { $skipStatus = ( $resUpd === null ) ? 'aborted' : 'err:internal'; return( $buffer ); }

	{
		$tmStat = microtime( true );

		$aStatDiff = array();
		$aStatDiff[ 'v' ][] = ( float )Gen::GetArrField( $ctxProcess[ '_stat' ], $aStatDiff[ 'k' ][] = array( 'imgRead', 'v' ) );
		$aStatDiff[ 'v' ][] = ( float )Gen::GetArrField( $ctxProcess[ '_stat' ], $aStatDiff[ 'k' ][] = array( 'ai', 'v' ) );
		foreach( array( 'webp','avif' ) as $comprType )
			$aStatDiff[ 'v' ][] = ( float )Gen::GetArrField( $ctxProcess[ '_stat' ], $aStatDiff[ 'k' ][] = array( 'compr', $comprType, 'v' ) );
	}

	if( !Images_Process( $ctxProcess, $doc, $settCache, $settImg, $settCdn ) )
	{
		$skipStatus = 'err:' . $stage;
		return( $buffer );
	}

	{
		$tmStat = microtime( true ) - $tmStat;

		foreach( $aStatDiff[ 'v' ] as $i => $v )
			$tmStat -= ( float )Gen::GetArrField( $ctxProcess[ '_stat' ], $aStatDiff[ 'k' ][ $i ] ) - $v;
		unset( $aStatDiff );
		if( $tmStat < 0 ) $tmStat = 0;

		$ctxProcess[ 'stat' ][ ucfirst( $stage ) . '-Duration' ] = LocId::Pack( 'TimeDurSec_%1$s', null, array( sprintf( '%.1F', $tmStat ) ) );
		$ctxProcess[ 'stat' ][ $ctxProcess[ '_stat' ][ 'imgRead' ][ 'k' ] = ucfirst( $stage ) . '-Reading-Duration' ] = '';
		$ctxProcess[ 'stat' ][ $ctxProcess[ '_stat' ][ 'imgReadCount' ][ 'k' ] = ucfirst( $stage ) . '-Reading' ] = '';
		$ctxProcess[ 'stat' ][ $ctxProcess[ '_stat' ][ 'ai' ][ 'k' ] = ucfirst( $stage ) . '-Adaptation-Duration' ] = '';
		foreach( array( 'webp','avif' ) as $comprType )
			$ctxProcess[ 'stat' ][ $ctxProcess[ '_stat' ][ 'compr' ][ $comprType ][ 'k' ] = ucfirst( $stage ) . '-Compression-' . ucfirst( $comprType ) . '-Duration' ] = '';
	}

	$stage = 'frames'; if( $seraph_accel_g_prepPrms && !( $resUpd = ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array( 'stage' => $stage ) ) ) ) { $skipStatus = ( $resUpd === null ) ? 'aborted' : 'err:internal'; return( $buffer ); }

	{
		$tmStat = microtime( true );

	}

	if( !Frames_Process( $ctxProcess, $doc, $settCache, $settFrm, $settImg, $settCdn, $settJs ) )
	{
		$skipStatus = 'err:' . $stage;
		return( $buffer );
	}

	{
		$tmStat = microtime( true ) - $tmStat;

		$ctxProcess[ 'stat' ][ ucfirst( $stage ) . '-Duration' ] = LocId::Pack( 'TimeDurSec_%1$s', null, array( sprintf( '%.1F', $tmStat ) ) );
	}

	if( ( $ctxProcess[ 'mode' ] & 1 ) )
	{
		$itemhuddqr = HtmlNd::Parse(
			Ui::Tag( 'a',
				Ui::TagOpen( 'img', array( 'src' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAMAAAAM7l6QAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAJcEhZcwAAFxIAABcSAWef0lIAAABOUExURUdwTAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAANyQSi4AAAAZdFJOUwAU7Y9RHUj2hSgEu6fGceGYMw17PLDWXGe4ORhvAAAAz0lEQVQoz42S2RaDIAxEQUBkEXCX///RYtVY28TTPHJhEmbC2J+lFuHmGNtpqO0PlC5fFYW5wUWXQ/1xIScOkDvtpel7NYYWeCUPOjpxNes8XBj2g+beSEKTZlOuv6c0FXDLFPJF6G9RC+pTvMctmgpsFQuBcDDnQlzmhMONYrLKho7A7x/Da3M5kuFto5HS69s3QTzdoyWkw+FYh0FzJpowOoDbCOTXMowInoF6hMr8JM0E4BqN4Um6LN1BNb4jLD1Iv+WL2bMkY7RrEve3L/wzFnTO5UlaAAAAAElFTkSuQmCC', 'alt' => Wp::GetLocString( 'Seraphinite Accelerator', null, 'seraphinite-accelerator' ), 'style' => array_map( $ctxProcess[ 'isAMP' ] ? function( $v ) { return( $v ); } : function( $v ) { return( '' . $v . '!important' ); }, array( 'display' => 'inline-block', 'vertical-align' => 'top', 'position' => 'absolute', 'width' => 'auto', 'height' => 'auto' ) ) ) ) .
				Ui::Tag( 'span', sprintf( __( 'BannerText_%s', 'seraphinite-accelerator' ), Wp::GetLocString( 'Seraphinite Accelerator', null, 'seraphinite-accelerator' ) ) . Ui::TagOpen( 'br' ) . Ui::Tag( 'span', Wp::GetLocString( 'Turns on site high speed to be attractive for people and search engines.', null, 'seraphinite-accelerator' ), array( 'style' => array_map( $ctxProcess[ 'isAMP' ] ? function( $v ) { return( $v ); } : function( $v ) { return( '' . $v . '!important' ); }, array( 'font-size' => '0.7em' ) ) ) ), array( 'style' => array_map( $ctxProcess[ 'isAMP' ] ? function( $v ) { return( $v ); } : function( $v ) { return( '' . $v . '!important' ); }, array( 'display' => 'inline-block', 'text-align' => 'left', 'vertical-align' => 'top', 'font-size' => '16px', 'padding-left' => '36px' ) ) ) ) .
				( !$ctxProcess[ 'isAMP' ] ? Ui::Tag( 'script', '(function(){var c=document.currentScript.parentNode;setTimeout(function(){var x=new window.XMLHttpRequest();x.onload=function(){if(this.status==200&&this.responseText=="f")c.outerHTML="";};x.open("GET","?seraph_accel_gbnr",true);x.send()},0)})()', array( 'seraph-accel-crit' => '1' ) ) : '' )
			, array( 'href' => Plugin::RmtCfgFld_GetLoc( PluginRmtCfg::Get(), 'Links.FrontendBannerUrl' ), 'target' => '_blank', 'style' => array_map( $ctxProcess[ 'isAMP' ] ? function( $v ) { return( $v ); } : function( $v ) { return( '' . $v . '!important' ); }, array( 'display' => 'block', 'clear' => 'both', 'text-align' => 'center', 'position' => 'relative', 'padding' => '0.5em', 'background-color' => 'transparent', 'color' => '#000', 'line-height' => 1 ) ) ) )
		);
		if( $itemhuddqr && $itemhuddqr -> firstChild )
			if( $item = $doc -> importNode( $itemhuddqr -> firstChild, true ) )
				$ctxProcess[ 'ndBody' ] -> appendChild( $item );
	}

	if( $ctxProcess[ 'mode' ] & 1 )
	{
		$stage = 'styles'; if( $seraph_accel_g_prepPrms && !( $resUpd = ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array( 'stage' => $stage ) ) ) ) { $skipStatus = ( $resUpd === null ) ? 'aborted' : 'err:internal'; return( $buffer ); }

		{
			$tmStat = microtime( true );

			$aStatDiff = array();
			$aStatDiff[ 'v' ][] = ( float )Gen::GetArrField( $ctxProcess[ '_stat' ], $aStatDiff[ 'k' ][] = array( 'imgRead', 'v' ) );
			$aStatDiff[ 'v' ][] = ( float )Gen::GetArrField( $ctxProcess[ '_stat' ], $aStatDiff[ 'k' ][] = array( 'ai', 'v' ) );
			foreach( array( 'webp','avif' ) as $comprType )
				$aStatDiff[ 'v' ][] = ( float )Gen::GetArrField( $ctxProcess[ '_stat' ], $aStatDiff[ 'k' ][] = array( 'compr', $comprType, 'v' ) );
		}

		$lastBodyChild = $ctxProcess[ 'ndBody' ] -> lastChild;

		if( $ctxProcess[ 'isJsDelayed' ] )
		{

			$xpath = new \DOMXPath( $doc );
			$xpath -> registerNamespace( 'php', 'http://php.net/xpath' );
			$xpath -> registerPhpFunctions( array( 'seraph_accel\\_JsClk_XpathExtFunc_ifExistsThenCssSel' ) );

			$ctxProcess[ 'aJsDelay_firstClickDelayExclCssSel' ] = array();

			foreach( array( 'excl' => 'data-lzl-clk-no', 'exclDef' => 'data-lzl-clk-nodef' ) as $settItem => $prop )
			{
				foreach( Gen::GetArrField( $settJs, array( 'clk', $settItem ), array() ) as $e )
				{
					$jsDelay_firstClickDelayExclCssSel = null;
					$items = null;
					$eOrig = $e;

					$exlScope = '*';
					if( preg_match( '@^([a-z,]*):@S', $e, $m ) )
					{
						$m[ 1 ] = array_unique( explode( ',', $m[ 1 ] ) ); sort( $m[ 1 ] );
						$exlScope = ',' . implode( ',', $m[ 1 ] ) . ',';
						$e = substr( $e, strlen( $m[ 0 ] ) );
					}
					unset( $m );

					if( strpos( $e, '/' ) === false )
					{
						if( $settItem == 'excl' )
							$jsDelay_firstClickDelayExclCssSel = $e;
					}
					else
					{
						$e = str_replace( 'ifExistsThenCssSel(', 'php:function("seraph_accel\\_JsClk_XpathExtFunc_ifExistsThenCssSel",', $e );

						$items = @$xpath -> query( $e, $ctxProcess[ 'ndHtml' ] );
						if( !$items )
							continue;

						foreach( $items as $item )
						{
							if( $item instanceof JsClk_ifExistsThenCssSel )
								$jsDelay_firstClickDelayExclCssSel = $item -> cssSel;
							else if( $item instanceof \DOMElement )
							{
								$item -> setAttribute( $prop, $exlScope );
								if( ($ctxProcess[ 'debug' ]??null) )
									$item -> setAttribute( $prop . '-debug-pattern', $eOrig );
							}
						}
					}

					if( is_string( $jsDelay_firstClickDelayExclCssSel ) && strlen( $jsDelay_firstClickDelayExclCssSel ) )
					{
						if( isset( $ctxProcess[ 'aJsDelay_firstClickDelayExclCssSel' ][ $exlScope ] ) )
							$ctxProcess[ 'aJsDelay_firstClickDelayExclCssSel' ][ $exlScope ] .= ',';
						else
							$ctxProcess[ 'aJsDelay_firstClickDelayExclCssSel' ][ $exlScope ] = '';
						$ctxProcess[ 'aJsDelay_firstClickDelayExclCssSel' ][ $exlScope ] .= $jsDelay_firstClickDelayExclCssSel;
					}
				}

				unset( $items, $jsDelay_firstClickDelayExclCssSel );
			}

			unset( $xpath );
		}

		if( $aCustStyles = Gen::GetArrField( $settCss, array( 'custom' ), array() ) )
		{
			foreach( $aCustStyles as $idStyle => $custStyle )
			{
				if( !($custStyle[ 'enable' ]??null) )
					continue;
				if( !($custStyle[ 'noJsDl' ]??null) && !$ctxProcess[ 'isJsDelayed' ] )
					continue;

				$contCustStyles = '';

				$descr = trim( ( string )($custStyle[ 'descr' ]??null) );
				if( strlen( $descr ) )
					$contCustStyles .= "/* " . $descr . " */\n";
				unset( $descr );

				$contCustStyles .= ($custStyle[ 'data' ]??null);

				if( $contCustStyles )
				{
					$item = $doc -> createElement( 'style' );
					if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
						$item -> setAttribute( 'type', 'text/css' );
					$item -> setAttribute( 'id', 'seraph-accel-css-custom-' . $idStyle );
					HtmlNd::SetValFromContent( $item, $contCustStyles );
					unset( $contCustStyles );

					$ctxProcess[ 'ndHead' ] -> appendChild( $item );
				}

				unset( $contCustStyles );
			}

			unset( $aCustStyles, $idStyle, $custStyle );
		}

		$ctxProcess[ 'lazyloadStyles' ] = array();

		if( !Styles_Process( $ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $doc ) )
		{
			$skipStatus = 'err:' . $stage;
			return( $buffer );
		}

		{
			$tmStat = microtime( true ) - $tmStat;

			foreach( $aStatDiff[ 'v' ] as $i => $v )
				$tmStat -= ( float )Gen::GetArrField( $ctxProcess[ '_stat' ], $aStatDiff[ 'k' ][ $i ] ) - $v;
			unset( $aStatDiff );
			if( $tmStat < 0 ) $tmStat = 0;
			$ctxProcess[ 'stat' ][ ucfirst( $stage ) . '-Duration' ] = LocId::Pack( 'TimeDurSec_%1$s', null, array( sprintf( '%.1F', $tmStat ) ) );
		}
	}

	{
		$stage = 'scripts'; if( $seraph_accel_g_prepPrms && !( $resUpd = ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array( 'stage' => $stage ) ) ) ) { $skipStatus = ( $resUpd === null ) ? 'aborted' : 'err:internal'; return( $buffer ); }

		{
			$tmStat = microtime( true );

		}

		if( !Scripts_Process( $ctxProcess, $sett, $settCache, $settContPr, $settJs, $settCdn, $doc ) )
		{
			$skipStatus = 'err:' . $stage;
			return( $buffer );
		}

		if( ContentProcess_IsAborted( $ctxProcess, $settCache ) ) { $skipStatus = 'aborted'; return( $buffer ); }

		{
			$tmStat = microtime( true ) - $tmStat;

			$ctxProcess[ 'stat' ][ ucfirst( $stage ) . '-Duration' ] = LocId::Pack( 'TimeDurSec_%1$s', null, array( sprintf( '%.1F', $tmStat ) ) );
		}
	}

	if( $ctxProcess[ 'mode' ] & 1 )
	{
		if( !$ctxProcess[ 'isAMP' ] )
		{
			$stage = 'lazyCont'; if( $seraph_accel_g_prepPrms && !( $resUpd = ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array( 'stage' => $stage ) ) ) ) { $skipStatus = ( $resUpd === null ) ? 'aborted' : 'err:internal'; return( $buffer ); }

			{
				$tmStat = microtime( true );

			}

			$bLazyCont = LazyCont_Process( $ctxProcess, $sett, $settCache, $settContPr, $doc, $norm, $jsNotCritsDelayTimeout );
			if( $bLazyCont === false )
			{
				$skipStatus = 'err:' . $stage;
				return( $buffer );
			}

			{
				$tmStat = microtime( true ) - $tmStat;

				$ctxProcess[ 'stat' ][ ucfirst( $stage ) . '-Duration' ] = LocId::Pack( 'TimeDurSec_%1$s', null, array( sprintf( '%.1F', $tmStat ) ) );
			}
		}

		HtmlNd::AddRemoveAttrClass( $ctxProcess[ 'ndBody' ], array(), array( 'seraph-accel-js-lzl-ing-ani' ) );

		$stage = 'final'; if( $seraph_accel_g_prepPrms && !( $resUpd = ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array( 'stage' => $stage ) ) ) ) { $skipStatus = ( $resUpd === null ) ? 'aborted' : 'err:internal'; return( $buffer ); }

		{
			$tmStat = microtime( true );

		}

		{

			$cssLzlItems = array();
			foreach( $ctxProcess[ 'lazyloadStyles' ] as $lazyloadStatus => $lazyloadMode )
				$cssLzlItems[ $jsNotCritsDelayTimeout ? $lazyloadMode : '' ][] = 'link[rel=\\"stylesheet/lzl' . ( $lazyloadStatus == 'nonCrit' ? '-nc' : '' ) . '\\"]';

			if( $cssLzlItems )
			{
				$item = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$item -> setAttribute( 'type', 'text/javascript' );
				$item -> setAttribute( 'id', 'seraph-accel-css-lzl' );
				HtmlNd::SetValFromContent( $item, str_replace( array( '_PRM_SEL_NORMAL_', '_PRM_SEL_DELAYED_', '__PRM_LOWPRI_', '__PRM_SYNC_' ), array( '"' . implode( ',', ($cssLzlItems[ '' ]??array()) ) . '"', '"' . implode( ',', ($cssLzlItems[ 'withScripts' ]??array()) ) . '"', $jsNotCritsDelayTimeout ? '1' : '0', ($settCss[ 'bfrJs' ]??null) ? '1' : '0' ), "(function(p,l){function e(b,m,d){b=p.querySelectorAll(b);var n=b.length;n?b.forEach(function(c){function g(){d&&(--n||d())}var a=c.cloneNode();a.rel=\"stylesheet\";if(c.hasAttribute(\"href\")){if(d||m)a.onload&&(a.onloadPrev=a.onload),m&&(a.mediaPrev=a.media?a.media:\"all\",a.media=\"print\"),a.onload=function(){this.mediaPrev&&(this.media=this.mediaPrev,this.mediaPrev=void 0);this.onload=this.onloadPrev;this.onloadPrev=void 0;if(this.onload)try{this.onload()}catch(q){}g()},a.onerror=function(){this.onerror=\nvoid 0;g()};c.parentNode.replaceChild(a,c)}else c.parentNode.replaceChild(a,c),g()}):d&&d()}var h=_PRM_SEL_NORMAL_;if(h.length)if(__PRM_SYNC_){var f=function(){};seraph_accel_izrbpb.add(function(b){if(f)return f=b,!0},4);l(function(){e(h,__PRM_LOWPRI_,function(){f();f=void 0})})}else l(function(){e(h,__PRM_LOWPRI_)});var k=_PRM_SEL_DELAYED_;k.length&&seraph_accel_izrbpb.add(function(b){if(__PRM_SYNC_)return e(k,!1,b),!0;e(k,!1)},4)})(document,setTimeout)" ) );

				$ctxProcess[ 'ndBody' ] -> appendChild( $item );
				ContentMarkSeparate( $item );
			}
		}

		if( ($ctxProcess[ 'lazyload' ]??null) || ($ctxProcess[ 'imgAdaptive' ]??null) )
		{
			{
				$itemInsertBefore = null;
				foreach( $ctxProcess[ 'ndHead' ] -> childNodes as $item )
				{
					if( $item -> nodeName == 'style' || ( $item -> nodeName == 'link' && strpos( $item -> getAttribute( 'rel' ), 'stylesheet' ) === 0 ) )
					{
						$itemInsertBefore = $item;
						break;
					}
				}

				{
					$item = $doc -> createElement( 'style' );
					if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
						$item -> setAttribute( 'type', 'text/css' );
					$item -> nodeValue = htmlspecialchars( '.lzl{display:none!important;}' );

					$itemParentCont = $doc -> createElement( 'noscript' );
					$itemParentCont -> appendChild( $item );

					$ctxProcess[ 'ndHead' ] -> insertBefore( $itemParentCont, $itemInsertBefore );
					$itemInsertBefore = $itemParentCont -> nextSibling;

					ContentMarkSeparate( $itemParentCont );
				}

				{
					$item = $doc -> createElement( 'style' );
					if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
						$item -> setAttribute( 'type', 'text/css' );
					$item -> nodeValue = htmlspecialchars( ( Gen::GetArrField( $settImg, array( 'lazy', 'smoothAppear' ), false ) ? 'img.lzl,img.lzl-ing{opacity:0.01;}img.lzl-ed{transition:opacity .25s ease-in-out;}' : '' ) . ( $bLazyCont ? 'i[data-lzl-nos]{height:10em;display:block}' : '' ) );

					$ctxProcess[ 'ndHead' ] -> insertBefore( $item, $itemInsertBefore );
					$itemInsertBefore = $item -> nextSibling;

					ContentMarkSeparate( $item );
				}

				unset( $itemInsertBefore );
			}

			{
				{

					$cont = '(function(d){var a=d.querySelectorAll("noscript[lzl]");for(var i=0;i<a.length;i++){var c=a[i];c.parentNode.removeChild(c)}})(document)';

					$item = $doc -> createElement( 'script' );
					if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
						$item -> setAttribute( 'type', 'text/javascript' );
					HtmlNd::SetValFromContent( $item, $cont );

					$ctxProcess[ 'ndBody' ] -> appendChild( $item );

					ContentMarkSeparate( $item );
				}

				$cont = 'window.lzl_lazysizesConfig={};';

				if( ($ctxProcess[ 'imgAdaptive' ]??null) )
				{

					$bSepStg = Gen::GetArrField( $settImg, array( 'szAdaptAsync' ), false ) || Gen::GetArrField( $settImg, array( 'szAdaptOnDemand' ), false );
					$cont .= str_replace( array( 'COMPILE_FAKE_CROP_SEL_SYMBOL', 'COMPILE_PROCESS_BJS', 'COMPILE_FAKE_DEVICEPIXELRATIO_MIN' ), array( $bSepStg ? 'c' : '@', ($ctxProcess[ 'imgAdaptiveBjs' ]??null) ? '1' : '0', ( string )( Gen::GetArrField( $settImg, array( 'szAdaptDprMin' ), 100 ) / 100 ) ), "(function(m,n){function t(e){if(!e)return[];for(var h=[],c=[e.szOrig[0],2160,1920,1366,992,768,480,360,120],b=0;b<c.length;b++){var a=c[b];if(!(b&&a>=e.szOrig[0])){if((b||!e.nRenderMinRatio)&&e.cx>a)break;if(b){var d=h,g=\"\"+a,f=e.aDim;f&&-1==f.indexOf(g)||d.push(g)}if(e.nRenderMinRatio||e.cxRenderMin)for(d=1;d<c.length;d++)if(f=c[d],!(f>=a)){if(e.nRenderMinRatio&&e.nRenderMinRatio>f/(a/e.szOrig[0]*e.szOrig[1]))break;if(e.cxRenderMin&&e.cxRenderMin>f)break;g=h;f=\"\"+(b?a:\"O\")+\"COMPILE_FAKE_CROP_SEL_SYMBOL\"+\nf;var k=e.aDim;k&&-1==k.indexOf(f)||g.push(f)}}}return h}function u(e,h=!1){if(h||!e.classList.contains(\"lzl\")&&!e.classList.contains(\"lzl-ing\")){try{var c=JSON.parse(e.getAttribute(\"data-ai-img\"))}catch(l){}if(c){var b=c,a=getComputedStyle(e);var d=[e.clientWidth,e.clientHeight];if(0>=d[0]||0>=d[1])d=void 0;else{if(\"y\"==e.getAttribute(\"data-ai-dpr\")){var g=window.devicePixelRatio;g<COMPILE_FAKE_DEVICEPIXELRATIO_MIN&&(g=COMPILE_FAKE_DEVICEPIXELRATIO_MIN);d[0]*=g;d[1]*=g}var f=b.s;if(f[1]){var k=a.getPropertyValue(\"object-fit\");\nb={szOrig:f};g=null;\"contain\"==k?(k=d[0]/d[1],f=f[0]/f[1],b.cx=f>k?d[0]:Math.round(f*d[1])):\"cover\"==k?(k=d[0]/d[1],f=f[0]/f[1],k>f?b.cx=d[0]:(b.cx=Math.round(f*d[1]),g=\"cover\")):b.cx=d[0];g&&(a=a.getPropertyValue(\"object-position\"),a=a.split(\" \")[0],\"50%\"==a&&\"cover\"==g&&(b.nRenderMinRatio=d[0]/d[1]));d=b}else d=void 0}a=t(d);d=c.O;for(b=a.length;0<b;b--)if(g=a[b-1],c.st){if(-1!=c.d.indexOf(g)){d=c.st.replace(\"_SERAPH_ACCEL_AID_\",g);break}}else if(g=c.d[g]){d=g;break}c=\"src\";h&&e.hasAttribute(\"data-lzl-src\")&&\n(c=\"data-lzl-src\");e.getAttribute(c)!=d&&e.setAttribute(c,d)}}}function v(e){var h=t(function(c){var b,a=[void 0,\"::before\",\"::after\"];for(g in a){var d=getComputedStyle(c,a[g]);if(b=d.getPropertyValue(\"--ai-bg-sz\"))break}if(\"fixed\"==d.getPropertyValue(\"background-attachment\"))a=[window.visualViewport.width,window.visualViewport.height];else{a=[c.clientWidth,c.clientHeight];var g=d.getPropertyValue(\"background-origin\");if(\"content-box\"==g||\"padding-box\"==g)a[0]-=parseInt(d.getPropertyValue(\"border-left-width\"),\n10)+parseInt(d.getPropertyValue(\"border-right-width\"),10),a[1]-=parseInt(d.getPropertyValue(\"border-top-width\"),10)+parseInt(d.getPropertyValue(\"border-bottom-width\"),10);\"content-box\"==g&&(a[0]-=parseInt(d.getPropertyValue(\"padding-left\"),10)+parseInt(d.getPropertyValue(\"padding-right\"),10),a[1]-=parseInt(d.getPropertyValue(\"padding-top\"),10)+parseInt(d.getPropertyValue(\"padding-bottom\"),10))}if(!(0>=a[0]||0>=a[1])){\"y\"==c.getAttribute(\"data-ai-dpr\")&&(c=window.devicePixelRatio,c<COMPILE_FAKE_DEVICEPIXELRATIO_MIN&&\n(c=COMPILE_FAKE_DEVICEPIXELRATIO_MIN),a[0]*=c,a[1]*=c);try{var f=JSON.parse(JSON.parse(b))}catch(q){}if(!f){b=b.split(\" \");if(!b[1])return;f=[[[parseInt(b[0],10),parseInt(b[1],10)]]]}b=d.getPropertyValue(\"background-size\").split(\",\").map(function(q){return q.trim()});d=d.getPropertyValue(\"background-position-x\").split(\",\").map(function(q){return q.trim()});for(var k in f){g=f[k];c=b[k];for(var l in g)return f={},b=g[l],b.s&&(f.aDim=b.d,b=b.s),f.szOrig=b,l=null,\"auto\"==c?(f.cx=b[0],l=\"auto\"):\"contain\"==\nc?(c=a[0]/a[1],b=b[0]/b[1],f.cx=b>c?a[0]:Math.round(b*a[1])):\"cover\"==c?(c=a[0]/a[1],b=b[0]/b[1],c>b?f.cx=a[0]:(f.cx=Math.round(b*a[1]),l=\"cover\")):(c=c.split(\" \")[0],c.lastIndexOf(\"%\")==c.length-1?f.cx=Math.round(parseInt(c,10)/100*a[0]):c.lastIndexOf(\"px\")==c.length-2?f.cx=parseInt(c,10):f.cx=999999999),l&&\"50%\"==d[k]&&(\"auto\"==l?f.cxRenderMin=a[0]:\"cover\"==l&&(f.nRenderMinRatio=a[0]/a[1])),f}}}(e));h.length||h.push(\"O\");h=\"-\"+h.join(\"-\")+\"-\";e.getAttribute(\"data-ai-bg\")!=h&&e.setAttribute(\"data-ai-bg\",\nh)}function p(){r||(r=setTimeout(function(){r=void 0;w()},250))}function w(){var e;for(e=0;e<x.length;e++)u(x[e]);for(e=0;e<y.length;e++)v(y[e])}n.lzl_lazysizesConfig.beforeCheckElem=function(e){e.classList.contains(\"ai-img\")&&u(e,!0);e.classList.contains(\"ai-bg\")&&v(e)};var y=m.getElementsByClassName(\"ai-bg\"),x=m.getElementsByClassName(\"ai-img\"),r;m.addEventListener(\"DOMContentLoaded\",w,!1);n.addEventListener(\"hashchange\",p,!0);n.addEventListener(\"resize\",p,!1);n.MutationObserver?(new n.MutationObserver(p)).observe(m.documentElement,\n{childList:!0,subtree:!0,attributes:!0}):(m.documentElement.addEventListener(\"DOMNodeInserted\",p,!0),m.documentElement.addEventListener(\"DOMAttrModified\",p,!0));COMPILE_PROCESS_BJS&&seraph_accel_izrbpb.add(function(){for(var e=m.querySelectorAll(\".ai-img.ai-bjs\"),h=0;h<e.length;h++){var c=e[h];c.classList.remove(\"ai-img\");try{var b=JSON.parse(c.getAttribute(\"data-ai-img\"))}catch(a){}b&&[\"src\",\"data-lzl-src\"].forEach(function(a){c.hasAttribute(a)&&c.getAttribute(a)!=b.O&&c.setAttribute(a,b.O)})}},\n118)})(document,window);" );
				}

				if( $bLazyCont )
				{

					$cont .= str_replace( array( '_URL_GET_CONTPARTS_' ), array( ContentProcess_GetGetPartUri( $ctxProcess, '{id}.html' ) ), ";(function(g,h,r){function p(a,b,d,c){var f=b.getAttribute(\"data-lzl-nos\");if(f){var e=b.getAttribute(\"data-cp\");if(e)c&&k++,d=new h.XMLHttpRequest,d.open(\"GET\",\"_URL_GET_CONTPARTS_\".replace(\"%7Bid%7D\",e),!0),d.onload=function(){if(200==this.status){try{b.outerHTML=this.responseText}catch(q){}h.lzl_lazySizes.fire(a,\"seraph_accel_lzlNosLoaded\",{},!1,!0)}c&&(k--,!k&&l&&(l(),l=void 0))},d.send();else{if(e=b.getAttribute(\"data-c\"))e=decodeURIComponent(e);else if(f=b.parentNode.querySelector('noscript[data-lzl-nos=\"'+\nf+'\"]'))e=f.textContent,f.parentNode.removeChild(f);b.outerHTML=e;d&&(d.fire(a,\"seraph_accel_lzlNosLoaded\",{},!1,!0),d.fire(h,\"resize\",{},!1,!0))}}}function m(a,b,d){if(void 0!==b){if(\"string\"!==typeof b)return;var c=b.indexOf(\"#\");if(-1==c)return;c=b.substr(c+1)}if(void 0===c||!a.querySelector('[id=\"'+c+'\"]')){b=h.lzl_lazySizes;for(var f=a.querySelectorAll(\"i\"+(d?\".bjs\":\"\")+\"[data-lzl-nos]\"),e=0;e<f.length;e++){var q=f[e];q.classList.remove(\"lzl\");p(a,q,b,d);if(void 0!==c&&a.querySelector('[id=\"'+\nc+'\"]'))break}}}function t(a){m(g,location.href);g.removeEventListener(r,t,{capture:!0,passive:!0});u=!0;setTimeout(function(){var b=h.lzl_lazySizes,d;for(d in n){var c=n[d];p(c.ownerDocument,c,b,c.classList.contains(\"bjs\"))}n=void 0},0)}var k=0,l,u,n=[];h.lzl_lazysizesConfig.beforeUnveil=function(a,b){u?p(a.ownerDocument,a,b,a.classList.contains(\"bjs\")):a.getAttribute(\"data-lzl-nos\")&&n.push(a)};g.addEventListener(r,t,{capture:!0,passive:!0});g.addEventListener(\"click\",function(a){m(g,a.target.getAttribute(\"href\"))},\n{capture:!0,passive:!0});g.addEventListener(\"keydown\",function(a){(70==a.keyCode&&(a.ctrlKey||a.metaKey)||191==a.keyCode)&&m(g)},{capture:!0,passive:!0});seraph_accel_izrbpb.add(function(a){m(g,void 0,!0);if(k)return l=a,!0},5)})(document,window,\"DOMContentLoaded\");" );
				}

				$cont .=
					@file_get_contents( __DIR__ . '/Cmn/Ext/JS/lazysizes/lazysizes' . $ctxProcess[ 'jsMinSuffix' ] . '.js' ) .
					@file_get_contents( __DIR__ . '/Cmn/Ext/JS/lazysizes/plugins/unveilhooks/ls.unveilhooks' . $ctxProcess[ 'jsMinSuffix' ] . '.js' ) .
					'';

				if( ($ctxProcess[ 'lazyloadBjs' ]??null) )
				{

					$cont .= "(function(e){seraph_accel_izrbpb.add(function(){for(var d=e.querySelectorAll(\".bjs[data-lzl-src]:is(.lzl,.lzl-ing):not(.lzl-ed),.bjs[data-lzl-v-src]\"),b,c=0;c<d.length;c++){var a=d[c];a.classList.add(\"lzl-ed\");a.classList.remove(\"lzl\");(b=a.getAttribute(\"data-lzl-v-src\"))?(a.setAttribute(\"allow\",String(a.getAttribute(\"allow\")).replace(\"autoplay\",\"\")),a.setAttribute(\"src\",b),a.removeAttribute(\"data-lzl-v-src\"),a.removeAttribute(\"data-lzl-src\")):(b=a.getAttribute(\"data-lzl-src\"))&&a.setAttribute(\"src\",\nb);(b=a.getAttribute(\"data-lzl-srcset\"))&&a.setAttribute(\"srcset\",b);(b=a.getAttribute(\"data-lzl-sizes\"))&&a.setAttribute(\"sizes\",b)}},120)})(document);";
				}

				$item = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$item -> setAttribute( 'type', 'text/javascript' );
				$item -> setAttribute( 'id', 'seraph-accel-lzl' );

				HtmlNd::SetValFromContent( $item, $cont );

				$ctxProcess[ 'ndHead' ] -> appendChild( $item );

				ContentMarkSeparate( $item );
				unset( $cont );
			}
		}

		if( ($ctxProcess[ 'lazyVid' ]??null) )
		{

			$item = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$item -> setAttribute( 'type', 'text/javascript' );
			$item -> setAttribute( 'id', 'seraph-accel-lzl-v' );
			$item -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $item, "(function(d,f){function g(a,b){(function(c){if(d.YT)c();else{var e=f.createElement(\"script\");e.type=\"text/javascript\";e.src=\"https://www.youtube.com/iframe_api\";e.onload=c;f.head.appendChild(e)}})(function(){d.YT.ready(function(){new d.YT.Player(a,{events:{onReady:function(c){c.target.playVideo()}}});b&&\"string\"==typeof a.src&&(a.src=a.src.replace(\"autoplay=0\",\"autoplay=1\"))})})}d.addEventListener(\"message\",function(a){if(\"string\"==typeof a.data){a=a.data.split(\":\");var b=a[1];a=a[0];if(\"seraph-accel-lzl-v\"==\na&&(a=f.querySelectorAll('iframe[lzl-v][data-id=\"'+b+'\"]'),a.length)){b=0;if(1<a.length){for(;b<a.length;b++){var c=a[b];if(c.offsetWidth||c.offsetHeight||c.getClientRects().length)break}if(b==a.length)return}a=a[b];a.src=a.getAttribute(\"data-lzl-v-src\");a.removeAttribute(\"data-lzl-v-src\");a.removeAttribute(\"data-lzl-src\");\"youtube\"==a.getAttribute(\"data-lzl-v-svc\")&&g(a)}}},!1);d.seraph_accel_youTubeFeedPlayVideo=function(a){a.setAttribute(\"onclick\",\"return false\")}})(window,document)" );
			$ctxProcess[ 'ndBody' ] -> appendChild( $item );

			ContentMarkSeparate( $item, false );
		}

		if( ($settContPr[ 'earlyPaint' ]??null) && !($ctxProcess[ 'compatView' ]??null) && !$ctxProcess[ 'isAMP' ] )
		{

			{
				$item = $doc -> createElement( 'img' );

				$item -> setAttribute( 'style', Ui::GetStyleAttr( array( 'z-index' => -99999, 'position' => 'fixed', 'top' => 0, 'left' => 0, 'margin' => '1px', 'max-width' => 'none!important', 'max-height' => 'none!important', 'width' => '100vw!important', 'height' => '100vh!important' ) ) );
				$item -> setAttribute( 'onload', 'var i=this,d=document;function c(e){d.removeEventListener(e.type,c);setTimeout(function(){i.parentNode.removeChild(i)},250)}d.addEventListener("DOMContentLoaded",c)' );
				$item -> setAttribute( 'src',
					LazyLoad_SrcSubst( $ctxProcess, array( 'cx' => 1000, 'cy' => 1000 ), Gen::GetArrField( $settImg, array( 'lazy', 'plchRast' ), true ) )

				);
				$item -> setAttribute( 'alt', '...' );

				HtmlNd::InsertChild( $ctxProcess[ 'ndBody' ], 0, $item );

			}

		}

		if( ( ($settCache[ 'cron' ]??null) && CacheDoesCronDelayPageLoad() && !$ctxProcess[ 'isAMP' ] )  )
		{
			$urlCron = $ctxProcess[ 'siteRootUri' ] . $ctxProcess[ 'wpRootSubPath' ] . 'wp-cron.php';
			if( $ctxProcess[ 'isAMP' ] )
				$urlCron = $ctxProcess[ 'siteDomainUrl' ] . $urlCron;

			$cont = 'setTimeout(function(){var x=new window.XMLHttpRequest();x.open("GET","' . $urlCron . '",true);x.send()},0)';

			if( $ctxProcess[ 'isAMP' ] )
			{
				$itemAmpScriptMjsTpl = null;
				$itemAmpScriptJsTpl = null;
				$itemAmpMetaScriptSrc = null;

				foreach( $ctxProcess[ 'ndHead' ] -> childNodes as $item )
				{
					if( $item -> nodeType != XML_ELEMENT_NODE )
						continue;

					if( $item -> nodeName == 'script' )
					{
						$m = array();
						if( preg_match( '@//cdn\\.ampproject\\.org/v\\d+/([a-z-]+)-(?:[\\d+\\.]+|latest)\\.(m?)js@', $item -> getAttribute( 'src' ), $m, PREG_OFFSET_CAPTURE ) )
						{
							if( $m[ 2 ][ 0 ] === 'm' )
							{
								if( $itemAmpScriptMjsTpl !== false )
									$itemAmpScriptMjsTpl = ( $m[ 1 ][ 0 ] === 'amp-script' ) ? false : array( 'item' => $item, 'm' => $m );
							}
							else
							{
								if( $itemAmpScriptJsTpl !== false )
									$itemAmpScriptJsTpl = ( $m[ 1 ][ 0 ] === 'amp-script' ) ? false : array( 'item' => $item, 'm' => $m );
							}
						}
					}

					if( !$itemAmpMetaScriptSrc && $item -> nodeName == 'meta' && $item -> getAttribute( 'name' ) == 'amp-script-src' )
						$itemAmpMetaScriptSrc = $item;
				}

				foreach( array( $itemAmpScriptMjsTpl, $itemAmpScriptJsTpl ) as $itemAmpScriptTpl )
				{
					if( !$itemAmpScriptTpl )
						continue;

					$item = $itemAmpScriptTpl[ 'item' ] -> cloneNode( true );
					$item -> setAttribute( 'custom-element', 'amp-script' );
					$src = $item -> getAttribute( 'src' );
					$item -> setAttribute( 'src', substr_replace( $src, 'amp-script', $itemAmpScriptTpl[ 'm' ][ 1 ][ 1 ], strlen( $itemAmpScriptTpl[ 'm' ][ 1 ][ 0 ] ) ) );

					$ctxProcess[ 'ndHead' ] -> appendChild( $item );
				}

				if( !$itemAmpMetaScriptSrc )
				{
					$itemAmpMetaScriptSrc = $doc -> createElement( 'meta' );
					$itemAmpMetaScriptSrc -> setAttribute( 'name', 'amp-script-src' );
					$ctxProcess[ 'ndHead' ] -> appendChild( $itemAmpMetaScriptSrc );
				}

				if( function_exists( 'hash' ) )
					$itemAmpMetaScriptSrc -> setAttribute( 'content', $itemAmpMetaScriptSrc -> getAttribute( 'content' ) . ' sha384-' . str_replace( array( '=', '+', '/' ), array( '', '-', '_' ), base64_encode( hash( 'sha384', $cont, true ) ) ) );

				$item = HtmlNd::Parse( Ui::Tag( 'amp-script', null, array( 'script' => 'seraph-accel-cron', 'layout' => 'fixed', 'height' => '1', 'width' => '1', 'style' => array( 'position' => 'fixed', 'top' => '0', 'left' => '0', 'visibility' => 'hidden' ) ) ) );
				if( $item && $item -> firstChild && ( $item = $doc -> importNode( $item -> firstChild, true ) ) )
					$ctxProcess[ 'ndBody' ] -> appendChild( $item );
			}

			$item = $doc -> createElement( 'script' );
			$item -> setAttribute( 'id', 'seraph-accel-cron' );

			if( $ctxProcess[ 'isAMP' ] )
			{
				$item -> setAttribute( 'type', 'text/plain' );
				$item -> setAttribute( 'target', 'amp-script' );
			}
			else
			{
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$item -> setAttribute( 'type', 'text/javascript' );
			}

			$item -> nodeValue = htmlspecialchars( $cont );
			$ctxProcess[ 'ndBody' ] -> appendChild( $item );
		}

		if( $aFreshItemClassApply )
		{
			if( $ctxProcess[ 'isAMP' ] )
			{

			}
			else
			{
				$item = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$item -> setAttribute( 'type', 'text/javascript' );
				$item -> setAttribute( 'id', 'seraph-accel-freshParts' );

				HtmlNd::SetValFromContent( $item, str_replace( array( '_URL_GET_FRESH_', '_ARRAY_SELECTORS_' ), array( ContentProcess_GetCurRelatedUri( $ctxProcess, array( 'seraph_accel_gf' => '{tm}' ) ), implode( ',', array_map( function( $v ) { return( '"' . $v . '"' ); }, $aFreshItemClassApply ) ) ), "(function(b,l,h){function g(){e&&([_ARRAY_SELECTORS_].forEach(function(a){a='[data-lzl-fr=\"'+a+'\"]';var c=b.querySelectorAll(a+\".lzl-fr-ing\");a=e.querySelectorAll(a+\":not(.lzl-fr-ed)\");for(var d=0;d<c.length;d++)d<a.length&&(c[d].innerHTML=a[d].innerHTML,a[d].classList.add(\"lzl-fr-ed\")),c[d].classList.remove(\"lzl-fr-ing\")}),e.querySelectorAll(\"[data-lzl-fr]:not(.lzl-fr-ed)\").length||(b.removeEventListener(\"seraph_accel_lzlNosLoaded\",g,{capture:!0,passive:!0}),e=void 0))}var f=new l.XMLHttpRequest,\nk=function(){},e;seraph_accel_izrbpb.add(function(a){if(f)return k=a,!0},5);b.addEventListener(\"seraph_accel_lzlNosLoaded\",g,{capture:!0,passive:!0});f.open(\"GET\",\"_URL_GET_FRESH_\".replace(\"%7Btm%7D\",\"\"+Date.now()),!0);f.setRequestHeader(\"Accept\",\"text/html\");f.onload=function(){function a(c=!0){c&&b.removeEventListener(h,a);g();f=void 0;c=b.createEvent(\"Events\");c.initEvent(\"seraph_accel_freshPartsDone\",!0,!1);b.dispatchEvent(c);k()}e=b.implementation.createHTMLDocument(\"\");200==this.status&&(e.documentElement.innerHTML=\nthis.responseText);\"loading\"!=b.readyState?a(!1):b.addEventListener(h,a,!1)};f.send()})(document,window,\"DOMContentLoaded\")" ) );
				$ctxProcess[ 'ndHead' ] -> insertBefore( $item, $ctxProcess[ 'ndHead' ] -> firstChild );
				ContentMarkSeparate( $item );
			}

			{
				$item = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$item -> setAttribute( 'type', 'text/css' );
				$item -> nodeValue = htmlspecialchars( '[data-lzl-fr].lzl-fr-ing:not(.lzl-fr-sa) *{opacity:0;visibility:hidden;}' . ( Gen::GetArrField( $settContPr, array( 'fresh', 'smoothAppear' ), false ) ? '[data-lzl-fr]:not(.lzl-fr-sa, .lzl-fr-ing) *{transition:opacity .25s ease-in-out;}' : '' ) );

				$ctxProcess[ 'ndHead' ] -> appendChild( $item );
				ContentMarkSeparate( $item );
			}
		}

		if( $ctxProcess[ 'isJsDelayed' ] && Gen::GetArrField( $settJs, array( 'prvntDblInit' ), false ) )
		{

			$item = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$item -> setAttribute( 'type', 'text/javascript' );
			$item -> setAttribute( 'id', 'seraph-accel-prvntDblInit' );
			HtmlNd::SetValFromContent( $item, "(function(d,e,k){function f(a){a.seraph_accellzl_el_f=a.addEventListener;a.seraph_accellzl_el_a=[];a.addEventListener=function(c,b,g){a.seraph_accellzl_el_f&&(-1!=k.indexOf(c)&&a.seraph_accellzl_el_a.push({t:c,l:b,o:g}),a.seraph_accellzl_el_f(c,b,g))}}function h(a){for(var c in a.seraph_accellzl_el_a){var b=a.seraph_accellzl_el_a[c];a.removeEventListener(b.t,b.l,b.o)}a.addEventListener=a.seraph_accellzl_el_f;delete a.seraph_accellzl_el_f;delete a.seraph_accellzl_el_a}f(d);f(e);seraph_accel_izrbpb.add(function(){h(e);\nh(d)},5)})(document,window,[\"DOMContentLoaded\",\"load\"])" );
			$ctxProcess[ 'ndHead' ] -> insertBefore( $item, $ctxProcess[ 'ndHead' ] -> firstChild );
		}

		if( !$ctxProcess[ 'isAMP' ] )
		{

			$item = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$item -> setAttribute( 'type', 'text/javascript' );
			HtmlNd::SetValFromContent( $item, "document.seraph_accel_usbpb=document.createElement;seraph_accel_izrbpb={add:function(b,a=10){void 0===this.a[a]&&(this.a[a]=[]);this.a[a].push(b)},a:{}}" );
			$ctxProcess[ 'ndHead' ] -> insertBefore( $item, $ctxProcess[ 'ndHead' ] -> firstChild );
		}

		if( ($ctxProcess[ 'jsDelay' ]??null) )
			Scripts_ProcessAddRtn( $ctxProcess, $sett, $settCache, $settContPr, $settJs, $settCdn, $doc, $ctxProcess[ 'jsDelay' ] );
	}

	{
		$xpath = new \DOMXPath( $doc );
		foreach( array( HtmlNd::FirstOfChildren( $xpath -> query( './meta[@http-equiv="Content-Type"]', $ctxProcess[ 'ndHead' ] ) ), HtmlNd::FirstOfChildren( $xpath -> query( './meta[@charset]', $ctxProcess[ 'ndHead' ] ) ) ) as $item )
			if( $item && $item !== $ctxProcess[ 'ndHead' ] -> firstChild )
				$ctxProcess[ 'ndHead' ] -> insertBefore( $item, $ctxProcess[ 'ndHead' ] -> firstChild );
		unset( $xpath );
	}

	if( ($ctxProcess[ 'chunksEnabled' ]??null) )
	{
		$settChunks = Gen::GetArrField( $settCache, array( 'chunks' ), array() );

		$xpath = new \DOMXPath( $doc );

		foreach( Gen::GetArrField( $settChunks, array( 'seps' ), array() ) as $sep )
		{
			if( !($sep[ 'enable' ]??null) )
				continue;

			$xpathQ = ($sep[ 'sel' ]??null);
			foreach( HtmlNd::ChildrenAsArr( $xpath -> query( $xpathQ, $ctxProcess[ 'ndHtml' ] ) ) as $item )
				ContentMarkSeparate( $item, false, $sep[ 'side' ] );
		}
	}

	if( ContentProcess_IsAborted( $ctxProcess, $settCache ) ) { $skipStatus = 'aborted'; return( $buffer ); }

	if( $ctxProcess[ 'mode' ] & 256 )
	{

		HtmlNd::ClearAllAttrs( $ctxProcess[ 'ndHtml' ] );
		HtmlNd::ClearAllAttrs( $ctxProcess[ 'ndHead' ] );
		HtmlNd::ClearAllAttrs( $ctxProcess[ 'ndBody' ] );

		foreach( HtmlNd::ChildrenAsArr( $ctxProcess[ 'ndHead' ] -> childNodes ) as $item )
		{
			if( $item -> nodeType == XML_ELEMENT_NODE && $item -> nodeName == 'meta' && $item -> hasAttribute( 'http-equiv' ) )
				continue;

			$ctxProcess[ 'ndHead' ] -> removeChild( $item );
		}

		$itemLast = $ctxProcess[ 'ndBody' ] -> lastChild;
		foreach( $ctxProcess[ 'fragments' ] as $item )
		{
			$ctxProcess[ 'ndBody' ] -> appendChild( $item );
			if( ($ctxProcess[ 'chunksEnabled' ]??null) )
				ContentMarkSeparate( $item, false );
		}

		foreach( HtmlNd::ChildrenAsArr( $ctxProcess[ 'ndBody' ] -> childNodes ) as $item )
		{
			$ctxProcess[ 'ndBody' ] -> removeChild( $item );
		  	if( $item === $itemLast )
				break;
		}

		foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'script' ) ) as $item )
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item, function( $itemFragment, $item ) { $type = $item -> getAttribute( 'type' ); return( $type == 'o/js-lzl' || $type == 'o/js-lzls' ); } ) )
				$item -> parentNode -> removeChild( $item );
		foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'noscript' ) ) as $item )
			$item -> parentNode -> removeChild( $item );

		foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'style' ) ) as $item )
		    $item -> parentNode -> removeChild( $item );
		foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'link' ) ) as $item )
			if( $item -> getAttribute( 'rel' ) == 'stylesheet' )
				$item -> parentNode -> removeChild( $item );
	}

	global $seraph_accel_g_cacheObjChildren;
	global $seraph_accel_g_cacheObjSubs;
	$seraph_accel_g_cacheObjChildren = DepsExpand( $ctxProcess[ 'deps' ], false );
	$seraph_accel_g_cacheObjSubs = $ctxProcess[ 'subs' ];

	$buffer = HtmlDocDeParse( $doc, $norm );

	if( $ctxProcess[ 'mode' ] & 256 )
		$buffer = ContentDisableIndexingEx( $buffer );

	{
		$tmStat = microtime( true ) - $tmStat;

		$ctxProcess[ 'stat' ][ ucfirst( $stage ) . '-Duration' ] = LocId::Pack( 'TimeDurSec_%1$s', null, array( sprintf( '%.1F', $tmStat ) ) );
	}

	{
		$ctxProcess[ 'stat' ][ $ctxProcess[ '_stat' ][ 'imgRead' ][ 'k' ] ] = LocId::Pack( 'TimeDurSec_%1$s', null, array( sprintf( '%.1F', $ctxProcess[ '_stat' ][ 'imgRead' ][ 'v' ] ) ) );
		$ctxProcess[ 'stat' ][ $ctxProcess[ '_stat' ][ 'imgReadCount' ][ 'k' ] ] = $ctxProcess[ '_stat' ][ 'imgReadCount' ][ 'v' ];
		if( isset( $ctxProcess[ '_stat' ][ 'ai' ][ 'v' ] ) )
			$ctxProcess[ 'stat' ][ $ctxProcess[ '_stat' ][ 'ai' ][ 'k' ] ] = LocId::Pack( 'TimeDurSec_%1$s', null, array( sprintf( '%.1F', $ctxProcess[ '_stat' ][ 'ai' ][ 'v' ] ) ) );
		else
			unset( $ctxProcess[ 'stat' ][ $ctxProcess[ '_stat' ][ 'ai' ][ 'k' ] ] );
		foreach( array( 'webp','avif' ) as $comprType )
		{
			if( isset( $ctxProcess[ '_stat' ][ 'compr' ][ $comprType ][ 'v' ] ) )
				$ctxProcess[ 'stat' ][ $ctxProcess[ '_stat' ][ 'compr' ][ $comprType ][ 'k' ] ] = LocId::Pack( 'TimeDurSec_%1$s', null, array( sprintf( '%.1F', $ctxProcess[ '_stat' ][ 'compr' ][ $comprType ][ 'v' ] ) ) );
			else
				unset( $ctxProcess[ 'stat' ][ $ctxProcess[ '_stat' ][ 'compr' ][ $comprType ][ 'k' ] ] );
		}

		unset( $ctxProcess[ '_stat' ] );
	}

	if( ( $ctxProcess[ 'mode' ] & 4 ) && isset( $ctxProcess[ 'lrn' ] ) && !Learn_Finish( $ctxProcess, $settHash, $ctxProcess[ 'lrn' ] ) )
	{
		$skipStatus = 'err:writeLrnDone';
		return( $buffer );
	}

	return( $buffer );
}

function ContentProcess_Replace( $ctxProcess, $settCache, $settContPr, &$buffer )
{
	$ctxAbort = new AnyObj();
	$ctxAbort -> ctxProcess = $ctxProcess;
	$ctxAbort -> settCache = $settCache;
	$ctxAbort -> cb =
		function( $ctxAbort )
		{
			return( ContentProcess_IsAborted( $ctxAbort -> ctxProcess, $ctxAbort -> settCache ) );
		};

	foreach( Gen::GetArrField( $settContPr, array( 'rpl', 'items' ), array() ) as $rpl )
	{
		if( !($rpl[ 'enable' ]??null) )
			continue;

		if( ContentProcess_ReplaceEx( ( string )($rpl[ 'expr' ]??null), ( string )($rpl[ 'data' ]??null), $buffer, array( $ctxAbort, 'cb' ) ) === false )
			return;
	}
}

function ContentProcess_ReplaceEx( $expr, $dataTpl, &$buffer, $cbIsAborted = null )
{
	$ctx = new AnyObj();
	$ctx -> cbReplTpl =
		function( $ctx, $m )
		{

			return( ( string )($ctx -> m[ $m[ 1 ] ][ 0 ]??null) );
		};

	$expr = ExprConditionsSet_Parse( $expr );
	if( !count( $expr ) )
		return( null );

	if( !ExprConditionsSet_IsRegExp( $expr ) && ExprConditionsSet_IsTrivial( $expr ) )
	{
		$n = 0;
		$buffer = str_replace( $expr[ 0 ][ 'expr' ], $dataTpl, $buffer, $n );
		return( $n > 0 ? true : null );
	}

	$exprLast = array_splice( $expr, count( $expr ) - 1, 1 )[ 0 ][ 'expr' ];

	if( count( $expr ) )
	{
		$ctxMatch = new AnyObj();
		$ctxMatch -> buffer = $buffer;
		$ctxMatch -> cbMatch =
			function( $ctxMatch, $expr )
			{
				if( IsStrRegExp( $expr ) )
					return( @preg_match( $expr, $ctxMatch -> buffer ) );
				return( false );
			};
		if( !ExprConditionsSet_MatchEx( $expr, array( $ctxMatch, 'cbMatch' ) ) )
		{
			unset( $ctxMatch );
			return( null );
		}
		unset( $ctxMatch );
	}

	$adjusted = null;

	$iPos = 0;
	$ctx -> m = array();
	while( @preg_match( $exprLast, $buffer, $ctx -> m, PREG_OFFSET_CAPTURE, $iPos ) )
	{

		$posRepl = $ctx -> m[ 0 ];

		$bSkip = true;
		foreach( $ctx -> m as $key => $d )
		{
			if( $bSkip )
			{
				$bSkip = false;
				continue;
			}

			if( !is_int( $key ) )
			{
				$bSkip = true;
				continue;
			}

			$posRepl = $d;
		}

		$data = preg_replace_callback( '@\\${(\\w+)}@', array( $ctx, 'cbReplTpl' ), $dataTpl );

		$buffer = substr_replace( $buffer, $data, $posRepl[ 1 ], strlen( $posRepl[ 0 ] ) );
		if( count( $ctx -> m ) == 1 )
			$iPos = $posRepl[ 1 ] + strlen( $data );
		else
			$iPos = $ctx -> m[ 0 ][ 1 ] + strlen( $ctx -> m[ 0 ][ 0 ] ) - strlen( $posRepl[ 0 ] ) + strlen( $data );

		$adjusted = true;

		if( $cbIsAborted && call_user_func( $cbIsAborted ) )
			return( false );
	}

	return( $adjusted );
}

function ContNoScriptItemClear( $itemNoScript )
{

	foreach( HtmlNd::ChildrenAsArr( $itemNoScript -> getElementsByTagName( 'noscript' ) ) as $itemCheck )
	{
		if( $itemNoScript === $itemCheck )
			continue;

		if( $itemCheck -> hasAttribute( 'data-lzl-bjs' ) )
			HtmlNd::MoveChildren( $itemCheck -> parentNode, $itemCheck, $itemCheck );

		$itemCheck -> parentNode -> removeChild( $itemCheck );
	}
}

function _GetContSkeletonHash_MatchEx( $v, &$aPattern )
{
	if( @is_a( $v, 'DOMNode' ) )
		$v = $v -> nodeValue;

	if( is_string( $v ) )
	{
		foreach( $aPattern as $pattern )
			if( @preg_match( $pattern, $v ) )
				return( true );
	}

	return( false );
}

function _GetContSkeletonHash_Match( $v )
{
	$aPattern = func_get_args();
	array_shift( $aPattern );

	if( is_array( $v ) )
	{
		foreach( $v as $vi )
			if( _GetContSkeletonHash_MatchEx( $vi, $aPattern ) )
				return( true );
	}
	else if( _GetContSkeletonHash_MatchEx( $v, $aPattern ) )
		return( true );

	return( null );
}

function _GetContSkeletonHash_ExclMatchAll( $v )
{
	if( is_array( $v ) )
		return( new ContSkeletonHash_MatchAll( $v, false, func_get_args() ) );

	if( is_string( $v ) )
	{
		$aPattern = func_get_args();
		array_shift( $aPattern );

		foreach( $aPattern as $pattern )
			if( @preg_match( $pattern, $v ) )
				return( true );
	}

	return( null );
}

function _GetContSkeletonHash_ExclMatchAllGlob( $v )
{
	if( is_array( $v ) )
		return( new ContSkeletonHash_MatchAll( $v, true, func_get_args() ) );

	return( null );
}

function ContSkeleton_FltName_PrepPatterns( $patterns )
{
	static $g_aPlchldr = null;

	if( !$g_aPlchldr )
		$g_aPlchldr = array(
			'ENUM_POSTTYPES_NOTBUILTINVIEWABLESPEC' =>
				function()
				{
					return( implode( '|', array_keys( array_filter( get_post_types( array(), 'objects' ), function( $o ) { return( !($o -> _builtin??false) && ($o -> show_in_nav_menus??false) && is_post_type_viewable( $o -> name ) ); } ) ) ) );
				},

			'ENUM_TAXONOMIES_NOTBUILTIN' =>
				function()
				{
					return( implode( '|', array_keys( array_filter( get_taxonomies( array(), 'objects' ), function( $o ) { return( !($o -> _builtin??false) ); } ) ) ) );
				},

			'POST_SLUG' =>
				function()
				{
					if( $oPost = get_post() )
						return( $oPost -> post_name );
					return( '' );
				},
		);

	$res = array();
	foreach( $patterns as $pattern )
	{
		$patternPrms = array( 'r' => '\\*' );
		if( preg_match( '@^([\\w,=\\*\\\\]+):[^:]@', $pattern, $m ) )
		{
			$patternPrms = array_merge( $patternPrms, Gen::ParseProps( $m[ 1 ], ',', '=' ) );
			$pattern = substr( $pattern, strlen( $m[ 1 ] ) + 1 );
		}

		foreach( $g_aPlchldr as $id => &$v )
		{
			$plchldr = '(?\'' . $id . '\')';

			if( strpos( $pattern, $plchldr ) === false )
				continue;

			if( !is_string( $v ) )
				$v = ( string )$v();

			$pattern = str_replace( '|' . $plchldr, strlen( $v ) ? ( '|' . $v ) : '', $pattern );
			$pattern = str_replace( $plchldr, $v, $pattern );
		}
		unset( $id, $cb, $plchldr, $v );

		$pattern .= 'S';

		$res[] = array( 'm' => $pattern, 'p' => $patternPrms );
	}

	return( $res );
}

function ContSkeleton_FltName( $patterns, $s, $spaceAround = false )
{
	foreach( $patterns as $pattern )
	{
		if( $spaceAround && strlen( $s ) )
		{
			if( $s[ 0 ] !== ' ' )
				$s = ' ' . $s;
			if( $s[ strlen( $s ) - 1 ] !== ' ' )
				$s = $s . ' ';
		}

		for( $i = 0; $i < 1000; $i++ )
		{
			if( !@preg_match_all( $pattern[ 'm' ], $s, $am, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) )
				break;

			for( $i = count( $am ); $i > 0; $i-- )
			{
				$m = $am[ $i - 1 ];

				$j = count( $m );
				$jmin = ( $j > 1 ) ? 1 : 0;

				for( ; $j > $jmin; $j-- )
				{
					$mj = $m[ $j - 1 ];
					$s = substr_replace( $s, $pattern[ 'p' ]['r' ], $mj[ 1 ], strlen( $mj[ 0 ] ) );
				}
			}
		}
	}

	return( $s );
}

function _GetContSkeleton_MaskSelector( $v, $bMask = true, $bInt = false )
{

	static $g_aMaskedInt = array( "\x01", "\x02", '\\@', '\\*', '\\:', '\\[', '\\]', '\\+', '\\&' );
	static $g_aMasked = array( '\\.', '\\#', '\\@', '\\*', '\\:', '\\[', '\\]', '\\+', '\\&' );
	static $g_aUnMasked = array( '.', '#', '@', '*', ':', '[', ']', '+', '&' );

	if( $bMask )
		return( str_replace( $g_aUnMasked, $g_aMasked, $v ) );
	return( str_replace( $bInt ? $g_aMaskedInt : $g_aMasked, $g_aUnMasked, $v ) );
}

function _GetContSkeletonHash_GetAttrs( &$aCssCrit, $item, $aExcl )
{
	$contItemTpl = $item -> nodeName;

	if( $item -> attributes )
	{
		foreach( array( 'class', 'id' ) as $attrName )
		{
			$attr = $item -> attributes -> getNamedItem( $attrName );
			if( !$attr )
				continue;

			if( in_array( $attr, $aExcl[ 'a' ], true ) )
			{
				_GetContSkeletonHash_AddCssCrit( $aCssCrit, $item, false, array( $attrName ) );
				continue;
			}

			$v = $attr -> nodeValue;
			if( $attr -> nodeName == 'class' )
				$v = ' ' . implode( ' ', Ui::ParseClassAttr( $v ) ) . ' ';

			$aPattern = array();
			foreach( $aExcl[ 'as' ] as $exclAttrStr )
				if( isset( $exclAttrStr -> attr ) ? ( $attr -> nodeName == $exclAttrStr -> attr ) : in_array( $attr, $exclAttrStr -> aAttr, true ) )
					$aPattern = array_merge( $aPattern, $exclAttrStr -> aPattern );

			if( $aPattern )
				$v = ContSkeleton_FltName( ContSkeleton_FltName_PrepPatterns( $aPattern ), $v, $attr -> nodeName == 'class' );

			switch( $attr -> nodeName )
			{
			case 'class':
				$v = explode( ' ', $v );
				foreach( $v as $vItem )
				{
					$vItem = trim( $vItem );
					if( strlen( $vItem ) )
						$contItemTpl .= '.' . _GetContSkeleton_MaskSelector( $vItem );
				}
				break;

			case 'id':
				$v = trim( $v );
				if( strlen( $v ) )
					$contItemTpl .= '#' . _GetContSkeleton_MaskSelector( $v );
				break;
			}
		}
	}

	$contItemTpl = trim( ContSkeleton_FltName( $aExcl[ 'sel' ], $contItemTpl, true ) );

	$contItemTplTag = _GetContSkeletonHash_GetAttrsParts( $contItemTpl, $contItemTplClasses, $contItemTplId );
	if( $contItemTplClasses )
	{
		$contItemTplClasses = array_unique( $contItemTplClasses );
		sort( $contItemTplClasses );
		$contItemTpl = $contItemTplTag . '.' . implode( '.', _GetContSkeleton_MaskSelector( $contItemTplClasses ) ) . ( $contItemTplId ? ( '#' . implode( '#', _GetContSkeleton_MaskSelector( $contItemTplId ) ) ) : '' );
	}

	return( $contItemTpl );
}

function _GetContSkeletonHash_GetAttrsParts( $contItemTpl, &$classes, &$ids )
{
	$contItemTpl = str_replace( array( '\\.', '\\#' ), array( "\x01", "\x02" ), $contItemTpl );

	$posClasses = strpos( $contItemTpl, '.' );
	$posId = strpos( $contItemTpl, '#' );

	$classes = ( $posClasses !== false ) ? ( $posId !== false ? substr( $contItemTpl, $posClasses, $posId - $posClasses ) : substr( $contItemTpl, $posClasses ) ) : '';
	$classes = strlen( $classes ) ? _GetContSkeleton_MaskSelector( explode( '.', substr( $classes, 1 ) ), false, true ) : array();

	$ids = ( $posId !== false ) ? substr( $contItemTpl, $posId ) : '';
	$ids = strlen( $ids ) ? _GetContSkeleton_MaskSelector( explode( '#', substr( $ids, 1 ) ), false, true ) : array();

	return( _GetContSkeleton_MaskSelector( ( $posClasses !== false ) ? substr( $contItemTpl, 0, $posClasses ) : ( $posId !== false ? substr( $contItemTpl, 0, $posId ) : $contItemTpl ), false, true ) );
}

function _GetContSkeletonHash_AddCssCrit( &$aCssCrit, $item, $bRecurse = true, $aAttrIncl = array() )
{
	if( !$aAttrIncl )
		$aCssCrit[ "@(?:[^\\w\\-\\#\\.]|^)" . preg_quote( $item -> nodeName, '@' ) . "(?:[^\\-\\w]|$)@" ] = true;

	if( !$aAttrIncl || in_array( 'id', $aAttrIncl ) )
		if( strlen( $v = trim( ( string )$item -> getAttribute( 'id' ) ) ) )
			$aCssCrit[ "@#" . preg_quote( _GetContSkeleton_MaskSelector( $v ), '@' ) . "(?:[^\\-\\w]|$)@" ] = true;

	if( !$aAttrIncl || in_array( 'class', $aAttrIncl ) )
		foreach( Ui::ParseClassAttr( ( string )$item -> getAttribute( 'class' ) ) as $v )
			$aCssCrit[ "@\\." . preg_quote( _GetContSkeleton_MaskSelector( $v ), '@' ) . "(?:[^\\-\\w]|$)@" ] = true;

	if( !$bRecurse )
		return;

	foreach( $item -> childNodes as $itemChild )
		if( $itemChild -> nodeType == XML_ELEMENT_NODE )
			_GetContSkeletonHash_AddCssCrit( $aCssCrit, $itemChild );
}

function _GetContSkeletonHash_Enum( &$aCssCrit, &$aParentUniqueItems, $itemParent, $aExcl, $bAgg = false )
{
	if( !$itemParent -> childNodes )
		return;

	foreach( $itemParent -> childNodes as $item )
	{
		if( $item -> nodeType != XML_ELEMENT_NODE )
			continue;

		if( in_array( $item -> nodeName, $aExcl[ 'n' ], true ) || in_array( $item, $aExcl[ 'e' ], true ) )
		{
			_GetContSkeletonHash_AddCssCrit( $aCssCrit, $item );
			continue;
		}

		$aUniqueItems = array();

			_GetContSkeletonHash_Enum( $aCssCrit, $aUniqueItems, $item, $aExcl, $bAgg );

		$contItemTpl = _GetContSkeletonHash_GetAttrs( $aCssCrit, $item, $aExcl );
		if( !strlen( $contItemTpl ) )
		{
			$aParentUniqueItems = array_merge_recursive( $aParentUniqueItems, $aUniqueItems );
			continue;
		}

		if( $bAgg )
		{
			$contItemTpl = _GetContSkeletonHash_GetAttrsParts( $contItemTpl, $contItemTplClasses, $contItemTplIds );

			$aUniqueItems = $aUniqueItems ? array( 'n' => $aUniqueItems ) : array();
			if( $contItemTplClasses )
				$aUniqueItems[ 'c' ] = array_combine( $contItemTplClasses, array_fill( 0, count( $contItemTplClasses ), array() ) );
			if( $contItemTplIds )
				$aUniqueItems[ 'i' ] = array_combine( $contItemTplIds, array_fill( 0, count( $contItemTplIds ), array() ) );
		}

		$aParentUniqueItems = array_merge_recursive( $aParentUniqueItems, array( $contItemTpl => $aUniqueItems ) );
	}
}

function _GetContSkeletonHash_EnumUniqueItems( &$contTpl, $docTpl, $itemParentTpl, &$aParentUniqueItems, $mode, $bAgg, $level = 0 )
{
	ksort( $aParentUniqueItems );

	foreach( $aParentUniqueItems as $contItemTpl => &$aUniqueItems )
	{
		if( $bAgg )
		{
			ksort( $aUniqueItems );

			if( ($aUniqueItems[ 'c' ]??null) )
			{
				ksort( $aUniqueItems[ 'c' ] );
				if( $mode == 'text' )
					$contItemTpl .= '.' . implode( '.', _GetContSkeleton_MaskSelector( array_keys( $aUniqueItems[ 'c' ] ) ) );
			}

			if( ($aUniqueItems[ 'i' ]??null) )
			{
				ksort( $aUniqueItems[ 'i' ] );
				if( $mode == 'text' )
					$contItemTpl .= '#' . implode( '#', _GetContSkeleton_MaskSelector( array_keys( $aUniqueItems[ 'i' ] ) ) );
			}
		}

		$itemTpl = null;
		if( $docTpl )
		{
			$itemTpl = $docTpl -> createElement( _GetContSkeletonHash_GetAttrsParts( $contItemTpl, $contItemTplClasses, $contItemTplId ) );
			if( $contItemTplClasses )
				$itemTpl -> setAttribute( 'class', ' ' . implode( ' ', $contItemTplClasses ) . ' ' );
			if( $contItemTplId )
				$itemTpl -> setAttribute( 'id', implode( '', $contItemTplId ) );
			$itemParentTpl -> appendChild( $itemTpl );
		}

		if( $mode != 'tree' )
		{
			if( $level )
				$contTpl .= $mode == 'text' ? str_repeat( "\t", $level ) : ( string )$level;
			$contTpl .= $contItemTpl;
			if( $mode == 'text' )
				$contTpl .= "\n";
		}

		if( $bAgg && !isset( $aUniqueItems[ 'n' ] ) )
			continue;

		if( $bAgg )
			_GetContSkeletonHash_EnumUniqueItems( $contTpl, $docTpl, $itemTpl, $aUniqueItems[ 'n' ], $mode, $bAgg, $level + 1 );
		else
			_GetContSkeletonHash_EnumUniqueItems( $contTpl, $docTpl, $itemTpl, $aUniqueItems, $mode, $bAgg, $level + 1 );
	}
}

function GetContSkeleton_GenNodesFromAgg( $docTpl, &$aParentUniqueItems )
{
	_GetContSkeleton_GenNodesFromAgg( $docTpl, $docTpl, $aParentUniqueItems );
}

function _GetContSkeleton_GenNodesFromAgg( $docTpl, $itemParentTpl, &$aParentUniqueItems )
{
	ksort( $aParentUniqueItems );

	foreach( $aParentUniqueItems as $contItemTpl => &$aUniqueItems )
	{
		ksort( $aUniqueItems );
		if( ($aUniqueItems[ 'c' ]??null) )
			ksort( $aUniqueItems[ 'c' ] );
		if( ($aUniqueItems[ 'i' ]??null) )
			ksort( $aUniqueItems[ 'i' ] );

		$contItemTplIds = ($aUniqueItems[ 'i' ]??null);
		if( !$contItemTplIds )
			$contItemTplIds = array( null );
		else
			$contItemTplIds = array_keys( $contItemTplIds );

		$classAttr = '';
		if( ($aUniqueItems[ 'c' ]??null) )
			$classAttr = ' ' . implode( ' ', array_keys( $aUniqueItems[ 'c' ] ) ) . ' ';

		foreach( $contItemTplIds as $contItemTplId )
		{
			$itemTpl = $docTpl -> createElement( $contItemTpl );

			if( strlen( $classAttr ) )
				$itemTpl -> setAttribute( 'class', $classAttr );
			if( $contItemTplId !== null )
				$itemTpl -> setAttribute( 'id', $contItemTplId );
			$itemParentTpl -> appendChild( $itemTpl );

			if( isset( $aUniqueItems[ 'n' ] ) )
				_GetContSkeleton_GenNodesFromAgg( $docTpl, $itemTpl, $aUniqueItems[ 'n' ] );
		}
	}
}

function GetContSkeleton( &$ctxProcess, $ndBody, $excls, $exclsCssSel, $mode = 'hash', $bAgg = false, $docTpl = null )
{
	$aExcl = array( 'n' => array(), 'e' => array(), 'a' => array(), 'as' => array(), 'sel' => ContSkeleton_FltName_PrepPatterns( is_array( $exclsCssSel ) ? $exclsCssSel : array() ) );
	{
		$xpath = new \DOMXPath( $ndBody -> ownerDocument );
		$xpath -> registerNamespace( 'php', 'http://php.net/xpath' );
		$xpath -> registerPhpFunctions( array( 'seraph_accel\\_GetContSkeletonHash_Match', 'seraph_accel\\_GetContSkeletonHash_ExclMatchAll', 'seraph_accel\\_GetContSkeletonHash_ExclMatchAllGlob' ) );

		foreach( $excls as $exclItemPath )
		{
			if( @preg_match( '@^\\.//([\\w\\-]+)$@', $exclItemPath, $m ) )
			{
				$aExcl[ 'n' ][] = $m[ 1 ];
				continue;
			}

			$exclItemPath = preg_replace( '@matchAll\\(\\s*\\.\\/\\/\\*\\[\\@([\\w]+)\\]/\\@([\\w]+)@', 'php:function("seraph_accel\\_GetContSkeletonHash_ExclMatchAllGlob",.//*[@${1}][1]/@${2}', $exclItemPath );
			$exclItemPath = str_replace( 'matchAll(', 'php:function("seraph_accel\\_GetContSkeletonHash_ExclMatchAll",', $exclItemPath );
			$exclItemPath = str_replace( 'match(', 'php:function("seraph_accel\\_GetContSkeletonHash_Match",', $exclItemPath );

			$items = @$xpath -> query( $exclItemPath, $ndBody -> parentNode -> parentNode );
			if( !$items )
				continue;

			foreach( $items as $item )
			{
				if( is_a( $item, 'seraph_accel\\ContSkeletonHash_MatchAll' ) )
					$aExcl[ 'as' ][] = $item;
				else if( is_a( $item, 'DOMElement' ) )
					$aExcl[ 'e' ][] = $item;
				else if( is_a( $item, 'DOMAttr' ) )
					$aExcl[ 'a' ][] = $item;
			}
		}

		unset( $xpath );
	}

	$aUniqueItems = array();

	if( $ctxProcess !== null )
		$aCssCrit = &$ctxProcess[ 'aCssCrit' ];
	else
		$aCssCrit = array();

	_GetContSkeletonHash_Enum( $aCssCrit, $aUniqueItems, $ndBody -> parentNode -> parentNode, $aExcl, $bAgg );

	$contTpl = '';
	_GetContSkeletonHash_EnumUniqueItems( $contTpl, $docTpl, $docTpl, $aUniqueItems, $mode, $bAgg );

	return( $mode == 'text' ? $contTpl : ( $mode == 'tree' ? $aUniqueItems : md5( str_replace( '\\*', '*', $contTpl ) ) ) );
}

function GetContProcSettHash( $settContPr )
{
	$settContPr = Gen::ArrCopy( $settContPr );

	foreach( Gen::GetArrField( $settContPr, array( 'cp' ), array() ) as $k => $item )
		if( !$item )
			unset( $settContPr[ 'cp' ][ $k ] );

	foreach( Gen::GetArrField( $settContPr, array( 'css', 'custom' ), array() ) as $k => $item )
		if( !($item[ 'enable' ]??null) )
			unset( $settContPr[ 'css' ][ 'custom' ][ $k ] );

	foreach( Gen::GetArrField( $settContPr, array( 'cdn', 'items' ), array() ) as $k => $item )
		if( !($item[ 'enable' ]??null) )
			unset( $settContPr[ 'cdn' ][ 'items' ][ $k ] );

	foreach( Gen::GetArrField( $settContPr, array( 'grps', 'items' ), array() ) as $k => $item )
		if( !($item[ 'enable' ]??null) )
			unset( $settContPr[ 'grps' ][ 'items' ][ $k ] );

	return( md5( @json_encode( $settContPr ), true ) );
}

function Learn_Id2File( $id )
{
	$pos = strpos( $id, '/' );
	if( $pos === false )
		return( null );

	$pos += 1;
	return( substr( $id, 0, $pos ) . bin2hex( substr( $id, $pos ) ) . '.dat.gz' );
}

function Learn_ReadDsc( $lrnFile )
{
	return( Tof_GetFileData( Gen::GetFileDir( $lrnFile ), Gen::GetFileName( $lrnFile ), array( 2,
		function( $data, $vFrom )
		{
			return( $data );
		}
	), true ) );
}

function Learn_KeepNeededData( &$datasDel, &$lrnsGlobDel, $lrnDsc, $lrnDataPath )
{
	ScriptsOpt::keepLrnNeededData( $datasDel, $lrnsGlobDel, $lrnDsc, $lrnDataPath );
	StyleProcessor::keepLrnNeededData( $datasDel, $lrnsGlobDel, $lrnDsc, $lrnDataPath );
}

function Learn_Init( &$ctxProcess, $settHash, $aSkeletonAggr = null )
{
	$ctxProcess[ 'lrnDsc' ] = Learn_ReadDsc( $ctxProcess[ 'lrnFile' ] );
	if( !$ctxProcess[ 'lrnDsc' ] )
		return( false );

	if( Gen::GetArrField( $ctxProcess[ 'lrnDsc' ], array( 'sh' ) ) !== $settHash )
	{
		unset( $ctxProcess[ 'lrnDsc' ] );
		@unlink( $ctxProcess[ 'lrnFile' ] );

		return( false );
	}

	$bOK = true;

	if( $aSkeletonAggr !== null )
	{
		$aSkeletonAggrLrn = Gen::GetArrField( $ctxProcess[ 'lrnDsc' ], array( 's' ), array() );
		if( !Gen::ArrContainRecursive( $aSkeletonAggrLrn, $aSkeletonAggr ) )
			$bOK = false;
	}

	return( $bOK );
}

function Learn_IsStarted( &$ctxProcess )
{
	return( Gen::FileMTime( $ctxProcess[ 'lrnFile' ] . '.p' ) );
}

function Learn_Start( &$ctxProcess )
{
	Gen::MakeDir( Gen::GetFileDir( $ctxProcess[ 'lrnFile' ] ), true );
	return( @file_put_contents( $ctxProcess[ 'lrnFile' ] . '.p', '' ) !== false );
}

function Learn_Finish( &$ctxProcess, $settHash, $lrnFileInitiate = null )
{
	if( !isset( $ctxProcess[ 'lrnDsc' ] ) )
		$ctxProcess[ 'lrnDsc' ] = array();

	$ctxProcess[ 'lrnDsc' ][ 'sh' ] = $settHash;

	$ok = Gen::HrSucc( @Tof_SetFileData( Gen::GetFileDir( $ctxProcess[ 'lrnFile' ] ), Gen::GetFileName( $ctxProcess[ 'lrnFile' ] ), $ctxProcess[ 'lrnDsc' ], 2, false, true ) );
	@unlink( $ctxProcess[ 'lrnFile' ] . '.p' );
	if( $lrnFileInitiate )
		@unlink( GetCacheDir() . '/' . $lrnFileInitiate . '.p' );
	return( $ok );
}

function Learn_Clear( $lrnFile )
{
	@unlink( $lrnFile );
	@unlink( $lrnFile . '.p' );
}

function ContUpdateItemIntegrity( $item, $cont )
{
	if( $cont === false )
		return;

	$integrity = trim( $item -> getAttribute( 'integrity' ) );
	if( !$integrity )
		return;

	$algo = strpos( $integrity, '-' );
	if( $algo === false )
		return;
	$algo = substr( $integrity, 0, $algo );

	$hashNew = function_exists( 'hash' ) ? hash( $algo, $cont, true ) : null;
	if( $hashNew )
		$item -> setAttribute( 'integrity', $algo . '-' . base64_encode( $hashNew ) );
	else
		$item -> removeAttribute( 'integrity' );
}

function GetSrcAttrInfoEx( $src )
{
	return( array( 'url' => $src, 'srcWoArgs' => $src, 'args' => array() ) );
}

function GetSrcAttrInfo( $ctxProcess, $requestDomainUrl, $requestUriPath, &$src )
{
	$src = trim( $src );

	if( Ui::IsSrcAttrData( $src ) )
		return( GetSrcAttrInfoEx( $src ) );

	$urlComps = Net::UrlParse( $src, Net::URLPARSE_F_PRESERVEEMPTIES | Net::URLPARSE_F_PATH_FIXFIRSTSLASH );
	if( !$urlComps )
		return( GetSrcAttrInfoEx( $src ) );

	$args = Net::UrlParseQuery( ($urlComps[ 'query' ]??null) );

	$serverArgs = $ctxProcess[ 'serverArgs' ];

	if( isset( $urlComps[ 'host' ] ) )
	{
		if( isset( $urlComps[ 'scheme' ] ) )
		{
			$srcUrlFullness = 4;
			if( $urlComps[ 'scheme' ] != ($serverArgs[ 'REQUEST_SCHEME' ]??null) && ($serverArgs[ 'REQUEST_SCHEME' ]??null) == 'https' )
				$urlComps[ 'scheme' ] = ($serverArgs[ 'REQUEST_SCHEME' ]??null);
		}
		else
		{
			$srcUrlFullness = 3;
			$urlComps[ 'scheme' ] = ($serverArgs[ 'REQUEST_SCHEME' ]??null);
		}
	}
	else
	{
		$srcUrlFullness = 2;

		$requestDomainUrlComps = $requestDomainUrl ? Net::UrlParse( $requestDomainUrl ) : null;
		if( !$requestDomainUrlComps )
		{
			$requestDomainUrlComps = array( 'scheme' => ($serverArgs[ 'REQUEST_SCHEME' ]??null), 'host' => $ctxProcess[ 'host' ] );
			if( ($serverArgs[ 'SERVER_PORT' ]??null) != 80 && ($serverArgs[ 'SERVER_PORT' ]??null) != 443 )
				$requestDomainUrlComps[ 'port' ] = ($serverArgs[ 'SERVER_PORT' ]??null);
		}

		$urlComps[ 'scheme' ] = ($requestDomainUrlComps[ 'scheme' ]??null);
		$urlComps[ 'host' ] = ($requestDomainUrlComps[ 'host' ]??null);
		$urlComps[ 'port' ] = ($requestDomainUrlComps[ 'port' ]??null);

		unset( $requestDomainUrlComps );

		if( ($urlComps[ 'path' ][ 0 ]??null) !== '/' )
		{
			if( $requestUriPath === null )
				$requestUriPath = $ctxProcess[ 'requestUriPath' ];
			$urlComps[ 'path' ] = $requestUriPath . '/' . $urlComps[ 'path' ];
		}
	}

	if( $urlComps[ 'host' ] != $ctxProcess[ 'host' ] || ( isset( $urlComps[ 'port' ] ) && $urlComps[ 'port' ] != ($serverArgs[ 'SERVER_PORT' ]??null) ) )
	{
		$src = Net::UrlDeParse( $urlComps, Net::URLPARSE_F_PRESERVEEMPTIES );
		return( array( 'url' => $src, 'srcWoArgs' => Net::UrlDeParse( $urlComps, Net::URLPARSE_F_PRESERVEEMPTIES, array( PHP_URL_QUERY, PHP_URL_FRAGMENT ) ), 'args' => $args, '#' => ($urlComps[ 'fragment' ]??null), 'ext' => true ) );
	}

	if( stripos( ($urlComps[ 'path' ]??null) . '/', $ctxProcess[ 'siteRootUri' ] . '/' ) !== 0 )
	{
		$src = Net::UrlDeParse( $urlComps, Net::URLPARSE_F_PRESERVEEMPTIES );
		return( array( 'url' => $src, 'srcWoArgs' => Net::UrlDeParse( $urlComps, Net::URLPARSE_F_PRESERVEEMPTIES, array( PHP_URL_QUERY, PHP_URL_FRAGMENT ) ), 'args' => $args, '#' => ($urlComps[ 'fragment' ]??null), 'ext' => true ) );
	}

	if( isset( $urlComps[ 'path' ] ) )
		$urlComps[ 'path' ] = VirtUriPath2Real( $urlComps[ 'path' ], $ctxProcess[ 'aVPth' ] );

	$res = array( 'url' => Net::UrlDeParse( $urlComps, Net::URLPARSE_F_PRESERVEEMPTIES ), 'srcWoArgs' => Net::UrlDeParse( $urlComps, Net::URLPARSE_F_PRESERVEEMPTIES, array( PHP_URL_SCHEME, PHP_URL_USER, PHP_URL_PASS, PHP_URL_HOST, PHP_URL_PORT, PHP_URL_QUERY, PHP_URL_FRAGMENT ) ), 'args' => $args, '#' => ($urlComps[ 'fragment' ]??null), 'srcUrlFullness' => $srcUrlFullness );
	$src = Net::UrlDeParse( $urlComps, Net::URLPARSE_F_PRESERVEEMPTIES, array( PHP_URL_SCHEME, PHP_URL_USER, PHP_URL_PASS, PHP_URL_HOST, PHP_URL_PORT ) );

	$srcRelFile = substr( ($urlComps[ 'path' ]??null), strlen( $ctxProcess[ 'siteRootUri' ] ) );
	if( $srcRelFile )
	{
		if( Gen::StrStartsWith( $srcRelFile, '/' . Gen::GetFileName( $ctxProcess[ 'siteContPath' ] ) . '/' ) )
			$res[ 'filePathRoot' ] = Gen::GetFileDir( $ctxProcess[ 'siteContPath' ] );
		else
			$res[ 'filePathRoot' ] = $ctxProcess[ 'siteRootPath' ];
		$res[ 'filePath' ] = $res[ 'filePathRoot' ] . rawurldecode( $srcRelFile );
	}

	return( $res );
}

function IsUrlInPartsList( $items, $url )
{
	if( !$url || !$items )
		return( false );

	$url = strtolower( $url );

	foreach( $items as $item )
		if( strpos( $url, $item ) !== false )
			return( true );

	return( false );
}

function IsObjInRegexpList( $list, array $scopes, &$detectedPattern = null )
{
	if( !($scopes[ 'src' ]??null) && !($scopes[ 'id' ]??null) && !($scopes[ 'body' ]??null) )
		return( false );

	foreach( $list as $item )
	{
		$isMatched = true;
		foreach( ExprConditionsSet_Parse( $item ) as $itemE )
		{
			$itemScope = array( 'src', 'id', 'body' );
			$posScopeEnd = strpos( $itemE[ 'expr' ], ':' );
			if( $posScopeEnd !== false )
			{
				$posExpBegin = false;
				foreach( array( '/', '~', '@', ';', '%', '`', '#' ) as $expQuote )
				{
					$posExpBegin2 = strpos( $itemE[ 'expr' ], $expQuote );
					if( $posExpBegin2 !== false && ( $posExpBegin === false || $posExpBegin2 < $posExpBegin ) )
						$posExpBegin = $posExpBegin2;
				}

				if( $posExpBegin !== false && $posScopeEnd < $posExpBegin )
				{
					$itemScope = explode( ',', substr( $itemE[ 'expr' ], 0, $posScopeEnd ) );
					$itemE[ 'expr' ] = substr( $itemE[ 'expr' ], $posScopeEnd + 1 );
				}
			}

			$match = false;
			foreach( $itemScope as $scopeCheck )
			{
				if( !($scopes[ $scopeCheck ]??null) )
					continue;

				$m = array();
				if( ExprConditionsSet_IsItemOpFullSearch( $itemE ) )
				{
					if( !@preg_match_all( $itemE[ 'expr' ], $scopes[ $scopeCheck ], $m, PREG_SET_ORDER ) )
						$m = array( array( '' ) );
				}
				else
				{
					if( !@preg_match( $itemE[ 'expr' ], $scopes[ $scopeCheck ], $m ) )
						$m = array( '' );
					$m = array( $m );
				}

				foreach( $m as $mi )
				{
					if( count( $mi ) > 1 )
						array_shift( $mi );
					$mi = implode( '', $mi );

					if( ExprConditionsSet_ItemOp( $itemE, $mi ) )
					{
						$match = true;
						break;
					}
				}

				if( $match )
					break;
			}

			if( !$match )
			{
				$isMatched = false;
				break;
			}
		}

		if( $isMatched )
		{
			$detectedPattern = $item;
			return( true );
		}
	}

	return( false );
}

function GetObjSrcCritStatus( $settNonCrit, $critSpec, $specs, $srcInfo, $src, $id, $body = null, &$detectedPattern = null )
{
	if( !IsObjSrcNotCrit( $settNonCrit, $srcInfo, $src, $id, $body, $detectedPattern ) )
		return( true );
	if( $critSpec && IsObjInRegexpList( $critSpec, array( 'src' => $src, 'id' => $id, 'body' => $body ), $detectedPattern ) )
		return( 'critSpec' );
	if( $specs && IsObjInRegexpList( $specs, array( 'src' => $src, 'id' => $id, 'body' => $body ), $detectedPattern ) )
		return( null );
	return( false );
}

function IsObjSrcNotCrit( $settNonCrit, $srcInfo, $src, $id, $body = null, &$detectedPattern = null )
{
	if( $srcInfo )
	{
		if( !($settNonCrit[ 'ext' ]??null) && ($srcInfo[ 'ext' ]??null) )
			return( false );
		if( !($settNonCrit[ 'int' ]??null) )
			return( false );
	}
	else if( !($settNonCrit[ 'inl' ]??null) )
		return( false );

	$inList = IsObjInRegexpList( Gen::GetArrField( $settNonCrit, array( 'items' ), array() ), array( 'src' => $src, 'id' => $id, 'body' => $body ), $detectedPattern );
	return( ($settNonCrit[ 'excl' ]??null) ? !$inList : $inList );
}

function UpdSc( &$ctxProcess, $settCache, $type, $cont, &$src = null, &$filePath = null )
{
	$fileExt = null;
	if( is_array( $type ) )
	{
		$fileExt = ($type[ 1 ]??null);
		$type = ($type[ 0 ]??null);
	}

	$chunk = CacheCw( $settCache, $ctxProcess[ 'siteRootDataPath' ], $ctxProcess[ 'dataPath' ], false, $cont, $type, $fileExt );
	if( !$chunk )
		return( false );

	DepsAdd( $ctxProcess[ 'deps' ], $type, $chunk[ 'id' ] );

	$src = $ctxProcess[ 'siteRootUri' ] . '/' . $chunk[ 'relFilePath' ];
	$filePath = $ctxProcess[ 'siteRootDataPath' ] . '/' . $chunk[ 'relFilePath' ];
	return( $chunk[ 'id' ] );
}

function ReadSc( $ctxProcess, $settCache, $id, $type )
{
	return( ReadSce( $ctxProcess[ 'dataPath' ], $settCache, $id, $type ) );
}

function CheckSc( &$ctxProcess, $settCache, $type, $oiCi, &$src = null, &$filePath = null )
{
	$fileExt = null;
	if( is_array( $type ) )
	{
		$fileExt = ($type[ 1 ]??null);
		$type = ($type[ 0 ]??null);
	}

	$chunk = CacheCc( $settCache, $ctxProcess[ 'siteRootDataPath' ], $ctxProcess[ 'dataPath' ], $oiCi, $type, $fileExt );
	if( !$chunk )
		return( false );

	DepsAdd( $ctxProcess[ 'deps' ], $type, $chunk[ 'id' ] );

	$src = $ctxProcess[ 'siteRootUri' ] . '/' . $chunk[ 'relFilePath' ];
	$filePath = $ctxProcess[ 'siteRootDataPath' ] . '/' . $chunk[ 'relFilePath' ];
	return( true );
}

function ContentParseStrIntEncodingCorrect()
{
	if( !function_exists( 'mb_strlen' ) || !( ( int )@ini_get( 'mbstring.func_overload' ) & 2 ) )
		return( null );

	$mbIntEnc = mb_internal_encoding();
	mb_internal_encoding( '8bit' );
	return( $mbIntEnc );
}

function ContentParseStrIntEncodingRestore( $mbIntEnc )
{
	if( $mbIntEnc !== null )
		mb_internal_encoding( $mbIntEnc );
}

function GetContentTestData( $size )
{
	$extra = '';

	$n = $size / 32;
	for( $i = 0; $i < $n; $i++ )
		$extra .= md5( '' . $i );

	return( $extra );
}

function GetContentsRawHead( $data )
{
	$nPos = Gen::StrPosArr( $data, array( '</head>', '</HEAD>' ) );
	if( $nPos === false )
		return( false );
	$data = substr( $data, 0, $nPos );

	$nPos = Gen::StrPosArr( $data, array( '<head>', '<HEAD>' ) );
	if( $nPos === false )
		return( false );
	return( substr( $data, $nPos + 6 ) );
}

function GetContentsMetaProps( $data )
{
	$res = array();

	$data = GetContentsRawHead( $data );
	if( !$data )
		return( $res );

	$doc = new \DOMDocument();
	if( !@$doc -> loadHTML( '<!DOCTYPE html><html><head>' . $data . '</head></html>', LIBXML_NOBLANKS | LIBXML_NONET | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_PARSEHUGE ) )
		return( $res );

	foreach( $doc -> getElementsByTagName( 'meta' ) as $item )
	{
		$k = $item -> getAttribute( 'property' );
		if( !$k )
			$k = $item -> getAttribute( 'name' );

		$v = $item -> getAttribute( 'content' );

		if( $k && $v )
			$res[ $k ] = $v;
	}

	return( $res );
}

function _ContGrpsGet_MatchEx( $v, $aPattern )
{
	global $seraph_accel_g_aContGrpsGet_matchedGroupsData;

	if( @is_a( $v, 'DOMNode' ) )
		$v = $v -> nodeValue;

	if( is_string( $v ) )
	{
		foreach( $aPattern as $pattern )
		{
			$m = array();
			if( !@preg_match( $pattern, $v, $m ) )
				continue;

			array_shift( $m );
			foreach( $m as $mI )
				$seraph_accel_g_aContGrpsGet_matchedGroupsData[ $mI ] = true;
			return( true );
		}
	}

	return( false );
}

function _ContGrpsGet_Match( $v )
{
	$aPattern = func_get_args();
	array_shift( $aPattern );

	if( is_array( $v ) )
	{
		foreach( $v as $vi )
			if( _ContGrpsGet_MatchEx( $vi, $aPattern ) )
				return( true );
	}
	else if( _ContGrpsGet_MatchEx( $v, $aPattern ) )
		return( true );

	return( null );
}

function ContGrpsGet( &$path, $ctxProcess, $settGrps, $doc, $viewId, &$grpVariationDataId = null )
{
	$res = array();

	$xpath = null;

	$pathOrig = substr( ParseContCachePathArgs( $ctxProcess[ 'serverArgs' ], $args ), strlen( $ctxProcess[ 'siteRootUri' ] ) );
	$path = CachePathNormalize( $pathOrig, $pathIsDir );
	if( $pathIsDir )
		$path .= '/';

	foreach( Gen::GetArrField( $settGrps, array( 'items' ), array() ) as $contGrpId => $contGrp )
	{
		$mode = ($contGrp[ 'enable' ]??null);
		if( !( $mode & ( ( isset( $res[ 1 ] ) ? 0 : 1 ) | ( isset( $res[ 2 ] ) ? 0 : 2 ) ) ) )
			continue;

		if( $a = Gen::GetArrField( $contGrp, array( 'views' ), array() ) )
			if( !in_array( $viewId, $a ) )
				continue;

		if( $a = Gen::GetArrField( $contGrp, array( 'urisIncl' ), array() ) )
			if( !CheckPathInUriList( $a, $path, $pathOrig ) )
				continue;

		if( $a = Gen::GetArrField( $contGrp, array( 'argsIncl' ), array() ) )
		{
			$found = false;
			foreach( $args as $argKey => $argVal )
			{
				$argKeyCmp = strtolower( $argKey );

				foreach( $a as $aI )
					if( _ContProcGetExclStatus_KeyValMatch( $aI, $argKeyCmp, $argVal ) )
					{
						$found = true;
						break;
					}
			}

			if( !$found )
				continue;
		}

		if( $doc && ( $a = Gen::GetArrField( $contGrp, array( 'patterns' ), array() ) ) )
		{
			global $seraph_accel_g_aContGrpsGet_matchedGroupsData;

			$found = false;
			foreach( $a as $pattern )
			{

				if( !$xpath )
				{
					$xpath = new \DOMXPath( $doc );
					$xpath -> registerNamespace( 'php', 'http://php.net/xpath' );
					$xpath -> registerPhpFunctions( array( 'seraph_accel\\_ContGrpsGet_Match' ) );
				}

				$seraph_accel_g_aContGrpsGet_matchedGroupsData = array();

				$pattern = str_replace( 'match(', 'php:function("seraph_accel\\_ContGrpsGet_Match",', $pattern );
				if( HtmlNd::FirstOfChildren( @$xpath -> query( $pattern, $doc ) ) )
				{
					$grpVariationDataId = md5( 'AGGR:' . implode( '', array_keys( $seraph_accel_g_aContGrpsGet_matchedGroupsData ) ) ); unset( $seraph_accel_g_aContGrpsGet_matchedGroupsData );
					$found = true;
					break;
				}
			}

			if( !$found )
				continue;
		}

		if( !isset( $res[ 1 ] ) && ( $mode & 1 ) )
			$res[ 1 ] = array( $contGrp, $contGrpId );
		if( !isset( $res[ 2 ] ) && ( $mode & 2 ) )
			$res[ 2 ] = array( $contGrp, $contGrpId );
	}

	return( $res );
}

function ulyjqbuhdyqcetbhkiy( $url )
{
	return( ($url[ 0 ]??null) == '/' && ($url[ 1 ]??null) != '/' );
}

function Cdn_AdjustUrl( $ctxProcess, $settCdn, &$uri, $fileType )
{
	$uriProbe = $uri;

	if( !ulyjqbuhdyqcetbhkiy( $uriProbe ) )
	{
		if( strpos( $uriProbe, 'seraph_accel_gp' ) === false )
			return( false );
		$uriProbe = $ctxProcess[ 'requestUriPath' ] . '/' . $uriProbe;
	}

	foreach( Gen::GetArrField( $settCdn, array( 'items' ), array() ) as $item )
	{
		$urlCdn = $item[ 'addr' ];
		if( !$item[ 'enable' ] || !$urlCdn )
			continue;

		{
			$types = Gen::GetArrField( $item, array( 'types' ), array() );
			if( $types && !in_array( $fileType, $types ) )
				continue;
		}

		{
			$uris = Gen::GetArrField( $item, array( 'uris' ), array() );
			if( $uris && !IsUrlInPartsList( $uris, $uriProbe ) )
				continue;
		}

		{
			$uris = Gen::GetArrField( $item, array( 'urisExcl' ), array() );
			if( $uris && IsUrlInPartsList( $uris, $uriProbe ) )
				continue;
		}

		$urlCdn = Net::GetUrlWithoutProtoEx( $urlCdn, $proto );
		if( $proto )
		{
			$scheme = ($ctxProcess[ 'serverArgs' ][ 'REQUEST_SCHEME' ]??null);
			if( $proto == 'http' && $scheme == 'https' )
				$proto = $scheme;
			$urlCdn = $proto . '://' . $urlCdn;
		}

		$uri = $urlCdn . $uriProbe;
		return( true );
	}

	return( false );
}

function Fullness_AdjustUrl( $ctxProcess, &$src, $srcUrlFullness = null )
{
	if( !ulyjqbuhdyqcetbhkiy( $src ) )
		return( false );

	$serverArgs = $ctxProcess[ 'serverArgs' ];
	$host = Net::GetUrlWithoutProto( $ctxProcess[ 'siteDomainUrl' ] );

	if( $ctxProcess[ 'srcUrlFullness' ] !== 0 )
		$srcUrlFullness = $ctxProcess[ 'srcUrlFullness' ];
	else if( $srcUrlFullness === null )
		return( false );

	switch( $srcUrlFullness )
	{
	case 4:		$src = ($serverArgs[ 'REQUEST_SCHEME' ]??null) . '://' . $host . $src; return( true );
	case 3:			$src = '//' . $host . $src; return( true );
	}

	if( substr( $src, 0, 3 ) == '//#' )
		$src = substr( $src, 2 );

	return( false );
}

function GetSourceItemTracePath( $ctxProcess, $nodePath, $srcInfo = null, $id = null )
{
	$res = '';
	if( $srcInfo )
	{
		if( isset( $srcInfo[ 'filePath' ] ) )
			$res .= substr( $srcInfo[ 'filePath' ], strlen( $srcInfo[ 'filePathRoot' ] ) );
		else
			$res .= ($srcInfo[ 'url' ]??'');
	}
	else
	{
		$res .= '/' . substr( $ctxProcess[ 'requestUriPath' ], strlen( $ctxProcess[ 'siteRootUri' ] ) );
		$res .= ':' . str_replace( array( '/', '[@' ), array( '>', '[' ), preg_replace( '@\\[(\\d+)\\]@', ':nth-of-type($1)', trim( $nodePath, '/' ) ) );

		if( $id )
			$res .= '#' . $id;
	}

	return( $res );
}

function Conts_CheckExclEx( &$ctxProcess, $doc, $sett, $item, $id1, $settPath )
{
	$exclItems = &$ctxProcess[ $id1 ];
	if( $exclItems === null )
	{
		$exclItems = array();

		$excls = Gen::GetArrField( $sett, $settPath, array() );
		if( $excls )
		{
			$xpath = new \DOMXPath( $doc );

			foreach( $excls as $exclItemPath )
			{
				$mode = 'y';
				if( Gen::StrStartsWith( $exclItemPath, 'ajs:' ) )
				{
					if( ($ctxProcess[ 'isJsDelayed' ]??null) )
						$mode = 'ajs';
					$exclItemPath = substr( $exclItemPath, 4 );
				}

				foreach( HtmlNd::ChildrenAsArr( @$xpath -> query( $exclItemPath, $ctxProcess[ 'ndHtml' ] ) ) as $itemExcl )
					$exclItems[ $mode ][] = $itemExcl;
			}
		}
	}

	foreach( array( 'y', 'ajs' ) as $mode )
		if( isset( $exclItems[ $mode ] ) && in_array( $item, $exclItems[ $mode ], true ) )
			return( $mode );

	return( false );
}

function LazyCont_Process( &$ctxProcess, $sett, $settCache, $settContPr, $doc, $norm, $jsNotCritsDelayTimeout )
{

	$itemsPathes = Gen::GetArrField( $settContPr, array( 'lazy', 'items' ), array() );
	if( !$itemsPathes )
		return( null );

	$bLazyCont = null;
	$itemPathPrmsDef = array( 'bjs' => Gen::GetArrField( $settContPr, array( 'lazy', 'bjs' ), false ), 'sep' => 9999999, 'chunk' => 8192, 'chunkSep' => 524288 );

	$xpath = new \DOMXPath( $doc );
	$xpath -> registerNamespace( 'php', 'http://php.net/xpath' );
	$xpath -> registerPhpFunctions( array( 'seraph_accel\\_LazyCont_XpathExtFunc_FollowingSiblingUpToParent' ) );

	$aItemsExcl = array();
	foreach( Gen::GetArrField( $settContPr, array( 'lazy', 'itemsExcl' ), array() ) as $itemPathExcl )
		foreach( HtmlNd::ChildrenIter( @$xpath -> query( $itemPathExcl ) ) as $item )
			$aItemsExcl[] = $item;

	$idNosPart = 1;
	$aItemSubstBlock = array();
	$aItemNoScript = array();

	foreach( $itemsPathes as $itemPath )
	{
		$itemPathPrms = $itemPathPrmsDef;
		if( preg_match( '@^([\\w,=]+):[^:]@', $itemPath, $m ) )
		{
			$itemPathPrms = array_merge( $itemPathPrms, Gen::ParseProps( $m[ 1 ], ',', '=', array( 'bjs' => '', 'sep' => 1, 'chunk' => 8192, 'chunkSep' => 524288 ) ) );
			$itemPath = substr( $itemPath, strlen( $m[ 1 ] ) + 1 );
		}

		if( $itemPathPrms[ 'bjs' ] && !$jsNotCritsDelayTimeout )
			continue;

		$itemPath = str_replace( 'followingSiblingUpToParent(', 'php:function("seraph_accel\\_LazyCont_XpathExtFunc_FollowingSiblingUpToParent",', $itemPath );

		$items = array();
		foreach( HtmlNd::ChildrenIter( @$xpath -> query( $itemPath ) ) as $item )
		{
			if( ( $item -> nodeName == 'script' || $item -> nodeName == 'style' || $item -> nodeName == 'link' ) )
				continue;

			$bExcl = false;
			foreach( $aItemsExcl as $itemExcl )
			{

				if( !HtmlNd::DoesContain( $itemExcl, $item ) && !HtmlNd::DoesContain( $item, $itemExcl ) )
					continue;
				$bExcl = true;
				break;
			}
			if( $bExcl )
				continue;

			for( $i = 0; $i < count( $items ); $i++ )
			{
				if( HtmlNd::DoesContain( $items[ $i ], $item ) )
					break;

				if( HtmlNd::DoesContain( $item, $items[ $i ] ) )
				{
					array_splice( $items, $i, 1 );
					continue;
				}
			}

			if( $i === count( $items ) )
				$items[] = $item;
		}
		if( !$items )
			continue;

		$bLazyCont = true;

		$nItemsGroupSize = 0;
		$itemGroupFirst = $itemGroupLast = null;
		$itemGroupCurParent = null;
		$iSubstSequentalBlock = 1;

		for( $i = 0; $i < count( $items ) + 1; $i++ )
		{
			$item = $i < count( $items ) ? $items[ $i ] : null;

			if( $item )
			{
				$overlapped = false;
				if( !$overlapped )
					foreach( $aItemSubstBlock as $itemSubstBlock )
						if( HtmlNd::DoesContain( $item, $itemSubstBlock ) )
						{
							$overlapped = true;
							break;
						}
				if( !$overlapped )
					foreach( $aItemNoScript as $itemNoScript )
						if( HtmlNd::DoesContain( $itemNoScript, $item ) )
						{
							$overlapped = true;
							break;
						}

				if( $overlapped )
					continue;
			}

			if( $item && $itemGroupLast && HtmlNd::GetNextTypeSibling( $itemGroupLast ) === $item && $nItemsGroupSize < ( ( $iSubstSequentalBlock >= $itemPathPrms[ 'sep' ] ) ? $itemPathPrms[ 'chunkSep' ] : $itemPathPrms[ 'chunk' ] ) )
			{
				$nItemsGroupSize += HtmlNd::GetOuterSize( $item );
				$itemGroupLast = $item;
				continue;
			}

			if( $itemGroupFirst )
			{
				if( ($ctxProcess[ 'compatView' ]??null) )
				{
					ContentMarkSeparate( $itemGroupFirst, false, 1 );
					ContentMarkSeparate( $itemGroupLast, false, 2 );
				}
				else
				{
					$itemSubstBlock = $doc -> createElement( 'i' );
					HtmlNd::AddRemoveAttrClass( $itemSubstBlock, array( $itemPathPrms[ 'bjs' ] !== 'only' ? 'lzl' : null, $itemPathPrms[ 'bjs' ] ? 'bjs' : null ) );
					$itemSubstBlock -> setAttribute( 'data-lzl-nos', ( string )$idNosPart );
					$itemGroupFirst -> parentNode -> insertBefore( $itemSubstBlock, $itemGroupFirst );

					if( isset( $itemPathPrms[ 'height' ] ) )
						$itemSubstBlock -> setAttribute( 'style', Ui::GetStyleAttr( array( 'height' => $itemPathPrms[ 'height' ] ), false ) );

					{
						$itemNoScript = $doc -> createElement( 'noscript' );
						$itemNoScript -> setAttribute( 'data-lzl-nos', ( string )$idNosPart );
						HtmlNd::InsertAfter( $itemSubstBlock -> parentNode, $itemNoScript, $itemSubstBlock );

						for( ;; )
						{
							$itemNext = $itemGroupFirst -> nextSibling;
							$itemNoScript -> appendChild( $itemGroupFirst );

							if( $itemGroupFirst === $itemGroupLast )
								break;
							$itemGroupFirst = $itemNext;
						}
						ContNoScriptItemClear( $itemNoScript );
					}

					if( HtmlNd::GetChildrenCount( $itemNoScript ) )
					{
						$aItemSubstBlock[] = $itemSubstBlock;

						$itemNoScript -> insertBefore( _ContentMarkSeparate_CreateSepElem( $doc ), $itemNoScript -> firstChild );
						$itemNoScript -> appendChild( _ContentMarkSeparate_CreateSepElem( $doc ) );

						ContentMarkSeparate( $itemSubstBlock, false, 1 );
						ContentMarkSeparate( $itemNoScript, false, 2 );

						if( $iSubstSequentalBlock >= $itemPathPrms[ 'sep' ] )
						{
							$idCp = ( string )( $ctxProcess[ 'subCurIdx' ]++ );
							$ctxProcess[ 'subs' ][ $idCp . '.html' ] = HtmlDocDeParse( $doc, $norm, $itemNoScript );
							$itemNoScript -> parentNode -> removeChild( $itemNoScript );
							$itemNoScript = null;

							$itemSubstBlock -> setAttribute( 'data-cp', $idCp );
						}
						else if( Gen::GetArrField( $settContPr, array( 'lazy', 'p' ), false ) )
						{

							$itemNoScript -> removeChild( $itemNoScript -> firstChild );
							$itemNoScript -> removeChild( $itemNoScript -> lastChild );

							$itemSubstBlock -> setAttribute( 'data-c', rawurlencode( HtmlDocDeParse( $doc, $norm, $itemNoScript ) ) );
							$itemSubstBlock -> setAttribute( 'data-gt-translate-attributes', @json_encode( array( array( 'attribute' => 'data-c', 'format' => 'html-urlencoded' ) ) ) );

							$itemNoScript -> parentNode -> removeChild( $itemNoScript );
							$itemNoScript = null;
						}
						else
							$aItemNoScript[] = $itemNoScript;

						$iSubstSequentalBlock++;
						$itemGroupCurParent = $itemSubstBlock -> parentNode;

						$idNosPart++;
						$ctxProcess[ 'lazyload' ] = true;
					}
					else
					{
						$itemNoScript -> parentNode -> removeChild( $itemNoScript );
						$itemNoScript = null;

						$itemSubstBlock -> parentNode -> removeChild( $itemSubstBlock );
						$itemSubstBlock = null;
					}
				}
			}

			$itemGroupFirst = $itemGroupLast = $item;
			$nItemsGroupSize = HtmlNd::GetOuterSize( $item );

			if( $item && $item -> parentNode !== $itemGroupCurParent )
				$iSubstSequentalBlock = 1;
		}
	}

	return( $bLazyCont );
}

function _LazyCont_XpathExtFunc_FollowingSiblingUpToParent( $v )
{
	if( !is_array( $v ) || count( $v ) < 1 )
		return( false );

	$aNdParent = func_get_args();
	if( count( $aNdParent ) > 1 && is_array( $aNdParent[ 1 ] ) )
		$aNdParent = $aNdParent[ 1 ];
	else
		$aNdParent = null;
	return( new LazyCont_XpathExtFunc_FollowingSiblingUpToParent_Iterator( $v, $aNdParent ) );
}

function GetContentProcessCtxEx( $serverArgs, $sett, $siteId, $siteUrl, $siteRootPath, $siteContentPath, $wpRootSubPath, $cacheDir, $scriptDebug )
{
	$ctx = array(
		'siteDomainUrl' => Net::GetSiteAddrFromUrl( $siteUrl, true ),
		'siteRootUri' => Gen::SetLastSlash( Net::Url2Uri( $siteUrl ), false ),
		'siteRootPath' => Gen::SetLastSlash( $siteRootPath, false ),
		'siteContPath' => Gen::SetLastSlash( $siteContentPath, false ),
		'siteRootDataPath' => null,
		'dataPath' => GetCacheDataDir( $cacheDir . '/s/' . $siteId ),
		'wpRootSubPath' => $wpRootSubPath . '/',
		'siteId' => $siteId,
		'deps' => array(),
		'subs' => array(),
		'subCurIdx' => 0,
		'debugM' => ($sett[ 'debug' ]??null),
		'debug' => ($sett[ 'debugInfo' ]??null),
		'jsMinSuffix' => $scriptDebug ? '' : '.min',
		'userAgent' => strtolower( isset( $_SERVER[ 'SERAPH_ACCEL_ORIG_USER_AGENT' ] ) ? $_SERVER[ 'SERAPH_ACCEL_ORIG_USER_AGENT' ] : ($serverArgs[ 'HTTP_USER_AGENT' ]??null) ),
		'mode' => ( 1 | 2 | 4 ),
		'modeReq' => 0,
		'aAttrImg' => array(),

		'aCssCrit' => array(),
		'aCssRpl' => array(),
		'aCssRplExcl' => array(),

	);

	if( strpos( $ctx[ 'dataPath' ], $ctx[ 'siteRootPath' ] . '/' ) === 0 )
		$ctx[ 'siteRootDataPath' ] = $ctx[ 'siteRootPath' ];
	else if( strpos( $ctx[ 'dataPath' ], $ctx[ 'siteContPath' ] . '/' ) === 0 )
		$ctx[ 'siteRootDataPath' ] = Gen::GetFileDir( $ctx[ 'siteContPath' ] );
	else
		$ctx[ 'siteRootDataPath' ] = $cacheDir;

	$ctx[ 'compatView' ] = ContProcIsCompatView( Gen::GetArrField( $sett, array( 'cache' ), array() ), $ctx[ 'userAgent' ] );

	CorrectRequestScheme( $serverArgs );

	$ctx[ 'serverArgs' ] = $serverArgs;
	$ctx[ 'requestUriPath' ] = Gen::GetFileDir( ($serverArgs[ 'REQUEST_URI' ]??null) );
	$ctx[ 'host' ] = Gen::GetArrField( Net::UrlParse( $serverArgs[ 'REQUEST_SCHEME' ] . '://' . GetRequestHost( $serverArgs ) ), array( 'host' ) );
	if( !$ctx[ 'host' ] )
		$ctx[ 'host' ] = ($serverArgs[ 'SERVER_NAME' ]??null);

	$settContPr = Gen::GetArrField( $sett, array( 'contPr' ), array() );
	if( Gen::GetArrField( $settContPr, array( 'normUrl' ), false ) )
		$ctx[ 'srcUrlFullness' ] = Gen::GetArrField( $settContPr, array( 'normUrlMode' ), 0 );
	else
		$ctx[ 'srcUrlFullness' ] = 0;

	$ctx[ 'aVPth' ] = array_map( function( $vPth ) { $vPth[ 'f' ] .= 'S'; return( $vPth ); }, GetVirtUriPathsFromSett( $sett ) );

	return( $ctx );
}

function &GetContentProcessCtx( $serverArgs, $sett )
{
	global $seraph_accel_g_ctxProcess;

	if( !$seraph_accel_g_ctxProcess )
	{
		$siteRootUrl = Wp::GetSiteRootUrl();

		$siteWpRootSubPath = rtrim( Wp::GetSiteWpRootUrl( '', null, true ), '/' );
		if( strpos( $siteWpRootSubPath, rtrim( $siteRootUrl, '/' ) ) === 0 )
			$siteWpRootSubPath = trim( substr( $siteWpRootSubPath, strlen( rtrim( $siteRootUrl, '/' ) ) ), '/' );
		else
			$siteWpRootSubPath = '';

		if( defined( 'SERAPH_ACCEL_SITEROOT_DIR' ) )
			$siteRootPath = SERAPH_ACCEL_SITEROOT_DIR;
		else
		{
			$siteRootPath = ABSPATH;
			if( $siteWpRootSubPath && Gen::StrEndsWith( rtrim( $siteRootPath, '\\/' ), $siteWpRootSubPath ) )
				$siteRootPath = substr( rtrim( $siteRootPath, '\\/' ), 0, - strlen( $siteWpRootSubPath ) );
		}

		$seraph_accel_g_ctxProcess = GetContentProcessCtxEx( $serverArgs, $sett, GetSiteId(), $siteRootUrl, $siteRootPath, WP_CONTENT_DIR, $siteWpRootSubPath, GetCacheDir(), defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
	}

	return( $seraph_accel_g_ctxProcess );
}

function _JsClk_XpathExtFunc_ifExistsThenCssSel( $v, $cssSel )
{
	if( !is_array( $v ) || count( $v ) < 1 )
		return( false );
	return( new JsClk_ifExistsThenCssSel( $cssSel ) );
}

