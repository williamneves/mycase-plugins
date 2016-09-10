<?php

if ( ! function_exists( 'yith_get_filesize_text' ) ) {
	/**
	 * Given a size in bytes, build a text rappresentation of the size using the correct unit of measure
	 *
	 * @param $size size in bytes
	 *
	 * @return string Textual rappresentation of the size
	 */
	function yith_get_filesize_text( $size ) {
		$unit = array( "bytes", "KB", "MB", "GB", "TB" );
		$step = 0;
		while ( $size >= 1024 ) {
			$size = $size / 1024;
			$step ++;
		}

		return sprintf( "%s %s", round( $size ), $unit[ $step ] );
	}
}

if ( ! function_exists( 'yith_create_zip' ) ) {
	/**
	 * Create a compressed archive containing one or more files
	 *
	 * @param array $files files to add to the compressed archive
	 * @param string $destination path of the resulting file
	 * @param $base_folder base folder of the files
	 * @param bool $overwrite
	 */
	function yith_create_zip( $files = array(), $destination = '', $base_folder, $overwrite = false ) {

		$archive = new PclZip( $destination );

		foreach ( $files as $file ) {
			$v_list = $archive->add( $file, PCLZIP_OPT_REMOVE_PATH, $base_folder );
		}

		if ( $v_list == 0 ) {
			die( "Error : " . $archive->errorInfo( true ) );
		}
	}
}

if ( ! function_exists( 'yith_download_file' ) ) {

	/**
	 * Download a file
	 *
	 * @param $filepath
	 */
	function yith_download_file( $filepath ) {
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . basename( $filepath ) );
		header( 'Content-Type: application/zip;' );
		header( "Content-Length: " . filesize( $filepath ) );

		readfile( $filepath );
		exit;
	}
}

if ( ! function_exists( 'yith_delete_folder' ) ) {

	function yith_delete_folder( $path ) {
		if ( is_dir( $path ) === true ) {
			$files = array_diff( scandir( $path ), array( '.', '..' ) );

			foreach ( $files as $file ) {
				yith_delete_folder( realpath( $path ) . '/' . $file );
			}

			return rmdir( $path );
		} else if ( is_file( $path ) === true ) {
			return unlink( $path );
		}

		return false;
	}
}
?>