<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

if( !defined( 'SERAPH_ACCEL_PLUGIN_DIR' ) ) define( 'SERAPH_ACCEL_PLUGIN_DIR', __DIR__ ); else if( SERAPH_ACCEL_PLUGIN_DIR != __DIR__ ) return;

require_once( __DIR__ . '/common.php' );

function _OutImage( $siteId, $settGlob, $sett, $file, $nonce, $aiFileId = null )
{
	if( Gen::StrStartsWith( $file, Gen::GetFileName( WP_CONTENT_DIR ) . '/' ) )
		$pathRoot = Gen::GetFileDir( WP_CONTENT_DIR ) . '/';
	else
		$pathRoot = Gen::SetLastSlash( str_replace( '\\', '/', ABSPATH ) );

	if( Gen::GetNonce( $file, GetSalt() ) != $nonce )
	{
		http_response_code( 403 );
		return;
	}

	$file = $fileOrig =  $pathRoot. $file;
	$fileExt = Gen::GetFileExt( $file );

	if( !in_array( strtolower( $fileExt ), array( 'jpe','jpg','jpeg','png','gif','bmp', 'webp','avif' ) ) )
	{
		http_response_code( 403 );
		return;
	}

	$settImg = Gen::GetArrField( $sett, array( 'contPr', 'img' ), array() );

	if( $aiFileId )
	{
		$aiId = array();
		if( !preg_match( '@^([\\w\\-]+)\\.(\\w+-(\\w+))$@', $aiFileId, $aiId ) )
		{
			http_response_code( 403 );
			return;
		}

		$fileAi = GetCacheDir() . '/s/' . $aiId[ 1 ] . '/ai/' . $aiId[ 2 ] . '.' . $fileExt;
		$aiId = $aiId[ 3 ];

		$fileTm = Images_ProcessSrcEx_FileMTime( $file );
		$fileAiTm = Images_ProcessSrcEx_FileMTime( $fileAi );
		if( $fileAiTm !== false && $fileTm !== false && $fileTm <= $fileAiTm )
			$file = $fileOrig = $fileAi;
		else if( Gen::GetArrField( $settImg, array( 'szAdaptOnDemand' ), false ) )
		{
			if( Gen::GetArrField( $settImg, array( 'szAdaptAsync' ), false ) )
			{
				CachePostPrepareObjEx( 20, $file, ( string )$siteId, 10, array( 'ai' => $aiId ) );
			}
			else
			{
				require_once( __DIR__ . '/content.php' );

				{
					$ctxProcess = &GetContentProcessCtxEx( $_SERVER, $sett, $siteId, '', str_replace( '\\', '/', ABSPATH ), WP_CONTENT_DIR, '', GetCacheDir(), false );
					Images_ProcessSrc_SizeAlternatives( $ctxProcess, $file, $sett, true, $aiId );
					unset( $ctxProcess );
				}

				if( file_exists( $fileAi ) )
					$file = $fileOrig = $fileAi;
			}
		}
	}

	$mimeType = Fs::MimeTypeDef;
	$aSkip = array();
	$bNeedCompr = false;
	foreach( array_reverse( array( 'webp','avif' ) ) as $comprType )
	{
		if( !Gen::GetArrField( $settImg, array( $comprType, 'redir' ), false ) )
			continue;

		if( strpos( ($_SERVER[ 'HTTP_ACCEPT' ]??''), 'image/' . $comprType ) === false )
			continue;

		$pathCompr = $file . '.' . $comprType;
		if( !@file_exists( $pathCompr ) )
		{
			$pathCompr .= '.json';
			if( !@file_exists( $pathCompr ) && Gen::GetArrField( $settImg, array( $comprType, 'enable' ), false ) )
				$bNeedCompr = true;
			$aSkip[ $comprType ] = @json_decode( ( string )@file_get_contents( $pathCompr, false, null, 0, 500 ), true );
			continue;
		}

		$file = $pathCompr;
		$mimeType = 'image/' . $comprType;
		break;
	}

	if( $bNeedCompr && Gen::GetArrField( $settImg, array( 'comprAsync' ), false ) )
		CachePostPrepareObjEx( 10, $fileOrig, ( string )$siteId, 10 );

	if( ($sett[ 'hdrTrace' ]??null) )
		@header( 'X-Seraph-Accel-Cache: 2.28.15; state=' . ( $mimeType == Fs::MimeTypeDef ? 'original' : 'preoptimized' ) . '; redir=alt;' . ( $aSkip ? ( '; skip=' . @json_encode( $aSkip ) ) : '' ) . ( $mimeType != Fs::MimeTypeDef ? ( '; sizeOrig=' . @filesize( $fileOrig ) ) : '' ) );

	if( $mimeType != Fs::MimeTypeDef && Gen::GetArrField( $settImg, array( 'redirCacheAdapt' ), false ) )
	{
		@header( 'Location: ' . rtrim( ( string )Net::UrlDeParse( Net::UrlParse( $_SERVER[ 'REQUEST_URI' ] ), 0, array(), array( PHP_URL_PATH ) ), '/' ) . '/' . substr( $file, strlen( Gen::SetLastSlash( str_replace( '\\', '/', ABSPATH ) ) ) ), false );
		http_response_code( 302 );
		return;
	}

	@header( 'Vary: Accept', false );

	Fs::StreamOutFileContent( $file, $mimeType, false, 16384, false, Gen::GetArrField( $sett, array( 'cacheBr', 'enable' ), false ) ? Gen::GetArrField( $sett, array( 'cacheBr', 'timeout' ), 0 ) * 60 : 0, Gen::GetArrField( $settGlob, array( 'cache', 'chkNotMdfSince' ), false ) );
}

global $seraph_accel_g_cacheSkipData;
global $seraph_accel_sites;

ExtCron_Init();

if( !$seraph_accel_sites )
	return;

$hr = _Process( $seraph_accel_sites );

if( $hr == Gen::S_OK || Gen::HrFail( $hr ) )
{
	if( isset( $args[ 'seraph_accel_gp' ] ) || !CacheDoCronAndEndRequest() )
	{

		flush();
		exit();
	}

	return;
}

if( $hr == Gen::S_NOTIMPL )
	return;

if( $hr == Gen::S_IO_PENDING )
{

	ob_start( 'seraph_accel\\_CbContentFinish' );
	ob_start( 'seraph_accel\\_CbContentProcess' );
}
else if( $seraph_accel_g_cacheSkipData )
{

	ob_start( 'seraph_accel\\_CbContentFinishSkip' );
	ob_start( 'seraph_accel\\_CbContentProcess' );
}

