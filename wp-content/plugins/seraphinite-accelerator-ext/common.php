<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

require_once( __DIR__ . '/Cmn/Gen.php' );
require_once( __DIR__ . '/Cmn/Ui.php' );
require_once( __DIR__ . '/Cmn/Fs.php' );
require_once( __DIR__ . '/Cmn/Db.php' );
require_once( __DIR__ . '/Cmn/Img.php' );
require_once( __DIR__ . '/Cmn/Plugin.php' );

spl_autoload_register(
	function( $class )
	{
		if( strpos( $class, 'seraph_accel\\MaxMind' ) === 0 )
			@include_once( __DIR__ . '/Cmn/Ext/PHP/MaxMind-DB-Reader-php/MaxMind/' . str_replace( '\\', '/', substr( $class, 21 ) ) . '.php' );
	}
);

const PLUGIN_SETT_VER								= 190;
const PLUGIN_DATA_VER								= 1;
const PLUGIN_EULA_VER								= 1;
const QUEUE_DB_VER									= 4;
const PLUGIN_STAT_VER								= 1;
const PLUGIN_EXTTOOLS_VER							= 1;

function OnOptRead_Sett( $sett, $verFrom )
{
	if( $verFrom == 5 )
	{
		$verFrom = 6;

		if( Gen::GetArrField( $sett, array( 'cache', 'updPostDeps' ), array() ) === array( '/' ) )
			unset( $sett[ 'cache' ][ 'updPostDeps' ] );
	}

	if( $verFrom == 7 )
	{
		$verFrom = 8;

		if( Gen::GetArrField( $sett, array( 'cache', 'lazyInvTmp' ), false ) )
			Gen::SetArrField( $sett, array( 'cache', 'lazyInvForcedTmp' ), true );
	}

	if( $verFrom && $verFrom < 9 )
		Gen::SetArrField( $sett, array( 'contPr', 'css', 'groupFont' ), Gen::GetArrField( $sett, array( 'contPr', 'css', 'groupNonCrit' ), false ) );

	if( $verFrom && $verFrom < 10 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'normUrl' ), true );
		Gen::SetArrField( $sett, array( 'contPr', 'normUrlMode' ), 2 );
	}

	if( $verFrom && $verFrom < 11 )
		Gen::SetArrField( $sett, array( 'contPr', 'lazy', 'bjs' ), false );

	if( $verFrom && $verFrom < 12 )
		Gen::SetArrField( $sett, array( 'contPr', 'css', 'sepImp' ), false );

	if( $verFrom && $verFrom < 17 )
		Gen::SetArrField( $sett, array( 'cache', 'updPostOp' ), 0 );

	if( $verFrom && $verFrom < 20 )
		Gen::SetArrField( $sett, array( 'contPr', 'frm', 'lazy', 'elmntrBg' ), false );

	if( $verFrom && $verFrom < 21 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'updPostDeps' ), Op_DepItems_MigrateFromOld( Gen::GetArrField( $sett, array( 'cache', 'updPostDeps' ), array() ) ) );
		Gen::SetArrField( $sett, array( 'cache', 'updAllDeps' ), Op_DepItems_MigrateFromOld( Gen::GetArrField( $sett, array( 'cache', 'updAllDeps' ), array() ) ) );
	}

	if( $verFrom && $verFrom < 22 )
		Gen::SetArrField( $sett, array( 'contPr', 'frm', 'lazy', 'youTubeFeed' ), false );

	if( $verFrom && $verFrom < 24 )
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'szAdaptBg' ), false );

	if( $verFrom && $verFrom < 26 )
		Gen::SetArrField( $sett, array( 'cache', 'chunks', 'js' ), false );

	if( $verFrom && $verFrom < 27 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'webp', 'redir' ), Gen::GetArrField( $sett, array( 'contPr', 'img', 'redirWebp' ) ) );
		unset( $sett[ 'contPr' ][ 'img' ][ 'redirWebp' ] );

		Gen::SetArrField( $sett, array( 'contPr', 'img', 'webp', 'enable' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'avif', 'enable' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'avif', 'redir' ), false );
	}

	if( $verFrom && $verFrom < 28 )
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'lazy', 'del3rd' ), false );

	if( $verFrom && $verFrom < 29 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'sldBdt' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrBgSldshw' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'css', 'corrErr' ), true );
	}

	if( $verFrom && $verFrom < 30 )
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'prtThSkel' ), false );

	if( $verFrom && $verFrom < 31 )
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'lazy', 'smoothAppear' ), true );

	if( $verFrom && $verFrom < 32 )
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'fltsmThBgFill' ), false );

	if( $verFrom && $verFrom < 33 )
		Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items' ), array() );

	if( $verFrom && $verFrom < 34 )
	{
		$grps = Gen::GetArrField( $sett, array( 'contPr', 'grps', 'items' ), array() );
		$grps[ '@' ] = Gen::GetArrField( OnOptGetDef_Sett(), array( 'contPr', 'grps', 'items', '@' ), array() );
		Gen::SetArrField( $grps, array( '@', 'enable' ), 0 );

		Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items' ), $grps );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ukSldshw' ), false );
	}

	if( $verFrom && $verFrom < 35 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'sldN2Ss' ), false );

		if( $verFrom >= 34 )
		{
			$sklExclDef = array(
				'matchAll(.//*[@class]/@class, "@(?:post|page|postid|pageid|-id|image|term|item|avatar|user|link_wishlist)[\\-_]([\\da-f]+)@i")',
				'matchAll(.//*[@id]/@id, "@[\\-_]([\\da-f]+)@i")',
			);

			foreach( Gen::GetArrField( $sett, array( 'contPr', 'grps', 'items' ), array() ) as $grpId => $grp )
			{
				Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', $grpId, 'sklExcl', '+' ), $sklExclDef[ 0 ] );
				Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', $grpId, 'sklExcl', '+' ), $sklExclDef[ 1 ] );
			}
		}
	}

	if( $verFrom && $verFrom < 36 )
		Gen::SetArrField( $sett, array( 'cache', 'srvClr' ), false );

	if( $verFrom && $verFrom < 37 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'swBdt' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'tdThumbCss' ), false );
	}

	if( $verFrom && $verFrom < 38 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmsKitImgCmp' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'haCrsl' ), false );
	}

	if( $verFrom && $verFrom < 39 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrTabs' ), false );
	}

	if( $verFrom && $verFrom < 40 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'phtncThmb' ), true );
	}

	if( $verFrom && $verFrom < 42 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'normalize' ), Gen::GetArrField( $sett, array( 'contPr', 'normalize' ), 0 ) | 512 );
	}

	if( $verFrom && $verFrom < 43 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrVids' ), false );
	}

	if( $verFrom && $verFrom < 44 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'procIntervalShort' ), Gen::GetArrField( $sett, array( 'cache', 'procInterval' ), 0 ) );
	}

	if( $verFrom && $verFrom < 45 )
	{
		$sklExclPrevDef = array(
			'matchAll(.//*[@class]/@class, "@(?:post|page|postid|pageid|-id|image|term|item|avatar|user|link_wishlist)[\\-_]([\\da-f]+)@i")',
			'matchAll(.//*[@id]/@id, "@[\\-_]([\\da-f]+)@i")',
			'.//svg[@width="0"][@height="0"]',
			'.//body[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")][contains(concat(" ",normalize-space(@class)," ")," single-product ")]//*[match(concat(" ",normalize-space(@class)," "),"@[\\s\\-]products\\s@")]/*',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")]//*[contains(concat(" ",normalize-space(@class)," ")," product ")]/@class, "@\\sproduct_cat-[^\\s]+@", "@\\sproduct_tag-[^\\s]+@", "@\\spa_[^\\s]+@")',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-attributes-item ")]/@class, "@\\swoocommerce-product-attributes-item--attribute_[^\\s]+@")',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")]//*[contains(concat(" ",normalize-space(@class)," ")," comment-author-")]/@class, "@\\scomment-author-[^\\s]+@")',
		);

		$sklExclPrev2Def = array(
			'.//svg[@width="0"][@height="0"]',
			'.//body[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")][contains(concat(" ",normalize-space(@class)," ")," single-product ")]//*[contains(concat(" ",normalize-space(@class)," ")," products ")]/*',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")]//*[contains(concat(" ",normalize-space(@class)," ")," product ")]/@class, "@\\sproduct_cat-[^\\s]+@", "@\\sproduct_tag-[^\\s]+@", "@\\spa_[^\\s]+@")',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-attributes-item ")]/@class, "@\\swoocommerce-product-attributes-item--attribute_[^\\s]+@")',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")]//*[contains(concat(" ",normalize-space(@class)," ")," comment-author-")]/@class, "@\\scomment-author-[^\\s]+@")',
			'matchAll(.//*[@class]/@class, "@(?:post|page|postid|pageid|-id|image|term|item|avatar|user|link_wishlist)[\\-_]([\\da-f]+)@i")',
			'matchAll(.//*[@id]/@id, "@[\\-_]([\\da-f]+)@i")',
		);

		$sklExclPrev3Def = array(
			'.//svg[@width="0"][@height="0"]',
			'.//body[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")][contains(concat(" ",normalize-space(@class)," ")," single-product ")]//*[contains(concat(" ",normalize-space(@class)," ")," products ")]/*',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")]//*[contains(concat(" ",normalize-space(@class)," ")," product ")]/@class, "@\\sproduct_cat-[^\\s]+@", "@\\sproduct_tag-[^\\s]+@", "@\\spa_[^\\s]+@")',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-attributes-item ")]/@class, "@\\swoocommerce-product-attributes-item--attribute_[^\\s]+@")',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")]//*[contains(concat(" ",normalize-space(@class)," ")," comment-author-")]/@class, "@\\scomment-author-[^\\s]+@")',
			'matchAll(.//*[@class]/@class, "@\\s(?:post|postid|menu-item|avatar|user|elementor-repeater-item)[\\-_]([\\da-f]+)@i")',
			'matchAll(.//*[@id]/@id, "@[\\-_]([\\da-f]+)(?:[\\s\\-_]|$)@i")',
		);

		$sklExclPrev4Def = array(
			'.//body[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")][contains(concat(" ",normalize-space(@class)," ")," single-product ")]//*[contains(concat(" ",normalize-space(@class)," ")," products ")]/*',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")]//*[contains(concat(" ",normalize-space(@class)," ")," product ")]/@class, "@\\sproduct_cat-[^\\s]+@", "@\\sproduct_tag-[^\\s]+@", "@\\spa_[^\\s]+@")',
			'.//svg[@width="0"][@height="0"]',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")]//*[contains(concat(" ",normalize-space(@class)," ")," comment-author-")]/@class, "@\\scomment-author-[^\\s]+@")',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-attributes-item ")]/@class, "@\\swoocommerce-product-attributes-item--attribute_[^\\s]+@")',
			'matchAll(.//*[@id]/@id, "@[\\-_]([\\da-f]+)(?:[\\s\\-_]|$)@i")',
			'matchAll(.//*[@class]/@class, "@(?:post|page|postid|pageid|-id|image|term|item|avatar|user|link_wishlist)[\\-_]([\\da-f]+)@i")',
		);

		$sklExclDef = array(
			'.//br', './/script', './/style', './/link', './/head',
			'.//svg[@width="0"][@height="0"]',
		);

		$sklCssSelExclDef = array(
			'@[\\.#][\\w\\-]*[\\-_]([\\da-f]+)[\\W_]@i',
			'@\\.(?:product_cat|product_tag|pa|woocommerce-product-attributes-item--attribute|comment-author)[\\-_]([\\w\\-]+)@i',
		);

		foreach( Gen::GetArrField( $sett, array( 'contPr', 'grps', 'items' ), array() ) as $grpId => $grp )
		{
			$sklExclCur = Gen::GetArrField( $grp, array( 'sklExcl' ), array() );
			if( Gen::ArrEqual( $sklExclCur, $sklExclPrevDef ) || Gen::ArrEqual( $sklExclCur, $sklExclPrev2Def ) || Gen::ArrEqual( $sklExclCur, $sklExclPrev3Def ) || Gen::ArrEqual( $sklExclCur, $sklExclPrev4Def ) )
			{
				Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', $grpId, 'sklSrch' ), true );
				Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', $grpId, 'sklCssSelExcl' ), $sklCssSelExclDef );
				Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', $grpId, 'sklExcl' ), $sklExclDef );
			}
			else
			{
				Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', $grpId, 'sklSrch' ), false );
				Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', $grpId, 'sklCssSelExcl' ), array() );
				foreach( array( './/br', './/script', './/style', './/link', './/head' ) as $sklExclItem )
					Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', $grpId, 'sklExcl', '+' ), $sklExclItem );
			}
		}
	}

	if( $verFrom && $verFrom < 46 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'jetMobMenu' ), false );
	}

	if( $verFrom && $verFrom < 48 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrNavMenu' ), false );
	}

	if( $verFrom && $verFrom < 49 )
		Gen::SetArrField( $sett, array( 'cache', 'srv' ), false );

	if( $verFrom && $verFrom < 51 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviMvImg' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviMvText' ), false );
	}

	if( $verFrom && $verFrom < 52 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrAdvTabs' ), false );
	}

	if( $verFrom && $verFrom < 54 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'useTimeoutClnForWpNonce' ), false );
	}

	if( $verFrom && $verFrom < 55 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'sldRev' ), false );
	}

	if( $verFrom && $verFrom < 57 )
	{
		$v = Gen::GetArrField( $sett, array( 'contPr', 'js', 'clickDelay' ) );
		if( $v !== null )
		{
			Gen::SetArrField( $sett, array( 'contPr', 'js', 'clk', 'delay' ), $v );
			unset( $sett[ 'contPr' ][ 'js' ][ 'clickDelay' ] );
		}

		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtGal' ), false );
	}

	if( $verFrom && $verFrom < 58 )
	{
		foreach( Gen::GetArrField( $sett, array( 'cache', 'updPostDeps' ), array() ) as $i => $dep )
		{
			if( Gen::StrStartsWith( $dep, '@pageNums' ) || Gen::StrStartsWith( $dep, '@commentPageNums' ) )
			{
				$dep = '@post@{ID}:' . $dep;
				Gen::SetArrField( $sett, array( 'cache', 'updPostDeps', $i ), $dep );
			}
		}
	}

	if( $verFrom && $verFrom < 59 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviVidBox' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'js', 'clk', 'exclDef', '+' ), './/a[contains(concat(" ",normalize-space(@class)," ")," et_pb_video_play ")]' );
	}

	if( $verFrom && $verFrom < 60 )
	{
		$aExcl = Gen::GetArrField( $sett, array( 'contPr', 'js', 'clk', 'exclDef' ), array() );
		if( !in_array( './/*[starts-with(@href,"#elementor-action")]', $aExcl ) )
			Gen::SetArrField( $sett, array( 'contPr', 'js', 'clk', 'exclDef', '+' ), './/*[starts-with(@href,"#elementor-action")]' );
		if( !in_array( './/a[@e-action-hash]', $aExcl ) )
			Gen::SetArrField( $sett, array( 'contPr', 'js', 'clk', 'exclDef', '+' ), './/a[@e-action-hash]' );
	}

	if( $verFrom && $verFrom < 62 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'scrlSeq' ), false );
	}

	if( $verFrom && $verFrom < 63 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'fusionBgVid' ), false );
	}

	if( $verFrom && $verFrom < 64 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'js', 'groupExcls' ), array() );
	}

	if( $verFrom && $verFrom < 65 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'normAgent' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'js', 'groupExclMdls' ), false );
	}

	if( $verFrom && $verFrom < 66 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'procWorkInt' ), 0.0 );
		Gen::SetArrField( $sett, array( 'cache', 'procPauseInt' ), 0.0 );
	}

	if( $verFrom && $verFrom < 67 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cln', 'cmtsExcl' ), Gen::GetArrField( $sett, array( 'contPr', 'minCmtsExcl' ), array() ) );
		Gen::SetArrField( $sett, array( 'contPr', 'cln', 'cmts' ), Gen::GetArrField( $sett, array( 'contPr', 'min' ), false ) );
		unset( $sett[ 'contPr' ][ 'minCmtsExcl' ] );
	}

	if( $verFrom && $verFrom < 68 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'mkImgSrcSet' ), false );
	}

	if( $verFrom && $verFrom < 69 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'woodmartPrcFlt' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'txpTagGrps' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviMvSld' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviMvFwHdr' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviDsmGal' ), false );
	}

	if( $verFrom && $verFrom < 70 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'eaelSmpMnu' ), false );
	}

	if( $verFrom && $verFrom < 74 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'suTabs' ), false );
	}

	if( $verFrom && $verFrom < 75 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'upbBgImg' ), false );
	}

	if( $verFrom && $verFrom < 76 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrAni' ), false );
	}

	if( $verFrom && $verFrom < 77 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'the7MblHdr' ), false );
	}

	if( $verFrom && $verFrom < 79 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrAccrdn' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wbwPrdFlt' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wpStrs' ), false );
	}

	if( $verFrom && $verFrom < 81 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ukBgImg' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ukAni' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'tmHdr' ), false );
	}

	if( $verFrom && $verFrom < 82 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'qodefApprAni' ), false );
	}

	if( $verFrom && $verFrom < 83 )
	{
		{
			$autoExcls = Gen::GetArrField( $sett, array( 'contPr', 'css', 'nonCrit', 'autoExcls' ), array() );
			foreach( array( '@\\.animated@', '@\\.qodef-qi--appeared@', '@\\.uk-animation-@', '@\\.show-mobile-header@' ) as $autoExclsExpr )
				if( !in_array( $autoExclsExpr, $autoExcls ) )
					$autoExcls[] = $autoExclsExpr;
			Gen::SetArrField( $sett, array( 'contPr', 'css', 'nonCrit', 'autoExcls' ), $autoExcls );
		}

		{
			$contCss = Gen::GetArrField( $sett, array( 'contPr', 'css', 'custom', 'elementor-vis', 'data' ) );
			if( is_string( $contCss ) )
			{
				$contCss = str_replace( 'body.seraph-accel-js-lzl-ing-ani .elementor-invisible', 'body.seraph-accel-js-lzl-ing-ani .elementor-invisible:not([data-lzl-an])', $contCss );
				Gen::SetArrField( $sett, array( 'contPr', 'css', 'custom', 'elementor-vis', 'data' ), $contCss );
			}
		}
	}

	if( $verFrom && $verFrom < 84 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'fsnEqHghtCols' ), false );
	}

	if( $verFrom && $verFrom < 86 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtImgCrsl' ), false );
	}

	if( $verFrom && $verFrom < 87 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ukGrid' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ukModal' ), false );
		Gen::SetArrField( $sett, array( 'cache', 'chkNotMdfSince' ), false );
	}

	if( $verFrom && $verFrom < 88 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ukHghtVwp' ), Gen::GetArrField( $sett, array( 'contPr', 'cp', 'tmHdr' ), false ) );

		{
			$autoExcls = Gen::GetArrField( $sett, array( 'contPr', 'css', 'nonCrit', 'autoExcls' ), array() );
			foreach( array( '@\\.uk-modal@', '@\\.uk-first-column@', '@\\.uk-grid-margin@', '@\\.uk-grid-stack@', ) as $autoExclsExpr )
				if( !in_array( $autoExclsExpr, $autoExcls ) )
					$autoExcls[] = $autoExclsExpr;
			Gen::SetArrField( $sett, array( 'contPr', 'css', 'nonCrit', 'autoExcls' ), $autoExcls );
		}

		{
			$contCss = Gen::GetArrField( $sett, array( 'contPr', 'css', 'custom', 'tm', 'data' ) );
			if( is_string( $contCss ) )
			{
				$contCss = str_replace( array( ".uk-flex[uk-height-viewport*=\"offset-top: true\"],\r\n.uk-flex[uk-height-viewport*=\"offset-top:true\"] {\r\n\tmin-height: calc(100vh - 1px*var(--tm-header-placeholder-cy));\r\n}\r\n\r\n", "--tm-header-placeholder-cy" ), array( "", "--uk-header-placeholder-cy" ), $contCss );
				Gen::SetArrField( $sett, array( 'contPr', 'css', 'custom', 'tm', 'data' ), $contCss );
			}
		}

		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ukNavBar' ), false );
	}

	if( $verFrom && $verFrom < 89 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviVidBg' ), false );
	}

	if( $verFrom && $verFrom < 91 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'merimagBgImg' ), false );
	}

	if( $verFrom && $verFrom < 92 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrSpltAni' ), false );
	}

	if( $verFrom && $verFrom < 93 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'fsnAni' ), false );
	}

	if( $verFrom && $verFrom < 94 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'astrRsp' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'thrvAni' ), false );
		Gen::SetArrField( $sett, array( 'cache', 'forceAdvCache' ), false );
	}

	if( $verFrom && $verFrom < 95 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'mdcrLdng' ), false );
		Gen::SetArrField( $sett, array( 'asyncUseRe' ), false );
	}

	if( $verFrom && $verFrom < 96 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'prmmprssLzStls' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviLzStls' ), false );
	}

	if( $verFrom && $verFrom < 97 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrPremNavMenu' ), false );
	}

	if( $verFrom && $verFrom < 98 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'deinlLrg' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'vidJs' ), false );
	}

	if( $verFrom && $verFrom < 99 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'upbAni' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'the7Ani' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ultRspnsv' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ultVcHd' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'mnmgImg' ), true );
	}

	if( $verFrom && $verFrom < 100 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'tldBgImg' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'jqVide' ), false );
	}

	if( $verFrom && $verFrom < 101 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviVidFr' ), false );
	}

	if( $verFrom && $verFrom < 102 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wprAniTxt' ), false );
	}

	if( $verFrom && $verFrom < 104 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'fltsmThAni' ), false );
	}

	if( $verFrom && $verFrom < 105 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtAvoShcs' ), false );
	}

	if( $verFrom && $verFrom < 106 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviAni' ), false );
	}

	if( $verFrom && $verFrom < 107 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrHdr' ), false );
	}

	if( $verFrom && $verFrom < 108 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ntBlueThRspnsv' ), false );
	}

	if( $verFrom && $verFrom < 109 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'phloxThRspnsv' ), false );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'phloxThAni' ), false );
	}

	if( $verFrom && $verFrom < 110 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrPremScrl' ), false );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviHdr' ), false );
	}

	if( $verFrom && $verFrom < 111 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'brcksAni' ), false );
	}

	if( $verFrom && $verFrom < 112 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrTrxAni' ), false );
	}

	if( $verFrom && $verFrom < 113 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wprTabs' ), false );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wooSctrCntDwnTmr' ), false );
	}

	if( $verFrom && $verFrom < 115 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'lazyInvInitTmp' ), true );
	}

	if( $verFrom && $verFrom < 116 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'ctxSkip' ), false );
	}

	if( $verFrom && $verFrom < 116 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'ctxSkip' ), false );
	}

	if( $verFrom && $verFrom < 117 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrPremTabs' ), false );
	}

	if( $verFrom && $verFrom < 118 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wooJs' ), false );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'sbThAni' ), false );
	}

	if( $verFrom && $verFrom < 119 )
	{
		Gen::SetArrField( $sett, array( 'asyncMode' ), Gen::GetArrField( $sett, array( 'asyncUseRe' ), false ) ? 're' : '' );
		unset( $sett[ 'asyncUseRe' ] );
	}

	if( $verFrom && $verFrom < 120 )
	{
		{
			$aCacheExt = Gen::GetArrField( $sett, array( 'contPr', 'img', 'cacheExt' ), array() );
			foreach( $aCacheExt as &$eCacheExt )
				if( Gen::StrPosArr( $eCacheExt, array( 'cdninstagram', 'googleusercontent', ) ) !== false )
					$eCacheExt = 'crit:' . $eCacheExt;
			unset( $eCacheExt );
			Gen::SetArrField( $sett, array( 'contPr', 'img', 'cacheExt' ), $aCacheExt );
		}

		Gen::SetArrField( $sett, array( 'contPr', 'js', 'groupCritSpec' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'js', 'critSpec' ), array( 'timeout' => array( 'enable' => true, 'v' => 0, ), 'items' => array(), ) );

		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'lottGen' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmsKitLott' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrShe' ), false );
	}

	if( $verFrom && $verFrom < 121 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'kdncThAni' ), false );
	}

	if( $verFrom && $verFrom < 122 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'jetLott' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviPrld' ), false );
	}

	if( $verFrom && $verFrom < 123 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviStck' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrStck' ), Gen::GetArrField( $sett, array( 'contPr', 'cp', 'elmntrHdr' ), false ) );
		Gen::UnsetArrField( $sett, array( 'contPr', 'cp', 'elmntrHdr' ) );

		Gen::SetArrField( $sett, array( 'contPr', 'js', 'scrlDelay' ), 0 );
	}

	if( $verFrom && $verFrom < 124 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'mdknThRspnsv' ), false );
	}

	if( $verFrom && $verFrom < 125 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'szAdaptImg' ), false );
	}

	if( $verFrom && $verFrom < 126 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'jetCrsl' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'jetCrslPst' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrPremCrsl' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'sprflMenu' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'sldRev_SmthLd' ), false );
	}

	if( $verFrom && $verFrom < 127 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'jqJpPlr' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'prstPlr' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ultAni' ), false );
	}

	if( $verFrom && $verFrom < 128 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'css', 'bfrJs' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'excl' ), array() );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtLott' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtWooPrdImgs' ), false );

		{
			$contCss = Gen::GetArrField( $sett, array( 'contPr', 'css', 'custom', 'rev-slider', 'data' ) );
			if( is_string( $contCss ) )
			{
				$contCss = preg_replace( '@height:\\s*100vh\\s*!important@', 'height: calc(100vh - var(--lzl-rs-offs-y)) !important', $contCss );
				$contCss = preg_replace( '@margin-top:\\s*-100vh\\s*!important@', 'margin-top: calc(-100vh + var(--lzl-rs-offs-y)) !important', $contCss );
				Gen::SetArrField( $sett, array( 'contPr', 'css', 'custom', 'rev-slider', 'data' ), $contCss );
			}
		}

		{
			$contCss = Gen::GetArrField( $sett, array( 'contPr', 'css', 'custom', 'n2-ss-slider', 'data' ) );
			if( is_string( $contCss ) )
			{
				$contCss = preg_replace( '@\\.n2-ss-slider:not\\(\\.n2-ss-loaded\\):not\\(\\[data-ss-carousel\\]\\)\\s+\\[data-slide-public-id="1"\\]\\s+\\.n2-ss-layers-container,\\s*\\.n2-ss-slider:not\\(\\.n2-ss-loaded\\)\\s+\\.n2-ss-slider-controls-advanced\\s*{\\s*opacity:@', ".n2-ss-slider:not(.n2-ss-loaded):not([data-ss-carousel]) [data-slide-public-id=\"1\"] .n2-ss-layers-container,\n.n2-ss-slider:not(.n2-ss-loaded):not([data-ss-carousel]) .n2-ss-slide-backgrounds [data-public-id=\"1\"],\n.n2-ss-slider:not(.n2-ss-loaded) .n2-ss-slider-controls-advanced {\n\topacity:", $contCss );
				Gen::SetArrField( $sett, array( 'contPr', 'css', 'custom', 'n2-ss-slider', 'data' ), $contCss );
			}
		}
	}

	if( $verFrom && $verFrom < 129 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'img', 'szAdaptDpr' ), false );

		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'esntlsThAni' ), false );

		{
			$contCss = Gen::GetArrField( $sett, array( 'contPr', 'css', 'custom', 'rev-slider', 'data' ) );
			if( is_string( $contCss ) )
			{
				$contCss = preg_replace( '@height:\\s*calc\\(100vh\\s*@', 'height: calc(var(--seraph-accel-dvh) ', $contCss );
				$contCss = preg_replace( '@margin-top:\\s*calc\\(-100vh\\s*@', 'margin-top: calc(-1*var(--seraph-accel-dvh) ', $contCss );
				Gen::SetArrField( $sett, array( 'contPr', 'css', 'custom', 'rev-slider', 'data' ), $contCss );
			}
		}
	}

	if( $verFrom && $verFrom < 130 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrStrtch' ), false );
	}

	if( $verFrom && $verFrom < 131 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'lazy', 'p' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'beThAni' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'xooelTabs' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtCntr' ), false );

		Gen::SetArrField( $sett, array( 'cache', 'cntLen' ), true );
	}

	if( $verFrom && $verFrom < 133 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'jqSldNivo' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'upbCntVid' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wooPrcFlt' ), false );

		{
			$contCss = Gen::GetArrField( $sett, array( 'contPr', 'css', 'custom', 'n2-ss-slider', 'data' ) );
			if( is_string( $contCss ) )
			{
				$contCss = str_replace( '[data-slide-public-id="1"]', '[data-slide-public-id][data-lzl-first="1"]', $contCss );
				$contCss = str_replace( '[data-public-id="1"]', '[data-public-id][data-lzl-first="1"]', $contCss );
				Gen::SetArrField( $sett, array( 'contPr', 'css', 'custom', 'n2-ss-slider', 'data' ), $contCss );
			}
		}
	}

	if( $verFrom && $verFrom < 134 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'sldRev7' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'css', 'font', 'deinlLrg' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'css', 'font', 'deinlLrgSize' ), 512 );
	}

	if( $verFrom && $verFrom < 135 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'ctxContPr' ), true );
		Gen::SetArrField( $sett, array( 'contPr', 'css', 'font', 'optLoadNameExpr' ), '' );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtPrmLott' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviDataAni' ), false );
	}

	if( $verFrom && $verFrom < 136 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'opAgentPostpone' ), false );
	}

	if( $verFrom && $verFrom < 137 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'lazy', 'own' ), true );
	}

	if( $verFrom && $verFrom < 138 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'nktrLott' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'grnshftPbAosOnceAni' ), false );
	}

	if( $verFrom && $verFrom < 139 )
	{
		foreach( Gen::GetArrField( $sett, array( 'contPr', 'cdn', 'items' ), array() ) as $i => $settCdnItem )
			Gen::SetArrField( $sett, array( 'contPr', 'cdn', 'items', $i, 'sa' ), false );
	}

	if( $verFrom && $verFrom < 140 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'comprAsync' ), false );

		Gen::SetArrField( $sett, array( 'contPr', 'img', 'szAdaptAsync' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'szAdaptOnDemand' ), false );

		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'grnshftPbAosAni' ), false );
	}

	if( $verFrom && $verFrom < 141 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wooTabs' ), false );
	}

	if( $verFrom && $verFrom < 142 )
	{
		if( Gen::GetArrField( $sett, array( 'cache', 'exclArgsAll' ), false ) )
			Gen::SetArrField( $sett, array( 'cache', 'skipArgsEnable' ), false );
		else
		{
			Gen::SetArrField( $sett, array( 'cache', 'skipArgsEnable' ), true );
			if( Gen::GetArrField( $sett, array( 'cache', 'skipArgsAll' ), false ) )
				Gen::SetArrField( $sett, array( 'cache', 'exclArgsAll' ), true );
		}
	}

	if( $verFrom && $verFrom < 143 )
	{

		$grps = Gen::GetArrField( $sett, array( 'contPr', 'grps', 'items' ), array() );
		$grps[ '@a' ] = Gen::GetArrField( OnOptGetDef_Sett(), array( 'contPr', 'grps', 'items', '@a' ), array() );
		Gen::SetArrField( $grps, array( '@a', 'enable' ), 0 );
		Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items' ), $grps );

	}

	if( $verFrom && $verFrom < 144 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'js', 'prvntDblInit' ), false );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'strmtbUpcTmr' ), false );

		{
			$grpsExcl = Gen::GetArrField( $sett, array( 'contPr', 'grps', 'items', '@a', 'sklCssSelExcl' ), array() );
			foreach( array(
				'r=:@#[\\w\\-]+@' =>
					'@#([\\w\\-]+)@',

				'@\\.(?:product_cat|product_tag|category|tag|term|pa|woocommerce-product-attributes-item--attribute|comment-author)[\\-_]([\\w\\-]+)@i' =>
					'@\\.(?:product_cat|product_tag|video_tag|category|categories|tag|term|pa|woocommerce-product-attributes-item--attribute|comment-author)[\\-_]([\\w\\-]+)@i'
				) as $f => $r )
				if( ( $i = array_search( $f, $grpsExcl ) ) !== false )
					$grpsExcl[ $i ] = $r;
			Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', '@a', 'sklCssSelExcl' ), $grpsExcl );
		}

		{
			if( array_search( '@(?:^|\\s)svg(?:$|[\\s\\.#\\[])@', Gen::GetArrField( OnOptGetDef_Sett(), array( 'contPr', 'css', 'nonCrit', 'autoExcls' ), array() ) ) === false )
				Gen::SetArrField( $sett, array( 'contPr', 'css', 'nonCrit', 'autoExcls', '+' ), '@(?:^|\\s)svg(?:$|[\\s\\.#\\[])@' );
		}
	}

	if( $verFrom && $verFrom < 145 )
	{
		{
			$grpsExcl = Gen::GetArrField( $sett, array( 'contPr', 'grps', 'items', '@a', 'sklCssSelExcl' ), array() );
			foreach( array(
				'@#([\\w\\-]+)@' =>
					'@#([\\w\\-\\%]+)@',
				) as $f => $r )
				if( ( $i = array_search( $f, $grpsExcl ) ) !== false )
					$grpsExcl[ $i ] = $r;

			if( array_search( '@[^[:alnum:]]eb-(?:row|column|text|accordion(?:-item|))-([[:alnum:]]+)[^[:alnum:]\\-_]@i', $grpsExcl ) === false && ( $i = array_search( '@[\\.#][\\w\\-]*[\\-_]([\\da-f]+)[\\W_]@i', $grpsExcl ) ) !== false )
				array_splice( $grpsExcl, $i, 0, array( '@[^[:alnum:]]eb-(?:row|column|text|accordion(?:-item|))-([[:alnum:]]+)[^[:alnum:]\\-_]@i' ) );

			Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', '@a', 'sklCssSelExcl' ), $grpsExcl );
		}
	}

	if( $verFrom && $verFrom < 146 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'frm', 'lazy', 'own' ), true );

		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrBg' ), Gen::GetArrField( $sett, array( 'contPr', 'frm', 'lazy', 'enable' ), false ) && Gen::GetArrField( $sett, array( 'contPr', 'frm', 'lazy', 'elmntrBg' ), false ) );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'youTubeFeed' ), Gen::GetArrField( $sett, array( 'contPr', 'frm', 'lazy', 'enable' ), false ) && Gen::GetArrField( $sett, array( 'contPr', 'frm', 'lazy', 'youTubeFeed' ), false ) );

		unset( $sett[ 'contPr' ][ 'frm' ][ 'lazy' ][ 'elmntrBg' ] );
		unset( $sett[ 'contPr' ][ 'frm' ][ 'lazy' ][ 'youTubeFeed' ] );

		{
			$grpsExcl = Gen::GetArrField( $sett, array( 'contPr', 'grps', 'items', '@a', 'sklCssSelExcl' ), array() );
			foreach( array(
				'@\\.(?:product_cat|product_tag|video_tag|category|categories|tag|term|pa|woocommerce-product-attributes-item--attribute|comment-author)[\\-_]([\\w\\-]+)@i' =>
					'@\\.(?:product_cat|product_tag|video_tag|category|categories|tag|term|label-term|pa|label-attribute-pa|woocommerce-product-attributes-item-|comment-author)[\\-_]([\\w\\-]+)@i',
				) as $f => $r )
				if( ( $i = array_search( $f, $grpsExcl ) ) !== false )
					$grpsExcl[ $i ] = $r;

			Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', '@a', 'sklCssSelExcl' ), $grpsExcl );
		}
	}

	if( $verFrom && $verFrom < 147 )
	{
		{
			$grpsExcl = Gen::GetArrField( $sett, array( 'contPr', 'grps', 'items', '@a', 'sklCssSelExcl' ), array() );
			foreach( array(
				'@[\\.#][\\w\\-]*[\\-_]([\\da-f]+)[\\W_]@i' =>
					'@[\\.#][\\w\\-\\:\\@\\\\]*[\\-_]([\\da-f]+)[\\W_]@i',
				) as $f => $r )
				if( ( $i = array_search( $f, $grpsExcl ) ) !== false )
					$grpsExcl[ $i ] = $r;

			Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', '@a', 'sklCssSelExcl' ), $grpsExcl );
		}

		{
			$autoExcls = Gen::GetArrField( $sett, array( 'contPr', 'css', 'nonCrit', 'autoExcls' ), array() );
			foreach( array( '@\\.uk-modal@', '@\\.uk-first-column@', '@\\.uk-grid-margin@', '@\\.uk-grid-stack@', '@\\.et_pb_column@', ) as $autoExclsExpr )
				if( !in_array( $autoExclsExpr, $autoExcls ) )
					$autoExcls[] = $autoExclsExpr;
			Gen::SetArrField( $sett, array( 'contPr', 'css', 'nonCrit', 'autoExcls' ), $autoExcls );
		}
	}

	if( $verFrom && $verFrom < 148 )
	{
		if( strlen( trim( Gen::GetArrField( $sett, array( 'cache', 'nginx', 'fastCgiDir' ), '' ) ) ) )
			Gen::SetArrField( $sett, array( 'cache', 'nginx', 'method' ), 'direct' );
		else
			Gen::SetArrField( $sett, array( 'cache', 'nginx', 'method' ), '3rdp' );

		Gen::SetArrField( $sett, array( 'cache', 'fastTmpOpt' ), false );
	}

	if( $verFrom && $verFrom < 149 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'hrrCntDwnTmr' ), false );
	}

	if( $verFrom && $verFrom < 150 )
	{
		{
			$grpsExcl = Gen::GetArrField( $sett, array( 'contPr', 'grps', 'items', '@a', 'sklCssSelExcl' ), array() );
			foreach( array(
				'@\\.(?:product_cat|product_tag|video_tag|category|categories|tag|term|label-term|pa|label-attribute-pa|woocommerce-product-attributes-item-|comment-author)[\\-_]([\\w\\-]+)@i' =>
					'@\\.(?:category|categories|tag|term|label-term|pa|label-attribute-pa|woocommerce-product-attributes-item-|comment-author|(?\'ENUM_TAXONOMIES_NOTBUILTIN\')|(?\'ENUM_POSTTYPES_NOTBUILTINVIEWABLESPEC\'))[\\-_]([\\w\\-]+)@i'
				) as $f => $r )
				if( ( $i = array_search( $f, $grpsExcl ) ) !== false )
					$grpsExcl[ $i ] = $r;
			Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', '@a', 'sklCssSelExcl' ), $grpsExcl );
		}

		Gen::SetArrField( $sett, array( 'cache', 'lazyInvFr' ), false );
		Gen::SetArrField( $sett, array( 'cache', 'timeoutFrCln' ), 60 * 60 );
	}

	if( $verFrom && $verFrom < 151 )
	{
		{
			$grpsExcl = Gen::GetArrField( $sett, array( 'contPr', 'grps', 'items', '@a', 'sklCssSelExcl' ), array() );
			_UpdTokensArr( $grpsExcl, array( '@\\.kbx-((?\'POST_SLUG\'))@' => 0 ) );
			Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', '@a', 'sklCssSelExcl' ), $grpsExcl );
		}
	}

	if( $verFrom && $verFrom < 152 )
	{
		{
			$grpsExcl = Gen::GetArrField( $sett, array( 'contPr', 'grps', 'items', '@a', 'sklCssSelExcl' ), array() );
			_UpdTokensArr( $grpsExcl, array(
				'@\\.kbx-((?\'POST_SLUG\'))@' =>
					"r=pslg:@\\.(?:[\\-\\w]+[\\-\\_]|)((?'POST_SLUG'))[\\-\\_\\W]@i",

				'@\\.(?:category|categories|tag|term|label-term|pa|label-attribute-pa|woocommerce-product-attributes-item-|comment-author|(?\'ENUM_TAXONOMIES_NOTBUILTIN\')|(?\'ENUM_POSTTYPES_NOTBUILTINVIEWABLESPEC\'))[\\-_]([\\w\\-]+)@i' =>
					"@\\.(?:[\\-\\w]+[\\-\\_]|)(?:category|categories|tag|term|comment-author|(?'ENUM_TAXONOMIES_NOTBUILTIN'))[\\-\\_]([\\w\\-]+)@i",

				"@\\.(?:[\\-\\w]+[\\-\\_]|)(?:post|page|attachment|(?'ENUM_POSTTYPES_NOTBUILTINVIEWABLESPEC'))[\\-\\_]([\\w\\-]+)@i" =>
					array( "@\\.(?:[\\-\\w]+[\\-\\_]|)(?:category|categories|tag|term|comment-author|(?'ENUM_TAXONOMIES_NOTBUILTIN'))[\\-\\_]([\\w\\-]+)@i", 1 ),

				"r=txnm:@\\.(?:[\\-\\w]+[\\-\\_]|)(category|(?'ENUM_TAXONOMIES_NOTBUILTIN'))[\\-\\_\\W]@i" =>
					array( "@\\.(?:[\\-\\w]+[\\-\\_]|)(?:category|categories|tag|term|comment-author|(?'ENUM_TAXONOMIES_NOTBUILTIN'))[\\-\\_]([\\w\\-]+)@i", 1 ),
			) );
			Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', '@a', 'sklCssSelExcl' ), $grpsExcl );
		}
	}

	if( $verFrom && $verFrom < 157 )
		Gen::SetArrField( $sett, array( 'cache', 'srvUpd' ), false );

	if( $verFrom && $verFrom < 158 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'updGlobs', 'op' ), Gen::GetArrField( $sett, array( 'cache', 'updTermsOp' ) ) );
		unset( $sett[ 'cache' ][ 'updTermsOp' ] );

		Gen::SetArrField( $sett, array( 'cache', 'updGlobs', 'terms', 'enable' ), Gen::GetArrField( $sett, array( 'cache', 'updTerms' ) ) );
		unset( $sett[ 'cache' ][ 'updTerms' ] );

		Gen::SetArrField( $sett, array( 'cache', 'updGlobs', 'terms', 'deps' ), Gen::GetArrField( $sett, array( 'cache', 'updTermsDeps' ) ) );
		unset( $sett[ 'cache' ][ 'updTermsDeps' ] );
	}

	if( $verFrom && $verFrom < 159 )
	{
		foreach( Gen::GetArrField( $sett, array( 'cache', 'data', 'items' ) ) as $k => $itemData )
		{
			foreach( array( 'exclArgsAll', 'exclArgs', 'skipArgsEnable', 'skipArgsAll', 'skipArgs' ) as $fld )
				Gen::SetArrField( $itemData, array( $fld ), Gen::GetArrField( $sett, array( 'cache', $fld ) ) );
			Gen::SetArrField( $sett, array( 'cache', 'data', 'items', $k ), $itemData );
		}
	}

	if( $verFrom && $verFrom < 162 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviMv' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviSld' ), false );
	}

	if( $verFrom && $verFrom < 163 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtEaelCntdwn' ), false );
	}

	if( $verFrom && $verFrom < 164 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'szAdaptDprMin' ), 100 );
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'lazy', 'plchRast' ), true );

		{
			$fld = array( 'cache', 'viewsCompatGrps', 0, 'agents' );
			$a = Gen::GetArrField( $sett, $fld, array() );
			_UpdTokensArr( $a, array(
				'@\\Wmsie \\d+\\.\\d+\\W@' =>
					'@\\Wmsie \\d+\\.\\d+\\W@i',

				'@\\Wtrident/\\d+\\.\\d+\\W@' =>
					'@\\Wtrident/\\d+\\.\\d+\\W@i',

				'@\\Wyandexmetrika/\\d+\\.\\d+\\W@' =>
					'@\\Wyandexmetrika/\\d+\\.\\d+\\W@i',

				'@\\Wgoogleadsenseinfeed\\W@' =>
					'@\\Wgoogleadsenseinfeed\\W@i',

				'!@\\Wchrome/\\d+\\W@ & @(?:\\W|^)safari/([\\d\\.]+)(?:\\W|$)@ < 603.3.8' =>
					'!@\\WChrome/\\d+\\W@i & @(?:\\W|^)Safari/([\\d\\.]+)(?:\\W|$)@i < 603.3.8',

				'@\\sMac\\sOS\\sX\\s([\\d\\_]+)@i < 10.12.6' =>
					'!@\\WChrome/\\d+\\W@i & @\\sMac\\sOS\\sX\\s([\\d\\_]+)@i < 10.12.6',

				'!@\\Wchrome/\\d+\\W@ & @(?:\\W|^)safari/([\\d\\.]+)(?:\\W|$)@' =>
					'!@\\WChrome/\\d+\\W@i & @(?:\\W|^)Safari/([\\d\\.]+)(?:\\W|$)@i',

				'@\\sMac\\sOS\\sX\\s([\\d\\_]+)@i' =>
					'!@\\WChrome/\\d+\\W@i & @\\sMac\\sOS\\sX\\s([\\d\\_]+)@i',
			) );
			Gen::SetArrField( $sett, $fld, $a );
		}
	}

	if( $verFrom && $verFrom < 165 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wooOuPrdGal' ), false );
	}

	if( $verFrom && $verFrom < 166 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wooPrdGallSldBde' ), false );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtAniHdln' ), false );
	}

	if( $verFrom && $verFrom < 167 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wooPrdGall' ), Gen::GetArrField( $sett, array( 'contPr', 'cp', 'wooPrdGallSldBde' ), false ) || Gen::GetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtWooPrdImgs' ), false ) );
		Gen::UnsetArrField( $sett, array( 'contPr', 'cp', 'wooPrdGallSldBde' ) );
		Gen::UnsetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtWooPrdImgs' ) );
	}

	if( $verFrom && $verFrom < 168 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wooPrdGallAstrThmbsHeight' ), false );
	}

	if( $verFrom && $verFrom < 169 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wooPrdGallFltsmThmbs' ), false );
	}

	if( $verFrom && $verFrom < 172 )
	{
		{
			$fld = array( 'cache', 'updPostDeps' );
			$a = Gen::GetArrField( $sett, $fld, array() );
			_UpdTokensArr( $a, array(
				'@post@{ID}:@pageNums' =>
					'@post@{ID}:@P@LOW:@pageNums',

				'@post@{ID}:@commentPageNums' =>
					'@post@{ID}:@P@LOW:@commentPageNums',

				'@postsBase@{post_type}:<|@pageNums|@commentPageNums>' =>
					'@postsBase@{post_type}:<|@P@LOW:@pageNums|@P@LOW:@commentPageNums>',

				'@postsBase@{post_type}:<|@pageNums>' =>
					'@postsBase@{post_type}:<|@P@LOW:@pageNums>',

				'@termsOfClass@categories@{post_type}@{ID}:<|@pageNums|@commentPageNums>' =>
					'@P@LOW:@termsOfClass@categories@{post_type}@{ID}:<|@pageNums|@commentPageNums>',

				'@termsOfClass@categories@{post_type}@{ID}:<|@pageNums>' =>
					'@P@LOW:@termsOfClass@categories@{post_type}@{ID}:<|@pageNums>',
			) );
			Gen::SetArrField( $sett, $fld, $a );
		}
	}

	if( $verFrom && $verFrom < 174 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'kpPsvStls' ), false );

		Gen::SetArrField( $sett, array( 'cache', 'ctxLazyInv' ), false );
	}

	if( $verFrom && $verFrom < 175 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtCntdwn' ), false );
	}

	if( $verFrom && $verFrom < 176 )
	{
		Gen::SetArrField( $sett, array( 'asyncSmpOpt' ), false );
	}

	if( $verFrom && $verFrom < 177 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'xstrThSwpr' ), false );
	}

	if( $verFrom && $verFrom < 178 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wooGsPrdGal' ), false );
	}

	if( $verFrom && $verFrom < 179 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'css', 'skipBad' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'js', 'skipBad' ), false );
	}

	if( $verFrom && $verFrom < 180 )
	{
		if( isset( $sett[ 'cache' ][ '_vPth' ] ) )
		{
			$a = array();
			foreach( Gen::GetArrField( $sett, array( 'cache', '_vPth' ), array() ) as $v )
				if( strpos( ( string )($v[ 'r' ]??null), 'wph-throw-' ) === false )
					$a[] = $v;
			Gen::SetArrField( $sett, array( 'cache', '_vPth' ), base64_encode( serialize( $a ) ) );
		}
	}

	if( $verFrom && $verFrom < 181 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtTmFnfctCntr' ), false );
	}

	if( $verFrom && $verFrom < 182 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wooPrdGallCrftThmbs' ), false );
	}

	if( $verFrom && $verFrom < 185 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'js', 'groupTrC' ), false );
	}

	if( $verFrom && $verFrom < 186 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'btCntSld' ), false );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'btMenu' ), false );
	}

	if( $verFrom && $verFrom < 187 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviVidBox2' ), false );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wcs' ), false );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'slckGen' ), false );
	}

	if( $verFrom && $verFrom < 188 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'bdeSwpr' ), false );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'bdeTabs' ), false );
	}

	if( $verFrom && $verFrom < 189 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'tpgbAni' ), false );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviCntdwnTmr' ), false );
	}

	if( $verFrom && $verFrom < 190 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wooNasaPrdGall' ), false );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrStckHdr' ), false );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wooPrdGallSld' ), false );
	}

	return( $sett );
}

