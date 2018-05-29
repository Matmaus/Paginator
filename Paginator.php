<?php
	/**
	 * Created by Matmaus
	 * Date: 24. 6. 2016
	 * Time: 15:58
	 */

	namespace Matmaus;

	class Paginator
	{
		// constants
		private $db;
		private $baseUrl;

		// state
		private $getLang;
		private $page = [];
		private $table;
		private $offset = NULL;
		private $limit;
		private $boxes;

		private $div = "";
		private $mob = "";

		// languages
		private $language_en = [
			"page"       => "Page",
			"of"         => "of",
			"next_page"  => "Next page",
			"prev_page"  => "Previous page",
			"first_page" => "First page",
			"last_page"  => "Last page"
		];
		private $language_sk = [
			"page"       => "Stránka",
			"of"         => "z",
			"next_page"  => "Ďaľšia stránka",
			"prev_page"  => "Predošlá stránka",
			"first_page" => "Prvá stránka",
			"last_page"  => "Posledná stránka"
		];
		private $language_cz = [
			"page"       => "Stránka",
			"of"         => "z",
			"next_page"  => "Další stránka",
			"prev_page"  => "Předchozí stránka",
			"first_page" => "První stránka",
			"last_page"  => "Poslední stránka"
		];

		//Example of usable UrlPatterns
		//$urlPattern = '/foo/articles/(:num)/slug';
		//$urlPattern = '/foo/articles/items/(:num)';
		//$urlPattern = '/foo/page/(:num)';
		//$urlPattern = '/foo?page=(:num)';

		/**
		 * Paginator constructor.
		 *
		 * @param \PDO   $database
		 * @param string $language
		 * @param int    $limit
		 */
		public function __construct(\PDO $database, $language = "en", $limit = 5)
		{
			$this->db = $database;

			$language = "language_".$language;
			if (!isset($this->{$language}))
				die("Paginator: Wrong language set!");
			$this->getLang = (object)$this->{$language};

			if (!is_numeric($limit))
				die("Paginator: Limit is not a number!");
			if ($limit < 1)
				die("Paginator: Limit has negative value!");
			$this->limit = $limit;
		}

		public function getLimit()
		{
			return $this->limit;
		}

		public function getOffset()
		{
			return $this->offset;
		}

		public function getMobile()
		{
			return $this->mob;
		}

		public function getNormal()
		{
			return $this->div;
		}

		public function getNormalAndMobile()
		{
			return $this->div."\n".$this->mob;
		}

		public function printNormalAndMobile()
		{
			echo $this->div."\n".$this->mob;;
		}

		/**
		 * @param $language
		 */
		public function addLanguage($language)
		{
			if (is_object($language)) {
				if (!isset($language->page) || !isset($language->of) || !isset($language->next_page) || !isset($language->prev_page) ||
				    !isset($language->first_page) || !isset($language->last_page))
					die("Paginator: Language must contain all of ->page, ->of, ->next_page, ->prev_page, ->first_page and ->last_page!");
			} else if (is_array($language)) {
				if (!isset($language['page']) || !isset($language['of']) || !isset($language['next_page']) || !isset($language['prev_page']) ||
				    !isset($language['first_page']) || !isset($language['last_page']))
					die("Paginator: Language must contain all of ['page'], ['of'], ['next_page'], ['prev_page'], ['first_page'] and ['last_page']!");
			} else
				die("Paginator: Language is nor array neither object!");
			$this->getLang = (object)$language;
		}

		/**
		 * @param        $urlPattern
		 * @param string $table
		 * @param int    $offset
		 * @param int    $limit
		 */
		public function setStatePaginateArrows($urlPattern, $table, $offset = 1, $limit = NULL)
		{
			$this->baseUrl = $urlPattern;

			$this->table = filter_var($table, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

			$offset = filter_var($offset, FILTER_SANITIZE_NUMBER_INT);
			if (!filter_var($offset, FILTER_VALIDATE_INT))
				die("Paginator: Index is not a number!");
			if ($offset < 1)
				die("Paginator: Offset has a negative value!");
			$this->offset = $offset;

			if ($limit != NULL) {
				$limit = filter_var($limit, FILTER_SANITIZE_NUMBER_INT);
				if (!filter_var($limit, FILTER_VALIDATE_INT))
					die("Paginator: Limit is not a number!");
				if ($limit < 1)
					die("Paginator: Limit has a negative value!");
				$this->limit = $limit;
			}

			// Find out how many items are in the table
			$total = $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();

			// How many pages will there be
			$pages = ceil($total / $this->limit);

			// What page are we currently on?
			$page = min($pages, $this->offset);

			// Calculate the offset for the query
			$this->offset = ($page - 1) * $this->limit;

			// Some information to display to the user
			$start = $this->offset + 1;
			$end   = min(($this->offset + $this->limit), $total);

			// Create an array of links
			$this->page_array($page, $pages);

			// The "back" link
			if (($page > 1)) {
				$prevLink = '<a href="'.$this->page["first"].
				            '" title="'.$this->getLang->first_page.'"><i class="fa fa-angle-double-left"></i></a> <a href="'.$this->page["-1"].
				            '" title="'.$this->getLang->prev_page.'"><i class="fa fa-angle-left"></i></a>';
			} else {
				$prevLink =
					'<span class="disabled"><i class="fa fa-angle-double-left"></i></span> <span class="disabled"><i class="fa fa-angle-left"></i></span>';
			}

			// The "forward" link
			if (($page < $pages)) {
				$nextLink = '<a href="'.$this->page["+1"].
				            '" title="'.$this->getLang->next_page.'"><i class="fa fa-angle-right"></i></a> <a href="'.$this->page["last"].
				            '" title="'.$this->getLang->last_page.'"><i class="fa fa-angle-double-right"></i></a>';
			} else {
				$nextLink =
					'<span class="disabled"><i class="fa fa-angle-right"></i></span> <span class="disabled"><i class="fa fa-angle-double-right"></i></span>';
			}

			$this->div =
				'<div class="paging"><p> '.$prevLink.' '.$this->getLang->page.' '.$page.' '.$this->getLang->of.' '.$pages.', '.$start.' - '.
				$end.' / '.$total.' '.$nextLink.' </p></div>';

			$this->mob = '<div class="mob_paging"><p> '.$prevLink.' '.$nextLink.' </p></div>';
		}

		/**
		 * @param     $urlPattern
		 * @param     $table
		 * @param int $offset
		 * @param int $limit
		 * @param int $boxes
		 */
		public function setStatePaginateNumbers($urlPattern, $table, $offset = NULL, $limit = NULL, $boxes = 5)
		{
			$this->baseUrl = $urlPattern;

			$this->table = filter_var($table, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

			if ($offset != NULL) {
				$offset = filter_var($offset, FILTER_SANITIZE_NUMBER_INT);
				if (!filter_var($offset, FILTER_VALIDATE_INT))
					die("Paginator: Index is not a number!");
				if ($offset < 1)
					die("Paginator: Offset has a negative value!");
				$this->offset = $offset;
			}

			if ($limit != NULL) {
				$limit = filter_var($limit, FILTER_SANITIZE_NUMBER_INT);
				if (!filter_var($limit, FILTER_VALIDATE_INT))
					die("Paginator: Limit is not a number!");
				if ($limit < 1)
					die("Paginator: Limit has a negative value!");
				$this->limit = $limit;
			}

			$boxes = filter_var($boxes, FILTER_SANITIZE_NUMBER_INT);
			if (filter_var($boxes, FILTER_VALIDATE_INT)) {
				$this->boxes = $boxes;
			}

			// Find out how many items are in the table
			$total = $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();

			// How many pages will there be
			$pages = ceil($total / $this->limit);

			// What page are we currently on?
			$page = min($pages, $this->offset);

			// Calculate the offset for the query
			$this->offset = ($page - 1) * $this->limit;

			//Create an array of links
			$this->page_array($page, $pages);

			//start link
			if (($page > 1)) {
				$startlink = '<li>
                    <a href="'.$this->page["first"].'" title="'.$this->getLang->first_page.'">
                        <i class="fa fa-chevron-circle-left"></i>
                    </a>
                </li>';
			} else {
				$startlink = '<li>
                    <span class="disabled">
                        <i class="fa fa-chevron-circle-left"></i>
                    </span>
                </li>';
			}

			// previous link
			if (($page > 1)) {
				$prevLink = '<li>
                    <a href="'.$this->page["prev"].'" title="'.$this->getLang->prev_page.'"">
                        <i class="fa fa-chevron-left"></i>
                    </a>
                </li>';
			} else {
				$prevLink = '<li>
                    <span class="disabled">
                        <i class="fa fa-chevron-left"></i>
                    </span>
                </li>';
			}
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
			if (($page < $pages)) {
				$nextLink = '<li>
                                <a href="'.$this->page["next"].'" title="'.$this->getLang->next_page.'">
                                    <i class="fa fa-chevron-right"></i>
                                </a>
                            </li>';
			} else {
				$nextLink = '<li>
                                <span class="disabled">
                                    <i class="fa fa-chevron-right"></i>
                                </span>
                            </li>';
			}

			//end link
			if (($page != $pages)) {
				$endlink = '<li>
                                <a href="'.$this->page["last"].'" title="'.$this->getLang->last_page.'">
									<i class="fa fa-chevron-circle-right"></i>
								</a>
                            </li>';
			} else {
				$endlink = '<li>
                                <span class="disabled">
									<i class="fa fa-chevron-circle-right"></i>
								</span>
                            </li>';
			}

			$div5 = '<nav class="text-center"> <ul class="pagination pagination-lg">'.$prevLink.$page1.$page2.
			        $page3.$page4.$page5.$nextLink.'</ul> </nav>';
			$div7 =
				'<nav class="text-center"> <ul class="pagination pagination-lg">'.$startlink.$prevLink.$page1.
				$page2.
				$page3.$page4.$page5.$nextLink.$endlink.'</ul> </nav>';

			$this->div = ($this->boxes === 5) ? $div5 : $div7;
		}

		/**
		 * @param $page
		 * @param $pages
		 */
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
