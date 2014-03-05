<?php
$lz_default['general']=array(
	'site_title'=>'%1$s %2$s',
	'logo'=>get_template_directory_uri().'/images/logo.png',
	'favicon'=>'',
	'footer_txt'=>'&copy; '.get_bloginfo('name'),
);
$lz_default['slider']=array(
	'slides'=>array(
		array(
			'title'=>'Slide 1',
			'description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam vulputate augue orci, eget tempus dui volutpat nec.',
			'link'=>'#',
			'image'=>get_template_directory_uri().'/images/demo/slide1.jpg'
		),
		array(
			'title'=>'Slide 2',
			'description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam vulputate augue orci, eget tempus dui volutpat nec.',
			'link'=>'#',
			'image'=>get_template_directory_uri().'/images/demo/slide2.jpg'
		),
		array(
			'title'=>'Slide 3',
			'description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam vulputate augue orci, eget tempus dui volutpat nec.',
			'link'=>'#',
			'image'=>get_template_directory_uri().'/images/demo/slide3.jpg'
		),
		array(
			'title'=>'Slide 4',
			'description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam vulputate augue orci, eget tempus dui volutpat nec.',
			'link'=>'#',
			'image'=>get_template_directory_uri().'/images/demo/slide4.jpg'
		),
		array(
			'title'=>'Slide 5',
			'description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam vulputate augue orci, eget tempus dui volutpat nec.',
			'link'=>'#',
			'image'=>get_template_directory_uri().'/images/demo/slide5.jpg'
		)
	),
	'innerpages'=>true,
	'speed'=>'2000',
	'delay'=>'5000',
	'effect'=>'fade'
);
$lz_default['showroom']=array(
	'showroomsrc'=>'page',
	'onlymarked'=>true,
	'length'=>100,
	'srinnerpages'=>false,
	'srreadmore'=>'Read more'
);
$lz_default['layout']=array(
	'pagination'=>'dynamic',
	'fimage_width'=>515,
	'fimage_height'=>190,
	'cutcontent'=>true,
	'contentlength'=>467,
	'fimage_position'=>'alignleft',
	'relatedposts'=>true
);

$lz_default['menus']=array(
	'effect'=>'slidedown',
	'speed'=>'500',
	'delay'=>'800',
	'showarrows'=>true
);
$lz_default['integration']=array(
	'css'=>'',
	'headcode'=>''
);
$lz_default['elements']=array(
	'logo'=>array('title'=>__( 'Logo', 'lizard' ), 'show'=>true, 'help'=>'logo', 'edit'=>'admin.php?page=LZSettings&section=general'),
	'search'=>array('title'=>__( 'Search', 'lizard' ), 'show'=>true, 'help'=>'search', 'edit'=>'widgets.php'),
	'secondary-menu'=>array('title'=>__( 'Secondary Menu', 'lizard' ), 'show'=>true, 'help'=>'menus', 'edit'=>'nav-menus.php'),
	'main-menu'=>array('title'=>__( 'Main Menu', 'lizard' ), 'show'=>true, 'help'=>'menus', 'edit'=>'nav-menus.php'),
	'slider'=>array('title'=>__( 'Slider', 'lizard' ), 'show'=>true, 'help'=>'slider', 'edit'=>'admin.php?page=LZSettings&section=slider'),
	'showroom'=>array('title'=>__( 'Showroom', 'lizard' ), 'show'=>true, 'help'=>'showroom', 'edit'=>'admin.php?page=LZSettings&section=showroom'),
	'leftsidebar'=>array('title'=>__( 'Left Sidebar', 'lizard' ), 'show'=>false, 'help'=>'sidebar', 'edit'=>'widgets.php'),
	'rightsidebar'=>array('title'=>__( 'Right Sidebar', 'lizard' ), 'show'=>true, 'help'=>'sidebar', 'edit'=>'widgets.php'),
	'footer'=>array('title'=>__( 'Footer', 'lizard' ), 'show'=>true, 'help'=>'footer', 'edit'=>'widgets.php')
);
$lz_default['fonts']=array(
	'heading'=>'Oswald',
	'body'=>'Arial',
	'menu'=>'Oswald'
);