<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

function _Scripts_EncodeBodyAsSrc( $cont )
{

	$cont = str_replace( "%", '%25', $cont );

	$cont = str_replace( "\n", '%0A', $cont );
	$cont = str_replace( "#", '%23', $cont );
	$cont = str_replace( "\"", '%22', $cont );

	return( $cont );
}

function IsScriptTypeJs( $type )
{
	return( !$type || $type == 'application/javascript' || $type == 'text/javascript' || $type == 'module' );
}

function Script_SrcAddPreloading( $item, $src, $head, $doc )
{
	if( !$src )
		return;

	$itemPr = $doc -> createElement( 'link' );
	$itemPr -> setAttribute( 'rel', ( $item -> getAttribute( 'data-type' ) == 'module' || $item -> getAttribute( 'type' ) == 'module' ) ? 'modulepreload' : 'preload' );
	$itemPr -> setAttribute( 'as', $item -> tagName == 'IFRAME' ? 'document' : 'script' );
	$itemPr -> setAttribute( 'href', $src );
	$itemPr -> setAttribute( 'fetchpriority', 'low' );
	if( $item -> hasAttribute( 'integrity' ) )
		$itemPr -> setAttribute( "integrity", $item -> getAttribute( "integrity" ) );
	if( $item -> hasAttribute( "crossorigin" ) )
		$itemPr -> setAttribute( "crossorigin", $item -> getAttribute( "crossorigin" ) );
	$head -> appendChild( $itemPr );
}