function _Process( $sites )
{
	$requestMethod = strtoupper( ($_SERVER[ 'REQUEST_METHOD' ]??'GET') );
	$args = $_GET;

	global $seraph_accel_g_noFo;
	global $seraph_accel_g_prepPrms;
	global $seraph_accel_g_lazyInvTmp;
	global $seraph_accel_g_cacheSkipData;
	global $seraph_accel_g_siteId;
	global $seraph_accel_g_cacheCtxSkip;
	global $seraph_accel_g_simpCacheMode;

	if( ( $requestMethod == 'GET' ) && isset( $_SERVER[ 'HTTP_X_SERAPH_ACCEL_TEST' ] ) )
	{
		if( $idTest = Gen::SanitizeId( substr( $_SERVER[ 'HTTP_X_SERAPH_ACCEL_TEST' ], 0, 64 ) ) )
		{
			@header( 'X-Seraph-Accel-test: ' . $idTest );
			@header( 'ETag: "001122334455667788"' );
		}

		unset( $idTest );
	}

	$seraph_accel_g_prepPrms = CacheExtractPreparePageParams( $args );
	if( $seraph_accel_g_prepPrms !== null )
	{

		BatCache_DontProcessCurRequest( true );

		if( $seraph_accel_g_prepPrms === false )
		{
			http_response_code( 400 );
			return( Gen::E_INVALIDARG );
		}

		@ignore_user_abort( true );
		Gen::SetTimeLimit( 570 );

		if( ($seraph_accel_g_prepPrms[ 'selfTest' ]??null) )
		{
			$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'selfTest' ) );
			return( Gen::S_FALSE );
		}

		if( !ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array( 'stage' => 'get' ), true, true ) )
		{
			http_response_code( 599 );
			return( Gen::E_FAIL );
		}

		register_shutdown_function(
			function()
			{
				for( $l = ob_get_level(); $l > 0; $l-- )
					ob_end_flush();
			}
		);

	}

	{
		if( isset( $_SERVER[ 'HTTP_X_SERAPH_ACCEL_POSTPONE_USER_AGENT' ] ) )
		{
			$_SERVER[ 'HTTP_USER_AGENT' ] = $_SERVER[ 'HTTP_X_SERAPH_ACCEL_POSTPONE_USER_AGENT' ];
			unset( $_SERVER[ 'HTTP_X_SERAPH_ACCEL_POSTPONE_USER_AGENT' ] );

		}
	}

	$siteUriRoot = ''; GetContCacheEarlySkipData( $pathOrig, $path, $pathIsDir, $args );
	if( $pathOrig !== null )
	{
		$host = GetRequestHost( $_SERVER );
		$addrSite = $host;

		$seraph_accel_g_siteId = GetCacheSiteIdAdjustPath( $sites, $addrSite, $siteSubId, $path );
		if( $seraph_accel_g_siteId === null )
			$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'siteIdUnk' ) );
		else
			$siteUriRoot = substr( $addrSite, strlen( $host ) );

		unset( $addrSite, $host );
	}

	$settGlob = Plugin::SettGet( Gen::CallFunc( 'seraph_accel_siteSettInlineDetach', array( 'm' ) ) );
	if( $seraph_accel_g_siteId != 'm' )
	{
		if( is_multisite() )
			PluginOptions::SetBlogId( ( int )GetBlogIdFromSiteId( $seraph_accel_g_siteId ) );
		$sett = Plugin::SettGet( Gen::CallFunc( 'seraph_accel_siteSettInlineDetach', array( $seraph_accel_g_siteId ) ) );
		PluginOptions::SetBlogId( null );
	}
	else
		$sett = $settGlob;

	if( ( $requestMethod == 'GET' ) && isset( $_REQUEST[ 'seraph_accel_gi' ] ) )
	{

		BatCache_DontProcessCurRequest( true );

		$siteId = $seraph_accel_g_siteId;

		$file = $_REQUEST[ 'seraph_accel_gi' ];
		if( isset( $_REQUEST[ 'intrnl' ] ) )
		{
			$siteIdProbe = Gen::SanitizeId( $_REQUEST[ 'intrnl' ] );
			foreach( $sites as $addrSite => $id )
			{
				if( $id != $siteIdProbe )
					continue;

				$siteId = $id;
				$siteUriRoot = substr( $addrSite, strlen( GetRequestHost( $_SERVER ) ) );

				$sett = Plugin::SettGet( Gen::CallFunc( 'seraph_accel_siteSettInlineDetach', array( $siteId ) ) );

				break;
			}

			unset( $siteIdProbe, $addrSite, $id );
		}
		else
			$file = $siteUriRoot . '/' . $file;

		$file = VirtUriPath2Real( $file, GetVirtUriPathsFromSett( $sett ) );
		$file = substr( $file, strlen( $siteUriRoot ) + 1 );

		_OutImage( $siteId, $settGlob, $sett, $file, Gen::SanitizeId( ($_REQUEST[ 'n' ]??null) ), Gen::SanitizeId( ($_REQUEST[ 'ai' ]??null) ) );
		exit();
	}

	if( ( $requestMethod == 'GET' ) && isset( $_REQUEST[ 'seraph_accel_gci' ] ) )
	{
		_OutImage( $seraph_accel_g_siteId, $settGlob, $sett, $_REQUEST[ 'seraph_accel_gci' ], Gen::SanitizeId( ($_REQUEST[ 'n' ]??null) ) );
		exit();
	}

	$settCache = Gen::GetArrField( $sett, array( 'cache' ), array() );

	$timeoutCln = Gen::GetArrField( $settCache, array( 'timeoutCln' ), 0 ) * 60;
	$timeout = ($settCache[ 'updByTimeout' ]??true) ? ( Gen::GetArrField( $settCache, array( 'timeout' ), 0 ) * 60 ) : 0;

	$lazyInv = ($settCache[ 'lazyInv' ]??null);

	InitTimeoutClnForWpNonce( $settCache );

	if( ( $requestMethod == 'GET' ) && isset( $_REQUEST[ 'seraph_accel_gf' ] ) )
	{
		@header( 'X-Robots-Tag: noindex' );

		$seraph_accel_g_simpCacheMode = 'fragments:' . Gen::SanitizeId( $_REQUEST[ 'seraph_accel_gf' ] );

		$timeoutCln = Gen::GetArrField( $settCache, array( 'timeoutFrCln' ), 0 );
		$timeout = Gen::GetArrField( $settCache, array( 'timeoutFr' ), 0 );

		$lazyInv = ($settCache[ 'lazyInvFr' ]??null);

		Net::CurRequestRemoveArgs( $args, array( 'seraph_accel_gf' ) );
		if( !$timeout )
		{
			$seraph_accel_g_cacheSkipData = array( 'revalidated', array( 'reason' => 'timeoutZero' ) );
			return( Gen::S_FALSE );
		}

	}

	if( $seraph_accel_g_simpCacheMode === null && Gen::GetArrField( $settCache, array( 'ctxSkip' ), false ) )
		$seraph_accel_g_cacheCtxSkip = true;

	$idSubPart = ( $requestMethod == 'GET' ) ? Gen::SanitizeId( ($_REQUEST[ 'seraph_accel_gp' ]??null), null ) : null;
	if( $idSubPart )
	{
		@header( 'X-Robots-Tag: noindex' );
		Net::CurRequestRemoveArgs( $args, array( 'seraph_accel_gp' ) );
	}

	$requestMethodCache = 'GET';
	{
		$itemDataFound = null;
		if( ( $requestMethod == 'GET' ) && isset( $_REQUEST[ 'seraph_accel_gbnr' ] ) )
		{
			$itemDataFound = array(
				'type' => 'GET', 'mime' => 'text/plain',
				'exclArgsAll' => false, 'exclArgs' => array(),
				'skipArgsEnable' => true, 'skipArgsAll' => false, 'skipArgs' => array( '!seraph_accel_gbnr' ),
				'timeoutCln' => 60 * 60 * 24, 'timeout' => 60, 'lazyInv' => true,
			);
		}
		else
		{
			$requestURICheck = null;
			foreach( Gen::GetArrField( $settCache, array( 'data', 'items' ), array() ) as $itemData )
			{
				if( !($itemData[ 'enable' ]??null) )
					continue;

				if( $requestMethod != ($itemData[ 'type' ]??'GET') )
					continue;

				$found = false;
				foreach( ExprConditionsSet_Parse( ($itemData[ 'pattern' ]??'') ) as $e )
				{
					if( $requestURICheck === null )
					{
						$requestURICheck = $_SERVER[ 'REQUEST_URI' ];

						if( $requestMethod == 'POST' )
						{
							AddCurPostArgs( $args );
							$requestURICheck = Net::UrlAddArgs( $requestURICheck, $args );
						}
					}

					$val = false;
					if( IsStrRegExp( $e[ 'expr' ] ) )
					{
						if( @preg_match( $e[ 'expr' ], $requestURICheck ) )
							$val = true;
					}
					else if( strpos( $requestURICheck, $e[ 'expr' ] ) !== false )
						$val = true;

					if( !ExprConditionsSet_ItemOp( $e, $val ) )
					{
						$found = false;
						break;
					}

					$found = true;
				}

				if( $found )
				{
					$itemDataFound = $itemData;
					break;
				}
			}
		}

		if( $itemDataFound )
		{
			$seraph_accel_g_simpCacheMode = 'data:' . Fs::GetFileTypeFromMimeContentType( ($itemDataFound[ 'mime' ]??''), 'bin' );

			foreach( array( 'exclArgsAll', 'exclArgs', 'skipArgsEnable', 'skipArgsAll', 'skipArgs' ) as $fld )
				Gen::SetArrField( $settCache, array( $fld ), Gen::GetArrField( $itemDataFound, array( $fld ) ) );
			$requestMethodCache = ($itemDataFound[ 'type' ]??'GET');
			$timeoutCln = Gen::GetArrField( $itemDataFound, array( 'timeoutCln' ), 0 );
			$timeout = Gen::GetArrField( $itemDataFound, array( 'timeout' ), 0 );

			$lazyInv = ($itemDataFound[ 'lazyInv' ]??null);

			if( $pathOrig !== null && $seraph_accel_g_cacheSkipData )
				$seraph_accel_g_cacheSkipData = null;
		}

		unset( $requestURICheck, $itemData, $itemDataFound, $found, $val );
	}

	if( $requestMethod != $requestMethodCache )
	{
		if( $requestMethod != 'GET' )
			$seraph_accel_g_cacheSkipData = null;
		return( Gen::S_FALSE );
	}

	if( $seraph_accel_g_cacheSkipData )
	{

		BatCache_DontProcessCurRequest();

		_ProcessOutHdrTrace( $sett, true, true, $seraph_accel_g_cacheSkipData[ 0 ], ($seraph_accel_g_cacheSkipData[ 1 ]??null) );
		if( $seraph_accel_g_prepPrms !== null )
			ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array_merge( array( 'finish' => true, 'skip' => Gen::GetArrField( ($seraph_accel_g_cacheSkipData[ 1 ]??null), array( 'reason' ), '' ) ), ($sett[ 'debugInfo' ]??null) ? array( 'infos' => array( LocId::Pack( 'SrvArgs' ) => PackKvArrInfo( $_SERVER ) ) ) : array() ), false, false );
		return( Gen::S_NOTIMPL );
	}

	if( GetContentProcessorForce( $sett ) !== null )
	{

		BatCache_DontProcessCurRequest();

		$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'debugContProcForce' ) );

		return( Gen::S_FALSE );
	}

	{
		$exclStatus = ContProcGetExclStatus( $seraph_accel_g_siteId, $settCache, $path, $pathOrig, $pathIsDir, $args, $varsOut, true, $seraph_accel_g_prepPrms === null );
		if( $exclStatus )
		{

			BatCache_DontProcessCurRequest();

			$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => $exclStatus ) );

			if( Gen::StrStartsWith( $exclStatus, 'excl' ) )
			{

				$debugData = ($seraph_accel_g_cacheSkipData[ 1 ]??null);
				if( ($sett[ 'debugInfo' ]??null) )
					$debugData[ 'args' ] = $args;

				_ProcessOutHdrTrace( $sett, true, true, $seraph_accel_g_cacheSkipData[ 0 ], $debugData );
				if( $seraph_accel_g_prepPrms !== null )
					ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array_merge( array( 'finish' => true, 'skip' => $exclStatus ), ($sett[ 'debugInfo' ]??null) ? array( 'infos' => array( LocId::Pack( 'SrvArgs' ) => PackKvArrInfo( $_SERVER ) ) ) : array() ), false, false );
				return( Gen::S_NOTIMPL );
			}

			return( Gen::S_FALSE );
		}

		extract( $varsOut );
		Net::CurRequestRemoveArgs( $args, $aArgRemove );
		unset( $varsOut, $exclStatus, $aArgRemove );
	}

	global $seraph_accel_g_dscFile;
	global $seraph_accel_g_dscFilePending;
	global $seraph_accel_g_dataPath;
	global $seraph_accel_g_prepOrigContHashPrev;
	global $seraph_accel_g_ctxCache;

	$seraph_accel_g_ctxCache = new AnyObj();

	$sessId = $userId ? ($sessInfo[ 'userSessId' ]??null) : ($sessInfo[ 'sessId' ]??null);
	$viewId = GetCacheViewId( $seraph_accel_g_ctxCache, $settCache, $userAgent, $path, $pathOrig, $args, Gen::StrStartsWith( ( string )$seraph_accel_g_simpCacheMode, 'fragments' ) );
	$seraph_accel_g_ctxCache -> viewId = $viewId;
	$cacheRootPath = GetCacheDir();
	$siteCacheRootPath = $cacheRootPath . '/s/' . $seraph_accel_g_siteId;
	$seraph_accel_g_ctxCache -> viewPath = GetCacheViewsDir( $siteCacheRootPath, $siteSubId ) . '/' . $viewId;
	$ctxsPath = $seraph_accel_g_ctxCache -> viewPath . '/c';

	if( ($settCache[ 'normAgent' ]??null) )
		add_action( 'template_redirect',
			function()
			{
				if( !is_404() )
					return;

				if( isset( $_SERVER[ 'SERAPH_ACCEL_ORIG_USER_AGENT' ] ) )
					$_SERVER[ 'HTTP_USER_AGENT' ] = $_SERVER[ 'SERAPH_ACCEL_ORIG_USER_AGENT' ];
			}
		, 0 );

	add_filter( 'wp_redirect_status',
		function( $status, $location )
		{
			global $seraph_accel_g_sRedirLocation;
			$seraph_accel_g_sRedirLocation = $location;
			return( $status );
		}
	, 99999, 2 );

	{
		$seraph_accel_g_ctxCache -> userId = $userId;
		if( !$sessId || !$stateCookId || !Gen::GetArrField( $settCache, array( 'ctxSessSep' ), false ) )
		{
			$seraph_accel_g_ctxCache -> userSessId = null;
			$sessId = '@';
		}
		else
			$seraph_accel_g_ctxCache -> userSessId = $sessId;
		$seraph_accel_g_ctxCache -> isUserSess = $seraph_accel_g_ctxCache -> userId || $seraph_accel_g_ctxCache -> userSessId;

		$ctxPathId = $userId . '/s/' . $sessId;

		if( !$seraph_accel_g_cacheCtxSkip && Gen::GetArrField( $settCache, array( 'ctx' ), false ) )
		{
			$_SERVER[ 'HTTP_X_SERAPH_ACCEL_SESSID' ] = $userId . '/' . $sessId;

			if( $seraph_accel_g_ctxCache -> isUserSess )
			{
				$ctxTimeoutCln = Gen::GetArrField( $settCache, array( 'ctxTimeoutCln' ), 0 ) * 60;
				if( $timeoutCln > $ctxTimeoutCln )
					$timeoutCln = $ctxTimeoutCln;
			}
		}

		if( $stateCookId )
			$stateCookId = md5( $stateCookId );
		else
			$stateCookId = '@';

		$ctxPathId .= '/s/' . $stateCookId;
	}

	$objectId = '@';
	$objectType = 'html';
	if( $pathIsDir )
		$objectId .= 'd';
	if( is_string( $seraph_accel_g_simpCacheMode ) )
	{
		if( Gen::StrStartsWith( $seraph_accel_g_simpCacheMode, 'fragments' ) )
			$objectId .= 'f';
		else if( Gen::StrStartsWith( $seraph_accel_g_simpCacheMode, 'data:' ) )
		{

			$objectType = substr( $seraph_accel_g_simpCacheMode, 5 );
		}
	}

	if( $requestMethod == 'POST' )
		$objectId .= '-p';

	if( !empty( $args ) )
	{
		$argsCumulative = '';
		foreach( $args as $argKey => $argVal )
			$argsCumulative .= $argKey . $argVal;

		$objectId = $objectId . '.' . @md5( $argsCumulative );
		unset( $argsCumulative );
	}

	$seraph_accel_g_dataPath = GetCacheDataDir( $siteCacheRootPath );

	$seraph_accel_g_dscFile = $ctxsPath . '/' . $ctxPathId . '/o';
	if( $path )
		$seraph_accel_g_dscFile .= '/' . $path;
	$seraph_accel_g_dscFile .= '/' . $objectId . '.' . $objectType . '.dat';

	if( $idSubPart )
	{
		$idSubPart = str_replace( '_', '.', $idSubPart );
		$subPartType = Gen::GetFileExt( $idSubPart );
		$idSubPart = Gen::GetFileExt( Gen::GetFileName( $idSubPart, true ) );

		$dscPart = Gen::GetArrField( CacheReadDsc( $seraph_accel_g_dscFile ), array( 'b', $idSubPart ), array() );
		if( !$dscPart )
		{
			http_response_code( 404 );
			return( Gen::S_OK );
		}

		if( _ProcessOutCachedData( true, $subPartType, $settGlob, $sett, $settCache, $dscPart, Gen::FileMTime( $seraph_accel_g_dscFile ), $tmCur, 'cache', null, true ) !== Gen::S_OK )
		{
			http_response_code( 599 );
			return( Gen::E_FAIL );
		}

		return( Gen::S_OK );
	}

	$seraph_accel_g_dscFilePending = $seraph_accel_g_dscFile . '.p';

	if( $seraph_accel_g_prepPrms !== null )
	{
		$seraph_accel_g_dscFilePending .= 'p';
        $seraph_accel_g_noFo = true;
	}

	$contProc = Gen::GetArrField( $sett, array( 'contPr', 'enable' ), false );

	$procTmLim = Gen::GetArrField( $settCache, array( 'procTmLim' ), 570 );

	$sessExpiration = ($sessInfo[ 'expiration' ]??null);
	if( !$sessExpiration )
		$sessExpiration = $tmCur;

	$httpCacheControl = strtolower( ($_SERVER[ 'HTTP_CACHE_CONTROL' ]??'') );

	if( $seraph_accel_g_ctxCache -> isUserSess && !($settCache[ 'ctxLazyInv' ]??null) )
		$lazyInv = false;
	$seraph_accel_g_lazyInvTmp = false;

	if( $timeoutCln && $timeout > $timeoutCln )
		$timeout = $timeoutCln;

	$reason = null;
	$dsc = null;
	$isCip = null;

	$dscFileTm = Gen::FileMTime( $seraph_accel_g_dscFile );
	$dscFileTmAge = $tmCur - $dscFileTm;

	if( !$dscFileTm || ( $timeoutCln > 0 && $dscFileTmAge > $timeoutCln && ( $dscFileTm >= 60 ) ) || ( $timeout > 0 ? ( $dscFileTmAge > $timeout ) : ( $dscFileTm < 60 ) ) || ( $tmCur > $sessExpiration ) || ( $seraph_accel_g_ctxCache -> isUserSess && $httpCacheControl == 'no-cache' && Gen::GetArrField( $settCache, array( 'ctxCliRefresh' ), false ) ) )
	{
		$lock = new Lock( 'dl', $cacheRootPath );
		if( !$lock -> Acquire() )
			return( Gen::E_FAIL );

		$dscFileTm = Gen::FileMTime( $seraph_accel_g_dscFile );

		if( $dscFileTm === false )
		{
			$ccs = _CacheContentStart( $tmCur, $procTmLim );
			$lock -> Release();

			if( $ccs )
			{

				$seraph_accel_g_lazyInvTmp = $lazyInv && ($settCache[ 'lazyInvInitTmp' ]??true);
				if( $seraph_accel_g_prepPrms === null && $contProc && $seraph_accel_g_simpCacheMode === null )
				{
					$seraph_accel_g_cacheSkipData = array( 'revalidating-begin', array( 'reason' => 'initial', 'dscFile' => substr( $seraph_accel_g_dscFile, strlen( $cacheRootPath ) ) ) );
					return( Gen::S_FALSE );
				}

				return( Gen::S_IO_PENDING );
			}

			if( $seraph_accel_g_prepPrms !== null )
			{
				ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array_merge( array( 'finish' => true, 'skip' => 'alreadyProcessing' ), ($sett[ 'debugInfo' ]??null) ? array( 'infos' => array( LocId::Pack( 'SrvArgs' ) => PackKvArrInfo( $_SERVER ) ) ) : array() ), false, false );
				return( Gen::S_OK );
			}

			if( $seraph_accel_g_simpCacheMode === null )
			{
				$seraph_accel_g_cacheSkipData = array( 'revalidating', array( 'reason' => 'initial', 'dscFile' => substr( $seraph_accel_g_dscFile, strlen( $cacheRootPath ) ) ) );
				return( Gen::S_FALSE );
			}

			return( Gen::S_IO_PENDING );
		}
		else
		{

			$dsc = CacheReadDsc( $seraph_accel_g_dscFile );

			$dscFileTmAge = $tmCur - $dscFileTm;

			if( $dscFileTm === 0 )
			{
				$reason = 'forced';

				if( $dsc && !isset( $dsc[ 't' ] ) )
					$seraph_accel_g_prepOrigContHashPrev = ($dsc[ 'h' ]??null);

			}
			else if( $timeoutCln > 0 && $dscFileTmAge > $timeoutCln && ( $dscFileTm >= 60 ) )
			{
				$reason = 'timeoutClnExpired';

				$lazyInv = false;

			}
			else if( $timeout > 0 ? ( $dscFileTmAge > $timeout ) : ( $dscFileTm < 60 ) )
			{
				$reason = ( $dscFileTm === 5 ) ? 'initial' : ( ( $dscFileTm === 10 ) ? 'forced' : 'timeoutExpired' );

				if( $dsc && !isset( $dsc[ 't' ] ) )
					$seraph_accel_g_prepOrigContHashPrev = ($dsc[ 'h' ]??null);

			}
			else if( $tmCur > $sessExpiration )
				$reason = 'userSessionExpired';
			else if( $seraph_accel_g_ctxCache -> isUserSess && $httpCacheControl == 'no-cache' && Gen::GetArrField( $settCache, array( 'ctxCliRefresh' ), false ) )
				$reason = 'forcedFromClient';

			if( $reason )
			{
				$ccs = _CacheContentStart( $tmCur, $procTmLim );
				if( $ccs )
				{
					if( $dscFileTm === 0 && !@touch( $seraph_accel_g_dscFile, 10 ) )
					{
						$lock -> Release();
						return( Gen::E_FAIL );
					}

					$lock -> Release();

					if( $lazyInv )
					{
						$isCip = false;
						$seraph_accel_g_lazyInvTmp = ( $seraph_accel_g_simpCacheMode !== null ) ? false : ( ( $reason === 'forced' ) ? ($settCache[ 'lazyInvForcedTmp' ]??null) : ($settCache[ 'lazyInvTmp' ]??null) );
					}
					else

					{

						if( $seraph_accel_g_prepPrms === null && $contProc && $seraph_accel_g_simpCacheMode === null )
						{
							$seraph_accel_g_cacheSkipData = array( 'revalidating-begin', array( 'reason' => $reason, 'dscFile' => substr( $seraph_accel_g_dscFile, strlen( $cacheRootPath ) ) ) );
							return( Gen::S_FALSE );
						}

						return( Gen::S_IO_PENDING );
					}
				}
				else
				{
					$lock -> Release();

					$isCip = true;

					if( !$lazyInv )

					{
						if( !( $dsc && isset( $dsc[ 't' ] ) ) && $seraph_accel_g_prepPrms === null )
						{
							if( $seraph_accel_g_simpCacheMode === null )
							{
								$seraph_accel_g_cacheSkipData = array( 'revalidating', array( 'reason' => $reason, 'dscFile' => substr( $seraph_accel_g_dscFile, strlen( $cacheRootPath ) ) ) );
								return( Gen::S_FALSE );
							}

							return( Gen::S_IO_PENDING );
						}
					}
				}
			}
			else
				$lock -> Release();
		}

		unset( $lock );
	}
	else
		$dsc = CacheReadDsc( $seraph_accel_g_dscFile );

	if( $seraph_accel_g_prepPrms === null )
	{

		if( $contProc && ( ( strlen( $userAgent ) > 100 ) || !MatchUserAgentExpressions( $userAgent, Gen::GetArrField( $sett, array( 'bots', 'agents' ), array() ), array( '@^\\^?([\\/\\~\\@\\;\\%\\`\\#])\\.[\\*\\+](?1)\\$?$@'  ) ) ) )
			lfjikztqjqji( $seraph_accel_g_siteId, $tmCur, true );

		$reasonOutputErr = null;
		if( !$dsc )
			$reasonOutputErr = 'brokenDsc';
		else
		{

			$hr = _ProcessOutCachedData( $seraph_accel_g_simpCacheMode === null, null, $settGlob, $sett, $settCache, $dsc, $dscFileTm, $tmCur, $isCip ? 'revalidating' : ( $isCip === false ? 'revalidating-begin' : 'cache' ), $reason, true );
			if( Gen::HrFail( $hr ) )
				return( $hr );

			if( $hr == Gen::S_FALSE )
				$reasonOutputErr = 'brokenData';
		}

		if( $reasonOutputErr )
		{
			$lock = new Lock( 'dl', $cacheRootPath );
			if( !$lock -> Acquire() )
				return( Gen::E_FAIL );

			@touch( $seraph_accel_g_dscFile, 0 );

			if( $isCip === false || _CacheContentStart( $tmCur, $procTmLim ) )
			{
				$lock -> Release();

				if( $contProc && $seraph_accel_g_simpCacheMode === null )
				{
					$seraph_accel_g_cacheSkipData = array( 'revalidating-begin', array( 'reason' => $reasonOutputErr, 'dscFile' => substr( $seraph_accel_g_dscFile, strlen( $cacheRootPath ) ) ) );
					return( Gen::S_FALSE );
				}

				return( Gen::S_IO_PENDING );
			}

			$lock -> Release();

			if( $seraph_accel_g_simpCacheMode === null )
			{
				$seraph_accel_g_cacheSkipData = array( 'revalidating', array( 'reason' => $reasonOutputErr, 'dscFile' => substr( $seraph_accel_g_dscFile, strlen( $cacheRootPath ) ) ) );
				return( Gen::S_FALSE );
			}

			return( Gen::S_IO_PENDING );
		}
	}
	else
		$hr = Gen::S_OK;

	if( $isCip === false )
	{
		if( $seraph_accel_g_prepPrms === null )
		{
			if( $bgEnabled = Gen::CloseCurRequestSessionForContinueBgWork() )
				CacheFem();

			if( $seraph_accel_g_simpCacheMode !== null )
			{
				if( !$bgEnabled )
					return( CacheSetCurRequestToPrepareAsync( null, false, false ) ? Gen::S_OK : Gen::S_FALSE );
			}
			else if( $contProc || !$bgEnabled )
				return( ( ( !($settCache[ 'updByTimeout' ]??null) && !$seraph_accel_g_lazyInvTmp  ) || CacheSetCurRequestToPrepareAsync( $contProc ? $seraph_accel_g_siteId : null, $seraph_accel_g_lazyInvTmp ? ( ($settCache[ 'updByTimeout' ]??null) ? true : 'only' ) : false, $bgEnabled ) ) ? Gen::S_OK : Gen::S_FALSE );

			$seraph_accel_g_noFo = true;
			return( Gen::S_IO_PENDING );
		}

		if( $dscFileTm && !($seraph_accel_g_prepPrms[ 'tmp' ]??null) && $dsc && isset( $dsc[ 't' ] ) )
		{
			$obj = new AnyObj();
			$obj -> cont = false;
			if( is_string( $dsc[ 't' ] ) )
				$obj -> cont = ReadSce( $seraph_accel_g_dataPath, $settCache, $dsc[ 't' ], 'html' );
			else if( $ctxData = CacheDscGetDataCtx( $settCache, $dsc, '', $seraph_accel_g_dataPath, $tmCur, 'html' ) )
			{
				$obj -> cont = CacheDscDataOutput( $ctxData, false );
				unset( $ctxData );
			}

			if( is_string( $obj -> cont ) )
			{
				{
					$posTmpTimeStamp = strrpos( $obj -> cont, '<!-- seraph-accel-tmpTimeStamp: ', -1 );
					if( $posTmpTimeStamp !== false )
						$obj -> cont = substr( $obj -> cont, 0, $posTmpTimeStamp );
				}

				$obj -> cb = function( $obj )
				{

					$obj -> cont = OnEarlyContentComplete( $obj -> cont );
					echo( _CbContentFinish( $obj -> cont ) );

					exit();
				};

				if( $obj -> cont )
				{

					_CacheStdHdrs( $seraph_accel_g_simpCacheMode === null, $seraph_accel_g_ctxCache, $settCache );
					add_action( 'wp_loaded', array( $obj, 'cb' ), -999999  );
					add_action( 'plugins_loaded',
						function()
						{
							Wp::RemoveFilters( 'plugins_loaded', 'linguiseFirstHook' );
						}
					, -999999 );
					return( Gen::S_FALSE );
				}
			}
		}

		$seraph_accel_g_noFo = true;
		return( Gen::S_IO_PENDING );
	}

	if( $seraph_accel_g_prepPrms !== null )
	{
		ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array_merge( array( 'finish' => true, 'skip' => $isCip ? 'alreadyProcessing' : 'alreadyProcessed' ), ($sett[ 'debugInfo' ]??null) ? array( 'infos' => array( LocId::Pack( 'SrvArgs' ) => PackKvArrInfo( $_SERVER ) ) ) : array() ), false, false );
		return( $hr );
	}

	return( $hr );
}

