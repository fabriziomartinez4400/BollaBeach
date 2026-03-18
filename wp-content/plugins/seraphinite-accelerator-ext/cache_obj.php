<?php

if( !defined( 'ABSPATH' ) )
	exit;

if( !defined( 'SERAPH_ACCEL_PLUGIN_DIR' ) ) define( 'SERAPH_ACCEL_PLUGIN_DIR', __DIR__ ); else if( SERAPH_ACCEL_PLUGIN_DIR != __DIR__ ) return;

require_once( __DIR__ . '/common.php' );

function wp_cache_add_global_groups( $groups )
{
	global $wp_object_cache;
	$wp_object_cache -> add_global_groups( $groups );
}

function wp_cache_add_non_persistent_groups( $groups )
{
	global $wp_object_cache;
	$wp_object_cache -> add_non_persistent_groups( $groups );
}

function wp_cache_supports( $feature )
{
	static $g_aFeature = array( 'add_multiple' => 1, 'set_multiple' => 1, 'get_multiple' => 1, 'delete_multiple' => 1, 'flush_runtime' => 1, 'flush_group' => 1 );
	return( isset( $g_aFeature[ $feature ] ) );
}

function wp_cache_init()
{
	global $wp_object_cache;
	$wp_object_cache = new WP_Object_Cache();

	global $batcache;
	if( $batcache && method_exists( $batcache, 'configure_groups' ) )
		$batcache -> configure_groups();
}

function wp_cache_add( $key, $data, $group = '', $expire = 0 )
{
	global $wp_object_cache;
	return( $wp_object_cache -> add( $key, $data, $group, $expire ) );
}

function wp_cache_add_multiple( array $data, $group = '', $expire = 0 )
{
	global $wp_object_cache;
	return( $wp_object_cache -> add_multiple( $data, $group, $expire ) );
}

function wp_cache_replace( $key, $data, $group = '', $expire = 0 )
{
	global $wp_object_cache;
	return( $wp_object_cache -> replace( $key, $data, $group, $expire ) );
}

function wp_cache_set( $key, $data, $group = '', $expire = 0 )
{
	global $wp_object_cache;
	return( $wp_object_cache -> set( $key, $data, $group, $expire ) );
}

function wp_cache_set_multiple( array $data, $group = '', $expire = 0 )
{
	global $wp_object_cache;
	return( $wp_object_cache -> set_multiple( $data, $group, $expire ) );
}

function wp_cache_get( $key, $group = '', $force = false, &$found = null )
{
	global $wp_object_cache;
	return( $wp_object_cache -> get( $key, $group, $force, $found ) );
}

function wp_cache_get_multiple( $keys, $group = '', $force = false )
{
	global $wp_object_cache;
	return( $wp_object_cache -> get_multiple( $keys, $group, $force ) );
}

function wp_cache_delete( $key, $group = '' )
{
	global $wp_object_cache;
	return( $wp_object_cache -> delete( $key, $group ) );
}

function wp_cache_delete_multiple( array $keys, $group = '' )
{
	global $wp_object_cache;
	return( $wp_object_cache -> delete_multiple( $keys, $group ) );
}

function wp_cache_incr( $key, $offset = 1, $group = '' )
{
	global $wp_object_cache;
	return( $wp_object_cache -> incr( $key, $offset, $group ) );
}

function wp_cache_decr( $key, $offset = 1, $group = '' )
{
	global $wp_object_cache;
	return( $wp_object_cache -> decr( $key, $offset, $group ) );
}

function wp_cache_flush()
{
	global $wp_object_cache;
	return( $wp_object_cache -> flush() );
}

function wp_cache_flush_runtime()
{
	return( wp_cache_flush() );
}

function wp_cache_flush_group( $group )
{
	global $wp_object_cache;
	return( $wp_object_cache -> flush_group( $group ) );
}

function wp_cache_close()
{
	return( true );
}

function wp_cache_switch_to_blog( $blog_id )
{
	global $wp_object_cache;
	$wp_object_cache -> switch_to_blog( $blog_id );
}