function Scripts_Process( &$ctxProcess, $sett, $settCache, $settContPr, $settJs, $settCdn, $doc )
{
	if( ($ctxProcess[ 'isAMP' ]??null) )
	    return( true );

	$optLoad = Gen::GetArrField( $settJs, array( 'optLoad' ), false );
	$skips = Gen::GetArrField( $settJs, array( 'skips' ), array() );

	if( !( $optLoad || Gen::GetArrField( $settJs, array( 'groupNonCrit' ), false ) || Gen::GetArrField( $settJs, array( 'min' ), false ) || Gen::GetArrField( $settCdn, array( 'enable' ), false ) || $skips ) )
		return( true );

	$ctxOpt = new ScriptsOpt();
	if( isset( $ctxProcess[ 'lrnDsc' ] ) )
		$ctxOpt -> readLrnData( $ctxProcess, $ctxProcess[ 'lrnDsc' ], $ctxProcess[ 'lrnDataPath' ], isset( $ctxProcess[ 'lrn' ] ) );

	if( ($ctxProcess[ 'compatView' ]??null) )
		$optLoad = false;

	$head = $ctxProcess[ 'ndHead' ];
	$body = $ctxProcess[ 'ndBody' ];

	$aGrpExcl = Gen::GetArrField( $settJs, array( 'groupExcls' ), array() );
	$notCritsDelayTimeout = Gen::GetArrField( $settJs, array( 'nonCrit', 'timeout', 'enable' ), false ) ? Gen::GetArrField( $settJs, array( 'nonCrit', 'timeout', 'v' ), 0 ) : null;

	$critSpecsDelayTimeout = Gen::GetArrField( $settJs, array( 'critSpec', 'timeout', 'enable' ), false ) ? Gen::GetArrField( $settJs, array( 'critSpec', 'timeout', 'v' ), 0 ) : null;
	$critSpec = array();
	if( $critSpecsDelayTimeout !== null )
	{
		$critSpec = Gen::GetArrField( $settJs, array( 'critSpec', 'items' ), array() );
		if( isset( $ctxProcess[ 'aJsCritSpec' ] ) )
		{
			foreach( array_keys( $ctxProcess[ 'aJsCritSpec' ] ) as $expr )
				if( !in_array( $expr, $critSpec ) )
					$critSpec[] = $expr;
		}

		$critSpec = array_map( function( $v ) { return( $v . 'S' ); }, $critSpec );
	}

	$specsDelayTimeout = Gen::GetArrField( $settJs, array( 'spec', 'timeout', 'enable' ), false ) ? Gen::GetArrField( $settJs, array( 'spec', 'timeout', 'v' ), 0 ) : null;
	$specs = ( ( $notCritsDelayTimeout !== null && $specsDelayTimeout ) || ( $notCritsDelayTimeout === null && $specsDelayTimeout !== null ) ) ? Gen::GetArrField( $settJs, array( 'spec', 'items' ), array() ) : array();
	{
		$specs = array_map( function( $v ) { return( $v . 'S' ); }, $specs );
	}

	$settNonCrit = Gen::GetArrField( $settJs, array( 'nonCrit' ), array() );
	{
		$aItems = Gen::GetArrField( $settNonCrit, array( 'items' ), array() );

		if( isset( $ctxProcess[ 'aJsCrit' ] ) )
		{
			foreach( array_keys( $ctxProcess[ 'aJsCrit' ] ) as $expr )
				if( !in_array( $expr, $aItems ) )
					$aItems[] = $expr;
		}

		$aItems = array_map( function( $v ) { return( $v . 'S' ); }, $aItems );

		Gen::SetArrField( $settNonCrit, array( 'items' ), $aItems );
		unset( $aItems );
	}

	$delayNotCritNeeded = false;
	$delaySpecNeeded = false;

	$items = HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'script' ) );

	$contGroups = array( 'crit' => array( array( 0, 0 ), array( '' ) ), 'critSpec' => array( array( 0, 0 ), array( '' ) ), '' => array( array( 0, 0 ), array( '' ) ), 'spec' => array( array( 0, 0 ), array( '' ) ) );

	foreach( $items as $item )
	{
		if( ContentProcess_IsAborted( $ctxProcess, $settCache ) ) return( true );

		$type = HtmlNd::GetAttrVal( $item, 'type' );
		if( !IsScriptTypeJs( $type ) )
			continue;

		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
		{
			if( !$type )
				$item -> setAttribute( 'type', $type = 'text/javascript' );
		}
		else if( $type && ($settContPr[ 'min' ]??null) && $type != 'module' )
		{
			$item -> removeAttribute( 'type' );
			$type = null;
		}

		$src = HtmlNd::GetAttrVal( $item, 'src' );
		$id = HtmlNd::GetAttrVal( $item, 'id' );
		$cont = $item -> nodeValue;

		{

		}

		$detectedPattern = null;
		if( IsObjInRegexpList( $skips, array( 'src' => $src, 'id' => $id, 'body' => $cont ), $detectedPattern ) )
		{
			if( ($ctxProcess[ 'debug' ]??null) )
			{
				$item -> setAttribute( 'type', 'o/js-inactive' );
				$item -> setAttribute( 'seraph-accel-debug', 'status=skipped;' . ( $detectedPattern ? ' detectedPattern="' . $detectedPattern . '"' : '' ) );
			}
			else
				$item -> parentNode -> removeChild( $item );
			continue;
		}

		$detectedPattern = null;
		if( $src )
		{
			$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, $src );

			$cont = null;
			$contMimeType = null;
			if( isset( $srcInfo[ 'filePath' ] ) && Gen::GetFileExt( $srcInfo[ 'filePath' ] ) == 'js' )
			{
				$cont = @file_get_contents( $srcInfo[ 'filePath' ] );
				if( $cont === false && !Gen::DoesFileDirExist( $srcInfo[ 'filePath' ], $srcInfo[ 'filePathRoot' ] ) )
					$cont = null;
			}
			else if( Ui::IsSrcAttrData( $src ) )
				$cont = Ui::GetSrcAttrData( $src, $contMimeType );

			if( $cont === null  )
			{

				$cont = GetExtContents( $ctxProcess, ($srcInfo[ 'url' ]??null), $contMimeType );
			}

			if( $contMimeType && $cont !== false && !in_array( $contMimeType, array( 'text/javascript', 'application/x-javascript', 'application/javascript' ) ) )
			{
				$cont = false;
				if( ($sett[ 'debug' ]??null) )
					LastWarnDscs_Add( LocId::Pack( 'JsUrlWrongType_%1$s%2$s', null, array( $srcInfo[ 'url' ], $contMimeType ) ) );
			}
			else if( $cont === false && ($sett[ 'debug' ]??null) )
				LastWarnDscs_Add( LocId::Pack( 'NetDownloadErr_%1$s', 'Common', array( $srcInfo[ 'url' ] ) ) );

			if( $cont === false && Gen::GetArrField( $settJs, array( 'skipBad' ), false ) )
			{
				$item -> parentNode -> removeChild( $item );
				continue;
			}

			$isCrit = $item -> hasAttribute( 'seraph-accel-crit' ) ? true : GetObjSrcCritStatus( $settNonCrit, $critSpec, $specs, $srcInfo, $src, $id, $cont, $detectedPattern );

			$r = $ctxOpt -> AdjustCont( $ctxProcess, $settCache, $settJs, $srcInfo, $src, $id, $cont );
			if( $r === false )
				return( false );
			if( $r )
			{
				if( ($ctxProcess[ 'debug' ]??null) )
					$cont = '// ################################################################################################################################################' . "\r\n" . '// DEBUG: seraph-accel JS src="' . $src . '"' . "\r\n\r\n" . $cont;

				$cont = apply_filters( 'seraph_accel_js_content', $cont, true );
				if( !UpdSc( $ctxProcess, $settCache, 'js', $cont, $src ) )
					return( false );
			}

			Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, 'js' );
			Fullness_AdjustUrl( $ctxProcess, $src, ($srcInfo[ 'srcUrlFullness' ]??null) );

			$item -> setAttribute( 'src', $src );
		}
		else
		{
			if( !$cont )
				continue;

			$isCrit = $item -> hasAttribute( 'seraph-accel-crit' ) ? true : GetObjSrcCritStatus( $settNonCrit, $critSpec, $specs, null, null, $id, $cont, $detectedPattern );

			$r = $ctxOpt -> AdjustCont( $ctxProcess, $settCache, $settJs, null, null, $id, $cont );
			if( $r === false )
				return( false );
			if( $r )
			{
				if( ($ctxProcess[ 'debug' ]??null) )
					$cont = '// ################################################################################################################################################' . "\r\n" . '// DEBUG: seraph-accel JS src="inline:' . ($ctxProcess[ 'serverArgs' ][ 'REQUEST_SCHEME' ]??null) . '://' . $ctxProcess[ 'host' ] . ':' . ($ctxProcess[ 'serverArgs' ][ 'SERVER_PORT' ]??null) . ($ctxProcess[ 'serverArgs' ][ 'REQUEST_URI' ]??null) . ':' . $item -> getLineNo() . '"' . "\r\n\r\n" . $cont;

				HtmlNd::SetValFromContent( $item, $cont );
			}
		}

		ContUpdateItemIntegrity( $item, $cont );

		if( ($ctxProcess[ 'debug' ]??null) )
			$item -> setAttribute( 'seraph-accel-debug', 'status=' . ( $isCrit === true ? 'critical' : ( $isCrit === 'critSpec' ? 'criticalSpecial' : ( $isCrit === null ? 'special' : 'nonCritical' ) ) ) . ';' . ( $detectedPattern ? ' detectedPattern="' . $detectedPattern . '"' : '' ) );

		$delay = 0;
		if( $optLoad )
		{
			if( !$isCrit )
			{
				$parentNode = $item -> parentNode;
				$async = $item -> hasAttribute( 'async' );

				$delay = ( $isCrit === null ) ? $specsDelayTimeout : $notCritsDelayTimeout;

				if( $delay === 0 && ( !$async || ( $parentNode === $head || $parentNode === $body ) ) )
					$body -> appendChild( $item );
			}
			else if( $isCrit === 'critSpec' && !$item -> hasAttribute( 'async' ) )
			{
				$item -> setAttribute( 'defer', '' );
				if( !$src )
				{
					$src = 'data:text/javascript,' . _Scripts_EncodeBodyAsSrc( $cont );
					$item -> nodeValue = '';
					$item -> setAttribute( 'src', $src );
				}
			}

		}

		if( ($ctxProcess[ 'chunksEnabled' ]??null) )
			ContentMarkSeparate( $item, false );

		if( $delay )
		{
			if( $type )
				$item -> setAttribute( 'data-type', $type );

			if( $isCrit === null )
			{

				$item -> setAttribute( 'type', 'o/js-lzls' );
				$delaySpecNeeded = true;
			}
			else
			{

				$item -> setAttribute( 'type', 'o/js-lzl' );
				$delayNotCritNeeded = true;
			}
		}

		if( !($ctxProcess[ 'compatView' ]??null) && ($settJs[ $isCrit === true ? 'group' : ( $isCrit === 'critSpec' ? 'groupCritSpec' : ( $isCrit === null ? 'groupSpec' : 'groupNonCrit' ) ) ]??null) )
		{
			if( $ctxProcess[ 'mode' ] & 1 )
			{
				if( is_string( $cont ) && Gen::GetArrField( $settJs, array( 'groupTrC' ) ) )
					$cont = 'try{' . $cont;

				if( ($ctxProcess[ 'debug' ]??null) && is_string( $cont ) )
					$cont = '/* ################################################################################################################################################ */' . "\r\n" . '/* DEBUG: seraph-accel JS src="' . $src . '" */' . "\r\n\r\n" . $cont;

				$bGrpExcl = ( Gen::GetArrField( $settJs, array( 'groupExclMdls' ) ) && $type == 'module' ) || IsObjInRegexpList( $aGrpExcl, array( 'src' => $src, 'id' => $id, 'body' => $cont ) );

				if( $cont === false || $bGrpExcl )
					$cont = '';

				if( strlen( $cont ) )
				{
					if( Gen::GetArrField( $settJs, array( 'groupTrC' ) ) )
						$cont = $cont . '}catch(e){console.error(e)}';

					if( substr( $cont, -1, 1 ) == ';' )
						$cont .= "\r\n";
					else
						$cont .= ";\r\n";

					if( ($ctxProcess[ 'chunksEnabled' ]??null) && Gen::GetArrField( $settCache, array( 'chunks', 'js' ) ) )
						$cont .= ContentMarkGetSep();

					if( $optLoad && $isCrit === false && $delayNotCritNeeded )
						$cont .= 'seraph_accel_gzjydy();';

				}

				$contGroup = &$contGroups[ $isCrit === true ? 'crit' : ( $isCrit === 'critSpec' ? 'critSpec' : ( $isCrit === null ? 'spec' : '' ) ) ];

				if( ( $item -> hasAttribute( 'defer' ) && $item -> getAttribute( 'defer' ) !== false ) && !( $item -> hasAttribute( 'async' ) && $item -> getAttribute( 'async' ) !== false ) && $src )
				{
					if( $bGrpExcl )
						array_splice( $contGroup[ 1 ], count( $contGroup[ 1 ] ), 0, array( $item, '' ) );

					$contGroup[ 1 ][ count( $contGroup[ 1 ] ) - 1 ] .= $cont;
				}
				else
				{
					if( $bGrpExcl )
					{
						array_splice( $contGroup[ 1 ], $contGroup[ 0 ][ 0 ], 1, array( substr( $contGroup[ 1 ][ $contGroup[ 0 ][ 0 ] ], 0, $contGroup[ 0 ][ 1 ] ), $item, substr( $contGroup[ 1 ][ $contGroup[ 0 ][ 0 ] ], $contGroup[ 0 ][ 1 ] ) ) );
						$contGroup[ 0 ][ 0 ] += 2;
						$contGroup[ 0 ][ 1 ] = 0;
					}

					$contGroup[ 1 ][ $contGroup[ 0 ][ 0 ] ] = substr_replace( $contGroup[ 1 ][ $contGroup[ 0 ][ 0 ] ], $cont, $contGroup[ 0 ][ 1 ], 0 );
					$contGroup[ 0 ][ 1 ] += strlen( $cont );
				}

				unset( $contGroup );
			}

			$item -> parentNode -> removeChild( $item );
		}
		else if( $delay && $isCrit === false && ($settJs[ 'preLoadEarly' ]??null) )
			Script_SrcAddPreloading( $item, $src, $head, $doc );
	}

	if( $optLoad )
	{
		foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'iframe' ) ) as $item )
		{
			if( ContentProcess_IsAborted( $ctxProcess, $settCache ) ) return( true );

			if( HtmlNd::FindUpByTag( $item, 'noscript' ) )
				continue;

			if( !Scripts_IsElemAs( $ctxProcess, $doc, $settJs, $item ) )
				continue;

			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$src = HtmlNd::GetAttrVal( $item, 'src' );
			$id = HtmlNd::GetAttrVal( $item, 'id' );
			$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, $src );

			$detectedPattern = null;
			$isCrit = GetObjSrcCritStatus( $settNonCrit, $critSpec, $specs, $srcInfo, $src, $id, null, $detectedPattern );

			Fullness_AdjustUrl( $ctxProcess, $src, ($srcInfo[ 'srcUrlFullness' ]??null) );
			if( $src )
				$item -> setAttribute( 'src', $src );
			$item -> setAttribute( 'async', '' );

			if( ($ctxProcess[ 'debug' ]??null) )
				$item -> setAttribute( 'seraph-accel-debug', 'status=' . ( $isCrit === true ? 'critical' : ( $isCrit === 'critSpec' ? 'criticalSpecial' : ( $isCrit === null ? 'special' : 'nonCritical' ) ) ) . ';' . ( $detectedPattern ? ' detectedPattern="' . $detectedPattern . '"' : '' ) );

			if( $isCrit )
				continue;

			$delay = ( $isCrit === null ) ? $specsDelayTimeout : $notCritsDelayTimeout;
			if( !$delay )
				continue;

			HtmlNd::RenameAttr( $item, 'src', 'data-src' );
			HtmlNd::RenameAttr( $item, 'onload', 'data-onload' );
			HtmlNd::RenameAttr( $item, 'onerror', 'data-onerror' );
			if( $isCrit === null )
			{
				$item -> setAttribute( 'type', 'o/js-lzls' );
				$delaySpecNeeded = true;
			}
			else
			{
				$item -> setAttribute( 'type', 'o/js-lzl' );
				$delayNotCritNeeded = true;
			}
		}
	}

	if( !( $ctxProcess[ 'mode' ] & 1 ) )
		return( true );

	$itemGrpCritLast = null;
	foreach( $contGroups as $contGroupId => $contGroup )
	{
		foreach( $contGroup[ 1 ] as $cont )
		{
			if( !$cont )
				continue;

			if( is_string( $cont ) )
			{
				$item = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$item -> setAttribute( $item, 'type', 'text/javascript' );

				if( !GetContentProcessorForce( $sett ) && ($ctxProcess[ 'chunksEnabled' ]??null) && Gen::GetArrField( $settCache, array( 'chunks', 'js' ) ) )
				{
					$cont = apply_filters( 'seraph_accel_js_content', $cont, true );
					$idSub = ( string )( $ctxProcess[ 'subCurIdx' ]++ ) . '.js';
					$ctxProcess[ 'subs' ][ $idSub ] = $cont;
					$src = ContentProcess_GetGetPartUri( $ctxProcess, $idSub );
				}
				else
				{
					$cont = str_replace( ContentMarkGetSep(), '', $cont );
					$cont = apply_filters( 'seraph_accel_js_content', $cont, true );
					if( !UpdSc( $ctxProcess, $settCache, 'js', $cont, $src ) )
						return( false );
				}

				Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, 'js' );
				Fullness_AdjustUrl( $ctxProcess, $src );
				$item -> setAttribute( 'src', $src );
			}
			else
				$item = $cont;

			if( $contGroupId === 'crit' || $contGroupId === 'critSpec' )
			{
				HtmlNd::InsertAfter( $head, $item, $itemGrpCritLast, true );
				$itemGrpCritLast = $item;

				if( $contGroupId === 'critSpec' )
					$item -> setAttribute( 'defer', '' );

				continue;
			}

			if( is_string( $cont ) && $optLoad )
			{
				$delay = ( $contGroupId === 'spec' ) ? $specsDelayTimeout : $notCritsDelayTimeout;
				if( $delay )
				{

					if( $contGroupId === 'spec' )
					{
						$item -> setAttribute( 'type', 'o/js-lzls' );
						$delaySpecNeeded = true;

						$delay = $specsDelayTimeout;
					}
					else
					{
						$item -> setAttribute( 'type', 'o/js-lzl' );
						$delayNotCritNeeded = true;

						$delay = $notCritsDelayTimeout;
					}

					if( $contGroupId === '' && ($settJs[ 'preLoadEarly' ]??null) )
						Script_SrcAddPreloading( $item, $src, $head, $doc );
				}
			}

			$body -> appendChild( $item );
		}
	}

	if( $delayNotCritNeeded || $delaySpecNeeded )
	{

		{

			$item = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$item -> setAttribute( 'type', 'text/javascript' );

			HtmlNd::SetValFromContent( $item, "function seraph_accel_cmn_calcSizes(a){var b=a.ownerDocument.body;b.style.setProperty(\"--seraph-accel-client-width\",\"\"+a.clientWidth+\"px\");b.style.setProperty(\"--seraph-accel-client-width-px\",\"\"+a.clientWidth);b.style.setProperty(\"--seraph-accel-client-height\",\"\"+a.clientHeight+\"px\");b.style.setProperty(\"--seraph-accel-dvh\",\"\"+window.innerHeight+\"px\")}(function(a){a.addEventListener(\"seraph_accel_calcSizes\",function(b){seraph_accel_cmn_calcSizes(a.documentElement)},{capture:!0,passive:!0});seraph_accel_cmn_calcSizes(a.documentElement)})(document)" );
			$body -> insertBefore( $item, $body -> firstChild );
		}

		$ctxProcess[ 'jsDelay' ] = array( 'a' => array( '_E_A1_', '_E_A2_', '_E_TM1_', '_E_TM2_', '_E_CJSD_', '_E_AD_', '_E_FSCRLD_', '_E_FCD_', '_E_FCDECS_', '_E_PRL_', '_E_LF_' ), 'v' => array( '"o/js-lzl"', '"o/js-lzls"', $notCritsDelayTimeout ? $notCritsDelayTimeout : 0, $specsDelayTimeout ? $specsDelayTimeout : 0, ($settJs[ 'cplxDelay' ]??null) ? 1 : 0, Gen::GetArrField( $settJs, array( 'aniDelay' ), 250 ), $notCritsDelayTimeout ? Gen::GetArrField( $settJs, array( 'scrlDelay' ), 0 ) : 0, Gen::GetArrField( $settJs, array( 'clk', 'delay' ), 250 ), json_encode( ( array )($ctxProcess[ 'aJsDelay_firstClickDelayExclCssSel' ]??null) ), ($settJs[ 'preLoadEarly' ]??null) ? 0 : 1, ($settJs[ 'loadFast' ]??null) ? 1 : 0 ) );

		unset( $firstClickDelayExclCssSel );
	}

	if( isset( $ctxProcess[ 'lrnDsc' ] ) )
	{
		if( isset( $ctxProcess[ 'lrn' ] ) )
		{
			if( $ctxProcess[ 'mode' ] & 4 )
				if( !$ctxOpt -> writeLrnData( $ctxProcess, $ctxProcess[ 'lrnDsc' ], $ctxProcess[ 'lrnDataPath' ] ) )
					return( false );
		}
		else
			$ctxOpt -> readLrnDataFinish( $ctxProcess, $ctxProcess[ 'lrnDsc' ], $ctxProcess[ 'lrnDataPath' ] );
	}

	return( true );
}

