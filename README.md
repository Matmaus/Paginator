# Simple Paginator - BETA

<!--
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]
[![SensioLabs Insight][ico-insight]][link-insight]
-->

Simple and easy to use plugin for websites to paginate of pages or whatever you want.

## Features

* Easy to use
* Multi-language support - EN, SK, CZ are predefined, possibility to add new one
* More editable CSS styles

## Install

1. Download manually Paginator plugin. Store Paginator file wherever you want.
2. Include this file everywhere you wish to use Paginator.
3. Create objects, as many as you wish.  
DO NOT FORGET about using namespace Matmaus.
If you want to use only single database and single language, 
you need just to create one instance, even if you want to paginate more items on a single page.
4. Set new state by calling `setStatePaginateArrows` or `setStatePaginateNumbers` methods.
5. Call method to print HTML code.

<!--
Via [Composer](http://getcomposer.org).

```bash
$ composer require tamtamchik/simple-flash
```

Inside your project make sure to start a session and load [Composer](http://getcomposer.org) autoload to make everything work.

````php
<?php
// Start a Session
if( !session_id() ) @session_start();

// Initialize Composer Autoload
require_once 'vendor/autoload.php';
````
-->

## Configure

##### Creating of instances

Parameter  |Explaining
----|----
database | PDO
language | must be one of predefined ("en", "sk", "cz") even if using own language, "en" is set as default
limit | positive number, used as default limit when is not specified by called method, default is set to 5

Example:
```php
$paginator = new Paginator($database);

OR

$paginator = new Paginator($database, "en", 5);
```

## Use

After creating a new instance, everything you need is to call methods on your objects.

Example:

```php
	<?php require_once 'Paginator.php'; ?>

...

	<?php 
		$paginator->setStatePaginateArrows("http://localhost/notices/(:num)", "notices", $offset);
		$results = get_notices( $paginator->getOffset(), $paginator->getLimit() );
	?>

...

	<?php if ( count($results) ) :foreach ( $results as $notice ) : $notice = format_notice( $notice )?>
		
		<article id="notice_<?= $notices->id ?>" class="notice">
			<h2>
				<a href=" <?= $notice->link ?> ">
					<?= $notice->title ?>
				</a>
			</h2>
			<div class="notice_content">
				<?= $notice->teaser ?>
			</div>
			<time datetime="<?=$notice->created_at?>">
				<small>
					Created: <?=$notice->created_at;?>
				</small>
			</time>
			<time datetime="<?=$notice->updated_at?>">
				<small>
					Updated: <?=$notice->updated_at?>
				</small>
			</time>
			<span class="read_more">
				<a href=" <?= $notice->link ?> ">See more</a>
			</span>
		</article>

	<?php endforeach; endif; ?>	

	<?php $paginator->printNormalAndMobile(); ?>

...	
```

Here are methods you can call:

### `getLimit()`
Getter for limit
#### Return
Limit

### `getOffset()`
Getter for offset
#### Return
Offset

### `getMobile()`
Get generated HTML code of mobile version
#### Return
```html
<div class="mob_paging">
	<p>
		<a href="http://localhost/farnost/example/1" title="First page"><i class="fa fa-angle-double-left"></i></a>
		<a href="http://localhost/farnost/example/1" title="Previous page"><i class="fa fa-angle-left"></i></a>
		<span class="disabled"><i class="fa fa-angle-right"></i></span>
		<span class="disabled"><i class="fa fa-angle-double-right"></i></span> 
	</p>
</div>
```

### `getNormal()`
Get generated HTML code of normal version
#### Return
```html
<div class="paging">
	<p>
		<a href="http://localhost/farnost/example/1" title="First page"><i class="fa fa-angle-double-left"></i></a>
		<a href="http://localhost/farnost/example/1" title="Previous page"><i class="fa fa-angle-left"></i></a> 
		Page 2 of 2, 9 - 14 / 14 
		<span class="disabled"><i class="fa fa-angle-right"></i></span>
		<span class="disabled"><i class="fa fa-angle-double-right"></i></span> 
	</p>
</div>
```

### `getNormalAndMobile()`
Get generated HTML code for both versions, mobile and normal
#### Return
```html
<div class="paging">
	<p>
		<a href="http://localhost/farnost/example/1" title="First page"><i class="fa fa-angle-double-left"></i></a>
		<a href="http://localhost/farnost/example/1" title="Previous page"><i class="fa fa-angle-left"></i></a> 
		Page 2 of 2, 9 - 14 / 14 
		<span class="disabled"><i class="fa fa-angle-right"></i></span>
		<span class="disabled"><i class="fa fa-angle-double-right"></i></span> 
	</p>
</div>
<div class="mob_paging">
	<p>
		<a href="http://localhost/farnost/example/1" title="First page"><i class="fa fa-angle-double-left"></i></a>
		<a href="http://localhost/farnost/example/1" title="Previous page"><i class="fa fa-angle-left"></i></a>
		<span class="disabled"><i class="fa fa-angle-right"></i></span>
		<span class="disabled"><i class="fa fa-angle-double-right"></i></span> 
	</p>
</div>
```

### `printNormalAndMobile()`
Print generated HTML code for both versions, mobile and normal

### `setStatePaginateArrows()`
Set new state for arrow style.
This means to set new limit, offset, url pattern, table, ...
Any method described above works with actual state.
#### Parameters
Full URL address with index of page replaced by (:num)
###### Example of usable UrlPatterns
```
$urlPattern = 'http://localhost/foo/articles/(:num)/slug';
$urlPattern = 'http://localhost/foo/articles/items/(:num)';
$urlPattern = 'http://localhost/foo/page/(:num)';
$urlPattern = 'http://localhost/foo?page=(:num)';
```
Name of table
Offset
Limit

### `setStatePaginateNumbers()`
Set new state for numeric style.
This means to set new limit, offset, url pattern, table, ...
Any method described above works with actual state.
#### Parameters
Full URL address with index of page replaced by (:num)
###### Example of usable UrlPatterns
```
$urlPattern = 'http://localhost/foo/articles/(:num)/slug';
$urlPattern = 'http://localhost/foo/articles/items/(:num)';
$urlPattern = 'http://localhost/foo/page/(:num)';
$urlPattern = 'http://localhost/foo?page=(:num)';
```
Name of table
Offset
Limit

### `addLanguage()`
Add new language
#### Parameters
Array or object containing new language
#### Example
```php
$myVeryOwnLanguage = [
			"page"       => "Page",
			"of"         => "of",
			"next_page"  => "Next page",
			"prev_page"  => "Previous page",
			"first_page" => "First page",
			"last_page"  => "Last page"
		];
$paginator->addLanguage($myVeryOwnLanguage);
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