function _UpdTokensArr( &$a, $aItem )
{
	foreach( $aItem as $item => $action )
	{
		if( $action === true )
		{
			if( !in_array( $item, $a ) )
				$a[] = $item;
		}
		else if( is_integer( $action ) )
		{
			if( !in_array( $item, $a ) )
				array_splice( $a, $action, 0, array( $item ) );
		}
		else if( is_string( $action ) )
		{
			if( ( $i = array_search( $item, $a ) ) !== false )
				$a[ $i ] = $action;
		}
		else if( is_array( $action ) )
		{
			if( !in_array( $item, $a ) )
			{
				if( ( $i = array_search( $action[ 0 ], $a ) ) !== false )
					array_splice( $a, $i + $action[ 1 ], 0, array( $item ) );

			}
		}
	}
}

function Op_DepItems_MigrateFromOld( $dependItems )
{
	foreach( $dependItems as &$dependItem )
	{
		$dependItem = preg_replace( '`(^|\\W)IF:([^:]+):([^:]+)`',							'${1}@IF@${2}@${3}',					$dependItem );
		$dependItem = preg_replace( '`(^|\\W)terms:`',										'${1}@terms@',							$dependItem );
		$dependItem = preg_replace( '`(^|\\W)termsOfClass:([^:]+):([^:]+):([^:]+)`',		'${1}@termsOfClass@${2}@${3}@${4}',		$dependItem );
		$dependItem = preg_replace( '`(^|\\W)home($|\\W)`',									'${1}@home${2}',						$dependItem );
		$dependItem = preg_replace( '`(^|\\W)postsBase:`',									'${1}@postsBase@',						$dependItem );
		$dependItem = preg_replace( '`(^|\\W)posts:`',										'${1}@posts@',							$dependItem );
		$dependItem = preg_replace( '`(^|\\W)sitemapItems:`',								'${1}@sitemapItems@',					$dependItem );
	}

	return( $dependItems );
}

function OnFileValuesGetRootDir( $var = null )
{
	return( array( GetCacheDir() . '/s', $var !== null ? ( string )$var : GetSiteId() ) );
}

function OnAsyncTasksGetFile()
{
	return( GetCacheDir() . '/at' );
}

function OnAsyncTasksGetPushUrlFile( $bForceIdx = false )
{
	return( !$bForceIdx && Gen::GetArrField( Plugin::SettGetGlobal(), array( 'asyncUseCron' ), true ) ? 'wp-cron.php' : 'index.php' );
}

function OnAsyncTasksUseCmptNbr()
{
	return( Gen::GetArrField( Plugin::SettGetGlobal(), array( 'asyncUseCmptNbr' ), false ) );
}

function OnAsyncTasksSetNeededHdrs( $aSrv, $aHdr )
{
	CacheExt_Clear_CopyHdrs( $aSrv );
	return( array_merge( Net::GetRequestHeaders( $aSrv, true, false, CacheExt_Clear_CopyHdrsArr() ), $aHdr ) );
}

function OnAsyncTasksPushGetMode( $settGlob = null )
{
	if( $settGlob === null )
		$settGlob = Plugin::SettGetGlobal();
	return( Gen::GetArrField( $settGlob, array( 'asyncMode' ), '' ) );
}

function OnAsyncTasksPushReGetLauncher()
{
	return( Gen::GetArrField( Plugin::SettGetGlobal(), array( 'reLnch' ), '' ) );
}

function OnAsyncTasksPushReGetTmpDir()
{
	return( GetCacheDir() . '/tmp' );
}

function OnExtToolsGetDir()
{
	return( GetCacheDir() . '/b' );
}