function Scripts_ProcessAddRtn( &$ctxProcess, $sett, $settCache, $settContPr, $settJs, $settCdn, $doc, $prms )
{

	$cont = str_replace( $prms[ 'a' ], $prms[ 'v' ], "(function(q,l,r,N,I,t,G,S,T,O,U,V,W,X){function P(){q.seraph_accel_js_lzl_initScrCustom&&q.seraph_accel_js_lzl_initScrCustom();if(w){var a=q[function(f){var c=\"\";f.forEach(function(b){c+=String.fromCharCode(b+3)});return c}([103,78,114,98,111,118])];!w.dkhjihyvjed&&a?w=void 0:(w.dkhjihyvjed=!0,w.jydy(a))}}function F(a,f=0,c){function b(){if(!a)return[];for(var d=[].slice.call(l.querySelectorAll('[type=\"'+a+'\"]')),e=0,m=d.length;e<m;e++){var h=d[e];if(h.hasAttribute(\"defer\")&&!1!==h.defer&&(!h.hasAttribute(\"async\")||\n!1===h.async)&&h.hasAttribute(\"src\")||\"module\"==h.getAttribute(\"data-type\"))d.splice(e,1),d.push(h),e--,m--}return d}function k(d=!1){P();X||d?x():r(x,f)}function n(d){var e=d.ownerDocument,m=e.seraph_accel_njsujyhmaeex={wdybryijnud:d.nextSibling,wyheujyhm:e[function(h){var g=\"\";h.forEach(function(u){g+=String.fromCharCode(u+3)});return g}([116,111,102,113,98])],wyhedbujyhm:e[function(h){var g=\"\";h.forEach(function(u){g+=String.fromCharCode(u+3)});return g}([116,111,102,113,98,105,107])],ujyhm:function(h){var g=\ne.createElement(\"span\");d.parentNode.insertBefore(g,this.seraph_accel_njsujyhmaeex.wdybryijnud);g.outerHTML=h},dbujyhm:function(h){this.write(h+\"\\n\")}};e[function(h){var g=\"\";h.forEach(function(u){g+=String.fromCharCode(u+3)});return g}([116,111,102,113,98])]=m.ujyhm;e[function(h){var g=\"\";h.forEach(function(u){g+=String.fromCharCode(u+3)});return g}([116,111,102,113,98,105,107])]=m.dbujyhm}function p(d){d=d.ownerDocument;var e=d.seraph_accel_njsujyhmaeex;e&&(d[function(m){var h=\"\";m.forEach(function(g){h+=\nString.fromCharCode(g+3)});return h}([116,111,102,113,98])]=e.wyheujyhm,d[function(m){var h=\"\";m.forEach(function(g){h+=String.fromCharCode(g+3)});return h}([116,111,102,113,98,105,107])]=e.wyhedbujyhm,delete d.seraph_accel_njsujyhmaeex)}function x(){var d=v.shift();if(d)if(d.parentNode){var e=l.seraph_accel_usbpb(d.tagName),m=d.attributes;if(m)for(var h=0;h<m.length;h++){var g=m[h],u=g.value;g=g.name;\"type\"!=g&&(\"data-type\"==g&&(g=\"type\"),\"data-src\"==g&&(g=\"src\"),\"data-onload\"==g&&(g=\"onload\"),\"data-onerror\"==\ng&&(g=\"onerror\"),e.setAttribute(g,u))}e.textContent=d.textContent;m=!e.hasAttribute(\"async\");h=e.hasAttribute(\"src\");g=e.hasAttribute(\"nomodule\");m&&n(e);if(h=m&&h&&!g)e.onload=e.onerror=function(){e._seraph_accel_loaded||(e._seraph_accel_loaded=!0,p(e),k())};d.parentNode.replaceChild(e,d);h||(m&&p(e),k(!m))}else v=b(),x();else c&&c()}var v=b();if(W){var A=l.createDocumentFragment();v.forEach(function(d){var e=d?d.getAttribute(\"src\"):void 0;if(e){var m=l.createElement(\"link\");m.setAttribute(\"rel\",\n\"module\"==d.getAttribute(\"data-type\")?\"modulepreload\":\"preload\");m.setAttribute(\"as\",\"IFRAME\"==d.tagName?\"document\":\"script\");m.setAttribute(\"href\",e);d.hasAttribute(\"integrity\")&&m.setAttribute(\"integrity\",d.getAttribute(\"integrity\"));d.hasAttribute(\"crossorigin\")&&m.setAttribute(\"crossorigin\",d.getAttribute(\"crossorigin\"));A.appendChild(m)}});l.head.appendChild(A)}k()}function y(a,f,c){var b=l.createEvent(\"Events\");b.initEvent(f,!0,!1);if(c)for(var k in c)b[k]=c[k];a.dispatchEvent(b)}function H(a,\nf){function c(k){try{Object.defineProperty(l,\"readyState\",{configurable:!0,enumerable:!0,value:k})}catch(n){}}function b(k){t?(w&&(w.jydyut(),w=void 0),c(\"interactive\"),y(l,\"readystatechange\"),y(l,\"DOMContentLoaded\"),delete l.readyState,y(l,\"readystatechange\"),r(function(){y(q,\"load\");y(q,\"scroll\");f&&f();k()})):k()}if(z){if(3==z){function k(){t&&c(\"loading\");!0===a?F(t?N:0,10,function(){b(function(){2==z?(z=1,1E6!=G&&r(function(){H(!0)},G)):F(I)})}):F(t?N:0,0,function(){b(function(){F(I)})})}function n(){for(var p,\nx;void 0!==(p=Object.keys(seraph_accel_izrbpb.a)[0]);){for(;x=seraph_accel_izrbpb.a[p].shift();)if(x(n))return;delete seraph_accel_izrbpb.a[p]}\"scrl\"===a&&O?r(k,O):k()}n()}else 1==z&&F(I);!0===a?z--:z=0}}function J(a){return\"click\"==a||\"mouseover\"==a||\"touchstart\"==a||\"touchmove\"==a||\"touchend\"==a||\"pointerdown\"==a||\"pointermove\"==a||\"pointerup\"==a}function K(a){var f=!1;\"touchstart\"==a.type?B=!1:\"pointerdown\"==a.type?C=!1:!1===B&&\"touchmove\"==a.type?B=!0:!1===C&&\"pointermove\"==a.type&&(C=!0);if(J(a.type)){if(void 0!==\nD){f=!0;var c=!1,b=!1,k=!0;\"click\"==a.type?c=b=!0:\"mouseover\"==a.type?(c=!0,k=!1):\"touchmove\"==a.type?(f=!1,B&&(b=!0)):\"touchend\"==a.type?B&&(b=!0):\"pointerdown\"==a.type?b=!0:\"pointermove\"==a.type?(f=!1,C&&(b=!0)):\"pointerup\"==a.type&&C&&(b=!0);if(k){function p(v,A,d){return(v=n.getAttribute(d))&&(\"*\"===v||-1!=v.indexOf(\",\"+A+\",\"))}function x(v,A,d){if(!d)return!1;for(var e in d)if((\"*\"===e||-1!=e.indexOf(\",\"+A+\",\"))&&v.matches(d[e]))return!0;return!1}for(var n=a.target;n;n=n.parentNode)if(n.getAttribute){if(p(n,\na.type,\"data-lzl-clk-no\")||x(n,a.type,V))f=!1;if(p(n,a.type,\"data-lzl-clk-nodef\")){f=!0;b&&(a.preventDefault(),a.stopImmediatePropagation());break}}}if(f){b=!1;if(c)for(c=0;c<D.length;c++)if(D[c].type==a.type){b=!0;break}b||D.push(a)}}}else l.removeEventListener(a.type,K,{passive:!0});\"touchend\"==a.type?B=void 0:\"pointerup\"==a.type&&(C=void 0);void 0===E?E=!0:!1===E&&\"touchstart\"!=a.type&&\"pointerdown\"!=a.type&&H(f||\"scroll\"!=a.type&&\"wheel\"!=a.type&&\"touchmove\"!=a.type&&\"pointermove\"!=a.type?!1:\n\"scrl\",L)}function L(){r(function(){Q.forEach(function(a){l.removeEventListener(a,K,J(a)?{capture:!0,passive:!1}:{passive:!0})});l.body.classList.remove(\"seraph-accel-js-lzl-ing\");y(l,\"seraph_accel_jsFinish\");D.forEach(function(a){function f(k){return k&&!k.getAttribute(\"data-lzl-clk-no\")}function c(k,n,p){(k=k.elementFromPoint(n,p))&&k.shadowRoot&&(k=k.shadowRoot.elementFromPoint(n,p));return k}if(\"click\"==a.type||\"mouseover\"==a.type){var b=c(l,a.clientX,a.clientY);f(b)&&b.dispatchEvent(new MouseEvent(a.type,\n{view:a.view,bubbles:!0,cancelable:!0,clientX:a.clientX,clientY:a.clientY}))}else if(\"touchstart\"==a.type||\"touchmove\"==a.type||\"touchend\"==a.type)b=(b=a.changedTouches&&a.changedTouches.length?a.changedTouches[0]:void 0)?c(l,b.clientX,b.clientY):void 0,f(b)&&b.dispatchEvent(a);else if(\"pointerdown\"==a.type||\"pointermove\"==a.type||\"pointerup\"==a.type)b=c(l,a.clientX,a.clientY),f(b)&&b.dispatchEvent(a)});D=void 0},U);r(function(){l.body.classList.remove(\"seraph-accel-js-lzl-ing-ani\")},T)}function R(a){a.currentTarget&&\na.currentTarget.removeEventListener(a.type,R);!0===E?(E=!1,H(!1,L)):(E=!1,1E6!=t&&r(function(){H(!0,L)},t))}function M(){r(function(){y(l,\"seraph_accel_calcSizes\")},0)}q.location.hash.length&&(t&&(t=1),G&&(G=1));t&&r(function(){l.body.classList.add(\"seraph-accel-js-lzl-ing-ani\")});var Q=\"scroll wheel mousemove pointermove keydown click touchstart touchmove touchend pointerdown pointerup\".split(\" \"),E,B,C,w=S?{a:[],jydy:function(a){if(a&&a.fn&&!a.seraph_accel_bpb){this.a.push(a);a.seraph_accel_bpb=\n{otquhdv:a.fn[function(f){var c=\"\";f.forEach(function(b){c+=String.fromCharCode(b+3)});return c}([111,98,94,97,118])]};if(a[function(f){var c=\"\";f.forEach(function(b){c+=String.fromCharCode(b+3)});return c}([101,108,105,97,79,98,94,97,118])])a[function(f){var c=\"\";f.forEach(function(b){c+=String.fromCharCode(b+3)});return c}([101,108,105,97,79,98,94,97,118])](!0);a.fn[function(f){var c=\"\";f.forEach(function(b){c+=String.fromCharCode(b+3)});return c}([111,98,94,97,118])]=function(f){l.addEventListener(\"DOMContentLoaded\",\nfunction(c){f.bind(l)(a,c)});return this}}},jydyut:function(){for(var a=0;a<this.a.length;a++){var f=this.a[a];f.fn[function(c){var b=\"\";c.forEach(function(k){b+=String.fromCharCode(k+3)});return b}([111,98,94,97,118])]=f.seraph_accel_bpb.otquhdv;delete f.seraph_accel_bpb;if(f[function(c){var b=\"\";c.forEach(function(k){b+=String.fromCharCode(k+3)});return b}([101,108,105,97,79,98,94,97,118])])f[function(c){var b=\"\";c.forEach(function(k){b+=String.fromCharCode(k+3)});return b}([101,108,105,97,79,98,\n94,97,118])](!1)}}}:void 0;q.seraph_accel_gzjydy=P;var z=3,D=[];Q.forEach(function(a){l.addEventListener(a,K,J(a)?{capture:!0,passive:!1}:{passive:!0})});q.addEventListener(\"load\",R);q.addEventListener(\"resize\",M,!1);l.addEventListener(\"DOMContentLoaded\",M,!1);q.addEventListener(\"load\",M)})(window,document,setTimeout,_E_A1_,_E_A2_,_E_TM1_,_E_TM2_,_E_CJSD_,_E_AD_,_E_FSCRLD_,_E_FCD_,_E_FCDECS_,_E_PRL_,_E_LF_)" );

	$item = $doc -> createElement( 'script' );
	if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
		$item -> setAttribute( 'type', 'text/javascript' );

	$item -> setAttribute( 'id', 'seraph-accel-js-lzl' );

	HtmlNd::SetValFromContent( $item, $cont );

	$ctxProcess[ 'ndBody' ] -> appendChild( $item );

	ContentMarkSeparate( $item );

}