function _CacheStdHdrs( $allowExtCache, $ctxCache, $settCache )
{
	if( $allowExtCache && ( $ctxCache -> viewCompatId || $ctxCache -> isUserSess || !($settCache[ 'srv' ]??null) ) )
		$allowExtCache = false;

	if( $allowExtCache )
	{
		@header( 'Cache-Control: public, max-age=' . Gen::GetArrField( $settCache, array( 'srvShrdTtl' ), 3600 ) . ', s-maxage=' . Gen::GetArrField( $settCache, array( 'srvShrdTtl' ), 3600 ) );
	}
	else
	{
		@header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
		@header( 'Pragma: no-cache' );
	}

	@header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
	@header( 'Content-Type: text/html; charset=UTF-8' );
	@header( 'Vary: Accept-Encoding', false );
}

function _ProcessOutHdrTrace( $sett, $bHdr, $bLog, $state, $data = null, $dscFile = null )
{
	if( $bHdr && !($sett[ 'hdrTrace' ]??null) )
		$bHdr = false;

	$userAgent = null;
	if( $bLog )
		$userAgent = ( isset( $_SERVER[ 'SERAPH_ACCEL_ORIG_USER_AGENT' ] ) ? $_SERVER[ 'SERAPH_ACCEL_ORIG_USER_AGENT' ] : ($_SERVER[ 'HTTP_USER_AGENT' ]??'') );

	if( $bLog && ( !($sett[ 'log' ]??null) || !($sett[ 'logScope' ][ 'request' ]??null) ) )
		$bLog = false;
	if( $bLog && $state === 'skipped' )
	{
		if( !($sett[ 'logScope' ][ 'requestSkipped' ]??null) )
			$bLog = false;
		else if( !($sett[ 'logScope' ][ 'requestSkippedAdmin' ]??null) && Gen::GetArrField( $data, array( 'reason' ) ) === 'admin' )
			$bLog = false;
	}
	if( $bLog && !($sett[ 'logScope' ][ 'requestBots' ]??null) && MatchUserAgentExpressions( strtolower( $userAgent ), Gen::GetArrField( $sett, array( 'bots', 'agents' ), array() ) ) )
		$bLog = false;

	$debugInfo = ' state=' . $state . ';';
	if( $dscFile )
		$debugInfo .= ' dscFile="' . substr( $dscFile, strlen( GetCacheDir() ) ) . '";';

	if( is_array( $data ) )
		foreach( $data as $dataK => $dataV )
		{
			$v = '';
			switch( gettype( $dataV ) )
			{
			case 'array':		$v = @json_encode( $dataV, JSON_INVALID_UTF8_IGNORE ); break;
			case 'string':		$v = '"' . $dataV . '"'; break;
			case 'boolean':		$v = $dataV ? 'true' : 'false'; break;
			default:			$v .= $dataV; break;
			}

			$debugInfo .= ' ' . $dataK . '=' . $v . ';';
		}

	if( $bHdr )
		@header( 'X-Seraph-Accel-Cache: 2.28.15;' . $debugInfo );

	if( $bLog )
	{
		$txt = $debugInfo . ' URL: ' . GetCurRequestUrl() . '; Agent: ' . $userAgent . '; IP: ' . ($_SERVER[ 'REMOTE_ADDR' ]??'<UNK>');

		LogWrite( $txt, Ui::MsgInfo, 'HTTP trace' );
	}
}