function OnOptGetDef_Sett()
{
	return( array(

		'full' => true,

		'cache' => array(
			'enable' => true,

			'normAgent' => true,
			'chkNotMdfSince' => false,
			'cntLen' => true,
			'opAgentPostpone' => true,

			'srv' => true,
			'srvClr' => true,
			'srvUpd' => true,
			'srvUpdTimeout' => 5,
			'nginx' => array(
				'method'=> '3rdp',
				'url' => '',
				'urlAll' => '',
				'fastCgiDir' => '',
				'fastCgiLevels' => '1:2',
			),
			'sucuri' => array(
				'apiKey' => '',
				'apiSecret' => '',
			),

			'cron' => true,
			'forceAdvCache' => false,

			'lazyInv' => true,
			'lazyInvInitTmp' => true,
			'lazyInvForcedTmp' => false,
			'lazyInvTmp' => false,
			'fastTmpOpt' => true,
			'lazyInvFr' => true,

			'updPost' => true,
			'updPostDelay' => 0,
			'updPostOp' => 0,
			'updPostDeps' => array(
				'@home',
				'@post@{ID}:@pageNums',
				'@post@{ID}:@commentPageNums',
				'@postsBase@{post_type}:<|@pageNums|@commentPageNums>',
				'@termsOfClass@categories@{post_type}@{ID}:<|@pageNums|@commentPageNums>',
			),
			'updPostMeta' => false,
			'updPostMetaExcl' => array(
				'@.*@',
				'@^\\d+$@',
				'@^_edit_lock$@',
				'@^_edit_last$@',
				'@^classic-editor-remember$@',
				'@post_views_@',
				'@^import_started_at@',
				'@^_wc_gla_@',
				'@^_yoast_@',
				'@^cwg_total_subscribers@',
				'@^_backorders$@',
				'@^_last_seen$@',
				'@^woodmart_history_of_visits$@',
			),

			'updGlobs' => array(
				'op' => 0,

				'terms' => array(
					'enable' => false,
					'deps' => array( 'category', 'product_cat', 'course_cat' ),
				),

				'menu' => array(
					'enable' => false,
				),

				'elmntrTpl' => array(
					'enable' => false,
				),

				'tblPrss' => array(
					'enable' => false,
				),

				'flntFrm' => array(
					'enable' => false,
				),
			),

			'updAllDeps' => array(
				'@home',
				'@postsViewable:<|@pageNums|@commentPageNums>',
				'@termsOfClass@categories',
			),

			'updSche' => array(
				'def' => array(
					'enable' => false,
					'op' => 0,
					'prior' => 7,
					'period' => 24,
					'periodN' => 1,
					'times' => array(
						array(
							'm' => 0,
							'tm' => 0,
						)
					),
					'deps' => array(
						'@home',
					),
				),
			),

			'updByTimeout' => true,

			'maxProc' => 1,
			'procInterval' => 5,
			'procIntervalShort' => 1,
			'procMemLim' => 2048,
			'procTmLim' => 570,
			'procWorkInt' => 0.5,
			'procPauseInt' => 0.5,

			'autoProc' => true,

			'timeout' => 60 * 24 * 60,
			'timeoutFr' => 60,
			'timeoutCln' => 182 * 24 * 60,
			'timeoutFrCln' => 60 * 60,
			'ctxTimeoutCln' => 1 * 24 * 60,
			'extObjTimeoutCln' => 7 * 24 * 60,
			'autoClnPeriod' => 1 * 24 * 60,
			'useTimeoutClnForWpNonce' => true,

			'encs' => array( '', 'gzip', 'deflate', 'compress' ),
			'dataCompr' => array( 'deflate' ),
			'dataLvl' => array(),
			'useDataComprAssets' => true,

			'chunks' => array(
				'enable' => true,
				'js' => true,
				'css' => true,
				'seps' => array(

					array( 'enable' => true, 'sel' => './/header[1]',	'side' => 1 | 2 ),
					array( 'enable' => true, 'sel' => './/footer[last()]',	'side' => 1 | 2 ),

					array( 'enable' => true, 'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " header ")][1]',	'side' => 1 | 2 ),
					array( 'enable' => true, 'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " footer ")][last()]',	'side' => 1 | 2 ),

					array( 'enable' => true, 'sel' => './/div[@id="comments" and contains(concat(" ", normalize-space(@class), " "), " comments-area ")]',	'side' => 1 | 2 ),
					array( 'enable' => true, 'sel' => './/section[@id="comment-wrap"]',	'side' => 1 | 2 ),

					array( 'enable' => true, 'sel' => './/div[@data-elementor-type="header"]',	'side' => 1 | 2 ),
					array( 'enable' => true, 'sel' => './/div[@data-elementor-type="footer"]',	'side' => 1 | 2 ),
					array( 'enable' => true, 'sel' => './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-icon-list-items ")]',	'side' => 1 | 2 ),

					array( 'enable' => true, 'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " tdc-header-wrap ")]',	'side' => 1 | 2 ),
					array( 'enable' => true, 'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " tdc-footer-wrap ")]',	'side' => 1 | 2 ),
					array( 'enable' => true, 'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " td-header-template-wrap ")]',	'side' => 1 | 2 ),
					array( 'enable' => true, 'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " td-footer-template-wrap ")]',	'side' => 1 | 2 ),

					array( 'enable' => true, 'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " fusion-tb-header ")]',	'side' => 1 | 2 ),
					array( 'enable' => true, 'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " fusion-tb-footer ")]',	'side' => 1 | 2 ),

					array( 'enable' => true, 'sel' => './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_comments_module ")]',	'side' => 1 | 2 ),

				),
			),

			'urisExcl' => array(
				'/checkout/',
				'@sitemap@',

				'@(?:^|/)page/@',
			),
			'exclAgents' => array(
				'printfriendly',
			),
			'exclCookies' => array(),

			'exclArgsAll' => true,
			'exclArgs' => array(
				'aiosp_sitemap_path',
				'aiosp_sitemap_page',
				'xml_sitemap',
				'seopress_sitemap',
				'seopress_news',
				'seopress_video',
				'seopress_cpt',
				'seopress_paged',
				'sitemap',
				'sitemap_n',
				'elementor-preview',
				'wc-ajax',
			),

			'skipArgsEnable' => false,
			'skipArgsAll' => false,
			'skipArgs' => array( 'redirect_to', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'story_fbid', 'mibextid', 'gclid', 'wbraid', 'gbraid', 'gad_campaignid', 'gad_source', '_ga', 'yclid', 'srsltid' ),

			'exclConts' => array(
				'.//*[@id="wpadminbar"]',
				'.//body[contains(concat(" ",normalize-space(@class)," ")," theme-woodmart ")][contains(concat(" ",normalize-space(@class)," ")," seraph-accel-view-cmn ")]//*[contains(concat(" ",normalize-space(@class)," ")," whb-general-header-inner ")][count(./*[contains(concat(" ",normalize-space(@class)," ")," whb-visible-lg ")]) = 0]',
			),

			'hdrs' => array(
				'@^Set-Cookie\\s*:\\s*wordpress_test_cookie\\s*=@i',
				'@^X-XSS-Protection\\s*:@i',
				'@^X-Frame-Options\\s*:@i',
				'@^X-Robots-Tag\\s*:@i',
				'@^Content-Security-Policy\\s*:@i',
				'@^Strict-Transport-Security\\s*:@i',
				'@^Referrer-Policy\\s*:@i',
				'@^Feature-Policy\\s*:@i',
				'@^Permissions-Policy\\s*:@i',
				'@^Cf-Edge-Cache\\s*:@i',
				'@^Ec-Cdn-@i',

			),

			'views' => true,
			'viewsDeviceGrps' => array(

				array(
					'enable' => false,
					'name' => '',
					'id' => 'mobilehighres',
					'agents' => array( 'android', 'bada', 'incognito',  'maemo', 'mobi', 'opera mini', 's8000', 'series60', 'ucbrowser', 'ucweb', 'webmate', 'webos', ),
				),

				array(
					'enable' => false,
					'name' => '',
					'id' => 'mobilelowres',
					'agents' => array( '240x320', '2.0 mmp', '\bppc\b', 'alcatel', 'amoi', 'asus', 'au-mic', 'audiovox', 'avantgo', 'benq', 'bird', 'blackberry', 'blazer', 'cdm', 'cellphone', 'danger', 'ddipocket', 'docomo', 'dopod', 'elaine/3.0', 'ericsson', 'eudoraweb', 'fly', 'haier', 'hiptop', 'hp.ipaq', 'htc', 'huawei', 'i-mobile', 'iemobile', 'iemobile/7', 'iemobile/9', 'j-phone', 'kddi', 'konka', 'kwc', 'kyocera/wx310k', 'lenovo', 'lg', 'lg/u990', 'lge vx', 'midp', 'midp-2.0', 'mmef20', 'mmp', 'mobilephone', 'mot-v', 'motorola', 'msie 10.0', 'netfront', 'newgen', 'newt', 'nintendo ds', 'nintendo wii', 'nitro', 'nokia', 'novarra', 'o2', 'openweb', 'opera mobi', 'opera.mobi', 'p160u', 'palm', 'panasonic', 'pantech', 'pdxgw', 'pg', 'philips', 'phone', 'playbook', 'playstation portable', 'portalmmm', 'proxinet', 'psp', 'qtek', 'sagem', 'samsung', 'sanyo', 'sch', 'sch-i800', 'sec', 'sendo', 'sgh', 'sharp', 'sharp-tq-gx10', 'small', 'smartphone', 'softbank', 'sonyericsson', 'sph', 'symbian', 'symbian os', 'symbianos', 'toshiba', 'treo', 'ts21i-10', 'up.browser', 'up.link', 'uts', 'vertu', 'vodafone', 'wap', 'willcome', 'windows ce', 'windows.ce', 'winwap', 'xda', 'xoom', 'zte', ),
				),

				array(
					'enable' => true,
					'name' => '',
					'id' => 'mobile',
					'agents' => array( 'mobile', 'android', 'silk/',  'blackberry', 'opera mini', 'opera mobi', ),
				),
			),

			'viewsGeo' => array(
				'enable' => false,
			),

			'viewsCompatGrps' => array(
				array(
					'enable' => true,
					'id' => 'c',
					'agents' => array(
						'@\\Wmsie \\d+\\.\\d+\\W@i',
						'@\\Wtrident/\\d+\\.\\d+\\W@i',
						'@\\Wyandexmetrika/\\d+\\.\\d+\\W@i',
						'@\\Wgoogleadsenseinfeed\\W@i',
						'!@\\WChrome/\\d+\\W@i & @(?:\\W|^)Safari/([\\d\\.]+)(?:\\W|$)@i < 603.3.8',
						'!@\\WChrome/\\d+\\W@i & @\\sMac\\sOS\\sX\\s([\\d\\_]+)@i < 10.12.6',
					),
				),

				array(
					'enable' => false,
					'id' => 'cm',
					'agents' => array(
						'@\\Wbingbot/\\d+\\.\\d+\\W@',
					),
				),
			),

			'viewsGrps' => array(
				array(
					'enable' => true,
					'name' => 'AMP',
					'cookies' => array(),
					'args' => array( 'amp', ),
				),

				array(
					'enable' => true,
					'name' => 'WPML',
					'cookies' => array( 'wp_wcml_currency', 'wcml_client_currency', ),
					'args' => array( 'lang', ),
				),

				array(
					'enable' => true,
					'name' => 'WPtouch',
					'cookies' => array( 'wptouch-pro-cache-state', 'wptouch-pro-view', ),
					'args' => array(),
				),

				array(
					'enable' => true,
					'name' => 'VillaTheme WooCommerce Multi Currency',
					'cookies' => array( 'wmc_current_currency' ),
				),

				array(
					'enable' => true,
					'name' => 'YITH Multi Currency Switcher for WooCommerce',
					'cookies' => array( 'yith_wcmcs_currency' ),
				),

				array(
					'enable' => true,
					'name' => 'Aelia Currency Switcher',
					'cookies' => array( 'aelia_cs_selected_currency' ),
				),

				array(
					'enable' => true,
					'name' => 'FOX - Currency Switcher',
					'cookies' => array( '@^wccs_changed_currency$@' ),
				),

				array(
					'enable' => false,
					'name' => 'GDPR Cookie Consent',
					'cookies' => array( 'viewed_cookie_policy', 'cli_user_preference' ),
				),

				array(
					'enable' => true,
					'name' => 'Pixelmate Cookie Banner',
					'cookies' => array( 'pixelmate' ),
				),

				array(
					'enable' => true,
					'name' => 'OneCom Cookie Banner',
					'cookies' => array( 'onecom_cookie_consent' ),
				),

				array(
					'enable' => true,
					'name' => 'us_cookie_notice',
					'cookies' => array( 'us_cookie_notice_accepted' ),
				),

				array(
					'enable' => true,
					'name' => 'Transcy',
					'cookies' => array( 'transcy_' ),
				),

				array(
					'enable' => true,
					'name' => 'WP Legal Pages',
					'cookies' => array( 'wplegalpages-' ),
				),
			),

			'ctx' => false,
			'ctxSkip' => false,
			'ctxSessSep' => true,
			'ctxContPr' => true,
			'ctxCliRefresh' => true,
			'ctxLazyInv' => false,
			'ctxGrps' => array(
				'common' => array(
					'enable' => true,
					'name' => 'Common',
					'cookies' => array(
						'wp-postpass_',
						'comment_author_',
						'sc_commented_posts',
					),
					'args' => array(
						'key',
					),

					'tables' => array(

						array(
							'name' => '%PREFIX%users',
							'col' => 'ID',
						),

						array(
							'name' => '%PREFIX%usermeta',
							'col' => 'user_id',
						),

					),
				),

				'wordpress-social-login' => array(
					'enable' => true,
					'name' => 'Social Login',
					'args' => array(
						'action',
					),
				),

				'theme_woodmart' => array(
					'enable' => true,
					'name' => 'WoodMart Theme',
					'cookies' => array(
						'woodmart_wishlist_products',
						'woodmart_compare_list',
					),
				),

				'jet-cw' => array(
					'enable' => true,
					'name' => 'Jet',
					'cookies' => array(
						'jet-wish-list',
						'jet-compare-list',
					),
				),

				'woocommerce' => array(
					'enable' => true,
					'name' => 'WooCommerce',
					'cookies' => array(
						'woocommerce_cart_hash',
						'DISABLED.wp_woocommerce_session_',
						'yith_wcwl_session_',
					),

					'args' => array(
						'add-to-cart',
						'remove_item',
						'removed_item',
						'undo_item',
						'update_cart',
						'proceed',
						'order_again',
						'apply_coupon',
						'remove_coupon',
					),

					'tables' => array(

						array(
							'name' => '%PREFIX%woocommerce_sessions',
							'col' => 'session_key',
						),

						array(
							'name' => '%PREFIX%posts',
							'col' => 'ID',
							'nameRel' => '%PREFIX%postmeta',
							'colRel' => 'meta_value',
							'colRelLink' => 'post_id',
							'condRel' => array( 'meta_key' => array( '_customer_user' ) ),
						),

						array(
							'name' => '%PREFIX%postmeta',
							'col' => 'post_id',
							'nameRel' => '%PREFIX%postmeta',
							'colRel' => 'meta_value',
							'colRelLink' => 'post_id',
							'condRel' => array( 'meta_key' => array( '_customer_user' ) ),
						),

					),
				),

				'easy-digital-downloads' => array(
					'enable' => true,
					'name' => 'Easy Digital Downloads',
					'cookies' => array(
						'@^edd_items_in_cart$@ > 0 & @^PHPSESSID$@i',
					),
				),

				'lifterlms' => array(
					'enable' => false,
					'name' => 'LMS by LifterLMS',

					'tables' => array(

						array(
							'name' => '%PREFIX%lifterlms_sessions',
							'col' => 'session_key',
						),

						array(
							'name' => '%PREFIX%posts',
							'col' => 'ID',
							'nameRel' => '%PREFIX%lifterlms_user_postmeta',
							'colRel' => 'user_id',
							'colRelLink' => 'post_id',
						),

						array(
							'name' => '%PREFIX%postmeta',
							'col' => 'post_id',
							'nameRel' => '%PREFIX%lifterlms_user_postmeta',
							'colRel' => 'user_id',
							'colRelLink' => 'post_id',
						),

					),
				),

				'wp-recall' => array(
					'enable' => true,
					'name' => 'WP-Recall',

					'tables' => array(

						array(
							'name' => '%PREFIX%rcl_bookmarks',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_bookmarks_gr',
							'col' => 'user_gr',
						),

						array(
							'name' => '%PREFIX%rcl_chat_messages',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_chat_messagemeta',
							'col' => 'message_id',
							'nameRel' => '%PREFIX%rcl_chat_messages',
							'colRel' => 'user_id',
							'colRelLink' => 'message_id',
						),

						array(
							'name' => '%PREFIX%rcl_chat_users',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_chats',
							'col' => 'chat_id',
							'nameRel' => '%PREFIX%rcl_chat_users',
							'colRel' => 'user_id',
							'colRelLink' => 'chat_id',
						),

						array(
							'name' => '%PREFIX%rcl_feeds',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_groups_users',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_groups_options',
							'col' => 'group_id',
							'nameRel' => '%PREFIX%rcl_groups_users',
							'colRel' => 'user_id',
							'colRelLink' => 'group_id',
						),

						array(
							'name' => '%PREFIX%rcl_groups',
							'col' => 'ID',
							'nameRel' => '%PREFIX%rcl_groups_users',
							'colRel' => 'user_id',
							'colRelLink' => 'group_id',
						),

						array(
							'name' => '%PREFIX%rcl_notifications',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_orders',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_order_items',
							'col' => 'order_id',
							'nameRel' => '%PREFIX%rcl_orders',
							'colRel' => 'user_id',
							'colRelLink' => 'order_id',
						),

						array(
							'name' => '%PREFIX%rcl_rating_values',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_rating_totals',
							'col' => 'object_id',
							'nameRel' => '%PREFIX%rcl_rating_values',
							'colRel' => 'user_id',
							'colRelLink' => 'object_id',
						),

						array(
							'name' => '%PREFIX%rcl_rating_users',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_temp_media',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_user_action',
							'col' => 'user',
						),

						array(
							'name' => '%PREFIX%rcl_uw_shares',
							'col' => 'author_id',
						),

					),
				),

				'cmsrush' => array(
					'enable' => true,
					'name' => 'CMS Rush',

					'cookies' => array(
						'cmsrush_',
					),
				),
			),

			'data' => array(
				'items' => array(
				),
			),
		),

		'cacheBr' => array(
			'enable' => true,
			'timeout' => 365 * 24 * 60,
		),

		'cacheObj' => array(
			'enable' => false,
			'forceDropin' => false,
			'timeout' => 24 * 60 * 60,

			'groupsGlobal' => array(
				'blog-details',
				'blog-id-cache',
				'blog-lookup',
				'global-posts',
				'networks',
				'rss',
				'sites',
				'site-details',
				'site-lookup',
				'site-options',
				'site-transient',
				'users',
				'useremail',
				'userlogins',
				'usermeta',
				'user_meta',
				'userslugs',

				'blog_meta',
				'image_editor',
				'network-queries',
				'site-queries',
				'theme_files',
				'translation_files',
				'user-queries',
			),

			'groupsNonPersistent' => array(
				'comment',
				'counts',
				'plugins',
				'theme_json',
				'themes',
				'trp',
				'wc_session_id',
			),
		),

		'contPr' => array(
			'enable' => true,
			'normalize' => 1|2,

			'normUrl' => false,
			'normUrlMode' => 2,

			'min' => true,
			'cln' => array(
				'cmts' => true,
				'cmtsExcl' => array(
					'@^\\s*/?noindex\\s*$@i',
					'@\\[et-ajax\\]@i',
					'@^\\s*\\[if\\s@i',
					'@\\[endif\\]\\s*$@i',
					'@fwp-loop@i',

					'@data-map-zoom@i',

					'@react@i',
				),
				'items' => array(
					'.//img/@loading',
					'.//iframe/@loading',
					'.//link[@rel="preload"][@as="font"][not(self::node()[@seraph-accel-crit="1"])]',
					'.//link[@rel="modulepreload"][not(self::node()[@seraph-accel-crit="1"])]',
				),
			),
			'rpl' => array(
				'items' => array(
					array(
						'enable' => true,
						'expr' => '@<link\\s+rel="stylesheet"[^>]+(consent-original-href-_)=[^>]+>@',
						'data' => 'href'
					),
					array(
						'enable' => true,
						'expr' => '@<body[^>]+(cm-manage-google-fonts)[^>]+>@',
						'data' => ''
					),
					array(
						'enable' => true,
						'expr' => '@\\sid=\\"nelio-ab-testing-main-js\\"@ & @<body[^>]+class="()@',
						'data' => 'nab-done '
					),
				),
			),

			'rc' => array(
				'thmFltsm' => true,
				'gglTrn' => true,
				'aksmtAs' => true,
				'advWooSrch' => true,
				'jetMblMnu' => true,
				'wpelLnk' => true,
				'cfTrnstl' => true,
				'tagGrpsShfflBx' => true,
				'g5Ere' => true,
				'elmntrTrx' => true,
				'thmXStr' => true,
				'wooPrdQnt' => true,
				'asClnTlk' => true,
				'chbsBkngFrm' => true,
			),

			'lazy' => array(
				'items' => array(
				),
				'itemsExcl' => array(
				),
				'bjs' => true,
				'p' => false,
			),

			'fresh' => array(
				'smoothAppear' => true,
				'items' => array(

				),
			),

			'earlyPaint' => true,

			'img' => array(
				'srcAddLm' => false,
				'sysFlt' => false,
				'inlSml' => true,
				'inlSmlSize' => 1024,
				'deinlLrg' => true,
				'deinlLrgSize' => 2048,
				'redirOwn' => false,
				'redirCacheAdapt' => false,
				'comprAsync' => false,
				'webp' => array(
					'enable' => true,
					'redir' => true,
					'prms' => array(
						'q' => Img::WEBP_QUALITY_DEF,
					),
				),
				'avif' => array(
					'enable' => false,
					'redir' => false,
					'prms' => array(
						'q' => Img::AVIF_QUALITY_DEF,
						's' => Img::AVIF_SPEED_DEF,
					),
				),
				'szAdaptImg' => false,
				'szAdaptBg' => false,

				'szAdaptAsync' => false,
				'szAdaptOnDemand' => true,

				'szAdaptExcl' => array(

				),
				'szAdaptBgCxMin' => 0,
				'szAdaptDpr' => true,
				'szAdaptDprMin' => 100,
				'excl' => array(
					'.//svg[contains(concat(" ",normalize-space(@class)," ")," lottgen ")][contains(concat(" ",normalize-space(@class)," ")," js-lzl-ing ")]/image',
					'.//meta[@property]',
					'.//img[@uk-svg]',
					'.//img[@name="zsCaptchaImage"]',
					'.//img[contains(@src,"arttrk.com/pixel")]',
				),
				'lazy' => array(
					'setSize' => false,
					'load' => true,
					'own' => true,
					'smoothAppear' => true,
					'plchRast' => true,
					'del3rd' => true,
					'excl' => array(

						'.//img[contains(concat(\' \',normalize-space(@class),\' \'),\' jetpack-lazy-image \')]',
						'.//img[@data-opt-src]',
						'.//img[contains(concat(" ",normalize-space(@class)," ")," rev-slidebg ")]',
						'.//sr7-module//img',
						'.//*[contains(concat(" ",normalize-space(@class)," ")," n2-ss-slider ")]//img',
						'.//img[contains(concat(" ",normalize-space(@class)," ")," a3-notlazy ")]',
						'.//*[contains(concat(" ",normalize-space(@class)," ")," t-bgimg ")]',
						'.//*[contains(concat(" ",normalize-space(@class)," ")," posts-container ")][@data-load-animation]//*[contains(concat(" ",normalize-space(@class)," ")," post-featured-img ")]//img',
						'.//*[contains(concat(" ",normalize-space(@class)," ")," dani-lzl ")]//img',

						'ajs:.//img[@data-no-lazy="1"]',
						'ajs:.//img[contains(concat(" ",normalize-space(@class)," ")," skip-lazy ")]',
						'ajs:.//*[@data-zoom_image]/img',

					),
				),

				'cacheExt' => array(
					'crit:@\\.cdninstagram\\.com/@',
					'crit:@\\.googleusercontent\\.com/@',
					'@\\.ytimg\\.com/@',
					'@\\.vimeocdn\\.com/@',
				),
			),

			'frm' => array(
				'excl' => array(
					'ajs:.//*[contains(concat(" ",normalize-space(@class)," ")," wprm-recipe-video ")]/iframe',
					'ajs:.//iframe[contains(@src,"/maps")]',
					'ajs:.//iframe[contains(concat(" ",normalize-space(@class)," ")," rezdy ")]',
				),
				'lazy' => array(
					'enable' => true,
					'own' => true,
					'yt' => true,
					'vm' => true,
				),
			),

			'cp' => array(
				'elmntrBg' => true,
				'youTubeFeed' => true,
				'sldBdt' => true,
				'swBdt' => true,
				'vidJs' => true,
				'elmntrAni' => true,
				'elmntrSpltAni' => true,
				'elmntrTrxAni' => true,
				'elmntrBgSldshw' => true,
				'elmntrVids' => true,
				'qodefApprAni' => true,
				'prtThSkel' => true,
				'astrRsp' => true,
				'ntBlueThRspnsv' => true,
				'mdknThRspnsv' => true,
				'fltsmThBgFill' => true,
				'fltsmThAni' => true,
				'ukSldshw' => true,
				'ukBgImg' => true,
				'ukAni' => true,
				'ukGrid' => true,
				'ukModal' => true,
				'ukHghtVwp' => true,
				'ukNavBar' => true,
				'tmHdr' => true,
				'fusionBgVid' => true,
				'fsnEqHghtCols' => true,
				'fsnAni' => true,
				'thrvAni' => true,
				'phloxThRspnsv' => true,
				'phloxThAni' => true,
				'sldN2Ss' => false,
				'sldRev' => true,
				'sldRev_SmthLd' => true,
				'sldRev7' => true,
				'tdThumbCss' => true,
				'elmsKitImgCmp' => true,
				'elmsKitLott' => true,
				'haCrsl' => true,
				'jetCrsl' => true,
				'jetCrslPst' => true,
				'elmntrTabs' => true,
				'elmntrAccrdn' => true,
				'elmntrAdvTabs' => true,
				'elmntrNavMenu' => true,
				'elmntrPremNavMenu' => true,
				'elmntrPremScrl' => true,
				'elmntrPremTabs' => true,
				'elmntrPremCrsl' => true,
				'elmntrWdgtGal' => true,
				'elmntrWdgtImgCrsl' => true,
				'elmntrWdgtCntr' => true,
				'elmntrWdgtCntdwn' => true,
				'elmntrWdgtEaelCntdwn' => true,
				'elmntrWdgtTmFnfctCntr' => true,
				'elmntrWdgtAvoShcs' => true,
				'elmntrWdgtLott' => true,
				'elmntrWdgtPrmLott' => true,
				'nktrLott' => true,
				'elmntrWdgtAniHdln' => true,
				'elmntrStck' => false,
				'elmntrShe' => false,
				'elmntrStrtch' => true,
				'elmntrStckHdr' => true,
				'xooelTabs' => true,
				'phtncThmb' => true,
				'jetMobMenu' => true,
				'jetLott' => true,
				'kpPsvStls' => true,
				'diviMv' => true,
				'diviSld' => true,
				'diviMvImg' => false,
				'diviMvText' => false,
				'diviMvSld' => false,
				'diviMvFwHdr' => true,
				'diviVidBox' => false,
				'diviVidBox2' => true,
				'diviVidBg' => true,
				'diviVidFr' => false,
				'diviDsmGal' => true,
				'diviLzStls' => true,
				'diviPrld' => true,
				'diviStck' => false,
				'diviAni' => true,
				'diviDataAni' => true,
				'diviHdr' => true,
				'diviCntdwnTmr' => true,
				'brcksAni' => true,
				'kdncThAni' => true,
				'scrlSeq' => true,
				'mkImgSrcSet' => true,
				'woodmartPrcFlt' => true,
				'wooPrcFlt' => true,
				'wbwPrdFlt' => true,
				'wooOuPrdGal' => true,
				'wooGsPrdGal' => true,
				'wooJs' => true,
				'wpStrs' => true,
				'txpTagGrps' => true,
				'eaelSmpMnu' => true,
				'wprAniTxt' => true,
				'wprTabs' => true,
				'wooTabs' => true,
				'suTabs' => true,
				'upbAni' => true,
				'upbBgImg' => true,
				'upbCntVid' => true,
				'ultRspnsv' => true,
				'ultVcHd' => true,
				'ultAni' => true,
				'the7Ani' => true,
				'the7MblHdr' => true,
				'sbThAni' => true,
				'esntlsThAni' => true,
				'beThAni' => true,
				'merimagBgImg' => true,
				'mdcrLdng' => true,
				'prmmprssLzStls' => true,
				'mnmgImg' => true,
				'tldBgImg' => true,
				'jqVide' => true,
				'jqSldNivo' => true,
				'wooSctrCntDwnTmr' => true,
				'strmtbUpcTmr' => true,
				'hrrCntDwnTmr' => true,
				'lottGen' => true,
				'sprflMenu' => true,
				'jqJpPlr' => true,
				'prstPlr' => true,
				'grnshftPbAosOnceAni' => true,
				'grnshftPbAosAni' => true,
				'wooPrdGall' => true,
				'wooPrdGallAstrThmbsHeight' => true,
				'wooPrdGallFltsmThmbs' => true,
				'wooPrdGallCrftThmbs' => true,
				'wooNasaPrdGall' => true,
				'wooPrdGallSld' => true,
				'xstrThSwpr' => true,
				'bdeSwpr' => true,
				'bdeTabs' => true,
				'btCntSld' => true,
				'btMenu' => true,
				'wcs' => true,
				'tpgbAni' => true,
				'slckGen' => true,

			),

			'js' => array(

				'groupCritSpec' => false,
				'groupNonCrit' => false,
				'groupTrC' => true,

				'groupExclMdls' => true,
				'groupExcls' => array(
					'src:@stripe@',

					'src:@\\.hsforms\\.net\\W@',
					'body:@window\\.hsFormsOnReady@',
					'body:@hbspt\\.forms\\.create@',

					'src:@//cdnjs\\.cloudflare\\.com/ajax/libs/bodymovin/[\\d\\.]+/lottie\\.@',
					'src:@/plugins/zippy-form/public/js/flatpickr\\.@',
					'id:@^wd-swiper-library-js@',
					'src:@\\Wtrustedshops\\.com\\W@',
					'body:@currentScript\\s*\\.\\s*getAttribute\\(\\s*\'data-gt-widget-id\'@',

					'src:@\\.cloudflare\\.com/turnstile/@',
				),

				'min' => false,
				'minExcls' => array(
				),
				'other' => array(
					'incl' => array(

						'.//iframe[@id=\'likes-master\' and contains(@src,\'//widgets.wp.com/likes/master.html?\')]',
						'.//iframe[@onload]',

					),
				),
				'cprRem' => false,
				'optLoad' => true,
				'cplxDelay' => false,
				'preLoadEarly' => false,
				'loadFast' => false,
				'prvntDblInit' => false,
				'aniDelay' => 1000,
				'scrlDelay' => 500,

				'clk' => array(
					'delay' => 250,

					'excl' => array(
						'.//*[contains(concat(" ",normalize-space(@class)," ")," cpel-switcher__lang ")]',
						'.//*[contains(concat(" ",normalize-space(@class)," ")," cpel-switcher__lang ")]//a',

						'.//*[contains(concat(" ",normalize-space(@class)," ")," n2-ss-slider ")]//*[contains(concat(" ",normalize-space(@class)," ")," nextend-arrow ")]',
						'.//*[contains(concat(" ",normalize-space(@class)," ")," n2-ss-slider ")]//*[contains(concat(" ",normalize-space(@class)," ")," n2-bullet ")]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," woocommerce-loop-product__link ")]',

						'ifExistsThenCssSel(.//script[@id="cookieyes"],".cky-btn")',

						'.//*[@data-map-zoom]',
					),

					'exclDef' => array(
						'.//a[@href="#"]',
						'.//a[@href="#link-popup"]',
						'.//a[@onclick]',

						'.//button',

						'.//*[starts-with(@href,"#elementor-action")]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," mobile-menu ")]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," elementor-button ")][not(self::node()[contains(concat(" ",normalize-space(@class)," ")," elementor-button-link ")])]',
						'.//a[@e-action-hash]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," elementor-toggle-title ")]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," sby_video_thumbnail ")]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," ui-tabs-anchor ")]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," elementor-icon ")]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," wd-open-popup ")]',
						'.//a[starts-with(@href,"#grve-")]',

						'.//a[starts-with(@href,"#")][contains(concat(" ",normalize-space(@class)," ")," infinite-mm-menu-button ")]',
						'.//*[contains(concat(" ",normalize-space(@class)," ")," elementor-swiper-button ")]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," jet-button__instance ")]',
						'.//*[contains(concat(" ",normalize-space(@class)," ")," jet-menu-item ")]/a[contains(concat(" ",normalize-space(@class)," ")," menu-link ")]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," ajax_add_to_cart ")]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," dt-mobile-menu-icon ")]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," submit ")]',

						'.//a[@uk-toggle]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," woodmart-nav-link ")]',
						'.//*[contains(concat(" ",normalize-space(@class)," ")," wd-action-btn ")]/a',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," et_pb_video_play ")]',
						'.//*[contains(concat(" ",normalize-space(@class)," ")," et-menu ")]/li/a[starts-with(@href,"#")]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," et_pb_button ")]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," meanmenu-reveal ")]',

						'.//*[contains(concat(" ",normalize-space(@class)," ")," wpforms-icon-choices-item ")]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," wd-el-video-link ")]',

						'.//*[contains(concat(" ",normalize-space(@class)," ")," product-video-button ")]/a',

						'.//a[@data-fslightbox="gallery"]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," dvmm_button ")]',

						'click:.//div[@data-thumb]//a',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," searchOpen ")]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," bricks-button ")]',

						'.//img[contains(concat(" ",normalize-space(@class)," ")," swiper-slide-image ")]',

						'.//*[contains(concat(" ",normalize-space(@class)," ")," e-click ")]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," wd-load-more ")]',

						'click:.//presto-player | .//presto-player-js-lzl-ing || .//presto-playlist | .//presto-playlist-js-lzl-ing',
					),
				),

				'nonCrit' => array(
					'inl' => true,
					'int' => true,
					'ext' => true,
					'excl' => true,
					'items' => array(

						'body:@\\Wfunction\\s+et_core_page_resource_fallback\\W@',

						'body:@\\WTRINITY_TTS_WP_CONFIG\\W@',

						'id:@^spai_js$@',

						'src:@/depicter/@', 'body:@\\WDepicter\\W@',

						'src:@/plugins/(?:nextend-|)smart-slider@',
						'body:@(?:^|\\W)_N2\\s*\\.\\s*r\\s*\\(\\s*[\'"]documentReady[\'"]@',
						'body:@\\Wthis\\s*\\.\\s*_N2\\s*=\\s*this\\s*\\.\\s*_N2\\W@',

						'src:@\\.github\\.com@',

						'body:@window\\.jetMenuMobileWidgetRenderData@',

						'src:@\\.typekit\\.net@',
						'body:@\\WTypekit\\.load\\(@',

						'body:@\\Wdocument\\s*\\.\\s*querySelector\\s*\\(\\s*"\\.jdgm-rev-widg"\\s*\\)@',

						'body:@window\\s*\\.\\s*gtranslateSettings\\s*=@',

						'src:@trustindex\\.io/assets/js/richsnippet@',

						'src:@web\\.cmp\\.usercentrics\\.eu/@',

						'id:@^zeroy-tailwind-@',
					),

					'timeout' => array(
						'enable' => true,
						'v' => 7500,
					),
				),

				'critSpec' => array(
					'timeout' => array(
						'enable' => true,
						'v' => 0,
					),

					'items' => array(

						'src:@\\.cookiebot\\.com@',

						'id:@^cookieyes$@',

						'src:@\\.elfsight\\.com/platform/@',

						'src:@\\.elfsight\\.com/platform/@',

						'src:@/gtranslate/@',
					),
				),

				'spec' => array(
					'timeout' => array(
						'enable' => false,
						'v' => 500,
					),

					'items' => array(

						'@googletagmanager\\.com@i',
						'@(?:^|\\W)gtag\\s*\\(\\s*[\'"]@',

						'@google-analytics\\.com@i',
						'@(?:^|\\W)ga\\s*\\(\\s*[\'"]create\\W@',
						'@(?:^|\\W)ga\\s*\\(\\s*[\'"][^\'"]*\\.?send\\W@',

						'@\\Wgoogleadservices\\.com\\W@i',
						'@\\Wgooglesyndication\\.com/pagead/js/adsbygoogle\\.js(?:$|\\W)@',
						'@(?:^|\\W)window\\s*.\\s*adsbygoogle(?:$|\\W)@',

						'@\\Wgstatic\\.com\\W@',

						'id:@^wpforms-recaptcha-js-after@',
						'src:@/plugins/contact-form-7/modules/recaptcha/@',

						'@//apis\\.google\\.com/js/plusone\\.js$@',
						'@//apis\\.google\\.com/js/platform\\.js$@',

						'!body:@elementor@i & src,body:@connect\\.facebook\\.net@i',
						'@(?:^|\\W)fbq\\s*\\(\\s*[\'"]@',

						'@static\\.hotjar\\.com@i',

						'src:@\\Wtrinitymedia\\.ai/player\\W@',

						'@mc\\.yandex\\.ru/metrika@i',
						'@(?:^|\\W)ym\\s*\\(\\s*\\d@',
						'@\\Wyastatic\\.net\\W@i',
						'@\\Wcdn\\.jsdelivr\\.net/npm/yandex-metrica-watch/[\\w\\-]+\\.js@',

						'@\\Wsite\\.yandex\\.net/v[\\d\\.]+/js/all\\.js\\W@s',

						'@\\Wyandex\\.st/share/share\\.js@',

						'@\\Wapi-maps\\.yandex\\.ru/services/constructor\\W@i',
						'body:@\\Wymaps\\W@',

						'@top-fwz1\\.mail\\.ru@i',
						'@(?:^|\\W)_tmr\\s*\\.@',

						'@\\Wconnect\\.ok\\.ru/connect\\.js\\W@',

						'@\\Wlc2ads\\.ru/js/adv_out\\.js@',

						'@counter\\.yadro\\.ru/hit\\?@i',

						'@\\Wadvertur\\.ru\\W@',

						'@\\Wapi\\.content-ad\\.net\\W@',

						'@\\Wuptolike\\.com\\W@i',

						'@\\Wshare\\.pluso\\.ru\\W@',

						'@\\Wsendpulse\\.com/js/push/@',

						'@\\Wmediametrics\\.ru/partner/inject/inject\\.js@',

						'@code[\\w-]*\\.jivosite\\.com@i',

						'@\\Wtawk\\.to\\W@',

						'@\\Wgetbutton\\.io\\W@',

						'@bigreal\\.org/@i',
						'@\\.realbig\\.media/@i',

						'@\\Wtruenat\\.bid\\W@i',
						'@\\Wnewup\\.bid\\W@i',
						'@\\Wnewrrb\\.bid\\W@i',
						'@\\Wrb_ajaxurl\\W@',
						'@\\Wcontent_rb\\W@',
						'@gi\\[\'ads\'\\]\\[\'siteAdBlock\'\\]&&gi\\[\'ads\'\\]\\[\'blockAdBlock\'\\]&&gh\\[\'setAttribute\'\\]\\(\'data-ad-block\'@',

						'@\\Wgismeteo\\.ru/api/informer/getinformer/@',

						'@\\Wvk\\.com/js/api/openapi\\.js\\W@',
						'@(?:^|\\W)VK\\s*\\.\\s*Widgets\\W@s',
						'@(?:^|\\W)VK\\s*\\.\\s*init\\s*\\(@s',

						'@\\.addtoany\\.com\\W@i',

						'@\\Wtravelpayouts\\.com\\W@i',

						'src:@\\Wcalendly\\.com@',

						'@/woocommerce-gateway-stripe/assets/js/stripe(?:\\.|\\.min\\.)js\\W@',

						'@/amp\\-analytics\\-@i',

						'@\\w*\\.write\\s*\\(\\s*[\'"]<a\\s*[^>]*href\\s*=\\s*[\'"][^\'"]*www\\.liveinternet\\.ru\\W@',
						'@\\w*\\.write\\s*\\(\\s*[\'"]<img\\s*[^>]*src\\s*=\\s*[\'"][^\'"]*\\Wcounter\\.yadro\\.ru\\W@',

						'@\\Wadvertur\\.ru/v\\d+/code\\.js\\?id=\\d+@',

						'@\\.googlesyndication\\.com/pagead/show_ads\\.js$@',

						'@(?:^|\\W)amzn_assoc_placement\\s*=\\s*[\\\'"][\\w\\-]+[\\\'"]@',
						'@\\Wamazon-adsystem\\.com/widgets/onejs\\W@',

						'@\\.\\s*write\\s*\\(.+<div.+div>.+\\Wuptolike\\.com\\W@s',

						'@\\Wbeeketing\\.com\\W@',

						'@counter\\.rambler\\.ru/top100\\.jcn\\?@',

						'body:@function\\(c,h,i,m,p\\)@',

						'src:@\\.popt\\.in\\W@',

						'src:@/interactive-geo-maps/@',
						'src:@amcharts\\.com@',

						'@\\.chatbase\\.co@i',

						'src:@\\.hsforms\\.net\\W@',
						'body:@hbspt\\.forms\\.create\\(@',

						'src:@\\.visitiq\\.io@',
						'body:@vpixel\\.piximage@',

						'body:@\\WnjtWhatsApp\\W@',

						'src:@\\Wtrustedshops\\.com\\W@',

						'id:@^cloudflare-recaptcha-js$@',

					),
				),
				'skipBad' => false,
				'skips' => array(),
			),

			'css' => array(
				'corrErr' => true,
				'group' => true,
				'groupCombine' => false,
				'groupNonCrit' => true,
				'groupNonCritCombine' => false,

				'groupFont' => true,
				'groupFontCombine' => true,
				'font' => array(
					'inl' => array(
						'enable' => true,
						'items' => array(),
					),

					'deinlLrg' => true,
					'deinlLrgSize' => 512,

					'optLoadNameExpr' => '',
				),

				'sepImp' => true,
				'min' => true,
				'optLoad' => true,
				'inlAsSrc' => false,
				'inlCrit' => true,
				'inlNonCrit' => false,
				'delayNonCritWithJs' => true,
				'bfrJs' => false,
				'nonCrit' => array(
					'auto' => true,
					'autoExcls' => array(

						'@depicter@',

						'@\\.n2-ss-@',

						'@\\.slick-dots@',

						'@\\.show-mobile-header@',

						'@\\.uk-modal@',
						'@\\.uk-first-column@',
						'@\\.uk-grid-margin@',
						'@\\.uk-grid-stack@',

						'@\\.et_pb_column@',

						'@#cr_floatingtrustbadge@',

						'@(?:^|\\s)br(?:$|[\\s\\.#\\[])@',

						'@(?:^|\\s)svg(?:$|[\\s\\.#\\[])@',
					),
					'inl' => true,
					'int' => true,
					'ext' => true,
					'excl' => false,
					'items' => array(),
				),

				'fontOptLoad' => true,
				'fontOptLoadDisp' => 'swap',
				'fontCrit' => true,

				'skipBad' => true,
				'skips' => array(
					'id:@^reycore-critical-css$@',
				),

				'custom' => array(
					'0' => array( 'enable' => true, 'data' => '' ),

					'preloaders'	=> array( 'enable' => true,		'descr' => 'Preloaders',				'data' => "#pre-load, #preloader, #page_preloader, #page-preloader, #loader-wrapper, #royal_preloader, #loftloader-wrapper, #page-loading, #the7-body > #load, #loader, #loaded, #loader-container, #ocean-preloader,\r\n.rokka-loader, .page-preloader-cover, .apus-page-loading, .medizco-preloder, e-page-transition, .loadercontent, .shadepro-preloader-wrap, .tslg-screen, .page-preloader, .pre-loading, .preloader-outer, .page-loader, .martfury-preloader, body.theme-dotdigital > .preloader, .loader-wrap, .site-loader, .pix-page-loading-bg, .pix-loading-circ-path, .mesh-loader, .lqd-preloader-wrap, .rey-sitePreloader, .et-loader, .preloader-plus, .plwao-loader-wrap, .wcf-preloader {\r\n\tdisplay: none !important;\r\n}\r\n\r\nbody.royal_preloader {\r\n\tvisibility: hidden !important;\r\n}\r\n\r\n/*html body > :not(.preloader-plus) {\r\n\topacity: unset;\r\n}*/" ),

					'htmlGen'		=> array( 'enable' => true,		'descr' => 'Generic HTML',					'data' => "html, html.async-hide, body {\r\n\tdisplay: block !important;\r\n\topacity: 1 !important;\r\n\tvisibility: unset !important;\r\n}\r\n\r\nhtml body:is(.seraph-accel-js-lzl-ing, .seraph-accel-js-lzl-ing-ani) {\r\n\toverflow: auto !important;\r\n}" ),

					'jet-menu'		=> array( 'enable' => false,	'descr' => 'Jet Menu',					'data' => ".seraph-accel-js-lzl-ing ul.jet-menu > li[id^=jet-menu-item-] {\n\tdisplay: none!important;\n}" ),
					'jet-testimonials'		=> array( 'enable' => true,	'descr' => 'Jet Testimonials',	'data' => ".jet-testimonials__instance:not(.slick-initialized) .jet-testimonials__item {\r\n\tmax-width: 100%;\r\n}\r\n\r\n.jet-testimonials__instance:not(.slick-initialized) .jet-testimonials__item:nth-child(n+4) {\r\n\tdisplay: none !important;\r\n}" ),
					'xo-slider'		=> array( 'enable' => true,		'descr' => 'XO Slider',					'data' => ".xo-slider .slide-content {\n\tdisplay: unset!important;\n}" ),

					'jqSldNivo'	=> array( 'enable' => true,		'descr' => 'Nivo Slider for jQuery',	'data' => "body:is(.seraph-accel-js-lzl-ing, .seraph-accel-js-lzl-ing-ani) .nivo-caption {\r\n\topacity: 1 !important;\r\n}\r\n\r\n.nivo-caption {\r\n\tdisplay: none;\r\n}" ),

					'owl-carousel'	=> array( 'enable' => true,		'descr' => 'OWL Carousel',				'data' => ".owl-carousel:not(.wd-owl):not(.owl-loaded) {\r\n\tdisplay: block !important;\r\n\tvisibility: visible !important;\r\n}\r\n\r\n.owl-carousel:not(.wd-owl):not(.owl-loaded) > *:not(:first-child) {\r\n\tdisplay: none;\r\n}\r\n\r\n.owl-carousel:not(.wd-owl) .container.full-screen {\r\n\theight: 100vh;\r\n}" ),

					'ult-carousel'	=> array( 'enable' => true,		'descr' => 'Ultimate Carousel',			'data' => ".seraph-accel-js-lzl-ing .ult-carousel-wrapper {\n\tvisibility:initial!important;\n}\n\n.seraph-accel-js-lzl-ing .ult-carousel-wrapper .ult-item-wrap:not(:first-child) {\n\tdisplay:none;\n}" ),

					'bdt-slideshow'	=> array( 'enable' => true,		'descr' => 'Airtech Plumber Slider',	'data' => ".seraph-accel-js-lzl-ing .bdt-prime-slider-previous, .seraph-accel-js-lzl-ing .bdt-prime-slider-next {\r\n\tdisplay: none !important;\r\n}\r\n\r\n.seraph-accel-js-lzl-ing .bdt-post-slider-item:first-child {\r\n\tdisplay: unset !important;\r\n}" ),

					'n2-ss-slider'	=> array( 'enable' => false,		'descr' => 'Smart Slider',				'data' => "ss3-force-full-width, ss3-fullpage {\r\n\ttransform: none !important;\r\n\topacity: 1 !important;\r\n\twidth: var(--seraph-accel-client-width) !important;\r\n\tmargin-left: calc((100% - var(--seraph-accel-client-width)) / 2);\r\n}\r\n\r\nss3-fullpage {\r\n\theight: 100vh !important;\r\n}\r\n\r\nbody.seraph-accel-js-lzl-ing .n2-ss-align {\r\n\toverflow: visible !important;\r\n}\r\n\r\n.n2-ss-slider:not(.n2-ss-loaded):not([data-ss-carousel]) .n2-ss-slide-backgrounds [data-public-id][data-lzl-first=\"1\"],\r\n.n2-ss-slider:not(.n2-ss-loaded):not([data-ss-carousel]) [data-slide-public-id][data-lzl-first=\"1\"] {\r\n\ttransform: translate3d(0px, 0px, 0px) !important;\r\n}\r\n\r\n.n2-ss-slider:not(.n2-ss-loaded):not([data-ss-carousel]) .n2-ss-slide:not([data-slide-public-id][data-lzl-first=\"1\"]),\r\n.n2-ss-slider:not(.n2-ss-loaded) .n2-ss-layer.js-lzl-n-ing,\r\n.n2-ss-slider:not(.n2-ss-loaded):not([style*=ss-responsive-scale]) [data-responsiveposition],\r\n.n2-ss-slider:not(.n2-ss-loaded):not([style*=ss-responsive-scale]) [data-responsivesize],\r\n.n2-ss-slider.n2-ss-loaded .n2-ss-layer.js-lzl-ing {\r\n\tvisibility: hidden !important;\r\n}\r\n\r\n.n2-ss-slider:not(.n2-ss-loaded):not([data-ss-carousel]) [data-slide-public-id][data-lzl-first=\"1\"] .n2-ss-layers-container,\r\n.n2-ss-slider:not(.n2-ss-loaded):not([data-ss-carousel]) .n2-ss-slide-backgrounds [data-public-id][data-lzl-first=\"1\"],\r\n.n2-ss-slider:not(.n2-ss-loaded) .n2-ss-slider-controls-advanced {\r\n\topacity: 1 !important;\r\n}\r\n\r\n.n2-ss-slider[data-ss-carousel]:not(.n2-ss-loaded) .n2-ss-layers-container {\r\n\topacity: 1 !important;\r\n\tvisibility: visible !important;\r\n}\r\n\r\n.n2-ss-slider-pane {\r\n\topacity: 1 !important;\r\n\tanimation-name: none !important;\r\n\t--self-side-margin: auto !important;\r\n\t--slide-width: 100% !important;\r\n}\r\n\r\n/*.n2-ss-showcase-slides:not(.n2-ss-showcase-slides--ready) {\r\n\topacity: 1 !important;\r\n\ttransform: none !important;\r\n}*/" ),

					'wp-block-ultimate-post-slider'	=> array( 'enable' => true,		'descr' => 'Block Ultimate Post Slider',	'data' => "[class*=wp-block-ultimate-post-post-slider] .ultp-block-items-wrap:not(.slick-initialized) > .ultp-block-item:not(:first-child)\n{\n\tdisplay: none!important;\n}" ),

					'elementor-vis'		=> array( 'enable' => false, 'descr' => 'Elementor (visibility and animation)', 'data' => "body.seraph-accel-js-lzl-ing-ani .elementor-invisible {\r\n\tvisibility: visible !important;\r\n}\r\n\r\n.elementor-element[data-settings*=\"animation\\\"\"] {\r\n\tanimation-name: none !important;\r\n}" ),

					'elementor'		=> array( 'enable' => true, 'descr' => 'Elementor', 'data' => ".vc_row[data-vc-full-width] {\r\n\tposition: relative;\r\n\twidth: var(--seraph-accel-client-width) !important;\r\n}\r\n\r\nhtml:not([dir=rtl]) .vc_row[data-vc-full-width] {\r\n\tleft: calc((100% - var(--seraph-accel-client-width)) / 2) !important;\r\n\tmargin-left: 0 !important;\r\n}\r\n\r\nhtml[dir=rtl] .vc_row[data-vc-full-width] {\r\n\tright: calc((100% - var(--seraph-accel-client-width)) / 2) !important;\r\n\tmargin-right: 0 !important;\r\n}\r\n\r\n.vc_row.wpb_row[data-vc-full-width]:not([data-vc-stretch-content=\"true\"]), .vc_row.mpc-row[data-vc-full-width]:not([data-vc-stretch-content=\"true\"]) {\r\n\t--pdd: calc((var(--seraph-accel-client-width) - (100% + 2*15px)) / 2);\r\n\tpadding-left: var(--pdd) !important;\r\n\tpadding-right: var(--pdd) !important;\r\n}\r\n\r\n.elementor-top-section.elementor-section-stretched[data-settings*=\"section-stretched\"] {\r\n\twidth: var(--seraph-accel-client-width) !important;\r\n}\r\n\r\nhtml:not([dir=rtl]) .elementor-top-section.elementor-section-stretched[data-settings*=\"section-stretched\"] {\r\n\tleft: calc(-1 * var(--lzl-strtch-offs-x)) !important;\r\n}\r\n\r\nhtml[dir=rtl] .elementor-top-section.elementor-section-stretched[data-settings*=\"section-stretched\"] {\r\n\tright: calc(-1 * var(--lzl-strtch-offs-x)) !important;\r\n}\r\n\r\nbody.seraph-accel-js-lzl-ing-ani .elementor-headline-dynamic-text.elementor-headline-text-active {\r\n\topacity: 1;\r\n}" ),

					'et'			=> array( 'enable' => true,		'descr' => 'Divi',						'data' => ".et_animated:not(.et_pb_sticky_placeholder,.dani-lzl) {\r\n\topacity: 1 !important;\r\n}\r\n\r\n.et_pb_section_video_bg > video {\r\n\theight: 100%;\r\n}\r\n\r\n.et_pb_preload .et_pb_section_video_bg, .et_pb_preload > div {\r\n\tvisibility: visible !important;\r\n}\r\n\r\nbody:is(.seraph-accel-js-lzl-ing, .seraph-accel-js-lzl-ing-ani) .et_pb_gallery_grid .et_pb_gallery_item {\r\n\tdisplay: block !important;\r\n}\r\n\r\n/* Slider */\r\n/*.et_pb_slider:not([data-active-slide]) {\r\n\theight: 1px;\r\n}*/\r\n\r\n.et_pb_slider:not([data-active-slide]) .et_pb_slides,\r\n.et_pb_slider:not([data-active-slide]) .et_pb_slide:first-child,\r\n.et_pb_slider:not([data-active-slide]) .et_pb_slide:first-child .et_pb_container {\r\n\theight: 100%;\r\n}" ),

					'tag-div'		=> array( 'enable' => true,		'descr' => 'tagDiv',					'data' => "body.td-animation-stack-type0 .td-animation-stack .entry-thumb,\nbody.td-animation-stack-type0 .post img:not(.woocommerce-product-gallery img):not(.rs-pzimg),\nbody.td-animation-stack-type0 .td-animation-stack .td-lazy-img,\n.tdb_header_menu .tdb-menu-items-pulldown.tdb-menu-items-pulldown-inactive {\n\topacity: 1!important;\n}" ),
					'photonic-thumb'	=> array( 'enable' => true,		'descr' => 'Photonic Photo Gallery',	'data' => ".photonic-thumb,\r\n.photonic-thumb a img {\r\n\tdisplay: unset !important;\r\n}\r\n\r\n.photonic-loading {\r\n\tdisplay: none !important;\r\n}\r\n\r\n.photonic-stream * {\r\n\tanimation-name: none !important;\r\n}" ),
					'avia-slideshow'	=> array( 'enable' => true,		'descr' => 'Avia Slideshow',		'data' => ".avia-slideshow.av-default-height-applied .avia-slideshow-inner > li:first-child {\r\n\topacity: 1 !important;\r\n\tvisibility: visible !important;\r\n}\r\n" ),

					'rev-slider'		=> array( 'enable' => true,		'descr' => 'Revolution Slider',		'data' => "rs-module-wrap:has(rs-module[data-lzl-layout=\"fullwidth\"]), rs-module-wrap:has(rs-module[data-lzl-layout=\"fullscreen\"]), rs-fullwidth-wrap, rs-fullwidth-wrap > rs-module-wrap {\r\n\twidth: var(--seraph-accel-client-width) !important;\r\n\tleft: calc((100% - var(--seraph-accel-client-width)) / 2) !important;\r\n}\r\n\r\nrs-module[data-lzl-layout=\"fullscreen\"] {\r\n\theight: calc(var(--seraph-accel-dvh) - var(--lzl-rs-offs-y)) !important;\r\n}\r\n\r\nrs-module[data-lzl-layout=\"fullscreen\"].js-lzl-ing {\r\n\tmargin-top: calc(-1*var(--seraph-accel-dvh) + var(--lzl-rs-offs-y)) !important;\r\n}" ),

					'fusion-vis'		=> array( 'enable' => false,	'descr' => 'Fusion (visibility and animation)',					'data' => ".fusion-animated {\n\tvisibility: visible;\n}\n" ),
					'fusion-menu'		=> array( 'enable' => true,		'descr' => 'Fusion Menu',					'data' => ".fusion-menu-element-wrapper.loading {\n\topacity: 1;\n}\n\n@media (max-width: 1024px) {\n\t.fusion-menu-element-wrapper.loading .fusion-menu {\n\t\tdisplay: none;\n\t}\n\n\t.fusion-menu-element-wrapper.loading button {\n\t\tdisplay: block !important;\n\t}\n\n\t.fusion-menu-element-wrapper.loading {\n\t\tdisplay: flex;\n\t}\n}" ),
					'jnews'			=> array( 'enable' => true,		'descr' => 'JNews Theme',					'data' => ".thumbnail-container.animate-lazy > img {\n\topacity: 1!important;\n}" ),
					'grve'			=> array( 'enable' => true,		'descr' => 'GROVE Theme',					'data' => ".grve-bg-image {\r\n\topacity: 1 !important;\r\n}\r\n\r\nbody.seraph-accel-js-lzl-ing-ani .grve-animated-item {\r\n\tanimation-fill-mode: both;\r\n\tanimation-duration: .8s;\r\n}\r\n\r\nbody.seraph-accel-js-lzl-ing-ani .grve-fade-in-left {\r\n\tanimation-name: grve_fade_in_left;\r\n}\r\n\r\nbody.seraph-accel-js-lzl-ing-ani .grve-fade-in {\r\n\tanimation-name: grve_fade_in;\r\n}\r\n\r\nbody.seraph-accel-js-lzl-ing-ani .grve-fade-in-up {\r\n\tanimation-name: grve_fade_in_up;\r\n}\r\n" ),

					'wpb'			=> array( 'enable' => true,		'descr' => 'WPBakery',					'data' => ".upb_row_bg[data-bg-override=\"browser_size\"],\r\n.upb_row_bg[data-bg-override*=\"full\"],\r\n.ult-vc-seperator[data-full-width=\"true\"] {\r\n\twidth: var(--seraph-accel-client-width) !important;\r\n}\r\n\r\n.ult-vc-seperator[data-full-width=\"true\"] .ult-main-seperator-inner {\r\n\twidth: 100% !important;\r\n\tmargin-left: 0 !important;\r\n\tmargin-right: 0 !important;\r\n}\r\n\r\nhtml:not([dir=rtl]) .upb_row_bg[data-bg-override=\"browser_size\"],\r\nhtml:not([dir=rtl]) .upb_row_bg[data-bg-override*=\"full\"],\r\nhtml:not([dir=rtl]) .ult-vc-seperator[data-full-width=\"true\"] {\r\n\tmargin-left: calc((100% - var(--seraph-accel-client-width)) / 2) !important;\r\n\tleft: 0 !important;\r\n}\r\n\r\nhtml[dir=rtl] .upb_row_bg[data-bg-override=\"browser_size\"],\r\nhtml[dir=rtl] .upb_row_bg[data-bg-override*=\"full\"],\r\nhtml[dir=rtl] .ult-vc-seperator[data-full-width=\"true\"] {\r\n\tmargin-right: calc((100% - var(--seraph-accel-client-width)) / 2) !important;\r\n\tright: 0 !important;\r\n}" ),

					'tm'			=> array( 'enable' => true,		'descr' => 'Yoo Theme',					'data' => ".tm-header-placeholder {\r\n\theight: calc(1px*var(--uk-header-placeholder-cy));\r\n}\r\n" ),

					'packery'		=> array( 'enable' => true,		'descr' => 'Packery',					'data' => "[data-packery-options].row.row-grid > .col:not([style*=\"position\"]),\r\n[data-packery-options].row.row-masonry > .col:not([style*=\"position\"]) {\r\n\tfloat: unset;\r\n\tdisplay: inline-block !important;\r\n\tvertical-align: top;\r\n}" ),

					'theme-xstore'		=> array( 'enable' => true,		'descr' => 'XStore theme',		'data' => "body.wp-theme-xstore.elementor-default:not([data-elementor-device-mode]) {\r\n\t--etheme-element-loading-opacity: 1;\r\n\t--etheme-element-loading-visibility:visible;\r\n\t--etheme-element-loader-display:none;\r\n}" ),

					'theme-bt'		=> array( 'enable' => true,		'descr' => 'BoldThemes',		'data' => "body:is(.seraph-accel-js-lzl-ing, .seraph-accel-js-lzl-ing-ani) .bt_bb_animation_fade_in {\r\n\topacity: 1;\r\n}\r\n\r\nbody:is(.seraph-accel-js-lzl-ing, .seraph-accel-js-lzl-ing-ani) .btSearch .btSearchInner.gutter {\r\n\tdisplay: none;\r\n}" ),

					'cookie-law-info'		=> array( 'enable' => true,		'descr' => 'CookieYes',					'data' => ".cky-consent-container.cky-hide ~ .cky-consent-container {\r\n\tdisplay: none;\r\n}" ),
				),
			),

			'cdn' => array(

				'items' => array(
					array(
						'enable' => true,
						'sa' => true,
						'addr' => '',
						'types' => array( 'js', 'css', 'less', 'gif', 'jpeg', 'jpg', 'bmp', 'png', 'svg', 'webp', 'avif', 'eot', 'aac', 'mp3', 'mp4', 'ogg', 'pdf', 'docx', 'otf', 'ttf', 'woff', 'woff2' ),
						'uris' => array( 'wp-content', 'wp-includes' ),
						'urisExcl' => array(),
					),
				),
			),

			'grps' => array(

				'items' => array(

					'home' => array(
						'enable' => 0,
						'name' => 'Home',
						'urisIncl' => array(),
						'argsIncl' => array(),
						'patterns' => array( './/body[contains(concat(" ",normalize-space(@class)," ")," home ")]' ),
						'views' => array(),
						'sklExcl' => array(),
						'contPr' => array(
							'enable' => true,
							'jsOvr' => true,
							'js' => array(
								'optLoad' => true,
								'nonCrit' => array( 'timeout' => array( 'enable' => false, 'v' => 0 ), 'inl' => true, 'int' => true, 'ext' => true, 'excl' => true, 'items' => array(), ),
								'spec' => array( 'timeout' => array( 'enable' => false, 'v' => 7500, ) ),
							),
							'jsNonCritScopeOvr' => false,
							'cssOvr'=> false,
							'css'=> array( 'nonCrit'=> array( 'auto'=> false ) ),
						)
					),

					'@a' => array(
						'enable' => 2,
						'name' => 'Common (advanced)',
						'urisIncl' => array(),
						'argsIncl' => array(),
						'patterns' => array(
							'.//body[match(concat(" ",normalize-space(@class)," "),"@\\s(page)\\s@")][match(concat(" ",normalize-space(@class)," "),"@\\s(parent)-pageid-(\\d+)\\s@")]',
							'.//body[match(concat(" ",normalize-space(@class)," "),"@\\s(page)\\s@")][match(concat(" ",normalize-space(@class)," "),"@\\spage-id-(\\d+)\\s@")]',
							'.//body[match(concat(" ",normalize-space(@class)," "),"@\\s(single)\\s@")][match(concat(" ",normalize-space(@class)," "),"@\\ssingle-([\\w\\-]+)\\s@")]',
							'.//body[match(concat(" ",normalize-space(@class)," "),"@\\s(archive)\\s@")][match(concat(" ",normalize-space(@class)," "),"@\\s(post)-type-archive-([\\w\\-]+)\\s@")]',
							'.//body[match(concat(" ",normalize-space(@class)," "),"@\\s(archive)\\s@")][match(concat(" ",normalize-space(@class)," "),"@\\s(tax)-([\\w\\-]+)\\s@")]',
							'.//body[match(concat(" ",normalize-space(@class)," "),"@\\s(archive)\\s@")][match(concat(" ",normalize-space(@class)," "),"@\\s(tag)\\s@")]',
							'.//body[match(concat(" ",normalize-space(@class)," "),"@\\s(archive)\\s@")][match(concat(" ",normalize-space(@class)," "),"@\\s(date)\\s@")]',
							'.//body[match(concat(" ",normalize-space(@class)," "),"@\\s(home)\\s@")]',
							'.//body[match(concat(" ",normalize-space(@class)," "),"@\\s(blog)\\s@")]',
						),
						'views' => array(),

						'sklSrch' => 'a',

						'sklExcl' => array(

							'.//script', './/style', './/link', './/head',
							'.//br', './/svg//*',

						),

						'sklCssSelExcl' => array(
							"r=pslg:@\\.(?:[\\-\\w]+[\\-\\_]|)((?'POST_SLUG'))[\\-\\_\\W]@i",
							"@#([\\w\\-\\%]+)@",

							"@\\.(?:[\\-\\w]+[\\-\\_]|)(?:category|categories|tag|term|comment-author|(?'ENUM_TAXONOMIES_NOTBUILTIN'))[\\-\\_]([\\w\\-]+)@i",
							"r=txnm:@\\.(?:[\\-\\w]+[\\-\\_]|)(category|(?'ENUM_TAXONOMIES_NOTBUILTIN'))[\\-\\_\\W]@i",
							"@\\.(?:[\\-\\w]+[\\-\\_]|)(?:post|page|attachment|(?'ENUM_POSTTYPES_NOTBUILTINVIEWABLESPEC'))[\\-\\_]([\\w\\-]+)@i",

							"@[^[:alnum:]]eb-(?:row|column|text|accordion(?:-item|))-([[:alnum:]]+)[^[:alnum:]\\-_]@i",

							"@[\\.#][\\w\\-\\:\\@\\\\]*[\\-_]([\\da-f]+)[\\W_]@i",

						),
					),

					'@' => array(
						'enable' => 2,
						'name' => 'Common',
						'urisIncl' => array(),
						'argsIncl' => array(),
						'patterns' => array(),
						'views' => array(),

						'sklSrch' => true,

						'sklExcl' => array(

							'.//script', './/style', './/link', './/head',
							'.//br',
							'.//svg[@width="0"][@height="0"]',
						),

						'sklCssSelExcl' => array(
							'@\\.(?:product_cat|product_tag|category|tag|term|pa|woocommerce-product-attributes-item--attribute|comment-author)[\\-_]([\\w\\-]+)@i',
							'@[\\.#][\\w\\-]*[\\-_]([\\da-f]+)[\\W_]@i',
						),
					),

					'desktop' => array(
						'enable' => 0,
						'name' => 'Desktop',
						'urisIncl' => array(),
						'argsIncl' => array(),
						'patterns' => array(),
						'views' => array( 'cmn' ),
						'sklSrch' => false,
						'sklExcl' => array(),
						'sklCssSelExcl' => array(),
						'contPr' => array(
							'enable' => true,
							'jsOvr' => true,
							'js' => array(
								'optLoad' => true,
								'nonCrit' => array( 'timeout' => array( 'enable' => true, 'v' => 7500 ), 'inl' => true, 'int' => true, 'ext' => true, 'excl' => true, 'items' => array(), ),
								'spec' => array( 'timeout' => array( 'enable' => false, 'v' => 7500, ) ),
							),
							'jsNonCritScopeOvr' => false,
							'cssOvr'=> false,
							'css'=> array( 'nonCrit'=> array( 'auto'=> false ) ),
						)
					),

				),
			),
		),

		'bots' => array(
			'agents' => array(
				'@\\Wcompatible\\W@i',
				'facebookexternalhit',
				'go-http-client',
				'google-adwords-instant',
				'adsbot-google',
				'googlebot',
				'googleyoutube',
				'ioncrawl',
				'chrome-lighthouse',
				'gtmetrix',
				'rankmathapi',
				'validator.w3.org',
				'zoominfobot',
				'freshpingbot',
				'wordpress/',
				'applebot/',
				'python-requests/',
				'slackbot',
				'uptimemonitor',
				'crawler_eb',
				'@\\s+web\\s+spider\\W@i',
				'dnbcrawler',
				'stormcrawler',
				'@df\\s+bot@',
				'webprosbot',
				'researchoftheweb',
				'siteanalyzerbot',
				'@2ip\\s+bot@',
				'ahrefs',
				'mj12bot',
				'bsbot',
				'okhttp',
				'phxbot',
				'sansanbot',
				'scrapy',
				'researchscan',
			),
		),

		'test' => array(
			'optDelay' => false,
			'optDelayTimeout' => 15000,
			'contDelay' => false,
			'contDelayTimeout' => 5000,
			'contExtra' => false,
			'contExtraSize' => 0x80000,
		),

		'hdrTrace' => false,
		'debugInfo' => false,
		'debug' => false,
		'emojiIcons' => false,

		'log' => false,
		'logScope' => array(
			'upd' => false,
			'srvClr' => false,
			'request' => false,
			'requestSkipped' => true,
			'requestSkippedAdmin' => true,
			'requestBots' => true,
		),

		'asyncUseCron' => true,
		'asyncMode' => '',
		'asyncUseCmptNbr' => false,
		'asyncSmpOpt' => true,
	) );
}

