<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

class ImgSrc
{
	public $src;
	public $srcInfo;
	public $mimeType;
	public $allowGetExt;

	private $ctxProcess;
	private $cont;
	private $info;

	function __construct( &$ctxProcess, $src = '', $srcInfo = null, $allowGetExt = null )
	{
		$this -> ctxProcess = &$ctxProcess;
		$this -> src = $src;
		$this -> srcInfo = $srcInfo;
		$this -> allowGetExt = $allowGetExt === null ? !!( $this -> ctxProcess[ 'mode' ] & 2 ) : $allowGetExt;
		$this -> cont = null;

	}

	function dispose()
	{
		$this -> cont = null;
		$this -> info = null;
		$this -> srcInfo = null;
		$this -> src = null;
	}

	function SetFile( $file, $filePathRoot = null )
	{
		if( !$this -> srcInfo )
			$this -> srcInfo = GetSrcAttrInfoEx( 'file://' . rawurlencode( $file ) );
		$this -> srcInfo[ 'filePath' ] = $file;

		if( $filePathRoot === null )
		{
			if( Gen::StrStartsWith( $file, $this -> ctxProcess[ 'siteRootDataPath' ] . '/' ) )
				$filePathRoot = $this -> ctxProcess[ 'siteRootDataPath' ];
			else if( Gen::StrStartsWith( $file, $this -> ctxProcess[ 'siteContPath' ] . '/' ) )
				$filePathRoot = Gen::GetFileDir( $this -> ctxProcess[ 'siteContPath' ] );
			else if( Gen::StrStartsWith( $file, $this -> ctxProcess[ 'siteRootPath' ] . '/' ) )
				$filePathRoot = $this -> ctxProcess[ 'siteRootPath' ];
			else
				$filePathRoot = Gen::GetFileDir( $file );
		}

		$this -> srcInfo[ 'filePathRoot' ] = $filePathRoot;
	}

	function GetFile()
	{
		if( $this -> srcInfo && isset( $this -> srcInfo[ 'filePath' ] ) )
			return( $this -> srcInfo[ 'filePath' ] );
		return( null );
	}

	function Init( $ctxProcessOBSOLETE, $requestDomainUrl = null, $requestUriPath = null )
	{
		if( !isset( $this -> srcInfo ) )
			$this -> srcInfo = Ui::IsSrcAttrData( $this -> src ) ? false : GetSrcAttrInfo( $this -> ctxProcess, $requestDomainUrl, $requestUriPath, $this -> src );
	}

	function GetSize()
	{
		if( $this -> cont === null && $this -> srcInfo )
		{
			$file = ($this -> srcInfo[ 'filePath' ]??null);
			if( $file )
				return( Gen::FileSize( $file ) );
		}

		$this -> GetCont();
		return( is_string( $this -> cont ) ? strlen( $this -> cont ) : false );
	}

	function IsCont()
	{
		return( $this -> cont !== null );
	}

	function GetCont()
	{
		if( $this -> cont === null )
		{
			if( isset( $this -> ctxProcess[ '_stat' ] ) )
				$tmStat = microtime( true );

			if( $this -> srcInfo )
			{
				$file = ($this -> srcInfo[ 'filePath' ]??null);
				if( $file )
				{
					$this -> cont = Gen::FileGetContents( $file );

				}

				if( $this -> cont === null )
				{
					if( $this -> allowGetExt )
					{

						$this -> cont = GetExtContents( $this -> ctxProcess, $this -> srcInfo[ 'url' ], $this -> mimeType, true, 10 );

					}
					else
						$this -> cont = false;
				}
			}
			else
			{
				$this -> cont = Ui::GetSrcAttrData( $this -> src, $this -> mimeType );
				if( $this -> mimeType == 'image/jpg' )
					$this -> mimeType = 'image/jpeg';
			}

			if( !$this -> mimeType )
			{
				$this -> GetInfo();
				if( $this -> info )
					$this -> mimeType = $this -> info[ 'mime' ];
			}

			if( isset( $this -> ctxProcess[ '_stat' ] ) )
			{
				$this -> ctxProcess[ '_stat' ][ 'imgRead' ][ 'v' ] += microtime( true ) - $tmStat;
				$this -> ctxProcess[ '_stat' ][ 'imgReadCount' ][ 'v' ] += 1;
			}
		}

		return( $this -> cont );
	}

	function GetInfo()
	{
		if( isset( $this -> info ) )
			return( $this -> info );

		$this -> GetCont();

		if( !isset( $this -> info ) )
		{
			$this -> info = Img::GetInfoFromData( $this -> cont );
			if( $this -> info === null )
				$this -> info = false;
		}

		return( $this -> info );
	}

	function GetDisplayFile()
	{
		if( $this -> srcInfo )
		{
			if( isset( $this -> srcInfo[ 'filePath' ] ) )
				return( $this -> srcInfo[ 'filePath' ] );
			return( $this -> srcInfo[ 'url' ] );
		}

		$this -> GetInfo();

		if( $this -> info )
			return( substr( _Images_ProcessSrc_InlineEx( $this -> info[ 'mime' ], ( string )$this -> GetCont() ), 0, 200 ) );

		return( '' );
	}

}

class ImgSzAlternatives
{
	public $a;
	public $isImportant;
	public $info;
	public $srcTpl;

	function __construct()
	{
		$this -> a = array();
	}

	function isEmpty()
	{
		return( count( $this -> a ) <= 1 );
	}
}

function _Images_ProcessSrc_CopyImageToHost( &$ctxProcess, $imgSrc, $imgCont, $settCache, $settImg )
{
	$type = Fs::GetFileTypeFromMimeContentType( $imgSrc -> mimeType );
	if( !$type )
		return( null );

	if( !UpdSc( $ctxProcess, $settCache, array( 'img', $type ), $imgCont, $imgSrc -> src, $file ) )
		return( false );
	$imgSrc -> SetFile( $file, $ctxProcess[ 'siteRootDataPath' ] );

	if( !is_array( $imgSrc -> srcInfo ) )
		$imgSrc -> srcInfo = array();

	if( Gen::GetArrField( $settImg, array( 'redirOwn' ), false ) && in_array( $type, array( 'jpe','jpg','jpeg','png','gif','bmp', 'webp','avif' ) ) )
	{
		$imgSrc -> srcInfo[ 'srcWoArgs' ] = $ctxProcess[ 'siteRootUri' ] . '/';
		$imgSrc -> srcInfo[ 'args' ] = Image_MakeOwnRedirUrlArgs( substr( $file, strlen( $imgSrc -> srcInfo[ 'filePathRoot' ] ) + 1 ) );
		$imgSrc -> src = Net::UrlDeParse( array( 'path' => $imgSrc -> srcInfo[ 'srcWoArgs' ], 'query' => $imgSrc -> srcInfo[ 'args' ] ), Net::URLPARSE_F_PRESERVEEMPTIES );
	}
	else
	{
		$imgSrc -> srcInfo[ 'srcWoArgs' ] = $imgSrc -> src;
		$imgSrc -> srcInfo[ 'args' ] = array();
	}

	$imgSrc -> srcInfo[ 'srcUrlFullness' ] = 2;
	$imgSrc -> srcInfo[ 'url' ] = $imgSrc -> src;
	$imgSrc -> srcInfo[ 'ext' ] = false;
	$imgSrc -> srcInfo[ '#' ] = null;

	Images_ProcessSrc_ConvertAll( $ctxProcess, $settImg, $imgCont, $file, Images_ProcessSrcEx_FileMTime( $file ) );
	return( true );
}

function _Images_ProcessSrc_DeInlineLarge( &$ctxProcess, $imgSrc, $settCache, $settImg )
{
	if( !Gen::GetArrField( $settImg, array( 'deinlLrg' ), false ) )
		return( null );

	$imgCont = $imgSrc -> GetCont();
	if( $imgCont === false )
		return( null );

	if( strlen( $imgCont ) < Gen::GetArrField( $settImg, array( 'deinlLrgSize' ), 0 ) )
		return( null );
	return( _Images_ProcessSrc_CopyImageToHost( $ctxProcess, $imgSrc, $imgCont, $settCache, $settImg ) );
}

function _Images_ProcessSrc_InlineEx( $mimeType, $imgCont )
{

	return( Ui::SetSrcAttrData( $imgCont, $mimeType ) );
}

function _Images_ProcessSrc_InlineSmall( $imgSrc, $settImg )
{
	if( !Gen::GetArrField( $settImg, array( 'inlSml' ), false ) )
		return( false );

	$fileSize = $imgSrc -> GetSize();
	if( $fileSize === false )
		return( false );

	if( !$fileSize || $fileSize > Gen::GetArrField( $settImg, array( 'inlSmlSize' ), 0 ) )
		return( false );

	$imgCont = $imgSrc -> GetCont();
	if( $imgCont === false )
		return( false );

	if( !$imgSrc -> mimeType )
		return( false );

	$imgSrc -> src = _Images_ProcessSrc_InlineEx( $imgSrc -> mimeType, $imgCont );
	return( true );
}