function _ProcessOutCachedData( $allowExtCache, $objSubType, $settGlob, $sett, $settCache, $dsc, $dscFileTm, $tmCur, $stateValidate, $reason, $out, &$output = null )
{
	global $seraph_accel_g_dscFile;
	global $seraph_accel_g_dscFilePending;
	global $seraph_accel_g_dataPath;
	global $seraph_accel_g_ctxCache;
	global $seraph_accel_g_simpCacheMode;

	if( $objSubType === null && is_string( $seraph_accel_g_simpCacheMode ) && Gen::StrStartsWith( ( string )$seraph_accel_g_simpCacheMode, 'data:' ) )
		$objSubType = substr( $seraph_accel_g_simpCacheMode, 5 );

	$bNotMdf = false;

	if( Gen::GetArrField( $settGlob, array( 'cache', 'chkNotMdfSince' ), false ) )
	{
		$hash = null;
		$tmLm = $dscFileTm;
		if( $tmLm < 60 )
		{
			$tmLm = Gen::FileMTime( $seraph_accel_g_dscFilePending );
			if( $tmLm === false )
				$tmLm = $tmCur;
		}

		{
			$tm2 = ( int )Gen::GetArrField( $settGlob, array( '_LM', 'cache', 'chkNotMdfSince' ) );
			if( $tmLm < $tm2 )
				$tmLm = $tm2;
			unset( $tm2 );
		}

		if( isset( $dsc[ 'h' ] ) )
		{
			$hash = $dsc[ 'h' ];
			foreach( ( array )($dsc[ 'p' ]??null) as $oiCi )
				$hash .= GetCacheCh( $oiCi, true );
			$hash = md5( $hash );
		}

		if( isset( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) && $hash )
			$bNotMdf = trim( $_SERVER[ 'HTTP_IF_NONE_MATCH' ], " \t\n\r\0\x0B\"" ) == $hash;
		else if( isset( $_SERVER[ 'HTTP_IF_MODIFIED_SINCE' ] ) )
		{
			$tmIfMdfSince = Net::GetTimeFromHdrVal( $_SERVER[ 'HTTP_IF_MODIFIED_SINCE' ] );

			if( $seraph_accel_g_ctxCache -> viewStateId )
			{
				$tmLm = ( $tmCur == $tmLm ) ? ( $tmCur - 1 ) : $tmCur;
				$bNotMdf = ( $tmIfMdfSince == $tmLm );
			}
			else
				$bNotMdf = ( $tmIfMdfSince >= $tmLm );
		}
	}

	if( $bNotMdf )
	{
		http_response_code( 304 );
		$ctxData = null;
	}
	else
	{
		$encoding = '';

		$acceptEncodings = array_map( 'trim', explode( ',', strtolower( ($_SERVER[ 'HTTP_ACCEPT_ENCODING' ]??'') ) ) );
		{
			$acceptEncodingsRaw = $acceptEncodings;
			$acceptEncodings = array();
			foreach( $acceptEncodingsRaw as $acceptEncodingRaw )
			{
				$parts = array_map( 'trim', explode( ';', $acceptEncodingRaw ) );
				if( count( $parts ) )
				{
					$parts = $parts[ 0 ];
					if( $parts != 'br' || IsBrotliAvailable() )
						$acceptEncodings[ $parts ] = true;
				}
			}

			unset( $parts );
			unset( $acceptEncodingsRaw );
			unset( $acceptEncodingRaw );
		}

		$encs = Gen::GetArrField( $settCache, array( 'encs' ), array() );

		{
			foreach( $encs as $enc )
			{
				if( $enc === '' )
					continue;

				if( ($acceptEncodings[ $enc ]??null) )
				{
					$encoding = $enc;
					break;
				}
			}
		}
		unset( $encs );
		unset( $acceptEncodings );

		if( $encoding === 'compress' )
			$encoding = '';

		if( !$out )
			$encoding = '';

		$ctxData = CacheDscGetDataCtx( $settCache, $dsc, $encoding, $seraph_accel_g_dataPath, $tmCur, $objSubType === null ? 'html' : $objSubType );
		if( !$ctxData || ( $objSubType === null && !CacheDscValidateDepsData( $sett, $dsc, $seraph_accel_g_dataPath ) ) )
		{

			@unlink( $seraph_accel_g_dscFile );

			return( Gen::S_FALSE );
		}

		if( !defined( 'SERAPH_ACCEL_ADVCACHE_COMP' ) )
		{
			if( $encoding )
			{
				@ini_set( 'zlib.output_compression', 'Off' );
				@ini_set( 'brotli.output_compression', 'Off' );
			}

			if( Gen::GetArrField( $settGlob, array( 'cache', 'cntLen' ), false ) && $ctxData[ 'contentLen' ] !== null )
				@header( 'Content-Length: '. $ctxData[ 'contentLen' ] );

			if( $encoding )
				@header( 'Content-Encoding: ' . $encoding );
		}
	}

	if( $objSubType === null )
		_CacheStdHdrs( $allowExtCache, $seraph_accel_g_ctxCache, $settCache );
	else
	{
		switch( $objSubType )
		{
		case 'css':		$objSubType = 'text/css; charset=UTF-8'; break;
		case 'js':		$objSubType = 'application/javascript; charset=UTF-8'; break;
		case 'json':	$objSubType = 'application/json; charset=UTF-8'; break;
		case 'xml':		$objSubType = 'text/xml; charset=UTF-8'; break;
		case 'txt':		$objSubType = 'text/plain; charset=UTF-8'; break;
		case 'bin':		$objSubType = 'application/octet-stream'; break;
		case 'rss':		$objSubType = 'application/rss+xml; charset=UTF-8'; break;
		default:		$objSubType = 'text/html; charset=UTF-8'; break;
		}

		@header( 'Content-Type: ' . $objSubType );
	}

	if( Gen::GetArrField( $settGlob, array( 'cache', 'chkNotMdfSince' ), false ) )
	{
		if( $hash )
			@header( 'ETag: "' . $hash . '"' );
		@header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $tmLm ) . ' GMT' );
	}

	foreach( Gen::GetArrField( $dsc, array( 'hd' ), array() ) as $hdr )
		@header( $hdr );

	if( ($sett[ 'hdrTrace' ]??null) || ( $objSubType === null && ($sett[ 'log' ]??null) && ($sett[ 'logScope' ][ 'request' ]??null) ) )
	{
		$debugData = array();
		if( $reason )
			$debugData[ 'reason' ] = $reason;
		if( isset( $dsc[ 't' ] ) )
			$debugData[ 'cacheTmp' ] = true;
		$debugData[ 'date' ] = gmdate( 'Y-m-d H:i:s', $dscFileTm );
		$debugData[ 'dscFile' ] = substr( $seraph_accel_g_dscFile, strlen( GetCacheDir() ) );

		if( $ctxData )
			$debugData = array_merge( $debugData, array_filter( $ctxData, function( $k ) { return( in_array( $k, array( 'encoding', 'recompress', 'compressedEncoding', 'sizeRaw', 'size' ) ) ); }, ARRAY_FILTER_USE_KEY ), array( 'parts' => count( $ctxData[ 'oiFs' ] ) ),
				($sett[ 'debugInfo' ]??null) ? array( 'PLG_DIR' => __DIR__, '_SERVER' => $_SERVER ) : array()
			);

		_ProcessOutHdrTrace( $sett, true, $objSubType === null, $stateValidate, $debugData );
	}

	if( $bNotMdf )
		return( Gen::S_OK );

	$output = CacheDscDataOutput( $ctxData, $out );
	if( $output !== false )
		return( Gen::S_OK );

	@unlink( $seraph_accel_g_dscFile );
	return( Gen::E_FAIL );
}

