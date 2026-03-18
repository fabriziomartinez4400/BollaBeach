<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

spl_autoload_register(
	function( $class )
	{
		if( strpos( $class, 'PhpCss' ) === 0 )
			@include_once( 'D:/Data/Temp/PhpCss/' . str_replace( '\\', '/', $class ) . '.php' );
	}
);

function _CssExtractImports_GetPosRange( &$aCommentRange, $pos )
{
	foreach( $aCommentRange as &$commentRange )
		if( $commentRange[ 0 ] <= $pos && $pos < $commentRange[ 1 ] )
			return( $commentRange );

	return( false );
}

function _CssExtractImports( &$cont )
{

	$res = array();

	$m = array(); preg_match_all( '#@import(?:\\s+url\\([^()]*\\)|\\s*"[^"]*"|\\s*\'[^\']*\')?[^@{}\\r\\n;]*(;\\s*\\n?|\\s*$)#S', $cont, $m, PREG_OFFSET_CAPTURE );
	if( !$m )
		return( $res );

	$aCommentRange = array();
	for( $offs = 0; ; )
	{
		$posCommentBegin = strpos( $cont, '/*', $offs );
		if( $posCommentBegin === false )
			break;

		$posCommentEnd = strpos( $cont, '*/', $posCommentBegin + 2 );
		if( $posCommentEnd === false )
			$posCommentEnd = strlen( $cont );
		else
			$posCommentEnd += 2;

		$aCommentRange[] = array( $posCommentBegin, $posCommentEnd );
		$offs = $posCommentEnd;
	}
	unset( $posCommentBegin, $posCommentEnd );

	for( $offs = 0; ; )
	{
		$posFirstBlock = strpos( $cont, '{', $offs );
		if( $posFirstBlock === false )
		{
			$posFirstBlock = strlen( $cont );
			break;
		}

		$range = _CssExtractImports_GetPosRange( $aCommentRange, $posFirstBlock );
		if( !$range )
			break;

		$offs = $range[ 1 ];
	}

	for( $i = count( $m[ 0 ] ); $i > 0; $i-- )
	{
		$mi = $m[ 0 ][ $i - 1 ];

		$offs = $mi[ 1 ];
		$len = strlen( $mi[ 0 ] );

		if( $offs > $posFirstBlock )
			continue;

		if( _CssExtractImports_GetPosRange( $aCommentRange, $offs ) )
			continue;

		$s = substr( $cont, $offs, $len );
		$suffix = $m[ 1 ][ $i - 1 ][ 0 ];
		if( !Gen::StrStartsWith( $suffix, ';' ) )
			$s = substr_replace( $s, ';' . ( Gen::StrStartsWith( $suffix, "\n" ) ? "\n" : "\r\n" ), $len - strlen( $suffix ) );

		array_splice( $res, 0, 0, array( $s ) );
		$cont = substr_replace( $cont, '', $offs, $len );
	}

	return( $res );
}

function _CssInsertImports( &$cont, $imports )
{
	$contHead = implode( '', array_merge( _CssExtractImports( $cont ), $imports ) );
	if( $contHead )
		$cont = $contHead . $cont;

}

function _CssCutCharset( &$cont, &$contPrefix = null )
{
	$m = array();
	if( substr( $cont, 0, 8 ) != '@charset' || !preg_match( '/^@charset\\s+"([\\w-]+)"\\s*;/iS', $cont, $m, PREG_OFFSET_CAPTURE ) )
		return;

	if( $m[ 0 ][ 1 ] == 0 )
	{
		$contPrefix = '@charset "' . strtoupper( $m[ 1 ][ 0 ] ) . '";';
		$cont = substr( $cont, strlen( $m[ 0 ][ 0 ] ) );
	}
}

function Style_ProcessCont( &$ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $head, &$item, $srcInfo, $src, $id,  $cont, $contAdjusted, $isInlined, $status, $isNoScript, &$contGroups, $optLoad = null )
{
	if( !is_string( $cont ) )
		return( null );

	$contPrefix = RemoveZeroSpace( $cont, '' );
	if( $contPrefix )
		$contPrefix = '@charset "' . $contPrefix . '";';

	_CssCutCharset( $cont, $contPrefix );

	$group = null;
	if( !$isNoScript )
	{
		if( $status == 'crit' )
		{
			if( ($settCss[ 'group' ]??null) )
				$group = !!( $ctxProcess[ 'mode' ] & 4 ) && !!($settCss[ 'groupCombine' ]??null);
		}
		else if( $status == 'fonts' )
		{
			if( ($settCss[ 'groupFont' ]??null) )
				$group = !!( $ctxProcess[ 'mode' ] & 4 ) && !!($settCss[ 'groupFontCombine' ]??null);
		}
		else
		{
			if( ($settCss[ 'groupNonCrit' ]??null) )
				$group = !!( $ctxProcess[ 'mode' ] & 4 ) && !!($settCss[ 'groupNonCritCombine' ]??null);
		}
	}

	$contImports = array();
	if( $group )
	{
		$contImports = _CssExtractImports( $cont );

		$media = HtmlNd::GetAttrVal( $item, 'media' );
		if( $media && $media != 'all' )
		{
			if( $onload = $item -> getAttribute( 'onload' ) )
			{
				if( preg_match( '@^\\s*this\\s*\\.\\s*media\\s*=\\s*this\\s*\\.\\s*dataset\\s*\\.\\s*media\\s*;\\s*delete\\s+this\\s*\\.\\s*dataset\\s*\\.\\s*media\\s*;\\s*this\\s*\\.\\s*removeAttribute\\s*\\(\\s*[\'"]onload[\'"]\\s*\\)\\s*;?\\s*$@S', $onload ) )
					$media = $item -> getAttribute( 'data-media' );
				else
					$media = '';
			}

			$media = trim( $media );
			if( $media && $media != 'all' )
				$cont = '@media ' . $media . "{\r\n" . $cont . "\r\n}";
		}
	}

	if( ( $contAdjusted || $group ) && ($ctxProcess[ 'debug' ]??null) )
		$cont = '/* ################################################################################################################################################ */' . "\r\n" . '/* DEBUG: seraph-accel CSS src="' . $src . '" */' . "\r\n\r\n" . $cont;

	if( $group )
	{
		$contGroup = &$contGroups[ $status ];
		_CssInsertImports( $contGroup, $contImports );
		$contGroup .= $cont . "\r\n";

		if( ($ctxProcess[ 'chunksEnabled' ]??null) && Gen::GetArrField( $settCache, array( 'chunks', 'css' ) ) )
			$contGroup .= ContentMarkGetSep();

		if( ($ctxProcess[ 'debug' ]??null) )
			$contGroup .= "\r\n";

		if( $item -> parentNode )
			$item -> parentNode -> removeChild( $item );
	}
	else
	{
		$cont = $contPrefix . $cont;

		if( !Style_ProcessCont_ItemApply( $ctxProcess, $sett, $settCache, $settCss, $settCdn, $head, $item, $srcInfo, $src, $id,  $cont, $contAdjusted, $isInlined, $status, $isNoScript, $group !== null, false, $optLoad ) )
			return( false );
	}

	return( true );
}

function _Style_ProcessCont_ItemApply_EscapeInline( $cont )
{
	return( str_replace( array( '<style', '</style' ), array( '&lt;style', '&lt;/style' ), $cont ) );
}

function _Style_InlineBodyAsScr( $cont )
{
	return( 'data:text/css,' . _Scripts_EncodeBodyAsSrc( $cont ) );
}

function Style_ProcessCont_ItemApply( &$ctxProcess, $sett, $settCache, $settCss, $settCdn, $head, &$item, $srcInfo, $src, $id,  $cont, $contAdjusted, $isInlined, $status, $isNoScript, $repos, $composite = false, $optLoad = null )
{
	$itemsAfter = array();
	if( $optLoad === null )
		$optLoad = ($settCss[ 'optLoad' ]??null);
	$inlineAsSrc = ($settCss[ 'inlAsSrc' ]??null);

	if( ($ctxProcess[ 'compatView' ]??null) )
		$inlineAsSrc = false;

	$inline = $isInlined;
	if( $optLoad && !$isNoScript )
	{
		$inline = ( ($ctxProcess[ 'compatView' ]??null) !== 'cm' ) && !!($settCss[ $status == 'crit' ? 'inlCrit' : 'inlNonCrit' ]??null);
		if( HtmlNd::FindUpByTag( $item, 'svg' ) && $isInlined )
			$inline = true;
	}

	$media = $item -> getAttribute( 'media' );

	$cont = str_replace( '::bhkdyqcetujyi::', (

		( $inlineAsSrc && $inline && !$isInlined  ) ) ? $ctxProcess[ 'siteDomainUrl' ] : '', $cont );

	ContUpdateItemIntegrity( $item, $cont );

	if( $inline )
	{

		if( $composite )
		    $cont = str_replace( ContentMarkGetSep(), '', $cont );
		$cont = apply_filters( 'seraph_accel_css_content', $cont, false );

		if( !$isInlined )
		{
			if( $inlineAsSrc )
			    $item -> setAttribute( 'href', _Style_InlineBodyAsScr( $cont ) );
			else
			{
				$item = HtmlNd::SetTag( $item, 'style', array( 'rel', 'as', 'href' ) );
				HtmlNd::SetValFromContent( $item, _Style_ProcessCont_ItemApply_EscapeInline( $cont ) );
			}
		}
		else if( $contAdjusted )
			HtmlNd::SetValFromContent( $item, _Style_ProcessCont_ItemApply_EscapeInline( $cont ) );
	}
	else
	{
		if( $isInlined )
		{
			$item = HtmlNd::SetTag( $item, 'link', true );
			$item -> setAttribute( 'rel', 'stylesheet' );

		}

		if( $contAdjusted || $isInlined )
		{
			if( $composite && !GetContentProcessorForce( $sett ) && ($ctxProcess[ 'chunksEnabled' ]??null) && Gen::GetArrField( $settCache, array( 'chunks', 'css' ) ) )
			{
				$cont = apply_filters( 'seraph_accel_css_content', $cont, true );

				$idSub = ( string )( $ctxProcess[ 'subCurIdx' ]++ ) . '.css';
				$ctxProcess[ 'subs' ][ $idSub ] = $cont;
				$src = ContentProcess_GetGetPartUri( $ctxProcess, $idSub );
			}
			else
			{
				if( $composite )
					$cont = str_replace( ContentMarkGetSep(), '', $cont );
				$cont = apply_filters( 'seraph_accel_css_content', $cont, true );

				if( !strlen( $cont ) )
					$src = _Style_InlineBodyAsScr( '' );
				else if( !UpdSc( $ctxProcess, $settCache, 'css', $cont, $src ) )
					return( false );
			}
		}

		Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, 'css' );
		Fullness_AdjustUrl( $ctxProcess, $src, $srcInfo ? ($srcInfo[ 'srcUrlFullness' ]??null) : null );

		$item -> nodeValue = '';
		$item -> setAttribute( 'href', $src );

		if( !($ctxProcess[ 'compatView' ]??null) && $optLoad && !$isNoScript && $status != 'crit' )
		{

			{
				$itemCopy = $item -> cloneNode( true );
				$itemNoScript = $item -> ownerDocument -> createElement( 'noscript' );
				if( !$itemCopy || !$itemNoScript )
					return( false );
				$itemCopy -> removeAttribute( 'id' );

				$itemNoScript -> setAttribute( 'lzl', '' );
				$itemNoScript -> appendChild( $itemCopy );

				$itemsAfter[] = $itemNoScript;
			}

			if( $status == 'fonts' )
			{
				$itemPreLoad = $item -> cloneNode( true );
				$itemPreLoad -> setAttribute( 'rel', 'preload' );
				$itemPreLoad -> setAttribute( 'as', 'style' );
				$itemsAfter[] = $itemPreLoad;
			}

			$item -> setAttribute( 'rel', 'stylesheet/lzl' . ( $status == 'nonCrit' ? '-nc' : '' ) );
			$ctxProcess[ 'lazyloadStyles' ][ $status ] = ( $status != 'fonts' ) && ($settCss[ 'delayNonCritWithJs' ]??null) ? 'withScripts' : '';

		}
	}

	if( $repos )
	{
		if( $item -> parentNode )
			$item -> parentNode -> removeChild( $item );

		if( $status == 'crit' )
		{
			$head -> appendChild( $item );
		}
		else
		{
			if( $item -> nodeName != 'style' )
				$head -> appendChild( $item );
			else
				$ctxProcess[ 'ndBody' ] -> appendChild( $item );
		}
	}

	$itemInsertAfter = $item;
	foreach( $itemsAfter as $itemAfter )
	{
		HtmlNd::InsertAfter( $item -> parentNode, $itemAfter, $itemInsertAfter );
		$itemInsertAfter = $itemAfter;
	}

	if( ($ctxProcess[ 'chunksEnabled' ]??null) )
	{
		ContentMarkSeparate( $item, false, 1 );
		ContentMarkSeparate( $itemsAfter ? $itemsAfter[ count( $itemsAfter ) - 1 ] : $item, false, 2 );
	}

	return( true );
}