function _Images_ProcessSrc_ConvertEx( &$ctxProcess, $type, $typeIdx, $settImg, $data, $file, $fileCnv, $fileTime, $fileTimeCnv, $aTypeFrom, &$sizeCheck, $postpone )
{
	global $seraph_accel_g_prepPrms;

	$fileCnvStat = $fileCnv . '.json';
	$fileTimeCnvStat = Gen::FileMTime( $fileCnvStat );

	$fileExt = Gen::GetFileExt( $file );

	if( !Gen::GetArrField( $settImg, array( $type, 'enable' ), false ) || !in_array( $fileExt, $aTypeFrom ) )
		return( Gen::S_FALSE );

	if( $fileTimeCnvStat !== false && $fileTime <= $fileTimeCnvStat )
		return( Gen::S_FALSE );

	if( $fileTimeCnv !== false && $fileTime <= $fileTimeCnv )
	{
		$sizeCheck = Gen::FileSize( $fileCnv );
		if( $sizeCheck === false )
			LastWarnDscs_Add( LocId::Pack( 'ImgConvertFileErr_%1$s%2$s%3$s', null, array( $file, $type, Gen::GetLocPackFileReadErr( $fileCnv ) ) ) );
		return( Gen::S_FALSE );
	}

	if( $data === null )
		$data = Gen::FileGetContents( $file );
	else if( @is_a( $data, 'seraph_accel\\ImgSrc' ) )
		$data = $data -> GetCont();

	$lock = new Lock( 'il', GetCacheDir() );
	if( !$lock -> Acquire() )
	{
		LastWarnDscs_Add( LocId::Pack( 'ImgConvertFileErr_%1$s%2$s%3$s', null, array( $file, $type, $lock -> GetErrDescr() ) ) );
		return( Gen::E_FAIL );
	}

	$status = null;
	if( ( $fileExt == 'png' && Img::IsDataPngAnimated( $data ) ) || ( $fileExt == 'gif' && Img::IsDataGifAnimated( $data ) ) )
		$status = 'aniNotSupp';

	$hr = Gen::S_FALSE;
	$fileCnvTmp = $fileCnv . '.tmp';
	if( !$status )
	{
		@unlink( $fileCnvTmp );

		if( $postpone )
		{
			$hr = Gen::S_IO_PENDING;
		}
		else if( $ctxProcess[ 'mode' ] & 4 )
		{
			if( $seraph_accel_g_prepPrms )
				ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array( 'stageDsc' => LocId::Pack( 'ImgConvertFile_%1$s%2$s', null, array( $file, $type ) ) ) );

			if( isset( $ctxProcess[ '_stat' ] ) )
				$tmStat = microtime( true );

			$hr = Img::ConvertDataEx( $dataCnvRes, $data, 'image/' . $type, Gen::GetArrField( $settImg, array( $type, 'prms' ), array() ), $fileCnvTmp );

			if( isset( $ctxProcess[ '_stat' ] ) )
				$ctxProcess[ '_stat' ][ 'compr' ][ $type ][ 'v' ] += microtime( true ) - $tmStat;

			if( $seraph_accel_g_prepPrms )
				ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array( 'stageDsc' => null ) );
		}
		else
		{

			$ctxProcess[ 'modeReq' ] |= 4;
		}
	}

	$fileTime += $typeIdx + 1;

	if( $hr == Gen::S_OK )
	{
		if( $sizeCheck === false )
			$sizeCheck = strlen( $data );

		$sizeCnv = Gen::FileSize( $fileCnvTmp );
		if( $sizeCnv !== false )
		{
			if( $sizeCnv < $sizeCheck )
			{
				if( _FileWriteTmpAndReplace( $fileCnv, $fileTime, null, $fileCnvTmp ) )
					$sizeCheck = $sizeCnv;
				@unlink( $fileCnvStat );
			}
			else
				$status = array( 'larger' => $sizeCnv );
		}
		else
			Gen::LastErrDsc_Set( Gen::GetLocPackFileReadErr( $fileCnvTmp ) );
	}
	else if( $hr == Gen::E_UNSUPPORTED )
		Gen::LastErrDsc_Set( LocId::Pack( 'ImgConvertUnsupp' ) );

	if( ( $hr != Gen::S_OK && $hr != Gen::S_FALSE && $hr != Gen::S_IO_PENDING ) || $status )
	{
		@unlink( $fileCnv );
		if( $status )
			_FileWriteTmpAndReplace( $fileCnvStat, $fileTime, @json_encode( $status ) );
		else
			@unlink( $fileCnvStat );
	}

	@unlink( $fileCnvTmp );

	$lock -> Release();

	if( Gen::LastErrDsc_Is() )
	{
		LastWarnDscs_Add( LocId::Pack( 'ImgConvertFileErr_%1$s%2$s%3$s', null, array( $file, $type, Gen::LastErrDsc_Get() ) ) );
		Gen::LastErrDsc_Set( null );
	}

	return( $hr );
}

function Images_ProcessSrc_ConvertAll( &$ctxProcess, $settImg, $imgSrcOrCont, $file, $fileTime, $postpone = null )
{
	global $seraph_accel_g_siteId;
	global $seraph_accel_g_prepPrms;

	if( !( $ctxProcess[ 'mode' ] & 2 ) || $fileTime === false )
		return;

	if( $postpone === null )
		$postpone = Gen::GetArrField( $settImg, array( 'comprAsync' ), false );

	$bNeedPostpone = false;

	$sizeCheck = false;
	$aTypeFrom = array( 'jpe','jpg','jpeg','png','gif','bmp' );
	foreach( array( 'webp','avif' ) as $typeIdx => $type )
	{
		$fileCnv = $file . '.' . $type;

		$fileTimeCnv = Images_ProcessSrcEx_FileMTime( $fileCnv );
		if( _Images_ProcessSrc_ConvertEx( $ctxProcess, $type, $typeIdx, $settImg, $imgSrcOrCont, $file, $fileCnv, $fileTime, $fileTimeCnv, $aTypeFrom, $sizeCheck, $postpone ) == Gen::S_IO_PENDING )
			$bNeedPostpone = true;

		$aTypeFrom[] = $type;
	}

	if( $bNeedPostpone )
		CachePostPrepareObjEx( 10, $file, $seraph_accel_g_siteId, ($seraph_accel_g_prepPrms[ 'p' ]??10) );
}

function Images_ProcessSrcEx( &$ctxProcess, $imgSrc, $settCache, $settImg )
{
	$args = $imgSrc -> srcInfo[ 'args' ];

	$file = ($imgSrc -> srcInfo[ 'filePath' ]??null);

	if( !$file )
	{

		$cache = false;
		$cacheCrit = false;
		foreach( Gen::GetArrField( $settImg, array( 'cacheExt' ), array() ) as $srcPattern )
		{
			$cacheCritTmp = false;
			if( Gen::StrStartsWith( $srcPattern, 'crit:' ) )
			{
				$srcPattern = substr( $srcPattern, 5 );
				$cacheCritTmp = true;
			}

			if( !@preg_match( $srcPattern, $imgSrc -> src ) )
				continue;

			$cache = true;
			$cacheCrit = $cacheCritTmp;
			break;
		}

		if( !$cache )
			return( null );

		if( $cacheCrit )
			$imgSrc -> allowGetExt = true;
		$imgCont = $imgSrc -> GetCont();

		if( $imgCont === false )
		{
			$sErrTxt = LocId::Pack( 'CacheExtImgErr_%1$s', null, array( LocId::Pack( 'NetDownloadErr_%1$s', 'Common', array( $imgSrc -> src ) ) ) );
			if( $cacheCrit )
			{
				Gen::LastErrDsc_Set( $sErrTxt );
				return( false );
			}

			if( ($ctxProcess[ 'debugM' ]??null) )
				LastWarnDscs_Add( $sErrTxt );
			return( null );
		}

		if( !$imgSrc -> mimeType )
		{
			$sErrTxt = LocId::Pack( 'CacheExtImgErr_%1$s', null, array( LocId::Pack( 'NetMimeErr_%1$s', 'Common', array( $imgSrc -> src ) ) ) );
			if( $cacheCrit )
			{
				Gen::LastErrDsc_Set( $sErrTxt );
				return( false );
			}

			if( ($ctxProcess[ 'debugM' ]??null) )
				LastWarnDscs_Add( $sErrTxt );
			return( null );
		}

		if( Gen::GetArrField( $settImg, array( 'inlSml' ), false ) && strlen( $imgCont ) <= Gen::GetArrField( $settImg, array( 'inlSmlSize' ), 0 ) )
			$imgSrc -> src = _Images_ProcessSrc_InlineEx( $imgSrc -> mimeType, $imgCont );
		else
		{
			$r = _Images_ProcessSrc_CopyImageToHost( $ctxProcess, $imgSrc, $imgCont, $settCache, $settImg );
			if( !$r )
				return( $r );
		}

		return( true );
	}

	$fileTime = Images_ProcessSrcEx_FileMTime( $file );
	if( !$fileTime )
	{
		if( ($ctxProcess[ 'debugM' ]??null) )
			LastWarnDscs_Add( Gen::GetLocPackFileReadErr( $file ) );
		return( null );
	}

	if( ( ($ctxProcess[ 'compatView' ]??null) !== 'cm' ) && _Images_ProcessSrc_InlineSmall( $imgSrc, $settImg ) )
		return( true );

	Images_ProcessSrc_ConvertAll( $ctxProcess, $settImg, $imgSrc, $file, $fileTime );

	foreach( array( 'webp','avif' ) as $typeCnv )
	{
		if( !( Gen::GetArrField( $settImg, array( $typeCnv, 'redir' ), false ) ) )
			continue;

		$srcRealCnvFile = $file . '.' . $typeCnv;
		$fileTimeCnv = Images_ProcessSrcEx_FileMTime( $srcRealCnvFile );
		if( $fileTimeCnv !== false && $fileTimeCnv > $fileTime )
			$fileTime = $fileTimeCnv;
	}

	$argsAdjusted = false;

	if( Gen::GetArrField( $settImg, array( 'redirOwn' ), false ) && in_array( strtolower( Gen::GetFileExt( $file ) ), array( 'jpe','jpg','jpeg','png','gif','bmp', 'webp','avif' ) ) )
	{
		$imgSrc -> srcInfo[ 'srcWoArgs' ] = $ctxProcess[ 'siteRootUri' ] . '/';
		$imgSrc -> srcInfo[ 'srcUrlFullness' ] = 2;
		$args = Image_MakeOwnRedirUrlArgs( substr( $file, strlen( $imgSrc -> srcInfo[ 'filePathRoot' ] ) + 1 ) );
		$argsAdjusted = true;
	}

	if( Gen::GetArrField( $settImg, array( 'srcAddLm' ), false ) )
	{
		$args[ 'lm' ] = sprintf( '%X', $fileTime );
		$argsAdjusted = true;
	}

	if( $argsAdjusted )
	{
		$imgSrc -> src = Net::UrlDeParse( array( 'path' => $imgSrc -> srcInfo[ 'srcWoArgs' ], 'query' => $args, 'fragment' => ($imgSrc -> srcInfo[ '#' ]??null) ), Net::URLPARSE_F_PRESERVEEMPTIES );

	}

	return( true );
}