function GetSiteId( $site = null )
{
	if( !is_multisite() )
		return( 'm' );

	if( !$site )
	{
		$site = get_current_site();
		$site = new AnyObj( array( 'blog_id' => get_current_blog_id(), 'site_id' => $site -> site_id ) );
	}

	if( defined( 'SITE_ID_CURRENT_SITE' ) && defined( 'BLOG_ID_CURRENT_SITE' ) && $site -> blog_id == BLOG_ID_CURRENT_SITE && $site -> site_id == SITE_ID_CURRENT_SITE )
		return( 'm' );

	return( '' . $site -> site_id . '_' . $site -> blog_id );
}

function GetBlogIdFromSiteId( $siteId )
{
	if( $siteId === 'm' )
		return( Gen::Constant( 'BLOG_ID_CURRENT_SITE', 0 ) );

	$nPos = strpos( $siteId, '_' );
	if( $nPos === false )
		return( false );

	return( ( int )substr( $siteId, $nPos + 1 ) );
}

function GetCacheDir()
{
	return( defined( 'SERAPH_ACCEL_CACHE_DIR' ) ? SERAPH_ACCEL_CACHE_DIR : ( WP_CONTENT_DIR . '/cache/seraphinite-accelerator' ) );
}

function GetCacheDataDir( $siteCacheRootPath )
{
	return( $siteCacheRootPath . '/d' );
}

function GetCacheViewsDir( $siteCacheRootPath, $siteSubId = null )
{
	$siteCacheRootPath .= '/v';
	if( $siteSubId )
		$siteCacheRootPath .= '-' . $siteSubId;
	return( $siteCacheRootPath );
}

function GetSalt()
{
	if( defined( 'SERAPH_ACCEL_SALT' ) )
		return( SERAPH_ACCEL_SALT );
	if( defined( 'NONCE_SALT' ) )
		return( NONCE_SALT );
	return( '' );
}

function _GetCacheCurUserSessionHash( $sessionId, $userSessionId, $userId, $expiration )
{
	return( hash_hmac( function_exists( 'hash' ) ? 'sha256' : 'sha1', $sessionId . $userSessionId . $userId . $expiration, AUTH_KEY ) );
}

function GetCacheCurUserSession( $siteId, $defForce = false )
{
	$sessInfoDef = array( 'userId' => '0' );

	if( $defForce )
		return( $sessInfoDef );

	global $seraph_accel_g_sessInfo;

	if( is_array( $seraph_accel_g_sessInfo ) )
		return( $seraph_accel_g_sessInfo );

	$secure = is_ssl();

	$cookieName = ( $secure ? '__Secure-' : '' ) . 'wp_seraph_accel_sess_' . $siteId;
	$cookie = Gen::SanitizeTextData( ($_COOKIE[ $cookieName ]??null) );
	if( empty( $cookie ) && $secure )
	{
		$cookieName = 'wp_seraph_accel_sess_' . $siteId;
		$cookie = Gen::SanitizeTextData( ($_COOKIE[ $cookieName ]??null) );
	}

	if( empty( $cookie ) )
	{

		return( $seraph_accel_g_sessInfo = $sessInfoDef );
	}

	$cookie_elements = explode( '|', $cookie );
	if( count( $cookie_elements ) !== 5 )
	{

		return( $seraph_accel_g_sessInfo = $sessInfoDef );
	}

	list( $userSessionId, $sessionId, $userId, $expiration, $hmac ) = $cookie_elements;

	if( $expiration && time() > $expiration )
	{

		return( $seraph_accel_g_sessInfo = $sessInfoDef );
	}

	$hmacCheck = _GetCacheCurUserSessionHash( $sessionId, $userSessionId, $userId, $expiration );
	if( $hmac !== $hmacCheck )
	{

		return( $seraph_accel_g_sessInfo = $sessInfoDef );
	}

	return( $seraph_accel_g_sessInfo = array( 'sessId' => $sessionId, 'userSessId' => $userSessionId, 'userId' => $userId, 'expiration' => $expiration ) );
}

function ShouldCurUserSessionExist()
{
	foreach( $_COOKIE as $cookKey => $cookVal )
	{
		if( strpos( $cookKey, 'wordpress_logged_in_' ) === 0 )
			return( true );

	}

	return( false );
}

function SetCacheCurUserSession( $siteId, $sessionId, $userSessionId, $userId, $expiration )
{
	if( headers_sent() )
		return;

	global $seraph_accel_g_sessInfo;

	$hmac = _GetCacheCurUserSessionHash( $sessionId, $userSessionId, $userId, $expiration );
	$secure = is_ssl();
	Net::SetCookie( ( $secure ? '__Secure-' : '' ) . 'wp_seraph_accel_sess_' . $siteId, $userSessionId . '|' . $sessionId . '|' . $userId . '|' . $expiration . '|' . $hmac, array( 'expires' => Gen::GetCurRequestTime() + YEAR_IN_SECONDS, 'path' => COOKIEPATH, 'domain' => COOKIE_DOMAIN, 'secure' => $secure, 'httponly' => true, 'samesite' => 'Strict' ) );

	$seraph_accel_g_sessInfo = array( 'sessId' => $sessionId, 'userSessId' => $userSessionId, 'userId' => $userId, 'expiration' => $expiration );
}

function _GetDataFileComprExt( $compr, $composite = true )
{

	switch( $compr )
	{
	case 'deflate':
		return( $composite ? '.deflu' : '.gz' );

	case 'brotli':

		return( '.br' );
	}

	return( '' );
}

function _GetDataFileEncExt( $encoding, $composite = true )
{

	switch( $encoding )
	{
	case 'gzip':
	case 'deflate':
	case 'compress':
		return( $composite ? '.deflu' : '.gz' );

	case 'br':

		return( '.br' );
	}

	return( '' );
}

function GetCacheCh( $oiCi, $binary = false )
{
	$res = Gen::GetFileName( $oiCi, true );
	return( $binary ? hex2bin( $res ) : $res );
}

function GetCacheCos( $oiCi )
{
	return( @intval( Gen::GetFileExt( $oiCi ), 16 ) );
}

function _CacheCompDf( $data, $level = -1 )
{

	$ctx = deflate_init( ZLIB_ENCODING_RAW, array( 'level' => $level ) );
	if( $ctx === false )
		return( false );

	$data = deflate_add( $ctx, $data, ZLIB_FULL_FLUSH );
	if( $data === false )
		return( false );

	$blockLast = deflate_add( $ctx, '', ZLIB_FINISH );
	if( $blockLast !== "\x03\0" )
		return( false );

	return( $data );
}

function _CacheCompBr( $data, $level = 11 )
{

	$ctx = Gen::CallFunc( 'brotli_compress_init', array( $level, BROTLI_GENERIC ), false );
	if( !$ctx )
		return( false );

	$blockFirst = brotli_compress_add( $ctx, '', 1 );
	if( $blockFirst !== "\x6b\x00" )
		return( false );

	$data = brotli_compress_add( $ctx, $data, 1 );
	if( $data === false )
		return( false );

	$blockLast = brotli_compress_add( $ctx, '', BROTLI_FINISH );
	if( $blockLast !== "\x03" )
		return( false );

	return( $data );
}