function Scripts_IsElemAs( &$ctxProcess, $doc, $settJs, $item )
{
	$items = &$ctxProcess[ 'scriptsInclItems' ];
	if( $items === null )
	{
		$items = array();

		$incls = Gen::GetArrField( $settJs, array( 'other', 'incl' ), array() );
		if( $incls )
		{
			$xpath = new \DOMXPath( $doc );

			foreach( $incls as $inclItemPath )
				foreach( HtmlNd::ChildrenAsArr( $xpath -> query( $inclItemPath, $ctxProcess[ 'ndHtml' ] ) ) as $itemIncl )
					$items[] = $itemIncl;
		}
	}

	return( in_array( $item, $items, true ) );
}

class ScriptsOpt
{
	protected $_aAdjustContCache;

	function __construct()
	{
		$this -> _aAdjustContCache = array();
	}

	static function keepLrnNeededData( &$datasDel, &$lrnsGlobDel, $dsc, $dataPath )
	{
		if( $id = Gen::GetArrField( $dsc, array( 'js', 'c' ) ) )
		{
			unset( $lrnsGlobDel[ 'js/c/' . $id . '.dat.gz' ] );

			if( ($dsc[ 'v' ]??null) < 2 )
			{
				$data = Tof_GetFileData( $dataPath . '/js/c', 'dat.gz', array( 1, function( $data, $vFrom ) { return( $data ); } ), true, $id );

				foreach( Gen::GetArrField( $data, array( 'ac' ), array() ) as $contPart )
					if( is_string( $contPart ) && strlen( $contPart ) )
						unset( $datasDel[ 'js' ][ $contPart ] );
			}
		}
	}

