<h4><?php _e( 'Schema.org Output Settings', 'autodescription' ); ?></h4><?php

if ( $this->has_json_ld_plugin() ) :
	$this->description( __( 'Another Schema.org plugin has been detected.', 'autodescription' ) );
else :
	$this->description( __( "The Schema.org markup is a standard way of annotating structured data for Search Engines. This markup is represented within hidden scripts throughout the website.", 'autodescription' ) );
	$this->description( __( "When your web pages include structured data markup, Search Engines can use that data to index your content better, present it more prominently in Search Results, and use it in several different applications.", 'autodescription' ) );

	?>
	<hr>

	<?php /* translators: https://developers.google.com/search/docs/data-types/sitelinks-searchbox */ ?>
	<h4><?php _ex( 'Sitelinks Searchbox', 'Product name', 'autodescription' ); ?></h4><?php
	$this->description( __( 'When Search users search for your brand name, the following option allows them to search through your website directly from the Search Results.', 'autodescription' ) );

	$info = $this->make_info(
		_x( 'Sitelinks Searchbox', 'Product name', 'autodescription' ),
		'https://developers.google.com/search/docs/data-types/sitelinks-searchbox',
		false
	);
	$this->wrap_fields(
		$this->make_checkbox(
			'ld_json_searchbox',
			_x( 'Enable Sitelinks Searchbox?', 'Product name', 'autodescription' ) . ' ' . $info,
			''
		),
		true
	);

	?>
	<hr>

	<h4><?php _e( 'Site Name', 'autodescription' ); ?></h4>	<?php
	$this->description( __( "When using breadcrumbs, the first entry is by default your website's address. Using the following option will convert it to the Site Name.", 'autodescription' ) );

	$info = $this->make_info(
		__( 'Include your Site Name in Search Results', 'autodescription' ),
		'https://developers.google.com/search/docs/data-types/sitename',
		false
	);
	$this->wrap_fields(
		$this->make_checkbox(
			'ld_json_sitename',
			__( 'Convert URL to Site Name?', 'autodescription' ) . ' ' . $info,
			sprintf( __( "The Site Name is: %s", 'autodescription' ), $this->code_wrap( $this->get_blogname() ) )
		),
		true
	);

	?>
	<hr>

	<h4><?php _e( 'Breadcrumbs', 'autodescription' ); ?></h4><?php
	$this->description( __( "Breadcrumb trails indicate the page's position in the site hierarchy. Using the following option will show the hierarchy within the Search Results when available.", 'autodescription' ) );

	$info = $this->make_info(
		__( 'About Breadcrumbs', 'autodescription' ),
		'https://developers.google.com/search/docs/data-types/breadcrumbs',
		false
	);
	$this->wrap_fields(
		$this->make_checkbox(
			'ld_json_breadcrumbs',
			__( 'Enable Breadcrumbs?', 'autodescription' ) . ' ' . $info,
			__( "Multiple trails can be outputted. The longest trail is prioritized.", 'autodescription' )
		),
		true
	);
endif;