function IsBrotliAvailable()
{
	return( function_exists( 'brotli_compress_init' ) );
}

function CacheCvs( $sz, $szOrig )
{
	return( $sz !== false && ( ( $sz === 0 && $szOrig === 0 ) || ( $sz !== 0 && $szOrig !== 0 ) ) );
}

function IsSrvNotSupportGzAssets()
{

	return( preg_match( '@litespeed@i', ($_SERVER[ 'SERVER_SOFTWARE' ]??'') ) );
}

function CacheCgf( $settCache, $dataPath, $oiCi, $fileExt, $dataFileExt = '' )
{
	return( $dataPath . '/' . CacheCgif( $settCache, $oiCi ) . '.' . $fileExt . $dataFileExt );
}

function UseGzAssets( $settCache )
{
	return( Gen::GetArrField( $settCache, array( 'useDataComprAssets' ), false ) && !IsSrvNotSupportGzAssets() );
}

function CacheCgif( $settCache, $oiCi )
{
	$oiCif = '';

	$nLvlTotal = 0;
	foreach( Gen::GetArrField( $settCache, array( 'dataLvl' ), array() ) as $nLvl )
	{
		$oiCif .= substr( $oiCi, $nLvlTotal, $nLvl ) . '/';
		$nLvlTotal += $nLvl;
	}

	$oiCif .= substr( $oiCi, $nLvlTotal );
	return( $oiCif );
}

function CacheCw( $settCache, $siteRootDataPath, $dataPath, $composite, $content, $type, $fileExt = null )
{
	if( !$fileExt )
		$fileExt = $type;

	$oiCi = @md5( $content ) . '.' . sprintf( '%x', strlen( $content ) );
	$oiCif = CacheCgif( $settCache, $oiCi );

	$dataComprs = Gen::GetArrField( $settCache, array( 'dataCompr' ), array() );
	if( empty( $dataComprs ) )
		$dataComprs[] = '';

	if( $type != 'html' )
	{
		$dataPath .= '/' . $type;
		if( !$composite )
		{
			if( ( $type != 'css' && $type != 'js' ) || !UseGzAssets( $settCache ) )
				$dataComprs = array( '' );
			else if( !in_array( '', $dataComprs, true ) )
				$dataComprs[] = '';
		}
	}

	$writeOk = true;
	$writeFailDsc = null;

	foreach( $dataComprs as $dataCompr )
	{
		$dataFileExt = _GetDataFileComprExt( $dataCompr, $composite );
		if( $dataFileExt === null )
		{
			$writeOk = false;
			Gen::LastErrDsc_Set( LocId::Pack( 'DataComprUnsupp_%1$s', null, array( $dataCompr ) ) );
			break;
		}

		$oiCf = $dataPath . '/' . $oiCif . '.' . $fileExt . $dataFileExt;

		$lock = new Lock( 'dtl', dirname( $dataPath ) );
		if( !$lock -> Acquire() )
		{
			$writeOk = false;
			Gen::LastErrDsc_Set( $lock -> GetErrDescr() );
			break;
		}

		if( CacheCvs( Gen::FileSize( $oiCf ), strlen( $content ) ) )
		{
			@touch( $oiCf );
			$lock -> Release();
			continue;
		}

		$oiCfTmp = $oiCf . '.tmp';
		Gen::FileOpenWithMakeDir( $chunkFileStm, $oiCfTmp, 'wb' );
		if( $chunkFileStm )
		{
			{
				$contentWrite = false;
				switch( $dataCompr )
				{
				case '':
					$contentWrite = $content;
					break;

				case 'deflate':
					$contentWrite = ( $dataFileExt == '.gz' ) ? @gzencode( $content, 9 ) : _CacheCompDf( $content, 9 );
					break;

				case 'brotli':
					$contentWrite = ( $dataFileExt == '.br' ) ? Gen::CallFunc( 'brotli_compress', array( $content, 11 ), false ) : _CacheCompBr( $content, 11 );
					break;
				}

				if( $contentWrite === false || $contentWrite === null )
				{
					$writeOk = false;
					Gen::LastErrDsc_Set( LocId::Pack( 'DataComprErr_%1$s', null, array( $dataCompr ) ) );
				}
				else if( @fwrite( $chunkFileStm, $contentWrite ) === false )
				{
					$writeOk = false;
					Gen::LastErrDsc_Set( LocId::Pack( 'FileWriteErr_%1$s', 'Common', array( $oiCfTmp ) ) );
				}

				unset( $contentWrite );
			}

			@fclose( $chunkFileStm );

			if( $writeOk && !@rename( $oiCfTmp, $oiCf ) )
			{
				$writeOk = false;
				Gen::LastErrDsc_Set( LocId::Pack( 'FileRenameErr_%1$s%2$s', 'Common', array( $oiCfTmp, $oiCf ) ) );
			}
		}
		else
		{
			$writeOk = false;
			Gen::LastErrDsc_Set( LocId::Pack( 'FileWriteErr_%1$s', 'Common', array( $oiCfTmp ) ) );
		}

		if( !$writeOk )
		{
			@unlink( $oiCfTmp );
			@unlink( $oiCf );
			$lock -> Release();
			break;
		}

		$lock -> Release();
	}

	if( !$writeOk )
		return( null );

	if( $siteRootDataPath !== null )
		$relFilePath = substr( $dataPath, strlen( $siteRootDataPath ) + 1 ) . '/';
	else
		$relFilePath = '';
	$relFilePath .= $oiCif . '.' . $fileExt;

	return( array( 'id' => $oiCi, 'relFilePath' => $relFilePath ) );
}

function CacheCc( $settCache, $siteRootDataPath, $dataPath, $oiCi, $type, $fileExt = null )
{
	if( !$fileExt )
		$fileExt = $type;

	$oiCif = CacheCgif( $settCache, $oiCi );

	if( $type != 'html' )
		$dataPath .= '/' . $type;

	$oiCf = $dataPath . '/' . $oiCif . '.' . $fileExt;

	$lock = new Lock( 'dtl', dirname( $dataPath ) );
	if( !$lock -> Acquire() )
	{
		Gen::LastErrDsc_Set( $lock -> GetErrDescr() );
		return( null );
	}

	$readOk = @file_exists( $oiCf );

	$lock -> Release();

	if( !$readOk )
		return( null );

	if( $siteRootDataPath !== null )
		$relFilePath = substr( $dataPath, strlen( $siteRootDataPath ) + 1 ) . '/';
	else
		$relFilePath = '';
	$relFilePath .= $oiCif . '.' . $fileExt;

	return( array( 'id' => $oiCi, 'relFilePath' => $relFilePath ) );
}

function _ContentCw( &$dsc, $data, $type, $settCache, $dataPath )
{
	if( !$data )
		return( true );

	$oiC = CacheCw( $settCache, null, $dataPath, true, $data, $type );
	if( !$oiC )
		return( false );

	$dsc[ 'p' ][] = $oiC[ 'id' ];
	return( $oiC[ 'id' ] );
}

function ReadSce( $dataPath, $settCache, $id, $type )
{
	$dataComprs = Gen::GetArrField( $settCache, array( 'dataCompr' ), array() );
	if( empty( $dataComprs ) )
		$dataComprs[] = '';

	if( $type != 'html' )
	{
		if( $type != 'css' && $type != 'js' )
			$dataComprs = array( '' );
		else if( !in_array( '', $dataComprs, true ) )
			$dataComprs[] = '';
	}

	$oiCf = _GetCcf( $settCache, $id, '', $dataPath, time(), $type, $dataComprs );
	if( !$oiCf )
		return( null );

	$oiCd = _GetCfc( $oiCf );
	if( $oiCd === false || !CacheCvs( strlen( $oiCd ), GetCacheCos( $id ) ) )
		return( null );

	switch( $oiCf[ 'fmt' ] )
	{
	case '.gz':				$oiCd = @gzdecode( $oiCd ); break;
	case '.deflu':		$oiCd = @gzinflate( $oiCd . "\x03\0" ); break;
	case '.br':				$oiCd = Gen::CallFunc( 'brotli_uncompress', array( $oiCd ), false ); break;
	case '.brua':		$oiCd = Gen::CallFunc( 'brotli_uncompress', array( "\x6b\x00" . $oiCd . "\x03" ), false ); break;
	}

	if( $oiCd === false )
		return( null );

	return( $oiCd );
}

function iadkxsejfuijdujdes_( &$content, $type, &$dsc, $contSep, $contSepLen, $settCache, $dataPath )
{
	$writeOk = true;

	$contentChunkOffset = 0;
	$contentChunkSearchOffset = 0;
	$contentChunkWrite = '';

	for( ;; )
	{
		$contentChunkOffsetNext = strpos( $content, $contSep, $contentChunkSearchOffset );

		if( $contentChunkOffsetNext === false )
		{
			if( !$contentChunkOffset )
				$contentChunk = $content;
			else
				$contentChunk = substr( $content, $contentChunkOffset );
		}
		else
		{
			$content = substr( $content, 0, $contentChunkOffsetNext ) . substr( $content, $contentChunkOffsetNext + $contSepLen );

			$contentChunk = substr( $content, $contentChunkOffset, $contentChunkOffsetNext - $contentChunkOffset );
			$contentChunkSearchOffset = $contentChunkOffsetNext;

			$contentChunkOffset = $contentChunkOffsetNext;
		}

		if( strlen( $contentChunkWrite ) >= 2048 || strlen( $contentChunk ) >= 2048 )
		{
			$writeOk = !!_ContentCw( $dsc, $contentChunkWrite, $type, $settCache, $dataPath );
			$contentChunkWrite = '';
		}

		$contentChunkWrite .= $contentChunk;
		$contentChunk = null;

		if( $contentChunkOffsetNext === false || !$writeOk )
			break;
	}

	if( $writeOk )
		$writeOk = !!_ContentCw( $dsc, $contentChunkWrite, $type, $settCache, $dataPath );

	return( $writeOk );
}

function CacheReadDsc( $filePath )
{
	if( !@file_exists( $filePath ) )
		return( false );
	return( @unserialize( @file_get_contents( $filePath ) ) );
}

function CacheDscUpdate( $lock, $settCache, $content, $deps, $subParts, $dataPath, $tmp = false, $tmpCont = null, $origContHash = null, $learnId = null )
{
	global $seraph_accel_g_dscFile;
	global $seraph_accel_g_dscFilePending;
	global $seraph_accel_g_simpCacheMode;

	$contentType = 'html';
	if( is_string( $seraph_accel_g_simpCacheMode ) && Gen::StrStartsWith( ( string )$seraph_accel_g_simpCacheMode, 'data:' ) )
		$contentType = substr( $seraph_accel_g_simpCacheMode, 5 );

	$dsc = array( 'p' => array() );

	$writeOk = true;

	{
		if( $tmp )
		{
			if( is_string( $tmpCont ) )
				$tmpCont .= '<!-- seraph-accel-tmpTimeStamp: ' . Gen::GetCurRequestTime() . ' -->';
			else
				$content .= '<!-- seraph-accel-tmpTimeStamp: ' . Gen::GetCurRequestTime() . ' -->';
		}
		else if( $contTmpTimeStampBeginPos = strrpos( $content, '<!-- seraph-accel-tmpTimeStamp: ' ) )
			$content = substr( $content, 0, $contTmpTimeStampBeginPos );
		unset( $contTmpTimeStampBeginPos );
	}

	if( !$tmp || is_string( $tmpCont ) )
	{
		$contSep = ContentMarkGetSep();
		$contSepLen = strlen( $contSep );

		$contentPartOffset = 0;
		$contentPartSearchOffset = 0;
		$contentPartWrite = '';

		if( $subParts )
			foreach( $subParts as $idSubPart => $contentPart )
			{
				if( !$contentPart )
					continue;

				$subPartType = Gen::GetFileExt( $idSubPart );
				$idSubPart = Gen::GetFileName( $idSubPart, true );

				$dscPart = array( 'p' => array() );

				$writeOk = Gen::GetArrField( $settCache, array( 'chunks', 'enable' ) ) ? iadkxsejfuijdujdes_( $contentPart, $subPartType, $dscPart, $contSep, $contSepLen, $settCache, $dataPath ) : !!_ContentCw( $dscPart, $contentPart, $subPartType, $settCache, $dataPath );
				if( !$writeOk )
					break;

				$dscPart[ 'c' ] = pack( 'V', crc32( $contentPart ) );
				$dscPart[ 'a' ] = hash( 'adler32', $contentPart, true );

				if( !isset( $dsc[ 'b' ] ) )
					$dsc[ 'b' ] = array();
				$dsc[ 'b' ][ $idSubPart ] = $dscPart;
			}

		if( $writeOk )
			$writeOk = Gen::GetArrField( $settCache, array( 'chunks', 'enable' ) ) ? iadkxsejfuijdujdes_( $content, $contentType, $dsc, $contSep, $contSepLen, $settCache, $dataPath ) : !!_ContentCw( $dsc, $content, $contentType, $settCache, $dataPath );
	}
	else

		$writeOk = !!_ContentCw( $dsc, $content, $contentType, $settCache, $dataPath );

	$dsc[ 'c' ] = pack( 'V', crc32( $content ) );
	$dsc[ 'a' ] = hash( 'adler32', $content, true );

	if( $deps )
		$dsc[ 's' ] = $deps;

	if( $tmp )
	{
		if( is_string( $tmpCont ) )
		{
			if( $writeOk )
			{
				if( $oiC = CacheCw( $settCache, null, $dataPath, true, $tmpCont, $contentType ) )
					$dsc[ 't' ] = $oiC[ 'id' ];
				else
					$writeOk = false;
				unset( $oiC );
			}
		}
		else
			$dsc[ 't' ] = true;
	}
	else if( $learnId )
		$dsc[ 'l' ] = $learnId;
	if( $origContHash )
		$dsc[ 'h' ] = $origContHash;

	if( !$lock -> Acquire() )
		return( null );

	if( $writeOk )
	{
		$dscOld = CacheReadDsc( $seraph_accel_g_dscFile );

		if( $tmp && $dscOld && isset( $dscOld[ 'b' ] ) )
			$dsc[ 'b' ] = $dscOld[ 'b' ];

		{
			if( !$tmp && $dscOld && isset( $dscOld[ 't' ] ) )
				$hdrs = ($dscOld[ 'hd' ]??null);
			else
				$hdrs = GetCurHdrsToStoreInCache( $settCache );

			if( $hdrs )
				$dsc[ 'hd' ] = $hdrs;
		}

		$dscFileNew = $seraph_accel_g_dscFile . '.new';

		if( @file_put_contents( $dscFileNew, @serialize( $dsc ) ) === false )
		{
			Gen::LastErrDsc_Set( LocId::Pack( 'FileWriteErr_%1$s', 'Common', array( $dscFileNew ) ) );
			$writeOk = false;
		}

		if( $writeOk )
		{
			$preservedFileTime = Gen::FileMTime( $seraph_accel_g_dscFile );
			if( $preservedFileTime === false )
			{
				if( $tmp )
					$preservedFileTime = 5;
			}
			else if( $preservedFileTime > 0 && !$tmp )
				$preservedFileTime = false;

			if( $preservedFileTime !== false && !@touch( $dscFileNew, $preservedFileTime ) )
			{
				Gen::LastErrDsc_Set( LocId::Pack( 'FileModifyErr_%1$s', 'Common', array( $dscFileNew ) ) );
				$writeOk = false;
			}

			if( $writeOk && !@rename( $dscFileNew, $seraph_accel_g_dscFile ) )
			{
				Gen::LastErrDsc_Set( LocId::Pack( 'FileRenameErr_%1$s%2$s', 'Common', array( $dscFileNew, $seraph_accel_g_dscFile ) ) );
				$writeOk = false;
			}
			else if( !$tmp && $dscOld && isset( $dscOld[ 't' ] ) )
			{
				if( is_string( $dscOld[ 't' ] ) )
					$oiCi = $dscOld[ 't' ];
				else
					$oiCi = Gen::GetArrField( $dscOld, array( 'p', 0 ) );

				if( $oiCi )
					foreach( glob( $dataPath . '/' . $oiCi . '.html*', GLOB_NOSORT ) as $file )
						@unlink( $file );
			}
		}

		if( !$writeOk )
		{

			@unlink( $dscFileNew );
		}
	}

	if( $tmp !== 'u' )
		$ulr = @unlink( $seraph_accel_g_dscFilePending );
	if( !$tmp && Gen::StrEndsWith( $seraph_accel_g_dscFilePending, '.pp' ) )
		@unlink( substr( $seraph_accel_g_dscFilePending, 0, -1 ) );

	$lock -> Release();

	return( $writeOk ? $dsc : null );
}

function CacheDoesCronDelayPageLoad()
{
	if( !Wp::IsCronEnabled() )
		return( false );
	if( Gen::IsRequestSessionsCanBeClosedForContinueBgWork() )
		return( false );
	return( true );
}

function CacheFem()
{

	@error_reporting( E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR );
	@ini_set( 'display_errors', 'Off' );

	set_error_handler(
		function( $errno, $errstr, $errfile, $errline, $errcontext = null )
		{
			if( $errno & ( E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR ) )
				return( false );
			return( true );
		}
	);
}

function CacheDoCronAndEndRequest()
{
	global $seraph_accel_g_prepPrms;

	if( $seraph_accel_g_prepPrms !== null || !Wp::IsCronEnabled() || !Gen::GetArrField( Plugin::SettGet(), array( 'cache', 'cron' ), false ) )
		return( null );

	if( !Gen::CloseCurRequestSessionForContinueBgWork() )
		return( false );

	CacheFem();

	add_action( 'wp_loaded', function() { if( Wp::GetFilters( 'init', 'wp_cron' ) ) wp_cron(); exit(); }, -999999 );
	return( true );
}

function GetContentProcessorForce( $sett )
{

	if( !($sett[ 'debug' ]??null) )
		return( null );

	if( isset( $_REQUEST[ 'seraph_accel_proc' ] ) )
		return( Gen::SanitizeTextData( $_REQUEST[ 'seraph_accel_proc' ] ) );

	if( isset( $_COOKIE[ 'seraph_accel_proc' ] ) )
		return( Gen::SanitizeTextData( $_COOKIE[ 'seraph_accel_proc' ] ) );

	return( null );
}

function ApplyContentProcessorForceSett( &$sett, $settContPrOverride )
{
	if( !$settContPrOverride )
		return;

	if( $settContPrOverride === '1' )
		return;

	$settContPrOverride = @json_decode( @base64_decode( $settContPrOverride ), true );
	if( is_array( $settContPrOverride ) )
	{
		$settContPrOverride[ 'enable' ] = true;
		$sett[ 'contPr' ] = $settContPrOverride;
	}
}

function GetCacheSessionSrvState( $dirSesss )
{
	$ctx = array( 'tmLast' => 0, 'stateId' => '' );

	Gen::DirEnum( $dirSesss . '/st_s', $ctx,
		function( $path, $file, &$ctx )
		{
			if( @filemtime( $path . '/' . $file ) > $ctx[ 'tmLast' ] )
				$ctx[ 'stateId' ] = $file;
		}
	);

	return( $ctx[ 'stateId' ] );
}

function SetCacheSessionSrvState( $dirSesss, $stateId )
{
	$dirSesss = $dirSesss . '/st_s';

	$hr = Gen::MakeDir( $dirSesss );
	if( Gen::HrFail( $hr ) )
		return( $hr );

	@touch( $dirSesss . '/' . $stateId );

	$ctx = array( 'stateId' => $stateId );

	if( !Gen::DirEnum( $dirSesss, $ctx,
		function( $path, $file, &$ctx )
		{
			if( $file != $ctx[ 'stateId' ] )
				@unlink( $path . '/' . $file );
		}
	) )
	{
		return( Gen::E_FAIL );
	}

	return( Gen::S_OK );
}

const TOF_COMPR_MAX	= 9;

function Tof_GetFileData( $dir, $id, $ver = null, $compressed = false, $oiCi = null )
{
	if( $oiCi !== null )
		$id = $oiCi . '.' . $id;

	$data = @file_get_contents( $dir . '/' . $id );
	if( $compressed )
		$data = @gzdecode( $data );
	$data = @unserialize( $data );

	if( !is_array( $data ) )
		$data = array();

	if( $ver )
	{
		$vFrom = ($data[ 'v' ]??null);
		if( is_array( $ver ) )
		{
			if( $ver[ 0 ] !== $vFrom )
				$data = call_user_func( $ver[ 1 ], $data, $vFrom );
		}
		else if( $ver !== $vFrom )
			$data = array();
	}

	return( $data );
}

function Tof_SetFileData( $dir, $id, $data, $ver = null, $tmp = false, $compressed = false, &$oiCi = null )
{
	$hr = Gen::MakeDir( $dir, true );
	if( Gen::HrFail( $hr ) )
		return( $hr );

	if( $ver )
		$data[ 'v' ] = $ver;

	$data = @serialize( $data );

	if( $oiCi !== null )
	{
		$oiCi = @md5( $data ) . '.' . sprintf( '%x', strlen( $data ) );
		$id = $oiCi . '.' . $id;

		if( @file_exists( $dir . '/' . $id ) )
			return( Gen::S_FALSE );
	}

	$fileTmp = tempnam( $dir, '' );
	if( !$fileTmp )
		return( Gen::E_FAIL );

	if( $compressed !== false )
		$data = @gzencode( $data, is_int( $compressed ) ? $compressed : -1 );

	if( @file_put_contents( $fileTmp, $data ) === false )
	{
		@unlink( $fileTmp );
		return( Gen::E_FAIL );
	}

	if( !@rename( $fileTmp, $tmp ? ( $fileTmp . '.' . $id ) : ( $dir . '/' . $id ) ) )
	{
		@unlink( $fileTmp );
		return( Gen::E_FAIL );
	}

	return( Gen::S_OK );
}

function _ContentMarkSeparate_CreateSepElem( $doc )
{

	$item = $doc -> createElement( 'script' );
	$item -> setAttribute( 'type', 'text/seraph-accel-cont-sep' );

	return( $item );
}

function ContentMarkSeparate( $item, $checkSize = true, $side = 0xFF )
{
	if( $checkSize && strlen( $item -> nodeValue ) < 2048 )
		return;

	$doc = $item -> ownerDocument;
	$parentNode = $item -> parentNode;

	if( $side & 1 )
		HtmlNd::InsertBefore( $parentNode, _ContentMarkSeparate_CreateSepElem( $doc ), $item );
	if( $side & 2 )
		HtmlNd::InsertAfter( $parentNode, _ContentMarkSeparate_CreateSepElem( $doc ), $item );
}

function ContentMarkGetSep()
{
	$doc = new \DOMDocument();
	$sep = _ContentMarkSeparate_CreateSepElem( $doc );
	return( $doc -> saveHTML( $sep ) );
}

function ContentMarkSeparate_CreateLazyPartSepElem( $doc, $id )
{
	$item = $doc -> createElement( 'script' );
	$item -> setAttribute( 'type', 'text/seraph-accel-lazypart-sep' );
	$item -> setAttribute( 'id', $id );
	return( $item );
}

function ContentMarkGetPartSep( $id )
{
	$doc = new \DOMDocument();
	$sep = ContentMarkSeparate_CreateLazyPartSepElem( $doc, $id );
	return( $doc -> saveHTML( $sep ) );
}

function ContentMarkSeparateSofter( &$content, $hide = true )
{
	$content = str_replace( ContentMarkGetSep(), $hide ? '' : '<!-- seraph-accel-cont-sep -->', $content );
}

function GetCacheSiteIdAdjustPath( $sites, &$addr, &$siteSubId, &$path )
{
	if( !is_array( $sites ) )
		return( null );

	if( $path )
		$addr .= '/' . $path;

	$addrSite = $addr;
	for( ;; )
	{
		$id = ($sites[ $addrSite ]??null);
		if( $id )
		{
			$path = ltrim( substr( $addr, strlen( $addrSite ) ), '/' );

			$posSubSite = strpos( $id, '-' );
			if( $posSubSite !== false )
			{
				$siteSubId = substr( $id, $posSubSite + 1 );
				$id = substr( $id, 0, $posSubSite );
			}

			$addr = $addrSite;
			return( $id );
		}
		else if( $id === false )
			break;

		$addrSiteNext = dirname( $addrSite );
		if( $addrSiteNext === $addrSite )
			break;
		$addrSite = $addrSiteNext;
		unset( $addrSiteNext );
	}

	return( null );
}

function GetSiteIds()
{
	static $aIds;

	if( $aIds !== null )
		return( $aIds );

	global $seraph_accel_sites;

	$aIds = array();
	foreach( ( array )$seraph_accel_sites as $id )
	{
		$posSubSite = strpos( $id, '-' );
		if( $posSubSite !== false )
			$id = substr( $id, 0, $posSubSite );
		$aIds[ $id ] = true;
	}

	return( $aIds = array_keys( $aIds ) );
}

function CachePathNormalize( $path, &$pathIsDir, $bLwr = true )
{
	if( $path == '/' )
	{
		$pathIsDir = true;
		$path = '';
	}
	else
	{
		$path = ltrim( $path, '/' );
		if( $bLwr )
			$path = strtolower( $path );

		if( substr( $path, -1 ) == '/' )
		{
			$pathIsDir = true;
			$path = rtrim( $path, '/' );
		}
	}

	return( Gen::GetNormalizedPath( $path ) );
}

function ParseContCachePathArgs( $serverArgs, &$args )
{

	$path = ($serverArgs[ 'REQUEST_URI' ]??null);
	$posQuery = strpos( $path, '?' );

	if( $posQuery !== false )
	{
		$queryOrig = substr( $path, $posQuery + 1 );
		if( $args === null || $queryOrig != ($serverArgs[ 'QUERY_STRING' ]??null) )
		{
			$args = array();
			@parse_str( $queryOrig, $args );
		}

		$path = substr( $path, 0, $posQuery );
	}
	else
		$args = array();

	return( $path );
}

function GetContCacheEarlySkipData( &$pathOrig = null , &$path = null , &$pathIsDir = null , &$args = null  )
{
	global $seraph_accel_g_cacheSkipData;

	if( $seraph_accel_g_cacheSkipData !== null )
		return( $seraph_accel_g_cacheSkipData );

	$seraph_accel_g_cacheSkipData = false;

	if( defined( 'SID' ) && SID != '' )
		$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'sid' ) );
	else if( defined( 'DOING_CRON' ) || isset( $_REQUEST[ 'doing_wp_cron' ] ) )
		$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'cron' ) );
	else if( isset( $_REQUEST[ 'seraph_accel_at' ] ) )
		$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'seraph_accel_at:' . Gen::SanitizeId( $_REQUEST[ 'seraph_accel_at' ] ) ) );
	else
	{
		$pathOrig = ParseContCachePathArgs( $_SERVER, $args );
		$path = CachePathNormalize( $pathOrig, $pathIsDir );

		if( is_admin() )
			$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'admin' ) );
		else if( defined( 'XMLRPC_REQUEST' ) )
			$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'xmlrpc' ) );
		else if( strpos( $path, 'robots.txt' ) !== false )
			$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'robots' ) );
		else if( strpos( $path, '.htaccess' ) !== false )
			$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'htaccess' ) );
		else if( isset( $_SERVER[ 'HTTP_LINGUISE_ORIGINAL_LANGUAGE' ] ) )
			$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'linguiseGetOrig' ) );
		else if( ( $pos = strpos( '/' . $path, '/wp-' ) ) !== false )
		{
			if( !Gen::StrStartsWith( substr( $path, $pos ), basename( WP_CONTENT_DIR ) ) )
				$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'wpUrl' ) );
		}
	}

	return( $seraph_accel_g_cacheSkipData );
}

function lfjikztqjqji( $siteId, $tmCur, $inc = true )
{
	if( Plugin::IsApiRequest() )
		return( null );

	$statPath = GetCacheDir() . '/s/' . $siteId . '/st';
	$imuyluwqfjqji = Tof_GetFileData( $statPath, 'pv' );

	if( !($imuyluwqfjqji[ 'ts' ]??null) )
		$imuyluwqfjqji[ 'ts' ] = $tmCur;
	if( !($imuyluwqfjqji[ 'n' ]??null) )
		$imuyluwqfjqji[ 'n' ] = 0;

	if( $tmCur - $imuyluwqfjqji[ 'ts' ] > 60 * 60 * 24 * 30 )
	{
		$imuyluwqfjqji[ 'ts' ] = $tmCur;
		$imuyluwqfjqji[ 'n' ] = 0;
	}

	if( $inc )
	{

		$imuyluwqfjqji[ 'n' ]++;

		if( Gen::HrFail( Tof_SetFileData( $statPath, 'pv', $imuyluwqfjqji ) ) )
			return( 'errUpdateStat' );
	}

	if( $imuyluwqfjqji[ 'n' ] > 5000 )
		return( 'outOfLimits' );

	return( null );
}

function _NormalizeExclPath( &$path )
{
	if( strlen( $path ) > 1 && $path[ 0 ] === '/' )
		$path = substr( $path, 1 );
}

function IsUriByPartsExcluded( $settCache, $path, &$query )
{
	$bDeParseArgs = false;
	if( is_string( $query ) )
	{
		$query = Net::UrlParseQuery( $query );
		$bDeParseArgs = true;
	}

	_NormalizeExclPath( $path );
	$res = !!_ContProcGetExclStatus( $settCache, Gen::GetArrField( $settCache, array( 'ctxGrps' ), array() ), null, null, $path, $path, $query, true );

	if( $bDeParseArgs )
	{
		$query = Net::UrlBuildQuery( $query );
		if( !$query )
			$query = null;
	}

	return( $res );
}

function CheckPathInUriList( $a, $path, $pathOrig = null )
{
	if( !is_string( $path ) )
		$path = '';
	if( $pathOrig === null )
		$pathOrig = $path;

	foreach( $a as $aa )
	{
		_NormalizeExclPath( $aa );

		$matched = false;
		foreach( ExprConditionsSet_Parse( $aa ) as $e )
		{
			$isRegExp = IsStrRegExp( $e[ 'expr' ] );

			if( $isRegExp )
			{
				if( @preg_match( $e[ 'expr' ], $pathOrig, $valFound ) )
					$valFound = count( $valFound ) > 1 ? $valFound[ 1 ] : $valFound[ 0 ];
				else
					$valFound = '';
			}
			else if( strpos( $path, $e[ 'expr' ] ) === 0 )
				$valFound = $path;
			else
				$valFound = '';

			$matched = ExprConditionsSet_ItemOp( $e, $valFound );
			if( !$matched )
				break;
		}

		if( $matched )
			return( $aa );
	}

	return( null );
}