function _GetCcf( $settCache, $oiCi, $encoding, $dataPath, $tmUpdate, $type, $dataComprExts )
{
	$ext = _GetDataFileEncExt( $encoding, true );
	if( $ext === null || !in_array( $ext, $dataComprExts ) )
		return( null );

	if( $type != 'html' )
		$dataPath .= '/' . $type;

	$oiCf = CacheCgf( $settCache, $dataPath, $oiCi, $type, $ext );

	@touch( $oiCf, $tmUpdate );
	return( array( 'path' => $oiCf, 'fmt' => $ext ) );
}

function _GetCfc( $oiCf, $out = false )
{
	if( !$out )
		return( @file_get_contents( $oiCf[ 'path' ] ) );

	$file = @fopen( $oiCf[ 'path' ], 'rb' );
	if( !$file )
		return( false );

	while( !@feof( $file ) && ( @connection_status() == 0 ) )
		CacheWriteOut( @fread( $file, 0x10000 ) );

	return( true );
}

function CacheDscGetDataCtxFirstFile( $settCache, $oiCi, &$ctxData, $dataPath, $tmUpdate, $type, $dataComprExts )
{
	$encoding = $ctxData[ 'encoding' ];

	$oiCf = _GetCcf( $settCache, $oiCi, $encoding, $dataPath, $tmUpdate, $type, $dataComprExts );
	if( $oiCf )
	{
		$ctxData[ 'compressedEncoding' ] = $encoding;
		return( $oiCf );
	}

	$ctxData[ 'recompress' ] = true;

	$encodings = array( '', 'gzip', 'deflate', 'compress', 'br' );
	if( !in_array( $encoding, $encodings ) )
		return( null );

	foreach( $encodings as $encoding )
	{
		$oiCf = _GetCcf( $settCache, $oiCi, $encoding, $dataPath, $tmUpdate, $type, $dataComprExts );
		if( $oiCf )
		{
			$ctxData[ 'compressedEncoding' ] = $encoding;
			return( $oiCf );
		}
	}

	return( null );
}