function wp_cache_reset()
{
	global $wp_object_cache;
	$wp_object_cache -> reset();
}

class WP_Object_Cache
{
	public		$nHits = 0;
	public		$nMisses = 0;

	protected	$aGlobalGroup = array();
	protected	$aNonPersistentGroup = array();

	private		$aData = array();
	private		$inited;
	private		$curSite;
	private		$curSiteId;
	private		$dataDir;
	private		$lock;

	public function __construct()
	{
	}

	function __destruct()
	{

	}

	public function add_global_groups( $groups )
	{
		$this -> aGlobalGroup = array_merge( $this -> aGlobalGroup, array_fill_keys( ( array )$groups, true ) );
	}

	public function add_non_persistent_groups( $groups )
	{
		$this -> aNonPersistentGroup = array_merge( $this -> aNonPersistentGroup, array_fill_keys( ( array )$groups, true ) );
	}

	protected function _init()
	{
		if( $this -> inited )
			return;

		global $seraph_accel_settObjCache;

		$this -> inited = true;

		if( is_multisite() && function_exists( 'get_current_site' ) )
		{
			$this -> curSite = get_current_site();
			$this -> curSite = new \seraph_accel\AnyObj( array( 'blog_id' => get_current_blog_id(), 'site_id' => $this -> curSite -> site_id ) );
			$this -> curSite -> blog_id_orig = $this -> curSite -> blog_id;
		}
		else
			$this -> curSite = null;
		$this -> curSiteId = \seraph_accel\GetSiteId( $this -> curSite );

		$dataDir = \seraph_accel\GetCacheDir() . '/oc';
		$this -> lock = new \seraph_accel\Lock( $dataDir . '/l', false );
		$this -> dataDir = $dataDir . '/g';

		$this -> add_global_groups( \seraph_accel\Gen::GetArrField( $seraph_accel_settObjCache, array( 'cacheObj', 'groupsGlobal' ), array() ) );
		$this -> add_non_persistent_groups( \seraph_accel\Gen::GetArrField( $seraph_accel_settObjCache, array( 'cacheObj', 'groupsNonPersistent' ), array() ) );

		add_filter( 'pre_cache_alloptions',
            function( $alloptions )
			{
				unset( $alloptions[ 'cron' ] );
                return( $alloptions );
            }
		, PHP_INT_MAX );

		add_action( 'added_option', array( $this, '_clnWpOption' ), PHP_INT_MAX );
		add_action( 'updated_option', array( $this, '_clnWpOption' ), PHP_INT_MAX );
		add_action( 'deleted_option', array( $this, '_clnWpOption' ), PHP_INT_MAX );
	}

	function _clnWpOption( $option )
	{
		if( wp_installing() )
			return;

		$alloptions = wp_load_alloptions();
		if( isset( $alloptions[ $option ] ) )
			add_action( 'shutdown', array( $this, '_delWpAllOptions' ), PHP_INT_MAX - 1 );
		unset( $alloptions );
	}

	function _delWpAllOptions()
	{
		$this -> delete( 'alloptions', 'options' );
	}

	protected function _getPath( $group, $key )
	{
		return( array( empty( $group ) ? '@' : $group, isset( $this -> aGlobalGroup[ $group ] ) ? 'g' : 's/' . $this -> curSiteId, $key ) );
	}

	protected static function _normPath( &$path )
	{
		if( strlen( $path ) > 200 )
			$path = md5( $path );
		else
			$path = str_replace( array( ':', '?', '|', '&', '=', '#' ), array( '@', '@', '~', '+', '-', '@' ), $path );
	}

	protected static function _normExpiration( $expire )
	{
		global $seraph_accel_settObjCache;

		$nMax = \seraph_accel\Gen::GetArrField( $seraph_accel_settObjCache, array( 'cacheObj', 'timeout' ), 60 );

		$expire = ( int )$expire;
		if( $expire <= 0 || $expire > $nMax )
			$expire = $nMax;

		return( $expire );
	}