function _ContProcGetExclStatus( $settCache, $ctxGrps, $userAgent, $cookies, $path, $pathOrig, &$args, $adjustArgs = false, &$aArgRemove = array() )
{

	if( !empty( $args ) )
	{
		if( $adjustArgs )
			$aArgProcess = &$args;
		else
		{
			$argsTmp = $args;
			$aArgProcess = &$argsTmp;
		}

		if( Gen::GetArrField( $settCache, array( 'skipArgsEnable' ), false ) && Gen::GetArrField( $settCache, array( 'skipArgsAll' ), false ) )
		{
			if( !Gen::GetArrField( $settCache, array( 'exclArgsAll' ), true ) )
			{
				$exclArgs = Gen::GetArrField( $settCache, array( 'exclArgs' ), array() );
				foreach( $aArgProcess as $argKey => $argVal )
				{
					$argKeyCmp = strtolower( $argKey );

					foreach( $exclArgs as $a )
						if( _ContProcGetExclStatus_KeyValMatch( $a, $argKeyCmp, $argVal ) )
							return( 'exclArgs:' . $a );

					foreach( $ctxGrps as $ctxGrp )
					{
						if( !($ctxGrp[ 'enable' ]??null) )
							continue;

						$ctxArgs = Gen::GetArrField( $ctxGrp, array( 'args' ), array() );
						foreach( $ctxArgs as $a )
							if( strpos( $argKeyCmp, $a ) === 0 )
								return( 'exclUserCtxArgs:' . $a );
					}
				}
			}

			$aArgRemove = array_keys( $aArgProcess );
			$aArgProcess = array();
		}
		else
		{
			if( Gen::GetArrField( $settCache, array( 'exclArgsAll' ), true ) )
			{
				if( Gen::GetArrField( $settCache, array( 'skipArgsEnable' ), true ) )
				{
					$skipArgs = Gen::GetArrField( $settCache, array( 'skipArgs' ), array() );

					foreach( $aArgProcess as $argKey => $argVal )
					{
						$argKeyCmp = strtolower( $argKey );

						foreach( $skipArgs as $a )
							if( _ContProcGetExclStatus_KeyValMatch( $a, $argKeyCmp, $argVal ) )
								unset( $aArgProcess[ $argKey ] );
					}
				}

				if( !empty( $aArgProcess ) )
					return( 'exclArgsAll' );
			}
			else
			{
				$exclArgs = Gen::GetArrField( $settCache, array( 'exclArgs' ), array() );
				$skipArgs = Gen::GetArrField( $settCache, array( 'skipArgsEnable' ), false ) ? Gen::GetArrField( $settCache, array( 'skipArgs' ), array() ) : array();
				foreach( $aArgProcess as $argKey => $argVal )
				{
					$argKeyCmp = strtolower( $argKey );

					foreach( $exclArgs as $a )
						if( _ContProcGetExclStatus_KeyValMatch( $a, $argKeyCmp, $argVal ) )
							return( 'exclArgs:' . $a );

					foreach( $ctxGrps as $ctxGrp )
					{
						if( !($ctxGrp[ 'enable' ]??null) )
							continue;

						$ctxArgs = Gen::GetArrField( $ctxGrp, array( 'args' ), array() );
						foreach( $ctxArgs as $a )
							if( strpos( $argKeyCmp, $a ) === 0 )
								return( 'exclUserCtxArgs:' . $a );
					}

					foreach( $skipArgs as $a )
						if( _ContProcGetExclStatus_KeyValMatch( $a, $argKeyCmp, $argVal ) )
						{
							$aArgRemove[] = $argKey;
							unset( $aArgProcess[ $argKey ] );
						}
				}
			}
		}

		unset( $aArgProcess );
	}

	if( $uriExcl = CheckPathInUriList( Gen::GetArrField( $settCache, array( 'urisExcl' ), array() ), $path, $pathOrig ) )
		return( 'exclUris:' . $uriExcl );

	if( $userAgent )
		foreach( Gen::GetArrField( $settCache, array( 'exclAgents' ), array() ) as $e )
			if( strpos( $userAgent, $e ) !== false )
				return( 'exclAgents:' . $e );

	$exclCookies = Gen::GetArrField( $settCache, array( 'exclCookies' ), array() );
	if( $exclCookies && $cookies )
		foreach( $cookies as $cookKey => $cookVal )
			foreach( $exclCookies as $e )
				if( strpos( $cookKey, $e ) === 0 )
					return( 'exclCookies:' . $e );

	return( false );
}

function _ContProcGetExclStatus_KeyValMatch( $expr, $k, $v, $sep = '=' )
{
	$found = false;
	foreach( ExprConditionsSet_Parse( $expr ) as $e )
	{
		$val = false;
		if( IsStrRegExp( $e[ 'expr' ] ) )
		{
			if( @preg_match( $e[ 'expr' ] . 'S', $k . $sep . $v, $m ) )
				$val = count( $m ) > 1 ? $m[ 1 ] : $m[ 0 ];
		}
		else if( Gen::StrStartsWith( $k, $e[ 'expr' ] ) )
			$val = true;

		if( !ExprConditionsSet_ItemOp( $e, $val ) )
			return( false );

		$found = true;
	}

	return( $found );
}

function ContProcGetExclStatus( $siteId, $settCache, $path, $pathOrig, $pathIsDir, &$args, &$varsOut, $adjustArgs = true, $adjustStat = true )
{
	global $seraph_accel_g_contProcGetExclStatus;
	global $seraph_accel_g_prepContIsUserCtx;
	global $seraph_accel_g_cacheCtxSkip;

	if( $seraph_accel_g_contProcGetExclStatus !== null )
		return( $seraph_accel_g_contProcGetExclStatus );

	$tmCur = Gen::GetCurRequestTime();

	$varsOut = array();

	$userAgent = strtolower( ($_SERVER[ 'HTTP_USER_AGENT' ]??'') );

	$varsOut[ 'tmCur' ] = $tmCur;
	$varsOut[ 'userAgent' ] = $userAgent;
	$varsOut[ 'aArgRemove' ] = array();

	$ctxGrps = Gen::GetArrField( $settCache, array( 'ctxGrps' ), array() );

	if( $pathIsDir )
		$path .= '/';
	$seraph_accel_g_contProcGetExclStatus = _ContProcGetExclStatus( $settCache, $ctxGrps, $userAgent, $_COOKIE, $path, $pathOrig, $args, $adjustArgs, $varsOut[ 'aArgRemove' ] );
	if( $seraph_accel_g_contProcGetExclStatus )
	{

		return( $seraph_accel_g_contProcGetExclStatus );
	}

	$sessInfo = GetCacheCurUserSession( $siteId, $seraph_accel_g_cacheCtxSkip );

	$userId = ($sessInfo[ 'userId' ]??null);

	$varsOut[ 'sessInfo' ] = $sessInfo;
	$varsOut[ 'userId' ] = $userId;

	if( !$seraph_accel_g_cacheCtxSkip )
	{
		$stateCookId = GetCookiesState( $ctxGrps, $_COOKIE );

		if( $stateCookId || $userId )
		{

			$seraph_accel_g_prepContIsUserCtx = true;
			if( !Gen::GetArrField( $settCache, array( 'ctx' ), false ) )
				$seraph_accel_g_contProcGetExclStatus = 'userCtx';
		}
	}
	else
	{
		$stateCookId = '';

		$viewsGrps = Gen::GetArrField( $settCache, array( 'viewsGrps' ), array() );

		foreach( array_keys( $_COOKIE ) as $cookKey )
		{
			$viewStateIdProbe = '';
			if( ($settCache[ 'views' ]??null) )
				foreach( $viewsGrps as $viewsGrp )
					if( ($viewsGrp[ 'enable' ]??null) )
						AccomulateCookiesState( $viewStateIdProbe, array( $cookKey => $_COOKIE[ $cookKey ] ), Gen::GetArrField( $viewsGrp, array( 'cookies' ), array() ) );

			if( !strlen( $viewStateIdProbe ) )
				unset( $_COOKIE[ $cookKey ] );
		}
	}

	$varsOut[ 'stateCookId' ] = $stateCookId;

	$shouldCurUserSessionExist = ShouldCurUserSessionExist();
	if( ( !($sessInfo[ 'sessId' ]??null) && ( $shouldCurUserSessionExist || $stateCookId ) ) || ( $shouldCurUserSessionExist && !$userId ) )
	{

		$seraph_accel_g_contProcGetExclStatus = Gen::GetArrField( $settCache, array( 'ctx' ), false ) ? 'noCacheSession' : 'userCtx';
	}

	return( $seraph_accel_g_contProcGetExclStatus );
}

function IsStrRegExp( $s )
{
	return( strpos( '/~@;%`#', ($s[ 0 ]??null) ) !== false );
}

function ExprConditionsSet_Parse( $expr )
{
	$a = explode( ' & ', $expr );
	foreach( $a as &$e )
	{
		$e = trim( $e );

		if( ( $posVal = strpos( $e, ' != ' ) ) !== false )
			$e = array( 'expr' => trim( substr( $e, 0, $posVal ) ), 'op' => 'ne', 'v' => trim( substr( $e, $posVal + 4 ) ) );
		else if( ( $posVal = strpos( $e, ' = ' ) ) !== false )
			$e = array( 'expr' => trim( substr( $e, 0, $posVal ) ), 'op' => 'e', 'v' => trim( substr( $e, $posVal + 3 ) ) );
		else if( ( $posVal = strpos( $e, ' < ' ) ) !== false )
			$e = array( 'expr' => trim( substr( $e, 0, $posVal ) ), 'op' => '<', 'v' => trim( substr( $e, $posVal + 3 ) ) );
		else if( ( $posVal = strpos( $e, ' > ' ) ) !== false )
			$e = array( 'expr' => trim( substr( $e, 0, $posVal ) ), 'op' => '>', 'v' => trim( substr( $e, $posVal + 3 ) ) );
		else if( ( $posVal = strpos( $e, ' >= ' ) ) !== false )
			$e = array( 'expr' => trim( substr( $e, 0, $posVal ) ), 'op' => '>=', 'v' => trim( substr( $e, $posVal + 4 ) ) );
		else if( ( $posVal = strpos( $e, ' <= ' ) ) !== false )
			$e = array( 'expr' => trim( substr( $e, 0, $posVal ) ), 'op' => '<=', 'v' => trim( substr( $e, $posVal + 4 ) ) );
		else if( Gen::StrStartsWith( $e, '!' ) )
			$e = array( 'expr' => trim( substr( $e, 1 ) ), 'op' => 'v' );
		else
			$e = array( 'expr' => $e, 'op' => '' );
	}

	return( $a );
}

function ExprConditionsSet_ItemOp( $e, $v )
{
	switch( $e[ 'op' ] )
	{
	case 'ne':		return( $v !== $e[ 'v' ] );
	case 'e':		return( $v === $e[ 'v' ] );
	case 'v':		return( is_string( $v ) ? !strlen( $v ) : !$v );

	case '<':
	case '>':
	case '>=':
	case '<=':		return( ( is_string( $v ) && strlen( $v ) ) ? @version_compare( $v, $e[ 'v' ], $e[ 'op' ] ) : false );
	}

	return( is_string( $v ) ? !!strlen( $v ) : !!$v );
}

function ExprConditionsSet_IsItemOpFullSearch( $e )
{
	switch( $e[ 'op' ] )
	{
	case 'ne':
	case 'e':		return( true );

	case '<':
	case '>':
	case '>=':
	case '<=':		return( true );
	}

	return( false );
}

function ExprConditionsSet_IsTrivial( $ee )
{
	return( count( $ee ) == 1 && $ee[ 0 ][ 'op' ] === '' );
}

function ExprConditionsSet_IsRegExp( $ee )
{
	foreach( $ee as $e )
		if( IsStrRegExp( $e[ 'expr' ] ) )
			return( true );
	return( false );
}

function ExprConditionsSet_MatchEx( $aExpr, $cbMatch )
{
	$found = false;
	foreach( $aExpr as $e )
	{
		$val = call_user_func( $cbMatch, $e[ 'expr' ] );
		if( !ExprConditionsSet_ItemOp( $e, $val ) )
			return( false );

		$found = true;
	}

	return( $found );
}

function ExprConditionsSet_Match( $expr, $cbMatch )
{
	return( ExprConditionsSet_MatchEx( ExprConditionsSet_Parse( $expr ), $cbMatch ) );
}

function AccomulateCookiesState( &$state, $cookies, $elems )
{
	foreach( $elems as $ee )
	{
		$statePart = '';
		foreach( ExprConditionsSet_Parse( $ee ) as $e )
		{
			$isRegExp = IsStrRegExp( $e[ 'expr' ] );

			$cookieVals = array();
			foreach( $cookies as $cookKey => $cookVal )
				if( $isRegExp ? @preg_match( $e[ 'expr' ], $cookKey ) : Gen::StrStartsWith( $cookKey, $e[ 'expr' ] ) )
					$cookieVals[] = $cookVal;
			if( !$cookieVals )
				$cookieVals = array( '' );

			$found = false;
			foreach( $cookieVals as $cookVal )
			{
				if( ExprConditionsSet_ItemOp( $e, $cookVal ) )
				{
					$statePart .= strlen( $cookVal ) ? $cookVal : '!';
					$found = true;
				}
			}

			if( !$found )
			{
				$statePart = '';
				break;
			}
		}

		$state .= $statePart;
	}
}

function AccomulateHdrsState( &$state, &$aCurHdr, $elems )
{
	foreach( $elems as $ee )
	{
		$statePart = '';
		foreach( ExprConditionsSet_Parse( $ee ) as $e )
		{
			if( $aCurHdr === null )
				$aCurHdr = Net::GetRequestHeaders( null, false, true );

			$isRegExp = IsStrRegExp( $e[ 'expr' ] );

			$vals = array();
			foreach( $aCurHdr as $hdr )
			{
				if( $isRegExp )
				{
					if( @preg_match( $e[ 'expr' ], $hdr, $m ) )
						$vals[] = count( $m ) > 1 ? $m[ 1 ] : $m[ 0 ];
				}
				else if( Gen::StrStartsWith( $hdr, $e[ 'expr' ] ) )
					$vals[] = $hdr;
			}
			if( !$vals )
				$vals = array( '' );

			$found = false;
			foreach( $vals as $val )
			{
				if( ExprConditionsSet_ItemOp( $e, $val ) )
				{
					$statePart .= strlen( $val ) ? $val : '!';
					$found = true;
				}
			}

			if( !$found )
			{
				$statePart = '';
				break;
			}
		}

		$state .= $statePart;
	}
}

function GetCookiesState( $ctxGrps, $cookies )
{
	$stateCookId = '';

	foreach( $ctxGrps as $ctxGrp )
		if( ($ctxGrp[ 'enable' ]??null) )
			AccomulateCookiesState( $stateCookId, $cookies, Gen::GetArrField( $ctxGrp, array( 'cookies' ), array() ) );

	return( $stateCookId );
}

function ContProcGetSkipStatus( $content )
{
	global $seraph_accel_g_contProcGetSkipStatus;
	global $seraph_accel_g_sRedirLocation;
	global $seraph_accel_g_simpCacheMode;

	if( $seraph_accel_g_contProcGetSkipStatus !== null )
		return( $seraph_accel_g_contProcGetSkipStatus );

	if( $seraph_accel_g_simpCacheMode === null && defined( 'REST_REQUEST' ) && REST_REQUEST )
		return( $seraph_accel_g_contProcGetSkipStatus = 'restapi' );

	if( $seraph_accel_g_simpCacheMode === null && defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
		return( $seraph_accel_g_contProcGetSkipStatus = 'xmlrpc' );

	$errLast = error_get_last();
	if( Gen::GetArrField( $errLast, array( 'type' ), 0 ) & ( E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR ) )
	{
		$msg = $errLast[ 'message' ];
		if( strpos( $msg, $errLast[ 'file' ] ) === false )
			$msg .= "\n" . $errLast[ 'file' ] . ':' . $errLast[ 'line' ];
		return( $seraph_accel_g_contProcGetSkipStatus = 'err:php:' . rawurlencode( $msg ) );
	}

	$http_response_code = http_response_code();
	if( $seraph_accel_g_sRedirLocation && $http_response_code === 200 )
		$http_response_code = 302;
	if( $http_response_code !== 200 )
	{
		$skipStatus = 'httpCode:' . $http_response_code;
		if( in_array( $http_response_code, array( 301, 302, 307, 308 ) ) && $seraph_accel_g_sRedirLocation )
			$skipStatus .= ':' . rawurlencode( $seraph_accel_g_sRedirLocation );
		return( $seraph_accel_g_contProcGetSkipStatus = $skipStatus );
	}

	if( is_404() )
		return( $seraph_accel_g_contProcGetSkipStatus = 'httpCode:404' );

	if( $seraph_accel_g_simpCacheMode === null && is_search() )
		return( $seraph_accel_g_contProcGetSkipStatus = 'search' );
	if( $seraph_accel_g_simpCacheMode === null && is_feed() )
		return( $seraph_accel_g_contProcGetSkipStatus = 'feed' );

	if( $seraph_accel_g_simpCacheMode === null && Gen::StrPosArr( $content, array( '</body>', '</BODY>' ) ) === false && Gen::StrPosArr( $content, array( '</head>', '</HEAD>' ) ) === false )
	{

		return( $seraph_accel_g_contProcGetSkipStatus = 'noHdrOrBody' );
	}

	return( $seraph_accel_g_contProcGetSkipStatus = false );
}

function MatchUserAgentExpressions( $userAgent, $aAgent, $aAgentPS = array() )
{
	foreach( $aAgent as $aa )
	{
		$bPS = false;
		foreach( $aAgentPS as $agentPS )
		{
			if( !@preg_match( $agentPS, $aa ) )
				continue;

			$bPS = true;
			break;
		}

		if( $bPS )
			continue;

		$matched = false;
		foreach( ExprConditionsSet_Parse( $aa ) as $a )
		{
			$isRegExp = IsStrRegExp( $a[ 'expr' ] );

			if( $isRegExp )
			{
				if( @preg_match( $a[ 'expr' ], $userAgent, $userAgentFoundVal ) )
					$userAgentFoundVal = count( $userAgentFoundVal ) > 1 ? $userAgentFoundVal[ 1 ] : $userAgentFoundVal[ 0 ];
				else
					$userAgentFoundVal = '';
			}
			else if( strpos( $userAgent, $a[ 'expr' ] ) !== false )
				$userAgentFoundVal = $userAgent;
			else
				$userAgentFoundVal = '';

			$matched = ExprConditionsSet_ItemOp( $a, $userAgentFoundVal );
			if( !$matched )
				break;
		}

		if( $matched )
			return( true );
	}

	return( false );
}

function ContProcIsCompatView( $settCache, $userAgent  )
{

	global $seraph_accel_g_contProcCompatView;

	if( $seraph_accel_g_contProcCompatView !== null )
		return( $seraph_accel_g_contProcCompatView );

	$compatView = false;

	$viewsCompatGrps = Gen::GetArrField( $settCache, array( 'viewsCompatGrps' ), array() );

	foreach( $viewsCompatGrps as $viewsCompatGrp )
	{
		if( !($viewsCompatGrp[ 'enable' ]??null) )
			continue;

		if( MatchUserAgentExpressions( $userAgent, Gen::GetArrField( $viewsCompatGrp, array( 'agents' ), array() ) ) )
		{
			$compatView = ($viewsCompatGrp[ 'id' ]??null);
			break;
		}
	}

	return( $seraph_accel_g_contProcCompatView = $compatView );
}

function GetViewTypeUserAgent( $viewsDeviceGrp )
{
	return( 'Mozilla/99999.9 AppleWebKit/9999999.99 (KHTML, like Gecko) Chrome/999999.0.9999.99 Safari/9999999.99 seraph-accel-Agent/2.28.15 ' . ucwords( implode( ' ', Gen::GetArrField( $viewsDeviceGrp, array( 'agents' ), array() ) ) ) );
}

function CorrectRequestScheme( &$serverArgs, $target = null )
{

	if( strtolower( ($serverArgs[ 'HTTPS' ]??'') ) == 'on' || ( $target == 'client' && strtolower( ($serverArgs[ 'HTTP_X_FORWARDED_PROTO' ]??'') ) == 'https' ) )
	{
		if( ($serverArgs[ 'REQUEST_SCHEME' ]??null) == 'http' && ($serverArgs[ 'SERVER_PORT' ]??null) == 80 )
			$serverArgs[ 'SERVER_PORT' ] = 443;
		$serverArgs[ 'REQUEST_SCHEME' ] = 'https';
	}
	else if( !($serverArgs[ 'REQUEST_SCHEME' ]??null) )
		$serverArgs[ 'REQUEST_SCHEME' ] = 'http';
}

function GetRequestHost( $serverArgs )
{
	return( strtolower( Net::GetRequestHost( $serverArgs ) ) );
}

function GetCurRequestUrl()
{
	$serverArgsTmp = Gen::ArrCopy( $_SERVER ); CorrectRequestScheme( $serverArgsTmp, 'client' );
	return( $serverArgsTmp[ 'REQUEST_SCHEME' ] . '://' . GetRequestHost( $serverArgsTmp ) . $serverArgsTmp[ 'REQUEST_URI' ] );
}

function AddCurPostArgs( &$args )
{
	foreach( $_POST as $argId => $argV )
		if( is_scalar( $argV ) )
			$args[ $argId ] = substr( ( string )$argV, 0, 100 );
}

function Queue_GetStgPrms( $dirQueue, $state )
{
	return( array( 'dirFilesPattern' => $dirQueue . '/' . $state . '/*.dat.gz', 'options' => array( 'countPerFirstChunk' => 100, 'cbSort' =>
		function( $item1, $item2 )
		{
			$iCmp = Gen::VarCmp( ($item1[ 'p' ]??null), ($item2[ 'p' ]??null) );
			if( $iCmp !== 0 )
				return( $iCmp );

			$iCmp = Gen::VarCmp( ($item1[ 't' ]??null), ($item2[ 't' ]??null) );
			if( $iCmp !== 0 )
				return( $iCmp );

			return( 0 );
		}
	) ) );
}

function Queue_IsPriorFirst( $siteId, $priority )
{
	$dirQueue = GetCacheDir() . '/q/' . $siteId;

	$lock = new Lock( 'l', $dirQueue );
	if( !$lock -> Acquire() )
		return( false );

	$res = false;

	foreach( array( 1, 0 ) as $state )
	{
		$a = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, $state ) );
		$b = Gen::GetArrField( current( $a -> slice( 0, 1 ) ), array( 'p' ), 0 ) == $priority;
		$a -> dispose();

		if( $b )
		{
			$res = true;
			break;
		}
	}

	$lock -> Release();
	return( $res );
}

function OnAsyncTask_QueueProcessItems( $args )
{
	$settGlobal = Plugin::SettGetGlobal();
	$settCacheGlobal = Gen::GetArrField( $settGlobal, array( 'cache' ), array() );

	$asyncMode = OnAsyncTasksPushGetMode( $settGlobal );

	$nMaxItems = ($settCacheGlobal[ 'maxProc' ]??null);
	if( !$nMaxItems )
		$nMaxItems = 1;
	$nMaxItemsTotal = $nMaxItems;

	$procTmLim = Gen::GetArrField( Plugin::SettGet(), array( 'cache', 'procTmLim' ), 570 );

	$dirFileValues = PluginFileValues::GetDirVar( '' );

	$tmCur = microtime( true );

	foreach( glob( GetCacheDir() . '/qt/*.dat' ) as $fileTempQueue )
	{
		$data = @unserialize( @file_get_contents( $fileTempQueue ) );
		@unlink( $fileTempQueue );

		if( $data )
			CachePostPreparePageEx( null, Gen::GetArrField( $data, array( 'u' ), '' ), Gen::GetArrField( $data, array( 's' ), '' ), Gen::GetArrField( $data, array( 'p' ), 10 ), null, Gen::GetArrField( $data, array( 'h' ), array() ), Gen::GetArrField( $data, array( 't' ), 0.0 ) );
	}

	$aCurItemsPrior = array();
	foreach( GetSiteIds() as $siteId )
	{
		$dirQueue = GetCacheDir() . '/q/' . $siteId;

		$lock = new Lock( 'l', $dirQueue );
		if( !$lock -> Acquire() )
			continue;

		$aProgress = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 1 ) );
		$aDone = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 2 ) );
		foreach( $aProgress -> slice( 0 ) as $id => $item )
		{
			$data = Gen::GetArrField( Gen::Unserialize( ($item[ 'd' ]??null) ), array( '' ), array() );

			$hrItemForce = Gen::S_OK;
			$tmDur = $tmCur - ( float )($item[ 't' ]??null);
			$fileCtl = ProcessCtlData_GetFullPath( ($data[ 'pc' ]??null) );
			if( $tmDur <= $procTmLim + 30 )
			{
				if( $tmDur > ( 30 ) && $fileCtl )
				{
					$ctlRes = ProcessCtlData_Get( $fileCtl, $isLive );

					if( $ctlRes === null )
						$hrItemForce = Gen::S_ABORTED;
					else if( Gen::GetArrField( $ctlRes, array( 'stage' ), '' ) )
					{
						if( !Gen::GetArrField( $ctlRes, array( 'finish' ) ) && !$isLive )
							$hrItemForce = Gen::E_INVALID_STATE;
					}
					else if( ( int )($item[ 'tp' ]??0) == 0 && $asyncMode != 'ec' && ($settGlobal[ 'asyncSmpOpt' ]??null) )
						_CacheProcessItem_RunSmpOpt( $asyncMode );
				}
			}
			else
				$hrItemForce = Gen::E_TIMEOUT;

			if( $hrItemForce == Gen::S_OK )
			{
				if( $nMaxItems > 0 )
					$nMaxItems --;
				$aCurItemsPrior[ ( int )($item[ 'p' ]??null) ] = true;
				continue;
			}

			if( $fileCtl )
				ProcessCtlData_Del( $fileCtl );

			$data[ 'td' ] = $tmDur;
			$data[ 'hr' ] = $hrItemForce;

			$item[ 'd' ] = Gen::Serialize( $data );
			$item[ 'p' ] = -1000;
			$item[ 't' ] = $tmCur;
			unset( $aProgress[ $id ] );
			$aDone[ $id ] = $item;
		}

		{
			$n = $aDone -> count();

			if( Gen::GetArrField( $settGlobal, array( 'debug' ), false ) )
			{
				if( $n > 2 * 10 )
				{
					$aDoneNew = array();
					$nDone = 0;
					$nDoneErr = 0;

					for( $aDone -> end(); $aDone -> valid(); $aDone -> prev() )
					{
						$item = $aDone -> current();

						$data = Gen::GetArrField( Gen::Unserialize( ($item[ 'd' ]??null) ), array( '' ), array() );
						list( $iconClr, $state, $stateDsc, $duration ) = GetQueueItem_Done_Attrs( $data );
						unset( $state, $stateDsc, $duration );

						$bPut = false;
						if( $iconClr == 'error' )
						{
							if( $nDoneErr < 10 )
							{
								$bPut = true;
								$nDoneErr++;
							}
						}
						else
						{
							if( $nDone < 10 )
							{
								$bPut = true;
								$nDone++;
							}
						}

						if( $bPut )
							Gen::ArrSplice( $aDoneNew, 0, 0, array( $aDone -> key() => $item ) );

						if( $nDone == 10 && $nDoneErr == 10 )
							break;
					}

					$aDone -> clear();
					$aDone -> setItems( $aDoneNew );
				}
			}
			else
			{
				if( $n > 10 )
					$aDone -> splice( 0, $n - 10 );
			}
		}

		$aDone -> dispose(); $aProgress -> dispose(); $lock -> Release();
		unset( $aDone, $aProgress, $lock );
	}

	if( !$nMaxItems )
	{

		return;
	}

	$procInterval = ($settCacheGlobal[ 'procInterval' ]??null);
	if( $procInterval )
	{
		if( $nMaxItems < $nMaxItemsTotal )
			return;

		$procEndLastTime = intval( PluginFileValues::GetEx( $dirFileValues, 'pelt' ) );
		if( $tmCur - $procEndLastTime < $procInterval )
			return;
	}

	$items = array();
	foreach( GetSiteIds() as $siteId )
	{
		$dirQueue = GetCacheDir() . '/q/' . $siteId;

		if( PluginFileValues::GetEx( PluginFileValues::GetDirVar( $siteId ), 'qp' ) )
			continue;

		$lock = new Lock( 'l', $dirQueue );
		if( !$lock -> Acquire() )
			continue;

		$aInitial = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 0 ) );
		foreach( $aInitial -> slice( 0, $nMaxItems ) as $id => $item )
		{
			$item[ 's' ] = $siteId;
			$items[ $id ] = $item;
		}
		unset( $item );

		$aInitial -> dispose(); $lock -> Release();
		unset( $aInitial, $lock );
	}

	if( !$items )
	{

		return;
	}

	uasort( $items, Gen::GetArrField( Queue_GetStgPrms( '', 0 ), array( 'options', 'cbSort' ) ) );
	$items = array_slice( $items, 0, $nMaxItems, true );

	foreach( $items as $id => $item )
	{
		$prior = ( int )($item[ 'p' ]??null);

		if( isset( $aCurItemsPrior[ -480 ] ) && $prior > -480 )
		    continue;

		$aCurItemsPrior[ $prior ] = true;

		Plugin::AsyncTaskPostEx( 'CacheProcessItem', ( int )($item[ 'tp' ]??0) == 0 && ( $asyncMode == 'ec' || ($settGlobal[ 'asyncSmpOpt' ]??null) ), array( 'id' => $id, 'siteId' => $item[ 's' ] ) );

	}

}

function CacheInitQueueProcessor()
{
	$tmCur = time();

	{
		$time = $tmCur + ( 30 );
		$time -= $time % ( 30 );
		Plugin::AsyncFastTaskPost( 'QueueProcessItems', null, array( $time, 30 ), false, function( $args, $argsPrev ) { return( false ); } );
	}

	{
		$time = $tmCur + ( 120 );
		$time -= $time % ( 120 );
		Plugin::AsyncFastTaskPost( 'CheckPostProcess', null, array( $time, 24 * 60 * 60 ), false, function( $args, $argsPrev ) { return( false ); } );
	}
}

function CachePushQueueProcessor( $next = false, $immediately = false, $shortInterval = false )
{

	$procInterval = 0;
	if( $next && $immediately === false )
	{
		$settCacheGlobal = Gen::GetArrField( Plugin::SettGetGlobal(), array( 'cache' ), array() );
		$procInterval = $shortInterval ? ($settCacheGlobal[ 'procIntervalShort' ]??null) : ($settCacheGlobal[ 'procInterval' ]??null);
	}

	$dirFileValues = PluginFileValues::GetDirVar( '' );
	$time = time() + $procInterval;

	if( !is_bool( $immediately ) )
	{
		$timeLast = PluginFileValues::GetEx( $dirFileValues, 'qpin' );
		if( $timeLast && ( $time - $timeLast < $immediately ) )
			$procInterval = 99999;
	}
	else if( $next && ( $immediately || $shortInterval ) )
		PluginFileValues::SetEx( $dirFileValues, 'pelt', 0 );

	PluginFileValues::SetEx( $dirFileValues, 'qpin', $time );

	Plugin::AsyncFastTaskPost( 'QueueProcessItems', null, array( $time ), false, true );

	if( !$next && !Wp::IsCronEnabled()  )
		return;

	if( $procInterval <= 5 )
	{
		if( $procInterval )
			sleep( $procInterval );

			Plugin::AsyncTaskPush( 0 );
	}

}

function ContentProcess_IsAborted( $ctxProcess = null, $settCache = null )
{
	global $seraph_accel_g_prepPrms;

	if( $seraph_accel_g_prepPrms === null )
		return;

	if( ($ctxProcess[ 'mode' ]??0) & 4 )
		$procPauseInt = ($settCache[ 'procPauseInt' ]??null);
	else
		$procPauseInt = 0;

	return( !Gen::SliceExecTime( ($settCache[ 'procWorkInt' ]??null), $procPauseInt, 2,
		function()
		{
			global $seraph_accel_g_prepPrms;
			return( ProcessCtlData_IsAborted( ($seraph_accel_g_prepPrms[ 'pc' ]??null) ) );
		}
	) );
}

function ProcessCtlData_GetFullPath( $file = '' )
{
	if( $file === null )
		return( null );
	return( GetCacheDir() . '/pc' . ( $file ? ( '/' . Gen::GetFileName( $file ) ) : '' ) );
}

function ProcessCtlData_IsAborted( $fileCtl )
{
	if( !$fileCtl )
		return;

	$lock = new Lock( 'pl', GetCacheDir() );
	if( !$lock -> Acquire() )
		return;

	$res = !@file_exists( $fileCtl . '.dat' );
	$lock -> Release();
	return( $res );
}

function ProcessCtlData_Init( $fileCtlDir, $data )
{
	if( Gen::HrFail( Gen::MakeDir( $fileCtlDir, true ) ) )
		return( null );

	$fileCtl = @tempnam( $fileCtlDir, '' );
	if( !$fileCtl )
		return( null );

	if( !@file_put_contents( $fileCtl . '.dat', @serialize( $data ) ) )
	{
		@unlink( $fileCtl );
		return( null );
	}

	return( $fileCtl );
}

function ProcessCtlData_Del( $fileCtl )
{
	$lock = new Lock( 'pl', GetCacheDir() );
	if( !$lock -> Acquire() )
		return;

	@unlink( $fileCtl . '.dat' );
	@unlink( $fileCtl );
	$lock -> Release();
}

function ProcessCtlData_Get( $fileCtl, &$isLive = null )
{
	$lock = new Lock( 'pl', GetCacheDir() );
	if( !$lock -> Acquire() )
		return( Gen::E_FAIL );

	if( Gen::FileContentExclusive_Open( $hFileCtl, $fileCtl ) !== Gen::E_BUSY )
	{
		if( !$hFileCtl )
		{
			$lock -> Release();
			return( null );
		}

		Gen::FileContentExclusive_Close( $hFileCtl );
		$isLive = false;
	}
	else
		$isLive = true;

	$data = @file_get_contents( $fileCtl . '.dat' );
	$data = is_string( $data ) ? @unserialize( $data ) : null;
	$lock -> Release();
	return( $data );
}