function CacheDscGetDataCtx( $settCache, $dsc, $encoding, $dataPath, $tmUpdate, $type )
{
	$oiCs = ($dsc[ 'p' ]??null);
	if( !is_array( $oiCs )  )
	{

		return( null );
	}

	$dataComprExts = Gen::GetArrField( $settCache, array( 'dataCompr' ), array() );
	if( empty( $dataComprExts ) )
		$dataComprExts[] = '';
	foreach( $dataComprExts as &$dataComprExt )
		$dataComprExt = _GetDataFileComprExt( $dataComprExt );

	$ctxData = array( 'encoding' => $encoding, 'recompress' => false, 'oiFs' => array() );
	if( $oiCs )
	{
		$oiCi = $oiCs[ 0 ];

		$oiCf = CacheDscGetDataCtxFirstFile( $settCache, $oiCi, $ctxData, $dataPath, $tmUpdate, $type, $dataComprExts );
		if( !$oiCf )
		{

			return( null );
		}

		$ctxData[ 'fmt' ] = $oiCf[ 'fmt' ];
	}
	else
		$ctxData[ 'fmt' ] = '';

	$fmt = $ctxData[ 'fmt' ];

	if( !$ctxData[ 'recompress' ] )
	{
		switch( $encoding )
		{
		case 'deflate':
			if( $fmt != '.deflu' )
				$ctxData[ 'recompress' ] = true;
			break;

		case 'compress':
			if( $fmt != '.deflu' )
				$ctxData[ 'recompress' ] = true;
			break;

		case 'gzip':
			if( $fmt != '.deflu' && count( $oiCs ) > 1 )
				$ctxData[ 'recompress' ] = true;
			break;

		case 'br':
			if( $fmt != '.brua' && count( $oiCs ) > 1 )
				$ctxData[ 'recompress' ] = true;
			break;

		}
	}

	if( defined( 'SERAPH_ACCEL_ADVCACHE_COMP' ) )
	{
		$ctxData[ 'recompress' ] = true;
		$encoding = '';
	}

	$recompress = $ctxData[ 'recompress' ];

	$size = 0;
	$contentLen = 0;
	$sizeRaw = 0;
	$content = '';

	for( $i = 0; $i < count( $oiCs ); $i++ )
	{
		if( $i )
		{
			$oiCi = $oiCs[ $i ];

			$oiCf = _GetCcf( $settCache, $oiCi, $ctxData[ 'compressedEncoding' ], $dataPath, $tmUpdate, $type, $dataComprExts );
			if( !$oiCf )
			{

				return( null );
			}
		}

		$ctxData[ 'oiFs' ][] = $oiCf;
		$oiCos = GetCacheCos( $oiCi );
		$size += $oiCos;

		if( $recompress )
		{
			$oiCd = _GetCfc( $oiCf );
			if( $oiCd === false || !CacheCvs( strlen( $oiCd ), $oiCos ) )
			{

				return( null );
			}

			$sizeRaw += strlen( $oiCd );

			switch( $fmt )
			{
			case '.gz':				$oiCd = @gzdecode( $oiCd ); break;
			case '.deflu':		$oiCd = @gzinflate( $oiCd . "\x03\0" ); break;
			case '.br':				$oiCd = Gen::CallFunc( 'brotli_uncompress', array( $oiCd ), false ); break;
			case '.brua':		$oiCd = Gen::CallFunc( 'brotli_uncompress', array( "\x6b\x00" . $oiCd . "\x03" ), false ); break;
			}

			if( $oiCd === false )
			{

				return( null );
			}

			$content .= $oiCd;
		}
		else
		{
			$oiCfs = @filesize( $oiCf[ 'path' ] );
			if( !CacheCvs( $oiCfs, $oiCos ) )
			{

				return( null );
			}
			$contentLen += $oiCfs;
		}

	}

	if( !$recompress )
	{
		switch( $encoding )
		{
		case 'deflate':
			if( $fmt == '.deflu' )
				$contentLen += 2;
			break;

		case 'compress':
			if( $fmt == '.deflu' )
				$contentLen += 2 + 2 + 4;
			break;

		case 'gzip':
			if( $fmt == '.deflu' )
				$contentLen += 10 + 2 + 4 + 4;
			break;

		case 'br':
			if( $fmt == '.brua' )
				$contentLen += 2 + 1;
			break;
		}

		$sizeRaw = $contentLen;
	}
	else
	{
		switch( $encoding )
		{
		case 'deflate':		$content = @gzdeflate( $content, 6 ); break;
		case 'compress':	$content = @gzcompress( $content, 6 ); break;
		case 'gzip':		$content = @gzencode( $content, 6 ); break;
		case 'br':			$content = Gen::CallFunc( 'brotli_compress', array( $content, 7 ), false ); break;
		}

		if( $content === false )
		{

			return( null );
		}

		$contentLen = strlen( $content );
	}

	$ctxData[ 'content' ] = $content;
	$ctxData[ 'contentLen' ] = $contentLen;
	$ctxData[ 'size' ] = $size;
	$ctxData[ 'sizeRaw' ] = $sizeRaw;
	$ctxData[ 'crc32' ] = $dsc[ 'c' ];
	$ctxData[ 'adler32' ] = $dsc[ 'a' ];
	return( $ctxData );
}

function CacheDscValidateDepsData( $sett, $dsc, $dataPath )
{
	static $g_aaCheckExt = array( 'css' => array( 'css' ), 'js' => array( 'js' ), 'img' => array( 'jpe','jpg','jpeg','png','gif','bmp', 'tiff', 'svg', 'webp','avif'  ) );

	$settCache = Gen::GetArrField( $sett, array( 'cache' ), array() );

	foreach( Gen::GetArrField( $dsc, array( 's' ), array() ) as $childType => $children )
	{
		$aCheckExt = ($g_aaCheckExt[ $childType ]??null);
		if( !$aCheckExt )
			continue;

		$dataPathSubType = $dataPath . '/' . $childType;
		foreach( $children as $childId )
		{
			$found = false;
			foreach( $aCheckExt as $fileExt )
			{
				if( !@file_exists( CacheCgf( $settCache, $dataPathSubType, $childId, $fileExt ) ) )
					continue;

				$found = true;
				break;
			}

			if( !$found )
			{
				if( ($sett[ 'log' ]??null) )
					LogWrite( 'Descriptor child "' . $childType . '" not found: ' . $childId, Ui::MsgErr, 'Errors' );
				return( false );
			}
		}
	}

	foreach( Gen::GetArrField( $dsc, array( 'b' ), array() ) as $idSubPart => $dscPart )
		if( !CacheDscValidateDepsData( $sett, $dscPart, $dataPath ) )
			return( false );

	return( true );
}

function CacheDscDataOutput( $ctxData, $out = true )
{
	$iubyvadkxs = $ctxData[ 'oiFs' ];
	$encoding = $ctxData[ 'encoding' ];
	$recompress = $ctxData[ 'recompress' ];
	$fmt = $ctxData[ 'fmt' ];

	if( $recompress )
	{
		$content = $ctxData[ 'content' ];
		if( !$out )
			return( $content );

		CacheWriteOut( $content );
		return( true );
	}

	$content = '';

	switch( $encoding )
	{
	case 'deflate':
		break;

	case 'compress':
		if( $fmt == '.deflu' )
		{
			$oiCd = "\x78\xDA";
			if( $out )
				CacheWriteOut( $oiCd );
			else
				$content .= $oiCd;
		}
		break;

	case 'gzip':
		if( $fmt == '.deflu' )
		{
			$oiCd = "\x1F\x8B\x08\0\0\0\0\0\x02\x0A";
			if( $out )
				CacheWriteOut( $oiCd );
			else
				$content .= $oiCd;
		}
		break;

	case 'br':
		if( $fmt == '.brua' )
		{
			$oiCd = "\x6b\x00";
			if( $out )
				CacheWriteOut( $oiCd );
			else
				$content .= $oiCd;
		}
		break;
	}

	foreach( $iubyvadkxs as $oiCf )
	{

		$oiCd = _GetCfc( $oiCf, $out );
		if( $oiCd === false )
			return( false );

		if( !$out )
			$content .= $oiCd;

	}

	switch( $encoding )
	{
	case 'deflate':
		if( $fmt == '.deflu' )
		{
			$oiCd = "\x03\0";
			if( $out )
				CacheWriteOut( $oiCd );
			else
				$content .= $oiCd;
		}
		break;

	case 'compress':
		if( $fmt == '.deflu' )
		{
			$oiCd = "\x03\0" . $ctxData[ 'adler32' ];
			if( $out )
				CacheWriteOut( $oiCd );
			else
				$content .= $oiCd;
		}
		break;

	case 'gzip':
		if( $fmt == '.deflu' )
		{
			$oiCd = "\x03\0" . $ctxData[ 'crc32' ] . pack( 'V', $ctxData[ 'size' ] );
			if( $out )
				CacheWriteOut( $oiCd );
			else
				$content .= $oiCd;
		}
		break;

	case 'br':
		if( $fmt == '.brua' )
		{
			$oiCd = "\x03";
			if( $out )
				CacheWriteOut( $oiCd );
			else
				$content .= $oiCd;
		}
		break;
	}

	if( !$out )
		return( $content );

	CacheWriteOut( $content );
	return( true );
}

function CacheWriteOut( $data )
{

	print( $data );
}

function CacheDscWriteCancel( $dscDel = true, $updTime = false )
{
	global $seraph_accel_g_dscFile;
	global $seraph_accel_g_dscFilePending;

	if( $updTime )
		@touch( $seraph_accel_g_dscFile );

	@unlink( $seraph_accel_g_dscFilePending );
	if( Gen::GetFileExt( $seraph_accel_g_dscFilePending ) == 'pp' )
		@unlink( substr( $seraph_accel_g_dscFilePending, 0, -1 ) );

	if( $dscDel && !$updTime )
	{

		@unlink( $seraph_accel_g_dscFile );
	}
}

function _CacheSetRequestToPrepareAsyncEx( $siteId, $method, $url, $hdrs, $tmp = false )
{

	if( !$siteId )
	{
		$urlProc = ProcessQueueItemCtx::AdjustRequestUrl( $url, Gen::GetCurRequestTime(), array() );

		$asyncMode = OnAsyncTasksPushGetMode();
		if( $asyncMode == 'ec' )
			ExtCron_AddUrlToQueue( $method, $urlProc, $hdrs );
		else

			ProcessQueueItemCtx::MakeRequest( $asyncMode, $method, $urlProc, $hdrs );
		return;
	}

	if( $tmp )
	{
		$urlProc = ProcessQueueItemCtx::AdjustRequestUrl( $url, Gen::GetCurRequestTime(), array( 'tmp' => true ) );

		$asyncMode = OnAsyncTasksPushGetMode();
		if( $asyncMode == 'ec' )
			ExtCron_AddUrlToQueue( $method, $urlProc, $hdrs );
		else

			ProcessQueueItemCtx::MakeRequest( $asyncMode, $method, $urlProc, $hdrs );
	}

	if( $tmp != 'only' && CachePostPreparePageEx( $method, $url, $siteId, 10, null, $hdrs ) )
		CachePushQueueProcessor();
}

