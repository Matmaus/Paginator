<?php require "_partials/header.php"; ?>
	<main>

		<?php
			if ($url->segment(2)) {
				$get_int = filter_var($url->segment(2), FILTER_SANITIZE_NUMBER_INT);
				if (!empty($get_int)) {
					$int = filter_var($get_int, FILTER_VALIDATE_INT);
				}
				else{
					$int = 1;
				}
			}
			else{
				$int = 1;
			}

			$paginator->setStatePaginateArrows("http://localhost/farnost/example/(:num)", "notices", $int);
			$results = get_notices( $paginator->getOffset(), $paginator->getLimit() );
		?>

		<section>
			<h2 class="view_title">Farské oznamy</h2>

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
							Vytvorené: <?=$notice->created_at;?>
						</small>
					</time>
					<time datetime="<?=$notice->updated_at?>">
						<small>
							Aktualizované: <?=$notice->updated_at?>
						</small>
					</time>
					<span class="read_more">
						<a href=" <?= $notice->link ?> ">Pozrieť celé</a>
					</span>
				</article>

			<?php endforeach; endif; ?>

			<?php $paginator->printNormalAndMobile(); ?>
		</section>

	</main>
<?php require "_partials/footer.php"; ?>