function Images_ProcessSrc_SizeAlternatives( &$ctxProcess, $file, $sett, $bCrop = false, $idAiOnly = null )
{
	$imgSzAlternatives = new ImgSzAlternatives();

	$imgSrc = new ImgSrc( $ctxProcess );
	$imgSrc -> SetFile( $file );

	return( Images_ProcessSrc_SizeAlternativesExEx( $imgSzAlternatives, $ctxProcess, $imgSrc, Gen::GetArrField( $sett, array( 'cache' ), array() ), Gen::GetArrField( $sett, array( 'contPr', 'img' ), array() ), Gen::GetArrField( $sett, array( 'contPr', 'cdn' ), array() ), $bCrop, false, $idAiOnly, false ) );
}

function Images_ProcessSrc_SizeAlternativesEx( $imgSzAlternatives, &$ctxProcess, $imgSrc, $settCache, $settImg, $settCdn, $bCrop = false, $isImportant = false, $idAiOnly = null, $postpone = null )
{
	global $seraph_accel_g_siteId;
	global $seraph_accel_g_prepPrms;

	$hr = Images_ProcessSrc_SizeAlternativesExEx( $imgSzAlternatives, $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn, $bCrop, $isImportant, $idAiOnly, $postpone );

	if( $hr == Gen::S_IO_PENDING && ( $file = $imgSrc -> GetFile() ) && !Gen::GetArrField( $settImg, array( 'szAdaptOnDemand' ), false ) )
		CachePostPrepareObjEx( 20, $file, $seraph_accel_g_siteId, ($seraph_accel_g_prepPrms[ 'p' ]??10), $idAiOnly ? array( 'ai' => $idAiOnly ) : array() );

	return( $hr );
}