function ProcessCtlData_Update( $fileCtl, $data, $clearPrev = false, $lifeCtlOpenClose = null )
{
	if( !$fileCtl )
		return( true );

	$lock = new Lock( 'pl', GetCacheDir() );
	if( !$lock -> Acquire() )
	{

		return( false );
	}

	global $seraph_accel_g_hFileCtl;

	$dataPrev = @file_get_contents( $fileCtl . '.dat' );
	if( $dataPrev === false )
	{
		$lock -> Release();

		return( null );
	}

	if( $lifeCtlOpenClose === true )
	{
		Gen::FileContentExclusive_Open( $seraph_accel_g_hFileCtl, $fileCtl, false );
		if( !$seraph_accel_g_hFileCtl )
		{
			$lock -> Release();

			return( false );
		}
	}
	else if( $lifeCtlOpenClose === false )
		Gen::FileContentExclusive_Close( $seraph_accel_g_hFileCtl );

	if( !$dataPrev || $clearPrev )
		$dataPrev = array();
	else
		$dataPrev = @unserialize( $dataPrev );

	$res = @file_put_contents( $fileCtl . '.dat', @serialize( array_merge( $dataPrev, $data ) ) ) !== false;
	$lock -> Release();

	return( $res );
}

class ProcessQueueItemCtx
{
	public $item;
	public $data;
	public $fileCtl;

	public $id;
	public $siteId;
	public $dirQueue;
	public $procTmLim = 60 * 60 * 24;

	public $needRepeatPage = false;
	public $needLrn = false;
	public $repeatIdx = null;
	public $skipStatus = null;
	public $warns = null;
	public $infos = array();
	public $requestRes = null;
	public $method = null;
	public $url = null;
	public $hdrs = null;
	public $hr = Gen::S_OK;
	public $httpCode = 200;

	public $immediatelyPushQueue = false;

	public $hdrsForRequest;
	public $tmFinish;
	public $urlRedir;

	public function __construct( $id, $siteId )
	{
		$this -> id = $id;
		$this -> siteId = $siteId;
		$this -> dirQueue = GetCacheDir() . '/q/' . $this -> siteId;
	}

	static function AdjustRequestUrl( $url, $tmStamp, array $prepArgs )
	{
		return( Net::UrlAddArgs( $url, array( 'seraph_accel_prep' => @base64_encode( @json_encode( array_merge( $prepArgs, array( 'nonce' => hash_hmac( 'md5', '' . $tmStamp, GetSalt() ), '_tm' => '' . $tmStamp ) ) ) ) ) ) );
	}

	static function MakeRequest( $asyncMode, $method, $url, $hdrs = array(), $timeout = 0 )
	{

		if( $asyncMode == 're' || $asyncMode == 're_r' )
			return( PluginRe::MakeRequest( $method, $url, array_merge( OnAsyncTasksSetNeededHdrs( $_SERVER, array() ), ( array )$hdrs ), $timeout, false ) );

		$prms = array( 'redirection' => 0, 'timeout' => $timeout, 'sslverify' => false, 'headers' => $hdrs );

		if( !$timeout )
		{
			if( OnAsyncTasksUseCmptNbr() )
				$prms[ 'timeout' ] = 5;
			else
			{

				$prms[ 'timeout' ] = 0.01;
				$prms[ 'blocking' ] = false;
			}
		}

		if( $asyncMode == 'loc' )
		{
			$prms[ 'local' ] = true;
			$prms[ 'headers' ] = array_merge( OnAsyncTasksSetNeededHdrs( $_SERVER, array() ), ( array )$hdrs );
		}

		return( Wp::RemoteRequest( $method, $url, $prms ) );
	}

	function PrepareRequest()
	{
		$prepArgs = array( 'pc' => $this -> data[ 'pc' ], 'p' => ( int )($this -> data[ 'p' ]??null) );
		if( ( int )($this -> item[ 'p' ]??null) == -480 )
			$prepArgs[ 'lrn' ] = ($this -> data[ 'l' ]??null);

		$this -> method = ( string )($this -> data[ 'm' ]??'GET');

		$this -> url = ProcessQueueItemCtx::AdjustRequestUrl( ($this -> data[ 'u' ]??null), $this -> item[ 't' ], $prepArgs );

		$this -> hdrs = ($this -> data[ 'h' ]??null);
		if( !is_array( $this -> hdrs ) )
			$this -> hdrs = array();

		$this -> hdrsForRequest = $this -> hdrs;
		Net::GetUrlWithoutProtoEx( $this -> url, $proto );
		if( strtolower( $proto ) == 'https' )
		{
			Net::RemoveHeader( $this -> hdrsForRequest, 'Upgrade-Insecure-Requests' );
			Net::RemoveHeader( $this -> hdrsForRequest, 'Ssl' );

		}

		Net::RemoveHeader( $this -> hdrsForRequest, 'Accept-Encoding' );
		Net::RemoveHeader( $this -> hdrsForRequest, 'Cloud-Protector-Client-Ip' );

		unset( $proto );

	}

	function WaitForEndRequest()
	{
		$this -> tmFinish = microtime( true );

		{
			$sett = Plugin::SettGet();
			if( ($sett[ 'debugInfo' ]??null) )
				$this -> infos[ LocId::Pack( 'HdrsForRequest' ) ] = PackKvArrInfo( $this -> hdrsForRequest );
		}

		$ctlRes = ProcessCtlData_Get( $this -> fileCtl, $isLive );
		if( Gen::GetArrField( $ctlRes, array( 'stage' ) ) )
		{

			for( ;; )
			{

				$this -> tmFinish = microtime( true );
				if( $this -> tmFinish - $this -> item[ 't' ] > $this -> procTmLim )
				{
					$this -> hr = Gen::E_TIMEOUT;
					$this -> requestRes = null;
					break;
				}

				if( is_int( $ctlRes ) )
				{
					$this -> hr = $ctlRes;
					$this -> requestRes = null;
					break;
				}

				if( $ctlRes === null )
				{
					$this -> hr = Gen::S_ABORTED;
					$this -> requestRes = null;
					break;
				}

				if( Gen::GetArrField( $ctlRes, array( 'finish' ) ) )
				{
					$this -> skipStatus = Gen::GetArrField( $ctlRes, array( 'skip' ) );
					$this -> hr = $this -> skipStatus ? ( Gen::StrStartsWith( $this -> skipStatus, 'err:' ) ? Gen::E_FAIL : Gen::S_FALSE ) : Gen::S_OK;
					$this -> warns = Gen::GetArrField( $ctlRes, array( 'warns' ), array() );
					Gen::ArrSplice( $this -> infos, count( $this -> infos ), 0, Gen::GetArrField( $ctlRes, array( 'infos' ), array() ) );
					break;
				}

				if( !$isLive )
				{
					$this -> hr = Gen::E_INVALID_STATE;
					$this -> requestRes = null;
					break;
				}

				sleep( 1 );

				$ctlRes = ProcessCtlData_Get( $this -> fileCtl, $isLive );
			}
		}
		else
		{
			if( ProcessCtlData_IsAborted( $this -> fileCtl ) )
			{
				$this -> hr = Gen::S_ABORTED;
				$this -> requestRes = null;
			}
			else if( $this -> httpCode && $this -> httpCode != 500 )
			{
				if( $this -> httpCode == 524 || $this -> httpCode == 522 || $this -> httpCode == 504 || $this -> httpCode == 503 )
					if( ( $this -> repeatIdx = ($this -> data[ 'rdr' ]??0) ) <= 3 )
						$this -> needRepeatPage = true;
				$this -> hr = Gen::HrSuccFromFail( $this -> hr );
			}
		}

		$this -> urlRedir = $this -> requestRes ? trim( Net::GetHeaderFromWpRemoteRequestRes( $this -> requestRes, 'location' ) ) : null;
		if( !$this -> urlRedir && $this -> skipStatus && preg_match( '@^httpCode\\:(?:301|302|307|308)\\:@', $this -> skipStatus ) )
			$this -> urlRedir = rawurldecode( substr( $this -> skipStatus, 13 ) );

		if( $this -> urlRedir && $this -> urlRedir != ($this -> data[ 'u' ]??null) )
		{
			$this -> urlRedir = remove_query_arg( array( 'seraph_accel_prep' ), $this -> urlRedir );
			if( Gen::StrStartsWith( $this -> urlRedir, '//' ) )
			{
				GetUrlWithoutProtoEx( $this -> url, $proto );
				$this -> urlRedir = $proto . ':' . $this -> urlRedir;
				unset( $proto );
			}
			else if( strpos( $this -> urlRedir, '://' ) === false )
				$this -> urlRedir = Net::GetSiteAddrFromUrl( $this -> url, true ) . $this -> urlRedir;

			if( ( int )($this -> item[ 'p' ]??null) !== 10 )
				if( ( $redirIdx = ($this -> data[ 'rdr' ]??0) ) <= 4 )
					if( CachePostPreparePageEx( ($this -> data[ 'm' ]??null), $this -> urlRedir, $this -> siteId, ( int )($this -> item[ 'p' ]??null), ($this -> data[ 'p' ]??null), $this -> hdrs, $this -> data[ 'to' ], $redirIdx + 1, ($this -> data[ 'l' ]??null) ) )
						$this -> immediatelyPushQueue = true;
		}
	}

	function Finish()
	{
		$this -> data[ 'td' ] = $this -> tmFinish - $this -> item[ 't' ];

		if( $this -> hr != Gen::S_OK && !$this -> skipStatus && $this -> httpCode )
			$this -> skipStatus = 'httpCode:' . $this -> httpCode;

		if( $this -> skipStatus )
		{
			if( Gen::StrStartsWith( $this -> skipStatus, 'httpCode:' ) && $this -> urlRedir && strlen( $this -> skipStatus ) === 12 )
				$this -> skipStatus .= ':' . rawurlencode( $this -> urlRedir );

			$this -> data[ 'hr' ] = ( $this -> hr = Gen::StrStartsWith( $this -> skipStatus, 'err:' ) ? Gen::E_FAIL : Gen::S_FALSE );
			$this -> data[ 'r' ] = $this -> skipStatus;

			if( $this -> skipStatus == 'alreadyProcessing' || $this -> skipStatus == 'lrnNeed' )
				$this -> needRepeatPage = true;
			else if( Gen::StrStartsWith( $this -> skipStatus, 'lrnNeed:' ) )
				$this -> needLrn = substr( $this -> skipStatus, 8 );
		}
		else if( $this -> hr != Gen::S_OK )
		{
			$this -> data[ 'hr' ] = $this -> hr;
			if( is_wp_error( $this -> requestRes ) )
				$this -> data[ 'r' ] = rawurlencode( $this -> requestRes -> get_error_message() ) . ':' . rawurlencode( LocId::Pack( 'RequestHeadersTrace_%1$s', null, array( strip_tags( str_replace( array( '<br>' ), array( "\n" ), GetHeadersResString( $this -> hdrsForRequest ) ) ) ) ) );
			if( $this -> skipStatus )
				$this -> data[ 'r' ] = $this -> skipStatus;
		}

		$priorOrig = ( int )($this -> item[ 'p' ]??null);

		if( $this -> needLrn )
		{
			{
				$lock = new Lock( 'l', $this -> dirQueue );
				if( !$lock -> Acquire() )
				{

					return;
				}

				$aProgress = new ArrayOnFiles( Queue_GetStgPrms( $this -> dirQueue, 1 ) );
				unset( $aProgress[ $this -> id ] );
				$aProgress -> dispose();

				$lock -> Release();
				unset( $aProgress, $lock );
			}

			CachePostPreparePageEx( ($this -> data[ 'm' ]??null), ($this -> data[ 'u' ]??null), $this -> siteId, -480, ($this -> data[ 'p' ]??null), $this -> hdrs, $this -> data[ 'to' ], null, $this -> needLrn );
			$this -> immediatelyPushQueue = true;
		}
		else
		{
			if( $this -> hr == Gen::S_OK && $this -> warns )
				$this -> data[ 'w' ] = $this -> warns;
			if( $this -> infos )
				$this -> data[ 'i' ] = $this -> infos;

			if( isset( $this -> data[ 'hr' ] ) && $this -> data[ 'hr' ] != Gen::S_OK && $this -> urlRedir && $this -> urlRedir == ($this -> data[ 'u' ]??null) )
			{
				$this -> data[ 'hr' ] = ( $this -> hr = Gen::E_FAIL );
				$this -> data[ 'r' ] = 'redirectToItself';
			}

			{
				$lock = new Lock( 'l', $this -> dirQueue );
				if( !$lock -> Acquire() )
				{

					return;
				}

				$aProgress = new ArrayOnFiles( Queue_GetStgPrms( $this -> dirQueue, 1 ) );
				$aDone = new ArrayOnFiles( Queue_GetStgPrms( $this -> dirQueue, 2 ) );
				{
					$dataExtUpdated = $aProgress[ $this -> id ];
					if( $dataExtUpdated )
					{
						if( $dataExtUpdated = Gen::GetArrField( Gen::Unserialize( ($dataExtUpdated[ 'd' ]??null) ), array( '' ), array() ) )
							if( ($dataExtUpdated[ 'rpt' ]??null) )
								$this -> needRepeatPage = true;

						unset( $dataExtUpdated );
					}
				}

				$this -> item[ 't' ] = $this -> tmFinish;
				$this -> item[ 'p' ] = -1000;
				$this -> item[ 'd' ] = Gen::Serialize( $this -> data );

				unset( $aProgress[ $this -> id ] );
				$aDone[ $this -> id ] = $this -> item;

				$aProgress -> dispose(); $aDone -> dispose(); $lock -> Release();
				unset( $aProgress, $aDone, $lock );
			}
		}

		ProcessCtlData_Del( $this -> fileCtl );

		PluginFileValues::SetEx( PluginFileValues::GetDirVar( '' ), 'pelt', ( int )$this -> tmFinish );

		if( $this -> needRepeatPage && CachePostPreparePageEx( ($this -> data[ 'm' ]??null), ($this -> data[ 'u' ]??null), $this -> siteId, $priorOrig, ($this -> data[ 'p' ]??null), $this -> hdrs, $this -> data[ 'to' ], $this -> repeatIdx !== null ? ( $this -> repeatIdx + 1 ) : null, ($this -> data[ 'l' ]??null) ) )
			$this -> immediatelyPushQueue = true;

		CachePushQueueProcessor( true, $this -> immediatelyPushQueue, $this -> hr != Gen::S_OK && Gen::HrSucc( $this -> hr ) );

	}

	function _CbFinishOnExtCron( $content )
	{
		$this -> WaitForEndRequest();
		$this -> Finish();

		return( '' );
	}

}

function PackKvArrInfo( $a )
{
	if( !$a )
		return( null );
	return( Gen::ArrMap( $a, function( $k, $v ) { return( LocId::Pack( 'NameToDetails_%1$s%2$s', null, array( ( string )$k, is_string( $v ) ? $v : json_encode( $v ) ) ) ); } ) );
}

function _CacheProcessItem_RunSmpOpt( $asyncMode )
{

	$tmStamp = microtime( true );
	return( ProcessQueueItemCtx::MakeRequest( $asyncMode, 'GET', Net::UrlAddArgs( Plugin::AsyncTaskPushGetUrlEx( Wp::GetSiteWpRootUrl( OnAsyncTasksGetPushUrlFile( true ) ), 'O', $tmStamp ), array( 'nonce' => hash_hmac( 'md5', Plugin::AsyncTaskPushGetTimerun( $tmStamp ), GetSalt() ) ) ), array(  ) ) );
}

function OnAsyncTask_CacheProcessItem( $args )
{

	$ctx = new ProcessQueueItemCtx( ($args[ 'id' ]??null), Gen::SanitizeId( ($args[ 'siteId' ]??null) ) );
	if( !$ctx -> id || !$ctx -> siteId )
		return;

	$ctx -> item = null;
	{
		$lock = new Lock( 'l', $ctx -> dirQueue );
		if( !$lock -> Acquire() )
			return;

		$aInitial = new ArrayOnFiles( Queue_GetStgPrms( $ctx -> dirQueue, 0 ) );
		$ctx -> item = $aInitial[ $ctx -> id ];

		$aInitial -> dispose(); $lock -> Release();
		unset( $aInitial, $lock );
	}

	if( !$ctx -> item )
		return;

	$ctx -> data = Gen::GetArrField( Gen::Unserialize( ($ctx -> item[ 'd' ]??null) ), array( '' ), array() );
	if( !$ctx -> data )
		return;

	{ if( !isset( $ctx -> data[ 'p' ] ) ) $ctx -> data[ 'p' ] = ( int )($ctx -> item[ 'p' ]??null); }

	$ctx -> procTmLim = Gen::GetArrField( Plugin::SettGet(), array( 'cache', 'procTmLim' ), 570 );
	Gen::SetTimeLimit( $ctx -> procTmLim + 30 );

	$fileCtlDir = ProcessCtlData_GetFullPath();
	if( !( $ctx -> fileCtl = ProcessCtlData_Init( $fileCtlDir, array() ) ) )
	{
		$ctx -> data[ 'td' ] = 0;
		$ctx -> data[ 'hr' ] = Gen::E_FAIL;
		$ctx -> data[ 'r' ] = 'init:Can\'t modify files in \'' . $fileCtlDir . '\'';

		$ctx -> item[ 'd' ] = Gen::Serialize( $ctx -> data );

		$lock = new Lock( 'l', $ctx -> dirQueue );
		if( $lock -> Acquire() )
		{
			$aInitial = new ArrayOnFiles( Queue_GetStgPrms( $ctx -> dirQueue, 0 ) );
			$aDone = new ArrayOnFiles( Queue_GetStgPrms( $ctx -> dirQueue, 2 ) );

			unset( $aInitial[ $ctx -> id ] );
			$aDone[ $ctx -> id ] = $ctx -> item;

			$aInitial -> dispose(); $aDone -> dispose();
		}
		$lock -> Release();
		unset( $aInitial, $aDone, $lock );

		return;
	}
	unset( $fileCtlDir );

	$ctx -> data[ 'pc' ] = Gen::GetFileName( $ctx -> fileCtl );
	$ctx -> data[ 'to' ] = ( float )($ctx -> item[ 't' ]??null);
	$ctx -> item[ 't' ] = microtime( true );
	$ctx -> item[ 'd' ] = Gen::Serialize( $ctx -> data );

	{
		$lock = new Lock( 'l', $ctx -> dirQueue );
		if( !$lock -> Acquire() )
		{

			return;
		}

		$aInitial = new ArrayOnFiles( Queue_GetStgPrms( $ctx -> dirQueue, 0 ) );
		$aProgress = new ArrayOnFiles( Queue_GetStgPrms( $ctx -> dirQueue, 1 ) );

		$ok = true;
		if( $aInitial -> unsetItem( $ctx -> id ) === false )
			$ok = false;
		if( $ok && $aProgress -> setItem( $ctx -> id, $ctx -> item ) === false )
			$ok = false;

		$aInitial -> dispose(); $aProgress -> dispose();

		if( !$ok )
		{
			ProcessCtlData_Del( $ctx -> fileCtl );
			$lock -> Release();
			return;
		}

		$lock -> Release();
		unset( $aInitial, $aProgress, $lock );
	}

	$itemType = ( int )($ctx -> item[ 'tp' ]??0);
	if( $itemType == 0 )
	{
		$settGlobal = Plugin::SettGetGlobal();

		$asyncMode = OnAsyncTasksPushGetMode( $settGlobal );

		if( $asyncMode == 'ec' )
			return;

		if( ($settGlobal[ 'asyncSmpOpt' ]??null) )
		{
			_CacheProcessItem_RunSmpOpt( $asyncMode );

			return;
		}

		$ctx -> PrepareRequest();

		{
			$ctx -> requestRes = ProcessQueueItemCtx::MakeRequest( $asyncMode, $ctx -> method, $ctx -> url, $ctx -> hdrsForRequest, 30 );

			$ctx -> hr = Net::GetHrFromWpRemoteGet( $ctx -> requestRes, true );
			$ctx -> httpCode = Net::GetResponseCodeFromHr( $ctx -> hr );

		}

		$ctx -> WaitForEndRequest();
	}
	else
	{
		global $seraph_accel_g_prepPrms;

		$seraph_accel_g_prepPrms = array( 'pc' => $ctx -> fileCtl, 'p' => ( int )($ctx -> item[ 'p' ]??null) );
		$sett = Plugin::SettGet();

		$ctxProcess = &GetContentProcessCtx( $_SERVER, $sett );

		if( $itemType == 10 )
		{
			ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array( 'stage' => 'images' ) );

			$file = ($ctx -> data[ 'u' ]??null);
			Images_ProcessSrc_ConvertAll( $ctxProcess, Gen::GetArrField( $sett, array( 'contPr', 'img' ), array() ), null, $file, Images_ProcessSrcEx_FileMTime( $file ), false );
		}
		else if( $itemType == 20 )
		{
			ProcessCtlData_Update( ($seraph_accel_g_prepPrms[ 'pc' ]??null), array( 'stage' => 'images' ) );

			$file = ($ctx -> data[ 'u' ]??null);
			Images_ProcessSrc_SizeAlternatives( $ctxProcess, $file, $sett, ($ctx -> data[ 'ai' ]??null) );
		}

		unset( $ctxProcess );

		if( Gen::LastErrDsc_Is() )
		{
			$ctx -> hr = Gen::E_FAIL;
			$ctx -> skipStatus = 'err:' . rawurlencode( Gen::LastErrDsc_Get() );
		}

		$ctx -> warns = LastWarnDscs_Get();

		unset( $seraph_accel_g_prepPrms );

		$ctx -> tmFinish = microtime( true );
	}

	$ctx -> Finish();
}

function ExtCron_AddUrlToQueue( $method, $url, $hdrs )
{
	$dirExtCacheQueue = GetCacheDir() . '/qte';

	Gen::MakeDir( $dirExtCacheQueue, true );

	$lock = new Lock( 'l', $dirExtCacheQueue );
	if( $lock -> Acquire() )
	{
		$a = new ArrayOnFiles( array( 'dirFilesPattern' => $dirExtCacheQueue . '/*.dat.gz' ) );
		$a[] = array_merge( array( 'u' => $url, 'h' => $hdrs ), $method != 'GET' ? array( 'm' => $method ) : array() );
		$a -> dispose(); $lock -> Release();
	}
	unset( $a, $lock, $dirExtCacheQueue );
}

function ExtCron_Init()
{
	if( !isset( $_REQUEST[ 'seraph_accel_at' ] ) )
		return;

	$timeout = 60;
	$mode = Gen::SanitizeId( $_REQUEST[ 'seraph_accel_at' ] );
	if( $mode == 'TO' )
	{
		$timeout = 5;
		$mode = 'T';
		Net::CurRequestRemoveArgs( $_REQUEST, array( 'seraph_accel_at' => $mode ) );
	}
	else if( $mode != 'O' )
		return;

	if( isset( $_REQUEST[ 'ow' ] ) )
		$timeout = ( int )$_REQUEST[ 'ow' ];

	$asyncMode = OnAsyncTasksPushGetMode( ( array )Gen::CallFunc( 'seraph_accel_siteSettInlineDetach', array( 'm' ) ) );
	if( $asyncMode != 'ec' && ( !isset( $_REQUEST[ 'nonce' ] ) || hash_hmac( 'md5', '' . Gen::SanitizeId( ($_REQUEST[ 'rt' ]??null) ), GetSalt() ) != Gen::SanitizeId( $_REQUEST[ 'nonce' ] ) ) )
		return;

	@ignore_user_abort( true );
	Gen::CloseCurRequestSessionForContinueBgWorkEx();

	$item = null; $itemSimpUrl = null;
	$resUpd = null;
	$tmFinish = Gen::GetCurRequestTime() + $timeout;
	$bLoop = true;
	while( $bLoop )
	{
		if( !isset( $_REQUEST[ 'nonce' ] ) )
		{
			$dirExtCacheQueue = GetCacheDir() . '/qte';

			$lock = new Lock( 'l', $dirExtCacheQueue );
			if( $lock -> Acquire() )
			{
				$a = new ArrayOnFiles( array( 'dirFilesPattern' => $dirExtCacheQueue . '/*.dat.gz' ) );
				$itemSimpUrl = Gen::ArrGetByPos( $a -> splice( 0, 1, $resUpd ), 0 );
				$a -> dispose(); $lock -> Release();
			}
			unset( $a, $lock, $dirExtCacheQueue );

			if( $itemSimpUrl || $resUpd === false )
				break;
		}

		foreach( GetSiteIds() as $siteId )
		{
			$dirQueue = GetCacheDir() . '/q/' . $siteId;

			$lock = new Lock( 'l', $dirQueue );
			if( $lock -> Acquire() )
			{
				$aProgress = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 1 ) );
				foreach( $aProgress as $id => $itemTmp )
				{
					if( ($itemTmp[ 'stExt' ]??null) )
						continue;

					$itemTmp[ 'stExt' ] = true;
					$itemTmp[ 't' ] = microtime( true );
					if( $resUpd = $aProgress -> setItem( $id, $itemTmp ) )
						$item = $itemTmp;
					break;
				}
				$aProgress -> dispose(); $lock -> Release();
				unset( $aProgress, $lock );
			}

			if( $item || $resUpd === false || $tmFinish <= time() )
			{
				$bLoop = false;
				break;
			}
		}

		if( !$bLoop )
			break;

		sleep( 1 );
	}

	$aUrlParts = null;
	if( $itemSimpUrl )
	{
		$sMethod = Gen::GetArrField( $itemSimpUrl, array( 'm' ), 'GET' );
		$aHdrs = Gen::GetArrField( $itemSimpUrl, array( 'h' ), array() );
		$aUrlParts = Net::UrlParse( Gen::GetArrField( $itemSimpUrl, array( 'u' ), '' ) );
	}
	else if( $item && ( $data = Gen::GetArrField( $item ? Gen::Unserialize( ($item[ 'd' ]??null) ) : null, array( '' ), array() ) ) )
	{
		$ctx = new ProcessQueueItemCtx( $id, $siteId );
		$ctx -> item = $item;
		$ctx -> data = $data;
		$ctx -> fileCtl = ProcessCtlData_GetFullPath( $ctx -> data[ 'pc' ] );

		$ctx -> PrepareRequest();

		$sMethod = $ctx -> method;
		$aHdrs = $ctx -> hdrsForRequest;
		if( $aUrlParts = Net::UrlParse( $ctx -> url ) )
			ob_start( array( $ctx, '_CbFinishOnExtCron' ) );
	}

	if( !$aUrlParts )
	{
		if( $mode == 'O' )
			exit;
		return;
	}

	$_SERVER[ 'SERVER_NAME' ] = $aUrlParts[ 'host' ];
	$_SERVER[ 'REQUEST_METHOD' ] = $sMethod;
	$_SERVER[ 'REQUEST_SCHEME' ] = $aUrlParts[ 'scheme' ];
	$_SERVER[ 'SERVER_PORT' ] = isset( $aUrlParts[ 'port' ] ) ? $aUrlParts[ 'port' ] : ( $aUrlParts[ 'scheme' ] === 'https' ? 443 : 80 );
	$_SERVER[ 'HTTPS' ] = $aUrlParts[ 'scheme' ] === 'https' ? 'on' : 'off';
	$_SERVER[ 'HTTP_HOST' ] = $aUrlParts[ 'host' ];
	if( $_SERVER[ 'HTTPS' ] === 'on' )
	{
		if( $_SERVER[ 'SERVER_PORT' ] != 443 )
			$_SERVER[ 'HTTP_HOST' ] .= ':' . $_SERVER[ 'SERVER_PORT' ];
	}
	else
	{
		if( $_SERVER[ 'SERVER_PORT' ] != 80 )
			$_SERVER[ 'HTTP_HOST' ] .= ':' . $_SERVER[ 'SERVER_PORT' ];
	}
	$_SERVER[ 'REQUEST_URI' ] = $aUrlParts[ 'path' ] . ( isset( $aUrlParts[ 'query' ] ) ? ( '?' . $aUrlParts[ 'query' ] ) : '' );
	$_SERVER[ 'QUERY_STRING' ] = isset( $aUrlParts[ 'query' ] ) ? $aUrlParts[ 'query' ] : '';

	foreach( $aHdrs as $hdrKey => $hdrVal )
	{
		$hdrKey = strtoupper( str_replace( array( '-' ), '_', $hdrKey ) );
		$_SERVER[ 'HTTP_' . $hdrKey ] = $hdrVal;

		if( $hdrKey == 'COOKIE' )
		{
			foreach( explode( ';', trim( $hdrVal, " \t\n\r\0\x0B;" ) ) as $cookieKeyVal )
			{
				$cookieKeyVal = explode( '=', trim( $cookieKeyVal ) );
				if( count( $cookieKeyVal ) == 2 )
					$_COOKIE[ trim( $cookieKeyVal[ 0 ] ) ] = rawurldecode( trim( $cookieKeyVal[ 1 ] ) );
			}
		}
	}
	unset( $hdrKey, $hdrVal );

	$_REQUEST = Net::UrlParseQuery( $_SERVER[ 'QUERY_STRING' ] );
	$_GET = $_REQUEST;
	$_POST = array();
}

function GetHeadersResString( $hdrs )
{
	$res = '';
	foreach( $hdrs as $hdrId => $hdrVals )
	{
		if( !is_array( $hdrVals ) )
			$hdrVals = array( $hdrVals );

		foreach( $hdrVals as $hdrVal )
		{
			if( $res )
				$res .= '<br>';
			$res .= Ui::Tag( 'em', htmlspecialchars( $hdrId ) ) . ': ' . htmlspecialchars( $hdrVal );
		}
	}

	return( $res );
}

function CacheInitQueueTable( $force = false )
{
	$data = Plugin::DataGet();

	$dbVer = Gen::GetArrField( $data, 'queueDbVer', 0 );
	if( !$force && $dbVer == QUEUE_DB_VER )
		return;

	Gen::SetArrField( $data, 'queueDbVer', QUEUE_DB_VER );
	Plugin::DataSet( $data );

	if( $dbVer && $dbVer < 4 )
	{
		$nRowsChunk = 100;
		$dbtran = new Lock( 'ql', GetCacheDir() );
		if( $dbtran -> Acquire() )
		{
			$run = true;
			$tmStart = time();

			foreach( array( 2, 1, 0 ) as $state )
			{
				for( $i = 0; ; $i++ )
				{
					$items = DbTbl::GetRows( Db::GetTblPrefix( 'seraph_accel_queue' ), null, array( $i * $nRowsChunk, $nRowsChunk ), array( 'state' => $state ) );
					if( !$items )
						break;

					foreach( $items as $itemFromDb )
					{
						$id = @hex2bin( ($itemFromDb[ 'id' ]??null) );
						$siteId = ($itemFromDb[ 'site_id' ]??null);
						if( !$siteId )
							$siteId = 'm';

						$item = array( 'p' => ( int )($itemFromDb[ 'prior' ]??null), 't' => ( float )($itemFromDb[ 'tm' ]??null), 'd' => ($itemFromDb[ 'data' ]??null) );

						{
							$dirQueue = GetCacheDir() . '/q/' . $siteId;
							$lock = new Lock( 'l', $dirQueue );
							if( $lock -> Acquire() )
							{
								$a = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, $state ) );
								$a[ $id ] = $item;
								$a -> dispose(); unset( $a );

								$lock -> Release();
							}
							unset( $lock );
						}

						if( time() - $tmStart > 10 )
						{
							$run = false;
							break;
						}
					}

					if( !$run )
						break;
				}

				if( !$run )
					break;
			}

			DbTbl::DeleteRows( Db::GetTblPrefix( 'seraph_accel_queue' ) );
			$dbtran -> Release();
		}
	}
}

function GetViewDeviceGrpNameFromData( $viewsDeviceGrp )
{
	$viewName = ($viewsDeviceGrp[ 'name' ]??null);
	if( !$viewName )
		$viewName = 'id:' . ($viewsDeviceGrp[ 'id' ]??null);
	return( $viewName );
}