	function readLrnData( &$ctxProcess, $dsc, $dataPath, $bLearning )
	{
		if( $id = Gen::GetArrField( $dsc, array( 'js', 'c' ) ) )
		{
			$data = Tof_GetFileData( $dataPath . '/js/c', 'dat.gz', array( 1, function( $data, $vFrom ) { return( $data ); } ), true, $id );
			$this -> _aAdjustContCache = Gen::GetArrField( $data, array( 'ac' ), array() );
		}
	}

	function readLrnDataFinish( &$ctxProcess, $dsc, $dataPath )
	{
	}

	function writeLrnData( &$ctxProcess, &$dsc, $dataPath )
	{
		if( $this -> _aAdjustContCache )
		{
			$data = array();
			$data[ 'ac' ] = $this -> _aAdjustContCache;

			$dsc[ 'js' ][ 'c' ] = '';
			if( Gen::HrFail( @Tof_SetFileData( $dataPath . '/js/c', 'dat.gz', $data, 1, false, TOF_COMPR_MAX, $dsc[ 'js' ][ 'c' ] ) ) )
				return( false );
		}

		return( true );
	}

	public function AdjustCont( &$ctxProcess, $settCache, $settJs, $srcInfo, $src, $id, &$cont )
	{
		if( !$cont )
			return( null );

		if( isset( $ctxProcess[ 'lrnDsc' ] ) )
		{
			$contHash = md5( $cont, true );

			$res = ($this -> _aAdjustContCache[ $contHash ]??null);
			if( $res === false )
			{
				return( null );
			}
			else if( $res === '' )
			{
				$cont = '';
				return( true );
			}
			else if( is_string( $res ) && strlen( $res ) )
			{
				$contPart = ReadSc( $ctxProcess, $settCache, $res, 'js' );
				if( $contPart !== null )
				{
					$cont = $contPart;
					DepsAdd( $ctxProcess[ 'deps' ], 'js', $res );
					return( true );
				}
			}
		}

		$res = ScriptsOpt::_AdjustCont( $ctxProcess, $settCache, $settJs, $srcInfo, $src, $id, $cont );

		if( isset( $ctxProcess[ 'lrnDsc' ] ) && isset( $ctxProcess[ 'lrn' ] ) )
		{
			if( !$res )
				$this -> _aAdjustContCache[ $contHash ] = false;
			else
			{
				$oiCi = ( $cont !== '' ) ? UpdSc( $ctxProcess, $settCache, 'js', $cont ) : '';
				if( $oiCi === false )
					return( false );

				$this -> _aAdjustContCache[ $contHash ] = $oiCi;
			}
		}

		return( $res ? true : null );
	}