function Images_ProcessSrc_SizeAlternativesExEx( $imgSzAlternatives, &$ctxProcess, $imgSrc, $settCache, $settImg, $settCdn, $bCrop = false, $isImportant = false, $idAiOnly = null, $postpone = null )
{
	global $seraph_accel_g_prepPrms;

	$imgSrc -> Init( $ctxProcess );

	$info = $imgSrc -> GetInfo();

	if( !$info || !Img::IsMimeRaster( $info[ 'mime' ] ) || !$info[ 'cx' ] )
		return( Gen::S_FALSE );

	$data = $imgSrc -> GetCont();
	if( !is_string( $data ) )
		return( Gen::E_FAIL );

	if( ( $info[ 'mime' ] == 'image/png' && Img::IsDataPngAnimated( $data ) ) || ( $info[ 'mime' ] == 'image/gif' && Img::IsDataGifAnimated( $data ) ) )
		return( Gen::S_FALSE );

	$bSepStg = Gen::GetArrField( $settImg, array( 'szAdaptAsync' ), false ) || Gen::GetArrField( $settImg, array( 'szAdaptOnDemand' ), false );

	if( $postpone === null )
		$postpone = $bSepStg;

	if( !$postpone && !( $ctxProcess[ 'mode' ] & 4 ) )
	{

		$ctxProcess[ 'modeReq' ] |= 4;
		return( Gen::S_FALSE );
	}

	$bNeedPostpone = false;

	$fileType = Gen::GetFileName( $info[ 'mime' ] );

	$idCache = md5( $data . ( $bSepStg ? '' : ( Gen::GetArrField( $settImg, array( 'inlSml' ), false ) ? ( string )Gen::GetArrField( $settImg, array( 'inlSmlSize' ), 0 ) : '' ) ), true );
	$infoCache = null;

	$lock = Images_ProcessSrcSizeAlternatives_Cache_GetLocker( $ctxProcess[ 'dataPath' ] );

	if( !$bSepStg )
	{
		$infoCache = Images_ProcessSrcSizeAlternatives_Cache_Get( $ctxProcess[ 'dataPath' ], $idCache, $lock );
		if( $infoCache === false )
			return( Gen::S_FALSE );
	}

	if( isset( $ctxProcess[ '_stat' ] ) )
	{
		$tmStat = microtime( true );
		$aStatDiff = array();
		$aStatDiff[ 'v' ][] = ( float )Gen::GetArrField( $ctxProcess[ '_stat' ], $aStatDiff[ 'k' ][] = array( 'imgRead', 'v' ) );
		foreach( array( 'webp','avif' ) as $comprType )
			$aStatDiff[ 'v' ][] = ( float )Gen::GetArrField( $ctxProcess[ '_stat' ], $aStatDiff[ 'k' ][] = array( 'compr', $comprType, 'v' ) );
	}

	$img = null;
	$infoCache = ( array )$infoCache;

	static $g_aAdaptDimsScale = array( 1.0, 	1.01,	1.01,	1.01,	1.03,	1.05,	1.0,	1.0,	1.0	 );

	$szAdaptBgCxMin = Gen::GetArrField( $settImg, array( 'szAdaptBgCxMin' ), 0 );
	foreach( array( $info[ 'cx' ], 				2160,	1920,	1366,	992,	768,	480,	360,	120	 ) as $i => $cx )
	{
		if( $infoCache === false )
			break;

		$scale = ( float )$info[ 'cx' ] / $cx;
		if( $scale < $g_aAdaptDimsScale[ $i ] || !( $szAdaptBgCxMin <= $cx ) )
			continue;

		$cy = ( int )round( ( float )$info[ 'cy' ] * ( $cx / $info[ 'cx' ] ) );

		$imgScaled = null;
		foreach( ( $bCrop ? array( $cx, 	2160,	1920,	1366,	992,	768,	480,	360				 ) : array( $cx ) ) as $cxCrop )
		{
			if( $cxCrop > $cx || $szAdaptBgCxMin > $cx )
				continue;

			if( $info[ 'cx' ] == $cxCrop )
				continue;

			$idAi = ( $info[ 'cx' ] != $cx ? ( string )$cx : 'O' ) . ( $cxCrop != $cx ? ( ( $bSepStg ? 'c'  : '@' ) . $cxCrop ) : '' );
			if( $idAiOnly && $idAi != $idAiOnly )
				continue;

			$imgSrcAlter = null;
			$file = null;
			$filePathRoot = null;
			$fileTpl = null;
			$infoCacheVal = null;

			if( $bSepStg )
			{
				$idCacheFile = base_convert( bin2hex( $idCache ), 16, 36 );

				if( !$imgSrc -> GetFile() )
				{

					if( !UpdSc( $ctxProcess, $settCache, array( 'img', $fileType ), $data, $imgSrc -> src, $fileOrig ) )
						return( Gen::E_FAIL );

					$imgSrc -> SetFile( $fileOrig );
					unset( $fileOrig );
				}

				$fileTpl = Gen::GetFileDir( $ctxProcess[ 'dataPath' ] ) . '/ai/' . $idCacheFile . '-_SERAPH_ACCEL_AID_.' . Gen::GetFileExt( $imgSrc -> GetFile() );
				$file = str_replace( '_SERAPH_ACCEL_AID_', $idAi, $fileTpl );
				$filePathRoot = $ctxProcess[ 'siteRootDataPath' ];
				if( file_exists( $file ) )
					$infoCacheVal = true;

				unset( $idCacheFile );
			}
			else if( $infoCacheVal = ($infoCache[ $idAi ]??null) )
			{
				if( is_array( $infoCacheVal ) )
					$imgSrcAlter = _Images_ProcessSrc_InlineEx( ( string )($infoCacheVal[ 't' ]??null), ( string )($infoCacheVal[ 'd' ]??null) );
				else if( CheckSc( $ctxProcess, $settCache, array( 'img', $fileType ), ( string )$infoCacheVal, $imgSrcAlter, $file ) )
					$filePathRoot = $ctxProcess[ 'siteRootDataPath' ];
				else
					$infoCacheVal = null;
			}

			if( !$infoCacheVal )
			{
				if( !$postpone )
				{
					if( $img === null )
					{
						$img = Img::CreateFromData( $data );
						unset( $data );

						if( !$img )
						{

							return( Gen::S_FALSE );
						}

						$imgScaled = $img;
					}
					else if( !$imgScaled )
						$imgScaled = $img;

					if( $seraph_accel_g_prepPrms )
						ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array( 'stageDsc' => LocId::Pack( 'ImgAdaptFile_%1$s%2$s', null, array( $imgSrc -> GetDisplayFile(), $idAi ) ) ) );

					if( $cx === $cxCrop || $imgScaled === $img )
					{

						$imgNew = Img::CreateCopyResample( $img,
						    array( 'cx' => $cx, 'cy' => $cy ),
						    array( 'x' => 0, 'y' => 0, 'cx' => $info[ 'cx' ], 'cy' => $info[ 'cy' ] ), null, null, IMG_BOX );

						if( !$imgNew )
						{

							continue;
						}

						$imgScaled = $imgNew;
					}

					if( $cx !== $cxCrop )
					{

						$imgNew = Img::CreateCopyResample( $imgScaled,
							array( 'cx' => $cxCrop, 'cy' => $cy ),
							array( 'x' => ( $cx - $cxCrop ) / 2, 'y' => 0, 'cx' => $cxCrop, 'cy' => $cy ) );
						if( !$imgNew )
						{

							continue;
						}
					}

					$imgNewCont = Img::GetData( $imgNew, $info[ 'mime' ] );
					if( $imgNew !== $imgScaled )
						imagedestroy( $imgNew );
					if( !$imgNewCont )
					{

						continue;
					}

					if( $seraph_accel_g_prepPrms )
						ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array( 'stageDsc' => null ) );

					if( $bSepStg )
					{
						if( !_FileWriteTmpAndReplace( $file, null, $imgNewCont, null, $lock ) )
							return( Gen::E_FAIL );
					}
					else
					{
						$infoCacheVal = array();

						if( Gen::GetArrField( $settImg, array( 'inlSml' ), false ) && $info[ 'mime' ] && strlen( $imgNewCont ) <= Gen::GetArrField( $settImg, array( 'inlSmlSize' ), 0 ) )
						{
							$imgSrcAlter = _Images_ProcessSrc_InlineEx( $info[ 'mime' ], $imgNewCont );

							$infoCacheVal = array( 't' => $info[ 'mime' ], 'd' => $imgNewCont );
						}
						else
						{
							$oiCi = UpdSc( $ctxProcess, $settCache, array( 'img', $fileType ), $imgNewCont, $imgSrcAlter, $file );
							if( !$oiCi )
								return( Gen::E_FAIL );

							$infoCacheVal = $oiCi;
							$filePathRoot = $ctxProcess[ 'siteRootDataPath' ];
						}

						$infoCache[ $idAi ] = $infoCacheVal;
					}
				}
				else
					$bNeedPostpone = true;
			}

			unset( $infoCacheVal );

			if( $file )
			{
				Images_ProcessSrc_ConvertAll( $ctxProcess, $settImg, null, $file, Images_ProcessSrcEx_FileMTime( $file ) );

				if( $bSepStg )
				{
					if( $imgSzAlternatives -> srcTpl === null )
					{
						if( in_array( $fileType, array( 'jpe','jpg','jpeg','png','gif','bmp', 'webp','avif' ) ) )
						{
							$fileOrigRelPath = substr( $imgSrc -> GetFile(), strlen( $imgSrc -> srcInfo[ 'filePathRoot' ] ) + 1 );
							if( Gen::GetArrField( $settImg, array( 'redirOwn' ), false ) )
								$imgSzAlternatives -> srcTpl = Net::UrlDeParse( array( 'path' => $ctxProcess[ 'siteRootUri' ] . '/', 'query' => Image_MakeOwnRedirUrlArgs( $fileOrigRelPath, substr( $fileTpl, strlen( GetCacheDir() . '/s/' ) ) ) ), Net::URLPARSE_F_PRESERVEEMPTIES );
							else
								$imgSzAlternatives -> srcTpl = Net::UrlDeParse( array( 'path' => $ctxProcess[ 'siteRootUri' ] . '/' . $fileOrigRelPath, 'query' => Image_MakeAiUrlArgs( Gen::GetArrField( $imgSrc -> srcInfo, array( 'args' ), array() ), $fileOrigRelPath, substr( $fileTpl, strlen( GetCacheDir() . '/s/' ) ) ) ), Net::URLPARSE_F_PRESERVEEMPTIES );
							unset( $fileOrigRelPath );

							Cdn_AdjustUrl( $ctxProcess, $settCdn, $imgSzAlternatives -> srcTpl, $fileType );
							Fullness_AdjustUrl( $ctxProcess, $imgSzAlternatives -> srcTpl );
						}
						else
							$imgSzAlternatives -> srcTpl = false;
					}

					if( $imgSzAlternatives -> srcTpl )
						$imgSrcAlter = str_replace( '_SERAPH_ACCEL_AID_', $idAi, $imgSzAlternatives -> srcTpl );
				}
				else
				{
					if( Gen::GetArrField( $settImg, array( 'redirOwn' ), false ) && in_array( $fileType, array( 'jpe','jpg','jpeg','png','gif','bmp', 'webp','avif' ) ) )
						$imgSrcAlter = Net::UrlDeParse( array( 'path' => $ctxProcess[ 'siteRootUri' ] . '/', 'query' => Image_MakeOwnRedirUrlArgs( substr( $file, strlen( $filePathRoot ) + 1 ) ) ), Net::URLPARSE_F_PRESERVEEMPTIES );

					Cdn_AdjustUrl( $ctxProcess, $settCdn, $imgSrcAlter, $fileType );
					Fullness_AdjustUrl( $ctxProcess, $imgSrcAlter );
				}
			}

			$imgSzAlternatives -> a[ $idAi ] = array( 'img' => $imgSrcAlter, 'sz' => array( $cx, $cy ) );
		}

		if( $imgScaled && $imgScaled !== $img )
		{
			imagedestroy( $imgScaled );
			$imgScaled = $img;
		}
	}

	if( $img )
		imagedestroy( $img );

	$imgSzAlternatives -> a[ '0' ] = array( 'img' => null );
	$imgSzAlternatives -> isImportant = $isImportant;
	$imgSzAlternatives -> info = $info;

	if( !$bSepStg )
		Images_ProcessSrcSizeAlternatives_Cache_Set( $ctxProcess[ 'dataPath' ], $idCache, $infoCache, $lock );

	if( isset( $ctxProcess[ '_stat' ] ) )
	{
		$tmStat = microtime( true ) - $tmStat;
		foreach( $aStatDiff[ 'v' ] as $i => $v )
			$tmStat -= ( float )Gen::GetArrField( $ctxProcess[ '_stat' ], $aStatDiff[ 'k' ][ $i ] ) - $v;
		if( $tmStat < 0 ) $tmStat = 0;
		$ctxProcess[ '_stat' ][ 'ai' ][ 'v' ] += $tmStat;
	}

	return( $infoCache === false ? Gen::S_FALSE : ( $bNeedPostpone ? Gen::S_IO_PENDING : Gen::S_OK ) );
}

function Images_ProcessSrc( &$ctxProcess, $imgSrc, $settCache, $settImg, $settCdn )
{
	if( !$imgSrc -> src )
		return( null );

	$adjusted = null;

	$imgSrc -> Init( $ctxProcess );
	if( !$imgSrc -> srcInfo )
	{
		$r = _Images_ProcessSrc_DeInlineLarge( $ctxProcess, $imgSrc, $settCache, $settImg );
		if( !$r )
			return( $r );

		$adjusted = true;
	}

	$fileType = strtolower( Gen::GetFileExt( ($imgSrc -> srcInfo[ 'srcWoArgs' ]??null) ) );

	if( $adjusted === null )
	{
		$adjusted = Images_ProcessSrcEx( $ctxProcess, $imgSrc, $settCache, $settImg );
		if( $adjusted === false )
			return( false );
	}

	if( Cdn_AdjustUrl( $ctxProcess, $settCdn, $imgSrc -> src, $fileType ) )
		$adjusted = true;
	if( Fullness_AdjustUrl( $ctxProcess, $imgSrc -> src, ($imgSrc -> srcInfo[ 'srcUrlFullness' ]??null) ) )
		$adjusted = true;

	return( $adjusted );
}

function Images_ProcessSrcSet( &$ctxProcess, &$srcset, $settCache, $settImg, $settCdn )
{
	$apply = false;

	$srcItems = Ui::ParseSrcSetAttr( $srcset );
	foreach( $srcItems as &$srcItem )
	{
		$imgSrc = new ImgSrc( $ctxProcess, html_entity_decode( $srcItem[ 0 ] ) );

		$r = Images_ProcessSrc( $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn );
		if( $r === false )
			return( false );

		if( $r )
		{
			$srcItem[ 0 ] = $imgSrc -> src;
			$apply = true;
		}
	}

	if( !$apply )
		return( null );

	$srcset = Ui::GetSrcSetAttr( $srcItems, false );
	return( true );
}