function _EmbedStyles_Process( $processor, &$ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $doc, &$aCritFonts, &$aImgSzAlternativesBlocksGlobal = null )
{
	$itemClassIdx = 0;
	for( $item = null; $item = HtmlNd::GetNextTreeChild( $ctxProcess[ 'ndBody' ], $item );  )
	{
		if( ContentProcess_IsAborted( $ctxProcess, $settCache ) ) return;

		if( $item -> nodeType != XML_ELEMENT_NODE )
			continue;

		$skip = false;
		switch( $item -> nodeName )
		{
		case 'script':
		case 'noscript':
		case 'style':
		case 'img':
		case 'picture':
		case 'source':
			$skip = true;
			break;
		}

		if( $skip )
			continue;

		$style = $item -> getAttribute( 'style' );
		if( !$style )
			continue;

		if( $processor -> IsTraceEnabled() )
			$processor -> SetCurObjectId( GetSourceItemTracePath( $ctxProcess, $item -> getNodePath() . '[@style]' ) );

		$ruleSet = $processor -> ParseRuleSet( $style );
		if( !$ruleSet )
			continue;

		$adjustes = new AnyObj();
		$adjustes -> item = $item;
		$adjustes -> lazyBg = null;
		$adjustes -> imgSzAlternatives = null;
		if( $aImgSzAlternativesBlocksGlobal !== null && !Images_CheckSzAdaptExcl( $ctxProcess, $doc, $settImg, $item ) )
			$adjustes -> imgSzAlternatives = new ImgSzAlternatives();

		$r = StyleProcessor::AdjustRuleSet( $ruleSet, $aCritFonts, $adjustes, $doc, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, null, null, true );
		if( $r === false )
			return( false );

		if( $r )
		{
			$style = $ruleSet -> renderWhole( $processor -> GetRenderFormatMin() );
			$style = str_replace( '::bhkdyqcetujyi::', '', $style );
			$item -> setAttribute( 'style', $style );
		}

		if( $adjustes -> imgSzAlternatives && !$adjustes -> imgSzAlternatives -> isEmpty() )
		{
			$itemCssClass = 'seraph-accel-bg-' . $itemClassIdx++;
			StyleProcessor::AdjustItemAdaptImg( $ctxProcess, $settImg, $doc, $item, $itemCssClass );
			$aImgSzAlternativesBlocksGlobal[] = array( 'sels' => array( '.' . $itemCssClass ), 'adjs' => $adjustes );
		}
	}

	return( true );
}