function CacheSetCurRequestToPrepareAsync( $siteId, $tmp = false, $bgEnabled = false, $early = true )
{
	global $seraph_accel_g_simpCacheMode;

	$obj = new AnyObj();
	$obj -> method = strtoupper( ($_SERVER[ 'REQUEST_METHOD' ]??'GET') );
	$obj -> url = GetCurRequestUrl();
	if( $obj -> method == 'POST' )
	{
		$aRequestArg = array(); AddCurPostArgs( $aRequestArg );
		$obj -> url = Net::UrlAddArgs( $obj -> url, $aRequestArg );
	}

	$obj -> hdrs = Net::GetRequestHeaders();
	if( isset( $_SERVER[ 'SERAPH_ACCEL_ORIG_USER_AGENT' ] ) )
		$obj -> hdrs[ 'User-Agent' ] = $_SERVER[ 'SERAPH_ACCEL_ORIG_USER_AGENT' ];

	if( Gen::StrStartsWith( ( string )$seraph_accel_g_simpCacheMode, 'fragments' ) )
		$obj -> url = Net::UrlAddArgs( $obj -> url, array( 'seraph_accel_gf' => substr( $seraph_accel_g_simpCacheMode, 10 ) ) );

	if( !$bgEnabled && $siteId && !$tmp && $seraph_accel_g_simpCacheMode === null )
	{
		Gen::MakeDir( $fileTempQueue = GetCacheDir() . '/qt', true );
		if( $fileTempQueue = tempnam( $fileTempQueue, '' ) )
		{
			if( @file_put_contents( $fileTempQueue, @serialize( array( 'u' => $obj -> url, 's' => $siteId, 'p' => 10, 'h' => $obj -> hdrs, 't' => microtime( true ) ) ) ) !== false )
			{

				if( @rename( $fileTempQueue, $fileTempQueue . '.dat' ) )
				{

					return( true );
				}
				else
					@unlink( $fileTempQueue );
			}
			else
				@unlink( $fileTempQueue );
		}
	}

	if( !$early )
	{
		_CacheSetRequestToPrepareAsyncEx( $siteId, $obj -> method, $obj -> url, $obj -> hdrs, $tmp );
		return( false );
	}

	$obj -> siteId = $siteId;
	$obj -> tmp = $tmp;
	$obj -> cb = function( $obj ) { _CacheSetRequestToPrepareAsyncEx( $obj -> siteId, $obj -> method, $obj -> url, $obj -> hdrs, $obj -> tmp ); };
	add_action( 'muplugins_loaded', array( $obj, 'cb' ) , 0 );

	if( Wp::IsCronEnabled() )
		add_action( 'wp_loaded', function() { if( Wp::GetFilters( 'init', 'wp_cron' ) ) wp_cron(); exit(); }, -999999 );
	else
		add_action( 'muplugins_loaded', function() { exit(); }, 1 );

	return( false );
}

function _CacheContentStart( $tmCur, $procTmLim )
{
	global $seraph_accel_g_dscFile;
	global $seraph_accel_g_dscFilePending;

	for( $try = 1; $try <= 2; $try++ )
	{
		$stm = null;
		$hr = Gen::FileOpenWithMakeDir( $stm, $seraph_accel_g_dscFilePending, 'x' );
		if( $stm )
		{
			@fclose( $stm );
			break;
		}

		if( $try == 2 )
			return( false );

		$dscFilePendingTm = Gen::FileMTime( $seraph_accel_g_dscFilePending );
		if( $dscFilePendingTm !== false && ( $tmCur - $dscFilePendingTm < $procTmLim ) )
			return( false );

		@unlink( $seraph_accel_g_dscFilePending );
	}

	return( true );
}

function _CbContentProcess( $content )
{
	if( !function_exists( 'seraph_accel\\OnEarlyContentComplete' ) )
		return( $content );
	return( OnEarlyContentComplete( $content, true ) );
}

function _CbContentFinishSkip( $content )
{
	global $seraph_accel_g_dscFile;
	global $seraph_accel_g_dscFilePending;
	global $seraph_accel_g_dataPath;
	global $seraph_accel_g_cacheSkipData;
	global $seraph_accel_g_prepPrms;
	global $seraph_accel_g_lazyInvTmp;
	global $seraph_accel_g_bPrepContTmpToMain;
	global $seraph_accel_g_prepOrigContHash;
	global $seraph_accel_g_prepOrigCont;
	global $seraph_accel_g_cacheObjChildren;
	global $seraph_accel_g_cacheObjSubs;
	global $seraph_accel_g_siteId;
	global $seraph_accel_g_prepCont;
	global $seraph_accel_g_simpCacheMode;

	$sett = Plugin::SettGet();
	$settGlob = Plugin::SettGetGlobal();
	$settCache = Gen::GetArrField( $sett, array( 'cache' ), array() );

	@ignore_user_abort( true );

	$skipStatus = Gen::GetArrField( ($seraph_accel_g_cacheSkipData[ 1 ]??null), array( 'reason' ), '' );

	if( ($seraph_accel_g_prepPrms[ 'selfTest' ]??null) )
	{
		$content = 'selfTest-' . $seraph_accel_g_prepPrms[ 'selfTest' ];
		sleep( 5 );
	}

	if( $seraph_accel_g_cacheSkipData[ 0 ] === 'revalidating-begin' )
	{
		$beginPrepare = false;
		$updateTmpCache = false;
		$skipStatus = null;

		$cacheSkipData = $seraph_accel_g_cacheSkipData;
		$seraph_accel_g_cacheSkipData = null;
		if( !GetContCacheEarlySkipData( $pathOrig, $path, $pathIsDir, $args ) )
		{
			if( $skipStatus = ContProcGetSkipStatus( $content ) )
			{
				$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => $skipStatus ) );
				if( $skipStatus == 'httpCode:500' || Gen::StrStartsWith( $skipStatus, 'err:php' ) )
					$beginPrepare = true;
			}
			else
			{
				$seraph_accel_g_cacheSkipData = $cacheSkipData;
				$beginPrepare = true;
				$updateTmpCache = $seraph_accel_g_lazyInvTmp;
			}
		}

		if( $beginPrepare )
		{
			if( $updateTmpCache )
			{
				$seraph_accel_g_dscFilePending = $seraph_accel_g_dscFilePending . 'p';

				$lock = new Lock( 'dl', GetCacheDir() );
				if( $seraph_accel_g_dataPath && $lock -> Acquire() )
				{
					if( _CacheContentStart( Gen::GetCurRequestTime(), Gen::GetArrField( $settCache, array( 'procTmLim' ), 570 ) ) )
					{
						$lock -> Release();

						CacheDscUpdate( $lock, $settCache, $content, $seraph_accel_g_cacheObjChildren, $seraph_accel_g_cacheObjSubs, $seraph_accel_g_dataPath, $seraph_accel_g_bPrepContTmpToMain ? false : true, $seraph_accel_g_prepOrigCont, $seraph_accel_g_prepOrigContHash );
						CacheAdditional_UpdateCurUrl( $settCache, true );
					}
					else
						$lock -> Release();
				}
				unset( $lock );
				$seraph_accel_g_dscFilePending = substr( $seraph_accel_g_dscFilePending, 0, -1 );

				if( Gen::GetArrField( $settCache, array( 'fastTmpOpt' ), false ) && Gen::GetArrField( $settCache, array( 'chunks', 'enable' ) ) )
					ContentMarkSeparateSofter( $content, true );
			}

			if( ($settCache[ 'updByTimeout' ]??null) )
				CacheSetCurRequestToPrepareAsync( $seraph_accel_g_siteId, false, false, false );
		}
		else
		{

			CacheDscWriteCancel( true, $skipStatus === 'notChanged' );
			if( $skipStatus !== 'notChanged' )
				CacheAdditional_UpdateCurUrl( $settCache );
		}
	}

	if( $skipStatus === 'notChanged' )
	{
		$content = '';
		if( $dsc = CacheReadDsc( $seraph_accel_g_dscFile ) )
		{
			$dscFileTm = Gen::FileMTime( $seraph_accel_g_dscFile );
			_ProcessOutCachedData( $seraph_accel_g_simpCacheMode === null, null, $settGlob, $sett, $settCache, $dsc, $dscFileTm, $dscFileTm, 'revalidated', 'notChanged', false, $content );
		}
		else
			_ProcessOutHdrTrace( $sett, true, true, 'skipped', array( 'reason' => 'brokenDsc' ), $seraph_accel_g_dscFile );
	}
	else
		_ProcessOutHdrTrace( $sett, true, true, $seraph_accel_g_cacheSkipData[ 0 ], ($seraph_accel_g_cacheSkipData[ 1 ]??null) );

	if( Gen::GetArrField( $settCache, array( 'chunks', 'enable' ) ) && GetContentProcessorForce( $sett ) )
		ContentMarkSeparateSofter( $content, false );

	if( $seraph_accel_g_prepPrms !== null )
	{
		ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array_merge( array( 'finish' => true, 'skip' => $skipStatus ), ($sett[ 'debugInfo' ]??null) ? array( 'infos' => array( LocId::Pack( 'SrvArgs' ) => PackKvArrInfo( $_SERVER ) ) ) : array() ), false, false );

		$httpCode = http_response_code();
		if( $httpCode >= 300 && $httpCode < 400 )
			http_response_code( 200 );
	}

	return( $content );
}