function LazyLoad_SvgSubst( $width, $height, $exact = false, $fill = null )
{
	return( Ui::Tag( 'svg', $fill ? Ui::TagOpen( 'rect', array( 'width' => '100%', 'height' => '100%', 'fill' => is_array( $fill ) && count( $fill ) >= 3 ? ( count( $fill ) > 3 ? sprintf( '#%02X%02X%02X%02X', $fill[ 0 ], $fill[ 1 ], $fill[ 2 ], $fill[ 3 ] ) : sprintf( '#%02X%02X%02X', $fill[ 0 ], $fill[ 1 ], $fill[ 2 ] ) ) : null ), true ) : null
		, array_merge( array( 'xmlns' => 'http://www.w3.org/2000/svg', 'viewBox' => '0 0 ' . $width . ' ' . $height ), $exact ? array( 'width' => $width, 'height' => $height ) : array() ) ) );
}

function LazyLoad_SrcSubst( $ctxProcess, $info, $raster = false, $exact = true, $fill = null )
{
	if( !$info )
		$info = array();
	if( !($info[ 'cx' ]??null) )
		$info[ 'cx' ] = 225;
	if( !($info[ 'cy' ]??null) )
		$info[ 'cy' ] = $info[ 'cx' ] / 3 * 2;

	if( !$raster )
		return( 'data:image/svg+xml,' . rawurlencode( LazyLoad_SvgSubst( $info[ 'cx' ], $info[ 'cy' ], $exact, $fill ) ) );

	$wh = $info[ 'cx' ] + $info[ 'cy' ];

	if( !Img::IsMimeRaster( ($info[ 'mime' ]??null) ) || $wh > 10000 || !@function_exists( 'imagecreatetruecolor' ) )
	{

		{
			if( !is_array( $fill ) || count( $fill ) < 3 )
				$fill = array( 0, 0, 0, 0 );
			else if( count( $fill ) == 3 )
				$fill[] = 0;

			foreach( $fill as $i => $c )
			{
				if( $c < 0 )
					$fill[ $i ] = 0;
				else if( $c > 0xFF )
					$fill[ $i ] = 0xFF;
			}

		}

		return( 'data:image/svg+xml,' . rawurlencode( Ui::Tag( 'svg', ( $fill ? Ui::TagOpen( 'rect', array( 'width' => '100%', 'height' => '100%', 'fill' => is_array( $fill ) && count( $fill ) >= 3 ? ( count( $fill ) > 3 ? sprintf( '#%02X%02X%02X%02X', $fill[ 0 ], $fill[ 1 ], $fill[ 2 ], $fill[ 3 ] ) : sprintf( '#%02X%02X%02X', $fill[ 0 ], $fill[ 1 ], $fill[ 2 ] ) ) : null ), true ) : '' )
			, array_merge( array( 'xmlns' => 'http://www.w3.org/2000/svg', 'viewBox' => isset( $info[ 'viewBox' ] ) ? $info[ 'viewBox' ] : ( '0 0 ' . $info[ 'cx' ] . ' ' . $info[ 'cy' ] ) ), array( 'width' => isset( $info[ 'width' ] ) ? $info[ 'width' ] : $info[ 'cx' ], 'height' => isset( $info[ 'height' ] ) ? $info[ 'height' ] : $info[ 'cy' ] ) ) ) ) );
	}

	if( !is_array( $fill ) || count( $fill ) < 3 )
		$fill = array( 0, 0, 0, 0 );
	else if( count( $fill ) == 3 )
		$fill[] = 0;

	foreach( $fill as $i => $c )
	{
		if( $c < 0 )
			$fill[ $i ] = 0;
		else if( $c > 0xFF )
			$fill[ $i ] = 0xFF;
	}

	$idCache = md5( 'LazyLoad_SrcSubst:' . sprintf( '%ux%u#%02X%02X%02X%02X', $info[ 'cx' ], $info[ 'cy' ], ( int )$fill[ 0 ], ( int )$fill[ 1 ], ( int )$fill[ 2 ], ( int )$fill[ 3 ] ), true );
	$imgNewCont = Images_ProcessSrcSizeAlternatives_Cache_Get( $ctxProcess[ 'dataPath' ], $idCache );

	if( !$imgNewCont )
	{
		$hNew = @imagecreatetruecolor( $info[ 'cx' ], $info[ 'cy' ] );

		$hClr = @imagecolorallocatealpha( $hNew, ( int )$fill[ 0 ], ( int )$fill[ 1 ], ( int )$fill[ 2 ], ( int )( 127 - $fill[ 3 ] / 2 ) );
		@imagefill( $hNew, 0, 0, $hClr );
		@imagecolordeallocate( $hNew, $hClr );
		if( $fill[ 3 ] !== 0xFF )
			@imagesavealpha( $hNew, true );

		$imgNewCont = Img::GetData( $hNew, 'image/png', array( 'c' => $wh > 1000 ? Img::PNG_COMPRESSION_HIGH : Img::PNG_COMPRESSION_LOW, 'q' => $wh > 4000 ? 1 : 100, 's' => $wh > 8000 ? Img::PNG_SPEED_LOW : Img::PNG_SPEED_HIGH ) );

		@imagedestroy( $hNew );
	}

	Images_ProcessSrcSizeAlternatives_Cache_Set( $ctxProcess[ 'dataPath' ], $idCache, $imgNewCont );

	return( _Images_ProcessSrc_InlineEx( 'image/png', $imgNewCont ) );

}

function _Images_ProcessItemLazy_Start( &$ctxProcess, $doc, $settImg, $item )
{
	if( $ctxProcess[ 'isAMP' ] || !Gen::GetArrField( $settImg, array( 'lazy', 'load' ), false ) )
		return( null );

	if( HtmlNd::FindUpByTag( $item, 'noscript' ) )
		return( null );

	$exclMode = Images_CheckLazyExcl( $ctxProcess, $doc, $settImg, $item );
	if( $exclMode && $exclMode !== 'ajs' )
		return( null );

	if( Gen::GetArrField( $settImg, array( 'lazy', 'del3rd' ), false ) )
	{

		HtmlNd::AddRemoveAttrClass( $item, array(), array( 'lazyload', 'blog-thumb-lazy-load', 'lazy-load', 'lazy', 'mfn-lazy', 'iso-lazy-load', 'll-image', 'wd-lazy-load', 'wd-lazy-fade', 'houzez-lazyload', 'pk-lazyload' ) );

		if( $item -> hasAttribute( 'data-src' ) )
		{
			if( strlen( ( string )$item -> getAttribute( 'data-src' ) ) )
				HtmlNd::RenameAttr( $item, 'data-src', 'src' );
			else
				$item -> removeAttribute( 'data-src' );
		}
		if( $item -> hasAttribute( 'data-srcset' ) )
		{
			if( strlen( ( string )$item -> getAttribute( 'data-srcset' ) ) )
				HtmlNd::RenameAttr( $item, 'data-srcset', 'srcset' );
			else
				$item -> removeAttribute( 'data-srcset' );
		}

		HtmlNd::RenameAttr( $item, 'data-orig-src', 'src' );
		HtmlNd::RenameAttr( $item, 'data-orig-srcset', 'srcset' );

		HtmlNd::RenameAttr( $item, 'data-lazy-src', 'src' );
		HtmlNd::RenameAttr( $item, 'data-lazy-srcset', 'srcset' );

		HtmlNd::RenameAttr( $item, 'data-wood-src', 'src' );

		HtmlNd::RenameAttr( $item, 'data-pk-src', 'src' );
		HtmlNd::RenameAttr( $item, 'data-pk-srcset', 'srcset' );
		HtmlNd::RenameAttr( $item, 'data-ls-sizes', 'sizes' );
		$item -> removeAttribute( 'data-pk-sizes' );

		if( HtmlNd::GetAttr( $item, 'srcset' ) === null )
			$item -> removeAttribute( 'srcset' );
	}

	return( $exclMode === 'ajs' ? 'bjs' : true );
}