	static function _AdjustCont( &$ctxProcess, $settCache, $settJs, $srcInfo, $src, $id, &$cont )
	{
		$adjusted = false;
		if( ( !$srcInfo || !($srcInfo[ 'ext' ]??null) ) && Gen::GetArrField( $settJs, array( 'min' ), false ) && !IsObjInRegexpList( Gen::GetArrField( $settJs, array( 'minExcls' ), array() ), array( 'src' => $src, 'id' => $id, 'body' => $cont ) ) )
		{
			if( !( $ctxProcess[ 'mode' ] & 4 ) )
			{

				$ctxProcess[ 'modeReq' ] |= 4;
				return( false );
			}

			$contNew = trim( ScriptsOpt::JsMinify( $cont, ($settJs[ 'minMthd' ]??null), ($settJs[ 'cprRem' ]??null) ) );
			if( $cont != $contNew )
			{
				$cont = $contNew;
				$adjusted = true;
			}
		}

		return( $adjusted );
	}

	static function JsMinify( $cont, $method, $removeFlaggedComments = false )
	{
		try
		{
			switch( $method )
			{
				case 'jshrink':		$contNew = JShrink\Minifier::minify( $cont, array( 'flaggedComments' => !$removeFlaggedComments ) ); break;
				default:			$contNew = JSMin\JSMin::minify( $cont, array( 'removeFlaggedComments' => $removeFlaggedComments ) ); break;
			}
		}
		catch( \Exception $e )
		{
			return( $cont );
		}

		if( !$contNew )
			return( $cont );

		$cont = $contNew;

		if( ($ctxProcess[ 'debug' ]??null) )
			$cont = '/* DEBUG: MINIFIED by seraph-accel */' . $cont;

		return( $cont );
	}
}