function Styles_Process( &$ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $doc )
{
	if( ($ctxProcess[ 'isAMP' ]??null) )
	    return( true );

	$adjustCont = ($settCss[ 'optLoad' ]??null)
		|| ($settCss[ 'min' ]??null)
		|| ($settCss[ 'fontOptLoad' ]??null)
		|| !($settCss[ 'fontCrit' ]??null)
		|| Gen::GetArrField( $settCss, array( 'font', 'deinlLrg' ), false )
		|| ( Gen::GetArrField( $settCss, array( 'font', 'inl', 'enable' ), false ) && Gen::GetArrField( $settCss, array( 'font', 'inl', 'items' ), array() ) )
		|| ( ($settCss[ 'group' ]??null) && ($settCss[ 'groupCombine' ]??null) )
		|| ( ($settCss[ 'groupNonCrit' ]??null) && ($settCss[ 'groupNonCritCombine' ]??null) )
		|| ($settImg[ 'szAdaptBg' ]??null)
		|| Gen::GetArrField( $settCdn, array( 'enable' ), false );

	if( !( $ctxProcess[ 'mode' ] & 4 ) )
	{

	}

	$skips = Gen::GetArrField( $settCss, array( 'skips' ), array() );
	if( !( $adjustCont || ($settCss[ 'group' ]??null) || ($settCss[ 'groupNonCrit' ]??null) || ($settCss[ 'sepImp' ]??null) || $skips ) )
		return( true );

	$head = $ctxProcess[ 'ndHead' ];

	$ctxAbort = new AnyObj();
	$ctxAbort -> ctxProcess = $ctxProcess;
	$ctxAbort -> settCache = $settCache;
	$ctxAbort -> cb =
		function( $ctxAbort )
		{
			return( ContentProcess_IsAborted( $ctxAbort -> ctxProcess, $ctxAbort -> settCache ) );
		};

	$aCritFonts = ($settCss[ 'fontCritAuto' ]??null) ? array() : null;

	$processor = new StyleProcessor( $doc, $ctxProcess[ 'ndHtml' ], ($ctxProcess[ 'lrnDsc' ]??null), ($ctxProcess[ 'docSkeleton' ]??null), ($ctxProcess[ 'sklCssSelExcl' ]??null), ($settCss[ 'corrErr' ]??null), ($sett[ 'debug' ]??null), ($settCss[ 'min' ]??null) === true );
	$processor -> Init( $ctxProcess );

	$aImgSzAlternativesBlocksGlobal = ($settImg[ 'szAdaptBg' ]??null) ? array() : null;
	if( $adjustCont && !_EmbedStyles_Process( $processor, $ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $doc, $aCritFonts, $aImgSzAlternativesBlocksGlobal ) )
		return( false );

	if( $ctxAbort -> cb() ) return( true );

	{
		$autoExcls = Gen::GetArrField( $settCss, array( 'nonCrit', 'autoExcls' ), array() );

		if( isset( $ctxProcess[ 'aCssCrit' ] ) )
		{
			foreach( array_keys( $ctxProcess[ 'aCssCrit' ] ) as $autoExclsExpr )
				if( !in_array( $autoExclsExpr, $autoExcls ) )
					$autoExcls[] = $autoExclsExpr;
		}

		$autoExcls = array_map( function( $v ) { return( $v . 'S' ); }, $autoExcls );

		Gen::SetArrField( $settCss, array( 'nonCrit', 'autoExcls' ), $autoExcls );
		unset( $autoExcls );
	}

	$processor -> InitFastDomSearch( $ctxProcess, $settCache, true );

	if( $ctxAbort -> cb() ) return( true );

	if( isset( $ctxProcess[ 'lrnDsc' ] ) )
		$processor -> readLrnData( $ctxProcess, $ctxProcess[ 'lrnDsc' ], $ctxProcess[ 'lrnDataPath' ], isset( $ctxProcess[ 'lrn' ] ) );

	$settNonCrit = Gen::GetArrField( $settCss, array( 'nonCrit' ), array() );
	$contGroups = array( 'crit' => '', 'fonts' => '', 'nonCrit' => '' );

	$items = array();
	for( $item = null; $item = HtmlNd::GetNextTreeChild( $doc, $item );  )
	{
		if( $item -> nodeType != XML_ELEMENT_NODE )
			continue;

		switch( $item -> nodeName )
		{
		case 'link':
		case 'style':
			$items[] = array( 'item' => $item, 'nodePath' => $item -> getNodePath() );
			break;
		}
	}

	$hrefs = array();

	for( $i = 0; $i < count( $items ); $i++ )
	{
		$itemData = &$items[ $i ];
		$item = $itemData[ 'item' ];
		$cont = ($itemData[ 'cont' ]??null);

		if( $ctxAbort -> cb() ) return( true );

		$isInlined = ( $item -> nodeName == 'style' );

		if( !$isInlined )
		{
			$rel = HtmlNd::GetAttrVal( $item, 'rel' );
			if( $cont === null )
				if( $rel != 'stylesheet' )
				{
					if( strpos( $rel, 'preload' ) !== false && HtmlNd::GetAttrVal( $item, 'as' ) == 'style' )
					{
						if( !HtmlNd::GetAttrVal( $item, 'onload' ) )
						{
							if( ($settCss[ 'optLoad' ]??null) && $item -> parentNode )
								$item -> parentNode -> removeChild( $item );
							continue;
						}
					}
					else
						continue;
				}
		}

		$type = HtmlNd::GetAttrVal( $item, 'type' );
		if( $cont === null )
		{
			if( $type && $type != 'text/css' )
				continue;

			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			{
				if( !$type )
					$item -> setAttribute( 'type', 'text/css' );
			}
			else if( $type && ($settContPr[ 'min' ]??null) )
				$item -> removeAttribute( 'type' );
		}
		else
			unset( $itemData[ 'cont' ] );

		$src = null;
		if( !$isInlined )
		{
			$src = HtmlNd::GetAttrVal( $item, 'href' );
			if( !$src )
				continue;
		}

		$id = HtmlNd::GetAttrVal( $item, 'id' );

		$detectedPattern = null;
		if( ( $cont === null ) && IsObjInRegexpList( $skips, array( 'src' => $src, 'id' => $id ), $detectedPattern ) )
		{
			if( ($ctxProcess[ 'debug' ]??null) )
			{
				$item -> setAttribute( 'type', 'o/css-inactive' );
				$item -> setAttribute( 'seraph-accel-debug', 'status=skipped;' . ( $detectedPattern ? ' detectedPattern="' . $detectedPattern . '"' : '' ) );
			}
			else if( $item -> parentNode )
				$item -> parentNode -> removeChild( $item );

			continue;
		}

		$isNoScript = HtmlNd::FindUpByTag( $item, 'noscript' );
		if( $isNoScript && !$isInlined )
			continue;

		$srcInfo = null;
		if( !$isInlined )
		{
			if( $cont === null )
			{
				$itemPrevHref = ($hrefs[ $src ]??null);
				if( $itemPrevHref )
				{
					if( $rel == 'stylesheet' && $itemPrevHref -> getAttribute( 'rel' ) != $rel )
					{
						if( ($ctxProcess[ 'debug' ]??null) )
						{
							$itemPrevHref -> setAttribute( 'href', $src );
							$itemPrevHref -> setAttribute( 'as', 'style-inactive' );
							$itemPrevHref -> setAttribute( 'seraph-accel-debug', 'status=skipped; reason=alreadyUsed;' );
						}
						else if( $itemPrevHref -> parentNode )
							$itemPrevHref -> parentNode -> removeChild( $itemPrevHref );
					}
					else
					{
						if( ($ctxProcess[ 'debug' ]??null) )
						{
							if( $rel == 'stylesheet' )
								$item -> setAttribute( 'type', 'o/css-inactive' );
							else
								$item -> setAttribute( 'as', 'style-inactive' );
							$item -> setAttribute( 'seraph-accel-debug', 'status=skipped; reason=alreadyUsed;' );
						}
						else if( $item -> parentNode )
							$item -> parentNode -> removeChild( $item );

						continue;
					}
				}

				$hrefs[ $src ] = $item;
			}

			$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, $src );
		}

		if( $processor -> IsTraceEnabled() )
			$processor -> SetCurObjectId( GetSourceItemTracePath( $ctxProcess, ($itemData[ 'nodePath' ]??''), $srcInfo, $id ) );

		if( $cont === null )
		{
			if( !$isInlined )
			{
				$contMimeType = null;
				if( isset( $srcInfo[ 'filePath' ] ) && Gen::GetFileExt( $srcInfo[ 'filePath' ] ) == 'css' )
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

				if( $contMimeType && $cont !== false && !in_array( $contMimeType, array( 'text/css' ) ) )
				{
					$cont = false;
					if( ($sett[ 'debug' ]??null) )
						LastWarnDscs_Add( LocId::Pack( 'CssUrlWrongType_%1$s%2$s', null, array( $srcInfo[ 'url' ], $contMimeType ) ) );
				}
				else if( $cont === false && ($sett[ 'debug' ]??null) )
					LastWarnDscs_Add( LocId::Pack( 'NetDownloadErr_%1$s', 'Common', array( $srcInfo[ 'url' ] ) ) );
			}
			else
				$cont = $item -> nodeValue;

			if( $cont === false || ( !$cont && $isInlined ) )
			{

				if( $cont === false && Gen::GetArrField( $settCss, array( 'skipBad' ), false ) )
					$item -> parentNode -> removeChild( $item );

				continue;
			}

			if( ($settCss[ 'sepImp' ]??null) && is_string( $cont ) )
			{
				$contWoImports = $cont;
				$imports = _CssExtractImports( $contWoImports );
				if( $imports )
				{
					$media = $item -> getAttribute( 'media' );

					foreach( $imports as &$import )
					{
						$import = StyleProcessor::GetFirstImportSimpleAttrs( $ctxProcess, $import, $src );
						if( !$import || ( ($import[ 'media' ]??null) && ($import[ 'media' ]??null) != 'all' && $media && $media != 'all' && ($import[ 'media' ]??null) != $media ) )
						{
							$imports = false;
							break;
						}
					}
					unset( $import );

					if( $imports )
					{
						$j = 0;
						foreach( $imports as $import )
						{

							$itemImp = $doc -> createElement( 'link' );
							HtmlNd::CopyAllAttrs( $item, $itemImp, array( 'id', 'type', 'rel' ) );
							$itemImp -> setAttribute( 'rel', 'stylesheet' );
							if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
								$itemImp -> setAttribute( 'type', 'text/css' );
							if( $id )
								$itemImp -> setAttribute( 'id', $id . '-i' . $j );

							$itemImp -> setAttribute( 'href', $import[ 'url' ] );
							if( ($import[ 'media' ]??null) && ( !$media || $media == 'all' ) )
								$itemImp -> setAttribute( 'media', $import[ 'media' ] );

							$item -> parentNode -> insertBefore( $itemImp, $item );

							$itemDataImp = array( 'item' => $itemImp );

							array_splice( $items, $i + $j, 0, array( $itemDataImp ) );
							$j++;
						}

						$i--;
						$itemData[ 'cont' ] = $contWoImports;
						unset( $contWoImports );

						continue;
					}
				}

				unset( $contWoImports );
			}
		}

			if( $adjustCont && is_string( $cont ) )
			{
				$bCssRpl = false;
				if( !in_array( $item, $ctxProcess[ 'aCssRplExcl' ], true ) )
				{
					foreach( $ctxProcess[ 'aCssRpl' ] as $rplExpr => $rplData )
					{
						$r = ContentProcess_ReplaceEx( $rplExpr . 'S', $rplData, $cont, array( $ctxAbort, 'cb' ) );
						if( $r === false )
							return( true );

						if( $r )
							$bCssRpl = true;
					}
				}

				$extract = !($ctxProcess[ 'compatView' ]??null) && ($settNonCrit[ 'auto' ]??null);
				$contsExtracted = $processor -> AdjustCont( $extract, $aCritFonts, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $cont, $isInlined );
				if( $contsExtracted === false )
					return( false );

				if( $contsExtracted === null && $bCssRpl )
					$contsExtracted = true;
			}
			else
			{
				$extract = false;
				$contsExtracted = null;
			}

			if( $ctxAbort -> cb() ) return( true );

		if( $item -> hasAttribute( 'seraph-accel-noadjust' ) )
		{
			$item -> removeAttribute( 'seraph-accel-noadjust' );
			continue;
		}

		$ps = array();

		if( $extract )
		{
			if( is_array( $contsExtracted ) )
			{
				$contsExtracted[ 'nonCrit' ] = $cont;
				unset( $cont );

				$contExtractedIdDef = 'nonCrit';
				if( $isInlined && ( ($settCss[ 'optLoad' ]??null) && ($settCss[ 'inlCrit' ]??null) ) && HtmlNd::HasAttrs( $item, array( 'type', 'media' ) ) )
					$contExtractedIdDef = 'crit';

				$itemInsertAfter = null;
				foreach( $contsExtracted as $contExtractedId => $contExtracted )
				{

					if( $contExtractedIdDef == $contExtractedId )
					{
						$itemExtracted = $item;
						$idExtracted = $id;
						$itemInsertAfter = $item;
					}
					else
					{
						if( !$contExtracted )
							continue;

						$itemExtracted = $doc -> createElement( $item -> nodeName );
						if( $itemInsertAfter )
						{
							HtmlNd::InsertAfter( $item -> parentNode, $itemExtracted, $itemInsertAfter );
							$itemInsertAfter = $itemExtracted;
						}
						else
							$item -> parentNode -> insertBefore( $itemExtracted, $item );

						if( $id )
						{
							$idExtracted = $id . '-' . $contExtractedId;
							$itemExtracted -> setAttribute( 'id', $idExtracted );
						}
						else
							$idExtracted = null;

						HtmlNd::CopyAllAttrs( $item, $itemExtracted, array( 'id' ) );
					}

					$ps[] = array( 'item' => $itemExtracted, 'id' => $idExtracted,  'cont' => $contExtracted, 'contAdjusted' => true, 'status' => $contExtractedId );
				}

				unset( $contExtracted );
				unset( $itemInsertAfter );
			}
			else
			{
				$ps[] = array( 'item' => $item, 'id' => $id,  'cont' => $cont, 'contAdjusted' => $contsExtracted !== null, 'status' => 'crit', 'optLoad' => false );
			}
		}
		else
		{
			$detectedPattern = null;
			$isCrit = GetObjSrcCritStatus( $settNonCrit, null, null, $srcInfo, $src, $id, $cont, $detectedPattern );
			$ps[] = array( 'item' => $item, 'id' => $id,  'cont' => $cont, 'contAdjusted' => $contsExtracted !== null, 'status' => $isCrit ? 'crit' : 'nonCrit', 'detectedPattern' => $detectedPattern );
		}

		if( $isInlined )
		{
			if( ($ctxProcess[ 'debug' ]??null) )
				$src = 'inline:' . ($ctxProcess[ 'serverArgs' ][ 'REQUEST_SCHEME' ]??null) . '://' . $ctxProcess[ 'host' ] . ':' . ($ctxProcess[ 'serverArgs' ][ 'SERVER_PORT' ]??null) . ($ctxProcess[ 'serverArgs' ][ 'REQUEST_URI' ]??null) . ':' . $item -> getLineNo();
		}

		foreach( $ps as $psi )
		{
			if( ($ctxProcess[ 'debug' ]??null) )
				$psi[ 'item' ] -> setAttribute( 'seraph-accel-debug', 'status=' . $psi[ 'status' ] . ';' . ( ($psi[ 'detectedPattern' ]??null) ? ' detectedPattern="' . $psi[ 'detectedPattern' ] . '"' : '' ) );

			if( Style_ProcessCont( $ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $head, $psi[ 'item' ], $srcInfo, $src, $psi[ 'id' ],  $psi[ 'cont' ], ($psi[ 'contAdjusted' ]??null), $isInlined, $psi[ 'status' ], $isNoScript, $contGroups, ($psi[ 'optLoad' ]??null) ) === false )
				return( false );
		}
	}

	if( $adjustCont )
		$processor -> ApplyItems( $ctxProcess, $settImg );

	if( $aImgSzAlternativesBlocksGlobal )
	{
		$cssCritDoc = new Sabberworm\CSS\CSSList\Document();

			foreach( $aImgSzAlternativesBlocksGlobal as $aImgSzAlternativesBlocks )
				$cssCritDoc -> append( StyleProcessor::ImgSzAlternativesGetStyleBlocks( $ctxProcess, $settImg, $aImgSzAlternativesBlocks[ 'sels' ], $aImgSzAlternativesBlocks[ 'adjs' ], true ) );

		$cont = $processor -> RenderData( $cssCritDoc );
		unset( $cssCritDoc );

		$item = $doc -> createElement( 'style' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$item -> setAttribute( 'type', 'text/css' );
		HtmlNd::SetValFromContent( $item, $cont );
		$head -> appendChild( $item );

		if( Style_ProcessCont( $ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $head, $item, null, null, null,  $cont, true, true, 'crit', false, $contGroups ) === false )
			return( false );

		unset( $cont );
		unset( $item );
	}

	unset( $itemData );
	unset( $hrefs );

	if( $ctxAbort -> cb() ) return( true );

	foreach( $contGroups as $contGroupId => $contGroup )
	{
		if( !$contGroup )
			continue;

		if( $contGroupId == 'crit' )
		{
			$item = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$item -> setAttribute( 'type', 'text/css' );

			if( !Style_ProcessCont_ItemApply( $ctxProcess, $sett, $settCache, $settCss, $settCdn, $head, $item, null, null, null,  $contGroup, true, true, $contGroupId, false, true, true ) )
				return( false );
		}
		else
		{
			$item = $doc -> createElement( 'link' );
			$item -> setAttribute( 'rel', 'stylesheet' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$item -> setAttribute( 'type', 'text/css' );

			if( !Style_ProcessCont_ItemApply( $ctxProcess, $sett, $settCache, $settCss, $settCdn, $head, $item, null, null, null,  $contGroup, true, false, $contGroupId, false, true, true ) )
				return( false );
		}
	}

	$processor -> InitFastDomSearch( $ctxProcess, $settCache, false );

	if( isset( $ctxProcess[ 'lrnDsc' ] ) )
	{
		if( isset( $ctxProcess[ 'lrn' ] ) )
		{
			if( $ctxProcess[ 'mode' ] & 4 )
				if( !$processor -> writeLrnData( $ctxProcess, $ctxProcess[ 'lrnDsc' ], $ctxProcess[ 'lrnDataPath' ] ) )
					return( false );
		}
		else
			$processor -> readLrnDataFinish( $ctxProcess, $ctxProcess[ 'lrnDsc' ], $ctxProcess[ 'lrnDataPath' ] );
	}

	unset( $processor );
	return( true );
}

class StyleProcessor
{
	protected $doc;
	protected $rootElem;
	protected $xpath;
	protected $xpathSkeleton;
	protected $sklCssSelExcls;
	protected $cnvCssSel2Xpath;

	public $cssSelFs;
	protected $docFs;

	protected $cssParser;
	protected $cssParserCurObjId;
	protected $curSelector;
	protected $cssFmt;
	protected $cssFmtSimple;
	protected $cssFmtMin;
	protected $minifier;

	protected $_aDepBegin;
	protected $_aCssSelIsCritCache;
	protected $_aAdjustContCache;
	protected $_aXpathSelCache;
	protected $_xpathCssSelCache;

	private $_aCssSelIsCritRtCache;

	public $aFonts;

	protected $aVoidSelector;

	function __construct( $doc, $rootElem, $lrnDsc = null, $docSkeleton = null, $sklCssSelExcls = null, $bCorrectErrors = true, $bTrace = false, $min = true )
	{
		$this -> doc = $doc;
		$this -> rootElem = $rootElem;
		$this -> xpath = new \DOMXPath( $doc );
		$this -> xpathSkeleton = $docSkeleton ? new \DOMXPath( $docSkeleton ) : null;
		$this -> minifier = new tubalmartin\CssMin\Minifier();

		$this -> sklCssSelExcls = ContSkeleton_FltName_PrepPatterns( is_array( $sklCssSelExcls ) ? $sklCssSelExcls : array() );

		$cssParserSett = Sabberworm\CSS\Settings::create() -> withKeepComments( false ) -> withMultibyteSupport( false ) -> withLenientParsing( Sabberworm\CSS\Settings::ParseErrMed | ( $bCorrectErrors ? Sabberworm\CSS\Settings::ParseErrHigh : 0 ) );

		if( $bTrace )
			$cssParserSett -> cbExceptionTracer = array( $this, '_trace' );

		$this -> cssParser = new Sabberworm\CSS\Parsing\ParserState( '', $cssParserSett );
		$this -> cssFmtSimple = new Sabberworm\CSS\OutputFormat();
		$this -> cssFmt = self::_GetRenderFormat( $min );
		$this -> cssFmtMin = self::_GetRenderFormat();

		$this -> aVoidSelector = array( new Sabberworm\CSS\Property\Selector( '&' ) );

		$this -> docFs = $docSkeleton ? $docSkeleton : $doc;
		$this -> cssSelFs = new CssSelFs( $this -> xpathSkeleton ? $this -> xpathSkeleton : $this -> xpath, $this -> cssParser -> getSettings(), CssSelFs::F_SUBSELS_SKIP_NAME | CssSelFs::F_PSEUDO_FORCE_TO_ANY | CssSelFs::F_FUNCTION_FORCE_TO_ANY | ( ( $lrnDsc || $this -> xpathSkeleton ) ? ( CssSelFs::F_ATTR_FORCE_TO_ANY | CssSelFs::F_COMB_ADJACENT_FORCE_TO_ANY ) : 0 ) | ( $lrnDsc && isset( $lrnDsc[ 's' ] ) ? CssSelFs::F_NEG_FORCE_TO_ANY : 0 ) );

		$this -> cnvCssSel2Xpath = new Symfony\Component\CssSelector\XPath\Translator();
		$this -> cnvCssSel2Xpath -> registerExtension( new CssToXPathHtmlExtension( $this -> cnvCssSel2Xpath ) );
		$this -> cnvCssSel2Xpath -> registerExtension( new CssToXPathNormalizedAttributeMatchingExtension() );
		$this -> cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\EmptyStringParser() );
		$this -> cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\ElementParser() );
		$this -> cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\ClassParser() );
		$this -> cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\HashParser() );

		$this -> _aDepBegin = array();
		$this -> _aCssSelIsCritCache = array();
		$this -> _aAdjustContCache = array();
		$this -> _aXpathSelCache = array();

		$this -> _aCssSelIsCritRtCache = array();
		$this -> aFonts = array();

	}

	function __destruct()
	{

	}

	static private function _EscapeNonStdParts( $cont, $escape )
	{
		if( $escape )
		{
			$cont = preg_replace_callback( '@{{(\\w+)}}@',
				function( array $matches )
				{
					return( 'TMPSYM293654_DBLSCOPEOPEN' . $matches[ 1 ] . 'TMPSYM293654_DBLSCOPECLOSE' );
				}
			, $cont );

			$cont = str_replace( '&gt;', 'TMPSYM293654_GT', $cont );

			return( $cont );
		}

		$cont = str_replace( array( 'TMPSYM293654_DBLSCOPEOPEN', 'TMPSYM293654_DBLSCOPECLOSE', 'TMPSYM293654_GT' ), array( '{{', '}}', '&gt;' ), $cont );
		return( $cont );
	}

	function Init( &$ctxProcess )
	{
		$this -> _aDepBegin = Gen::ArrCopy( $ctxProcess[ 'deps' ] );
	}

	function InitFastDomSearch( &$ctxProcess, $settCache, $init = true )
	{
		for( $item = null; $item = HtmlNd::GetNextTreeChild( $this -> doc, $item );  )
		{
			if( ContentProcess_IsAborted( $ctxProcess, $settCache ) ) return;

			if( $item -> nodeType != XML_ELEMENT_NODE )
				continue;

			if( !$init )
			{
				if( $item -> hasAttribute( 'class' ) )
					$item -> setAttribute( 'class', str_replace( array( '| ', ' |', 'SEP535643564' ), array( '', '', '|' ), $item -> getAttribute( 'class' ) ) );

				$this -> cssSelFs -> _deinitItemData( $item );

				continue;
			}

			if( $item -> hasAttribute( 'class' ) )
			{
				$sClasses = Ui::SpacyClassAttr( $item -> getAttribute( 'class' ) );
				$item -> setAttribute( 'class', '| ' . str_replace( '|', 'SEP535643564', $sClasses ) . ' |' );
			}
			else
				$sClasses = null;

			if( $this -> docFs === $this -> doc )
				$this -> cssSelFs -> _initItemData( $item, $sClasses );

		}

		if( $this -> docFs === $this -> doc )
			return;

		for( $item = null; $item = HtmlNd::GetNextTreeChild( $this -> docFs, $item );  )
		{
			if( ContentProcess_IsAborted( $ctxProcess, $settCache ) ) return;

			if( $item -> nodeType != XML_ELEMENT_NODE )
				continue;

			if( !$init )
			{
				$this -> cssSelFs -> _deinitItemData( $item );
				continue;
			}

			if( $item -> hasAttribute( 'class' ) )
				$sClasses = $item -> getAttribute( 'class' );
			else
				$sClasses = null;

			$this -> cssSelFs -> _initItemData( $item, $sClasses );
		}

	}

	function AdjustCont( $extract, &$aCritFonts, &$ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, &$cont, $isInlined )
	{
		if( isset( $ctxProcess[ 'lrnDsc' ] ) )
		{
			$contHash = md5( $cont, true );

			$aInfo = ($this -> _aAdjustContCache[ $contHash ]??null);
			if( $aInfo )
			{
				$res = ($aInfo[ 'c' ]??null);
				if( is_array( $res ) )
				{
					$ok = true;
					$aDeps = array();
					foreach( $res as $contPartId => &$contPart )
					{
						if( $contPart === '' )
							continue;

						DepsAdd( $aDeps, 'css', $contPart );
						$contPart = ReadSc( $ctxProcess, $settCache, $contPart, 'css' );
						if( $contPart === null )
						{
							$ok = false;
							break;
						}
					}
					unset( $contPart );

					if( $ok )
					{
						$cont = $res[ 'nonCrit' ];
						unset( $res[ 'nonCrit' ] );
						DepsAddMany( $ctxProcess[ 'deps' ], $aDeps );
						DepsAddMany( $ctxProcess[ 'deps' ], DepsExpand( Gen::GetArrField( $aInfo, array( 'd' ), array() ) ) );
						self::_XpathSelCacheAdd( $this -> _aXpathSelCache, Gen::GetArrField( $aInfo, array( 'xslb' ), array() ) );
						return( $res );
					}
				}
				else if( $res === false )
				{
					return( null );
				}
				else if( $res === '' )
				{
					$cont = '';
					return( true );
				}
				else if( $res )
				{
					$contPart = ReadSc( $ctxProcess, $settCache, $res, 'css' );
					if( $contPart !== null )
					{
						$cont = $contPart;
						DepsAdd( $ctxProcess[ 'deps' ], 'css', $res );
						DepsAddMany( $ctxProcess[ 'deps' ], DepsExpand( Gen::GetArrField( $aInfo, array( 'd' ), array() ) ) );
						self::_XpathSelCacheAdd( $this -> _aXpathSelCache, Gen::GetArrField( $aInfo, array( 'xslb' ), array() ) );
						return( true );
					}
				}
			}

			$aDepsPrev = $ctxProcess[ 'deps' ]; $ctxProcess[ 'deps' ] = array();
			$aXpathSelCachePrev = $this -> _aXpathSelCache; $this -> _aXpathSelCache = array();
		}

		$res = $this -> _AdjustCont( $extract, $aCritFonts, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $cont, $isInlined );
		if( $res === false )
			return( false );

		if( isset( $ctxProcess[ 'lrnDsc' ] ) )
		{
			$aDeps = $ctxProcess[ 'deps' ]; $ctxProcess[ 'deps' ] = $aDepsPrev; unset( $aDepsPrev ); DepsAddMany( $ctxProcess[ 'deps' ], $aDeps );
			$aXpathSelCache = $this -> _aXpathSelCache; $this -> _aXpathSelCache = $aXpathSelCachePrev; unset( $aXpathSelCachePrev ); self::_XpathSelCacheAdd( $this -> _aXpathSelCache, $aXpathSelCache );

			if( isset( $ctxProcess[ 'lrn' ] ) )
			{
				$aInfo = array();
				if( is_array( $res ) )
				{
					$resLrn = array();
					foreach( array_merge( array( 'nonCrit' => $cont ), $res ) as $contPartId => $contPart )
					{
						$oiCi = ( $contPart !== '' ) ? UpdSc( $ctxProcess, $settCache, 'css', $contPart ) : '';
						if( $oiCi === false )
							return( false );

						$resLrn[ $contPartId ] = $oiCi;
					}

					$aInfo[ 'c' ] = $resLrn;
				}
				else if( $res === null )
					$aInfo[ 'c' ] = false;
				else
				{
					$oiCi = ( $cont !== '' ) ? UpdSc( $ctxProcess, $settCache, 'css', $cont ) : '';
					if( $oiCi === false )
						return( false );

					$aInfo[ 'c' ] = $oiCi;
				}

				if( $aDeps )
					$aInfo[ 'd' ] = DepsExpand( $aDeps, false );
				if( $aXpathSelCache )
					$aInfo[ 'xslb' ] = $aXpathSelCache;
				$this -> _aAdjustContCache[ $contHash ] = $aInfo;
			}
		}

		return( $res );
	}

	function _AdjustCont( $extract, &$aCritFonts, &$ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, &$cont, $isInlined )
	{
		RemoveZeroSpace( $cont );

		if( !( $ctxProcess[ 'mode' ] & 4 ) )
		{

			$ctxProcess[ 'modeReq' ] |= 4;

			$r = self::_AdjustContFast( $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $cont, $isInlined );
			if( $r === false )
				return( false );

			return( $r );

		}

		$cont = self::_EscapeNonStdParts( $cont, true );

		$this -> curSelector = null;
		$this -> cssParser -> setText( $cont );

		$cssDoc = new Sabberworm\CSS\CSSList\Document( $this -> cssParser -> currentPos() );

		try
		{
			$cssDoc -> parseEx( $this -> cssParser );
		}
		catch( \Exception $e )
		{
			$this -> cssParser -> traceException( $e );

			if( ($settCss[ 'corrErr' ]??null) )
			{
				if( !$extract )
					return( null );

				$contExtracted = self::_EscapeNonStdParts( $cont, false );
				$cont = '';
				return( array( 'crit' => $contExtracted ) );
			}
		}

		$cssCritDoc = new Sabberworm\CSS\CSSList\Document();
		$isCritDocAdjusted = false;
		$cssFontsDoc = new Sabberworm\CSS\CSSList\Document();
		$isFontsDocAdjusted = false;
		$isAdjusted = false;

		$blockParents = array( $cssDoc );
		$blockParentsCrit = array( $cssCritDoc );
		$blockParentsFonts = array( $cssFontsDoc );

		foreach( ( $aCritFonts !== null ? array( 'main', 'fonts' ) : array( '' ) ) as $stage )
		{
			foreach( $cssDoc -> getContents() as $i )
			{
				if( $i instanceof Sabberworm\CSS\Property\Charset )
				{
					if( !$stage || $stage == 'main' )
					{
						$cssCritDoc -> append( $i );
						$cssFontsDoc -> append( $i );
					}
				}
				else
				{
					$r = $this -> _AdjustContBlock( $stage, $aCritFonts, $i, '', $blockParents, $blockParentsCrit, $blockParentsFonts, $isCritDocAdjusted, $isFontsDocAdjusted, $extract, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined );
					if( $r === false )
						return( false );
					if( $r )
						$isAdjusted = true;
				}

				if( ContentProcess_IsAborted( $ctxProcess, $settCache ) ) return( null );
			}
		}

		$min = ($settCss[ 'min' ]??null);

		if( !$isAdjusted && !$isCritDocAdjusted && !$isFontsDocAdjusted && !$min )
		{
			if( !$extract )
				return( null );
			return( array( 'crit' => '' ) );
		}

		$cont = $this -> RenderData( $cssDoc );

		if( !$extract )
			return( ( $min || $isAdjusted ) ? true : null );

		$res = array();
		$res[ 'crit' ] = $isCritDocAdjusted ? $this -> RenderData( $cssCritDoc ) : '';
		if( $isFontsDocAdjusted )
			$res[ 'fonts' ] = $this -> RenderData( $cssFontsDoc );

		return( $res );
	}

	static private function _AdjustContFast( &$ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, &$cont, $isInlined )
	{
		$adjusted = null;
		if( ($settCss[ 'fontOptLoad' ]??null) )
		{
			$nRepl = 0;
			$cont = preg_replace( '#(@font\-face\s*\{)#isUS', '${1}' . "\r\n" . 'font-display:' . ( ($settCss[ 'fontOptLoadDisp' ]??null) ? $settCss[ 'fontOptLoadDisp' ] : 'swap' ) . ';', $cont, -1, $nRepl );
			if( $nRepl )
				$adjusted = true;
		}

		$urlDomainUrl = $isInlined ? null : Net::GetSiteAddrFromUrl( $src, true );
		$urlPath = $isInlined ? $ctxProcess[ 'requestUriPath' ] : Gen::GetFileDir( Net::Url2Uri( $src ) );

		$r = self::_CssProcessUrlsFast( $ctxProcess, $settCache, $settImg, $settCdn, $urlDomainUrl, $urlPath, $cont );
		if( $r === false )
			return( false );

		if( $r )
			$adjusted = true;

		return( $adjusted );
	}

	static private function _CssProcessUrlsFast( &$ctxProcess, $settCache, $settImg, $settCdn, $urlDomainUrl, $urlPath, &$cont )
	{
		$isAdjusted = null;

		$nRepl = 0;
		$cont = preg_replace( '#@import ("|\')(.+?)\.css.*?("|\')#S', '@import url("${2}.css")', $cont, -1, $nRepl );
		if( $nRepl )
			$isAdjusted = true;

		if( !preg_match_all( '/url\s*\(\s*(?!["\']?data:)(?![\'|\"]?[\#|\%|])([^)]+)\s*\)([^;},\s]*)/iS', $cont, $matches ) )
			return( $isAdjusted );

		$repls = array();
		foreach( $matches[ 1 ] as $k => $url )
		{

			$url = stripcslashes( trim( $url, " \t\n\r\0\x0B\"'" ) );

			$urlAdjusted = false;
			$urlNew = $url;
			$r = self::_AdjustUrl( $urlNew, $urlAdjusted, array( 'jpeg', 'jpg', 'gif', 'png', 'webp', 'bmp' ), $urlDomainUrl, $urlPath, $ctxProcess, $settCache, $settImg, $settCdn );
			if( $r === false )
				return( false );

			if( $r )
				$isAdjusted = true;

			if( $urlAdjusted )
			{

				$whatever = $matches[ 2 ][ $k ];
				$hash = md5( $url . $whatever );
				$cont = str_replace( $matches[ 0 ][ $k ], $hash, $cont );
				$repls[ $hash ] = 'url(\'' . $urlNew . '\')' . $whatever;
			}
		}

		$cont = str_replace( array_keys( $repls ), array_values( $repls ), $cont );
		return( $isAdjusted );
	}

	function GetRenderFormatMin()
	{
		return( $this -> cssFmtMin );
	}

	function RenderData( $renderable )
	{
		return( self::_EscapeNonStdParts( trim( $renderable -> renderWhole( $this -> cssFmt ) ), false ) );
	}

	static function AppendSelectors( $aSel, $add )
	{
		$aSelBlock = array();
		foreach( ( array )$add as $addA )
		{
			foreach( $aSel as $sel )
			{
				if( preg_match( '@::(?:before|after)$@S', $sel, $m, PREG_OFFSET_CAPTURE ) )
					$sel = substr_replace( $sel, $addA, $m[ 0 ][ 1 ], 0 );
				else
					$sel .= $addA;

				$aSelBlock[] = $sel;
			}
		}

		return( $aSelBlock );
	}

	static function ImgLazyBgGetStyleBlocks( $ctxProcess, $settImg, $aSel, $adjustes, $important = false )
	{
		$lazyBg = $adjustes -> lazyBg;

		$iBgImgRuleComp = ($adjustes -> iBgImgRuleComp??0);

		$aRuleComp = ($adjustes -> aBgImgRuleComp??array( null ));
		$aRuleComp[ $iBgImgRuleComp ] = $lazyBg -> info ? new Sabberworm\CSS\Value\URL( new Sabberworm\CSS\Value\CSSString( LazyLoad_SrcSubst( $ctxProcess, $lazyBg -> info, Gen::GetArrField( $settImg, array( 'lazy', 'plchRast' ), true ) ) ) ) : 'none';

		if( count( $aRuleComp ) > 1 )
		{
			foreach( $aRuleComp as $iRuleComp => &$oRuleComp )
			{
				if( $iRuleComp == $iBgImgRuleComp )
					continue;

				if( is_object( $oRuleComp ) )
					$oRuleComp = clone $oRuleComp;

				$urlsImg = array(); self::_GetCssRuleValUrlObjs( $oRuleComp, $urlsImg );
				foreach( $urlsImg as $urlImg )
				{
					if( $imgInfo = $urlImg -> getRtProp( 'imgInfo' ) )
						$urlImg -> setURL( new Sabberworm\CSS\Value\CSSString( LazyLoad_SrcSubst( $ctxProcess, $imgInfo, false ) ) );
					else
						$urlImg -> setReplacer( 'none' );
				}
			}
			unset( $oRuleComp );
		}

		$ruleAdd = new Sabberworm\CSS\Rule\Rule( 'background-image' );
		$ruleAdd -> setValue( new Sabberworm\CSS\Value\RuleValueList( $aRuleComp ) );
		$ruleAdd -> setIsImportant( $important || $lazyBg -> isImportant );

		$block = new Sabberworm\CSS\RuleSet\DeclarationBlock();
		$block -> setSelectors( StyleProcessor::AppendSelectors( $aSel, array( '.lzl:not(.lzl-ed)', '.lzl-ing:not(.lzl-ed)' ) ) );
		$block -> addRule( $ruleAdd );

		return( array( $block ) );
	}

	static function ImgSzAlternativesGetStyleBlocks( $ctxProcess, $settImg, $aSel, $adjustes, $important = false )
	{
		$aBlock = array();
		$imgSzAlternatives = $adjustes -> imgSzAlternatives;

		if( $imgSzAlternatives -> isImportant )
			$important = true;

		foreach( $imgSzAlternatives -> a as $dim => $imgSzAlternative )
		{

			if( ulyjqbuhdyqcetbhkiy( $imgSzAlternative[ 'img' ] ) )
				$imgSzAlternative[ 'img' ] = '::bhkdyqcetujyi::' . $imgSzAlternative[ 'img' ];

			$aSelApply = StyleProcessor::AppendSelectors( $aSel, '[data-ai-bg*="-' . $dim . '-"]' );

			$block = new Sabberworm\CSS\RuleSet\DeclarationBlock();
			$block -> setSelectors( $aSelApply );

			$aBlock[] = $block;

			{
				$aRuleComp = ($adjustes -> aBgImgRuleComp??array( null ));
				$aRuleComp[ ($adjustes -> iBgImgRuleComp??0) ] = $imgSzAlternative[ 'img' ] !== null ? new Sabberworm\CSS\Value\URL( new Sabberworm\CSS\Value\CSSString( $imgSzAlternative[ 'img' ] ) ) : 'none';

				$ruleAdd = new Sabberworm\CSS\Rule\Rule( 'background-image' );
				$ruleAdd -> setValue( new Sabberworm\CSS\Value\RuleValueList( $aRuleComp ) );
				$ruleAdd -> setIsImportant( $important );
				$block -> addRule( $ruleAdd );
				unset( $ruleAdd );
			}

			if( $adjustes -> lazyBg && $imgSzAlternative[ 'img' ] !== null )
			{
				$ruleAdd = new Sabberworm\CSS\Rule\Rule( '--lzl-bg-img' );
				$ruleAdd -> setValue( new Sabberworm\CSS\Value\CSSString( $imgSzAlternative[ 'img' ] ) );
				$block -> addRule( $ruleAdd );
				unset( $ruleAdd );

			}
		}

		if( $adjustes -> lazyBg )
			Gen::ArrAdd( $aBlock, StyleProcessor::ImgLazyBgGetStyleBlocks( $ctxProcess, $settImg, $aSel, $adjustes, $important ) );

		return( $aBlock );
	}

	private function _AdjustContBlock( $stage, &$aCritFonts, $block, $selParent, &$blockParents, &$blockParentsCrit, &$blockParentsFonts, &$isCritDocAdjusted, &$isFontsDocAdjusted, $extract, &$ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined )
	{
		$isAdjusted = null;
		$canMoveTo = false;
		$aBlockAddAfter = array();

		if( $block instanceof Sabberworm\CSS\RuleSet\RuleSet )
		{
			$blockParents[] = $block;
			$blockParentsCrit[] = null;
			$blockParentsFonts[] = null;
		}

		if( $block instanceof Sabberworm\CSS\Property\Import )
		{
			if( !$stage || $stage == 'main' )
			{
				$r = self::_AdjustUrls( array( $block -> getLocation() ), false, $ctxProcess, $settCache, $settImg, $settCdn, $src, $isInlined );
				if( $r === false )
					return( false );
				if( $r )
					$isAdjusted = true;
				$canMoveTo = true;
			}
		}
		else if( $block instanceof Sabberworm\CSS\RuleSet\AtRuleSet && $block -> atRuleName() == 'font-face' )
		{
			if( !$stage || $stage == 'fonts' )
			{

				$aDepFonts = null;

				$r = self::AdjustRuleSet( $block, $aDepFonts, new AnyObj(), $this -> doc, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined );
				if( $r === false )
					return( false );

				if( $r )
					$isAdjusted = true;

				if( ($settCss[ 'fontCrit' ]??null) )
					$canMoveTo = true;
				else
				{
					if( ($settCss[ 'delayNonCritWithJs' ]??null) )
					{
						if( $aCritFonts !== null )
						{
							$isFontCrit = false;

							$names = array();
							foreach( $block -> getRules( 'font-family' ) as $rule )
								self::_GetCssRuleValFontNames( $rule -> getValue(), $names );

							foreach( $names as $name => $namesVal )
								if( ($aCritFonts[ $name ]??null) )
									$isFontCrit = true;

							if( $isFontCrit )
								$canMoveTo = 'fonts';
						}
						else
							$canMoveTo = 'fonts';
					}
				}
			}
		}
		else if( $block instanceof Sabberworm\CSS\RuleSet\RuleSet )
		{
			$selectors = null;

			if( !$stage || $stage == 'main' )
			{
				$isCrit = false;

				if( !$block -> isEmpty() )
				{
					$adjustes = new AnyObj();
					$adjustes -> lazyBg = null;
					$adjustes -> imgSzAlternatives = null;

					if( ($settImg[ 'szAdaptBg' ]??null) && !Images_CheckSzAdaptExcl( $ctxProcess, $this -> doc, $settImg, ( string )$block ) )
						$adjustes -> imgSzAlternatives = new ImgSzAlternatives();

					if( $selectors === null )
						$selectors = $this -> _GetBlockSels( $selParent, $block, $settCss );

					if( $this -> _AdjustBlock( $selectors, $block, $extract, $isCrit, $aCritFonts, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined, $adjustes ) )
						$isAdjusted = true;

					if( $block instanceof Sabberworm\CSS\RuleSet\DeclarationBlock )
					{
						if( $adjustes -> imgSzAlternatives && !$adjustes -> imgSzAlternatives -> isEmpty() )
							Gen::ArrAdd( $aBlockAddAfter, StyleProcessor::ImgSzAlternativesGetStyleBlocks( $ctxProcess, $settImg, $block -> getSelectors(), $adjustes, false ) );
						else if( $adjustes -> lazyBg )
							Gen::ArrAdd( $aBlockAddAfter, StyleProcessor::ImgLazyBgGetStyleBlocks( $ctxProcess, $settImg, $block -> getSelectors(), $adjustes ) );
					}

					unset( $adjustes );
				}

				if( $isCrit )
					$canMoveTo = true;
			}

			{
				if( !$stage || $stage == 'main' )
				{
					if( ($settCss[ 'min' ]??null) === true && $block instanceof Sabberworm\CSS\RuleSet\AtRuleSet )
						$block -> setAtRuleArgs( $this -> _SelectorMinify( $block -> atRuleArgs() ) );
				}

				if( $selectors === null )
					$selectors = $this -> _GetBlockSels( $selParent, $block, $settCss );

				$selFull = self::_getFullBlockSel( $selectors );

				foreach( $block -> getContents() as $i )
				{
					$r = $this -> _AdjustContBlock( $stage, $aCritFonts, $i, $selFull, $blockParents, $blockParentsCrit, $blockParentsFonts, $isCritDocAdjusted, $isFontsDocAdjusted, $extract, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined );
					if( $r === false )
						return( false );
					if( $r )
						$isAdjusted = true;
					if( ContentProcess_IsAborted( $ctxProcess, $settCache ) ) return( null );
				}

				unset( $selFull );
			}
		}
		else
		{
			if( !$stage || $stage == 'main' )
			{
				$canMoveTo = true;
			}
		}

		$isCrit = $extract && $canMoveTo;
		if( $isCrit )
		{
			if( $canMoveTo === 'fonts' )
			{
				$blockParentsMoveTo = &$blockParentsFonts;
				$isFontsDocAdjusted = true;
			}
			else
			{
				$blockParentsMoveTo = &$blockParentsCrit;
				$isCritDocAdjusted = true;
			}

			for( $iParent = 0; $iParent < count( $blockParentsMoveTo ); $iParent++ )
			{
				if( $blockParentsMoveTo[ $iParent ] )
					continue;

				$oParent = $blockParents[ $iParent ];
				$oParentClone = null;
				if( $oParent instanceof Sabberworm\CSS\RuleSet\DeclarationBlock )
				{
					$oParentClone = new Sabberworm\CSS\RuleSet\DeclarationBlock();
					$oParentClone -> setSelectors( $oParent -> getSelectors() );
				}
				else if( $oParent instanceof Sabberworm\CSS\RuleSet\AtRuleBlockList )
					$oParentClone = new Sabberworm\CSS\CSSList\AtRuleBlockList( $oParent -> atRuleName(), $oParent -> atRuleArgs() );
				else if( $oParent instanceof Sabberworm\CSS\RuleSet\AtRuleSet )
					$oParentClone = new Sabberworm\CSS\RuleSet\AtRuleSet( $oParent -> atRuleName(), $oParent -> atRuleArgs() );
				else
					$oParentClone = new Sabberworm\CSS\RuleSet\AtRuleSet( 'media all' );

				$blockParentsMoveTo[ $iParent ] = $oParentClone;
				$blockParentsMoveTo[ $iParent - 1 ] -> append( $oParentClone );
			}

			if( $block instanceof Sabberworm\CSS\RuleSet\RuleSet )
			{
				$blockParentsMoveTo[ count( $blockParentsMoveTo ) - 1 ] -> moveRulesFrom( $block );
				if( $aBlockAddAfter )
					$blockParentsMoveTo[ count( $blockParentsMoveTo ) - 2 ] -> append( $aBlockAddAfter );

				if( $block -> isEmpty() && !$block -> getContents() )
				{
					$blockParents[ count( $blockParents ) - 2 ] -> remove( $block );
					$isAdjusted = true;
				}
			}
			else
			{
				$blockParents[ count( $blockParents ) - 1 ] -> remove( $block );
				$blockParentsMoveTo[ count( $blockParentsMoveTo ) - 1 ] -> append( $block );
			}
		}
		else
		{
			if( $block instanceof Sabberworm\CSS\RuleSet\RuleSet )
				if( $aBlockAddAfter )
					$blockParents[ count( $blockParents ) - 2 ] -> insert( $aBlockAddAfter, $block );
		}

		if( $block instanceof Sabberworm\CSS\RuleSet\RuleSet )
		{
			if( ($settCss[ 'min' ]??null) === true && $block -> isEmpty() && !$block -> getContents() )
			{
				$blockParents[ count( $blockParents ) - 2 ] -> remove( $block );
				$isAdjusted = true;
			}

			array_pop( $blockParents );
			array_pop( $blockParentsCrit );
			array_pop( $blockParentsFonts );
		}

		return( $isAdjusted );
	}

	static private function _GetRenderFormat( $min = true )
	{
		if( $min )
		{
			$format = Sabberworm\CSS\OutputFormat::createCompact();
			$format -> setSemicolonAfterLastRule( false );
			$format -> setSpaceAfterRuleName( '' );
			$format -> setSpaceBeforeImportant( '' );
		}
		else
			$format = Sabberworm\CSS\OutputFormat::createPretty() -> set( 'Space*Rules', "\r\n" ) -> set( 'Space*Blocks', "\r\n" ) -> setSpaceBetweenBlocks( "\r\n\r\n" );

		return( $format );
	}

	private function _GetBlockSels( $selParent, $ruleSet, $settCss )
	{
		$selectors = null;
		if( $ruleSet instanceof Sabberworm\CSS\RuleSet\DeclarationBlock )
			$selectors = $ruleSet -> getSelectors();

		if( !$selectors )
			$selectors = $this -> aVoidSelector;

		foreach( $selectors as $i => $sel )
		{

			if( ($settCss[ 'min' ]??null) === true )
				$sel -> setSelector( $this -> _SelectorMinify( $sel -> getSelector() ) );

			$selectors[ $i ] = array( self::_getFullSel( $sel -> getSelector(), $selParent ), $sel );
		}

		return( $selectors );
	}

	private function _AdjustBlock( $selectors, $ruleSet, $extract, &$isCrit, &$aCritFonts, &$ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined, $adjustes )
	{
		if( !$extract )
			$isCrit = true;

		foreach( $selectors as $sel )
		{
			if( $isCrit )
				break;

			foreach( Gen::GetArrField( $settCss, array( 'nonCrit', 'autoExcls' ), array() ) as $excl )
			{
				if( @preg_match( $excl, $sel[ 1 ] -> getSelector() ) )
				{
					$isCrit = true;
					break;
				}
			}
		}

		$isAdjusted = self::AdjustRuleSet( $ruleSet, $aCritFonts, $adjustes, $this -> doc, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined );

		if( $adjustes -> lazyBg || ( $adjustes -> imgSzAlternatives && !$adjustes -> imgSzAlternatives -> isEmpty() ) )
			foreach( $selectors as $sel )
				if( $sel = $this -> cssSelToXPath( $sel[ 0 ] ) )
				{
					$scope = 0;
					if( $adjustes -> lazyBg )
						$scope |= 1;
					if( $adjustes -> imgSzAlternatives && !$adjustes -> imgSzAlternatives -> isEmpty() )
						$scope |= 2;

					self::_XpathSelCacheAddElem( $this -> _aXpathSelCache, $sel, $scope );
				}

		if( !$isCrit )
			foreach( $selectors as $sel )
			{
				$this -> curSelector = $sel[ 1 ];
				if( $this -> isCssSelCrit( $ctxProcess, $sel[ 0 ] ) )
				{
					$isCrit = true;
					break;
				}
			}

		return( $isAdjusted );
	}

	static private function _XpathSelCacheAddElem( &$a, $sel, $scope )
	{
		$a[ $sel ] = $scope | ($a[ $sel ]??0);
	}

	static private function _XpathSelCacheAdd( &$a, $aSel )
	{
		foreach( $aSel as $sel => $scope )
			self::_XpathSelCacheAddElem( $a, $sel, $scope );
	}

	function ApplyItems( &$ctxProcess, $settImg )
	{
		foreach( $this -> _aXpathSelCache as $xpathSel => $scope )
			if( $items = $this -> xpathEvaluate( $xpathSel ) )
				foreach( $items as $item )
				{
					if( ( int )$scope & 1 )
						StyleProcessor::AdjustItemLazyBg( $ctxProcess, $settImg, $this -> doc, $item, true );

					if( ( int )$scope & 2 )
						StyleProcessor::AdjustItemAdaptImg( $ctxProcess, $settImg, $this -> doc, $item );
				}
	}

	static function AdjustItemLazyBg( &$ctxProcess, $settImg, $doc, $item, $bFromStyle = false )
	{
		if( Images_CheckLazyExcl( $ctxProcess, $doc, $settImg, $item ) )
			return;

		$ctxProcess[ 'lazyload' ] = true;
		HtmlNd::AddRemoveAttrClass( $item, array( 'lzl' ) );

		if( $bFromStyle && !$item -> hasAttribute( 'data-lzl-bg' ) )
			$item -> setAttribute( 'data-lzl-bg', '' );
	}

	static function AdjustItemAdaptImg( &$ctxProcess, $settImg, $doc, $item, $itemCssClass = null )
	{
		if( Images_CheckSzAdaptExcl( $ctxProcess, $doc, $settImg, $item ) )
		    return;

		static $g_defSizes = null;

		if( $g_defSizes === null )
		{
			$g_defSizes = '';

			foreach( array( 				2160,	1920,	1366,	992,	768,	480,	360,	120	 ) as $dim )
				$g_defSizes .= '-' . $dim;

			$g_defSizes .= '-0-';
		}

		$ctxProcess[ 'imgAdaptive' ] = true;
		$item -> setAttribute( 'data-ai-bg', $g_defSizes );
		if( ($settImg[ 'szAdaptDpr' ]??null) )
			$item -> setAttribute( 'data-ai-dpr', 'y' );
		HtmlNd::AddRemoveAttrClass( $item, array( 'ai-bg', $itemCssClass ) );
	}

	static function AdjustRuleSet( $ruleSet, &$aDepFonts, $adjustes, $doc, &$ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined )
	{
		$isAdjusted = null;

		$urlDomainUrl = $isInlined ? null : Net::GetSiteAddrFromUrl( $src, true );
		$urlPath = $isInlined ? $ctxProcess[ 'requestUriPath' ] : Gen::GetFileDir( Net::Url2Uri( $src ) );

		$isLazy = Gen::GetArrField( $settImg, array( 'lazy', 'load' ), false );
		$bPriorityBgProcessed = false;

		$urls = array();
		$aRule = $ruleSet -> getRules();
		for( $iRule = count( $aRule ); $iRule > 0; $iRule-- )
		{
			$rule = $aRule[ $iRule - 1 ];

			if( ($settCss[ 'min' ]??null) === true )
				self::_RuleMinify( $rule );

			$bAddUrl = true;
			$ruleName = $rule -> getRule();
			$ruleVal = $rule -> getValue();
			if( $ruleName == 'background-image' || $ruleName == 'background' )
			{

				$bAddUrl = false;

				$aComp = array( $ruleVal );
				if( $ruleVal instanceof Sabberworm\CSS\Value\RuleValueList && $ruleVal -> getListSeparator() == ',' )
					$aComp = $ruleVal -> getListComponents();

				if( !$bPriorityBgProcessed )
				{
					$adjustes -> iBgImgRuleComp = 0;
					$adjustes -> aBgImgRuleComp = array();
				}

				$bFirstBgImgUrlProcessed = false;
				foreach( $aComp as $iComp => $ruleVal )
				{
					if( !$bPriorityBgProcessed )
					{
						$ruleValBgImg = $ruleName == 'background' ? self::_GetCssBgRuleValImg( $ruleVal ) : $ruleVal;
						$adjustes -> aBgImgRuleComp[] = $ruleValBgImg;
					}

					$urlsImg = array(); self::_GetCssRuleValUrlObjs( $ruleVal, $urlsImg );
					foreach( $urlsImg as $urlImg )
					{
						$bgImgSrcOrig = html_entity_decode( trim( $urlImg -> getURL() -> getString() ) );
						if( !$bgImgSrcOrig )
						{
							if( $ruleName == 'background-image' )
								$urlImg -> setReplacer( 'none' );
							continue;
						}

						$bProcessBgImg = !$bPriorityBgProcessed && !$bFirstBgImgUrlProcessed && $urlImg === $ruleValBgImg;

						$adjustedItem = false;

						$bgImgSrc = new ImgSrc( $ctxProcess, $bgImgSrcOrig );
						$bgImgSrc -> Init( $ctxProcess, $urlDomainUrl, $urlPath );
						if( $bgImgSrc -> src != $bgImgSrcOrig )
							$adjustedItem = true;

						if( $bProcessBgImg && isset( $adjustes -> imgSzAlternatives ) )
						{
							if( Gen::HrFail( Images_ProcessSrc_SizeAlternativesEx( $adjustes -> imgSzAlternatives, $ctxProcess, $bgImgSrc, $settCache, $settImg, $settCdn, true, $rule -> getIsImportant() ) ) )
								return( false );

							if( !$adjustes -> imgSzAlternatives -> isEmpty() )
							{
								$dataAi = array( 's' => array( $adjustes -> imgSzAlternatives -> info[ 'cx' ], $adjustes -> imgSzAlternatives -> info[ 'cy' ] ), 'd' => array() );
								foreach( $adjustes -> imgSzAlternatives -> a as $dim => $imgSzAlternative )
									if( $imgSzAlternative[ 'img' ] )
										$dataAi[ 'd' ][] = ( string )$dim;
								$ruleAdd = new Sabberworm\CSS\Rule\Rule( '--ai-bg-sz' );

								$ruleAdd -> setValue( new Sabberworm\CSS\Value\CSSString( json_encode( array( $iComp => array( 0 => $dataAi ) ) ) ) );
								$ruleSet -> addRule( $ruleAdd );
								unset( $ruleAdd, $dataAi, $dim );

								$isAdjusted = true;
							}
						}

						$r = Images_ProcessSrc( $ctxProcess, $bgImgSrc, $settCache, $settImg, $settCdn );
						if( $r === false )
							return( false );

						if( $r )
							$adjustedItem = true;

						if( $bProcessBgImg && $isLazy && !Ui::IsSrcAttrData( $bgImgSrc -> src ) )
						{
							if( isset( $adjustes -> item ) )
							{
								if( Images_ProcessItemLazyBg( $ctxProcess, $doc, $settImg, $adjustes -> item, $bgImgSrc ) )
								{
									$adjustedItem = true;
									$adjustes -> lazyBg = new AnyObj();
									$adjustes -> lazyBg -> info = $bgImgSrc -> GetInfo();
									$adjustes -> lazyBg -> isImportant = $rule -> getIsImportant();
								}
							}
							else
							{
								$adjustes -> lazyBg = new AnyObj();
								$adjustes -> lazyBg -> info = $bgImgSrc -> GetInfo();
								$adjustes -> lazyBg -> isImportant = $rule -> getIsImportant();

								$ruleAdd = new Sabberworm\CSS\Rule\Rule( '--lzl-bg-img' );
								$ruleAdd -> setValue( new Sabberworm\CSS\Value\CSSString( $bgImgSrc -> src ) );
								$ruleSet -> addRule( $ruleAdd );
								unset( $ruleAdd );

								$isAdjusted = true;
							}
						}

						if( ulyjqbuhdyqcetbhkiy( $bgImgSrc -> src ) )
						{
							$bgImgSrc -> src = '::bhkdyqcetujyi::' . $bgImgSrc -> src;
							$adjustedItem = true;
						}

						if( $adjustedItem )
						{
							$isAdjusted = true;
							$urlImg -> setURL( new Sabberworm\CSS\Value\CSSString( $bgImgSrc -> src ) );
						}

						if( !$bPriorityBgProcessed )
							$urlImg -> setRtProp( 'imgInfo', $bgImgSrc -> GetInfo() );

						$bgImgSrc -> dispose();
						unset( $bgImgSrc );

						if( $bProcessBgImg )
						{
							$adjustes -> iBgImgRuleComp = $iComp;
							$bFirstBgImgUrlProcessed = true;
						}
					}

					unset( $urlsImg );
				}

				$bPriorityBgProcessed = true;
			}
			else if( $ruleName == 'mask-image' || $ruleName == '-webkit-mask-image' )
			{

				$bAddUrl = false;

				$urlsImg = array(); self::_GetCssRuleValUrlObjs( $ruleVal, $urlsImg );
				foreach( $urlsImg as $urlImg )
				{
					$bgImgSrcOrig = html_entity_decode( trim( $urlImg -> getURL() -> getString() ) );
					if( !$bgImgSrcOrig )
						continue;

					$adjustedItem = false;

					$bgImgSrc = new ImgSrc( $ctxProcess, $bgImgSrcOrig );
					$bgImgSrc -> Init( $ctxProcess, $urlDomainUrl, $urlPath );
					if( $bgImgSrc -> src != $bgImgSrcOrig )
						$adjustedItem = true;

					$r = Images_ProcessSrc( $ctxProcess, $bgImgSrc, $settCache, $settImg, $settCdn );
					if( $r === false )
						return( false );

					if( $r )
						$adjustedItem = true;

					if( ulyjqbuhdyqcetbhkiy( $bgImgSrc -> src ) )
					{
						$bgImgSrc -> src = '::bhkdyqcetujyi::' . $bgImgSrc -> src;
						$adjustedItem = true;
					}

					if( $adjustedItem )
					{
						$isAdjusted = true;
						$urlImg -> setURL( new Sabberworm\CSS\Value\CSSString( $bgImgSrc -> src ) );
					}

					$bgImgSrc -> dispose();
					unset( $bgImgSrc );
				}

				unset( $urlsImg );
			}

			if( $aDepFonts !== null )
			{
				switch( $rule -> getRule() )
				{

				case 'font-family':
				case 'font':
					self::_GetCssRuleValFontNames( $ruleVal, $aDepFonts );
					break;
				}
			}

			if( $bAddUrl )
				self::_GetCssRuleValUrlObjs( $ruleVal, $urls );
		}

		if( $ruleSet instanceof Sabberworm\CSS\RuleSet\AtRuleSet && $ruleSet -> atRuleName() == 'font-face' )
		{

			$ctxFont = new AnyObj();
			$ctxFont -> ruleSet = $ruleSet;
			$ctxFont -> matchProp =
				function( $ctxFont, $name, $expr )
				{
					if( IsStrRegExp( $expr ) )
						return( @preg_match( $expr . 'S', $ctxFont -> getProp( $name ) ) );
					return( $expr == $ctxFont -> getProp( $name ) );
				};
			$ctxFont -> getProp =
				function( $ctxFont, $name )
				{
					$p = 'p_' . ( is_array( $name ) ? $name[ 1 ] : $name );
					if( !isset( $ctxFont -> { $p } ) )
					{
						if( $oVal = $ctxFont -> ruleSet -> getRule( is_array( $name ) ? ( $name[ 0 ] . $name[ 1 ] ) : ( 'font-' . $name ) ) )
						{
							$oVal = $oVal -> getValue();
							if( $oVal instanceof Sabberworm\CSS\Value\CSSString )
								$oVal = $oVal -> getString();
							$ctxFont -> { $p } = $oVal;
						}
						else
							$ctxFont -> { $p } = '';
					}

					return( $ctxFont -> { $p } );
				};
			$ctxFont -> cbMatch =
				function( $ctxFont, $expr )
				{
					if( Gen::StrStartsWith( $expr, 'weight:' ) )
					{
						$expr = substr( $expr, 7 );
						return( $ctxFont -> matchProp( 'weight', $expr ) );
					}

					if( Gen::StrStartsWith( $expr, 'style:' ) )
					{
						$expr = substr( $expr, 6 );
						return( $ctxFont -> matchProp( 'style', $expr ) );
					}

					if( Gen::StrStartsWith( $expr, 'range:' ) )
					{
						$expr = substr( $expr, 6 );
						return( $ctxFont -> matchProp( array( 'unicode-', 'range' ), $expr ) );
					}

					if( Gen::StrStartsWith( $expr, 'family:' ) )
						$expr = substr( $expr, 7 );

					return( $ctxFont -> matchProp( 'family', $expr ) );
				};

			$bFontInlined = false;
			if( Gen::GetArrField( $settCss, array( 'font', 'inl', 'enable' ), false ) && ( $aFontInl = Gen::GetArrField( $settCss, array( 'font', 'inl', 'items' ), array() ) ) )
			{
				foreach( $aFontInl as $fontInlExpr )
				{
					if( !ExprConditionsSet_Match( $fontInlExpr, array( $ctxFont, 'cbMatch' ) ) )
						continue;

					$aFmt = array();
					foreach( $ctxFont -> ruleSet -> getRules( 'src' ) as $oValSrc )
					{
						$oValSrc = $oValSrc -> getValue();

						$aSrc = array( $oValSrc );
						if( $oValSrc instanceof Sabberworm\CSS\Value\RuleValueList && $oValSrc -> getListSeparator() == ',' )
							$aSrc = $oValSrc -> getListComponents();

						foreach( $aSrc as $oSrc )
						{
							if( !( $fmt = self::_GetCssRuleValFunc( $oSrc, 'format' ) ) )
								continue;

							$fmt = $fmt -> getArguments();
							if( !$fmt )
								continue;

							$fmt = $fmt[ 0 ];
							if( !is_string( $fmt ) )
							{
								if( $fmt instanceof Sabberworm\CSS\Value\CSSString )
									$fmt = $fmt -> getString();
								else
									$fmt = null;
							}
							if( !$fmt )
								continue;

							$oUrl = array(); self::_GetCssRuleValUrlObjs( $oSrc, $oUrl );
							if( $oUrl )
								$oUrl = $oUrl[ 0 ];

							$aFmt[ $fmt ] = $oUrl;
						}

						unset( $aSrc );
					}

					if( $aFmt )
					{
						if( isset( $aFmt[ $fmt = 'woff2' ] ) )
							$url = $aFmt[ $fmt ];
						else if( isset( $aFmt[ $fmt = 'woff' ] ) )
							$url = $aFmt[ $fmt ];
						else
							$url = Gen::ArrGetByPos( $aFmt, 0, null, $fmt );

						$url = trim( $url -> getURL() -> getString() );

						if( !Ui::IsSrcAttrData( $url ) )
						{
							$contFont = null;
							$srcInfo = GetSrcAttrInfo( $ctxProcess, $urlDomainUrl, $urlPath, $url );
							if( isset( $srcInfo[ 'filePath' ] ) )
							{
								$contFont = Gen::FileGetContents( $srcInfo[ 'filePath' ] );
								if( $contFont === false && !Gen::DoesFileDirExist( $srcInfo[ 'filePath' ], $srcInfo[ 'filePathRoot' ] ) )
									$contFont = null;
							}

							if( $contFont === null )
								$contFont = GetExtContents( $ctxProcess, $srcInfo[ 'url' ] );

							if( is_string( $contFont ) )
								$url = Ui::SetSrcAttrData( $contFont, Fs::GetMimeContentType( '.' . $fmt ) );
							else
								$url = null;

							unset( $srcInfo, $contFont );
						}

						if( $url )
						{
							{
								$rule = new Sabberworm\CSS\Rule\Rule( 'src' );
								$rule -> setValue( new Sabberworm\CSS\Value\RuleValueList( array( new Sabberworm\CSS\Value\URL( new Sabberworm\CSS\Value\CSSString( $url ) ), new Sabberworm\CSS\Value\CSSFunction( 'format', array( new Sabberworm\CSS\Value\CSSString( $fmt ) ) ) ), ' ' ) );
								$ruleSet -> removeRule( $rule -> getRule() );
								$ruleSet -> addRule( $rule );
							}

							{
								$rule = new Sabberworm\CSS\Rule\Rule( 'font-display' );
								$rule -> setValue( 'block' );
								$ruleSet -> removeRule( $rule -> getRule() );
								$ruleSet -> addRule( $rule );
							}

							$bFontInlined = true;
							$isAdjusted = true;
						}
					}

					break;
				}
			}

			if( !$bFontInlined )
			{
				if( Gen::GetArrField( $settCss, array( 'font', 'deinlLrg' ), false ) )
				{
					foreach( $urls as $oUrl )
					{
						$url = trim( $oUrl -> getURL() -> getString() );
						if( !Ui::IsSrcAttrData( $url ) )
							continue;

						$data = Ui::GetSrcAttrData( $url, $type );
						if( !$data || strlen( $data ) < Gen::GetArrField( $settCss, array( 'font', 'deinlLrgSize' ), 0 ) )
							continue;

						$type = Fs::GetFileTypeFromMimeContentType( $type, 'bin' );
						if( !UpdSc( $ctxProcess, $settCache, array( 'font', $type ), $data, $url ) )
							return( false );

						if( ($srcInfo[ 'ext' ]??null) )
							$url = $ctxProcess[ 'siteDomainUrl' ] . $url;

						$oUrl -> setURL( new Sabberworm\CSS\Value\CSSString( $url ) );
					}
				}

				if( ($settCss[ 'fontOptLoad' ]??null) )
				{
					$fontNameExpr = Gen::GetArrField( $settCss, array( 'font', 'optLoadNameExpr' ), '' );

					if( !strlen( $fontNameExpr ) || ( IsStrRegExp( $fontNameExpr ) ? @preg_match( $fontNameExpr, $ctxFont -> getProp( 'family' ) ) : stripos( $ctxFont -> getProp( 'family' ), $fontNameExpr ) !== false ) )
					{
						$rule = new Sabberworm\CSS\Rule\Rule( 'font-display' );
						$rule -> setValue( ($settCss[ 'fontOptLoadDisp' ]??null) ? $settCss[ 'fontOptLoadDisp' ] : 'swap' );
						$ruleSet -> removeRule( $rule -> getRule() );
						$ruleSet -> addRule( $rule );

						$isAdjusted = true;
					}
				}
			}

			unset( $ctxFont );
		}

		$r = self::_AdjustUrls( $urls, $ruleSet instanceof Sabberworm\CSS\RuleSet\AtRuleSet && $ruleSet -> atRuleName() == 'font-face', $ctxProcess, $settCache, $settImg, $settCdn, $src, $isInlined );
		if( $r === false )
			return( false );
		if( $r )
			$isAdjusted = true;

		return( $isAdjusted );
	}

	static private function _AdjustUrl( &$url, &$urlAdjusted, array $aImgExt, $urlDomainUrl, $urlPath, &$ctxProcess, $settCache, $settImg, $settCdn )
	{

		if( !strlen( $url ) || Ui::IsSrcAttrData( $url ) || Gen::StrStartsWith( $url, '#' ) )
			return( null );

		$isAdjusted = null;
		$urlNew = $url;

		$srcInfo = GetSrcAttrInfo( $ctxProcess, $urlDomainUrl, $urlPath, $urlNew );
		if( $urlNew != $url )
			$urlAdjusted = true;

		$fileType = strtolower( Gen::GetFileExt( ($srcInfo[ 'srcWoArgs' ]??null) ) );

		if( in_array( $fileType, $aImgExt ) )
		{

			$imgSrc = new ImgSrc( $ctxProcess, $urlNew, $srcInfo );

			$r = Images_ProcessSrcEx( $ctxProcess, $imgSrc, $settCache, $settImg );
			if( $r === false )
				return( false );

			if( $r )
				$urlAdjusted = true;

			$urlNew = $imgSrc -> src;
			unset( $imgSrc );
		}

		if( Cdn_AdjustUrl( $ctxProcess, $settCdn, $urlNew, $fileType ) )
			$urlAdjusted = true;
		if( Fullness_AdjustUrl( $ctxProcess, $urlNew, ($srcInfo[ 'srcUrlFullness' ]??null) ) )
			$urlAdjusted = true;

		if( $urlAdjusted )
			$isAdjusted = true;

		if( ulyjqbuhdyqcetbhkiy( $urlNew ) )
		{
			$urlNew = '::bhkdyqcetujyi::' . $urlNew;
			$urlAdjusted = true;
		}

		if( $urlAdjusted )
			$url = $urlNew;

		return( $isAdjusted );
	}

	static function _AdjustUrls( $urls, $isFont, &$ctxProcess, $settCache, $settImg, $settCdn, $src, $isInlined )
	{

		$isAdjusted = null;

		$urlDomainUrl = $isInlined ? null : Net::GetSiteAddrFromUrl( $src, true );
		$urlPath = $isInlined ? $ctxProcess[ 'requestUriPath' ] : Gen::GetFileDir( Net::Url2Uri( $src ) );

		foreach( $urls as $oUrl )
		{
			$url = trim( $oUrl -> getURL() -> getString() );

			$urlAdjusted = false;
			$r = self::_AdjustUrl( $url, $urlAdjusted, $isFont ? array() : array( 'jpeg', 'jpg', 'gif', 'png', 'webp', 'bmp', 'svg' ), $urlDomainUrl, $urlPath, $ctxProcess, $settCache, $settImg, $settCdn );
			if( $r === false )
				return( false );

			if( $r )
				$isAdjusted = true;

			if( $urlAdjusted )
				$oUrl -> setURL( new Sabberworm\CSS\Value\CSSString( $url ) );
		}

		return( $isAdjusted );
	}

	function _trace( $e )
	{
		$eS = ( $e instanceof Sabberworm\CSS\Parsing\SrcExcptn ) ? $e : null;
		$sev = $eS ? $eS -> getSeverity() : Sabberworm\CSS\Settings::ParseErrHigh;

		$sevLocId = LocId::Pack( $sev == Sabberworm\CSS\Settings::ParseErrHigh ? 'CssParseTrace_ErrHigh' : ( $sev == Sabberworm\CSS\Settings::ParseErrMed ? 'CssParseTrace_ErrMed' : 'CssParseTrace_ErrLow' ) );
		if( $this -> curSelector )
			$locId = LocId::Pack( 'CssParseSelTrace_%1$s%2$s%3$s%4$s%5$s%6$s', null, array( $sevLocId, $this -> cssParserCurObjId, ( string )$this -> cssParser -> currentLineCharNo( $this -> curSelector -> getPos() ), $e -> getMessage(), str_replace( array( "\t", "\n", "\r", "\0", "\x0B", "\v" ), array( '\\t', '\\n', '\\r', '\\0', '\\x0B', '\\v' ), $this -> cssSelFs -> parser -> getText() ), ( string )$this -> cssSelFs -> parser -> currentLineCharNo( $eS ? $eS -> getPos() : 0 ) ) );
		else
			$locId = LocId::Pack( 'CssParseTrace_%1$s%2$s%3$s%4$s', null, array( $sevLocId, $this -> cssParserCurObjId, ( string )$this -> cssParser -> currentLineCharNo( $eS ? $eS -> getPos() : 0 ), $e -> getMessage() ) );

		if( $sev !== Sabberworm\CSS\Settings::ParseErrLow )
			LastWarnDscs_Add( $locId );

	}

	function IsTraceEnabled()
	{
		return( $this -> cssParser -> isTraceEnabled() );
	}

	function SetCurObjectId( $id )
	{
		$this -> cssParserCurObjId = $id;
	}

	function ParseRuleSet( $data )
	{
		$this -> curSelector = null;
		$this -> cssParser -> setText( $data );

		$ruleSet = new Sabberworm\CSS\RuleSet\RuleSet();

		try
		{
			Sabberworm\CSS\RuleSet\RuleSet::parseRuleSet( $this -> cssParser, $ruleSet );
		}
		catch( \Exception $e )
		{
			$this -> cssParser -> traceException( $e );
			$ruleSet = null;
		}

		return( $ruleSet );
	}

	static function GetFirstImportSimpleAttrs( $ctxProcess, $import, $src )
	{
		if( preg_match( '@\\ssupports\\s*\\(@S', $import ) )
			return( null );

		try
		{
			$cssParser = new Sabberworm\CSS\Parser( $import, Sabberworm\CSS\Settings::create() -> withMultibyteSupport( false ) );
			$cssDoc = $cssParser -> parse();
			unset( $cssParser );
		}
		catch( \Exception $e )
		{
			return( null );
		}

		foreach( $cssDoc -> getContents() as $block )
		{
			if( $block instanceof Sabberworm\CSS\Property\Import )
			{
				$args = $block -> atRuleArgs();

				$url = $args[ 0 ];
				if( $url instanceof Sabberworm\CSS\Value\URL )
					$url = $url -> getURL();
				if( $url instanceof Sabberworm\CSS\Value\CSSString )
					$url = $url -> getString();

				if( gettype( $url ) !== 'string' )
					return( null );

				{
					$urlDomainUrl = $src ? Net::GetSiteAddrFromUrl( $src, true ) : null;
					$urlPath = $src ? Gen::GetFileDir( Net::Url2Uri( $src ) ) : $ctxProcess[ 'requestUriPath' ];
					$srcInfo = GetSrcAttrInfo( $ctxProcess, $urlDomainUrl, $urlPath, $url );
					Fullness_AdjustUrl( $ctxProcess, $url, ($srcInfo[ 'srcUrlFullness' ]??null) );
				}

				$res = array( 'url' => $url );
				if( count( $args ) > 1 )
					$res[ 'media' ] = ( string )$args[ 1 ];

				return( $res );
			}
		}

		return( null );
	}

	static function cssSelToXPathEx( $cnvCssSel2Xpath, string $sel )
	{

		$pos = strpos( $sel, '::' );
		if( $pos !== false )
			$sel = substr( $sel, 0, $pos );

		if( preg_match( '@[^\\s:](:(?:before|after))$@', $sel, $m ) )
			$sel = substr( $sel, 0, -strlen( $m[ 1 ] ) );

		$xpathQ = null; try { $xpathQ = $cnvCssSel2Xpath -> cssToXPath( $sel, 'descendant-or-self::' ); } catch( \Exception $e ) {}
		return( $xpathQ );
	}

	static function createCnvCssSel2Xpath()
	{
		$cnvCssSel2Xpath = new Symfony\Component\CssSelector\XPath\Translator();
		$cnvCssSel2Xpath -> registerExtension( new Symfony\Component\CssSelector\XPath\Extension\HtmlExtension( $cnvCssSel2Xpath ) );
		$cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\EmptyStringParser() );
		$cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\ElementParser() );
		$cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\ClassParser() );
		$cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\HashParser() );
		return( $cnvCssSel2Xpath );
	}

	function cssSelToXPath( string $sel )
	{
		return( StyleProcessor::cssSelToXPathEx( $this -> cnvCssSel2Xpath, $sel ) );
	}

	function xpathEvaluate( $query )
	{
		return( $this -> xpath -> evaluate( $query, $this -> rootElem ) );
	}

	function isCssSelCrit( $ctxProcess, $sel )
	{

		if( isset( $this -> _aCssSelIsCritCache[ $sel ] ) )
		{

			return( $this -> _aCssSelIsCritCache[ $sel ] );
		}

		$selFiltered = trim( ContSkeleton_FltName( $this -> sklCssSelExcls, $sel, true ) );

		$selector = $this -> cssSelFs -> parseSelector( $selFiltered );

		if( !$selector )
			$isCrit = true;
		else
		{
			$selDeparsed = $selector -> renderWhole( $this -> cssFmtSimple );
			if( isset( $this -> _aCssSelIsCritRtCache[ $selDeparsed ] ) )
				$isCrit = $this -> _aCssSelIsCritRtCache[ $selDeparsed ];
			else
			{

				$mr = $selector -> match( $this -> cssSelFs, $this -> docFs );

				$isCrit = $mr === false || !!$mr;
				$this -> _aCssSelIsCritRtCache[ $selDeparsed ] = $isCrit;
			}
		}

		$this -> _aCssSelIsCritCache[ $sel ] = $isCrit;

		return( $isCrit );

		$items = false;

		$xpathQ = $this -> cssSelToXPath( $selFiltered );

		if( $xpathQ )
		{
			$xpathQ = '(' . $xpathQ . ')[1]';

			$items = $this -> xpathSkeleton ? $this -> xpathSkeleton -> evaluate( $xpathQ ) : $this -> xpathEvaluate( $xpathQ );

		}

		return( $this -> _aCssSelIsCritCache[ $sel ] = ( $items === false || HtmlNd::FirstOfChildren( $items ) ) );

	}

	static private function _getFullBlockSel( $selectors )
	{
		$aSel = array();
		foreach( $selectors as $sel )
			$aSel[] = $sel[ 0 ];

		return( count( $aSel ) > 1 ? ':is(' . implode( ',', $aSel ) . ')' : $aSel[ 0 ] );
	}

	static private function _getFullSel( $sel, $selParent = '' )
	{
		if( $sel == '&' )
			return( $selParent );

		$bComb = strpos( '>+~', substr( $sel, 0, 1 ) ) !== false;

		$sel = ' ' . $sel;
		if( $bComb || !@preg_match( '@[^\\\\]&@', $sel ) )
			$sel = ' &' . $sel;

		$sel = preg_replace( '@([^\\\\])&@', '${1}' . $selParent, ' ' . $sel );
		return( trim( $sel ) );
	}

	static function keepLrnNeededData( &$datasDel, &$lrnsGlobDel, $dsc, $dataPath )
	{
		if( $id = Gen::GetArrField( $dsc, array( 'css', 'c' ) ) )
		{
			unset( $lrnsGlobDel[ 'css/c/' . $id . '.dat.gz' ] );

			if( ($dsc[ 'v' ]??null) < 2 )
			{
				$data = Tof_GetFileData( $dataPath . '/css/c', 'dat.gz', array( 3, function( $data, $vFrom ) { return( $data ); } ), true, $id );
				$v = Gen::GetArrField( $data, array( 'v' ), 1 );

				foreach( Gen::GetArrField( $data, array( 'ac' ), array() ) as $contHash => $contParts )
				{
					if( is_array( $contParts ) )
					{
						foreach( $contParts as $partId => $oiCi )
							if( is_string( $oiCi ) && strlen( $oiCi ) )
								unset( $datasDel[ 'css' ][ $oiCi ] );
					}
					else if( is_string( $contParts ) && strlen( $contParts ) )
						unset( $datasDel[ 'css' ][ $contParts ] );
				}

				if( $v < 2 )
				{

					unset( $datasDel[ 'img' ] );
				}
				else
				{
					foreach( Gen::GetArrField( $data, array( 'd' ), array() ) as $type => $aoiCi )
						foreach( $aoiCi as $oiCi )
							unset( $datasDel[ $type ][ $oiCi ] );
				}
			}
		}

		if( ( ($dsc[ 'v' ]??null) < 2 ) && ( $id = Gen::GetArrField( $dsc, array( 'css', 'xslb' ) ) ) )
		{
			unset( $lrnsGlobDel[ 'css/xslb/' . $id . '.dat.gz' ] );
		}
	}

	function readLrnData( &$ctxProcess, $dsc, $dataPath, $bLearning )
	{
		if( ( !$bLearning || isset( $dsc[ 's' ] ) ) && ( $id = Gen::GetArrField( $dsc, array( 'css', 'c' ) ) ) )
		{
			$data = Tof_GetFileData( $dataPath . '/css/c', 'dat.gz', array( 3, function( $data, $vFrom ) { return( $data ); } ), true, $id );
			$v = Gen::GetArrField( $data, array( 'v' ), 1 );

			if( isset( $dsc[ 's' ] ) )
			{
				$this -> _aCssSelIsCritCache = Gen::GetArrField( $data, array( 'sc' ), array() );

			}

			if( !$bLearning )
			{
				if( $v == 3 )
				{
					$this -> _aAdjustContCache = Gen::GetArrField( $data, array( 'ac' ), array() );
				}
				else
				{
					$aXpathSelCacheOld = array();
					if( $id = Gen::GetArrField( $dsc, array( 'css', 'xslb' ) ) )
						$aXpathSelCacheOld = Gen::GetArrField( Tof_GetFileData( $dataPath . '/css/xslb', 'dat.gz', 1, true, $id ), array( 'd' ), array() );

					$aAdjustContCacheOld = Gen::GetArrField( $data, array( 'ac' ), array() );
					$this -> _aAdjustContCache = array();
					foreach( $aAdjustContCacheOld as $contHash => $res )
						$this -> _aAdjustContCache[ $contHash ] = array( 'c' => $res, 'd' => Gen::GetArrField( $data, array( 'd' ), array() ), 'xslb' => $aXpathSelCacheOld );
				}
			}
		}
	}

	function readLrnDataFinish( &$ctxProcess, $dsc, $dataPath )
	{
	}

	function writeLrnData( &$ctxProcess, &$dsc, $dataPath )
	{
		if( ( isset( $dsc[ 's' ] ) && $this -> _aCssSelIsCritCache ) || $this -> _aAdjustContCache )
		{
			$data = array();

			if( isset( $dsc[ 's' ] ) && $this -> _aCssSelIsCritCache )
			{
				$aCssSelCrit = array();
				foreach( $this -> _aCssSelIsCritCache as $sel => $v )
					if( $v )
						$aCssSelCrit[ $sel ] = true;
				$data[ 'sc' ] = $aCssSelCrit;
			}

			if( $this -> _aAdjustContCache )
				$data[ 'ac' ] = $this -> _aAdjustContCache;

			$dsc[ 'css' ][ 'c' ] = '';
			if( Gen::HrFail( @Tof_SetFileData( $dataPath . '/css/c', 'dat.gz', $data, 3, false, TOF_COMPR_MAX, $dsc[ 'css' ][ 'c' ] ) ) )
				return( false );
		}

		return( true );
	}

	private static function _GetCssRuleValFunc( $v, $name )
	{
		if( $v instanceof Sabberworm\CSS\Value\CSSFunction && $v -> getName() == $name )
			return( $v );

		if( $v instanceof Sabberworm\CSS\Value\RuleValueList )
			foreach( $v -> getListComponents() as $vComp )
				if( $o = self::_GetCssRuleValFunc( $vComp, $name ) )
					return( $o );

		return( null );
	}

	private static function _GetCssRuleValUrlObjs( $v, &$urls )
	{
		if( $v instanceof Sabberworm\CSS\Value\URL )
			$urls[] = $v;
		else if( $v instanceof Sabberworm\CSS\Value\ValueList )
			foreach( $v -> getListComponents() as $vComp )
				self::_GetCssRuleValUrlObjs( $vComp, $urls );
	}

	static function _GetCssBgRuleValImg( $v, $bSub = true )
	{

		if( $v instanceof Sabberworm\CSS\Value\URL )
			return( $v );

		if( $v instanceof Sabberworm\CSS\Value\CSSFunction )
		{

			https:
			if( Gen::StrEndsWith( $v -> getName(), '-gradient' ) )
				return( $v );

			if( in_array( $v -> getName(), array( 'element', 'image', '-webkit-image', 'image-set', '-webkit-image-set', 'cross-fade', 'paint' ) ) )
				return( $v );
		}

		if( !$bSub )
			return( null );

		if( $v instanceof Sabberworm\CSS\Value\RuleValueList )
			foreach( $v -> getListComponents() as $vComp )
				if( $vSub = self::_GetCssBgRuleValImg( $vComp, false ) )
					return( $vSub );

		return( 'none' );
	}

	private static function _GetCssRuleValFontNames( $v, &$names )
	{
		if( gettype( $v ) === 'string' )
		{
			if( !in_array( $v, array( 'normal', 'inherit', 'italic', 'oblique', 'small-caps', 'bold', 'bolder', 'lighter' ) ) )
				$names[ $v ] = true;
		}
		else if( $v instanceof Sabberworm\CSS\Value\CSSString )
			$names[ $v -> getString() ] = true;
		else if( $v instanceof Sabberworm\CSS\Value\RuleValueList )
			foreach( $v -> getListComponents() as $vComp )
				self::_GetCssRuleValFontNames( $vComp, $names );
	}

	private static function _DoesCSSRuleValContainFileURL( $v )
	{
		if( $v instanceof Sabberworm\CSS\Value\URL )
			return( !Ui::IsSrcAttrData( trim( $v -> getURL() -> getString() ) ) );

		if( !( $v instanceof Sabberworm\CSS\Value\RuleValueList ) )
			return( false );

		foreach( $v -> getListComponents() as $vComp )
			if( self::_DoesCSSRuleValContainFileURL( $vComp ) )
				return( true );

		return( false );
	}

	private static function _RuleMinify( $rule )
	{
		$aShorters = array(
			'font-weight'		=> array( 'normal' => 400, 'bold' => 700, ),
			'background'		=> array( 'transparent' => '0 0', 'none' => '0 0', 'black' => '#000', 'white' => '#fff', 'fuchsia' => '#f0f', 'magenta' => '#f0f', 'yellow' => '#ff0' ),

			'margin'			=> __CLASS__ . '::_RuleMinifySizes',
			'padding'			=> __CLASS__ . '::_RuleMinifySizes',
			'border-width'		=> __CLASS__ . '::_RuleMinifySizes',

			'left'				=> __CLASS__ . '::_RuleMinifySizes',
			'top'				=> __CLASS__ . '::_RuleMinifySizes',
			'right'				=> __CLASS__ . '::_RuleMinifySizes',
			'bottom'			=> __CLASS__ . '::_RuleMinifySizes',

			'margin-left'		=> __CLASS__ . '::_RuleMinifySizes',
			'margin-top'		=> __CLASS__ . '::_RuleMinifySizes',
			'margin-right'		=> __CLASS__ . '::_RuleMinifySizes',
			'margin-bottom'		=> __CLASS__ . '::_RuleMinifySizes',

			'padding-left'		=> __CLASS__ . '::_RuleMinifySizes',
			'padding-top'		=> __CLASS__ . '::_RuleMinifySizes',
			'padding-right'		=> __CLASS__ . '::_RuleMinifySizes',
			'padding-bottom'	=> __CLASS__ . '::_RuleMinifySizes',
		);

		$shorter = ($aShorters[ $rule -> getRule() ]??null);
		if( !$shorter )
			return;

		if( is_array( $shorter ) )
		{
			$val = $rule -> getValue();
			if( !is_object( $val ) )
			{
				$valShort = ($shorter[ $val ]??null);
				if( $valShort !== null )
					$rule -> setValue( $valShort );
			}
		}
		else
			@call_user_func( $shorter, $rule );
	}

	static function _SizeMin( $v )
	{
		if( $v instanceof Sabberworm\CSS\Value\Size && !$v -> getSize() )
			$v -> setUnit( null );
		return( $v );
	}

	static function _RuleMinifySizes( $rule )
	{

		$v = $rule -> getValue();
		if( $v instanceof Sabberworm\CSS\Value\RuleValueList )
		{
			$comps = $v -> getListComponents();
			foreach( $comps as &$vComp )
				$vComp = self::_SizeMin( $vComp );

			if( count( $comps ) == 4 && ( string )$comps[ 1 ] === ( string )$comps[ 3 ] )
				array_pop( $comps );
			if( count( $comps ) == 3 && ( string )$comps[ 0 ] === ( string )$comps[ 2 ] )
				array_pop( $comps );
			if( count( $comps ) == 2 && ( string )$comps[ 0 ] === ( string )$comps[ 1 ] )
				array_pop( $comps );

			$v -> setListComponents( $comps );
		}
		else
			$v = self::_SizeMin( $v );

		$rule -> setValue( $v );
	}

	private function _SelectorMinify( $sel )
	{
		$selWrongSuffix = '';

		{
			$posWrongSel = strpos( $sel, '{' );
			if( $posWrongSel !== false )
			{
				$selWrongSuffix = substr( $sel, $posWrongSel );
				$sel = substr( $sel, 0, $posWrongSel );
			}
		}

		if( $selNew = $this -> minifier -> run( $sel ) )
			$sel = $selNew;

		return( $sel . $selWrongSuffix );
	}
}