function _Images_ProcessItemLazy_Finish( &$ctxProcess, $doc, $settImg, $item, $imgSrc, $modeLazy )
{
	$src = $item -> getAttribute( 'src' );
	if( !$src )
		return( null );
	if( !$item -> getAttribute( 'srcset' ) && Ui::IsSrcAttrData( $src ) )
		return( null );

	if( Gen::GetArrField( $settImg, array( 'lazy', 'own' ), false ) )
	{

		{
			$itemCopy = $item -> cloneNode( true );
			if( !$itemCopy )
				return( false );
			$itemCopy -> removeAttribute( 'id' );

			$itemNoScript = $doc -> createElement( 'noscript' );
			if( !$itemNoScript )
				return( false );

			$itemNoScript -> setAttribute( 'lzl', '' );
			$itemNoScript -> appendChild( $itemCopy );
			HtmlNd::InsertAfter( $item -> parentNode, $itemNoScript, $item );
		}

		$ctxProcess[ 'lazyload' ] = true;
		HtmlNd::AddRemoveAttrClass( $item, array( 'lzl', $modeLazy === 'bjs' ? 'bjs' : null ) );
		if( $modeLazy === 'bjs' )
			$ctxProcess[ 'lazyloadBjs' ] = true;

		HtmlNd::RenameAttr( $item, 'srcset', 'data-lzl-srcset' );
		HtmlNd::RenameAttr( $item, 'sizes', 'data-lzl-sizes' );

		$item -> setAttribute( 'data-lzl-src', $src );

		$item -> setAttribute( 'src', LazyLoad_SrcSubst( $ctxProcess, $imgSrc && ( ( $ctxProcess[ 'mode' ] & 2 ) || $imgSrc -> IsCont() ) ? $imgSrc -> GetInfo() : null, Gen::GetArrField( $settImg, array( 'lazy', 'plchRast' ), true ) && !!( $ctxProcess[ 'mode' ] & 2 ) ) );

	}
	else
	{
		$item -> setAttribute( 'loading', 'lazy' );
	}

	{
		for( $p = $item -> parentNode; $p && $p -> nodeType == XML_ELEMENT_NODE; $p = $p -> parentNode )
		{
			if( !in_array( 'woocommerce-product-gallery', Ui::ParseClassAttr( $p -> getAttribute( 'class' ) ) ) )
				continue;

			$styles = Ui::ParseStyleAttr( $p -> getAttribute( 'style' ) );
			$styles[ 'opacity' ] = 1;

			$p -> setAttribute( 'style', Ui::GetStyleAttr( $styles ) );
			break;
		}
	}

	return( true );
}

function Images_ProcessItemLazyBg( &$ctxProcess, $doc, $settImg, $item, $imgSrc )
{
	if( HtmlNd::FindUpByTag( $item, 'noscript' ) )
		return( false );

	if( $item -> hasAttribute( 'data-bg' ) )
		return( false );

	if( Images_CheckLazyExcl( $ctxProcess, $doc, $settImg, $item ) )
		return( false );

	$ctxProcess[ 'lazyload' ] = true;
	HtmlNd::AddRemoveAttrClass( $item, array( 'lzl' ) );

	$item -> setAttribute( 'data-lzl-bg', $imgSrc -> src );

	$imgSrc -> src = LazyLoad_SrcSubst( $ctxProcess, $imgSrc -> GetInfo(), Gen::GetArrField( $settImg, array( 'lazy', 'plchRast' ), true ) );

	return( true );
}

function Images_CheckExcl( &$ctxProcess, $doc, $settImg, $item )
{
	return( Conts_CheckExclEx( $ctxProcess, $doc, $settImg, $item, 'imgExclItems', array( 'excl' ) ) );
}

function Images_CheckLazyExcl( &$ctxProcess, $doc, $settImg, $item )
{
	return( Conts_CheckExclEx( $ctxProcess, $doc, $settImg, $item, 'lazyExclItems', array( 'lazy', 'excl' ) ) );
}

function Images_CheckSzAdaptExcl( &$ctxProcess, $doc, $settImg, $item )
{
	$excls = Gen::GetArrField( $settImg, array( 'szAdaptExcl' ), array() );
	if( !$excls )
		return( false );

	$ctxSzAdaptExcl = ($ctxProcess[ 'ctxSzAdaptExcl' ]??null);
	if( !$ctxSzAdaptExcl )
		$ctxProcess[ 'ctxSzAdaptExcl' ] = $ctxSzAdaptExcl = new AnyObj();

	$itemRoot = $ctxProcess[ 'ndHtml' ];
	if( is_string( $item ) )
	{
		if( !($ctxSzAdaptExcl -> itemTmp??null) )
		{
			$ctxSzAdaptExcl -> itemTmpCont = $doc -> createElement( 'root' );
			$ctxSzAdaptExcl -> itemTmpCont -> appendChild( $ctxSzAdaptExcl -> itemTmp = $doc -> createElement( 'style' ) );
		}

		HtmlNd::SetValFromContent( $ctxSzAdaptExcl -> itemTmp, $item );
		$item = $ctxSzAdaptExcl -> itemTmp;
		$itemRoot = $ctxSzAdaptExcl -> itemTmpCont;
	}

	$xpath = new \DOMXPath( $doc );

	$found = false;
	foreach( $excls as $exclItem )
	{
		$mode = 'y';
		if( Gen::StrStartsWith( $exclItem, 'ajs:' ) )
		{
			if( ($ctxProcess[ 'isJsDelayed' ]??null) )
				$mode = 'ajs';
			$exclItem = substr( $exclItem, 4 );
		}

		$items = HtmlNd::ChildrenAsArr( @$xpath -> query( $exclItem, $itemRoot ) );
		if( in_array( $item, $items, true ) )
		{
			$found = $mode;
			break;
		}
	}

	HtmlNd::SetValFromContent( ($ctxSzAdaptExcl -> itemTmp??null), '' );
	return( $found );
}