function _CbContentFinish( $content )
{
	global $post;

	global $seraph_accel_g_dscFile;
	global $seraph_accel_g_dscFilePending;
	global $seraph_accel_g_dataPath;
	global $seraph_accel_g_noFo;
	global $seraph_accel_g_cacheObjChildren;
	global $seraph_accel_g_cacheObjSubs;
	global $seraph_accel_g_prepPrms;
	global $seraph_accel_g_prepCont;
	global $seraph_accel_g_bPrepContTmpToMain;
	global $seraph_accel_g_prepOrigContHash;
	global $seraph_accel_g_prepOrigCont;
	global $seraph_accel_g_prepLearnId;
	global $seraph_accel_g_simpCacheMode;
	global $seraph_accel_g_ctxProcess;

	$sett = Plugin::SettGet();
	$settGlob = Plugin::SettGetGlobal();
	$settCache = Gen::GetArrField( $sett, array( 'cache' ), array() );

	$skipStatus = ContProcGetSkipStatus( $content );
	if( !$skipStatus && ContentProcess_IsAborted() )
		$skipStatus = 'aborted';

	$asyncMode = OnAsyncTasksPushGetMode( $settGlob );

	if( $skipStatus )
	{
		if( $skipStatus == 'noHdrOrBody'  && ( $asyncMode == 'ec' || ($settGlob[ 'asyncSmpOpt' ]??null) ) )
		{
			$urlCur = GetCurRequestUrl();
			if( !Gen::StrEndsWith( $urlCur, '/' ) )
				$skipStatus = 'httpCode:301:' . rawurlencode( $urlCur . '/' );
		}

		if( $seraph_accel_g_prepPrms !== null )
		{

			$httpCode = http_response_code();
			if( $httpCode >= 300 && $httpCode < 400 )
				http_response_code( 200 );

		}

		if( !$seraph_accel_g_noFo && $skipStatus !== 'notChanged' )
			_ProcessOutHdrTrace( $sett, true, true, 'skipped', array( 'reason' => $skipStatus ) );

		CacheDscWriteCancel( $skipStatus !== 'aborted' && !Gen::StrStartsWith( $skipStatus, 'lrnNeed' ), $skipStatus === 'notChanged' );

		if( $skipStatus !== 'aborted' && !Gen::StrStartsWith( $skipStatus, 'lrnNeed' ) && $skipStatus !== 'notChanged' )
			CacheAdditional_UpdateCurUrl( $settCache );

		if( $seraph_accel_g_prepPrms !== null )
			ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array_merge( array( 'finish' => true, 'skip' => $skipStatus ), ($sett[ 'debugInfo' ]??null) ? array( 'infos' => array( LocId::Pack( 'SrvArgs' ) => PackKvArrInfo( $_SERVER ) ) ) : array() ), false, false );

		if( Gen::GetArrField( $settCache, array( 'chunks', 'enable' ) ) && GetContentProcessorForce( $sett ) )
			ContentMarkSeparateSofter( $content, false );

		if( $seraph_accel_g_noFo )
			return( '' );

		if( $skipStatus === 'notChanged' )
		{
			$content = '';
			if( $dsc = CacheReadDsc( $seraph_accel_g_dscFile ) )
			{
				$dscFileTm = Gen::FileMTime( $seraph_accel_g_dscFile );
				_ProcessOutCachedData( $seraph_accel_g_simpCacheMode === null, null, $settGlob, $sett, $settCache, $dsc, $dscFileTm, $dscFileTm, 'revalidated', 'notChanged', false, $content );
			}
			else
				_ProcessOutHdrTrace( $sett, true, true, 'skipped', array( 'reason' => 'brokenDsc' ), $seraph_accel_g_dscFile );
		}

		return( $content );
	}

	$lock = new Lock( 'dl', GetCacheDir() );
	$dsc = CacheDscUpdate( $lock, $settCache, $content, $seraph_accel_g_cacheObjChildren, $seraph_accel_g_cacheObjSubs, $seraph_accel_g_dataPath, $seraph_accel_g_bPrepContTmpToMain ? false : Gen::GetArrField( $seraph_accel_g_prepPrms, array( 'tmp' ) ), $seraph_accel_g_prepOrigCont, $seraph_accel_g_prepOrigContHash, $seraph_accel_g_prepLearnId );
	unset( $lock );

	if( !$dsc )
	{
		$skipStatus = 'dscFileUpdateError';

		if( !$seraph_accel_g_noFo )
			_ProcessOutHdrTrace( $sett, true, true, 'skipped', array( 'reason' => $skipStatus ), $seraph_accel_g_dscFile );

		if( $seraph_accel_g_prepPrms !== null )
		{
			if( Gen::LastErrDsc_Is() )
				$skipStatus .= ':' . rawurlencode( Gen::LastErrDsc_Get() );
			ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array_merge( array( 'finish' => true, 'skip' => $skipStatus ), ($sett[ 'debugInfo' ]??null) ? array( 'infos' => array( LocId::Pack( 'SrvArgs' ) => PackKvArrInfo( $_SERVER ) ) ) : array() ), false, false );
		}

		return( $content );
	}

	CacheAdditional_UpdateCurUrl( $settCache, true );

	if( $seraph_accel_g_prepPrms !== null )
		ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array_merge( array( 'finish' => true, 'warns' => LastWarnDscs_Get() ), ($sett[ 'debugInfo' ]??null) ? array( 'infos' => array( LocId::Pack( 'ProcStat' ) => PackKvArrInfo( ($seraph_accel_g_ctxProcess[ 'stat' ]??null) ), LocId::Pack( 'SrvArgs' ) => PackKvArrInfo( $_SERVER ) ) ) : array() ), false, false );

	if( $seraph_accel_g_noFo )
		return( '' );

	$content = '';
	$dscFileTm = Gen::FileMTime( $seraph_accel_g_dscFile );
	_ProcessOutCachedData( $seraph_accel_g_simpCacheMode === null, null, $settGlob, $sett, $settCache, $dsc, $dscFileTm, $dscFileTm, 'revalidated', null, false, $content );
	return( $content );
}

function GetCacheViewId( $ctxCache, $settCache, $userAgent, $path, $pathOrig, &$args, $bFreshParts = false )
{
	$ctxCache -> viewStateId = '';
	$ctxCache -> viewGeoId = '';

	$type = 'cmn';
	if( ($settCache[ 'normAgent' ]??null) )
	{
		$_SERVER[ 'SERAPH_ACCEL_ORIG_USER_AGENT' ] = ($_SERVER[ 'HTTP_USER_AGENT' ]??'');
		$_SERVER[ 'HTTP_USER_AGENT' ] = 'Mozilla/99999.9 AppleWebKit/9999999.99 (KHTML, like Gecko) Chrome/999999.0.9999.99 Safari/9999999.99 seraph-accel-Agent/2.28.15';
	}

	if( ($settCache[ 'views' ]??null) )
	{
		if( $viewsDeviceGrp = GetCacheViewDeviceGrp( $settCache, $userAgent ) )
		{
			$type = ($viewsDeviceGrp[ 'id' ]??null);
			if( ($settCache[ 'normAgent' ]??null) )
				$_SERVER[ 'HTTP_USER_AGENT' ] = GetViewTypeUserAgent( $viewsDeviceGrp );
		}

		$aCurHdr = null;

		$viewsGrps = Gen::GetArrField( $settCache, array( 'viewsGrps' ), array() );
		foreach( $viewsGrps as $viewsGrp )
		{
			if( !($viewsGrp[ 'enable' ]??null) )
				continue;

			if( ($viewsGrp[ 'fr' ]??null) && !$bFreshParts )
				continue;

			if( CheckPathInUriList( Gen::GetArrField( $viewsGrp, array( 'urisExcl' ), array() ), $path, $pathOrig ) )
				continue;

			AccomulateCookiesState( $ctxCache -> viewStateId, $_COOKIE, Gen::GetArrField( $viewsGrp, array( 'cookies' ), array() ) );
			AccomulateHdrsState( $ctxCache -> viewStateId, $aCurHdr, Gen::GetArrField( $viewsGrp, array( 'hdrs' ), array() ) );

			$viewsArgs = Gen::GetArrField( $viewsGrp, array( 'args' ), array() );
			foreach( $viewsArgs as $a )
			{
				foreach( $args as $argKey => $argVal )
				{
					if( strpos( $argKey, $a ) === 0 )
					{
						$ctxCache -> viewStateId .= $argKey . $argVal;
						unset( $args[ $argKey ] );
					}
				}
			}
		}

		if( Gen::GetArrField( $settCache, array( 'viewsGeo', 'enable' ) ) )
		{
			$ctxCache -> viewGeoId = ($_SERVER[ 'HTTP_X_SERAPH_ACCEL_GEOID' ]??null);
			if( !is_string( $ctxCache -> viewGeoId ) )
			{
				$ip = Net::GetRequestIp();
				$ctxCache -> viewGeoId = GetViewGeoId( $settCache, $_SERVER, $ip );

				$_SERVER[ 'HTTP_X_SERAPH_ACCEL_GEOID' ] = $ctxCache -> viewGeoId;
				$_SERVER[ 'HTTP_X_SERAPH_ACCEL_GEO_REMOTE_ADDR' ] = $_SERVER[ 'REMOTE_ADDR' ] = $_SERVER[ 'HTTP_X_REAL_IP' ] = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] = $ip;
			}
			else if( isset( $_SERVER[ 'HTTP_X_SERAPH_ACCEL_GEO_REMOTE_ADDR' ] ) )
				$_SERVER[ 'REMOTE_ADDR' ] = $_SERVER[ 'HTTP_X_REAL_IP' ] = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] = $_SERVER[ 'HTTP_X_SERAPH_ACCEL_GEO_REMOTE_ADDR' ];
		}
	}

	$ctxCache -> viewType = $type;

	if( strlen( $ctxCache -> viewGeoId ) )
		$type .= '-' . $ctxCache -> viewGeoId;

	{
		$serverArgsTmp = Gen::ArrCopy( $_SERVER ); CorrectRequestScheme( $serverArgsTmp, 'client' );
		if( ($serverArgsTmp[ 'REQUEST_SCHEME' ]??null) == 'http' )
		{
			$type .= '-ns';
			$ctxCache -> viewNonSecure = true;
		}
	}

	$ctxCache -> viewCompatId = ContProcIsCompatView( $settCache, $userAgent );
	if( $ctxCache -> viewCompatId )
	{
		$type .= '-' . $ctxCache -> viewCompatId;

		if( ($settCache[ 'normAgent' ]??null) )
			$_SERVER[ 'HTTP_USER_AGENT' ] = $_SERVER[ 'SERAPH_ACCEL_ORIG_USER_AGENT' ];
	}

	if( strlen( $ctxCache -> viewStateId ) )
	{
		$ctxCache -> viewStateId = md5( $ctxCache -> viewStateId );
		$type .= '-' . $ctxCache -> viewStateId;
	}

	return( $type );
}