	protected function _getFilePath( $aPath )
	{
		self::_normPath( $aPath[ 0 ] );
		self::_normPath( $aPath[ count( $aPath ) - 1 ] );
		return( $this -> dataDir . '/' . implode( '/', $aPath ) . '.dat.gz' );
	}

	private function _updateFromStg( $aPath )
	{

		$file = $this -> _getFilePath( $aPath );

		$v = null;
		if( ( $lr = $this -> lock -> Acquire() ) !== false )
		{
			$v = @file_exists( $file ) ? array( @file_get_contents( $file ), @filemtime( $file ) ) : null;
			if( $lr )
				$this -> lock -> Release();

			if( $v !== null )
			{
				if( is_string( $v[ 0 ] ) && is_int( $v[ 1 ] ) )
				{
					if( is_string( $v[ 0 ] = @gzdecode( $v[ 0 ] ) ) )
					{
						$bOk = false;
						$v[ 0 ] = \seraph_accel\Gen::Unserialize( $v[ 0 ], null, $bOk );
						if( !$bOk )
							$v = null;
					}
					else
						$v = null;
				}
				else
					$v = null;
			}
		}

		if( $v !== null )
			\seraph_accel\Gen::SetArrField( $this -> aData, $aPath, $v );
		else
			\seraph_accel\Gen::UnsetArrField( $this -> aData, $aPath );

		return( $v );

	}

	private function _updateToStg( $aPath, $v )
	{

		$file = $this -> _getFilePath( $aPath );

		if( $v === null )
		{
			if( ( $lr = $this -> lock -> Acquire() ) === false )
				return( false );

			if( @file_exists( $file ) )
				@unlink( $file );

			if( $lr )
				$this -> lock -> Release();

			return( true );
		}

		if( !is_string( $data = \seraph_accel\Gen::Serialize( $v[ 0 ] ) ) )
			return( false );
		if( !is_string( $data = @gzencode( $data, 9 ) ) )
			return( false );

		if( ( $lr = $this -> lock -> Acquire() ) === false )
			return( false );

		\seraph_accel\Gen::MakeDir( @dirname( $file ), true );
		$res = \seraph_accel\_FileWriteTmpAndReplace( $file, $v[ 1 ], $data, null, $this -> lock );

		if( $lr )
			$this -> lock -> Release();

		return( $res );

	}

	public function __get( $name )
	{
		return $this -> $name;
	}

	public function __set( $name, $value )
	{
		$this -> $name = $value;
	}

	public function __isset( $name )
	{
		return isset( $this -> $name );
	}

	public function __unset( $name )
	{
		unset( $this -> $name );
	}

	static protected function _is_valid_key( $key )
	{
		if( is_int( $key ) )
			return( true );

		if( is_string( $key ) && trim( $key ) !== '' )
			return( true );

		return( false );
	}

	protected function _add( $bSetExist, $key, $data, $group, $expire, $time )
	{

		if( !self::_is_valid_key( $key ) )
			return( false );

		$aPath = $this -> _getPath( $group, $key );
		$bNonPersistentGroup = isset( $this -> aNonPersistentGroup[ $aPath[ 0 ] ] );

		$v = \seraph_accel\Gen::GetArrField( $this -> aData, $aPath );
		if( $v === null && !$bNonPersistentGroup )
			$v = $this -> _updateFromStg( $aPath );
		if( $bSetExist ? ( $v === null ) : ( $v !== null ) )
			return( false );

		return( $this -> _set( $aPath, $bNonPersistentGroup, $data, $expire, $time ) );
	}

	public function add( $key, $data, $group = '', $expire = 0 )
	{
		if( function_exists( 'wp_suspend_cache_addition' ) && wp_suspend_cache_addition()  )
			return( false );

		$this -> _init();

		return( $this -> _add( false, $key, $data, $group, self::_normExpiration( $expire ), time() ) );
	}