function Images_Process( &$ctxProcess, $doc, $settCache, $settImg, $settCdn )
{
	if( !( Gen::GetArrField( $settImg, array( 'srcAddLm' ), false ) || Gen::GetArrField( $settImg, array( 'inlSml' ), false ) || Gen::GetArrField( $settImg, array( 'deinlLrg' ), false ) || Gen::GetArrField( $settImg, array( 'lazy', 'setSize' ), false ) || Gen::GetArrField( $settImg, array( 'lazy', 'load' ), false ) || Gen::GetArrField( $settCdn, array( 'enable' ), false ) || ($settImg[ 'szAdaptImg' ]??null) ) )
		return( true );

	$items = HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'img' ) );
	if( $ctxProcess[ 'isAMP' ] )
		$items = array_merge( $items, HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'amp-img' ) ) );
	foreach( $items as $item )
	{
		if( ContentProcess_IsAborted( $ctxProcess, $settCache ) ) return( true );

		if( !$item -> attributes || Images_CheckExcl( $ctxProcess, $doc, $settImg, $item ) )
			continue;

		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( HtmlNd::FindUpByTag( $item, 'noscript' ) )
		{
			if( ( $ctxProcess[ 'mode' ] & 2 ) && ( $src = $item -> getAttribute( 'src' ) ) )
			{
				$imgSrc = new ImgSrc( $ctxProcess, $src );
				$imgSrc -> Init( $ctxProcess );
				if( $file = $imgSrc -> GetFile() )
					Images_ProcessSrc_ConvertAll( $ctxProcess, $settImg, $imgSrc, $file, Images_ProcessSrcEx_FileMTime( $file ) );
			}

			continue;
		}

		$inlinedSize = 0;
		$imgSrc = null;
		$srcAiSmallest = null;

		$modeLazy = _Images_ProcessItemLazy_Start( $ctxProcess, $doc, $settImg, $item );
		if( $modeLazy === false )
			return( false );

		$attr = $item -> attributes -> getNamedItem( 'src' );
		if( $attr )
		{
			$imgSrc = new ImgSrc( $ctxProcess, html_entity_decode( $attr -> nodeValue ) );
			$dataAi = null;
			$aiExclMode = null;

			if( ($settImg[ 'szAdaptImg' ]??null) )
			{
				$aiExclMode = Images_CheckSzAdaptExcl( $ctxProcess, $doc, $settImg, $item );
				if( !$aiExclMode || $aiExclMode === 'ajs' )
				{
					$imgSzAlternatives = new ImgSzAlternatives();
					if( Gen::HrFail( Images_ProcessSrc_SizeAlternativesEx( $imgSzAlternatives, $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn, Gen::GetArrField( $settImg, array( 'szAdaptOnDemand' ), false ) ) ) )
						return( false );

					if( !$imgSzAlternatives -> isEmpty() )
					{
						$dataAi = array( 's' => array( $imgSzAlternatives -> info[ 'cx' ], $imgSzAlternatives -> info[ 'cy' ] ), 'd' => array() );
						foreach( $imgSzAlternatives -> a as $dim => $imgSzAlternative )
						{
							if( !$imgSzAlternative[ 'img' ] )
								continue;

							$srcAiSmallest = $imgSzAlternative[ 'img' ];
							$dataAi[ 'd' ][ $dim ] = $srcAiSmallest;
						}

						if( $imgSzAlternatives -> srcTpl )
						{
							$dataAi[ 'd' ] = array_map( function( $v ) { return( ( string )$v ); }, array_keys( $dataAi[ 'd' ] ) );
							$dataAi[ 'st' ] = $imgSzAlternatives -> srcTpl;
						}

						$ctxProcess[ 'imgAdaptive' ] = true;
						if( $aiExclMode === 'ajs' )
							$ctxProcess[ 'imgAdaptiveBjs' ] = true;
					}
				}
			}

			$r = Images_ProcessSrc( $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn );
			if( $r === false )
				return( false );

			if( $r )
				$attr -> nodeValue = htmlspecialchars( $imgSrc -> src );

			if( $dataAi )
			{
				$dataAi[ 'O' ] = $imgSrc -> src;

				HtmlNd::AddRemoveAttrClass( $item, array( 'ai-img', $aiExclMode === 'ajs' ? 'ai-bjs' : null ) );
				$item -> setAttribute( 'data-ai-img', @json_encode( $dataAi ) );
				if( ($settImg[ 'szAdaptDpr' ]??null) )
					$item -> setAttribute( 'data-ai-dpr', 'y' );
				$item -> removeAttribute( 'srcset' );
				$item -> removeAttribute( 'sizes' );
			}

			if( Gen::GetArrField( $settImg, array( 'lazy', 'setSize' ), false ) && !$item -> hasAttribute( 'width' ) && !$item -> hasAttribute( 'height' ) && ( $srcImgDim = $imgSrc -> GetInfo() ) )
			{
				if( $srcImgDim[ 'cx' ] !== null && $srcImgDim[ 'cy' ] !== null )
				{
					$item -> setAttribute( 'width', ( int )round( ( float )$srcImgDim[ 'cx' ] ) );
					$item -> setAttribute( 'height', ( int )round( ( float )$srcImgDim[ 'cy' ] ) );
				}
			}

			if( Ui::IsSrcAttrData( $imgSrc -> src ) )
				$inlinedSize = strlen( $imgSrc -> src );
		}

		if( ( $ctxProcess[ 'mode' ] & 2 ) && ( $attrSrcSet = $item -> attributes -> getNamedItem( 'srcset' ) ) )
		{
			$srcset = $attrSrcSet -> nodeValue;

			$r = Images_ProcessSrcSet( $ctxProcess, $srcset, $settCache, $settImg, $settCdn );
			if( $r === false )
				return( false );

			if( $r )
				$attrSrcSet -> nodeValue = htmlspecialchars( $srcset );

			if( stripos( $srcset, 'data:' ) !== false )
				$inlinedSize = strlen( $srcset );
		}

		if( $inlinedSize >= 2048 && ($ctxProcess[ 'chunksEnabled' ]??null) )
			ContentMarkSeparate( $item );

		if( $modeLazy )
		{
			if( _Images_ProcessItemLazy_Finish( $ctxProcess, $doc, $settImg, $item, $imgSrc, $modeLazy ) === false )
				return( false );
		}
		else if( $srcAiSmallest )
			$item -> setAttribute( 'src', $srcAiSmallest );

	}

	foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'picture' ) ) as $itemPict )
	{
		if( ContentProcess_IsAborted( $ctxProcess, $settCache ) ) return( true );

		if( HtmlNd::FindUpByTag( $itemPict, 'noscript' ) || Images_CheckExcl( $ctxProcess, $doc, $settImg, $itemPict ) )
			continue;

		$lazyPict = Gen::GetArrField( $settImg, array( 'lazy', 'load' ), false ) && !Images_CheckLazyExcl( $ctxProcess, $doc, $settImg, $itemPict );

		foreach( $itemPict -> childNodes as $item )
		{
			if( $item -> nodeType != XML_ELEMENT_NODE || !$item -> attributes || $item -> nodeName != 'source' )
				continue;

			if( ( $ctxProcess[ 'mode' ] & 2 ) && ( $attrSrcSet = $item -> attributes -> getNamedItem( 'srcset' ) ) )
			{
				$srcset = $attrSrcSet -> nodeValue;

				$r = Images_ProcessSrcSet( $ctxProcess, $srcset, $settCache, $settImg, $settCdn );
				if( $r === false )
					return( false );

				if( $r )
					$attrSrcSet -> nodeValue = htmlspecialchars( $srcset );
			}

			if( $lazyPict )
			{

				{
					$itemCopy = $item -> cloneNode( true );
					if( !$itemCopy )
						return( false );
					$itemCopy -> removeAttribute( 'id' );

					$itemNoScript = $doc -> createElement( 'noscript' );
					if( !$itemNoScript )
						return( false );

					$itemNoScript -> setAttribute( 'lzl', '' );
					$itemNoScript -> appendChild( $itemCopy );
					HtmlNd::InsertAfter( $item -> parentNode, $itemNoScript, $item );
				}

				$ctxProcess[ 'lazyload' ] = true;
				HtmlNd::RenameAttr( $item, 'srcset', 'data-lzl-srcset' );
			}
		}
	}

	foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'image' ) ) as $itemSvgImg )
	{
		if( !HtmlNd::FindUpByTag( $itemSvgImg, 'svg' ) || HtmlNd::FindUpByTag( $itemSvgImg, 'noscript' ) || Images_CheckExcl( $ctxProcess, $doc, $settImg, $itemSvgImg ) )
			continue;

		foreach( array( 'href', 'xlink:href' ) as $attrHref )
		{
			$href = $itemSvgImg -> getAttribute( $attrHref );
			if( !$href )
				continue;

			$imgSrc = new ImgSrc( $ctxProcess, $href );

			$r = Images_ProcessSrc( $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn );
			if( $r === false )
				return( false );

			if( $r )
				$itemSvgImg -> setAttribute( $attrHref, $imgSrc -> src );
		}
	}

	foreach( $ctxProcess[ 'aAttrImg' ] as $attrImg )
	{
		$imgSrc = new ImgSrc( $ctxProcess, $attrImg -> nodeValue );

		$r = Images_ProcessSrc( $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn );
		if( $r === false )
			return( false );

		if( $r )
			$attrImg -> nodeValue = $imgSrc -> src;
	}

	if( Gen::GetArrField( $settImg, array( 'srcAddLm' ), false ) || Gen::GetArrField( $settCdn, array( 'enable' ), false ) )
	{
		$srcImgDim = null;

		$settImgForMeta = Gen::ArrCopy( $settImg );
		Gen::SetArrField( $settImgForMeta, array( 'inlSml' ), false );
		Gen::SetArrField( $settImgForMeta, array( 'deinlLrg' ), false );

		foreach( $ctxProcess[ 'ndHead' ] -> childNodes as $item )
		{
			if( HtmlNd::FindUpByTag( $item, 'noscript' ) || Images_CheckExcl( $ctxProcess, $doc, $settImg, $item ) )
				continue;

			if( ContentProcess_IsAborted( $ctxProcess, $settCache ) ) return( true );

			$srcAttrName = null; $src = null;
			$bImg = true;

			if( $item -> nodeName == 'meta' )
			{
				$id = $item -> getAttribute( 'property' );
				if( !$id )
					$id = $item -> getAttribute( 'name' );

				if( $id && in_array( $id, array( 'og:image', 'og:image:secure_url', 'twitter:image', 'vk:image' ) ) )
					$srcAttrName = 'content';
			}
			else if( $item -> nodeName == 'link' )
			{
				switch( $item -> getAttribute( 'rel' ) )
				{
				case 'icon':
					$srcAttrName = 'href';
					break;

				case 'preload':
					switch( $item -> getAttribute( 'as' ) )
					{
					case 'image':
						$srcAttrName = 'href';
						break;

					case 'font':
						$srcAttrName = 'href';
						$bImg = false;
						break;
					}
					break;
				}
			}

			if( !$srcAttrName )
				continue;

			$src = $item -> getAttribute( $srcAttrName );
			if( !$src )
				continue;

			if( $bImg )
			{
				$src = new ImgSrc( $ctxProcess, $src );

				$r = Images_ProcessSrc( $ctxProcess, $src, $settCache, $settImgForMeta, $settCdn );
				if( $r === false )
					return( false );
			}
			else
			{
				$r = false;
				if( $srcInfo = Ui::IsSrcAttrData( $src ) ? false : GetSrcAttrInfo( $ctxProcess, null, null, $src ) )
				{
					if( Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, Gen::GetFileExt( $srcInfo[ 'srcWoArgs' ] ) ) )
						$r = true;
					if( Fullness_AdjustUrl( $ctxProcess, $src, ($srcInfo[ 'srcUrlFullness' ]??null) ) )
						$r = true;
				}
			}

			if( $r )
				$item -> setAttribute( $srcAttrName, is_string( $src ) ? $src : $src -> src );
		}
	}

	foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'video' ) ) as $item )
	{
		if( HtmlNd::FindUpByTag( $item, 'noscript' ) || Images_CheckExcl( $ctxProcess, $doc, $settImg, $item ) )
			continue;

		if( $src = $item -> getAttribute( 'poster' ) )
		{
			$imgSrc = new ImgSrc( $ctxProcess, $src );

			$r = Images_ProcessSrc( $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn );
			if( $r === false )
				return( false );

			if( $r )
				$item -> setAttribute( 'poster', $imgSrc -> src );
		}

		foreach( array( 'src', 'data-src' ) as $attr )
		{
			$src = $item -> getAttribute( $attr );
			if( !$src )
				continue;

			$r = false;
			if( $srcInfo = Ui::IsSrcAttrData( $src ) ? false : GetSrcAttrInfo( $ctxProcess, null, null, $src ) )
			{
				if( Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, Gen::GetFileExt( $srcInfo[ 'srcWoArgs' ] ) ) )
					$r = true;
				if( Fullness_AdjustUrl( $ctxProcess, $src, ($srcInfo[ 'srcUrlFullness' ]??null) ) )
					$r = true;
			}

			if( $r )
				$item -> setAttribute( $attr, $src );
		}

		foreach( $item -> childNodes as $itemSrc )
		{
			if( $itemSrc -> nodeName != 'source' )
				continue;

			$src = $itemSrc -> getAttribute( 'src' );

			$r = false;
			if( $srcInfo = Ui::IsSrcAttrData( $src ) ? false : GetSrcAttrInfo( $ctxProcess, null, null, $src ) )
			{
				if( Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, Gen::GetFileExt( $srcInfo[ 'srcWoArgs' ] ) ) )
					$r = true;
				if( Fullness_AdjustUrl( $ctxProcess, $src, ($srcInfo[ 'srcUrlFullness' ]??null) ) )
					$r = true;
			}

			if( $r )
				$itemSrc -> setAttribute( 'src', $src );
		}
	}

	if( Gen::GetArrField( $settCdn, array( 'enable' ), false ) )
	{
        $ctxRpl = new AnyObj();
		$ctxRpl -> ctxProcess = $ctxProcess;
		$ctxRpl -> settCdn = $settCdn;

		$ctxRpl -> cb =
			function( $ctxRpl, $srcOrig )
			{
				$src = $srcOrig;

				$r = false;
				if( $srcInfo = Ui::IsSrcAttrData( $src ) ? false : GetSrcAttrInfo( $ctxRpl -> ctxProcess, null, null, $src ) )
				{
					if( Cdn_AdjustUrl( $ctxRpl -> ctxProcess, $ctxRpl -> settCdn, $src, Gen::GetFileExt( $srcInfo[ 'srcWoArgs' ] ) ) )
						$r = true;
				}

				if( !$r )
					return( $srcOrig );

				$ctxRpl -> r = true;
				return( $src );
			}
		;
		$ctxRpl -> cbEscaped =
			function( $ctxRpl, $m )
			{
				$srcOrig = str_replace( '\\/', '/', $m[ 0 ] );
				if( $srcOrigLastSlash = Gen::StrEndsWith( $srcOrig, '\\' ) )
					$srcOrig = substr( $srcOrig, 0, -1 );
				$src = $ctxRpl -> cb( $srcOrig );
				if( $srcOrig === $src )
					return( $m[ 0 ] );
				return( str_replace( '/', '\\/', $src ) . ( $srcOrigLastSlash ? '\\' : '' ) );
			}
		;

		$ctxRpl -> cbSimple =
			function( $ctxRpl, $m )
			{
				return( $ctxRpl -> cb( $m[ 0 ] ) );
			}
		;

		$originalUrl = $ctxProcess[ 'siteDomainUrl' ] . $ctxProcess[ 'siteRootUri' ];
        $directories = array();
		$bApply = false;
		{
			foreach( Gen::GetArrField( $settCdn, array( 'items' ), array() ) as $settCdnItem )
				if( $settCdnItem[ 'enable' ] && $settCdnItem[ 'addr' ] && ($settCdnItem[ 'sa' ]??null) )
				{
					$bApply = true;
					$directories = array_merge( Gen::GetArrField( $settCdnItem, array( 'uris' ), array() ) );
				}
			$directories = array_unique( $directories );
		}

		if( $bApply )
		{
			$regexOriginalUrl = preg_quote( $originalUrl, '@' );
			$directories = implode( '|', array_map( function( $v ) { return( preg_quote( Gen::SetLastSlash( $v ), '@' ) ); }, $directories ) );
			$escapedOriginalUrl = str_replace( '/', '(?:\\\\/)', $regexOriginalUrl );
			$escapedIncludedDirs = str_replace( '/', '(?:\\\\/)', $directories );
			$regexSimple = '@(?<=[(\\"\'])(?:' . $regexOriginalUrl . ')?/(?:((?:' . $directories . ')[^\\"\')]+)|([^/\\"\']+\\.[^/\\"\')]+))(?=[\\"\')])@S';
			$regexEscaped = '@(?<=[(\\"\'])(?:' . $escapedOriginalUrl . ')?(?:\\\\/)(?:((?:' . $escapedIncludedDirs . ')[^\\"\')]+)|([^/\\"\']+\\.[^/\\"\')]+))(?=[\\"\')])@S';

			foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'script' ) ) as $item )
			{
				if( strlen( ( string )$item -> getAttribute( 'src' ) ) > 0 )
					continue;

				$cont = $item -> nodeValue;

				$ctxRpl -> r = false;

				{
					$result = @preg_replace_callback( $regexEscaped, array( $ctxRpl, 'cbEscaped' ), $cont );
					if( $result !== null )
						$cont = $result;
					unset( $result );
				}

				{
					$result = @preg_replace_callback( $regexSimple, array( $ctxRpl, 'cbSimple' ), $cont );
					if( $result !== null )
						$cont = $result;
					unset( $result );
				}

				if( $ctxRpl -> r )
					HtmlNd::SetValFromContent( $item, $cont );
			}
		}
	}

	return( true );
}

