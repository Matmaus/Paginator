<?php
	/**
	 * Created by Matmaus
	 * Date: 24. 6. 2016
	 * Time: 15:58
	 */

	namespace Matmaus;

	class Paginator
	{
		private $db;
		private $baseUrl;
		private $page = [];
		private $table;
		//private $tabs;
		private $offset;
		private $limit;
		private $boxes;
		private $results;
		private $div;
		private $mob;

		/**
		 * Paginator constructor.
		 *
		 * @param \PDO   $db
		 * @param string $urlPattern
		 */
		public function __construct(\PDO $db, $urlPattern)
		{
			if (version_compare(phpversion(), '5.4.0', '<')) {
				die('PHP 5.4.0 required for Paginator engine!');
			}

			$this->db = $db;

			$this->createUrl($urlPattern);
		}

		/**
		 * @param string $table
		 * @param int    $int
		 * @param int    $limit
		 *
		 * @return object
		 */
		public function paginate_arrows($table, $int = 1, $limit = 5)
		{

			if (filter_var($table, FILTER_SANITIZE_FULL_SPECIAL_CHARS) != NULL) {
				$this->table = $table;
			}

			$int = filter_var($int, FILTER_SANITIZE_NUMBER_INT);
			if (filter_var($int, FILTER_VALIDATE_INT)) {
				$this->offset = $int;
			}

			$limit = filter_var($limit, FILTER_SANITIZE_NUMBER_INT);
			if (filter_var($limit, FILTER_VALIDATE_INT)) {
				$this->limit = $limit;
			}

			// Find out how many items are in the table
			$total = $this->db->query("
     		    SELECT COUNT(*)
     		    FROM {$this->table}
    		")->fetchColumn();

			// How many pages will there be
			$pages = ceil($total / $this->limit);

			// What page are we currently on?
			$page = min($pages, $this->offset);

			// Calculate the offset for the query
			$this->offset = ($page - 1) * $this->limit;

			// Some information to display to the user
			$start = $this->offset + 1;
			$end   = min(($this->offset + $this->limit), $total);

			//Create an array of links
			$this->page_array($page, $pages);

			// The "back" link
			$prevLink = ($page > 1)
				? '<a href="'.$this->page["first"].
				  '" title="Začiatok"><i class="fa fa-angle-double-left"></i></a> <a href="'.$this->page["-1"].
				  '" title="Predošlá stránka"><i class="fa fa-angle-left"></i></a>'
				:
				'<span class="disabled"><i class="fa fa-angle-double-left"></i></span> <span class="disabled"><i class="fa fa-angle-left"></i></span>';

			// The "forward" link
			$nextLink = ($page < $pages)
				? '<a href="'.$this->page["+1"].
				  '" title="Ďalšia stránka"><i class="fa fa-angle-right"></i></a> <a href="'.$this->page["last"].
				  '" title="Koniec"><i class="fa fa-angle-double-right"></i></a>'
				:
				'<span class="disabled"><i class="fa fa-angle-right"></i></span> <span class="disabled"><i class="fa fa-angle-double-right"></i></span>';

			$this->div =
				'<div class="paging"><p> '.$prevLink.' Strana '.$page.' z '.$pages.', '.$start.' - '.
				$end.' / '.$total.' '.$nextLink.' </p></div>';

			$this->mob = '<div class="mob_paging"><p> '.$prevLink.' '.$nextLink.' </p></div>';

			$this->results = ['limit' => $this->limit, 'offset' => $this->offset, 'div' => $this->div,
			                  'mob'   => $this->mob,
			];

			return (object)$this->results;

		}

		/**
		 * @param     $table
		 * @param int $int
		 * @param int $limit
		 * @param int $boxes
		 *
		 * @return object
		 */
		public function paginate_numbers($table, $int = 1, $limit = 5, $boxes = 5)
		{

			if (filter_var($table, FILTER_SANITIZE_FULL_SPECIAL_CHARS) != NULL) {
				$this->table = $table;
			}

			$int = filter_var($int, FILTER_SANITIZE_NUMBER_INT);
			if (filter_var($int, FILTER_VALIDATE_INT)) {
				$this->offset = $int;
			}

			$limit = filter_var($limit, FILTER_SANITIZE_NUMBER_INT);
			if (filter_var($limit, FILTER_VALIDATE_INT)) {
				$this->limit = $limit;
			}

			$boxes = filter_var($boxes, FILTER_SANITIZE_NUMBER_INT);
			if (filter_var($boxes, FILTER_VALIDATE_INT)) {
				$this->boxes = $boxes;
			}

			// Find out how many items are in the table
			$total = $this->db->query("
                SELECT COUNT(*)
                FROM {$this->table}
            ")->fetchColumn();

			// How many pages will there be
			$pages = ceil($total / $this->limit);

			// What page are we currently on?
			$page = min($pages, $this->offset);

			// Calculate the offset for the query
			$this->offset = ($page - 1) * $this->limit;

			// Some information to display to the user
			$start = $this->offset + 1;
			$end   = min(($this->offset + $this->limit), $total);

			//Create an array of links
			$this->page_array($page, $pages);

			//start link
			$startlink = ($page > 1)
				? '<li>
                    <a href="'.$this->page["first"].'" title="Začiatok">
                        <i class="fa fa-chevron-circle-left"></i>
                    </a>
                </li>'
				:
				'<li>
                    <span class="disabled">
                        <i class="fa fa-chevron-circle-left"></i>
                    </span>
                </li>';

			// previous link
			$prevLink = ($page > 1)
				? '<li>
                    <a href="'.$this->page["prev"].'" title="Predošlá"">
                        <i class="fa fa-chevron-left"></i>
                    </a>
                </li>'
				:
				'<li>
                    <span class="disabled">
                        <i class="fa fa-chevron-left"></i>
                    </span>
                </li>';
			/*numbered links*/
			// 10000
			if ($page < 2) {
				$page1 = '<li class="active"> <span class="disabled">'.$page.'</span></li>';
				if ($pages > 1) {
					$page2 = '<li> <a href="'.$this->page["+1"].'">'.($page + 1).'</a></li>';
				} else {
					$page2 = '<li> <span class="disabled">'.($page + 1).'</span></li>';
				}
				if ($pages > 2) {
					$page3 = '<li> <a href="'.$this->page["+2"].'">'.($page + 2).'</a></li>';
				} else {
					$page3 = '<li> <span class="disabled">'.($page + 2).'</span></li>';
				}
				if ($pages > 3) {
					$page4 = '<li> <a href="'.$this->page["+3"].'">'.($page + 3).'</a></li>';
				} else {
					$page4 = '<li> <span class="disabled">'.($page + 3).'</span></li>';
				}
				if ($pages > 4) {
					$page5 = '<li> <a href="'.$this->page["+4"].'">'.($page + 4).'</a></li>';
				} else {
					$page5 = '<li> <span class="disabled">'.($page + 4).'</span></li>';
				}
			} // 01000
			else if ($page == 2) {
				$page1 =
					'<li> <a href="'.$this->page["-1"].'">'.($page - 1).'</a></li>';
				$page2 = '<li class="active"> <span class="disabled">'.$page.'</span></li>';
				if ($pages > 2) {
					$page3 = '<li> <a href="'.$this->page["+1"].'">'.($page + 1).
					         '</a></li>';
				} else {
					$page3 = '<li> <span class="disabled">'.($page + 1).'</span></li>';
				}
				if ($pages > 3) {
					$page4 = '<li> <a href="'.$this->page["+2"].'">'.($page + 2).
					         '</a></li>';
				} else {
					$page4 = '<li> <span class="disabled">'.($page + 2).'</span></li>';
				}
				if ($pages > 4) {
					$page5 = '<li> <a href="'.$this->page["+3"].'">'.($page + 3).
					         '</a></li>';
				} else {
					$page5 = '<li> <span class="disabled">'.($page + 3).'</span></li>';
				}
			} // 00010
			else if ($page > 3 && ($page == ($pages - 1) || $pages == 4)) {
				$page1 =
					'<li> <a href="'.$this->page["-3"].'">'.($page - 3).'</a></li>';
				$page2 =
					'<li> <a href="'.$this->page["-2"].'">'.($page - 2).'</a></li>';
				$page3 =
					'<li> <a href="'.$this->page["+1"].'">'.($page - 1).'</a></li>';
				$page4 = '<li class="active"> <span class="disabled">'.$page.'</span></li>';
				if ($pages > 4) {
					$page5 = '<li> <a href="'.$this->page["+1"].'">'.($page + 1).
					         '</a></li>';
				} else {
					$page5 = '<li> <span class="disabled">'.($page + 1).'</span></li>';
				}
			} // 00001
			else if ($page > 4 && $page == $pages) {
				$page1 =
					'<li> <a href="'.$this->page["-4"].'">'.($page - 4).'</a></li>';
				$page2 =
					'<li> <a href="'.$this->page["-3"].'">'.($page - 3).'</a></li>';
				$page3 =
					'<li> <a href="'.$this->page["-2"].'">'.($page - 2).'</a></li>';
				$page4 =
					'<li> <a href="'.$this->page["-1"].'">'.($page - 1).'</a></li>';
				$page5 = '<li class="active"> <span class="disabled">'.$page.'</span></li>';
			} // 00100
			else if ($page > 2 && ($page == 3 || $page <= ($pages - 2))) {
				$page1 =
					'<li> <a href="'.$this->page["-2"].'">'.($page - 2).'</a></li>';
				$page2 =
					'<li> <a href="'.$this->page["-1"].'">'.($page - 1).'</a></li>';
				$page3 = '<li class="active"> <span class="disabled">'.$page.'</span></li>';
				if ($pages > 3) {
					$page4 = '<li> <a href="'.$this->page["+1"].'">'.($page + 1).
					         '</a></li>';
				} else {
					$page4 = '<li> <span class="disabled">'.($page + 1).'</span></li>';
				}
				if ($pages > 4) {
					$page5 = '<li> <a href="'.$this->page["+2"].'">'.($page + 2).
					         '</a></li>';
				} else {
					$page5 = '<li> <span class="disabled">'.($page + 2).'</span></li>';
				}
			}
			// next link
			$nextLink = ($page < $pages)
				? '<li>
                                                <a href="'.$this->page["next"].'" title="Ďalšia">
                                                    <i class="fa fa-chevron-right"></i>
                                                </a>
                                            </li>'
				:
				'<li>
                                                <span class="disabled">
                                                    <i class="fa fa-chevron-right"></i>
                                                </span>
                                            </li>';

			//end link
			$endlink = ($page != $pages)
				? '<li>
                                            	<a href="'.$this->page["last"].'" title="Koniec">
													<i class="fa fa-chevron-circle-right"></i>
												</a>
                                        	</li>'
				:
				'<li>
                                           		<span class="disabled">
													<i class="fa fa-chevron-circle-right"></i>
												</span>
                                       		</li>';

			$div5 = '<nav class="text-center"> <ul class="pagination pagination-lg">'.$prevLink.$page1.$page2.
			        $page3.$page4.$page5.$nextLink.'</ul> </nav>';
			$div7 =
				'<nav class="text-center"> <ul class="pagination pagination-lg">'.$startlink.$prevLink.$page1.
				$page2.
				$page3.$page4.$page5.$nextLink.$endlink.'</ul> </nav>';

			$this->div = ($this->boxes === 5) ? $div5 : $div7;

			$this->results = ['limit' => $this->limit, 'offset' => $this->offset, 'div' => $this->div];

			return (object)$this->results;
		}

		private function createUrl($urlPattern)
		{
			//Example of usable UrlPatterns
			//$urlPattern = '/foo/articles/(:num)/slug';
			//$urlPattern = '/foo/articles/items/(:num)';
			//$urlPattern = '/foo/page/(:num)';
			//$urlPattern = '/foo?page=(:num)';

			$this->baseUrl = $_SERVER['SCRIPT_NAME'].substr($urlPattern,
			                                                strpos($urlPattern, basename($_SERVER['SCRIPT_NAME'])) +
			                                                strlen(basename($_SERVER['SCRIPT_NAME'])));
		}

		private function page_array($page, $pages)
		{
			$this->page = [
				"first" => str_replace("(:num)", 1, $this->baseUrl),
				"last"  => str_replace("(:num)", $pages, $this->baseUrl),
				"next"  => str_replace("(:num)", ($page + 1), $this->baseUrl),
				"prev"  => str_replace("(:num)", ($page - 1), $this->baseUrl),
				"-4"    => str_replace("(:num)", ($page - 4), $this->baseUrl),
				"-3"    => str_replace("(:num)", ($page - 3), $this->baseUrl),
				"-2"    => str_replace("(:num)", ($page - 2), $this->baseUrl),
				"-1"    => str_replace("(:num)", ($page - 1), $this->baseUrl),
				"+1"    => str_replace("(:num)", ($page + 1), $this->baseUrl),
				"+2"    => str_replace("(:num)", ($page + 2), $this->baseUrl),
				"+3"    => str_replace("(:num)", ($page + 3), $this->baseUrl),
				"+4"    => str_replace("(:num)", ($page + 4), $this->baseUrl),
			];
		}

	}