	public function add_multiple( array $aData, $group = '', $expire = 0 )
	{
		if( function_exists( 'wp_suspend_cache_addition' ) && wp_suspend_cache_addition()  )
			return( array_fill_keys( array_keys( $aData ), false ) );

		$this -> _init();

		$expire = self::_normExpiration( $expire );
		$time = time();

		$aRes = array();

		foreach( $aData as $key => $data )
			$aRes[ $key ] = $this -> _add( false, $key, $data, $group, $expire, $time );

		return( $aRes );
	}

	public function replace( $key, $data, $group = '', $expire = 0 )
	{

		$this -> _init();

		return( $this -> _add( true, $key, $data, $group, self::_normExpiration( $expire ), time() ) );
	}

	protected function _set( $aPath, $bNonPersistentGroup, $data, $expire, $time )
	{
		if( is_object( $data ) )
			$data = clone $data;

		$v = array( $data, $time + $expire );
		\seraph_accel\Gen::SetArrField( $this -> aData, $aPath, $v );
		if( !$bNonPersistentGroup )
			$this -> _updateToStg( $aPath, $v );

		return( true );
	}

	public function set( $key, $data, $group = '', $expire = 0 )
	{

		if( !self::_is_valid_key( $key ) )
			return( false );

		$this -> _init();

		$aPath = $this -> _getPath( $group, $key );
		$bNonPersistentGroup = isset( $this -> aNonPersistentGroup[ $aPath[ 0 ] ] );
		return( $this -> _set( $aPath, $bNonPersistentGroup, $data, self::_normExpiration( $expire ), time() ) );
	}

	public function set_multiple( array $aData, $group = '', $expire = 0 )
	{

		$this -> _init();

		$expire = self::_normExpiration( $expire );
		$time = time();

		$aRes = array();

		foreach( $aData as $key => $data )
		{
			$aPath = $this -> _getPath( $group, $key );
			$bNonPersistentGroup = isset( $this -> aNonPersistentGroup[ $aPath[ 0 ] ] );
			$aRes[ $key ] = self::_is_valid_key( $key ) ? $this -> _set( $aPath, $bNonPersistentGroup, $data, $expire, $time ) : false;
		}

		return( $aRes );
	}

	public function get( $key, $group = '', $force = false, &$found = null )
	{
		if( !self::_is_valid_key( $key ) )
			return( false );

		$this -> _init();

		$aPath = $this -> _getPath( $group, $key );
		$bNonPersistentGroup = isset( $this -> aNonPersistentGroup[ $aPath[ 0 ] ] );

		$v = null;

		if( $force && !$bNonPersistentGroup )
			$v = $this -> _updateFromStg( $aPath );
		else

		{
			$v = \seraph_accel\Gen::GetArrField( $this -> aData, $aPath );
			if( $v === null && !$bNonPersistentGroup )
				$v = $this -> _updateFromStg( $aPath );
		}

		if( $v !== null && $v[ 1 ] && $v[ 1 ] < time() )
		{
			\seraph_accel\Gen::UnsetArrField( $this -> aData, $aPath );
			$v = null;
			if( !$bNonPersistentGroup )
				$this -> _updateToStg( $aPath, null );
		}

		if( $v === null )
		{
			$found = false;
			$this -> nMisses += 1;
			return( false );
		}

		$v = $v[ 0 ];
		if( is_object( $v ) )
			$v = clone $v;

		$found = true;
		$this -> nHits += 1;
		return( $v );
	}

	public function get_multiple( $aKey, $group = '', $force = false )
	{
		$this -> _init();

		$aData = array();

		foreach( $aKey as $key )
			$aData[ $key ] = $this -> get( $key, $group, $force );

		return( $aData );
	}