function _Images_ProcessSrcSizeAlternatives_Cache_ArrayOnFiles( $fileTpl )
{
	return( array( 'dirFilesPattern' => $fileTpl . '_*.dat.gz', 'options' => array( 'comprLev' => 9 ) ) );
}

function Images_ProcessSrcSizeAlternatives_Cache_GetLocker( $dataPath )
{
	$fileTpl = Gen::GetFileDir( $dataPath ) . '/ai/c';
	return( new Lock( $fileTpl . 'l', false ) );
}

function Images_ProcessSrcSizeAlternatives_Cache_Get( $dataPath, $imgStgId, $lock = null )
{
	$fileTpl = Gen::GetFileDir( $dataPath ) . '/ai/c';

	if( !$lock )
		$lock = new Lock( $fileTpl . 'l', false );

	if( !$lock -> Acquire() )
		return( null );

	$aCache = new ArrayOnFiles( _Images_ProcessSrcSizeAlternatives_Cache_ArrayOnFiles( $fileTpl ) );
	$a = ( array )$aCache -> offsetGet( $imgStgId );
	$aCache -> dispose(); $lock -> Release();

	return( ($a[ 'v' ]??null) );
}

function Images_ProcessSrcSizeAlternatives_Cache_Set( $dataPath, $imgStgId, $v, $lock = null )
{
	$fileTpl = Gen::GetFileDir( $dataPath ) . '/ai/c';

	if( !$lock )
		$lock = new Lock( $fileTpl . 'l', false );

	if( !$lock -> Acquire() )
		return;

	$aCache = new ArrayOnFiles( _Images_ProcessSrcSizeAlternatives_Cache_ArrayOnFiles( $fileTpl ) );
	$aCache[ $imgStgId ] = array( 't' => time(), 'v' => $v );
	$aCache -> dispose(); $lock -> Release();
}

function Images_ProcessSrcSizeAlternatives_Cache_Cleanup( $dataPath, $tm, $cbIsAborted )
{
	$dir = Gen::GetFileDir( $dataPath ) . '/ai/';
	$fileTpl = $dir . 'c';

	$lock = new Lock( $fileTpl . 'l', false );
	if( !$lock -> Acquire() )
		return;

	$ctx = new AnyObj();
	$ctx -> tm = $tm;
	$ctx -> cbIsAborted = $cbIsAborted;

	$bAborted = false;

	{
		$aDel = array();
		$aCache = new ArrayOnFiles( _Images_ProcessSrcSizeAlternatives_Cache_ArrayOnFiles( $fileTpl ) );

		foreach( $aCache as $imgStgId => $a )
		{
			if( @call_user_func( $ctx -> cbIsAborted ) )
			{
				$bAborted = true;
				break;
			}

			if( ( int )($a[ 't' ]??null) < $ctx -> tm )
				$aDel[] = $imgStgId;
		}

		foreach( $aDel as $imgStgId )
		{
			if( @call_user_func( $ctx -> cbIsAborted ) )
			{
				$bAborted = true;
				break;
			}

			unset( $aCache[ $imgStgId ] );
		}

		$aCache -> dispose(); $lock -> Release();
		unset( $aDel, $aCache );
	}

	if( Gen::DirEnum( $dir, $ctx,
		function( $path, $item, &$ctx )
		{
			if( @call_user_func( $ctx -> cbIsAborted ) )
				return( false );

			$item = $path . '/' . $item;
			if( @is_dir( $item ) )
				return;

			if( !in_array( strtolower( Gen::GetFileExt( $item ) ), array( 'jpe','jpg','jpeg','png','gif','bmp', 'webp','avif' ) ) )
				return;

			$tmFile = @filemtime( $item );
			if( $tmFile !== false && $tmFile < $ctx -> tm )
				@unlink( $item );
		}
	) === false )
	{
		$bAborted = true;
	}

	return( !$bAborted );
}

function _Image_GetAiFileId( $aiFileId = null )
{
	if( !$aiFileId )
		return( null );

	if( preg_match( '@^([\\w\\-]+)/ai/([\\w\\-]+)@', $aiFileId, $m ) )
		$aiFileId = $m[ 1 ] . '.' . $m[ 2 ];
	else
		$aiFileId = null;

	return( $aiFileId );
}

function Image_MakeOwnRedirUrlArgsEx( $path, $aiFileId, $nonce = null )
{
	return( array( 'seraph_accel_gi' => $path, 'ai' => $aiFileId, 'n' => $nonce ) );
}

function Image_MakeOwnRedirUrlArgs( $path, $aiFileId = null )
{

	$path = Gen::GetNormalizedPath( $path );
	return( Image_MakeOwnRedirUrlArgsEx( $path, _Image_GetAiFileId( $aiFileId ), Gen::GetNonce( $path, GetSalt() ) ) );
}

function Image_MakeAiUrlArgs( $args, $path, $aiFileId )
{

	$path = Gen::GetNormalizedPath( $path );

	return( array_merge( ( array )$args, array( 'seraph_accel_ai' => _Image_GetAiFileId( $aiFileId ), 'n' => Gen::GetNonce( $path, GetSalt() ) ) ) );
}