function _CachePostPreparePageEx_StopAndRepeat( $aProgress, $id, $data = null )
{
	$item = $aProgress[ $id ];
	if( !$item )
		return;

	$itemInProgressData = Gen::GetArrField( Gen::Unserialize( ($item[ 'd' ]??null) ), array( '' ), array() );

	if( ($itemInProgressData[ 'rpt' ]??null) || !( $fileCtl = ProcessCtlData_GetFullPath( ($itemInProgressData[ 'pc' ]??null) ) ) )
		return;

	ProcessCtlData_Del( $fileCtl );

	if( $data )
		$itemInProgressData = array_merge( $data, $itemInProgressData );
	$itemInProgressData[ 'rpt' ] = true;

	$item[ 'd' ] = Gen::Serialize( $itemInProgressData );
	$aProgress[ $id ] = $item;
}

function CachePostPreparePageEx( $method , $url, $siteId, $priority, $priorityInitiator, $headers = null, $time = null, $redirIdx = null, $lrnId = null )
{

	if( !$url )
		return( false );

	if( $priority == -480 && !$lrnId )
		return( false );

	if( $priorityInitiator === null )
		$priorityInitiator = $priority;

	$settCache = Gen::GetArrField( Plugin::SettGet(), array( 'cache' ), array() );

	if( $time === null )
		$time = microtime( true );

	if( !is_array( $headers ) )
		$headers = array();

	$id = $url . $priority;
	$idLearn = $url . -480;
	foreach( $headers as $header => $headerVal )
	{
		$headerVal = $header . ( is_array( $headerVal ) ? implode( '', $headerVal ) : $headerVal );
		$id .= $headerVal;
		$idLearn .= $headerVal;
	}
	$id = md5( $id, true );
	$idLearn = md5( $idLearn, true );

	$viewName = null;
	if( $viewsDeviceGrp = GetCacheViewDeviceGrp( $settCache, strtolower( isset( $headers[ 'X-Seraph-Accel-Postpone-User-Agent' ] ) ? $headers[ 'X-Seraph-Accel-Postpone-User-Agent' ] : ($headers[ 'User-Agent' ]??'') ) ) )
		$viewName = GetViewDeviceGrpNameFromData( $viewsDeviceGrp );

	$dirQueue = GetCacheDir() . '/q/' . $siteId;

	if( $priority == 10 )
	{
		$count = 0;
		{
			$lock = new Lock( 'l', $dirQueue );
			if( $lock -> Acquire() )
			{
				$aInitial = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 0 ) );
				$count = $aInitial -> count();
				$aInitial -> dispose();

				$lock -> Release();
			}

			unset( $aInitial, $lock );
		}

		if( $count > 1000 )
		{

			return( null );
		}
	}

	$data = array( 'p' => $priorityInitiator, 'u' => $url, 'h' => $headers, 'v' => $viewName );
	if( $method && $method != 'GET' )
		$data[ 'm' ] = $method;
	if( $redirIdx )
		$data[ 'rdr' ] = $redirIdx;
	if( $lrnId )
		$data[ 'l' ] = $lrnId;

	{
		$lock = new Lock( 'l', $dirQueue );
		if( !$lock -> Acquire() )
		{

			return( false );
		}

		$aInitial = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 0 ) );
		$aProgress = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 1 ) );
		$aDone = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 2 ) );

		unset( $aDone[ $id ] );

		$res = false;
		if( !isset( $aProgress[ $id ] ) )
		{
			$aInitial[ $id ] = array( 'p' => $priority, 't' => $time, 'd' => Gen::Serialize( $data ) );
			$res = true;
		}

		if( $priority !== 10 )
		{
			_CachePostPreparePageEx_StopAndRepeat( $aProgress, $id, $data );
			if( $id != $idLearn )
				_CachePostPreparePageEx_StopAndRepeat( $aProgress, $idLearn );
		}

		$aInitial -> dispose(); $aProgress -> dispose(); $aDone -> dispose(); $lock -> Release();
		unset( $aInitial, $aProgress, $aDone, $lock );
	}

	return( $res );
}

function CachePostPrepareObjEx( $type, $addr, $siteId, $priority, $data = array(), $priorityInitiator = null, $time = null )
{

	if( !$addr )
		return( false );

	if( $priorityInitiator === null )
		$priorityInitiator = $priority;

	$settCache = Gen::GetArrField( Plugin::SettGet(), array( 'cache' ), array() );

	if( $time === null )
		$time = microtime( true );

	$id = ( string )$type . $addr . $priority;
	$id = md5( $id, true );

	$dirQueue = GetCacheDir() . '/q/' . $siteId;

	$data = array_merge( $data, array( 'p' => $priorityInitiator, 'u' => $addr ) );

	{
		$lock = new Lock( 'l', $dirQueue );
		if( !$lock -> Acquire() )
		{

			return( false );
		}

		$aInitial = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 0 ) );
		$aDone = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 2 ) );

		unset( $aDone[ $id ] );

		$res = false;
		{
			$aInitial[ $id ] = array( 'tp' => $type, 'p' => $priority, 't' => $time, 'd' => Gen::Serialize( $data ) );
			$res = true;
		}

		$aInitial -> dispose(); $aDone -> dispose(); $lock -> Release();
		unset( $aInitial, $aDone, $lock );
	}

	return( $res );
}

function CacheQueueDelete( $siteId )
{
	$res = true;
	foreach( ( $siteId ? array( $siteId ) : GetSiteIds() ) as $siteIdEnum )
	{
		$dirQueue = GetCacheDir() . '/q/' . $siteIdEnum;

		$lock = new Lock( 'l', $dirQueue );
		if( !$lock -> Acquire() )
		{
			$res = false;
			continue;
		}

		$aInitial = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 0 ) );
		$aDone = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 2 ) );

		$aInitial -> clear();
		$aDone -> clear();

		$aInitial -> dispose(); $aDone -> dispose(); $lock -> Release();
		unset( $aInitial, $aDone, $lock );
	}

	return( $res );
}

function GetExtContents( &$ctxProcess, $url, &$contMimeType = null, $userAgentCmn = true, $timeout = 30, $rememberServerState = true )
{
	$extCacheId = null;
	if( $ctxProcess !== null )
	{
		$extCacheId = md5( $url );
		$file = null;
		$cont = null;

		$lock = new Lock( Gen::GetFileDir( $ctxProcess[ 'dataPath' ] ) . '/eo/l', false );
		if( $lock -> Acquire() )
		{
			foreach( glob( Gen::GetFileDir( $lock -> GetFileName() ) . '/' . $extCacheId . '.*', GLOB_NOSORT ) as $file )
				break;

			if( $file && ( $tmFile = @filemtime( $file ) ) !== false )
			{
				if( $tmFile > time() )
					$cont = @file_get_contents( $file );
				else
					@unlink( $file );
			}

			$lock -> Release();
		}

		if( is_string( $cont ) && Gen::GetFileExt( $file ) == 'gz' && is_string( $cont = @gzdecode( $cont ) ) )
			$file = Gen::GetFileName( $file, true, true );

		if( is_string( $cont ) )
		{
			$contMimeType = Fs::GetMimeContentType( $file );
			return( $cont );
		}

		unset( $file, $cont );
	}

	$args = array( 'sslverify' => false, 'timeout' => $timeout, 'headers' => array() );
	if( $userAgentCmn )
		$args[ 'headers' ][ 'User-Agent' ] = 'Mozilla/99999.9 AppleWebKit/9999999.99 (KHTML, like Gecko) Chrome/999999.0.9999.99 Safari/9999999.99 seraph-accel-Agent/2.28.15';

	global $seraph_accel_g_aGetExtContentsFailedSrvs;

	if( $serverId = Net::UrlParse( $url ) )
	{
		$args[ 'headers' ][ 'Host' ] = ($serverId[ 'host' ]??null);
		$serverId = Net::UrlDeParse( $serverId, 0, array( PHP_URL_USER, PHP_URL_PASS, PHP_URL_PATH, PHP_URL_QUERY, PHP_URL_FRAGMENT ) );
	}

	$args[ 'headers' ][ 'Accept' ] = '*/*';

	if( $rememberServerState && $serverId && Gen::HrFail( ($seraph_accel_g_aGetExtContentsFailedSrvs[ $serverId ]??null) ) )
		return( false );

	$res = Wp::RemoteGet( $url, $args );
	$hr = Net::GetHrFromWpRemoteGet( $res );
	if( Gen::HrFail( $hr ) )
	{

		if( $rememberServerState && ( $hr == Gen::E_TIMEOUT || $hr == Net::E_TIMEOUT ) && $serverId )
			$seraph_accel_g_aGetExtContentsFailedSrvs[ $serverId ] = $hr;
		return( false );
	}

	$contMimeType = ( string )Net::GetHeaderFromWpRemoteRequestRes( $res, 'content-type' );
	$cont = wp_remote_retrieve_body( $res );

	if( ( $nPos = strpos( $contMimeType, ';' ) ) !== false )
		$contMimeType = substr( $contMimeType, 0, $nPos );
	$contMimeType = trim( $contMimeType );

	if( $extCacheId !== null )
	{

		$contCacheTtl = Gen::ParseProps( ( string )Net::GetHeaderFromWpRemoteRequestRes( $res, 'cache-control' ), ',' );
		if( isset( $contCacheTtl[ 'no-cache' ] ) || isset( $contCacheTtl[ 'no-store' ] ) )
			$contCacheTtl = 5 * 60;
		else if( isset( $contCacheTtl[ 's-maxage' ] ) )
			$contCacheTtl = ( int )$contCacheTtl[ 's-maxage' ] - ( int )Net::GetHeaderFromWpRemoteRequestRes( $res, 'age' );
		else if( isset( $contCacheTtl[ 'max-age' ] ) )
			$contCacheTtl = ( int )$contCacheTtl[ 'max-age' ] - ( int )Net::GetHeaderFromWpRemoteRequestRes( $res, 'age' );
		else
		{
			if( $contCacheTtl = ( string )Net::GetHeaderFromWpRemoteRequestRes( $res, 'expires' ) )
			{
				if( $sDate = ( string )Net::GetHeaderFromWpRemoteRequestRes( $res, 'date' ) )
					$contCacheTtl = Net::GetTimeFromHdrVal( $contCacheTtl ) - Net::GetTimeFromHdrVal( $sDate );
				else
					$contCacheTtl = Net::GetTimeFromHdrVal( $contCacheTtl ) - time();
			}
			else
				$contCacheTtl = 60 * 60 * 24;
		}

		if( $contCacheTtl > 0 && ( $fileType = Fs::GetFileTypeFromMimeContentType( $contMimeType ) ) )
		{
			$contCache = $cont;
			if( in_array( $fileType, array( 'css', 'js', 'html', 'txt' ) ) && function_exists( 'gzencode' ) )
			{
				$contCache = @gzencode( $contCache, 9 );
				if( $contCache === false )
					$contCache = $cont;
				else
					$fileType .= '.gz';
			}

			_FileWriteTmpAndReplace( Gen::GetFileDir( $lock -> GetFileName() ) . '/' . $extCacheId . '.' . $fileType, time() + $contCacheTtl, $contCache, null, $lock );
			unset( $contCache );
		}
	}

	return( $cont );
}

function Images_ProcessSrcEx_FileMTime( $file )
{
	return( Gen::FileSize( $file ) ? @filemtime( $file ) : false );
}

function CacheExtractPreparePageParams( &$args )
{
	$prms = ($args[ 'seraph_accel_prep' ]??null);
	if( !$prms )
		return( null );

	Net::CurRequestRemoveArgs( $args, array( 'seraph_accel_prep' ) );

	$prms = @json_decode( @base64_decode( Gen::SanitizeTextData( $prms ) ), true );
	if( hash_hmac( 'md5', '' . ($prms[ '_tm' ]??null), GetSalt() ) != ($prms[ 'nonce' ]??null) )
		return( false );

	unset( $prms[ '_tm' ] );
	unset( $prms[ 'nonce' ] );

	if( isset( $prms[ 'pc' ] ) )
		$prms[ 'pc' ] = ProcessCtlData_GetFullPath( $prms[ 'pc' ] );

	if( isset( $_SERVER[ 'HTTP_X_SERAPH_ACCEL_GEO_REMOTE_ADDR' ] ) )
		$_SERVER[ 'REMOTE_ADDR' ] = $_SERVER[ 'HTTP_X_REAL_IP' ] = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] = $_SERVER[ 'HTTP_X_SERAPH_ACCEL_GEO_REMOTE_ADDR' ];

	return( $prms );
}

function GetCacheViewDeviceGrp( $settCache, $userAgent )
{
	if( !($settCache[ 'views' ]??null) )
		return( null );

	$viewsDeviceGrps = Gen::GetArrField( $settCache, array( 'viewsDeviceGrps' ), array() );

	foreach( $viewsDeviceGrps as $viewsDeviceGrp )
	{
		if( !($viewsDeviceGrp[ 'enable' ]??null) )
			continue;

		$a = implode( ' ', Gen::GetArrField( $viewsDeviceGrp, array( 'agents' ), array() ) );
		if( $a && strpos( $userAgent, $a ) !== false )
			return( $viewsDeviceGrp );
	}

	foreach( $viewsDeviceGrps as $viewsDeviceGrp )
	{
		if( !($viewsDeviceGrp[ 'enable' ]??null) )
			continue;

		if( MatchUserAgentExpressions( $userAgent, Gen::GetArrField( $viewsDeviceGrp, array( 'agents' ), array() ) ) )
			return( $viewsDeviceGrp );
	}

	return( null );
}

function InitTimeoutClnForWpNonce( $settCache )
{
	static $g_bInited;

	if( $g_bInited )
		return;

	if( !Gen::GetArrField( $settCache, array( 'enable' ), false ) || !Gen::GetArrField( $settCache, array( 'useTimeoutClnForWpNonce' ), false ) )
	{
		$g_bInited = true;
		return;
	}

	$ctx = new AnyObj();
	$ctx -> nonceTtlNeeded = Gen::GetArrField( $settCache, array( 'timeoutCln' ), 0 ) * 60 * 2;
	$ctx -> nonceTtlNeededLoggedin = ( Gen::GetArrField( $settCache, array( 'ctx' ), false ) && !Gen::GetArrField( $settCache, array( 'ctxSkip' ), false ) ) ? ( Gen::GetArrField( $settCache, array( 'ctxTimeoutCln' ), 0 ) * 60 * 2 ) : 0;

	{
		$ctx -> cb =
			function( $ctx, $nonceTtl )
			{
				$nonceTtlNeeded = is_user_logged_in() ? $ctx -> nonceTtlNeededLoggedin : $ctx -> nonceTtlNeeded;
				return( $nonceTtl < $nonceTtlNeeded ? $nonceTtlNeeded : $nonceTtl );
			};

		add_filter( 'nonce_life', array( $ctx, 'cb' ), 99999 );
	}

	{
		$ctx -> cbWpForms =
			function( $ctx, $times )
			{
				$nonceTtlNeeded = is_user_logged_in() ? $ctx -> nonceTtlNeededLoggedin : $ctx -> nonceTtlNeeded;
				if( !$nonceTtlNeeded )
				    return( $times );

				$nTtlDays = ( int )( $nonceTtlNeeded / DAY_IN_SECONDS );
				for( $day = 1; $day <= $nTtlDays; $day++ )
					$times[] = $day * DAY_IN_SECONDS;
				return( $times );
			};

		add_filter( 'wpforms_form_token_check_before_today', array( $ctx, 'cbWpForms' ), 99999 );
	}

	$g_bInited = true;
}

function GetCurHdrsToStoreInCache( $settCache )
{
	$res = array();

	$hdrPatterns = ($settCache[ 'hdrs' ]??null);
	if( !$hdrPatterns )
		return( $res );

	foreach( headers_list() as $hdr )
	{
		foreach( $hdrPatterns as $hdrPattern )
		{
			if( @preg_match( $hdrPattern, $hdr ) )
			{
				$res[] = $hdr;
				break;
			}
		}
	}

	return( $res );
}

function VirtUriPath2Real( $path, array $aVPth )
{
	foreach( $aVPth as $vPth )
	{
		$n = 0;
		$path = @preg_replace( $vPth[ 'f' ], $vPth[ 'r' ], $path, -1, $n );
		if( $n )
			break;
	}

	return( $path );
}

function GetVirtUriPathsFromSett( $sett )
{
	return( Gen::GetArrField( unserialize( ( string )base64_decode( Gen::GetArrField( $sett, array( 'cache', '_vPth' ), '' ) ) ), array( '' ), array() ) );
}

function _FileWriteTmpAndReplace( $file, $fileTime = null, $data = null, $fileTmp = null, $lock = null )
{

	if( $fileTmp === null )
		$fileTmp = $file . '.tmp';

	if( !$lock )
		$lock = new Lock( $fileTmp . '.l', false, true );

	if( ( $lr = $lock -> Acquire() ) !== false )
	{
		if( $data === null || @file_put_contents( $fileTmp, $data ) )
		{
			if( $fileTime === null || @touch( $fileTmp, $fileTime ) )
			{
				if( @file_exists( $file ) )
					@unlink( $file );

				if( @rename( $fileTmp, $file ) )
				{
					$lock -> Release();
					return( true );
				}
				else
					Gen::LastErrDsc_Set( LocId::Pack( 'FileRenameErr_%1$s%2$s', 'Common', array( $fileTmp, $file ) ) );
			}
			else
				Gen::LastErrDsc_Set( LocId::Pack( 'FileWriteErr_%1$s', 'Common', array( $fileTmp ) ) );
		}
		else
			Gen::LastErrDsc_Set( LocId::Pack( 'FileWriteErr_%1$s', 'Common', array( $fileTmp ) ) );

		if( $lr )
			$lock -> Release();
	}
	else
		Gen::LastErrDsc_Set( $lock -> GetErrDescr() );

	@unlink( $fileTmp );
	@unlink( $file );

	return( false );
}

function _FileReadWithLocker( $file, $lock = null )
{
	if( !$lock )
		$lock = new Lock( $fileTmp . '.l', false, true );

	if( !$lock -> Acquire() )
		return( false );

	$data = file_exists( $file ) ? @file_get_contents( $file ) : false;
	$lock -> Release();
	return( $data );
}

function LastWarnDscs_Add( $txt )
{
	global $seraph_accel_g_aLastWarnDsc;
	$seraph_accel_g_aLastWarnDsc[] = $txt;
}

function LastWarnDscs_Get()
{
	global $seraph_accel_g_aLastWarnDsc;
	return( $seraph_accel_g_aLastWarnDsc !== null ? $seraph_accel_g_aLastWarnDsc : array() );
}

function _SetExpirableOption( $option, $value, $timeout )
{
	update_option( $option, $value, false );
	update_option( $option . '_tmMax', time() + $timeout, false );
}

function _DelExpirableOption( $option )
{
	delete_option( $option );
	delete_option( $option . '_tmMax' );
}

function _DelExpiredOption( $option )
{
	$tmMax = get_option( $option . '_tmMax' );
	if( $tmMax && $tmMax < time() )
		_DelExpirableOption( $option );
}

function GetCountryCodeByIp( $settCache, &$ip_address )
{

	$country_code = '^';

	$dbFile = Gen::GetArrField( $settCache, array( 'viewsGeo', 'fileMmDb' ), '' );
	if( !$dbFile )
		$dbFile = GetCacheDir() . '/db/mm/ip2c.mmdb';

	try
	{
		$reader = new MaxMind\Db\Reader( $dbFile );
		$data = $reader -> get( $ip_address );

		if( isset( $data[ 'country' ][ 'iso_code' ] ) )
			$country_code = strtoupper( $data[ 'country' ][ 'iso_code' ] );

		$reader -> close();
	}
	catch( \Exception $e )
	{
	}

	return( $country_code );
}

function GetRegion2IPMap()
{
	$lock = new Lock( GetCacheDir() . '/db/l', false );
	$data = ( string )_FileReadWithLocker( GetCacheDir() . '/db/mm/c2ip-v1.dat', $lock );
	unset( $lock );

	$aRegionsIp = @unserialize( $data );
	return( is_array( $aRegionsIp ) ? $aRegionsIp : array() );
}

function DoesViewGeoGrpItemMatchEx( $aa, $countryCode )
{
	$matched = false;
	foreach( $aa as $a )
	{
		$v = null;
		if( IsStrRegExp( $a[ 'expr' ] ) )
		{
			if( @preg_match( $a[ 'expr' ], $countryCode ) )
				$v = $countryCode;
		}
		else if( $countryCode === $a[ 'expr' ] )
			$v = $countryCode;

		$matched = ExprConditionsSet_ItemOp( $a, $v );
		if( !$matched )
			break;
	}

	return( $matched );
}

function GetViewGeoId( $settCache, $serverArgs, &$ip, $geoIdForce = null )
{
	$aRegionsIp = GetRegion2IPMap();

	$aGrp = Gen::GetArrField( $settCache, array( 'viewsGeo', 'grps' ), array() );

	if( $geoIdForce !== null && $aRegionsIp )
	{
		$countryCodeForce = null;
		if( $geoIdForce === '' || Gen::StrStartsWith( $geoIdForce, 'G^' ) )
		{
			foreach( $aGrp as $grpId => $grp )
			{
				if( !($grp[ 'enable' ]??null) || ( $geoIdForce !== '' && $geoIdForce !== $grpId ) )
					continue;

				$grpItem = Gen::ArrGetByPos( Gen::GetArrField( $grp, array( 'items' ), array() ), 0 );
				if( ExprConditionsSet_IsTrivial( ExprConditionsSet_Parse( $grpItem ) ) )
				{
					if( isset( $aRegionsIp[ $grpItem ] ) )
						$countryCodeForce = $grpItem;
				}

				break;
			}
		}
		else
			$countryCodeForce = $geoIdForce;

		if( $countryCodeForce && isset( $aRegionsIp[ $countryCodeForce ] ) )
		{
			$ip = $aRegionsIp[ $countryCodeForce ];
			return( $geoIdForce );
		}
	}

	$countryCode = isset( $serverArgs[ 'HTTP_CF_IPCOUNTRY' ] ) ? trim( $serverArgs[ 'HTTP_CF_IPCOUNTRY' ] ) : '';
	if( !$countryCode )
		$countryCode = GetCountryCodeByIp( $settCache, $ip );

	$viewGeoId = null;
	$grpIsFirst = true;
	$countryCodeForce = null;
	foreach( $aGrp as $grpId => $grp )
	{
		if( !($grp[ 'enable' ]??null) )
			continue;

		$matched = false;
		$countryCodeFirstTmp = null;
		foreach( Gen::GetArrField( $grp, array( 'items' ), array() ) as $grpItem )
		{
			$aa = ExprConditionsSet_Parse( $grpItem );
			if( $countryCodeFirstTmp === null && ExprConditionsSet_IsTrivial( $aa ) )
				$countryCodeFirstTmp = $grpItem;

			if( !DoesViewGeoGrpItemMatchEx( $aa, $countryCode ) )
				continue;

			$matched = true;
			break;
		}

		if( $matched )
		{
			$viewGeoId = $grpIsFirst ? '' : $grpId;
			$countryCodeForce = $countryCodeFirstTmp;
			break;
		}

		$grpIsFirst = false;
	}

	if( $aRegionsIp )
	{
		if( $countryCodeForce && isset( $aRegionsIp[ $countryCodeForce ] ) )
			$ip = $aRegionsIp[ $countryCodeForce ];
		else if( $countryCode != '^' && isset( $aRegionsIp[ $countryCode ] ) )
			$ip = $aRegionsIp[ $countryCode ];
		else
			$ip = '127.0.0.1';
	}

	if( $viewGeoId === null )
		$viewGeoId = $countryCode;

	return( $viewGeoId );
}

function DepsExpand( $a, $bExpand = true )
{
	$aRes = array();
	foreach( $a as $type => $aId )
		$aRes[ $type ] = $bExpand ? array_fill_keys( $aId, array() ) : array_keys( $aId );
	return( $aRes );
}

function DepsDiff( $a, $aNew )
{
	$aRes = array_diff_key( $aNew, $a );
	foreach( $a as $type => $aoiCi )
		$aRes[ $type ] = array_diff_key( Gen::GetArrField( $aNew, array( $type ), array() ), $aoiCi );
	return( $aRes );
}

function DepsAdd( &$a, $type, $oiCi )
{
	$a[ $type ][ $oiCi ] = array();
}

function DepsAddMany( &$a, $aDeps )
{
	foreach( $aDeps as $type => $aoiCi )
		foreach( $aoiCi as $oiCi => $v )
			DepsAdd( $a, $type, $oiCi );
}

function DepsRemove( &$a, $aRem )
{
	foreach( $aRem as $type => $aoiCi )
	{
		foreach( $aoiCi as $oiCi => $v )
			unset( $a[ $type ][ $oiCi ] );

		if( isset( $a[ $type ] ) && !count( $a[ $type ] ) )
			unset( $a[ $type ] );
	}
}

function CacheExt_Clear_CopyHdrs( &$aSrv )
{
	if( isset( $aSrv[ 'cw_allowed_ip' ] ) )
		$aSrv[ 'HTTP_X_SERAPH_ACCEL_CW_ALLOWED_IP' ] = $aSrv[ 'cw_allowed_ip' ];
	if( isset( $aSrv[ 'PRESSABLE_PROXIED_REQUEST' ] ) )
		$aSrv[ 'HTTP_X_SERAPH_ACCEL_PRESSABLE_PROXIED_REQUEST' ] = $aSrv[ 'PRESSABLE_PROXIED_REQUEST' ];
	if( isset( $aSrv[ 'WARPDRIVE_API' ] ) )
		$aSrv[ 'HTTP_X_SERAPH_ACCEL_WARPDRIVE_API' ] = $aSrv[ 'WARPDRIVE_API' ];
	if( isset( $aSrv[ 'H_PLATFORM' ] ) )
		$aSrv[ 'HTTP_X_SERAPH_ACCEL_H_PLATFORM' ] = $aSrv[ 'H_PLATFORM' ];
}

function CacheExt_Clear_CopyHdrsArr()
{
	return( array( 'HTTP_X_LSCACHE', 'HTTP_X_ZXCS_VHOST', 'HTTP_X_SERAPH_ACCEL_CW_ALLOWED_IP', 'HTTP_X_SERAPH_ACCEL_PRESSABLE_PROXIED_REQUEST', 'HTTP_X_SERAPH_ACCEL_WARPDRIVE_API', 'HTTP_X_VARNISH', 'HTTP_X_SERAPH_ACCEL_H_PLATFORM' ) );
}

function CacheAdditional_WarmupUrl( $settCache, $url, $aHdrs, $cbIsAborted = null )
{

	$asyncMode = OnAsyncTasksPushGetMode();

	if( $asyncMode == 'ec' )
		return;

	$srvUpdTimeout = Gen::GetArrField( $settCache, array( 'srvUpdTimeout' ), 5 );
	foreach( $aHdrs as $hdrsId => $headers )
	{
		if( !isset( $headers[ 'User-Agent' ] ) )
			$headers[ 'User-Agent' ] = ($headers[ 'X-Seraph-Accel-Postpone-User-Agent' ]??'Mozilla/99999.9 AppleWebKit/9999999.99 (KHTML, like Gecko) Chrome/999999.0.9999.99 Safari/9999999.99 seraph-accel-Agent/2.28.15');
		$headers[ 'User-Agent' ] = str_replace( 'seraph-accel-Agent/', 'seraph-accel-Agent-WarmUp/', $headers[ 'User-Agent' ] );

		if( isset( $headers[ 'X-Seraph-Accel-Geo-Remote-Addr' ] ) )
			$headers[ 'X-Real-Ip' ] = $headers[ 'X-Forwarded-For' ] = $headers[ 'X-Seraph-Accel-Geo-Remote-Addr' ];

		$bProcess = true;
		$tmStart = microtime( true );
		for( ;; )
		{
			$requestRes = Wp::RemoteGet( $url, array( 'timeout' => $srvUpdTimeout, 'headers' => $headers, 'sslverify' => false,  ) );
			if( Net::GetHrFromWpRemoteGet( $requestRes ) !== Gen::S_OK )
			{
				$bProcess = false;
				break;
			}

			$cacheStatusCf = Net::GetHeaderFromWpRemoteRequestRes( $requestRes, 'cf-cache-status' );

			if( $cacheStatusCf != 'MISS' && $cacheStatusCf != 'EXPIRED' )
				break;

			if( ( microtime( true ) - $tmStart > $srvUpdTimeout ) || ( $cbIsAborted && call_user_func( $cbIsAborted ) ) )
			{
				$bProcess = false;
				break;
			}

			sleep( 1 );
		}

		if( !$bProcess )
			break;
	}
}

function CacheAdditional_UpdateCurUrl( $settCache, $srvUpd = false )
{
	global $seraph_accel_g_simpCacheMode;

	if( $seraph_accel_g_simpCacheMode !== null )

		return;

	$url = null;

	if( IsBatCacheRtm() )
	{
		if( $url === null )
		    $url = GetCurRequestUrl();
		BatCache_Clear( $url );
	}

	if( Gen::GetArrField( $settCache, array( 'srvClr' ), false ) && function_exists( 'seraph_accel\\CacheExt_Clear' ) )
	{
		if( $url === null )
			$url = GetCurRequestUrl();
		CacheExt_Clear( $url );
	}

	if( $srvUpd && Gen::GetArrField( $settCache, array( 'srvUpd' ), false ) )
	{
		if( $url === null )
			$url = GetCurRequestUrl();

		global $seraph_accel_g_ctxCache;

		$aHdrs = array( $seraph_accel_g_ctxCache !== null ? $seraph_accel_g_ctxCache -> viewId : 'cur' => Net::GetRequestHeaders() );
		CacheAdditional_WarmupUrl( $settCache, $url, $aHdrs );
	}
}

function IsBatCacheRtm()
{
	global $batcache;
	return( $batcache && is_object( $batcache ) );
}

function BatCache_Clear( $url = null )
{
	global $batcache;

	if( !$batcache )
		return( false );

	wp_cache_init();
	$batcache -> configure_groups();

	if( !isset( $batcache -> group ) )
		return( false );

	if( $url === null )
	{
		if( function_exists( 'wp_cache_flush_group' ) )
		{
			wp_cache_flush_group( $batcache -> group );
			if( isset( $batcache -> flush_group ) )
				wp_cache_flush_group( $batcache -> flush_group );
		}
		else
			wp_cache_flush();

		return( true );
	}

	$urlComps = Net::UrlParse( $url, Net::URLPARSE_F_QUERY );
	if( !$urlComps && !isset( $urlComps[ 'host' ] ) )
		return( false );

	if( isset( $batcache -> ignored_query_args ) )
		foreach( $batcache -> ignored_query_args as $arg )
			unset( $urlComps[ 'query' ][ $arg ] );
	ksort( $urlComps[ 'query' ] );

	$keysOld = $batcache -> keys;
	$batcache -> keys = array(
		'host' => Net::GetRequestHost( array( 'SERVER_NAME' => ($urlComps[ 'host' ]??''), 'SERVER_PORT' => ($urlComps[ 'port' ]??null) ) ),
		'method' => 'GET',
		'path' => ($urlComps[ 'path' ]??''),
		'query' => ($urlComps[ 'query' ]??array()),
		'extra' => array(),
	);

	$batcache -> add_flush_keys();

	$keys = $batcache -> keys;
	$batcache -> keys = $keysOld;

	if( isset( $batcache -> origin ) )
		$keys[ 'origin' ] = $batcache -> origin;

	if( ($urlComps[ 'scheme' ]??'') == 'https' )
		$keys[ 'ssl' ] = true;

	foreach( array( 'mobile', 'tablet', 'desktop' ) as $deviceType )
	{
		$keys[ 'extra' ] = array( $deviceType );
		wp_cache_delete( md5( serialize( $keys ) ), $batcache -> group );
	}

	return( true );
}

function BatCache_DontProcessCurRequest( $bForce = false )
{

	global $batcache;

	if( !$batcache )
		return( false );

	if( function_exists( 'batcache_cancel' ) )
		\batcache_cancel();

	if( $bForce )
		wp_cache_delete( $batcache -> url_key . '_genlock', $batcache -> group );

}

function LogGetRelativeFile()
{
	static $g_fileRel;

	if( $g_fileRel === null )
		$g_fileRel = '/logs/log.' . Gen::GetNonce( 'logFileSfx', GetSalt() ) . '.txt';

	return( $g_fileRel );
}

function LogWrite( $text, $severity = Ui::MsgInfo, $category = 'DEBUG' )
{
	$file = GetCacheDir() . LogGetRelativeFile();

	Gen::LogWrite( $file, $text, $severity, $category );
}