	public function delete( $key, $group = '', $deprecated = false )
	{
		if( !self::_is_valid_key( $key ) )
			return( false );

		$this -> _init();

		$aPath = $this -> _getPath( $group, $key );
		$bNonPersistentGroup = isset( $this -> aNonPersistentGroup[ $aPath[ 0 ] ] );

		$v = \seraph_accel\Gen::GetArrField( $this -> aData, $aPath );
		if( $v === null && !$bNonPersistentGroup )
			$v = $this -> _updateFromStg( $aPath );
		if( $v === null )
			return( false );

		\seraph_accel\Gen::UnsetArrField( $this -> aData, $aPath );
		if( !$bNonPersistentGroup )
			$this -> _updateToStg( $aPath, null );
		return( true );
	}

	public function delete_multiple( array $aKey, $group = '' )
	{
		$this -> _init();

		$aRes = array();

		foreach( $aKey as $key )
			$aRes[ $key ] = $this -> delete( $key, $group );

		return( $aRes );
	}

	public function incr( $key, $offset = 1, $group = '' )
	{

		return( $this -> _incr( $key, $offset, $group ) );
	}

	public function decr( $key, $offset = 1, $group = '' )
	{

		return( $this -> _incr( $key, -$offset, $group ) );
	}

	private function _incr( $key, $offset = 1, $group = '' )
	{
		if( !self::_is_valid_key( $key ) )
			return( false );

		$this -> _init();

		if( $this -> lock -> Acquire() === false )
			return( false );

		$aPath = $this -> _getPath( $group, $key );
		$bNonPersistentGroup = isset( $this -> aNonPersistentGroup[ $aPath[ 0 ] ] );

		$v = \seraph_accel\Gen::GetArrField( $this -> aData, $aPath );
		if( $v === null && !$bNonPersistentGroup )
			$v = $this -> _updateFromStg( $aPath );
		if( $v === null )
		{
			$this -> lock -> Release();
			return( false );
		}

		if( !is_numeric( $v[ 0 ] ) )
			$v[ 0 ] = 0;

		$v[ 0 ] += ( int )$offset;
		if( $v[ 0 ] < 0 )
			$v[ 0 ] = 0;

		\seraph_accel\Gen::SetArrField( $this -> aData, $aPath, $v );
		if( !$bNonPersistentGroup )
			$this -> _updateToStg( $aPath, $v );

		$this -> lock -> Release();

		return( $v[ 0 ] );
	}

	public function flush()
	{
		$this -> _init();

		$this -> aData = array();

		if( $this -> lock -> Acquire() )

		{
			\seraph_accel\Gen::DelDir( $this -> dataDir, false );

			$this -> lock -> Release();

		}

		return( true );
	}

	public function flush_group( $group )
	{
		$this -> _init();

		$aPath = $this -> _getPath( $group, '' );

		\seraph_accel\Gen::UnsetArrField( $this -> aData, array( $aPath[ 0 ] ) );

		if( $this -> lock -> Acquire() )

		{
			\seraph_accel\Gen::DelDir( $this -> dataDir . '/' . $aPath[ 0 ] );

			$this -> lock -> Release();

		}

		return( true );
	}

	public function switch_to_blog( $blog_id )
	{
		$this -> _init();

		if( $this -> curSite )
		{
			$this -> curSite -> blog_id = ( int )$blog_id;
			$this -> curSiteId = \seraph_accel\GetSiteId( $this -> curSite );
		}
	}

	public function reset()
	{
		$this -> _init();

		if( $this -> curSite )
		{
			$this -> curSite -> blog_id = $this -> curSite -> blog_id_orig;
			$this -> curSiteId = \seraph_accel\GetSiteId( $this -> curSite );
		}
	}

	public function stats()
	{
		echo '<p>';
		echo "<strong>Cache Hits:</strong> {$this -> nHits}<br />";
		echo "<strong>Cache Misses:</strong> {$this -> nMisses}<br />";
		echo '</p>';
		echo '<ul>';
		foreach( $this -> aData as $group => $cache )
		{
			echo '<li><strong>Group:</strong> "' . esc_html( $group == '@' ? '' : $group ) . '" - (' . number_format( strlen( serialize( $cache ) ) / KB_IN_BYTES, 2 ) . ' KB)</li>';
		}
		echo '</ul>';
	}
}

